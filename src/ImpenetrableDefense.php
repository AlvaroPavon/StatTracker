<?php
/**
 * Clase ImpenetrableDefense - Capa FINAL de seguridad
 * Cierra TODOS los vectores de ataque restantes
 * @package App
 */

namespace App;

class ImpenetrableDefense
{
    // ==================== CONFIGURACIÓN ====================
    
    // Rate limiting GLOBAL por cuenta (además de por IP)
    private const MAX_FAILED_PER_ACCOUNT = 3;
    private const ACCOUNT_LOCKOUT_DURATION = 86400; // 24 horas
    private const ACCOUNT_LOCKOUT_FILE = __DIR__ . '/../logs/.account_lockouts.json';
    
    // Proof of Work para formularios (anti-bot)
    private const POW_DIFFICULTY = 4; // Número de ceros requeridos
    private const POW_VALIDITY = 300; // 5 minutos
    
    // Global rate limiting
    private const GLOBAL_MAX_REQUESTS_PER_MINUTE = 60;
    private const GLOBAL_RATE_FILE = __DIR__ . '/../logs/.global_rate.json';
    
    // Honey accounts (cuentas trampa)
    private const HONEY_ACCOUNTS = [
        'admin@stattracker.com',
        'administrator@stattracker.com', 
        'root@stattracker.com',
        'test@stattracker.com',
        'user@stattracker.com',
        'demo@stattracker.com'
    ];
    
    // GeoIP blocking (rangos de IP sospechosos conocidos)
    private const BLOCKED_IP_RANGES = [
        // TOR exit nodes conocidos (ejemplo)
        '185.220.100.0/24',
        '185.220.101.0/24',
        // Añadir más rangos según sea necesario
    ];
    
    // Request signing key
    private const REQUEST_SIGNING_KEY = 'ST_REQ_SIGN_2025_ULTRA_SECURE';

    // ==================== ACCOUNT LOCKOUT (Por cuenta, no por IP) ====================

    /**
     * Verifica si una cuenta está bloqueada (independiente de IP)
     */
    public static function isAccountLocked(string $email): bool
    {
        $lockouts = self::getAccountLockouts();
        $emailHash = hash('sha256', strtolower(trim($email)));
        
        if (isset($lockouts[$emailHash])) {
            if ($lockouts[$emailHash]['until'] > time()) {
                return true;
            }
            // Expiró, eliminar
            unset($lockouts[$emailHash]);
            self::saveAccountLockouts($lockouts);
        }
        
        return false;
    }

    /**
     * Registra intento fallido por cuenta
     */
    public static function recordAccountFailure(string $email): array
    {
        $lockouts = self::getAccountLockouts();
        $emailHash = hash('sha256', strtolower(trim($email)));
        
        if (!isset($lockouts[$emailHash])) {
            $lockouts[$emailHash] = [
                'failures' => 0,
                'first_failure' => time(),
                'until' => 0
            ];
        }
        
        $lockouts[$emailHash]['failures']++;
        $lockouts[$emailHash]['last_failure'] = time();
        
        // Si excede el límite, bloquear cuenta
        if ($lockouts[$emailHash]['failures'] >= self::MAX_FAILED_PER_ACCOUNT) {
            $lockouts[$emailHash]['until'] = time() + self::ACCOUNT_LOCKOUT_DURATION;
            
            SecurityAudit::log('ACCOUNT_LOCKED', null, [
                'email_hash' => substr($emailHash, 0, 16),
                'failures' => $lockouts[$emailHash]['failures'],
                'duration' => self::ACCOUNT_LOCKOUT_DURATION
            ], 'CRITICAL');
        }
        
        self::saveAccountLockouts($lockouts);
        
        return [
            'locked' => $lockouts[$emailHash]['until'] > time(),
            'failures' => $lockouts[$emailHash]['failures'],
            'remaining' => max(0, self::MAX_FAILED_PER_ACCOUNT - $lockouts[$emailHash]['failures'])
        ];
    }

    /**
     * Resetea intentos fallidos de una cuenta
     */
    public static function resetAccountFailures(string $email): void
    {
        $lockouts = self::getAccountLockouts();
        $emailHash = hash('sha256', strtolower(trim($email)));
        
        if (isset($lockouts[$emailHash])) {
            unset($lockouts[$emailHash]);
            self::saveAccountLockouts($lockouts);
        }
    }

    private static function getAccountLockouts(): array
    {
        if (!file_exists(self::ACCOUNT_LOCKOUT_FILE)) {
            return [];
        }
        $data = json_decode(file_get_contents(self::ACCOUNT_LOCKOUT_FILE), true);
        return is_array($data) ? $data : [];
    }

    private static function saveAccountLockouts(array $data): void
    {
        $dir = dirname(self::ACCOUNT_LOCKOUT_FILE);
        if (!is_dir($dir)) mkdir($dir, 0750, true);
        file_put_contents(self::ACCOUNT_LOCKOUT_FILE, json_encode($data), LOCK_EX);
    }

    // ==================== PROOF OF WORK (Anti-bot sin CAPTCHA) ====================

    /**
     * Genera un desafío Proof of Work
     */
    public static function generatePoWChallenge(): array
    {
        $challenge = bin2hex(random_bytes(16));
        $timestamp = time();
        $signature = hash_hmac('sha256', $challenge . $timestamp, self::REQUEST_SIGNING_KEY);
        
        return [
            'challenge' => $challenge,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'difficulty' => self::POW_DIFFICULTY
        ];
    }

    /**
     * Verifica la solución del Proof of Work
     */
    public static function verifyPoWSolution(string $challenge, int $timestamp, string $signature, int $nonce): bool
    {
        // Verificar firma del challenge
        $expectedSig = hash_hmac('sha256', $challenge . $timestamp, self::REQUEST_SIGNING_KEY);
        if (!hash_equals($expectedSig, $signature)) {
            SecurityAudit::log('POW_INVALID_SIGNATURE', null, [], 'WARNING');
            return false;
        }
        
        // Verificar tiempo (no más de 5 minutos)
        if (time() - $timestamp > self::POW_VALIDITY) {
            SecurityAudit::log('POW_EXPIRED', null, [], 'WARNING');
            return false;
        }
        
        // Verificar solución
        $hash = hash('sha256', $challenge . $nonce);
        $requiredPrefix = str_repeat('0', self::POW_DIFFICULTY);
        
        if (substr($hash, 0, self::POW_DIFFICULTY) !== $requiredPrefix) {
            SecurityAudit::log('POW_INVALID_SOLUTION', null, [], 'WARNING');
            return false;
        }
        
        return true;
    }

    // ==================== HONEY ACCOUNTS (Detección de atacantes) ====================

    /**
     * Verifica si es un intento de acceso a cuenta trampa
     */
    public static function checkHoneyAccount(string $email): bool
    {
        $email = strtolower(trim($email));
        
        if (in_array($email, self::HONEY_ACCOUNTS)) {
            // ¡Atacante detectado! Bloquear IP inmediatamente
            $ip = self::getClientIp();
            
            SecurityAudit::log('HONEY_ACCOUNT_TRIGGERED', null, [
                'email' => $email,
                'ip' => $ip
            ], 'CRITICAL');
            
            // Bloquear IP por 24 horas
            SecurityFirewall::blockIp($ip, ['HONEY_ACCOUNT_ACCESS']);
            
            return true;
        }
        
        return false;
    }

    // ==================== GLOBAL RATE LIMITING ====================

    /**
     * Control de tasa global (todas las IPs combinadas)
     */
    public static function checkGlobalRateLimit(): bool
    {
        $data = self::getGlobalRateData();
        $currentMinute = floor(time() / 60);
        
        if (!isset($data['minute']) || $data['minute'] !== $currentMinute) {
            $data = ['minute' => $currentMinute, 'count' => 0];
        }
        
        $data['count']++;
        self::saveGlobalRateData($data);
        
        if ($data['count'] > self::GLOBAL_MAX_REQUESTS_PER_MINUTE * 100) {
            // Bajo ataque DDoS masivo
            SecurityAudit::log('DDOS_DETECTED', null, [
                'requests_per_minute' => $data['count']
            ], 'CRITICAL');
            return false;
        }
        
        return true;
    }

    private static function getGlobalRateData(): array
    {
        if (!file_exists(self::GLOBAL_RATE_FILE)) {
            return ['minute' => 0, 'count' => 0];
        }
        $data = json_decode(file_get_contents(self::GLOBAL_RATE_FILE), true);
        return is_array($data) ? $data : ['minute' => 0, 'count' => 0];
    }

    private static function saveGlobalRateData(array $data): void
    {
        $dir = dirname(self::GLOBAL_RATE_FILE);
        if (!is_dir($dir)) mkdir($dir, 0750, true);
        file_put_contents(self::GLOBAL_RATE_FILE, json_encode($data), LOCK_EX);
    }

    // ==================== IP RANGE BLOCKING ====================

    /**
     * Verifica si la IP está en un rango bloqueado
     */
    public static function isIpInBlockedRange(string $ip): bool
    {
        foreach (self::BLOCKED_IP_RANGES as $range) {
            if (self::ipInRange($ip, $range)) {
                SecurityAudit::log('BLOCKED_IP_RANGE', null, [
                    'ip' => $ip,
                    'range' => $range
                ], 'WARNING');
                return true;
            }
        }
        return false;
    }

    private static function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }
        
        [$subnet, $bits] = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - (int)$bits);
        
        return ($ip & $mask) === ($subnet & $mask);
    }

    // ==================== REQUEST SIGNING (Anti-replay) ====================

    /**
     * Genera una firma para el request
     */
    public static function signRequest(string $action): array
    {
        $timestamp = time();
        $nonce = bin2hex(random_bytes(16));
        $data = $action . $timestamp . $nonce . session_id();
        $signature = hash_hmac('sha256', $data, self::REQUEST_SIGNING_KEY);
        
        return [
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'signature' => $signature
        ];
    }

    /**
     * Verifica la firma del request
     */
    public static function verifyRequestSignature(string $action, int $timestamp, string $nonce, string $signature): bool
    {
        // Verificar tiempo (máximo 5 minutos)
        if (abs(time() - $timestamp) > 300) {
            return false;
        }
        
        // Verificar nonce no usado
        if (self::isNonceUsed($nonce)) {
            SecurityAudit::log('REPLAY_ATTACK_DETECTED', null, [
                'nonce' => substr($nonce, 0, 8)
            ], 'CRITICAL');
            return false;
        }
        
        // Verificar firma
        $data = $action . $timestamp . $nonce . session_id();
        $expectedSig = hash_hmac('sha256', $data, self::REQUEST_SIGNING_KEY);
        
        if (!hash_equals($expectedSig, $signature)) {
            return false;
        }
        
        // Marcar nonce como usado
        self::markNonceUsed($nonce, $timestamp);
        
        return true;
    }

    private static function isNonceUsed(string $nonce): bool
    {
        $file = __DIR__ . '/../logs/.used_nonces.json';
        if (!file_exists($file)) return false;
        
        $data = json_decode(file_get_contents($file), true);
        return isset($data[$nonce]);
    }

    private static function markNonceUsed(string $nonce, int $timestamp): void
    {
        $file = __DIR__ . '/../logs/.used_nonces.json';
        $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        
        // Limpiar nonces antiguos (más de 10 minutos)
        $data = array_filter($data, fn($ts) => time() - $ts < 600);
        
        $data[$nonce] = $timestamp;
        
        $dir = dirname($file);
        if (!is_dir($dir)) mkdir($dir, 0750, true);
        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    // ==================== ANOMALY DETECTION ====================

    /**
     * Detecta comportamiento anómalo
     */
    public static function detectAnomaly(): array
    {
        $anomalies = [];
        
        // 1. Velocidad de escritura imposible (bot)
        if (isset($_POST['_form_start_time'])) {
            $startTime = (int)$_POST['_form_start_time'];
            $elapsed = time() - $startTime;
            
            if ($elapsed < 2) {
                $anomalies[] = 'IMPOSSIBLE_TYPING_SPEED';
            }
        }
        
        // 2. Múltiples sesiones simultáneas
        $ip = self::getClientIp();
        $sessionsFromIp = self::countSessionsFromIp($ip);
        if ($sessionsFromIp > 5) {
            $anomalies[] = 'TOO_MANY_SESSIONS';
        }
        
        // 3. Patrones de navegación sospechosos
        if (!isset($_SESSION['_navigation'])) {
            $_SESSION['_navigation'] = [];
        }
        
        $_SESSION['_navigation'][] = [
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'time' => time()
        ];
        
        // Mantener solo últimas 50 navegaciones
        $_SESSION['_navigation'] = array_slice($_SESSION['_navigation'], -50);
        
        // Detectar navegación repetitiva (scraping)
        $uris = array_column($_SESSION['_navigation'], 'uri');
        $uniqueUris = array_unique($uris);
        if (count($uris) > 10 && count($uniqueUris) < 3) {
            $anomalies[] = 'REPETITIVE_NAVIGATION';
        }
        
        // 4. Headers inconsistentes
        if (self::hasInconsistentHeaders()) {
            $anomalies[] = 'INCONSISTENT_HEADERS';
        }
        
        if (!empty($anomalies)) {
            SecurityAudit::log('ANOMALY_DETECTED', $_SESSION['user_id'] ?? null, [
                'anomalies' => $anomalies,
                'ip' => $ip
            ], 'WARNING');
        }
        
        return $anomalies;
    }

    private static function countSessionsFromIp(string $ip): int
    {
        // En producción, esto debería usar Redis o base de datos
        // Por ahora, retornamos un valor seguro
        return 1;
    }

    private static function hasInconsistentHeaders(): bool
    {
        // Navegador dice ser Chrome pero no tiene headers típicos de Chrome
        $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        
        if (strpos($ua, 'chrome') !== false) {
            // Chrome siempre envía estos headers
            if (empty($_SERVER['HTTP_SEC_CH_UA']) && 
                empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                return true;
            }
        }
        
        // Firefox siempre envía Accept-Language
        if (strpos($ua, 'firefox') !== false) {
            if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                return true;
            }
        }
        
        return false;
    }

    // ==================== BEHAVIORAL ANALYSIS ====================

    /**
     * Analiza comportamiento del usuario
     */
    public static function analyzeUserBehavior(): array
    {
        $score = 100; // Puntuación de confianza (100 = totalmente confiable)
        $reasons = [];
        
        // En modo desarrollo/pruebas, ser menos estricto
        $isDevelopment = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost']) ||
                        (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);
        
        // 1. User-Agent score
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (strlen($ua) < 20) {
            $penalty = $isDevelopment ? 10 : 30;
            $score -= $penalty;
            $reasons[] = 'short_user_agent';
        }
        
        // 2. Referer check (solo para POST y no en desarrollo)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isDevelopment) {
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            if (empty($referer)) {
                $score -= 20;
                $reasons[] = 'no_referer';
            }
        }
        
        // 3. Accept headers
        if (empty($_SERVER['HTTP_ACCEPT']) && !$isDevelopment) {
            $score -= 15;
            $reasons[] = 'no_accept_header';
        }
        
        // 4. Cookie support (menos penalización en primera petición)
        if (empty($_COOKIE) && !$isDevelopment) {
            $score -= 5; // Reducido de 10 a 5
            $reasons[] = 'no_cookies';
        }
        
        // 5. Session consistency
        if (isset($_SESSION['_behavior_first_seen'])) {
            $age = time() - $_SESSION['_behavior_first_seen'];
            if ($age > 3600) {
                $score += 10; // Usuario establecido
            }
        } else {
            $_SESSION['_behavior_first_seen'] = time();
        }
        
        $result = [
            'score' => max(0, min(100, $score)),
            'reasons' => $reasons,
            'trusted' => $score >= 50
        ];
        
        if ($score < 30) {
            SecurityAudit::log('LOW_TRUST_SCORE', null, $result, 'WARNING');
        }
        
        return $result;
    }

    // ==================== UTILS ====================

    private static function getClientIp(): string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = filter_var($_SERVER[$header], FILTER_VALIDATE_IP);
                if ($ip !== false) return $ip;
            }
        }
        return '0.0.0.0';
    }

    // ==================== FULL DEFENSE CHECK ====================

    /**
     * Ejecuta TODAS las verificaciones de defensa
     */
    public static function fullDefenseCheck(): array
    {
        $result = [
            'allowed' => true,
            'blocks' => []
        ];
        
        $ip = self::getClientIp();
        
        // 1. IP en rango bloqueado
        if (self::isIpInBlockedRange($ip)) {
            $result['allowed'] = false;
            $result['blocks'][] = 'IP_RANGE_BLOCKED';
        }
        
        // 2. Rate limit global
        if (!self::checkGlobalRateLimit()) {
            $result['allowed'] = false;
            $result['blocks'][] = 'GLOBAL_RATE_EXCEEDED';
        }
        
        // 3. Detección de anomalías
        $anomalies = self::detectAnomaly();
        if (count($anomalies) >= 2) {
            $result['allowed'] = false;
            $result['blocks'] = array_merge($result['blocks'], $anomalies);
        }
        
        // 4. Análisis de comportamiento
        $behavior = self::analyzeUserBehavior();
        if (!$behavior['trusted']) {
            // No bloqueamos, pero aumentamos escrutinio
            $result['high_risk'] = true;
        }
        
        return $result;
    }
}
