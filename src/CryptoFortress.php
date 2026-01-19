<?php
/**
 * Clase CryptoFortress - Criptografía de máximo nivel
 * Implementa múltiples capas de protección criptográfica
 * Resistente a ataques cuánticos (preparación)
 * @package App
 */

namespace App;

class CryptoFortress
{
    // ==================== CONFIGURACIÓN ====================
    
    // Algoritmo de hash principal (más fuerte disponible)
    // SHA3-512 si está disponible, sino SHA-512
    private const HASH_ALGO_PRIMARY = 'sha3-512';
    private const HASH_ALGO_FALLBACK = 'sha512';
    
    // Algoritmo de cifrado simétrico
    private const CIPHER_ALGO = 'aes-256-gcm';
    
    // Longitud del IV para AES-GCM
    private const IV_LENGTH = 12;
    
    // Longitud del tag de autenticación
    private const TAG_LENGTH = 16;
    
    // Iteraciones para PBKDF2
    private const PBKDF2_ITERATIONS = 600000; // OWASP 2023 recomienda 600k para SHA-256
    
    // Costo de Argon2id (si está disponible)
    private const ARGON2_MEMORY = 65536;  // 64 MB
    private const ARGON2_TIME = 4;
    private const ARGON2_THREADS = 4;
    
    // Pepper secreto (almacenar en variable de entorno en producción)
    private const PEPPER = 'ST4tTr4ck3r_P3pp3r_2025_$uP3r_$3cr3t!';
    
    // Salt adicional para operaciones (único por instalación)
    private static ?string $installationSalt = null;

    /**
     * Obtiene el algoritmo de hash disponible
     */
    private static function getHashAlgo(): string
    {
        if (in_array(self::HASH_ALGO_PRIMARY, hash_algos())) {
            return self::HASH_ALGO_PRIMARY;
        }
        return self::HASH_ALGO_FALLBACK;
    }

    // ==================== HASHING DE CONTRASEÑAS ====================

    /**
     * Hash de contraseña con máxima seguridad
     * Usa Argon2id si está disponible, sino bcrypt con costo alto
     */
    public static function hashPassword(string $password): string
    {
        // Añadir pepper a la contraseña
        $pepperedPassword = self::applyPepper($password);
        
        // Intentar usar Argon2id (más resistente a GPU/ASIC)
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($pepperedPassword, PASSWORD_ARGON2ID, [
                'memory_cost' => self::ARGON2_MEMORY,
                'time_cost' => self::ARGON2_TIME,
                'threads' => self::ARGON2_THREADS
            ]);
        }
        
        // Fallback a bcrypt con costo máximo práctico
        return password_hash($pepperedPassword, PASSWORD_BCRYPT, [
            'cost' => 14  // 2^14 iteraciones
        ]);
    }

    /**
     * Verifica contraseña con timing constante
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        $startTime = hrtime(true);
        
        // Aplicar pepper
        $pepperedPassword = self::applyPepper($password);
        
        // Verificar
        $result = password_verify($pepperedPassword, $hash);
        
        // Asegurar tiempo mínimo de 250ms para dificultar timing attacks
        $elapsed = (hrtime(true) - $startTime) / 1e6; // Convertir a milisegundos
        if ($elapsed < 250) {
            usleep((int)((250 - $elapsed) * 1000));
        }
        
        return $result;
    }

    /**
     * Verifica si el hash necesita ser actualizado (rehash)
     */
    public static function needsRehash(string $hash): bool
    {
        // Verificar si se debe migrar a Argon2id
        if (defined('PASSWORD_ARGON2ID')) {
            return password_needs_rehash($hash, PASSWORD_ARGON2ID, [
                'memory_cost' => self::ARGON2_MEMORY,
                'time_cost' => self::ARGON2_TIME,
                'threads' => self::ARGON2_THREADS
            ]);
        }
        
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 14]);
    }

    /**
     * Aplica pepper a la contraseña
     */
    private static function applyPepper(string $password): string
    {
        // HMAC con pepper para añadir entropía
        return hash_hmac('sha256', $password, self::PEPPER);
    }

    // ==================== HASHING GENERAL ====================

    /**
     * Hash seguro de datos arbitrarios
     * Usa SHA3-512 con salt
     */
    public static function hash(string $data, bool $withSalt = true): string
    {
        if ($withSalt) {
            $salt = self::getInstallationSalt();
            $data = $salt . $data . $salt;
        }
        
        // Doble hash para mayor seguridad
        $firstPass = hash(self::HASH_ALGO, $data, true);
        return hash(self::HASH_ALGO, $firstPass);
    }

    /**
     * HMAC seguro
     */
    public static function hmac(string $data, string $key): string
    {
        // Derivar clave más fuerte
        $derivedKey = self::deriveKey($key, 64);
        
        return hash_hmac(self::HASH_ALGO, $data, $derivedKey);
    }

    /**
     * Verifica HMAC en tiempo constante
     */
    public static function verifyHmac(string $data, string $key, string $expectedHmac): bool
    {
        $actualHmac = self::hmac($data, $key);
        return hash_equals($expectedHmac, $actualHmac);
    }

    // ==================== DERIVACIÓN DE CLAVES ====================

    /**
     * Deriva una clave segura usando PBKDF2 o Argon2
     */
    public static function deriveKey(string $password, int $length = 32, ?string $salt = null): string
    {
        $salt = $salt ?? self::getInstallationSalt();
        
        // Intentar usar Argon2 para derivación si está disponible
        if (function_exists('sodium_crypto_pwhash')) {
            return sodium_crypto_pwhash(
                $length,
                $password,
                str_pad($salt, SODIUM_CRYPTO_PWHASH_SALTBYTES, "\0"),
                SODIUM_CRYPTO_PWHASH_OPSLIMIT_SENSITIVE,
                SODIUM_CRYPTO_PWHASH_MEMLIMIT_SENSITIVE,
                SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13
            );
        }
        
        // Fallback a PBKDF2
        return hash_pbkdf2('sha512', $password, $salt, self::PBKDF2_ITERATIONS, $length, true);
    }

    // ==================== CIFRADO SIMÉTRICO ====================

    /**
     * Cifra datos con AES-256-GCM (autenticado)
     */
    public static function encrypt(string $plaintext, string $key): string
    {
        // Derivar clave de cifrado
        $encKey = self::deriveKey($key, 32, 'encryption');
        
        // Generar IV aleatorio
        $iv = random_bytes(self::IV_LENGTH);
        
        // Cifrar con AES-256-GCM
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER_ALGO,
            $encKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );
        
        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed');
        }
        
        // Formato: IV + TAG + CIPHERTEXT (todo en base64)
        return base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Descifra datos
     */
    public static function decrypt(string $encryptedData, string $key): string
    {
        // Derivar clave de cifrado
        $encKey = self::deriveKey($key, 32, 'encryption');
        
        // Decodificar
        $data = base64_decode($encryptedData, true);
        if ($data === false) {
            throw new \RuntimeException('Invalid encrypted data');
        }
        
        // Extraer componentes
        $iv = substr($data, 0, self::IV_LENGTH);
        $tag = substr($data, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($data, self::IV_LENGTH + self::TAG_LENGTH);
        
        // Descifrar
        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER_ALGO,
            $encKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        if ($plaintext === false) {
            throw new \RuntimeException('Decryption failed - data may be tampered');
        }
        
        return $plaintext;
    }

    // ==================== TOKENS SEGUROS ====================

    /**
     * Genera un token criptográficamente seguro
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Genera un token firmado (no puede ser falsificado)
     */
    public static function generateSignedToken(string $data, int $expiry = 3600): string
    {
        $payload = [
            'data' => $data,
            'exp' => time() + $expiry,
            'jti' => self::generateToken(16)  // ID único
        ];
        
        $payloadJson = json_encode($payload);
        $signature = self::hmac($payloadJson, self::getSigningKey());
        
        return base64_encode($payloadJson) . '.' . $signature;
    }

    /**
     * Verifica y extrae datos de un token firmado
     */
    public static function verifySignedToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }
        
        [$encodedPayload, $signature] = $parts;
        
        $payloadJson = base64_decode($encodedPayload, true);
        if ($payloadJson === false) {
            return null;
        }
        
        // Verificar firma
        if (!self::verifyHmac($payloadJson, self::getSigningKey(), $signature)) {
            return null;
        }
        
        $payload = json_decode($payloadJson, true);
        if (!$payload) {
            return null;
        }
        
        // Verificar expiración
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }
        
        return $payload;
    }

    // ==================== UTILIDADES ====================

    /**
     * Obtiene el salt de instalación (único por instalación)
     */
    private static function getInstallationSalt(): string
    {
        if (self::$installationSalt === null) {
            $saltFile = __DIR__ . '/../logs/.installation_salt';
            
            if (file_exists($saltFile)) {
                self::$installationSalt = file_get_contents($saltFile);
            } else {
                // Generar nuevo salt
                self::$installationSalt = bin2hex(random_bytes(32));
                
                $dir = dirname($saltFile);
                if (!is_dir($dir)) {
                    mkdir($dir, 0750, true);
                }
                
                file_put_contents($saltFile, self::$installationSalt, LOCK_EX);
                chmod($saltFile, 0600);
            }
        }
        
        return self::$installationSalt;
    }

    /**
     * Obtiene la clave de firma
     */
    private static function getSigningKey(): string
    {
        return self::deriveKey(self::PEPPER . self::getInstallationSalt(), 64, 'signing');
    }

    /**
     * Verifica que las funciones criptográficas están disponibles y funcionan
     */
    public static function verifyCryptoIntegrity(): array
    {
        $result = [
            'valid' => true,
            'errors' => [],
            'warnings' => []
        ];
        
        // Verificar extensiones necesarias
        if (!extension_loaded('openssl')) {
            $result['valid'] = false;
            $result['errors'][] = 'OpenSSL extension not loaded';
        }
        
        // Verificar algoritmos disponibles
        if (!in_array(self::CIPHER_ALGO, openssl_get_cipher_methods())) {
            $result['valid'] = false;
            $result['errors'][] = 'AES-256-GCM not available';
        }
        
        if (!in_array(self::getHashAlgo(), hash_algos())) {
            $result['valid'] = false;
            $result['errors'][] = 'No secure hash algorithm available';
        }
        
        // Advertencia si SHA3-512 no está disponible
        if (!in_array(self::HASH_ALGO_PRIMARY, hash_algos())) {
            $result['warnings'][] = 'SHA3-512 not available - using SHA-512';
        }
        
        // Verificar random_bytes funciona
        try {
            $random = random_bytes(32);
            if (strlen($random) !== 32) {
                $result['valid'] = false;
                $result['errors'][] = 'random_bytes not working correctly';
            }
        } catch (\Exception $e) {
            $result['valid'] = false;
            $result['errors'][] = 'random_bytes failed: ' . $e->getMessage();
        }
        
        // Verificar password_hash funciona
        try {
            $hash = password_hash('test', PASSWORD_BCRYPT);
            if (!password_verify('test', $hash)) {
                $result['valid'] = false;
                $result['errors'][] = 'password_hash/verify not working';
            }
        } catch (\Exception $e) {
            $result['valid'] = false;
            $result['errors'][] = 'password functions failed';
        }
        
        // Advertencias sobre configuración subóptima
        if (!defined('PASSWORD_ARGON2ID')) {
            $result['warnings'][] = 'Argon2id not available - using bcrypt';
        }
        
        if (!function_exists('sodium_crypto_pwhash')) {
            $result['warnings'][] = 'Sodium not available - using PBKDF2';
        }
        
        // Verificar entropía del sistema
        if (function_exists('random_int')) {
            try {
                $entropy = [];
                for ($i = 0; $i < 1000; $i++) {
                    $entropy[] = random_int(0, 255);
                }
                $uniqueValues = count(array_unique($entropy));
                if ($uniqueValues < 200) {
                    $result['warnings'][] = 'Low entropy detected in random number generator';
                }
            } catch (\Exception $e) {
                $result['warnings'][] = 'Could not test entropy';
            }
        }
        
        if (!$result['valid']) {
            SecurityAudit::log('CRYPTO_INTEGRITY_FAILURE', null, $result, 'CRITICAL');
        }
        
        return $result;
    }

    /**
     * Compara dos strings en tiempo constante
     */
    public static function constantTimeEquals(string $a, string $b): bool
    {
        return hash_equals($a, $b);
    }

    /**
     * Limpia datos sensibles de memoria
     */
    public static function secureClear(string &$data): void
    {
        if (function_exists('sodium_memzero')) {
            sodium_memzero($data);
        } else {
            // Sobrescribir con datos aleatorios
            $length = strlen($data);
            $data = str_repeat("\0", $length);
            $data = random_bytes($length);
            $data = '';
        }
    }
}
