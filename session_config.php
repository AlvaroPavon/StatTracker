<?php
/**
 * Configuración de Sesiones Seguras
 * Estas directivas deben ejecutarse ANTES de session_start()
 */

// Cargar clase de seguridad si está disponible
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    \App\SecurityHeaders::configureSecureSession();
} else {
    // Fallback si autoload no está disponible
    
    // HTTP-Only: JavaScript no puede acceder a la cookie de sesión
    ini_set('session.cookie_httponly', 1);
    
    // Solo propagar ID de sesión mediante cookies
    ini_set('session.use_only_cookies', 1);
    
    // SameSite: Prevenir ataques CSRF
    ini_set('session.cookie_samesite', 'Strict');
    
    // Secure: Solo HTTPS (en producción)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    // Nombre de sesión personalizado
    session_name('STATTRACKER_SESSION');
}
