<?php namespace App;

class Session {

    /**
     * Inicia la sesión con configuraciones seguras.
     */
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Configuraciones de seguridad que estaban en tu db.php
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            // ini_set('session.cookie_secure', '1'); // Descomentar en producción (HTTPS)
            
            session_start();
        }
    }

    public static function set(string $key, $value): void {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public static function regenerateId(bool $deleteOld = true): void {
        session_regenerate_id($deleteOld);
    }

    /**
     * Destruye la sesión de forma segura.
     */
    public static function destroy(): void {
        $_SESSION = []; // Limpiar variables

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    // --- Gestión de CSRF ---

    public static function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

     public static function validateCsrfToken(string $tokenFromRequest): bool {
         $tokenInSession = self::get('csrf_token');
         if (empty($tokenInSession) || empty($tokenFromRequest)) {
             return false;
         }
         return hash_equals($tokenInSession, $tokenFromRequest);
     }

     public static function regenerateCsrfToken(): string {
         $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
         return $_SESSION['csrf_token'];
     }
}