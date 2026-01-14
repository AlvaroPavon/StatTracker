<?php
/**
 * Clase SupplyChainGuard - Protección contra ataques de cadena de suministro
 * Verifica integridad de TODAS las dependencias y código
 * @package App
 */

namespace App;

class SupplyChainGuard
{
    // Archivo de hashes de dependencias
    private const VENDOR_HASH_FILE = __DIR__ . '/../logs/.vendor_hashes.json';
    
    // Archivo de hashes de archivos de la aplicación
    private const APP_HASH_FILE = __DIR__ . '/../logs/.app_hashes.json';
    
    // Clases autorizadas para autoload
    private const AUTHORIZED_NAMESPACES = [
        'App\\',
        'PHPUnit\\',
        'Composer\\',
    ];
    
    // Funciones peligrosas que no deberían existir en dependencias
    private const DANGEROUS_PATTERNS = [
        'eval(',
        'exec(',
        'shell_exec(',
        'system(',
        'passthru(',
        'popen(',
        'proc_open(',
        'pcntl_exec(',
        'assert(',
        'create_function(',
        'call_user_func_array(',
        'preg_replace_callback(',  // con /e modifier
        '`',  // backtick execution
        'file_get_contents(\'http',
        'file_get_contents(\'ftp',
        'file_get_contents("http',
        'file_get_contents("ftp',
        'curl_exec(',
        'fsockopen(',
        'base64_decode(', // Puede ocultar código malicioso
    ];

    /**
     * Verifica la integridad completa del sistema
     */
    public static function verify(): array
    {
        $result = [
            'valid' => true,
            'vendor_valid' => true,
            'app_valid' => true,
            'autoload_valid' => true,
            'suspicious_code' => [],
            'modified_files' => [],
            'unauthorized_files' => []
        ];
        
        // 1. Verificar integridad de vendor/
        $vendorCheck = self::verifyVendorIntegrity();
        if (!$vendorCheck['valid']) {
            $result['valid'] = false;
            $result['vendor_valid'] = false;
            $result['modified_files'] = array_merge(
                $result['modified_files'], 
                $vendorCheck['modified']
            );
        }
        
        // 2. Verificar integridad de archivos de la app
        $appCheck = self::verifyAppIntegrity();
        if (!$appCheck['valid']) {
            $result['valid'] = false;
            $result['app_valid'] = false;
            $result['modified_files'] = array_merge(
                $result['modified_files'], 
                $appCheck['modified']
            );
        }
        
        // 3. Escanear código sospechoso en vendor/
        $suspiciousCode = self::scanForSuspiciousCode();
        if (!empty($suspiciousCode)) {
            $result['valid'] = false;
            $result['suspicious_code'] = $suspiciousCode;
        }
        
        // 4. Verificar que no hay archivos no autorizados
        $unauthorizedFiles = self::findUnauthorizedFiles();
        if (!empty($unauthorizedFiles)) {
            $result['valid'] = false;
            $result['unauthorized_files'] = $unauthorizedFiles;
        }
        
        // Registrar si hay problemas
        if (!$result['valid']) {
            SecurityAudit::log('SUPPLY_CHAIN_VIOLATION', null, $result, 'CRITICAL');
        }
        
        return $result;
    }

    /**
     * Genera y guarda hashes de todas las dependencias
     */
    public static function generateVendorHashes(): array
    {
        $hashes = [];
        $vendorPath = dirname(__DIR__) . '/vendor';
        
        if (!is_dir($vendorPath)) {
            return $hashes;
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($vendorPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = str_replace($vendorPath . '/', '', $file->getPathname());
                $hashes[$relativePath] = [
                    'hash' => hash_file('sha384', $file->getPathname()),
                    'size' => $file->getSize()
                ];
            }
        }
        
        // Guardar hashes
        $data = [
            '_generated' => date('Y-m-d H:i:s'),
            '_count' => count($hashes),
            'files' => $hashes
        ];
        
        $dir = dirname(self::VENDOR_HASH_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        
        file_put_contents(self::VENDOR_HASH_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
        
        return $hashes;
    }

    /**
     * Genera hashes de archivos de la aplicación
     */
    public static function generateAppHashes(): array
    {
        $hashes = [];
        $basePath = dirname(__DIR__);
        
        $files = [
            'security_init.php',
            'database_connection.php',
            'db.php',
            'index.php',
            'login.php',
            'register.php',
            'logout.php',
            'dashboard.php',
            'profile.php',
            '.htaccess'
        ];
        
        // Añadir todos los archivos de src/
        $srcPath = $basePath . '/src';
        if (is_dir($srcPath)) {
            $srcFiles = glob($srcPath . '/*.php');
            foreach ($srcFiles as $file) {
                $files[] = 'src/' . basename($file);
            }
        }
        
        foreach ($files as $file) {
            $fullPath = $basePath . '/' . $file;
            if (file_exists($fullPath)) {
                $hashes[$file] = [
                    'hash' => hash_file('sha384', $fullPath),
                    'size' => filesize($fullPath)
                ];
            }
        }
        
        // Guardar hashes
        $data = [
            '_generated' => date('Y-m-d H:i:s'),
            '_count' => count($hashes),
            'files' => $hashes
        ];
        
        file_put_contents(self::APP_HASH_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
        
        return $hashes;
    }

    /**
     * Verifica integridad de vendor/
     */
    private static function verifyVendorIntegrity(): array
    {
        $result = ['valid' => true, 'modified' => []];
        
        if (!file_exists(self::VENDOR_HASH_FILE)) {
            // Primera vez - generar hashes
            self::generateVendorHashes();
            return $result;
        }
        
        $savedData = json_decode(file_get_contents(self::VENDOR_HASH_FILE), true);
        if (!$savedData || !isset($savedData['files'])) {
            return $result;
        }
        
        $vendorPath = dirname(__DIR__) . '/vendor';
        
        foreach ($savedData['files'] as $file => $info) {
            $fullPath = $vendorPath . '/' . $file;
            
            if (!file_exists($fullPath)) {
                $result['valid'] = false;
                $result['modified'][] = ['file' => $file, 'status' => 'deleted'];
                continue;
            }
            
            $currentHash = hash_file('sha384', $fullPath);
            if ($currentHash !== $info['hash']) {
                $result['valid'] = false;
                $result['modified'][] = [
                    'file' => $file, 
                    'status' => 'modified',
                    'expected_hash' => substr($info['hash'], 0, 16) . '...',
                    'actual_hash' => substr($currentHash, 0, 16) . '...'
                ];
            }
        }
        
        return $result;
    }

    /**
     * Verifica integridad de archivos de la app
     */
    private static function verifyAppIntegrity(): array
    {
        $result = ['valid' => true, 'modified' => []];
        
        if (!file_exists(self::APP_HASH_FILE)) {
            self::generateAppHashes();
            return $result;
        }
        
        $savedData = json_decode(file_get_contents(self::APP_HASH_FILE), true);
        if (!$savedData || !isset($savedData['files'])) {
            return $result;
        }
        
        $basePath = dirname(__DIR__);
        
        foreach ($savedData['files'] as $file => $info) {
            $fullPath = $basePath . '/' . $file;
            
            if (!file_exists($fullPath)) {
                $result['valid'] = false;
                $result['modified'][] = ['file' => $file, 'status' => 'deleted'];
                continue;
            }
            
            $currentHash = hash_file('sha384', $fullPath);
            if ($currentHash !== $info['hash']) {
                $result['valid'] = false;
                $result['modified'][] = [
                    'file' => $file,
                    'status' => 'modified'
                ];
            }
        }
        
        return $result;
    }

    /**
     * Escanea código sospechoso en dependencias
     */
    private static function scanForSuspiciousCode(): array
    {
        $suspicious = [];
        $vendorPath = dirname(__DIR__) . '/vendor';
        
        if (!is_dir($vendorPath)) {
            return $suspicious;
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($vendorPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                $relativePath = str_replace(dirname(__DIR__) . '/', '', $file->getPathname());
                
                foreach (self::DANGEROUS_PATTERNS as $pattern) {
                    // Buscar patrones peligrosos (ignorar comentarios)
                    if (stripos($content, $pattern) !== false) {
                        // Verificar que no está en un comentario
                        $lines = explode("\n", $content);
                        foreach ($lines as $lineNum => $line) {
                            // Ignorar comentarios
                            $trimmedLine = trim($line);
                            if (str_starts_with($trimmedLine, '//') || 
                                str_starts_with($trimmedLine, '*') ||
                                str_starts_with($trimmedLine, '/*')) {
                                continue;
                            }
                            
                            if (stripos($line, $pattern) !== false) {
                                $suspicious[] = [
                                    'file' => $relativePath,
                                    'line' => $lineNum + 1,
                                    'pattern' => $pattern,
                                    'context' => substr(trim($line), 0, 100)
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        return $suspicious;
    }

    /**
     * Encuentra archivos no autorizados
     */
    private static function findUnauthorizedFiles(): array
    {
        $unauthorized = [];
        $basePath = dirname(__DIR__);
        
        // Buscar archivos PHP en ubicaciones no permitidas
        $suspiciousLocations = [
            $basePath . '/uploads',
            $basePath . '/css',
            $basePath . '/js',
            $basePath . '/logs'
        ];
        
        foreach ($suspiciousLocations as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            
            $files = glob($dir . '/*.php');
            foreach ($files as $file) {
                $unauthorized[] = str_replace($basePath . '/', '', $file);
            }
            
            // También buscar archivos ocultos
            $hiddenFiles = glob($dir . '/.*');
            foreach ($hiddenFiles as $file) {
                $basename = basename($file);
                if ($basename !== '.' && $basename !== '..' && 
                    $basename !== '.htaccess' && $basename !== '.gitkeep') {
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                        $unauthorized[] = str_replace($basePath . '/', '', $file);
                    }
                }
            }
        }
        
        return $unauthorized;
    }

    /**
     * Registra un autoloader seguro que verifica namespaces
     */
    public static function registerSecureAutoloader(): void
    {
        spl_autoload_register(function ($class) {
            // Verificar que el namespace está autorizado
            $authorized = false;
            foreach (self::AUTHORIZED_NAMESPACES as $namespace) {
                if (str_starts_with($class, $namespace)) {
                    $authorized = true;
                    break;
                }
            }
            
            if (!$authorized) {
                // Intento de cargar clase no autorizada
                SecurityAudit::log('UNAUTHORIZED_CLASS_LOAD', null, [
                    'class' => $class
                ], 'CRITICAL');
                
                throw new \RuntimeException('Unauthorized class: ' . $class);
            }
        }, true, true); // Prepend = true para ejecutar antes que Composer
    }

    /**
     * Verifica la integridad del autoloader de Composer
     */
    public static function verifyComposerAutoloader(): bool
    {
        $autoloadFile = dirname(__DIR__) . '/vendor/autoload.php';
        
        if (!file_exists($autoloadFile)) {
            return false;
        }
        
        // Verificar que el contenido es el esperado
        $content = file_get_contents($autoloadFile);
        
        // El autoloader de Composer debe contener estas líneas
        $expectedPatterns = [
            'ComposerAutoloaderInit',
            'getLoader',
            'vendor/composer'
        ];
        
        foreach ($expectedPatterns as $pattern) {
            if (strpos($content, $pattern) === false) {
                SecurityAudit::log('CORRUPTED_AUTOLOADER', null, [
                    'missing_pattern' => $pattern
                ], 'CRITICAL');
                return false;
            }
        }
        
        // Verificar que no contiene código malicioso
        foreach (self::DANGEROUS_PATTERNS as $dangerous) {
            if (stripos($content, $dangerous) !== false) {
                SecurityAudit::log('MALICIOUS_AUTOLOADER', null, [
                    'pattern' => $dangerous
                ], 'CRITICAL');
                return false;
            }
        }
        
        return true;
    }

    /**
     * Bloquea el sistema si se detecta compromiso
     */
    public static function verifyOrDie(): void
    {
        // Verificar autoloader primero
        if (!self::verifyComposerAutoloader()) {
            self::emergencyShutdown('Autoloader comprometido');
        }
        
        // Verificar integridad completa
        $result = self::verify();
        
        if (!$result['valid']) {
            self::emergencyShutdown('Integridad del sistema comprometida');
        }
    }

    /**
     * Apagado de emergencia
     */
    private static function emergencyShutdown(string $reason): void
    {
        // Log crítico
        error_log("CRITICAL SECURITY ALERT: {$reason}");
        
        // Enviar headers de error
        if (!headers_sent()) {
            http_response_code(503);
            header('Content-Type: text/html; charset=utf-8');
            header('Retry-After: 3600');
        }
        
        echo '<!DOCTYPE html><html><head><title>503</title></head>';
        echo '<body><h1>Service Temporarily Unavailable</h1>';
        echo '<p>The server is under maintenance. Please try again later.</p></body></html>';
        
        exit(1);
    }
}
