<?php
/**
 * Clase TimingSafeCompare - Comparaciones seguras contra timing attacks
 * @package App
 */

namespace App;

class TimingSafe
{
    /**
     * Comparación de strings en tiempo constante
     * Siempre toma el mismo tiempo independientemente de dónde falle
     */
    public static function compare(string $known, string $user): bool
    {
        // hash_equals ya es timing-safe en PHP 5.6+
        return hash_equals($known, $user);
    }

    /**
     * Comparación de arrays en tiempo constante
     */
    public static function compareArrays(array $known, array $user): bool
    {
        if (count($known) !== count($user)) {
            // Añadir delay para ocultar diferencia de longitud
            self::randomDelay();
            return false;
        }
        
        $result = 0;
        
        foreach ($known as $key => $value) {
            if (!isset($user[$key])) {
                $result |= 1;
                continue;
            }
            
            if (is_string($value) && is_string($user[$key])) {
                $result |= self::compareStringsInternal($value, $user[$key]) ? 0 : 1;
            } else {
                $result |= ($value === $user[$key]) ? 0 : 1;
            }
        }
        
        // Añadir delay aleatorio para ocultar timing
        self::randomDelay();
        
        return $result === 0;
    }

    /**
     * Comparación interna de strings
     */
    private static function compareStringsInternal(string $a, string $b): bool
    {
        $lenA = strlen($a);
        $lenB = strlen($b);
        
        // Si longitudes diferentes, comparar con padding
        if ($lenA !== $lenB) {
            $b = str_pad($b, $lenA, "\0");
        }
        
        $result = 0;
        for ($i = 0; $i < $lenA; $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }
        
        return $result === 0;
    }

    /**
     * Genera un delay aleatorio para ocultar timing
     */
    public static function randomDelay(): void
    {
        // Delay entre 1-5 milisegundos
        usleep(random_int(1000, 5000));
    }

    /**
     * Hash de contraseña con timing constante
     */
    public static function hashPassword(string $password): string
    {
        $start = microtime(true);
        
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Asegurar tiempo mínimo de 100ms
        $elapsed = (microtime(true) - $start) * 1000;
        if ($elapsed < 100) {
            usleep((100 - $elapsed) * 1000);
        }
        
        return $hash;
    }

    /**
     * Verificación de contraseña con timing constante
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        $start = microtime(true);
        
        $result = password_verify($password, $hash);
        
        // Asegurar tiempo mínimo de 100ms (igual para éxito y fallo)
        $elapsed = (microtime(true) - $start) * 1000;
        if ($elapsed < 100) {
            usleep((100 - $elapsed) * 1000);
        }
        
        return $result;
    }

    /**
     * Genera token criptográficamente seguro
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Genera un HMAC seguro
     */
    public static function hmac(string $data, string $key): string
    {
        return hash_hmac('sha256', $data, $key);
    }

    /**
     * Verifica un HMAC de forma segura
     */
    public static function verifyHmac(string $data, string $key, string $expectedHmac): bool
    {
        $actualHmac = self::hmac($data, $key);
        return hash_equals($expectedHmac, $actualHmac);
    }
}
