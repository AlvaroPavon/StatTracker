<?php
/**
 * Clase SessionManager - Gestión segura de sesiones
 * Implementa protecciones contra session hijacking, fixation, etc.
 * @package App
 */

namespace App;

class SessionManager
{
    // Configuración
    private const SESSION_LIFETIME = 3600;      // 1 hora
    private const SESSION_REGENERATE_TIME = 300; // Regenerar ID cada 5 minutos
    private const MAX_IDLE_TIME = 1800;          // 30 minutos de inactividad máxima
    private const FINGERPRINT_COMPONENTS = ['HTTP_USER_AGENT', 'HTTP_ACCEPT_LANGUAGE', 'HTTP_ACCEPT_ENCODING'];
    
    /**
     * Inicia una sesión segura
     */
    public static function start(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return true;
        }
        
        // Configurar parámetros de sesión seguros antes de iniciar
        self::configureSession();
        
        // Iniciar sesión
        if (!session_start()) {
            return false;
        }
        
        // Validar la sesión existente
        if (!self::validate()) {
            self::destroy();
            session_start();
        }
        
        // Inicializar metadatos si es sesión nueva
        if (!isset($_SESSION['_security'])) {
            self::initializeSession();
        }
        
        // Verificar y regenerar ID si es necesario
        self::maybeRegenerateId();
        
        // Actualizar tiempo de última actividad
        $_SESSION['_security']['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Configura parámetros de sesión seguros
     */
    private static function configureSession(): void
    {
        // Solo cookies, no URL
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_trans_sid', 0);
        
        // Cookie settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Secure cookie en HTTPS
        if (self::isHttps()) {
            ini_set('session.cookie_secure', 1);
        }
        
        // Lifetime
        ini_set('session.gc_maxlifetime', self::SESSION_LIFETIME);
        ini_set('session.cookie_lifetime', 0); // Hasta cerrar navegador
        
        // Entropy
        ini_set('session.entropy_length', 32);
        ini_set('session.hash_function', 'sha256');
        
        // Nombre de sesión personalizado (no revelar PHP)
        session_name('STATTRACKER_SID');
        
        // Cookie params
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
    
    /**
     * Inicializa metadatos de seguridad en sesión nueva
     */
    private static function initializeSession(): void
    {
        $_SESSION['_security'] = [
            'created_at' => time(),
            'last_activity' => time(),
            'last_regeneration' => time(),
            'fingerprint' => self::generateFingerprint(),
            'ip' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
    }
    
    /**
     * Valida la integridad de la sesión
     */
    public static function validate(): bool
    {
        if (!isset($_SESSION['_security'])) {
            return true; // Sesión nueva, será inicializada
        }
        
        $security = $_SESSION['_security'];
        
        // 1. Verificar tiempo de vida máximo
        if (time() - $security['created_at'] > self::SESSION_LIFETIME) {
            SecurityAudit::log('SESSION_EXPIRED', $_SESSION['user_id'] ?? null, [
                'reason' => 'lifetime_exceeded',
                'age' => time() - $security['created_at']
            ]);
            return false;
        }
        
        // 2. Verificar inactividad
        if (time() - $security['last_activity'] > self::MAX_IDLE_TIME) {
            SecurityAudit::log('SESSION_EXPIRED', $_SESSION['user_id'] ?? null, [
                'reason' => 'idle_timeout',
                'idle_time' => time() - $security['last_activity']
            ]);
            return false;
        }
        
        // 3. Verificar fingerprint
        $currentFingerprint = self::generateFingerprint();
        if (!hash_equals($security['fingerprint'], $currentFingerprint)) {
            SecurityAudit::logSessionHijackAttempt($_SESSION['user_id'] ?? null);
            return false;
        }
        
        // 4. Verificar cambio drástico de User-Agent (posible hijacking)
        $currentUA = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (!empty($security['user_agent']) && !empty($currentUA)) {
            $similarity = 0;
            similar_text($security['user_agent'], $currentUA, $similarity);
            if ($similarity < 70) { // Menos del 70% similar es sospechoso
                SecurityAudit::logSessionHijackAttempt($_SESSION['user_id'] ?? null);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Regenera el ID de sesión si es necesario
     */
    private static function maybeRegenerateId(): void
    {
        if (!isset($_SESSION['_security']['last_regeneration'])) {
            return;
        }
        
        if (time() - $_SESSION['_security']['last_regeneration'] > self::SESSION_REGENERATE_TIME) {
            self::regenerateId();
        }
    }
    
    /**
     * Regenera el ID de sesión de forma segura
     */
    public static function regenerateId(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        
        // Regenerar ID y eliminar sesión anterior
        if (!session_regenerate_id(true)) {
            return false;
        }
        
        // Actualizar tiempo de regeneración
        $_SESSION['_security']['last_regeneration'] = time();
        
        return true;
    }
    
    /**
     * Destruye la sesión de forma segura
     */
    public static function destroy(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return true;
        }
        
        // Guardar ID de usuario para logging
        $userId = $_SESSION['user_id'] ?? null;
        
        // Limpiar variables de sesión
        $_SESSION = [];
        
        // Eliminar cookie de sesión
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Destruir sesión en servidor
        $result = session_destroy();
        
        // Registrar logout
        if ($userId !== null) {
            SecurityAudit::log('LOGOUT', $userId, ['clean' => true]);
        }
        
        return $result;
    }
    
    /**
     * Autentica un usuario y configura la sesión
     */
    public static function authenticate(int $userId, string $userName): void
    {
        // Regenerar ID para prevenir session fixation
        self::regenerateId();
        
        // Establecer datos de usuario
        $_SESSION['user_id'] = $userId;
        $_SESSION['nombre'] = $userName;
        $_SESSION['authenticated_at'] = time();
        
        // Actualizar fingerprint con datos actuales
        $_SESSION['_security']['fingerprint'] = self::generateFingerprint();
        $_SESSION['_security']['ip'] = self::getClientIp();
        $_SESSION['_security']['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Registrar login exitoso
        SecurityAudit::logLoginSuccess($userId, '');
    }
    
    /**
     * Verifica si el usuario está autenticado
     */
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && self::validate();
    }
    
    /**
     * Obtiene el ID del usuario actual
     */
    public static function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Genera huella digital de la sesión
     */
    private static function generateFingerprint(): string
    {
        $data = [];
        
        foreach (self::FINGERPRINT_COMPONENTS as $component) {
            $data[] = $_SERVER[$component] ?? '';
        }
        
        return hash('sha256', implode('|', $data) . '|STATTRACKER_SALT_2025');
    }
    
    /**
     * Obtiene la IP del cliente
     */
    private static function getClientIp(): string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Verifica si la conexión es HTTPS
     */
    private static function isHttps(): bool
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            return true;
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }
        return false;
    }
    
    /**
     * Obtiene información de la sesión actual
     */
    public static function getInfo(): array
    {
        if (!isset($_SESSION['_security'])) {
            return [];
        }
        
        $security = $_SESSION['_security'];
        
        return [
            'session_id' => session_id(),
            'user_id' => $_SESSION['user_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s', $security['created_at']),
            'last_activity' => date('Y-m-d H:i:s', $security['last_activity']),
            'age_seconds' => time() - $security['created_at'],
            'idle_seconds' => time() - $security['last_activity'],
            'ip' => $security['ip']
        ];
    }
}
