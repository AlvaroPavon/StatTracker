<?php
/**
 * Middleware de verificación JWT
 * Valida el token y extrae la información del usuario
 */

require_once __DIR__ . '/../config/jwt.php';

class JWTMiddleware {
    
    /**
     * Verifica el token JWT del header Authorization
     * @return array|null Datos del usuario o null si falla
     */
    public static function verify(): ?array {
        $headers = function_exists('getallheaders') ? getallheaders() : apache_request_headers();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token de autenticación requerido']);
            return null;
        }

        $token = $matches[1];
        
        try {
            // Decodificar JWT manualmente (sin librerías externas)
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                throw new Exception('Token inválido');
            }

            $header = json_decode(self::base64UrlDecode($parts[0]), true);
            $payload = json_decode(self::base64UrlDecode($parts[1]), true);
            $signature = $parts[2];

            // Verificar expiración
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                throw new Exception('Token expirado');
            }

            // Verificar firma
            $expectedSignature = self::generateSignature($parts[0], $parts[1], JWT_SECRET);
            if (!hash_equals($expectedSignature, $signature)) {
                throw new Exception('Firma inválida');
            }

            return $payload;

        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido: ' . $e->getMessage()]);
            return null;
        }
    }

    /**
     * Genera un token JWT
     * @param array $payload Datos del usuario
     * @return string Token JWT
     */
    public static function generateToken(array $payload): string {
        $header = [
            'alg' => JWT_ALGO,
            'typ' => 'JWT'
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_EXPIRY;

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        $signature = self::generateSignature($headerEncoded, $payloadEncoded, JWT_SECRET);

        return "$headerEncoded.$payloadEncoded.$signature";
    }

    /**
     * Genera la firma del token
     */
    private static function generateSignature(string $header, string $payload, string $secret): string {
        return hash_hmac('sha256', "$header.$payload", $secret, true);
    }

    /**
     * Codificación base64 URL-safe
     */
    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodificación base64 URL-safe
     */
    private static function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
