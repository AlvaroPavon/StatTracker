<?php
/**
 * Clase SecurityHeaders - Configura headers de seguridad HTTP
 * @package App
 */

namespace App;

class SecurityHeaders
{
    /**
     * Aplica todos los headers de seguridad
     */
    public static function apply(): void
    {
        if (headers_sent()) {
            return;
        }

        // Prevenir clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevenir MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Habilitar XSS filter del navegador
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Permissions Policy (antiguo Feature-Policy)
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        
        // Content Security Policy
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com https://cdnjs.cloudflare.com",
            "font-src 'self' https://fonts.gstatic.com https://fonts.googleapis.com",
            "img-src 'self' data: blob:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'"
        ]);
        header('Content-Security-Policy: ' . $csp);
        
        // HSTS (Solo en producción con HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    /**
     * Configura cookies de sesión seguras
     */
    public static function configureSecureSession(): void
    {
        // HTTP-Only: JavaScript no puede acceder
        ini_set('session.cookie_httponly', 1);
        
        // Solo propagar por cookies
        ini_set('session.use_only_cookies', 1);
        
        // SameSite: Prevenir CSRF
        ini_set('session.cookie_samesite', 'Strict');
        
        // Secure: Solo HTTPS (en producción)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            ini_set('session.cookie_secure', 1);
        }
        
        // Regenerar ID de sesión frecuentemente
        ini_set('session.gc_maxlifetime', 3600); // 1 hora
        
        // Nombre de cookie personalizado
        session_name('STATTRACKER_SESSION');
    }

    /**
     * Aplica headers para respuestas JSON
     */
    public static function applyJsonHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }

    /**
     * Aplica headers para prevenir cache
     */
    public static function noCache(): void
    {
        if (headers_sent()) {
            return;
        }
        
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
    }
}
