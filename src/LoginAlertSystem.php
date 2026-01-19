<?php
/**
 * Clase LoginAlertSystem - Sistema de alertas de login sospechoso
 * Detecta y registra actividad inusual en el inicio de sesión
 * @package App
 */

namespace App;

class LoginAlertSystem
{
    // Configuración
    private const KNOWN_DEVICES_FILE = __DIR__ . '/../logs/known_devices.json';
    private const LOGIN_HISTORY_FILE = __DIR__ . '/../logs/login_history.json';
    private const MAX_HISTORY_ENTRIES = 1000;
    private const SUSPICIOUS_THRESHOLD = 3; // Número de flags para considerar sospechoso
    
    /**
     * Analiza un intento de login y detecta actividad sospechosa
     * @param int $userId ID del usuario
     * @param string $email Email del usuario
     * @return array ['suspicious' => bool, 'reasons' => array, 'score' => int]
     */
    public static function analyzeLogin(int $userId, string $email): array
    {
        $flags = [];
        $score = 0;
        
        $currentData = self::getCurrentLoginData();
        $previousLogins = self::getLoginHistory($userId);
        $knownDevices = self::getKnownDevices($userId);
        
        // 1. Verificar si es un dispositivo nuevo
        $deviceFingerprint = self::generateDeviceFingerprint();
        if (!in_array($deviceFingerprint, $knownDevices)) {
            $flags[] = 'new_device';
            $score += 2;
        }
        
        // 2. Verificar cambio de User-Agent significativo
        if (!empty($previousLogins)) {
            $lastLogin = $previousLogins[0];
            if (isset($lastLogin['user_agent']) && 
                self::isSignificantUaChange($lastLogin['user_agent'], $currentData['user_agent'])) {
                $flags[] = 'user_agent_changed';
                $score += 1;
            }
        }
        
        // 3. Verificar IP en rango diferente (país/ISP diferente)
        if (!empty($previousLogins)) {
            $lastIp = $previousLogins[0]['ip'] ?? null;
            if ($lastIp && self::isDifferentIpRange($lastIp, $currentData['ip'])) {
                $flags[] = 'different_ip_range';
                $score += 2;
            }
        }
        
        // 4. Verificar hora inusual (fuera del horario habitual del usuario)
        if (self::isUnusualTime($previousLogins)) {
            $flags[] = 'unusual_time';
            $score += 1;
        }
        
        // 5. Verificar múltiples IPs en poco tiempo
        if (self::hasMultipleRecentIps($previousLogins, $currentData['ip'])) {
            $flags[] = 'multiple_ips_recently';
            $score += 2;
        }
        
        // 6. Verificar si es primera vez desde este país (si tenemos geolocalización)
        $country = self::getCountryFromIp($currentData['ip']);
        if ($country && self::isNewCountry($previousLogins, $country)) {
            $flags[] = 'new_country';
            $score += 3;
        }
        
        // 7. Verificar si hay intentos fallidos recientes
        if (self::hasRecentFailedAttempts($email)) {
            $flags[] = 'recent_failed_attempts';
            $score += 1;
        }
        
        // Registrar el login actual
        self::recordLogin($userId, $currentData, !empty($flags));
        
        // Añadir dispositivo a conocidos si no es muy sospechoso
        if ($score < self::SUSPICIOUS_THRESHOLD) {
            self::addKnownDevice($userId, $deviceFingerprint);
        }
        
        $suspicious = $score >= self::SUSPICIOUS_THRESHOLD;
        
        // Registrar evento de seguridad si es sospechoso
        if ($suspicious) {
            SecurityAudit::log('SUSPICIOUS_LOGIN', $userId, [
                'flags' => $flags,
                'score' => $score,
                'ip' => $currentData['ip'],
                'user_agent' => substr($currentData['user_agent'], 0, 100)
            ], 'WARNING');
        }
        
        return [
            'suspicious' => $suspicious,
            'reasons' => $flags,
            'score' => $score,
            'is_new_device' => in_array('new_device', $flags),
            'is_new_location' => in_array('different_ip_range', $flags) || in_array('new_country', $flags)
        ];
    }
    
    /**
     * Obtiene datos del login actual
     */
    private static function getCurrentLoginData(): array
    {
        return [
            'ip' => $_SERVER['HTTP_CF_CONNECTING_IP'] 
                    ?? $_SERVER['HTTP_X_REAL_IP'] 
                    ?? $_SERVER['REMOTE_ADDR'] 
                    ?? '0.0.0.0',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'timestamp' => time(),
            'hour' => (int) date('G'), // Hora del día (0-23)
            'day_of_week' => (int) date('N'), // Día de la semana (1-7)
        ];
    }
    
    /**
     * Genera huella digital del dispositivo
     */
    private static function generateDeviceFingerprint(): string
    {
        $data = [
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
        ];
        
        return hash('sha256', implode('|', $data));
    }
    
    /**
     * Verifica si hay un cambio significativo en User-Agent
     */
    private static function isSignificantUaChange(string $oldUa, string $newUa): bool
    {
        // Extraer sistema operativo y navegador básico
        $oldOs = self::extractOs($oldUa);
        $newOs = self::extractOs($newUa);
        
        $oldBrowser = self::extractBrowser($oldUa);
        $newBrowser = self::extractBrowser($newUa);
        
        // Cambio de SO es significativo
        if ($oldOs !== $newOs && $oldOs !== 'unknown' && $newOs !== 'unknown') {
            return true;
        }
        
        // Cambio de navegador base es significativo
        if ($oldBrowser !== $newBrowser && $oldBrowser !== 'unknown' && $newBrowser !== 'unknown') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Extrae el sistema operativo del User-Agent
     */
    private static function extractOs(string $ua): string
    {
        $ua = strtolower($ua);
        
        if (strpos($ua, 'windows') !== false) return 'windows';
        if (strpos($ua, 'mac os') !== false || strpos($ua, 'macintosh') !== false) return 'macos';
        if (strpos($ua, 'linux') !== false) return 'linux';
        if (strpos($ua, 'android') !== false) return 'android';
        if (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false) return 'ios';
        
        return 'unknown';
    }
    
    /**
     * Extrae el navegador del User-Agent
     */
    private static function extractBrowser(string $ua): string
    {
        $ua = strtolower($ua);
        
        if (strpos($ua, 'edg') !== false) return 'edge';
        if (strpos($ua, 'chrome') !== false) return 'chrome';
        if (strpos($ua, 'firefox') !== false) return 'firefox';
        if (strpos($ua, 'safari') !== false) return 'safari';
        if (strpos($ua, 'opera') !== false || strpos($ua, 'opr') !== false) return 'opera';
        
        return 'unknown';
    }
    
    /**
     * Verifica si las IPs están en rangos diferentes
     */
    private static function isDifferentIpRange(string $ip1, string $ip2): bool
    {
        // Comparar los primeros dos octetos (clase B)
        $parts1 = explode('.', $ip1);
        $parts2 = explode('.', $ip2);
        
        if (count($parts1) < 2 || count($parts2) < 2) {
            return false;
        }
        
        return $parts1[0] !== $parts2[0] || $parts1[1] !== $parts2[1];
    }
    
    /**
     * Verifica si es una hora inusual basándose en el historial
     */
    private static function isUnusualTime(array $previousLogins): bool
    {
        if (count($previousLogins) < 5) {
            return false; // No hay suficiente historial
        }
        
        $currentHour = (int) date('G');
        $hourCounts = array_fill(0, 24, 0);
        
        foreach ($previousLogins as $login) {
            if (isset($login['hour'])) {
                $hourCounts[$login['hour']]++;
            }
        }
        
        // Si nunca se ha logueado a esta hora y hay al menos 10 logins anteriores
        if (count($previousLogins) >= 10 && $hourCounts[$currentHour] === 0) {
            // Verificar si es una hora muy diferente a las habituales
            $totalLogins = array_sum($hourCounts);
            $avgPerHour = $totalLogins / 24;
            
            // Si esta hora tiene 0 y la media es mayor a 0.5, es inusual
            if ($avgPerHour > 0.5) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Verifica si hay múltiples IPs en las últimas horas
     */
    private static function hasMultipleRecentIps(array $previousLogins, string $currentIp): bool
    {
        $recentThreshold = time() - (2 * 3600); // Últimas 2 horas
        $recentIps = [$currentIp];
        
        foreach ($previousLogins as $login) {
            if (isset($login['timestamp']) && $login['timestamp'] >= $recentThreshold) {
                if (isset($login['ip']) && !in_array($login['ip'], $recentIps)) {
                    $recentIps[] = $login['ip'];
                }
            }
        }
        
        return count($recentIps) >= 3; // 3 o más IPs diferentes en 2 horas
    }
    
    /**
     * Obtiene el país de una IP (simplificado)
     * En producción, usar un servicio de geolocalización real
     */
    private static function getCountryFromIp(string $ip): ?string
    {
        // Verificar IPs privadas
        if (self::isPrivateIp($ip)) {
            return 'LOCAL';
        }
        
        // Aquí se podría integrar con un servicio de geolocalización
        // Por ahora, retornamos null (no disponible)
        return null;
    }
    
    /**
     * Verifica si es una IP privada
     */
    private static function isPrivateIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, 
            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
    
    /**
     * Verifica si es un país nuevo para el usuario
     */
    private static function isNewCountry(array $previousLogins, string $country): bool
    {
        foreach ($previousLogins as $login) {
            if (isset($login['country']) && $login['country'] === $country) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Verifica si hay intentos fallidos recientes
     */
    private static function hasRecentFailedAttempts(string $email): bool
    {
        $key = 'failed_login_' . md5($email);
        $attempts = $_SESSION[$key] ?? 0;
        return $attempts > 0;
    }
    
    /**
     * Obtiene el historial de logins de un usuario
     */
    private static function getLoginHistory(int $userId): array
    {
        $history = self::loadLoginHistory();
        return $history[$userId] ?? [];
    }
    
    /**
     * Registra un login en el historial
     */
    private static function recordLogin(int $userId, array $data, bool $suspicious): void
    {
        $history = self::loadLoginHistory();
        
        if (!isset($history[$userId])) {
            $history[$userId] = [];
        }
        
        // Añadir al principio
        array_unshift($history[$userId], array_merge($data, [
            'suspicious' => $suspicious,
            'country' => self::getCountryFromIp($data['ip'])
        ]));
        
        // Limitar historial por usuario
        $history[$userId] = array_slice($history[$userId], 0, 50);
        
        self::saveLoginHistory($history);
    }
    
    /**
     * Carga el historial de logins
     */
    private static function loadLoginHistory(): array
    {
        if (!file_exists(self::LOGIN_HISTORY_FILE)) {
            return [];
        }
        
        $content = @file_get_contents(self::LOGIN_HISTORY_FILE);
        if (!$content) {
            return [];
        }
        
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }
    
    /**
     * Guarda el historial de logins
     */
    private static function saveLoginHistory(array $history): void
    {
        $dir = dirname(self::LOGIN_HISTORY_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        
        file_put_contents(
            self::LOGIN_HISTORY_FILE,
            json_encode($history, JSON_PRETTY_PRINT),
            LOCK_EX
        );
    }
    
    /**
     * Obtiene los dispositivos conocidos de un usuario
     */
    private static function getKnownDevices(int $userId): array
    {
        $devices = self::loadKnownDevices();
        return $devices[$userId] ?? [];
    }
    
    /**
     * Añade un dispositivo a la lista de conocidos
     */
    private static function addKnownDevice(int $userId, string $fingerprint): void
    {
        $devices = self::loadKnownDevices();
        
        if (!isset($devices[$userId])) {
            $devices[$userId] = [];
        }
        
        if (!in_array($fingerprint, $devices[$userId])) {
            $devices[$userId][] = $fingerprint;
            
            // Limitar a 10 dispositivos por usuario
            $devices[$userId] = array_slice($devices[$userId], -10);
            
            self::saveKnownDevices($devices);
        }
    }
    
    /**
     * Carga dispositivos conocidos
     */
    private static function loadKnownDevices(): array
    {
        if (!file_exists(self::KNOWN_DEVICES_FILE)) {
            return [];
        }
        
        $content = @file_get_contents(self::KNOWN_DEVICES_FILE);
        if (!$content) {
            return [];
        }
        
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }
    
    /**
     * Guarda dispositivos conocidos
     */
    private static function saveKnownDevices(array $devices): void
    {
        $dir = dirname(self::KNOWN_DEVICES_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        
        file_put_contents(
            self::KNOWN_DEVICES_FILE,
            json_encode($devices, JSON_PRETTY_PRINT),
            LOCK_EX
        );
    }
    
    /**
     * Genera mensaje de alerta para el usuario
     */
    public static function generateAlertMessage(array $analysis): ?string
    {
        if (!$analysis['suspicious']) {
            return null;
        }
        
        $messages = [];
        
        if (in_array('new_device', $analysis['reasons'])) {
            $messages[] = "nuevo dispositivo detectado";
        }
        
        if (in_array('different_ip_range', $analysis['reasons']) || 
            in_array('new_country', $analysis['reasons'])) {
            $messages[] = "ubicación diferente a la habitual";
        }
        
        if (in_array('unusual_time', $analysis['reasons'])) {
            $messages[] = "hora de acceso inusual";
        }
        
        if (in_array('multiple_ips_recently', $analysis['reasons'])) {
            $messages[] = "múltiples ubicaciones detectadas recientemente";
        }
        
        if (empty($messages)) {
            return null;
        }
        
        return "⚠️ Alerta de seguridad: " . implode(', ', $messages) . 
               ". Si no reconoces esta actividad, cambia tu contraseña inmediatamente.";
    }
    
    /**
     * Limpia registros antiguos (para cron job)
     */
    public static function cleanup(int $olderThanDays = 90): void
    {
        $cutoff = time() - ($olderThanDays * 86400);
        
        // Limpiar historial de logins
        $history = self::loadLoginHistory();
        foreach ($history as $userId => &$logins) {
            $logins = array_filter($logins, function($login) use ($cutoff) {
                return ($login['timestamp'] ?? 0) >= $cutoff;
            });
        }
        self::saveLoginHistory($history);
    }
}
