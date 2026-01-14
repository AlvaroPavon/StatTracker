<?php
/**
 * Clase AdvancedProtection - Protecciones avanzadas adicionales
 * Cubre vectores de ataque menos comunes pero importantes
 * @package App
 */

namespace App;

class AdvancedProtection
{
    /**
     * Protección contra Account Lockout DOS
     * Limita cuántas cuentas diferentes pueden ser bloqueadas desde una IP
     */
    public static function checkAccountLockoutAbuse(string $ip): bool
    {
        $key = 'lockout_abuse_' . md5($ip);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'time' => time()];
        }
        
        // Reset después de 1 hora
        if (time() - $_SESSION[$key]['time'] > 3600) {
            $_SESSION[$key] = ['count' => 0, 'time' => time()];
        }
        
        // Si una IP ha causado bloqueo de más de 10 cuentas diferentes, bloquear IP
        if ($_SESSION[$key]['count'] > 10) {
            SecurityAudit::log('ACCOUNT_LOCKOUT_ABUSE', null, [
                'ip' => $ip,
                'count' => $_SESSION[$key]['count']
            ], 'CRITICAL');
            return false;
        }
        
        return true;
    }
    
    /**
     * Registra un intento de bloqueo de cuenta
     */
    public static function recordLockoutAttempt(string $ip, string $targetEmail): void
    {
        $key = 'lockout_abuse_' . md5($ip);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'time' => time(), 'targets' => []];
        }
        
        // Solo contar si es un email diferente
        if (!in_array(md5($targetEmail), $_SESSION[$key]['targets'] ?? [])) {
            $_SESSION[$key]['count']++;
            $_SESSION[$key]['targets'][] = md5($targetEmail);
        }
    }
    
    /**
     * Protección contra HTTP Parameter Pollution
     * Detecta múltiples valores para el mismo parámetro
     */
    public static function checkParameterPollution(): bool
    {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        // Buscar parámetros duplicados
        parse_str($queryString, $params);
        $rawParams = explode('&', $queryString);
        
        $paramCounts = [];
        foreach ($rawParams as $param) {
            $parts = explode('=', $param);
            $name = $parts[0] ?? '';
            if (!empty($name)) {
                $paramCounts[$name] = ($paramCounts[$name] ?? 0) + 1;
            }
        }
        
        foreach ($paramCounts as $name => $count) {
            if ($count > 1) {
                SecurityAudit::log('HTTP_PARAM_POLLUTION', null, [
                    'parameter' => $name,
                    'count' => $count
                ], 'WARNING');
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Protección contra Open Redirect
     * Valida URLs de redirección
     */
    public static function validateRedirectUrl(?string $url): string
    {
        if (empty($url)) {
            return 'index.php';
        }
        
        // Solo permitir URLs relativas o del mismo dominio
        $parsed = parse_url($url);
        
        // Si tiene scheme (http://, https://), es peligroso
        if (isset($parsed['scheme'])) {
            SecurityAudit::log('OPEN_REDIRECT_ATTEMPT', null, [
                'url' => substr($url, 0, 100)
            ], 'WARNING');
            return 'index.php';
        }
        
        // Si tiene host, es peligroso
        if (isset($parsed['host'])) {
            SecurityAudit::log('OPEN_REDIRECT_ATTEMPT', null, [
                'url' => substr($url, 0, 100)
            ], 'WARNING');
            return 'index.php';
        }
        
        // Verificar que no empiece con // (protocol-relative URL)
        if (str_starts_with($url, '//')) {
            return 'index.php';
        }
        
        // Verificar que no contenga javascript:
        if (stripos($url, 'javascript:') !== false) {
            return 'index.php';
        }
        
        // Lista blanca de páginas permitidas
        $allowedPages = [
            'index.php', 'dashboard.php', 'profile.php', 
            'register_page.php', 'logout.php'
        ];
        
        $path = $parsed['path'] ?? '';
        $baseName = basename($path);
        
        if (!in_array($baseName, $allowedPages) && !empty($baseName)) {
            // Si no está en la lista blanca, ir a index
            return 'index.php';
        }
        
        return $url;
    }
    
    /**
     * Protección contra Race Conditions en operaciones críticas
     * Implementa un lock simple basado en sesión
     */
    public static function acquireLock(string $operation, int $timeout = 5): bool
    {
        $lockKey = '_lock_' . $operation;
        $now = time();
        
        // Si hay un lock activo y no ha expirado, denegar
        if (isset($_SESSION[$lockKey]) && $_SESSION[$lockKey] > $now) {
            return false;
        }
        
        // Adquirir lock
        $_SESSION[$lockKey] = $now + $timeout;
        return true;
    }
    
    /**
     * Libera un lock
     */
    public static function releaseLock(string $operation): void
    {
        $lockKey = '_lock_' . $operation;
        unset($_SESSION[$lockKey]);
    }
    
    /**
     * Verifica integridad de formularios con Double Submit Cookie
     * Añade una capa extra sobre CSRF
     */
    public static function generateDoubleSubmitToken(): string
    {
        $token = bin2hex(random_bytes(32));
        
        // Guardar en sesión
        $_SESSION['_ds_token'] = $token;
        
        // También establecer cookie
        $cookieParams = [
            'expires' => 0,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ];
        setcookie('_ds_token', $token, $cookieParams);
        
        return $token;
    }
    
    /**
     * Valida Double Submit Token
     */
    public static function validateDoubleSubmitToken(?string $formToken): bool
    {
        $sessionToken = $_SESSION['_ds_token'] ?? null;
        $cookieToken = $_COOKIE['_ds_token'] ?? null;
        
        if (empty($sessionToken) || empty($cookieToken) || empty($formToken)) {
            return false;
        }
        
        // Los tres deben coincidir
        return hash_equals($sessionToken, $formToken) && 
               hash_equals($sessionToken, $cookieToken);
    }
    
    /**
     * Protección contra Slow HTTP Attacks
     * Verifica que la petición se completó en tiempo razonable
     */
    public static function checkRequestTiming(): bool
    {
        // Si el servidor ha estado procesando más de 30 segundos, algo está mal
        $startTime = $_SERVER['REQUEST_TIME'] ?? time();
        if (time() - $startTime > 30) {
            SecurityAudit::log('SLOW_REQUEST_DETECTED', null, [
                'duration' => time() - $startTime
            ], 'WARNING');
            return false;
        }
        return true;
    }
    
    /**
     * Protección contra ataques de Host Header Injection
     */
    public static function validateHostHeader(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        
        // Lista de hosts permitidos (configurar según tu dominio)
        $allowedHosts = [
            'localhost',
            '127.0.0.1',
            // Añadir tu dominio real aquí
        ];
        
        // Extraer solo el host (sin puerto)
        $hostOnly = explode(':', $host)[0];
        
        // En desarrollo, permitir cualquier host local
        if (in_array($hostOnly, $allowedHosts) || 
            str_ends_with($hostOnly, '.localhost') ||
            filter_var($hostOnly, FILTER_VALIDATE_IP)) {
            return true;
        }
        
        // En producción, serías más estricto
        // Por ahora, solo loggeamos hosts sospechosos
        if (preg_match('/[<>"\']/', $host)) {
            SecurityAudit::log('HOST_HEADER_INJECTION', null, [
                'host' => substr($host, 0, 100)
            ], 'CRITICAL');
            return false;
        }
        
        return true;
    }
    
    /**
     * Genera un nonce para Content Security Policy
     */
    public static function generateCspNonce(): string
    {
        if (!isset($_SESSION['_csp_nonce'])) {
            $_SESSION['_csp_nonce'] = base64_encode(random_bytes(16));
        }
        return $_SESSION['_csp_nonce'];
    }
    
    /**
     * Detecta si la petición viene de un proxy anónimo conocido
     */
    public static function detectAnonymousProxy(): bool
    {
        $headers = [
            'HTTP_VIA',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_CLIENT_IP',
            'HTTP_PROXY_CONNECTION',
        ];
        
        $proxyIndicators = 0;
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $proxyIndicators++;
            }
        }
        
        // Múltiples headers de proxy es sospechoso
        if ($proxyIndicators >= 3) {
            SecurityAudit::log('ANONYMOUS_PROXY_DETECTED', null, [
                'indicators' => $proxyIndicators
            ], 'INFO');
            return true;
        }
        
        return false;
    }
    
    /**
     * Verifica la integridad de los datos JSON recibidos
     */
    public static function validateJsonIntegrity(string $json): array
    {
        // Verificar longitud máxima
        if (strlen($json) > 1048576) { // 1MB
            return ['valid' => false, 'error' => 'JSON too large'];
        }
        
        // Intentar decodificar
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['valid' => false, 'error' => 'Invalid JSON'];
        }
        
        // Verificar profundidad máxima (prevenir ataques de recursión)
        if (self::getArrayDepth($data) > 10) {
            return ['valid' => false, 'error' => 'JSON too deep'];
        }
        
        return ['valid' => true, 'data' => $data];
    }
    
    /**
     * Calcula la profundidad de un array
     */
    private static function getArrayDepth(array $array): int
    {
        $maxDepth = 1;
        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = self::getArrayDepth($value) + 1;
                $maxDepth = max($maxDepth, $depth);
            }
        }
        return $maxDepth;
    }
    
    /**
     * Sanitiza y valida un ID numérico
     */
    public static function validateNumericId(mixed $id): ?int
    {
        if (!is_numeric($id)) {
            return null;
        }
        
        $id = (int) $id;
        
        if ($id <= 0 || $id > PHP_INT_MAX) {
            return null;
        }
        
        return $id;
    }
}
