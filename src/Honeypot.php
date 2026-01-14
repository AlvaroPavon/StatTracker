<?php
/**
 * Clase Honeypot - Trampa para detectar bots y atacantes
 * Añade campos ocultos que los bots rellenan automáticamente
 * @package App
 */

namespace App;

class Honeypot
{
    // Nombres de campos honeypot (parecen campos reales)
    private const FIELD_NAMES = [
        'website',
        'url',
        'phone2',
        'fax',
        'company',
        'address2'
    ];
    
    // Tiempo mínimo para enviar un formulario (anti-bot)
    private const MIN_SUBMIT_TIME = 3; // segundos
    
    /**
     * Genera el HTML del honeypot (campos ocultos)
     * @return string HTML de los campos honeypot
     */
    public static function generate(): string
    {
        // Seleccionar un campo aleatorio
        $fieldName = self::FIELD_NAMES[array_rand(self::FIELD_NAMES)];
        
        // Generar timestamp cifrado
        $timestamp = time();
        $token = self::encryptTimestamp($timestamp);
        
        // Guardar el campo usado en sesión para validación
        $_SESSION['_honeypot_field'] = $fieldName;
        $_SESSION['_honeypot_time'] = $timestamp;
        
        // HTML con CSS inline para ocultar (los bots no procesan CSS)
        $html = <<<HTML
<!-- Security fields - do not remove -->
<div style="position:absolute;left:-9999px;top:-9999px;height:0;width:0;overflow:hidden;" aria-hidden="true">
    <label for="{$fieldName}">Leave this empty</label>
    <input type="text" name="{$fieldName}" id="{$fieldName}" value="" tabindex="-1" autocomplete="off">
</div>
<input type="hidden" name="_hp_token" value="{$token}">
<input type="hidden" name="_hp_field" value="{$fieldName}">
HTML;
        
        return $html;
    }
    
    /**
     * Valida el honeypot
     * @return array ['valid' => bool, 'reason' => string]
     */
    public static function validate(): array
    {
        // 1. Verificar que existan las variables de sesión
        if (!isset($_SESSION['_honeypot_field']) || !isset($_SESSION['_honeypot_time'])) {
            return ['valid' => false, 'reason' => 'honeypot_session_missing'];
        }
        
        $fieldName = $_SESSION['_honeypot_field'];
        $submitTime = $_SESSION['_honeypot_time'];
        
        // Limpiar sesión
        unset($_SESSION['_honeypot_field']);
        unset($_SESSION['_honeypot_time']);
        
        // 2. Verificar que el campo honeypot esté vacío
        $honeypotValue = $_POST[$fieldName] ?? '';
        if (!empty($honeypotValue)) {
            // Un bot rellenó el campo oculto
            self::logBotDetected('field_filled', $fieldName);
            return ['valid' => false, 'reason' => 'honeypot_triggered'];
        }
        
        // 3. Verificar el token de tiempo
        $token = $_POST['_hp_token'] ?? '';
        $expectedField = $_POST['_hp_field'] ?? '';
        
        if (empty($token) || $expectedField !== $fieldName) {
            self::logBotDetected('token_mismatch', $fieldName);
            return ['valid' => false, 'reason' => 'honeypot_token_invalid'];
        }
        
        $decryptedTime = self::decryptTimestamp($token);
        if ($decryptedTime === null || $decryptedTime !== $submitTime) {
            self::logBotDetected('timestamp_mismatch', $fieldName);
            return ['valid' => false, 'reason' => 'honeypot_timestamp_invalid'];
        }
        
        // 4. Verificar tiempo mínimo de envío
        $elapsed = time() - $submitTime;
        if ($elapsed < self::MIN_SUBMIT_TIME) {
            // Formulario enviado demasiado rápido (probable bot)
            self::logBotDetected('too_fast', "elapsed: {$elapsed}s");
            return ['valid' => false, 'reason' => 'form_submitted_too_fast'];
        }
        
        return ['valid' => true, 'reason' => ''];
    }
    
    /**
     * Versión simplificada que solo retorna bool
     */
    public static function isValid(): bool
    {
        return self::validate()['valid'];
    }
    
    /**
     * Cifra el timestamp
     */
    private static function encryptTimestamp(int $timestamp): string
    {
        $key = self::getKey();
        $data = $timestamp . '|' . bin2hex(random_bytes(8));
        
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Descifra el timestamp
     */
    private static function decryptTimestamp(string $token): ?int
    {
        try {
            $key = self::getKey();
            $decoded = base64_decode($token);
            
            if (strlen($decoded) < 16) {
                return null;
            }
            
            $iv = substr($decoded, 0, 16);
            $encrypted = substr($decoded, 16);
            
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
            
            if ($decrypted === false) {
                return null;
            }
            
            $parts = explode('|', $decrypted);
            return (int) $parts[0];
            
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Obtiene o genera la clave de cifrado
     */
    private static function getKey(): string
    {
        // En producción, esto debería venir de una variable de entorno
        // Por ahora usamos una clave basada en el directorio de la aplicación
        $baseKey = __DIR__ . '|STATTRACKER_HONEYPOT_2025';
        return hash('sha256', $baseKey, true);
    }
    
    /**
     * Registra detección de bot
     */
    private static function logBotDetected(string $type, string $details): void
    {
        SecurityAudit::log(
            'BOT_DETECTED',
            $_SESSION['user_id'] ?? null,
            [
                'type' => $type,
                'details' => $details,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200)
            ],
            'WARNING'
        );
    }
    
    /**
     * Genera JavaScript adicional anti-bot
     * (Verifica que JavaScript esté habilitado)
     */
    public static function generateJsCheck(): string
    {
        $token = bin2hex(random_bytes(16));
        $_SESSION['_js_token'] = $token;
        
        return <<<HTML
<script>
(function() {
    var f = document.querySelector('form');
    if (f) {
        var i = document.createElement('input');
        i.type = 'hidden';
        i.name = '_js_check';
        i.value = '{$token}';
        f.appendChild(i);
    }
})();
</script>
HTML;
    }
    
    /**
     * Valida que JavaScript esté habilitado
     */
    public static function validateJsCheck(): bool
    {
        $expected = $_SESSION['_js_token'] ?? null;
        $received = $_POST['_js_check'] ?? null;
        
        unset($_SESSION['_js_token']);
        
        if ($expected === null || $received === null) {
            return false;
        }
        
        return hash_equals($expected, $received);
    }
}
