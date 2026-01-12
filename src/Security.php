<?php
/**
 * Clase Security - Centraliza todas las funciones de seguridad
 * @package App
 */

namespace App;

class Security
{
    // Constantes de validación
    public const MAX_NOMBRE = 50;
    public const MAX_APELLIDOS = 100;
    public const MAX_EMAIL = 255;
    public const MIN_PASSWORD = 8;
    public const MAX_PASSWORD = 72; // Límite de bcrypt
    
    public const MIN_ALTURA = 0.50;
    public const MAX_ALTURA = 2.50;
    public const MIN_PESO = 1.0;
    public const MAX_PESO = 500.0;
    
    public const MAX_LOGIN_ATTEMPTS = 5;
    public const LOCKOUT_TIME = 900; // 15 minutos en segundos
    
    public const MAX_FILE_SIZE = 2097152; // 2MB en bytes
    public const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    public const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Valida y sanitiza un nombre
     */
    public static function validateNombre(string $nombre): array
    {
        $nombre = trim($nombre);
        
        if (empty($nombre)) {
            return ['valid' => false, 'error' => 'El nombre es obligatorio.', 'value' => ''];
        }
        
        if (mb_strlen($nombre) > self::MAX_NOMBRE) {
            return ['valid' => false, 'error' => 'El nombre no puede exceder ' . self::MAX_NOMBRE . ' caracteres.', 'value' => ''];
        }
        
        // Solo permite letras, espacios, guiones y acentos
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u', $nombre)) {
            return ['valid' => false, 'error' => 'El nombre contiene caracteres no permitidos.', 'value' => ''];
        }
        
        return ['valid' => true, 'error' => '', 'value' => $nombre];
    }

    /**
     * Valida y sanitiza apellidos
     */
    public static function validateApellidos(string $apellidos): array
    {
        $apellidos = trim($apellidos);
        
        if (empty($apellidos)) {
            return ['valid' => false, 'error' => 'Los apellidos son obligatorios.', 'value' => ''];
        }
        
        if (mb_strlen($apellidos) > self::MAX_APELLIDOS) {
            return ['valid' => false, 'error' => 'Los apellidos no pueden exceder ' . self::MAX_APELLIDOS . ' caracteres.', 'value' => ''];
        }
        
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u', $apellidos)) {
            return ['valid' => false, 'error' => 'Los apellidos contienen caracteres no permitidos.', 'value' => ''];
        }
        
        return ['valid' => true, 'error' => '', 'value' => $apellidos];
    }

    /**
     * Valida email
     */
    public static function validateEmail(string $email): array
    {
        $email = trim(strtolower($email));
        
        if (empty($email)) {
            return ['valid' => false, 'error' => 'El email es obligatorio.', 'value' => ''];
        }
        
        if (mb_strlen($email) > self::MAX_EMAIL) {
            return ['valid' => false, 'error' => 'El email no puede exceder ' . self::MAX_EMAIL . ' caracteres.', 'value' => ''];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => 'Formato de email inválido.', 'value' => ''];
        }
        
        // Validación adicional del dominio
        $parts = explode('@', $email);
        if (count($parts) !== 2 || !checkdnsrr($parts[1], 'MX') && !checkdnsrr($parts[1], 'A')) {
            // Solo advertencia, no bloqueamos por DNS
        }
        
        return ['valid' => true, 'error' => '', 'value' => $email];
    }

    /**
     * Valida contraseña con requisitos de complejidad
     */
    public static function validatePassword(string $password): array
    {
        if (empty($password)) {
            return ['valid' => false, 'error' => 'La contraseña es obligatoria.', 'value' => ''];
        }
        
        $length = strlen($password);
        
        if ($length < self::MIN_PASSWORD) {
            return ['valid' => false, 'error' => 'La contraseña debe tener al menos ' . self::MIN_PASSWORD . ' caracteres.', 'value' => ''];
        }
        
        if ($length > self::MAX_PASSWORD) {
            return ['valid' => false, 'error' => 'La contraseña no puede exceder ' . self::MAX_PASSWORD . ' caracteres.', 'value' => ''];
        }
        
        // Requisitos de complejidad
        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'error' => 'La contraseña debe contener al menos una letra minúscula.', 'value' => ''];
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'error' => 'La contraseña debe contener al menos una letra mayúscula.', 'value' => ''];
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'error' => 'La contraseña debe contener al menos un número.', 'value' => ''];
        }
        
        return ['valid' => true, 'error' => '', 'value' => $password];
    }

    /**
     * Valida altura
     */
    public static function validateAltura(mixed $altura): array
    {
        if (!is_numeric($altura)) {
            return ['valid' => false, 'error' => 'La altura debe ser un número.', 'value' => 0];
        }
        
        $altura = (float) $altura;
        
        if ($altura < self::MIN_ALTURA || $altura > self::MAX_ALTURA) {
            return ['valid' => false, 'error' => 'La altura debe estar entre ' . self::MIN_ALTURA . ' y ' . self::MAX_ALTURA . ' metros.', 'value' => 0];
        }
        
        return ['valid' => true, 'error' => '', 'value' => round($altura, 2)];
    }

    /**
     * Valida peso
     */
    public static function validatePeso(mixed $peso): array
    {
        if (!is_numeric($peso)) {
            return ['valid' => false, 'error' => 'El peso debe ser un número.', 'value' => 0];
        }
        
        $peso = (float) $peso;
        
        if ($peso < self::MIN_PESO || $peso > self::MAX_PESO) {
            return ['valid' => false, 'error' => 'El peso debe estar entre ' . self::MIN_PESO . ' y ' . self::MAX_PESO . ' kg.', 'value' => 0];
        }
        
        return ['valid' => true, 'error' => '', 'value' => round($peso, 1)];
    }

    /**
     * Valida fecha (no puede ser futura)
     */
    public static function validateFecha(string $fecha): array
    {
        $fecha = trim($fecha);
        
        if (empty($fecha)) {
            return ['valid' => false, 'error' => 'La fecha es obligatoria.', 'value' => ''];
        }
        
        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        
        if (!$d || $d->format('Y-m-d') !== $fecha) {
            return ['valid' => false, 'error' => 'Formato de fecha inválido (usar AAAA-MM-DD).', 'value' => ''];
        }
        
        $hoy = new \DateTime();
        $hoy->setTime(23, 59, 59);
        
        if ($d > $hoy) {
            return ['valid' => false, 'error' => 'La fecha no puede ser futura.', 'value' => ''];
        }
        
        // No permitir fechas muy antiguas (más de 100 años)
        $minDate = new \DateTime('-100 years');
        if ($d < $minDate) {
            return ['valid' => false, 'error' => 'La fecha es demasiado antigua.', 'value' => ''];
        }
        
        return ['valid' => true, 'error' => '', 'value' => $fecha];
    }

    /**
     * Valida archivo de imagen subido
     */
    public static function validateImageUpload(array $file): array
    {
        // Verificar errores de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor.',
                UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido.',
                UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente.',
                UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo.',
                UPLOAD_ERR_NO_TMP_DIR => 'Error del servidor: falta carpeta temporal.',
                UPLOAD_ERR_CANT_WRITE => 'Error del servidor: no se puede escribir el archivo.',
                UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida.',
            ];
            $error = $errorMessages[$file['error']] ?? 'Error desconocido al subir el archivo.';
            return ['valid' => false, 'error' => $error];
        }
        
        // Verificar tamaño
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['valid' => false, 'error' => 'El archivo no puede exceder 2MB.'];
        }
        
        // Verificar extensión
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS)) {
            return ['valid' => false, 'error' => 'Tipo de archivo no permitido. Use: ' . implode(', ', self::ALLOWED_IMAGE_EXTENSIONS)];
        }
        
        // Verificar tipo MIME real (no confiar en el cliente)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, self::ALLOWED_IMAGE_TYPES)) {
            return ['valid' => false, 'error' => 'El archivo no es una imagen válida.'];
        }
        
        // Verificar que realmente es una imagen
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['valid' => false, 'error' => 'El archivo no es una imagen válida.'];
        }
        
        return ['valid' => true, 'error' => '', 'mime' => $mimeType, 'extension' => $extension];
    }

    /**
     * Genera un nombre seguro para archivo subido
     */
    public static function generateSecureFilename(int $userId, string $extension): string
    {
        return 'user_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    }

    /**
     * Verifica intentos de login (Rate Limiting)
     */
    public static function checkLoginAttempts(string $identifier): array
    {
        $sessionKey = 'login_attempts_' . md5($identifier);
        $lockoutKey = 'login_lockout_' . md5($identifier);
        
        // Verificar si está bloqueado
        if (isset($_SESSION[$lockoutKey])) {
            $remainingTime = $_SESSION[$lockoutKey] - time();
            if ($remainingTime > 0) {
                $minutes = ceil($remainingTime / 60);
                return [
                    'allowed' => false, 
                    'error' => "Demasiados intentos fallidos. Intente de nuevo en {$minutes} minuto(s)."
                ];
            } else {
                // El bloqueo expiró
                unset($_SESSION[$lockoutKey]);
                unset($_SESSION[$sessionKey]);
            }
        }
        
        return ['allowed' => true, 'error' => ''];
    }

    /**
     * Registra un intento de login fallido
     */
    public static function recordFailedLogin(string $identifier): void
    {
        $sessionKey = 'login_attempts_' . md5($identifier);
        $lockoutKey = 'login_lockout_' . md5($identifier);
        
        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = 0;
        }
        
        $_SESSION[$sessionKey]++;
        
        if ($_SESSION[$sessionKey] >= self::MAX_LOGIN_ATTEMPTS) {
            $_SESSION[$lockoutKey] = time() + self::LOCKOUT_TIME;
        }
    }

    /**
     * Resetea los intentos de login tras éxito
     */
    public static function resetLoginAttempts(string $identifier): void
    {
        $sessionKey = 'login_attempts_' . md5($identifier);
        $lockoutKey = 'login_lockout_' . md5($identifier);
        
        unset($_SESSION[$sessionKey]);
        unset($_SESSION[$lockoutKey]);
    }

    /**
     * Sanitiza salida para HTML
     */
    public static function escapeHtml(?string $string): string
    {
        if ($string === null) {
            return '';
        }
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitiza para uso en JavaScript
     */
    public static function escapeJs(?string $string): string
    {
        if ($string === null) {
            return '';
        }
        return addslashes($string);
    }

    /**
     * Genera token CSRF
     */
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Valida token CSRF
     */
    public static function validateCsrfToken(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
