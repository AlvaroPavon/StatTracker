<?php
/**
 * Clase SecurityAudit - Sistema de Auditoría y Logging de Seguridad
 * Registra eventos de seguridad importantes para detección de amenazas
 * @package App
 */

namespace App;

class SecurityAudit
{
    private const LOG_DIR = __DIR__ . '/../logs';
    private const SECURITY_LOG = 'security.log';
    private const MAX_LOG_SIZE = 10485760; // 10MB
    private const MAX_LOG_FILES = 5;
    
    // Tipos de eventos de seguridad
    public const EVENT_LOGIN_SUCCESS = 'LOGIN_SUCCESS';
    public const EVENT_LOGIN_FAILED = 'LOGIN_FAILED';
    public const EVENT_LOGIN_BLOCKED = 'LOGIN_BLOCKED';
    public const EVENT_LOGOUT = 'LOGOUT';
    public const EVENT_REGISTER = 'REGISTER';
    public const EVENT_PASSWORD_CHANGE = 'PASSWORD_CHANGE';
    public const EVENT_CSRF_INVALID = 'CSRF_INVALID';
    public const EVENT_SESSION_HIJACK = 'SESSION_HIJACK_ATTEMPT';
    public const EVENT_SQL_INJECTION = 'SQL_INJECTION_ATTEMPT';
    public const EVENT_XSS_ATTEMPT = 'XSS_ATTEMPT';
    public const EVENT_FILE_UPLOAD = 'FILE_UPLOAD';
    public const EVENT_FILE_UPLOAD_BLOCKED = 'FILE_UPLOAD_BLOCKED';
    public const EVENT_ACCESS_DENIED = 'ACCESS_DENIED';
    public const EVENT_RATE_LIMIT = 'RATE_LIMIT_EXCEEDED';
    public const EVENT_SUSPICIOUS_ACTIVITY = 'SUSPICIOUS_ACTIVITY';

    /**
     * Registra un evento de seguridad
     */
    public static function log(
        string $eventType,
        ?int $userId = null,
        array $details = [],
        string $severity = 'INFO'
    ): bool {
        try {
            self::ensureLogDirectory();
            self::rotateLogsIfNeeded();

            $logEntry = self::formatLogEntry($eventType, $userId, $details, $severity);
            $logFile = self::LOG_DIR . '/' . self::SECURITY_LOG;
            
            return file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX) !== false;
        } catch (\Exception $e) {
            error_log("SecurityAudit error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra un intento de login exitoso
     */
    public static function logLoginSuccess(int $userId, string $email): void
    {
        self::log(self::EVENT_LOGIN_SUCCESS, $userId, [
            'email' => self::maskEmail($email),
            'ip' => self::getClientIp(),
            'user_agent' => self::getUserAgent()
        ]);
    }

    /**
     * Registra un intento de login fallido
     */
    public static function logLoginFailed(string $email, string $reason = ''): void
    {
        self::log(self::EVENT_LOGIN_FAILED, null, [
            'email' => self::maskEmail($email),
            'ip' => self::getClientIp(),
            'user_agent' => self::getUserAgent(),
            'reason' => $reason
        ], 'WARNING');
    }

    /**
     * Registra un bloqueo por rate limiting
     */
    public static function logLoginBlocked(string $email): void
    {
        self::log(self::EVENT_LOGIN_BLOCKED, null, [
            'email' => self::maskEmail($email),
            'ip' => self::getClientIp(),
            'user_agent' => self::getUserAgent()
        ], 'WARNING');
    }

    /**
     * Registra un intento de CSRF inválido
     */
    public static function logCsrfInvalid(?int $userId = null): void
    {
        self::log(self::EVENT_CSRF_INVALID, $userId, [
            'ip' => self::getClientIp(),
            'user_agent' => self::getUserAgent(),
            'referer' => $_SERVER['HTTP_REFERER'] ?? 'none',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
        ], 'WARNING');
    }

    /**
     * Registra un posible intento de secuestro de sesión
     */
    public static function logSessionHijackAttempt(?int $userId = null): void
    {
        self::log(self::EVENT_SESSION_HIJACK, $userId, [
            'ip' => self::getClientIp(),
            'user_agent' => self::getUserAgent(),
            'session_id_hash' => md5(session_id())
        ], 'CRITICAL');
    }

    /**
     * Registra actividad sospechosa genérica
     */
    public static function logSuspiciousActivity(string $description, ?int $userId = null): void
    {
        self::log(self::EVENT_SUSPICIOUS_ACTIVITY, $userId, [
            'description' => $description,
            'ip' => self::getClientIp(),
            'user_agent' => self::getUserAgent(),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
        ], 'WARNING');
    }

    /**
     * Verifica patrones sospechosos en la entrada
     */
    public static function detectSuspiciousInput(string $input): array
    {
        $threats = [];
        
        // Patrones de SQL Injection
        $sqlPatterns = [
            '/\bunion\b.*\bselect\b/i',
            '/\bselect\b.*\bfrom\b/i',
            '/\binsert\b.*\binto\b/i',
            '/\bdelete\b.*\bfrom\b/i',
            '/\bdrop\b.*\btable\b/i',
            '/\bexec\b.*\(/i',
            '/--/',
            '/\/\*.*\*\//'
        ];
        
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $threats[] = 'SQL_INJECTION';
                break;
            }
        }
        
        // Patrones de XSS
        $xssPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/\beval\s*\(/i'
        ];
        
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $threats[] = 'XSS';
                break;
            }
        }
        
        // Patrones de Path Traversal
        if (preg_match('/\.\.[\/\\]/', $input)) {
            $threats[] = 'PATH_TRAVERSAL';
        }
        
        // Patrones de Command Injection
        $cmdPatterns = ['/[;&|`$]/', '/\|\|/', '/&&/'];
        foreach ($cmdPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $threats[] = 'COMMAND_INJECTION';
                break;
            }
        }
        
        return $threats;
    }

    /**
     * Analiza y registra entrada sospechosa
     */
    public static function auditInput(array $input, ?int $userId = null): void
    {
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $threats = self::detectSuspiciousInput($value);
                if (!empty($threats)) {
                    self::log(self::EVENT_SUSPICIOUS_ACTIVITY, $userId, [
                        'field' => $key,
                        'threats' => implode(',', $threats),
                        'ip' => self::getClientIp(),
                        'value_preview' => substr($value, 0, 100)
                    ], 'CRITICAL');
                }
            }
        }
    }

    /**
     * Obtiene eventos recientes del log
     */
    public static function getRecentEvents(int $limit = 100): array
    {
        $logFile = self::LOG_DIR . '/' . self::SECURITY_LOG;
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_slice($lines, -$limit);
        
        $events = [];
        foreach ($lines as $line) {
            $decoded = json_decode($line, true);
            if ($decoded) {
                $events[] = $decoded;
            }
        }
        
        return array_reverse($events);
    }

    /**
     * Cuenta eventos por tipo en las últimas N horas
     */
    public static function countEventsByType(string $eventType, int $hours = 24): int
    {
        $events = self::getRecentEvents(1000);
        $cutoff = strtotime("-{$hours} hours");
        $count = 0;
        
        foreach ($events as $event) {
            if ($event['event'] === $eventType) {
                $eventTime = strtotime($event['timestamp']);
                if ($eventTime >= $cutoff) {
                    $count++;
                }
            }
        }
        
        return $count;
    }

    // ==================== Helpers privados ====================

    private static function ensureLogDirectory(): void
    {
        if (!is_dir(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0750, true);
            
            // Crear .htaccess para proteger el directorio
            file_put_contents(
                self::LOG_DIR . '/.htaccess',
                "Order deny,allow\nDeny from all"
            );
        }
    }

    private static function rotateLogsIfNeeded(): void
    {
        $logFile = self::LOG_DIR . '/' . self::SECURITY_LOG;
        
        if (file_exists($logFile) && filesize($logFile) > self::MAX_LOG_SIZE) {
            // Rotar archivos existentes
            for ($i = self::MAX_LOG_FILES - 1; $i >= 1; $i--) {
                $oldFile = $logFile . '.' . $i;
                $newFile = $logFile . '.' . ($i + 1);
                if (file_exists($oldFile)) {
                    if ($i + 1 >= self::MAX_LOG_FILES) {
                        unlink($oldFile);
                    } else {
                        rename($oldFile, $newFile);
                    }
                }
            }
            rename($logFile, $logFile . '.1');
        }
    }

    private static function formatLogEntry(
        string $eventType,
        ?int $userId,
        array $details,
        string $severity
    ): string {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'severity' => $severity,
            'event' => $eventType,
            'user_id' => $userId,
            'ip' => self::getClientIp(),
            'details' => $details
        ];
        
        return json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n";
    }

    private static function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return 'unknown';
    }

    private static function getUserAgent(): string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return substr($ua, 0, 255);
    }

    private static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***@***';
        }
        
        $local = $parts[0];
        $domain = $parts[1];
        
        $maskedLocal = substr($local, 0, 2) . str_repeat('*', max(0, strlen($local) - 2));
        
        return $maskedLocal . '@' . $domain;
    }
}
