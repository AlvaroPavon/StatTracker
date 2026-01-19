<?php
/**
 * Clase UltimateShield - Capa de seguridad de máximo nivel
 * Implementa TODAS las protecciones conocidas contra ataques web
 * @package App
 */

namespace App;

class UltimateShield
{
    // ==================== CONFIGURACIÓN ====================
    
    // IPs en lista negra permanente (conocidos atacantes)
    private const BLACKLISTED_IPS = [
        // Añadir IPs conocidas de atacantes aquí
    ];
    
    // Países bloqueados (códigos ISO) - opcional
    private const BLOCKED_COUNTRIES = [
        // 'CN', 'RU', 'KP' // Ejemplo: China, Rusia, Corea del Norte
    ];
    
    // Extensiones de archivo NUNCA permitidas
    private const FORBIDDEN_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'phar',
        'exe', 'bat', 'cmd', 'sh', 'bash', 'zsh', 'ps1',
        'jsp', 'jspx', 'asp', 'aspx', 'asa', 'asax', 'ascx', 'ashx', 'asmx',
        'cgi', 'pl', 'py', 'rb', 'js', 'vbs', 'wsf', 'wsh',
        'htaccess', 'htpasswd', 'ini', 'config', 'conf',
        'sql', 'db', 'sqlite', 'mdb',
        'swf', 'jar', 'class',
        'dll', 'so', 'dylib'
    ];
    
    // User-Agents de herramientas de hacking
    private const HACKING_TOOLS = [
        'sqlmap', 'nikto', 'nmap', 'masscan', 'nessus', 'openvas',
        'acunetix', 'havij', 'pangolin', 'w3af', 'burp', 'owasp',
        'dirbuster', 'gobuster', 'wfuzz', 'hydra', 'medusa', 'john',
        'metasploit', 'meterpreter', 'cobalt', 'empire', 'mimikatz',
        'curl/', 'wget/', 'python-requests', 'go-http-client',
        'libwww-perl', 'mechanize', 'scrapy', 'phantom', 'headless',
        'zgrab', 'censys', 'shodan', 'masscan', 'zmap'
    ];
    
    // Patrones de ataque adicionales (regex)
    private const ATTACK_PATTERNS = [
        // SQL Injection avanzado
        '/(\%27)|(\')|(\-\-)|(\%23)|(#)/i',
        '/((\%3D)|(=))[^\n]*((\%27)|(\')|(\-\-)|(\%3B)|(;))/i',
        '/\w*((\%27)|(\'))((\%6F)|o|(\%4F))((\%72)|r|(\%52))/i',
        '/((\%27)|(\'))union/i',
        '/exec(\s|\+)+(s|x)p\w+/i',
        '/UNION(\s+)SELECT/i',
        '/UNION(\s+)ALL(\s+)SELECT/i',
        '/INTO(\s+)(DUMP|OUT)FILE/i',
        '/GROUP(\s+)BY(.+)HAVING/i',
        '/ORDER(\s+)BY(\s+)\d+/i',
        '/BENCHMARK\s*\(/i',
        '/SLEEP\s*\(/i',
        '/WAITFOR\s+DELAY/i',
        '/LOAD_FILE\s*\(/i',
        '/INFORMATION_SCHEMA/i',
        
        // XSS avanzado
        '/(<|%3C)script/i',
        '/(javascript|vbscript|expression|applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)/i',
        '/on\w+\s*=/i',
        '/(&#x?[0-9A-Fa-f]+;?)/i',
        '/\\\\x[0-9A-Fa-f]{2}/i',
        '/\\\\u[0-9A-Fa-f]{4}/i',
        
        // LDAP Injection
        '/[)(|*\\\\]/i',
        
        // XML/XXE
        '/<\?xml/i',
        '/<!DOCTYPE/i',
        '/<!ENTITY/i',
        '/SYSTEM\s+["\']file:/i',
        
        // Server-Side Template Injection
        '/\{\{.*\}\}/i',
        '/\{%.*%\}/i',
        '/\$\{.*\}/i',
        '/#\{.*\}/i',
        
        // NoSQL Injection
        '/\$where/i',
        '/\$regex/i',
        '/\$ne/i',
        '/\$gt/i',
        '/\$lt/i',
        
        // Log Injection
        '/[\r\n]/i',
        
        // Null byte
        '/\x00/',
        '/%00/',
        '/\\\\0/',
    ];

    // ==================== MÉTODOS PRINCIPALES ====================

    /**
     * Ejecuta TODAS las verificaciones de seguridad
     */
    public static function protect(): array
    {
        $threats = [];
        
        // 1. Verificar IP en lista negra
        $ip = self::getClientIp();
        if (self::isBlacklistedIp($ip)) {
            $threats[] = 'BLACKLISTED_IP';
        }
        
        // 2. Verificar User-Agent
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (self::isHackingTool($ua)) {
            $threats[] = 'HACKING_TOOL_DETECTED';
        }
        
        // 3. Verificar método HTTP
        if (!self::isAllowedMethod()) {
            $threats[] = 'INVALID_HTTP_METHOD';
        }
        
        // 4. Verificar Content-Type en POST
        if (!self::isValidContentType()) {
            $threats[] = 'INVALID_CONTENT_TYPE';
        }
        
        // 5. Analizar TODOS los inputs
        $inputThreats = self::analyzeAllInputs();
        $threats = array_merge($threats, $inputThreats);
        
        // 6. Verificar headers sospechosos
        if (self::hasSuspiciousHeaders()) {
            $threats[] = 'SUSPICIOUS_HEADERS';
        }
        
        // 7. Verificar request body sospechoso
        if (self::hasSuspiciousBody()) {
            $threats[] = 'SUSPICIOUS_BODY';
        }
        
        // 8. Verificar tamaño de request
        if (!self::isValidRequestSize()) {
            $threats[] = 'REQUEST_TOO_LARGE';
        }
        
        // 9. Verificar frecuencia de requests (DDoS simple)
        if (!self::checkRequestFrequency($ip)) {
            $threats[] = 'TOO_MANY_REQUESTS';
        }
        
        // 10. Verificar integridad de cookies
        if (!self::validateCookieIntegrity()) {
            $threats[] = 'COOKIE_TAMPERING';
        }
        
        // Registrar amenazas detectadas
        if (!empty($threats)) {
            self::logThreats($threats);
        }
        
        return [
            'safe' => empty($threats),
            'threats' => $threats,
            'ip' => $ip
        ];
    }

    /**
     * Bloquea la petición y registra el intento
     */
    public static function block(array $threats): void
    {
        $ip = self::getClientIp();
        
        // Registrar el bloqueo
        SecurityAudit::log('ULTIMATE_SHIELD_BLOCK', null, [
            'ip' => $ip,
            'threats' => $threats,
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200)
        ], 'CRITICAL');
        
        // Bloquear IP temporalmente
        SecurityFirewall::blockIp($ip, $threats);
        
        // Responder con error genérico
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        
        echo '<!DOCTYPE html><html><head><title>403</title></head><body><h1>403 Forbidden</h1></body></html>';
        exit;
    }

    // ==================== VERIFICACIONES ====================

    /**
     * Verifica si la IP está en lista negra
     */
    private static function isBlacklistedIp(string $ip): bool
    {
        return in_array($ip, self::BLACKLISTED_IPS);
    }

    /**
     * Detecta herramientas de hacking por User-Agent
     */
    private static function isHackingTool(string $userAgent): bool
    {
        $ua = strtolower($userAgent);
        
        // En localhost, ser más permisivo (para desarrollo)
        $isLocalhost = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']) ||
                      (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);
        
        if ($isLocalhost) {
            // En localhost, solo bloquear las herramientas más agresivas
            $aggressiveTools = ['sqlmap', 'nikto', 'acunetix', 'havij', 'hydra', 'metasploit'];
            foreach ($aggressiveTools as $tool) {
                if (strpos($ua, $tool) !== false) {
                    return true;
                }
            }
            return false;
        }
        
        // En producción, verificación completa
        foreach (self::HACKING_TOOLS as $tool) {
            if (strpos($ua, $tool) !== false) {
                return true;
            }
        }
        
        // User-Agent vacío o muy corto es sospechoso
        if (strlen($userAgent) < 10) {
            return true;
        }
        
        // User-Agent sin navegador conocido es sospechoso
        $browsers = ['mozilla', 'chrome', 'safari', 'firefox', 'edge', 'opera', 'msie', 'trident'];
        $hasBrowser = false;
        foreach ($browsers as $browser) {
            if (strpos($ua, $browser) !== false) {
                $hasBrowser = true;
                break;
            }
        }
        
        return !$hasBrowser;
    }

    /**
     * Verifica método HTTP permitido
     */
    private static function isAllowedMethod(): bool
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? '';
        return in_array($method, ['GET', 'POST', 'HEAD']);
    }

    /**
     * Verifica Content-Type válido para POST
     */
    private static function isValidContentType(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }
        
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        $validTypes = [
            'application/x-www-form-urlencoded',
            'multipart/form-data',
            'application/json',
            'text/plain'
        ];
        
        foreach ($validTypes as $type) {
            if (strpos($contentType, $type) !== false) {
                return true;
            }
        }
        
        // Sin Content-Type en POST es sospechoso
        return !empty($contentType);
    }

    /**
     * Analiza todos los inputs
     */
    private static function analyzeAllInputs(): array
    {
        $threats = [];
        
        // Analizar GET
        foreach ($_GET as $key => $value) {
            $threats = array_merge($threats, self::analyzeValue($value, "GET[$key]"));
        }
        
        // Analizar POST
        foreach ($_POST as $key => $value) {
            $threats = array_merge($threats, self::analyzeValue($value, "POST[$key]"));
        }
        
        // Analizar COOKIES
        foreach ($_COOKIE as $key => $value) {
            if ($key !== session_name()) {
                $threats = array_merge($threats, self::analyzeValue($value, "COOKIE[$key]"));
            }
        }
        
        // Analizar URI
        $threats = array_merge($threats, self::analyzeValue($_SERVER['REQUEST_URI'] ?? '', 'URI'));
        
        // Analizar Query String
        $threats = array_merge($threats, self::analyzeValue($_SERVER['QUERY_STRING'] ?? '', 'QUERY'));
        
        // Analizar Referer
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $threats = array_merge($threats, self::analyzeValue($_SERVER['HTTP_REFERER'], 'REFERER'));
        }
        
        return array_unique($threats);
    }

    /**
     * Analiza un valor individual
     */
    private static function analyzeValue($value, string $context): array
    {
        $threats = [];
        
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $threats = array_merge($threats, self::analyzeValue($v, "{$context}[$k]"));
            }
            return $threats;
        }
        
        if (!is_string($value)) {
            return $threats;
        }
        
        // Decodificar múltiples capas
        $decoded = self::multiDecode($value);
        
        // Verificar patrones de ataque
        foreach (self::ATTACK_PATTERNS as $pattern) {
            if (@preg_match($pattern, $decoded)) {
                $threats[] = "ATTACK_PATTERN:$context";
                break;
            }
        }
        
        // Verificar longitud excesiva
        if (strlen($value) > 10000) {
            $threats[] = "INPUT_TOO_LONG:$context";
        }
        
        // Verificar caracteres nulos
        if (strpos($value, "\0") !== false) {
            $threats[] = "NULL_BYTE:$context";
        }
        
        // Verificar extensiones de archivo prohibidas
        // SOLO para parámetros, NO para URI/QUERY normales (que naturalmente tienen .php)
        if ($context !== 'URI' && $context !== 'QUERY' && $context !== 'REFERER') {
            if (preg_match('/\.(' . implode('|', self::FORBIDDEN_EXTENSIONS) . ')(\?|$)/i', $decoded)) {
                $threats[] = "FORBIDDEN_EXTENSION:$context";
            }
        }
        
        return $threats;
    }

    /**
     * Decodifica múltiples capas de encoding
     */
    private static function multiDecode(string $value): string
    {
        $decoded = $value;
        $iterations = 0;
        
        while ($iterations < 5) {
            $prev = $decoded;
            
            // URL decode
            $decoded = rawurldecode($decoded);
            
            // HTML entity decode
            $decoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
            // Base64 decode si parece base64
            if (preg_match('/^[a-zA-Z0-9+\/]+=*$/', trim($decoded)) && strlen($decoded) > 20) {
                $b64 = @base64_decode($decoded, true);
                if ($b64 !== false && ctype_print($b64)) {
                    $decoded = $b64;
                }
            }
            
            if ($decoded === $prev) {
                break;
            }
            
            $iterations++;
        }
        
        return $decoded;
    }

    /**
     * Verifica headers sospechosos
     */
    private static function hasSuspiciousHeaders(): bool
    {
        // Headers que no deberían estar presentes
        $suspiciousHeaders = [
            'HTTP_X_FORWARDED_HOST',
            'HTTP_X_ORIGINAL_URL',
            'HTTP_X_REWRITE_URL',
            'HTTP_PROXY',
            'HTTP_PROXY_CONNECTION',
            'HTTP_X_CUSTOM_IP_AUTHORIZATION'
        ];
        
        foreach ($suspiciousHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                return true;
            }
        }
        
        // Verificar Host header injection
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (preg_match('/[<>"\'\\\\]/', $host)) {
            return true;
        }
        
        return false;
    }

    /**
     * Verifica body sospechoso
     */
    private static function hasSuspiciousBody(): bool
    {
        $body = file_get_contents('php://input');
        
        if (empty($body)) {
            return false;
        }
        
        // Body demasiado grande
        if (strlen($body) > 1048576) { // 1MB
            return true;
        }
        
        // Verificar patrones en body
        foreach (self::ATTACK_PATTERNS as $pattern) {
            if (@preg_match($pattern, $body)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Verifica tamaño de request
     */
    private static function isValidRequestSize(): bool
    {
        $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
        return $contentLength <= 5242880; // 5MB máximo
    }

    /**
     * Control de frecuencia de requests
     */
    private static function checkRequestFrequency(string $ip): bool
    {
        $key = 'freq_' . md5($ip);
        $now = time();
        $window = 10; // 10 segundos
        $maxRequests = 30; // máximo 30 requests en 10 segundos
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'start' => $now];
        }
        
        // Reset si pasó la ventana
        if ($now - $_SESSION[$key]['start'] > $window) {
            $_SESSION[$key] = ['count' => 1, 'start' => $now];
            return true;
        }
        
        $_SESSION[$key]['count']++;
        
        return $_SESSION[$key]['count'] <= $maxRequests;
    }

    /**
     * Valida integridad de cookies
     */
    private static function validateCookieIntegrity(): bool
    {
        foreach ($_COOKIE as $name => $value) {
            // Verificar caracteres inválidos en nombre de cookie
            if (preg_match('/[=,; \t\r\n\013\014]/', $name)) {
                return false;
            }
            
            // Verificar valor excesivamente largo
            if (strlen($value) > 4096) {
                return false;
            }
            
            // Verificar patrones de ataque en valor
            $decoded = self::multiDecode($value);
            foreach (self::ATTACK_PATTERNS as $pattern) {
                if (@preg_match($pattern, $decoded)) {
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Registra amenazas detectadas
     */
    private static function logThreats(array $threats): void
    {
        SecurityAudit::log('THREATS_DETECTED', null, [
            'threats' => $threats,
            'ip' => self::getClientIp(),
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200)
        ], 'WARNING');
    }

    /**
     * Obtiene IP del cliente
     */
    private static function getClientIp(): string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = filter_var($_SERVER[$header], FILTER_VALIDATE_IP);
                if ($ip !== false) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }

    // ==================== UTILIDADES ADICIONALES ====================

    /**
     * Genera hash de integridad para formularios
     */
    public static function generateFormIntegrity(string $formId): string
    {
        $data = $formId . session_id() . date('Y-m-d-H');
        return hash_hmac('sha256', $data, self::getSecretKey());
    }

    /**
     * Valida hash de integridad
     */
    public static function validateFormIntegrity(string $formId, string $hash): bool
    {
        // Verificar hora actual y anterior (por si cruza la hora)
        $expected1 = self::generateFormIntegrity($formId);
        
        $data = $formId . session_id() . date('Y-m-d-H', strtotime('-1 hour'));
        $expected2 = hash_hmac('sha256', $data, self::getSecretKey());
        
        return hash_equals($expected1, $hash) || hash_equals($expected2, $hash);
    }

    /**
     * Obtiene clave secreta
     */
    private static function getSecretKey(): string
    {
        return hash('sha256', __DIR__ . 'STATTRACKER_SECRET_2025');
    }

    /**
     * Verifica si un archivo subido es seguro
     */
    public static function isUploadSafe(array $file): array
    {
        $errors = [];
        
        // Verificar errores de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload error: ' . $file['error'];
            return ['safe' => false, 'errors' => $errors];
        }
        
        // Verificar que es un archivo subido real
        if (!is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'Not a valid upload';
            return ['safe' => false, 'errors' => $errors];
        }
        
        // Verificar extensión
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, self::FORBIDDEN_EXTENSIONS)) {
            $errors[] = 'Forbidden extension';
            return ['safe' => false, 'errors' => $errors];
        }
        
        // Verificar nombre de archivo
        if (preg_match('/[<>:"\/\\\\|?*\x00-\x1f]/', $file['name'])) {
            $errors[] = 'Invalid filename';
            return ['safe' => false, 'errors' => $errors];
        }
        
        // Verificar tamaño
        if ($file['size'] > 2097152) { // 2MB
            $errors[] = 'File too large';
            return ['safe' => false, 'errors' => $errors];
        }
        
        // Verificar MIME type real
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp'
        ];
        
        if (!in_array($mime, $allowedMimes)) {
            $errors[] = 'Invalid file type';
            return ['safe' => false, 'errors' => $errors];
        }
        
        // Verificar contenido del archivo (magic bytes)
        $handle = fopen($file['tmp_name'], 'rb');
        $bytes = fread($handle, 12);
        fclose($handle);
        
        $validSignatures = [
            "\xFF\xD8\xFF" => 'image/jpeg',      // JPEG
            "\x89PNG\r\n\x1a\n" => 'image/png',  // PNG
            "GIF87a" => 'image/gif',              // GIF87a
            "GIF89a" => 'image/gif',              // GIF89a
            "RIFF" => 'image/webp',               // WEBP
        ];
        
        $validSignature = false;
        foreach ($validSignatures as $sig => $type) {
            if (strpos($bytes, $sig) === 0) {
                $validSignature = true;
                break;
            }
        }
        
        if (!$validSignature) {
            $errors[] = 'Invalid file signature';
            return ['safe' => false, 'errors' => $errors];
        }
        
        // Verificar que no contiene código PHP
        $content = file_get_contents($file['tmp_name']);
        $phpPatterns = ['<?php', '<?=', '<script', '<%'];
        foreach ($phpPatterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                $errors[] = 'File contains code';
                return ['safe' => false, 'errors' => $errors];
            }
        }
        
        return ['safe' => true, 'errors' => []];
    }
}
