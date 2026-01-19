<?php
/**
 * Clase SecurityHeaders - Headers de seguridad HTTP MÁXIMOS
 * Implementa TODAS las protecciones HTTP conocidas
 * @package App
 */

namespace App;

class SecurityHeaders
{
    // Tiempo de caché de HSTS (2 años - máximo recomendado)
    private const HSTS_MAX_AGE = 63072000;
    
    // Nonce para CSP (se genera por request)
    private static ?string $cspNonce = null;

    /**
     * Aplica TODOS los headers de seguridad posibles
     */
    public static function apply(): void
    {
        if (headers_sent()) {
            return;
        }

        // ==================== Headers Anti-Clickjacking ====================
        
        header('X-Frame-Options: DENY');
        
        // ==================== Headers Anti-XSS ====================
        
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        
        // ==================== Headers de Privacidad ====================
        
        // No enviar referrer a otros dominios
        header('Referrer-Policy: same-origin');
        
        // ==================== Permissions Policy (MÁXIMO RESTRICTIVO) ====================
        
        // Deshabilitar TODAS las APIs sensibles
        $permissions = [
            'accelerometer=()',
            'ambient-light-sensor=()',
            'autoplay=()',
            'battery=()',
            'camera=()',
            'clipboard-read=()',
            'clipboard-write=()',
            'display-capture=()',
            'document-domain=()',
            'encrypted-media=()',
            'execution-while-not-rendered=()',
            'execution-while-out-of-viewport=()',
            'fullscreen=()',
            'geolocation=()',
            'gyroscope=()',
            'hid=()',
            'idle-detection=()',
            'magnetometer=()',
            'microphone=()',
            'midi=()',
            'navigation-override=()',
            'payment=()',
            'picture-in-picture=()',
            'publickey-credentials-get=()',
            'screen-wake-lock=()',
            'serial=()',
            'speaker-selection=()',
            'sync-xhr=()',
            'usb=()',
            'web-share=()',
            'xr-spatial-tracking=()'
        ];
        header('Permissions-Policy: ' . implode(', ', $permissions));
        
        // ==================== Cross-Origin Policies ====================
        
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Cross-Origin-Resource-Policy: same-origin');
        // COEP deshabilitado porque rompe CDNs externos
        // header('Cross-Origin-Embedder-Policy: require-corp');
        
        // ==================== Content Security Policy ESTRICTO ====================
        
        $csp = self::buildStrictCSP();
        header('Content-Security-Policy: ' . $csp);
        
        // También enviar en Report-Only para monitoreo
        // header('Content-Security-Policy-Report-Only: ' . $csp);
        
        // ==================== Headers HTTPS ====================
        
        if (self::isHttps()) {
            // HSTS con preload
            header('Strict-Transport-Security: max-age=' . self::HSTS_MAX_AGE . '; includeSubDomains; preload');
        }
        
        // ==================== Headers Anti-Cache ====================
        
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // ==================== Headers adicionales de seguridad ====================
        
        // No permitir que IE detecte el tipo de contenido
        header('X-Download-Options: noopen');
        
        // Deshabilitar DNS prefetch
        header('X-DNS-Prefetch-Control: off');
        
        // No permitir que el sitio sea usado como origen
        header('Origin-Agent-Cluster: ?1');
        
        // ==================== Eliminar headers que exponen info ====================
        
        self::removeServerHeaders();
    }

    /**
     * Genera o retorna el nonce CSP actual
     */
    public static function getNonce(): string
    {
        if (self::$cspNonce === null) {
            self::$cspNonce = base64_encode(random_bytes(16));
            if (isset($_SESSION)) {
                $_SESSION['_csp_nonce'] = self::$cspNonce;
            }
        }
        return self::$cspNonce;
    }

    /**
     * Construye CSP estricto
     */
    private static function buildStrictCSP(): string
    {
        $nonce = self::getNonce();
        
        // Detectar si estamos en desarrollo (localhost)
        $isDevelopment = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']) ||
                        (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);
        
        // En desarrollo, permitir unsafe-inline sin nonce para facilitar debugging
        $scriptSrc = $isDevelopment 
            ? "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com"
            : "script-src 'self' 'nonce-{$nonce}' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com";
        
        $styleSrc = $isDevelopment
            ? "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net"
            : "style-src 'self' 'nonce-{$nonce}' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net";
        
        $policies = [
            // Por defecto: solo mismo origen
            "default-src 'self'",
            
            // Scripts
            $scriptSrc,
            
            // Estilos: mismo origen + inline para Tailwind
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com https://cdnjs.cloudflare.com",
            
            // Fuentes
            "font-src 'self' https://fonts.gstatic.com https://fonts.googleapis.com data:",
            
            // Imágenes
            "img-src 'self' data: blob:",
            
            // Conexiones AJAX: solo mismo origen
            "connect-src 'self'",
            
            // Media
            "media-src 'self'",
            
            // NO plugins (Flash, Java, etc)
            "object-src 'none'",
            
            // NO frames hijos
            "child-src 'none'",
            
            // NO iframes padres
            "frame-ancestors 'none'",
            
            // Formularios solo al mismo origen
            "form-action 'self'",
            
            // Base URI
            "base-uri 'self'",
            
            // Manifiestos
            "manifest-src 'self'",
            
            // Workers
            "worker-src 'self' blob:",
            
            // Forzar HTTPS
            "upgrade-insecure-requests",
            
            // Bloquear mixed content
            "block-all-mixed-content"
        ];
        
        return implode('; ', $policies);
    }

    /**
     * Headers para respuestas JSON
     */
    public static function applyJsonHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        // CSP para JSON (sin scripts)
        header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'");
        
        self::removeServerHeaders();
    }

    /**
     * Headers para descargas de archivos
     */
    public static function applyDownloadHeaders(string $filename, string $contentType): void
    {
        if (headers_sent()) {
            return;
        }
        
        // Sanitizar nombre de archivo
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('X-Content-Type-Options: nosniff');
        header('X-Download-Options: noopen');
        header('Content-Security-Policy: default-src \'none\'');
        header('Cache-Control: no-cache');
        
        self::removeServerHeaders();
    }

    /**
     * Headers para imágenes
     */
    public static function applyImageHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: public, max-age=31536000'); // 1 año
        header("Content-Security-Policy: default-src 'none'");
        
        self::removeServerHeaders();
    }

    /**
     * Headers anti-cache estrictos
     */
    public static function noCache(): void
    {
        if (headers_sent()) {
            return;
        }
        
        header('Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        header('Surrogate-Control: no-store');
    }

    /**
     * Elimina headers que revelan información
     */
    private static function removeServerHeaders(): void
    {
        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
            @header_remove('Server');
            @header_remove('X-AspNet-Version');
            @header_remove('X-AspNetMvc-Version');
        }
        
        // Ocultar versión de PHP
        @ini_set('expose_php', 'Off');
    }

    /**
     * Verifica si la conexión es HTTPS
     */
    private static function isHttps(): bool
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            return true;
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }
        if (isset($_SERVER['HTTP_CF_VISITOR'])) {
            $cfVisitor = json_decode($_SERVER['HTTP_CF_VISITOR'], true);
            if (isset($cfVisitor['scheme']) && $cfVisitor['scheme'] === 'https') {
                return true;
            }
        }
        return false;
    }
}
