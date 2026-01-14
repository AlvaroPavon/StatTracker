<?php
/**
 * Clase FileIntegrityChecker - Verifica integridad de archivos del sistema
 * Detecta modificaciones no autorizadas
 * @package App
 */

namespace App;

class FileIntegrityChecker
{
    private const HASH_FILE = __DIR__ . '/../logs/.file_hashes.json';
    private const CRITICAL_FILES = [
        'security_init.php',
        'database_connection.php',
        'db.php',
        'login.php',
        'register.php',
        'logout.php',
        'src/Security.php',
        'src/SecurityFirewall.php',
        'src/SecurityHeaders.php',
        'src/SessionManager.php',
        'src/Auth.php',
        'src/UltimateShield.php',
        '.htaccess'
    ];

    /**
     * Genera hashes de archivos críticos
     */
    public static function generateHashes(): array
    {
        $hashes = [];
        $basePath = dirname(__DIR__);
        
        foreach (self::CRITICAL_FILES as $file) {
            $fullPath = $basePath . '/' . $file;
            if (file_exists($fullPath)) {
                $hashes[$file] = [
                    'hash' => hash_file('sha256', $fullPath),
                    'size' => filesize($fullPath),
                    'mtime' => filemtime($fullPath)
                ];
            }
        }
        
        return $hashes;
    }

    /**
     * Guarda hashes actuales
     */
    public static function saveHashes(): bool
    {
        $hashes = self::generateHashes();
        $hashes['_generated'] = date('Y-m-d H:i:s');
        $hashes['_version'] = '1.0';
        
        $dir = dirname(self::HASH_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        
        return file_put_contents(
            self::HASH_FILE,
            json_encode($hashes, JSON_PRETTY_PRINT),
            LOCK_EX
        ) !== false;
    }

    /**
     * Verifica integridad de archivos
     */
    public static function verify(): array
    {
        $result = [
            'valid' => true,
            'modified' => [],
            'missing' => [],
            'new' => []
        ];
        
        // Cargar hashes guardados
        if (!file_exists(self::HASH_FILE)) {
            // Primera vez - generar hashes
            self::saveHashes();
            return $result;
        }
        
        $savedHashes = json_decode(file_get_contents(self::HASH_FILE), true);
        if (!$savedHashes) {
            return $result;
        }
        
        $currentHashes = self::generateHashes();
        
        // Comparar archivos
        foreach (self::CRITICAL_FILES as $file) {
            if (!isset($savedHashes[$file])) {
                if (isset($currentHashes[$file])) {
                    $result['new'][] = $file;
                }
                continue;
            }
            
            if (!isset($currentHashes[$file])) {
                $result['missing'][] = $file;
                $result['valid'] = false;
                continue;
            }
            
            if ($savedHashes[$file]['hash'] !== $currentHashes[$file]['hash']) {
                $result['modified'][] = [
                    'file' => $file,
                    'old_hash' => substr($savedHashes[$file]['hash'], 0, 16) . '...',
                    'new_hash' => substr($currentHashes[$file]['hash'], 0, 16) . '...',
                    'old_size' => $savedHashes[$file]['size'],
                    'new_size' => $currentHashes[$file]['size']
                ];
                $result['valid'] = false;
            }
        }
        
        // Si hay modificaciones, registrar
        if (!$result['valid']) {
            SecurityAudit::log('FILE_INTEGRITY_VIOLATION', null, $result, 'CRITICAL');
        }
        
        return $result;
    }

    /**
     * Verifica y bloquea si hay modificaciones
     */
    public static function verifyOrDie(): void
    {
        $result = self::verify();
        
        if (!$result['valid']) {
            // Modificación detectada - bloquear sistema
            http_response_code(503);
            error_log('CRITICAL: File integrity violation detected!');
            exit('System maintenance in progress');
        }
    }
}
