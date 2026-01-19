<?php
/**
 * Clase SimpleCaptcha - CAPTCHA matemático simple
 * No requiere servicios externos (reCAPTCHA, hCaptcha, etc.)
 * @package App
 */

namespace App;

class SimpleCaptcha
{
    // Configuración
    private const SESSION_KEY = '_captcha_answer';
    private const SESSION_TIME_KEY = '_captcha_time';
    private const EXPIRY_TIME = 300; // 5 minutos de validez
    private const MAX_NUMBER = 20;   // Números máximos para operaciones
    
    // Tipos de operaciones
    private const OPERATIONS = ['sum', 'subtract', 'multiply'];
    
    /**
     * Genera un nuevo CAPTCHA matemático
     * @return array ['question' => string, 'html' => string]
     */
    public static function generate(): array
    {
        $operation = self::OPERATIONS[array_rand(self::OPERATIONS)];
        
        switch ($operation) {
            case 'sum':
                $num1 = random_int(1, self::MAX_NUMBER);
                $num2 = random_int(1, self::MAX_NUMBER);
                $answer = $num1 + $num2;
                $question = "¿Cuánto es {$num1} + {$num2}?";
                $questionShort = "{$num1} + {$num2} = ";
                break;
                
            case 'subtract':
                $num1 = random_int(10, self::MAX_NUMBER);
                $num2 = random_int(1, $num1); // Asegurar resultado positivo
                $answer = $num1 - $num2;
                $question = "¿Cuánto es {$num1} - {$num2}?";
                $questionShort = "{$num1} - {$num2} = ";
                break;
                
            case 'multiply':
                $num1 = random_int(2, 10);
                $num2 = random_int(2, 10);
                $answer = $num1 * $num2;
                $question = "¿Cuánto es {$num1} × {$num2}?";
                $questionShort = "{$num1} × {$num2} = ";
                break;
                
            default:
                $num1 = random_int(1, 10);
                $num2 = random_int(1, 10);
                $answer = $num1 + $num2;
                $question = "¿Cuánto es {$num1} + {$num2}?";
                $questionShort = "{$num1} + {$num2} = ";
        }
        
        // Guardar respuesta en sesión (cifrada)
        $_SESSION[self::SESSION_KEY] = self::encryptAnswer($answer);
        $_SESSION[self::SESSION_TIME_KEY] = time();
        
        // Generar HTML del campo
        $html = self::generateHtml($questionShort);
        
        return [
            'question' => $question,
            'questionShort' => $questionShort,
            'html' => $html
        ];
    }
    
    /**
     * Genera el HTML del campo CAPTCHA
     */
    private static function generateHtml(string $question): string
    {
        return <<<HTML
<div class="captcha-container">
    <label class="flex flex-col w-full">
        <p class="text-base font-medium leading-normal pb-2">
            <span class="material-symbols-outlined text-primary align-middle text-lg">lock</span>
            Verificación de seguridad
        </p>
        <div class="flex items-center gap-3">
            <span class="text-lg font-bold text-gray-700 dark:text-gray-300 whitespace-nowrap">{$question}</span>
            <input type="number" 
                   name="_captcha_answer" 
                   id="captcha_answer"
                   class="glass-input flex w-24 min-w-0 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-400 p-3 text-base font-normal text-center transition-all duration-300"
                   placeholder="?"
                   required
                   autocomplete="off"
                   min="0"
                   max="999" />
        </div>
        <p class="text-xs text-gray-500 mt-1">Resuelve la operación matemática</p>
    </label>
</div>
HTML;
    }
    
    /**
     * Valida la respuesta del CAPTCHA
     * @param mixed $userAnswer Respuesta del usuario
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validate($userAnswer = null): array
    {
        // Obtener respuesta del POST si no se proporciona
        if ($userAnswer === null) {
            $userAnswer = $_POST['_captcha_answer'] ?? null;
        }
        
        // Verificar que existe la respuesta en sesión
        if (!isset($_SESSION[self::SESSION_KEY]) || !isset($_SESSION[self::SESSION_TIME_KEY])) {
            return [
                'valid' => false,
                'error' => 'Verificación de seguridad no encontrada. Por favor, recargue la página.'
            ];
        }
        
        $encryptedAnswer = $_SESSION[self::SESSION_KEY];
        $captchaTime = $_SESSION[self::SESSION_TIME_KEY];
        
        // Limpiar sesión después de validar (un solo uso)
        unset($_SESSION[self::SESSION_KEY]);
        unset($_SESSION[self::SESSION_TIME_KEY]);
        
        // Verificar expiración
        if (time() - $captchaTime > self::EXPIRY_TIME) {
            return [
                'valid' => false,
                'error' => 'La verificación ha expirado. Por favor, inténtelo de nuevo.'
            ];
        }
        
        // Verificar que el usuario proporcionó una respuesta
        if ($userAnswer === null || $userAnswer === '') {
            return [
                'valid' => false,
                'error' => 'Por favor, resuelva la operación matemática.'
            ];
        }
        
        // Desencriptar y comparar
        $correctAnswer = self::decryptAnswer($encryptedAnswer);
        $userAnswerInt = (int) $userAnswer;
        
        if ($correctAnswer !== null && $userAnswerInt === $correctAnswer) {
            return ['valid' => true, 'error' => ''];
        }
        
        // Respuesta incorrecta
        SecurityAudit::log('CAPTCHA_FAILED', null, [
            'expected' => $correctAnswer,
            'received' => $userAnswerInt,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ], 'WARNING');
        
        return [
            'valid' => false,
            'error' => 'Respuesta incorrecta. Por favor, inténtelo de nuevo.'
        ];
    }
    
    /**
     * Versión simplificada que solo retorna bool
     */
    public static function isValid($userAnswer = null): bool
    {
        return self::validate($userAnswer)['valid'];
    }
    
    /**
     * Cifra la respuesta para almacenarla en sesión
     */
    private static function encryptAnswer(int $answer): string
    {
        $key = self::getKey();
        $data = $answer . '|' . bin2hex(random_bytes(8));
        
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Descifra la respuesta
     */
    private static function decryptAnswer(string $encrypted): ?int
    {
        try {
            $key = self::getKey();
            $decoded = base64_decode($encrypted);
            
            if (strlen($decoded) < 16) {
                return null;
            }
            
            $iv = substr($decoded, 0, 16);
            $ciphertext = substr($decoded, 16);
            
            $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, 0, $iv);
            
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
     * Obtiene la clave de cifrado
     */
    private static function getKey(): string
    {
        $baseKey = __DIR__ . '|STATTRACKER_CAPTCHA_2025';
        return hash('sha256', $baseKey, true);
    }
    
    /**
     * Genera un CAPTCHA de imagen simple (alternativa)
     * Requiere GD library
     */
    public static function generateImage(): ?array
    {
        if (!extension_loaded('gd')) {
            return null;
        }
        
        // Generar código aleatorio
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= chr(random_int(65, 90)); // A-Z
        }
        
        // Guardar en sesión
        $_SESSION['_captcha_image_code'] = strtoupper($code);
        $_SESSION[self::SESSION_TIME_KEY] = time();
        
        // Crear imagen
        $width = 150;
        $height = 50;
        $image = imagecreatetruecolor($width, $height);
        
        // Colores
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        $noiseColor = imagecolorallocate($image, 100, 100, 100);
        
        // Fondo
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
        
        // Añadir ruido (líneas)
        for ($i = 0; $i < 5; $i++) {
            imageline($image, 
                random_int(0, $width), random_int(0, $height),
                random_int(0, $width), random_int(0, $height),
                $noiseColor
            );
        }
        
        // Añadir texto
        $font = 5; // Fuente incorporada
        $x = 20;
        for ($i = 0; $i < strlen($code); $i++) {
            $y = random_int(10, 25);
            imagestring($image, $font, $x, $y, $code[$i], $textColor);
            $x += 20;
        }
        
        // Convertir a base64
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return [
            'image' => 'data:image/png;base64,' . base64_encode($imageData),
            'html' => self::generateImageHtml()
        ];
    }
    
    /**
     * HTML para CAPTCHA de imagen
     */
    private static function generateImageHtml(): string
    {
        return <<<HTML
<div class="captcha-image-container">
    <label class="flex flex-col w-full">
        <p class="text-base font-medium leading-normal pb-2">Escribe el código de la imagen</p>
        <input type="text" 
               name="_captcha_image_answer" 
               class="glass-input h-12 p-3"
               placeholder="Código"
               required
               autocomplete="off"
               maxlength="6" />
    </label>
</div>
HTML;
    }
    
    /**
     * Valida CAPTCHA de imagen
     */
    public static function validateImage($userAnswer = null): array
    {
        if ($userAnswer === null) {
            $userAnswer = $_POST['_captcha_image_answer'] ?? null;
        }
        
        if (!isset($_SESSION['_captcha_image_code'])) {
            return ['valid' => false, 'error' => 'Verificación no encontrada.'];
        }
        
        $correctCode = $_SESSION['_captcha_image_code'];
        unset($_SESSION['_captcha_image_code']);
        
        if (strtoupper(trim($userAnswer)) === $correctCode) {
            return ['valid' => true, 'error' => ''];
        }
        
        return ['valid' => false, 'error' => 'Código incorrecto.'];
    }
}
