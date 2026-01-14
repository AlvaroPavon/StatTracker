<?php
/**
 * Clase TwoFactorAuth - Autenticación de dos factores
 * Implementa TOTP (Time-based One-Time Password) RFC 6238
 * @package App
 */

namespace App;

class TwoFactorAuth
{
    private const SECRET_LENGTH = 20; // 160 bits
    private const CODE_LENGTH = 6;
    private const TIME_STEP = 30; // 30 segundos
    private const ALLOWED_DRIFT = 1; // Permitir 1 paso de tiempo de diferencia
    private const RECOVERY_CODES_COUNT = 10;
    
    private const SECRETS_FILE = __DIR__ . '/../logs/.2fa_secrets.enc';
    private const USED_CODES_FILE = __DIR__ . '/../logs/.2fa_used_codes.json';

    /**
     * Genera un nuevo secreto para 2FA
     */
    public static function generateSecret(): string
    {
        $randomBytes = random_bytes(self::SECRET_LENGTH);
        return self::base32Encode($randomBytes);
    }

    /**
     * Genera el URI para QR code (Google Authenticator, Authy, etc.)
     */
    public static function getQRCodeUri(string $secret, string $accountName, string $issuer = 'StatTracker'): string
    {
        $params = [
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::CODE_LENGTH,
            'period' => self::TIME_STEP
        ];
        
        return 'otpauth://totp/' . urlencode($issuer) . ':' . urlencode($accountName) 
             . '?' . http_build_query($params);
    }

    /**
     * Verifica un código TOTP
     */
    public static function verifyCode(string $secret, string $code, int $userId): bool
    {
        // Limpiar código
        $code = preg_replace('/[^0-9]/', '', $code);
        
        if (strlen($code) !== self::CODE_LENGTH) {
            return false;
        }
        
        // Verificar que el código no se ha usado antes (anti-replay)
        if (self::isCodeUsed($userId, $code)) {
            SecurityAudit::log('2FA_CODE_REUSE_ATTEMPT', $userId, [], 'WARNING');
            return false;
        }
        
        // Decodificar secreto
        $secretBytes = self::base32Decode($secret);
        if ($secretBytes === false) {
            return false;
        }
        
        // Obtener tiempo actual
        $timestamp = time();
        $timeSlice = floor($timestamp / self::TIME_STEP);
        
        // Verificar código actual y códigos adyacentes (por drift de tiempo)
        for ($i = -self::ALLOWED_DRIFT; $i <= self::ALLOWED_DRIFT; $i++) {
            $calculatedCode = self::generateCode($secretBytes, $timeSlice + $i);
            
            if (hash_equals($calculatedCode, $code)) {
                // Marcar código como usado
                self::markCodeUsed($userId, $code);
                return true;
            }
        }
        
        return false;
    }

    /**
     * Genera un código TOTP
     */
    private static function generateCode(string $secretBytes, int $timeSlice): string
    {
        // Convertir time slice a bytes (8 bytes, big endian)
        $timeBytes = pack('J', $timeSlice);
        
        // Calcular HMAC-SHA1
        $hash = hash_hmac('sha1', $timeBytes, $secretBytes, true);
        
        // Dynamic truncation
        $offset = ord($hash[19]) & 0x0f;
        $binary = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        );
        
        // Generar código de N dígitos
        $code = $binary % pow(10, self::CODE_LENGTH);
        
        return str_pad((string)$code, self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Genera códigos de recuperación
     */
    public static function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < self::RECOVERY_CODES_COUNT; $i++) {
            // Formato: XXXX-XXXX-XXXX
            $code = strtoupper(bin2hex(random_bytes(2))) . '-' 
                  . strtoupper(bin2hex(random_bytes(2))) . '-'
                  . strtoupper(bin2hex(random_bytes(2)));
            $codes[] = $code;
        }
        return $codes;
    }

    /**
     * Verifica un código de recuperación
     */
    public static function verifyRecoveryCode(int $userId, string $code, array $storedHashes): ?int
    {
        $code = strtoupper(trim($code));
        
        foreach ($storedHashes as $index => $hash) {
            if (password_verify($code, $hash)) {
                return $index; // Retorna índice para que se pueda eliminar
            }
        }
        
        return null;
    }

    /**
     * Hashea códigos de recuperación para almacenamiento seguro
     */
    public static function hashRecoveryCodes(array $codes): array
    {
        return array_map(fn($code) => password_hash($code, PASSWORD_BCRYPT), $codes);
    }

    /**
     * Habilita 2FA para un usuario
     */
    public static function enable(int $userId, string $secret, array $recoveryCodes): bool
    {
        $data = self::loadSecrets();
        
        $data[$userId] = [
            'secret' => CryptoFortress::encrypt($secret, self::getEncryptionKey()),
            'recovery_codes' => self::hashRecoveryCodes($recoveryCodes),
            'enabled_at' => time(),
            'last_used' => null
        ];
        
        return self::saveSecrets($data);
    }

    /**
     * Verifica si un usuario tiene 2FA habilitado
     */
    public static function isEnabled(int $userId): bool
    {
        $data = self::loadSecrets();
        return isset($data[$userId]) && !empty($data[$userId]['secret']);
    }

    /**
     * Obtiene el secreto de un usuario
     */
    public static function getSecret(int $userId): ?string
    {
        $data = self::loadSecrets();
        
        if (!isset($data[$userId]['secret'])) {
            return null;
        }
        
        try {
            return CryptoFortress::decrypt($data[$userId]['secret'], self::getEncryptionKey());
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Deshabilita 2FA para un usuario
     */
    public static function disable(int $userId): bool
    {
        $data = self::loadSecrets();
        
        if (isset($data[$userId])) {
            unset($data[$userId]);
            return self::saveSecrets($data);
        }
        
        return true;
    }

    /**
     * Usa un código de recuperación
     */
    public static function useRecoveryCode(int $userId, string $code): bool
    {
        $data = self::loadSecrets();
        
        if (!isset($data[$userId]['recovery_codes'])) {
            return false;
        }
        
        $index = self::verifyRecoveryCode($userId, $code, $data[$userId]['recovery_codes']);
        
        if ($index !== null) {
            // Eliminar código usado
            array_splice($data[$userId]['recovery_codes'], $index, 1);
            self::saveSecrets($data);
            
            SecurityAudit::log('2FA_RECOVERY_CODE_USED', $userId, [
                'remaining_codes' => count($data[$userId]['recovery_codes'])
            ], 'WARNING');
            
            return true;
        }
        
        return false;
    }

    // ==================== HELPERS ====================

    private static function isCodeUsed(int $userId, string $code): bool
    {
        $data = self::loadUsedCodes();
        $key = $userId . ':' . $code;
        
        // Limpiar códigos antiguos (más de 2 minutos)
        $data = array_filter($data, fn($ts) => time() - $ts < 120);
        self::saveUsedCodes($data);
        
        return isset($data[$key]);
    }

    private static function markCodeUsed(int $userId, string $code): void
    {
        $data = self::loadUsedCodes();
        $key = $userId . ':' . $code;
        $data[$key] = time();
        self::saveUsedCodes($data);
    }

    private static function loadUsedCodes(): array
    {
        if (!file_exists(self::USED_CODES_FILE)) {
            return [];
        }
        $data = json_decode(file_get_contents(self::USED_CODES_FILE), true);
        return is_array($data) ? $data : [];
    }

    private static function saveUsedCodes(array $data): void
    {
        $dir = dirname(self::USED_CODES_FILE);
        if (!is_dir($dir)) mkdir($dir, 0750, true);
        file_put_contents(self::USED_CODES_FILE, json_encode($data), LOCK_EX);
    }

    private static function loadSecrets(): array
    {
        if (!file_exists(self::SECRETS_FILE)) {
            return [];
        }
        
        $encrypted = file_get_contents(self::SECRETS_FILE);
        try {
            $decrypted = CryptoFortress::decrypt($encrypted, self::getEncryptionKey());
            $data = json_decode($decrypted, true);
            return is_array($data) ? $data : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private static function saveSecrets(array $data): bool
    {
        $dir = dirname(self::SECRETS_FILE);
        if (!is_dir($dir)) mkdir($dir, 0750, true);
        
        $json = json_encode($data);
        $encrypted = CryptoFortress::encrypt($json, self::getEncryptionKey());
        
        return file_put_contents(self::SECRETS_FILE, $encrypted, LOCK_EX) !== false;
    }

    private static function getEncryptionKey(): string
    {
        return hash('sha256', 'STATTRACKER_2FA_KEY_2025_' . __DIR__);
    }

    /**
     * Base32 encoding (RFC 4648)
     */
    private static function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        
        for ($i = 0; $i < strlen($data); $i++) {
            $binary .= str_pad(decbin(ord($data[$i])), 8, '0', STR_PAD_LEFT);
        }
        
        $result = '';
        $chunks = str_split($binary, 5);
        
        foreach ($chunks as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $result .= $alphabet[bindec($chunk)];
        }
        
        // Padding
        $padding = [0, 6, 4, 3, 1];
        $padLength = $padding[strlen($data) % 5];
        $result .= str_repeat('=', $padLength);
        
        return $result;
    }

    /**
     * Base32 decoding
     */
    private static function base32Decode(string $data): string|false
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $data = strtoupper(rtrim($data, '='));
        
        $binary = '';
        for ($i = 0; $i < strlen($data); $i++) {
            $pos = strpos($alphabet, $data[$i]);
            if ($pos === false) {
                return false;
            }
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        
        $result = '';
        $chunks = str_split($binary, 8);
        
        foreach ($chunks as $chunk) {
            if (strlen($chunk) === 8) {
                $result .= chr(bindec($chunk));
            }
        }
        
        return $result;
    }
}
