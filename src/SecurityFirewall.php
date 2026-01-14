<?php
/**
 * Clase SecurityFirewall - Firewall de Aplicación Web (WAF) simplificado
 * Detecta y bloquea ataques comunes antes de que lleguen a la aplicación
 * @package App
 */

namespace App;

class SecurityFirewall
{
    // Configuración
    private const BLOCK_DURATION = 3600; // 1 hora de bloqueo
    private const MAX_REQUEST_SIZE = 1048576; // 1MB máximo
    private const MAX_INPUT_LENGTH = 10000; // Longitud máxima de input individual
    private const BLOCK_FILE = __DIR__ . '/../logs/blocked_ips.json';
    
    // Patrones de ataque SQL Injection (más exhaustivos)
    private const SQL_PATTERNS = [
        '/\bunion\s+(all\s+)?select\b/i',
        '/\bselect\b.*\bfrom\b.*\bwhere\b/i',
        '/\binsert\s+into\b/i',
        '/\bdelete\s+from\b/i',
        '/\bdrop\s+(table|database|index)\b/i',
        '/\btruncate\s+table\b/i',
        '/\balter\s+table\b/i',
        '/\bexec(ute)?\s*\(/i',
        '/\bxp_cmdshell\b/i',
        '/\bsp_executesql\b/i',
        '/\/\*.*\*\//s',  // Comentarios SQL
        '/--[\s\r\n]/',   // Comentarios SQL inline
        '/;\s*(drop|delete|update|insert)/i',
        '/\bor\b\s*[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+/i', // OR 1=1
        '/\band\b\s*[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+/i', // AND 1=1
        '/\b(sleep|benchmark|waitfor)\s*\(/i', // Time-based
        '/\bload_file\s*\(/i',
        '/\binto\s+(out|dump)file\b/i',
        '/\bconcat\s*\(/i',
        '/\bchar\s*\(\s*\d+/i',
        '/0x[0-9a-fA-F]+/',  // Hex encoding
        '/\%27|\%22|\%3D/i', // URL encoded quotes
    ];
    
    // Patrones de XSS
    private const XSS_PATTERNS = [
        '/<script[^>]*>/i',
        '/<\/script>/i',
        '/javascript\s*:/i',
        '/vbscript\s*:/i',
        '/on(click|load|error|mouseover|mouseout|keyup|keydown|submit|focus|blur|change)\s*=/i',
        '/<iframe/i',
        '/<object/i',
        '/<embed/i',
        '/<applet/i',
        '/<meta/i',
        '/<link[^>]+href/i',
        '/<base[^>]+href/i',
        '/<form[^>]+action/i',
        '/\beval\s*\(/i',
        '/\bdocument\s*\./i',
        '/\bwindow\s*\./i',
        '/\balert\s*\(/i',
        '/\bprompt\s*\(/i',
        '/\bconfirm\s*\(/i',
        '/expression\s*\(/i',
        '/url\s*\(\s*[\'"]?\s*data:/i',
        '/data\s*:\s*text\/html/i',
        '/<svg[^>]*onload/i',
        '/<img[^>]*onerror/i',
        '/<body[^>]*onload/i',
        '/&#x?[0-9a-fA-F]+;/',  // HTML entities
        '/\\u00[0-9a-fA-F]{2}/i', // Unicode escapes
    ];
    
    // Patrones de Path Traversal
    private const PATH_TRAVERSAL_PATTERNS = [
        '/\.\.[\\\/]/',
        '/\.\.\./',
        '/%2e%2e[\\\/]/i',
        '/%252e%252e/i',
        '/\.\.\\//',
        '/\.\.%2f/i',
        '/\.\.%5c/i',
        '/etc\/passwd/i',
        '/etc\/shadow/i',
        '/proc\/self/i',
        '/windows\/system32/i',
        '/boot\.ini/i',
    ];
    
    // Patrones de Command Injection
    private const COMMAND_PATTERNS = [
        '/[;&|`$]/',
        '/\|\|/',
        '/&&/',
        '/\$\(/',
        '/`[^`]+`/',
        '/\bwget\b/i',
        '/\bcurl\b/i',
        '/\bnc\b/',
        '/\bnetcat\b/i',
        '/\btelnet\b/i',
        '/\bperl\b/i',
        '/\bpython\b/i',
        '/\bruby\b/i',
        '/\bphp\b/i',
        '/\bbash\b/i',
        '/\bsh\b/',
        '/\/bin\//i',
        '/\/usr\//i',
    ];
    
    // Patrones de LFI/RFI
    private const INCLUSION_PATTERNS = [
        '/php:\/\/filter/i',
        '/php:\/\/input/i',
        '/php:\/\/output/i',
        '/expect:\/\//i',
        '/phar:\/\//i',
        '/zip:\/\//i',
        '/data:\/\//i',
        '/file:\/\//i',
        '/glob:\/\//i',
        '/ftp:\/\//i',
        '/zlib:\/\//i',
        '/\binclude\b.*\$/i',
        '/\brequire\b.*\$/i',
    ];
    
    // User agents sospechosos (scanners, bots maliciosos)
    private const SUSPICIOUS_USER_AGENTS = [
        'sqlmap',
        'nikto',
        'nmap',
        'masscan',
        'nessus',
        'openvas',
        'acunetix',
        'havij',
        'pangolin',
        'w3af',
        'burpsuite',
        'dirbuster',
        'gobuster',
        'wfuzz',
        'hydra',
        'medusa',
    ];

    /**
     * Analiza la petición entrante y bloquea si es maliciosa
     * @return array ['safe' => bool, 'threats' => array, 'blocked' => bool]
     */
    public static function analyze(): array
    {
        $result = [
            'safe' => true,
            'threats' => [],
            'blocked' => false,
            'ip' => self::getClientIp()
        ];
        
        // 1. Verificar si la IP está bloqueada
        if (self::isIpBlocked($result['ip'])) {
            $result['safe'] = false;
            $result['blocked'] = true;
            $result['threats'][] = 'IP_BLOCKED';
            return $result;
        }
        
        // 2. Verificar tamaño de la petición
        $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
        if ($contentLength > self::MAX_REQUEST_SIZE) {
            $result['safe'] = false;
            $result['threats'][] = 'REQUEST_TOO_LARGE';
        }
        
        // 3. Verificar User-Agent
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        foreach (self::SUSPICIOUS_USER_AGENTS as $suspicious) {
            if (strpos($userAgent, $suspicious) !== false) {
                $result['safe'] = false;
                $result['threats'][] = 'SUSPICIOUS_USER_AGENT:' . $suspicious;
            }
        }
        
        // 4. Verificar método HTTP
        $method = $_SERVER['REQUEST_METHOD'] ?? '';
        $allowedMethods = ['GET', 'POST', 'HEAD'];
        if (!in_array($method, $allowedMethods)) {
            $result['safe'] = false;
            $result['threats'][] = 'INVALID_METHOD:' . $method;
        }
        
        // 5. Analizar parámetros GET
        foreach ($_GET as $key => $value) {
            $threats = self::analyzeInput($value, $key);
            if (!empty($threats)) {
                $result['safe'] = false;
                $result['threats'] = array_merge($result['threats'], $threats);
            }
        }
        
        // 6. Analizar parámetros POST
        foreach ($_POST as $key => $value) {
            $threats = self::analyzeInput($value, $key);
            if (!empty($threats)) {
                $result['safe'] = false;
                $result['threats'] = array_merge($result['threats'], $threats);
            }
        }
        
        // 7. Analizar cookies
        foreach ($_COOKIE as $key => $value) {
            // Solo analizar cookies que no sean de sesión
            if ($key !== session_name()) {
                $threats = self::analyzeInput($value, 'cookie:' . $key);
                if (!empty($threats)) {
                    $result['safe'] = false;
                    $result['threats'] = array_merge($result['threats'], $threats);
                }
            }
        }
        
        // 8. Analizar URI
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $threats = self::analyzeInput($uri, 'REQUEST_URI');
        if (!empty($threats)) {
            $result['safe'] = false;
            $result['threats'] = array_merge($result['threats'], $threats);
        }
        
        // 9. Si se detectaron amenazas críticas, bloquear IP
        $criticalThreats = ['SQL_INJECTION', 'COMMAND_INJECTION', 'FILE_INCLUSION'];
        foreach ($result['threats'] as $threat) {
            foreach ($criticalThreats as $critical) {
                if (strpos($threat, $critical) === 0) {
                    self::blockIp($result['ip'], $result['threats']);
                    $result['blocked'] = true;
                    break 2;
                }
            }
        }
        
        // 10. Registrar amenazas detectadas
        if (!empty($result['threats'])) {
            self::logThreat($result);
        }
        
        return $result;
    }
    
    /**
     * Analiza un input individual
     */
    private static function analyzeInput(mixed $value, string $context): array
    {
        $threats = [];
        
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $threats = array_merge($threats, self::analyzeInput($v, $context . '[' . $k . ']'));
            }
            return $threats;
        }
        
        if (!is_string($value)) {
            return $threats;
        }
        
        // Verificar longitud
        if (strlen($value) > self::MAX_INPUT_LENGTH) {
            $threats[] = "INPUT_TOO_LONG:{$context}";
        }
        
        // Decodificar para análisis más profundo
        $decoded = self::deepDecode($value);
        
        // SQL Injection
        foreach (self::SQL_PATTERNS as $pattern) {
            if (preg_match($pattern, $decoded)) {
                $threats[] = "SQL_INJECTION:{$context}";
                break;
            }
        }
        
        // XSS
        foreach (self::XSS_PATTERNS as $pattern) {
            if (preg_match($pattern, $decoded)) {
                $threats[] = "XSS:{$context}";
                break;
            }
        }
        
        // Path Traversal
        foreach (self::PATH_TRAVERSAL_PATTERNS as $pattern) {
            if (preg_match($pattern, $decoded)) {
                $threats[] = "PATH_TRAVERSAL:{$context}";
                break;
            }
        }
        
        // Command Injection
        foreach (self::COMMAND_PATTERNS as $pattern) {
            if (preg_match($pattern, $decoded)) {
                $threats[] = "COMMAND_INJECTION:{$context}";
                break;
            }
        }
        
        // LFI/RFI
        foreach (self::INCLUSION_PATTERNS as $pattern) {
            if (preg_match($pattern, $decoded)) {
                $threats[] = "FILE_INCLUSION:{$context}";
                break;
            }
        }
        
        // Null byte injection
        if (strpos($value, "\0") !== false || strpos($value, '%00') !== false) {
            $threats[] = "NULL_BYTE:{$context}";
        }
        
        return $threats;
    }
    
    /**
     * Decodifica múltiples capas de encoding
     */
    private static function deepDecode(string $value): string
    {
        $decoded = $value;
        $prev = '';
        $iterations = 0;
        $maxIterations = 5;
        
        while ($decoded !== $prev && $iterations < $maxIterations) {
            $prev = $decoded;
            
            // URL decode
            $decoded = urldecode($decoded);
            
            // HTML entity decode
            $decoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
            // Unicode decode
            $decoded = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function($m) {
                return mb_convert_encoding(pack('H*', $m[1]), 'UTF-8', 'UTF-16BE');
            }, $decoded);
            
            // Hex decode
            $decoded = preg_replace_callback('/0x([0-9a-fA-F]+)/', function($m) {
                return @hex2bin($m[1]) ?: $m[0];
            }, $decoded);
            
            $iterations++;
        }
        
        return $decoded;
    }
    
    /**
     * Verifica si una IP está bloqueada
     */
    public static function isIpBlocked(string $ip): bool
    {
        $blockedIps = self::getBlockedIps();
        
        if (isset($blockedIps[$ip])) {
            // Verificar si el bloqueo ha expirado
            if ($blockedIps[$ip]['until'] > time()) {
                return true;
            }
            // El bloqueo expiró, eliminarlo
            unset($blockedIps[$ip]);
            self::saveBlockedIps($blockedIps);
        }
        
        return false;
    }
    
    /**
     * Bloquea una IP
     */
    public static function blockIp(string $ip, array $reasons = []): void
    {
        $blockedIps = self::getBlockedIps();
        
        $blockedIps[$ip] = [
            'until' => time() + self::BLOCK_DURATION,
            'blocked_at' => date('Y-m-d H:i:s'),
            'reasons' => $reasons
        ];
        
        self::saveBlockedIps($blockedIps);
        
        // Registrar el bloqueo
        SecurityAudit::log(
            'IP_BLOCKED',
            null,
            [
                'ip' => $ip,
                'reasons' => $reasons,
                'duration' => self::BLOCK_DURATION
            ],
            'CRITICAL'
        );
    }
    
    /**
     * Desbloquea una IP
     */
    public static function unblockIp(string $ip): bool
    {
        $blockedIps = self::getBlockedIps();
        
        if (isset($blockedIps[$ip])) {
            unset($blockedIps[$ip]);
            self::saveBlockedIps($blockedIps);
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtiene la lista de IPs bloqueadas
     */
    private static function getBlockedIps(): array
    {
        if (!file_exists(self::BLOCK_FILE)) {
            return [];
        }
        
        $content = @file_get_contents(self::BLOCK_FILE);
        if (!$content) {
            return [];
        }
        
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }
    
    /**
     * Guarda la lista de IPs bloqueadas
     */
    private static function saveBlockedIps(array $ips): void
    {
        $dir = dirname(self::BLOCK_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        
        file_put_contents(
            self::BLOCK_FILE,
            json_encode($ips, JSON_PRETTY_PRINT),
            LOCK_EX
        );
    }
    
    /**
     * Registra una amenaza detectada
     */
    private static function logThreat(array $result): void
    {
        SecurityAudit::log(
            'THREAT_DETECTED',
            $_SESSION['user_id'] ?? null,
            [
                'threats' => $result['threats'],
                'ip' => $result['ip'],
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200)
            ],
            'WARNING'
        );
    }
    
    /**
     * Obtiene la IP del cliente
     */
    private static function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
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
        
        return '0.0.0.0';
    }
    
    /**
     * Genera respuesta de bloqueo
     */
    public static function blockResponse(): void
    {
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
        
        echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Denegado</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #1a1a2e; color: #eee; }
        h1 { color: #e74c3c; }
        p { color: #bbb; }
    </style>
</head>
<body>
    <h1>403 - Acceso Denegado</h1>
    <p>Su solicitud ha sido bloqueada por razones de seguridad.</p>
    <p>Si cree que esto es un error, contacte al administrador.</p>
</body>
</html>';
        exit;
    }
}
