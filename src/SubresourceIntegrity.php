<?php
/**
 * Clase SubresourceIntegrity - Genera y verifica SRI para recursos externos
 * Protege contra CDN comprometidos
 * @package App
 */

namespace App;

class SubresourceIntegrity
{
    // Cache de hashes SRI conocidos para CDNs comunes
    // IMPORTANTE: Actualizar estos hashes cuando se actualicen las versiones
    private const KNOWN_SRI = [
        // Tailwind CSS CDN (versión específica recomendada en producción)
        'https://cdn.tailwindcss.com' => null, // Tailwind CDN genera dinámicamente, no se puede usar SRI
        
        // Chart.js
        'https://cdn.jsdelivr.net/npm/chart.js' => 'sha384-CHART_JS_HASH_HERE',
        
        // Animate.css
        'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css' => 'sha512-c42qTSw/wPZ3/5LBzD+Bw5f7bSF2oxou6wEb+I/lqeaKV5FDIfMvvRp772y4jcJLKuGUOpbJMdg/BTl50fJYAw==',
        
        // Google Fonts (no soporta SRI directamente)
    ];

    /**
     * Genera el atributo integrity para un recurso
     */
    public static function generateIntegrity(string $url): ?string
    {
        // Verificar si tenemos un hash conocido
        if (isset(self::KNOWN_SRI[$url]) && self::KNOWN_SRI[$url] !== null) {
            return self::KNOWN_SRI[$url];
        }
        
        // Para URLs conocidas sin SRI (como Tailwind CDN dinámico)
        if (array_key_exists($url, self::KNOWN_SRI)) {
            return null;
        }
        
        return null;
    }

    /**
     * Genera una etiqueta script segura
     */
    public static function script(string $url, bool $async = false, bool $defer = false): string
    {
        $integrity = self::generateIntegrity($url);
        
        $attrs = [
            'src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"'
        ];
        
        if ($integrity) {
            $attrs[] = 'integrity="' . $integrity . '"';
            $attrs[] = 'crossorigin="anonymous"';
        }
        
        if ($async) {
            $attrs[] = 'async';
        }
        
        if ($defer) {
            $attrs[] = 'defer';
        }
        
        // Añadir nonce CSP si está disponible
        if (isset($_SESSION['_csp_nonce'])) {
            $attrs[] = 'nonce="' . htmlspecialchars($_SESSION['_csp_nonce'], ENT_QUOTES, 'UTF-8') . '"';
        }
        
        return '<script ' . implode(' ', $attrs) . '></script>';
    }

    /**
     * Genera una etiqueta link (CSS) segura
     */
    public static function stylesheet(string $url): string
    {
        $integrity = self::generateIntegrity($url);
        
        $attrs = [
            'rel="stylesheet"',
            'href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"'
        ];
        
        if ($integrity) {
            $attrs[] = 'integrity="' . $integrity . '"';
            $attrs[] = 'crossorigin="anonymous"';
        }
        
        return '<link ' . implode(' ', $attrs) . '>';
    }

    /**
     * Calcula el hash SRI de un archivo local
     */
    public static function calculateHash(string $filepath, string $algo = 'sha384'): ?string
    {
        if (!file_exists($filepath)) {
            return null;
        }
        
        $content = file_get_contents($filepath);
        $hash = hash($algo, $content, true);
        
        return $algo . '-' . base64_encode($hash);
    }

    /**
     * Calcula el hash SRI de una URL (para desarrollo/actualización)
     */
    public static function calculateUrlHash(string $url, string $algo = 'sha384'): ?string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'StatTracker-SRI-Calculator/1.0'
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true
            ]
        ]);
        
        $content = @file_get_contents($url, false, $context);
        if ($content === false) {
            return null;
        }
        
        $hash = hash($algo, $content, true);
        
        return $algo . '-' . base64_encode($hash);
    }

    /**
     * Genera HTML seguro para todos los recursos externos comunes
     */
    public static function getSecureHeadTags(): string
    {
        $html = '';
        
        // Preconnect para mejorar rendimiento
        $html .= '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>' . "\n";
        $html .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        $html .= '<link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin>' . "\n";
        
        // DNS prefetch
        $html .= '<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">' . "\n";
        $html .= '<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">' . "\n";
        
        return $html;
    }

    /**
     * Verifica la integridad de recursos descargados
     */
    public static function verifyResource(string $content, string $expectedHash): bool
    {
        // Parsear el hash esperado
        $parts = explode('-', $expectedHash, 2);
        if (count($parts) !== 2) {
            return false;
        }
        
        [$algo, $expectedBase64] = $parts;
        
        // Verificar que el algoritmo es soportado
        if (!in_array($algo, ['sha256', 'sha384', 'sha512'])) {
            return false;
        }
        
        // Calcular hash
        $actualHash = hash($algo, $content, true);
        $actualBase64 = base64_encode($actualHash);
        
        return hash_equals($expectedBase64, $actualBase64);
    }
}
