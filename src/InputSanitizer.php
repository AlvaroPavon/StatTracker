<?php
/**
 * Clase InputSanitizer - Sanitización avanzada de entrada de usuario
 * @package App
 */

namespace App;

class InputSanitizer
{
    /**
     * Sanitiza string básico
     */
    public static function sanitizeString(?string $input): string
    {
        if ($input === null) {
            return '';
        }
        
        // Eliminar caracteres nulos
        $input = str_replace("\0", '', $input);
        
        // Normalizar saltos de línea
        $input = str_replace(["\r\n", "\r"], "\n", $input);
        
        // Eliminar espacios extra
        $input = trim($input);
        
        return $input;
    }

    /**
     * Sanitiza email
     */
    public static function sanitizeEmail(?string $email): string
    {
        if ($email === null) {
            return '';
        }
        
        $email = self::sanitizeString($email);
        $email = strtolower($email);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        return $email ?: '';
    }

    /**
     * Sanitiza número entero
     */
    public static function sanitizeInt(mixed $input): int
    {
        return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitiza número flotante
     */
    public static function sanitizeFloat(mixed $input): float
    {
        $filtered = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        return (float) $filtered;
    }

    /**
     * Sanitiza para salida HTML
     */
    public static function sanitizeForHtml(?string $input): string
    {
        if ($input === null) {
            return '';
        }
        
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitiza para uso en atributos HTML
     */
    public static function sanitizeForAttribute(?string $input): string
    {
        if ($input === null) {
            return '';
        }
        
        $input = self::sanitizeString($input);
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitiza para uso en JavaScript
     */
    public static function sanitizeForJs(?string $input): string
    {
        if ($input === null) {
            return '';
        }
        
        // Escapar caracteres especiales de JS
        $replacements = [
            "\\" => "\\\\",
            "'" => "\\'",
            '"' => '\\"',
            "\n" => "\\n",
            "\r" => "\\r",
            "\t" => "\\t",
            "</" => "<\\/"
        ];
        
        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $input
        );
    }

    /**
     * Sanitiza nombre de archivo
     */
    public static function sanitizeFilename(?string $filename): string
    {
        if ($filename === null) {
            return '';
        }
        
        // Eliminar caracteres peligrosos
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Eliminar dobles puntos (prevenir path traversal)
        $filename = str_replace('..', '', $filename);
        
        // Limitar longitud
        $filename = substr($filename, 0, 255);
        
        return $filename ?: 'unnamed';
    }

    /**
     * Sanitiza URL
     */
    public static function sanitizeUrl(?string $url): string
    {
        if ($url === null) {
            return '';
        }
        
        $url = self::sanitizeString($url);
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        // Verificar que sea una URL válida
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }
        
        // Solo permitir http y https
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return '';
        }
        
        return $url;
    }

    /**
     * Sanitiza array de entrada completo
     */
    public static function sanitizeArray(array $input, array $rules = []): array
    {
        $sanitized = [];
        
        foreach ($input as $key => $value) {
            // Sanitizar la clave
            $safeKey = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
            
            if (isset($rules[$safeKey])) {
                $sanitized[$safeKey] = self::applySanitizer($value, $rules[$safeKey]);
            } elseif (is_string($value)) {
                $sanitized[$safeKey] = self::sanitizeString($value);
            } elseif (is_array($value)) {
                $sanitized[$safeKey] = self::sanitizeArray($value);
            } else {
                $sanitized[$safeKey] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Aplica un sanitizador específico
     */
    private static function applySanitizer(mixed $value, string $type): mixed
    {
        return match ($type) {
            'string' => self::sanitizeString($value),
            'email' => self::sanitizeEmail($value),
            'int' => self::sanitizeInt($value),
            'float' => self::sanitizeFloat($value),
            'html' => self::sanitizeForHtml($value),
            'url' => self::sanitizeUrl($value),
            'filename' => self::sanitizeFilename($value),
            default => self::sanitizeString($value)
        };
    }

    /**
     * Elimina tags HTML peligrosos pero permite algunos seguros
     */
    public static function sanitizeRichText(?string $input, array $allowedTags = []): string
    {
        if ($input === null) {
            return '';
        }
        
        $defaultAllowed = ['<p>', '<br>', '<b>', '<i>', '<u>', '<strong>', '<em>'];
        $allowed = array_merge($defaultAllowed, $allowedTags);
        
        // Eliminar tags no permitidos
        $input = strip_tags($input, implode('', $allowed));
        
        // Eliminar atributos de eventos (onclick, onerror, etc.)
        $input = preg_replace('/\s*on\w+\s*=\s*["\'][^"]*["\']/i', '', $input);
        $input = preg_replace('/\s*on\w+\s*=\s*[^\s>]*/i', '', $input);
        
        // Eliminar javascript: en href/src
        $input = preg_replace('/\s*(href|src)\s*=\s*["\']*\s*javascript:[^"]*["\']*/i', '', $input);
        
        return $input;
    }
}
