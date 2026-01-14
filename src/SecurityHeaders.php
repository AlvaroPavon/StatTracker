<?php
/**
 * Clase SecurityHeaders - Configura headers de seguridad HTTP
 * Implementa las mejores prácticas de seguridad web
 * @package App
 */

namespace App;

class SecurityHeaders
{
    // Tiempo de caché de HSTS (1 año)
    private const HSTS_MAX_AGE = 31536000;
    
    // Tiempo de caché de Expect-CT (1 día)
    private const EXPECT_CT_MAX_AGE = 86400;

    /**
     * Aplica todos los headers de seguridad
     */
    public static function apply(): void
    {
        if (headers_sent()) {
            return;
        }

        // ==================== Headers Básicos de Seguridad ====================
        
        // Prevenir clickjacking - no permitir ningún iframe
        header('X-Frame-Options: DENY');
        
        // Prevenir MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Habilitar XSS filter del navegador (legacy pero útil)
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy - enviar referrer solo al mismo origen
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Permissions Policy (antes Feature-Policy)
        // Deshabilitar APIs sensibles que no necesitamos
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=()');
        
        // Cross-Origin policies
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Cross-Origin-Resource-Policy: same-origin');
        header('Cross-Origin-Embedder-Policy: require-corp');

        // ==================== Content Security Policy ====================
        
        $csp = self::buildContentSecurityPolicy();
        header('Content-Security-Policy: ' . $csp);
        
        // ==================== HTTPS Headers ====================
        
        if (self::isHttps()) {
            // HSTS - Forzar HTTPS por 1 año, incluir subdominios
            header('Strict-Transport-Security: max-age=' . self::HSTS_MAX_AGE . '; includeSubDomains; preload');
            
            // Expect-CT - Verificar Certificate Transparency (deprecated pero aún útil)
            header('Expect-CT: max-age=' . self::EXPECT_CT_MAX_AGE . ', enforce');
        }

        // ==================== Headers Anti-Cache para páginas sensibles ====================
        
        // No cachear respuestas por defecto (mejor para páginas con datos de usuario)
        header('Cache-Control: no-store, no-cache, must-revalidate, private');
        header('Pragma: no-cache');
        
        // Eliminar headers que exponen información del servidor
        self::removeServerHeaders();
    }

    /**
     * Construye el Content Security Policy
     */
    private static function buildContentSecurityPolicy(): string
    {
        $policies = [
            // Solo cargar recursos del mismo origen por defecto
            "default-src 'self'",
            
            // Scripts: mismo origen + CDNs necesarios
            // Nota: 'unsafe-inline' y 'unsafe-eval' son necesarios para Tailwind y Chart.js
            // En producción ideal, usar nonces o hashes
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            
            // Estilos: mismo origen + Google Fonts + CDNs
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com https://cdnjs.cloudflare.com",
            
            // Fuentes: mismo origen + Google Fonts
            "font-src 'self' https://fonts.gstatic.com https://fonts.googleapis.com data:",
            
            // Imágenes: mismo origen + data URIs + blobs (para Chart.js)
            "img-src 'self' data: blob:",
            
            // Conexiones: solo mismo origen
            "connect-src 'self'",
            
            // Multimedia: solo mismo origen
            "media-src 'self'",
            
            // Objetos: ninguno (prevenir Flash, Java, etc.)
            "object-src 'none'",
            
            // Frames hijos: ninguno
            "child-src 'none'",
            
            // Frame ancestors: ninguno (refuerza X-Frame-Options)
            "frame-ancestors 'none'",
            
            // Formularios: solo mismo origen
            "form-action 'self'",
            
            // Base URI: solo mismo origen (prevenir base tag injection)
            "base-uri 'self'",
            
            // Manifiestos: solo mismo origen
            "manifest-src 'self'",
            
            // Workers: solo mismo origen
            "worker-src 'self' blob:",
            
            // Upgrade requests inseguras a HTTPS
            "upgrade-insecure-requests"
        ];
        
        return implode('; ', $policies);
    }

    /**
     * Elimina headers que exponen información del servidor
     */
    private static function removeServerHeaders(): void
    {
        // Intentar eliminar header X-Powered-By
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
            header_remove('Server');
        }
        
        // Ocultar versión de PHP
        if (ini_get('expose_php')) {
            @ini_set('expose_php', 'Off');
        }
    }

    /**
     * Verifica si la conexión es HTTPS
     */
    private static function isHttps(): bool
    {
        // Verificar HTTPS directo
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }
        
        // Verificar puerto 443
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            return true;
        }
        
        // Verificar header de proxy (Cloudflare, Load Balancers, etc.)
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }
        
        // Verificar Cloudflare
        if (isset($_SERVER['HTTP_CF_VISITOR'])) {
            $cfVisitor = json_decode($_SERVER['HTTP_CF_VISITOR'], true);
            if (isset($cfVisitor['scheme']) && $cfVisitor['scheme'] === 'https') {
                return true;
            }
        }
        
        return false;
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
