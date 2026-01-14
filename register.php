<?php
/**
 * register.php - Procesamiento de registro seguro
 * @package StatTracker
 */

// 1. Inicializar seguridad (WAF + Headers + Session)
require __DIR__ . '/security_init.php';

// 2. Cargar la conexión a la BD ($pdo)
require __DIR__ . '/db.php'; 

// 3. Usar namespaces
use App\Auth;
use App\Security;
use App\SecurityAudit;
use App\SessionManager;
use App\RateLimiter;
use App\InputSanitizer;

// 4. Comprobar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 5. Validar CSRF token
    if (!Security::validateCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['register_error'] = "Error de seguridad. Por favor, inténtelo de nuevo.";
        header("Location: register_page.php");
        exit();
    }

    // 5.1 Validar Honeypot (detección de bots)
    $honeypotResult = \App\Honeypot::validate();
    if (!$honeypotResult['valid']) {
        // Es un bot - registrar y bloquear silenciosamente
        SecurityAudit::log('BOT_REGISTER_ATTEMPT', null, [
            'reason' => $honeypotResult['reason'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ], 'WARNING');
        
        // Simular un error genérico
        $_SESSION['register_error'] = "Ha ocurrido un error. Por favor, inténtelo de nuevo.";
        header("Location: register_page.php");
        exit();
    }

    // 6. Rate Limiting para registro
    $clientIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $rateLimiter = new RateLimiter('register', $clientIp);
    $rateCheck = $rateLimiter->isAllowed();
    
    if (!$rateCheck['allowed']) {
        $_SESSION['register_error'] = $rateCheck['message'];
        header("Location: register_page.php");
        exit();
    }

    // 7. Sanitizar datos del formulario
    $nombre = InputSanitizer::sanitizeString($_POST['nombre'] ?? '');
    $apellidos = InputSanitizer::sanitizeString($_POST['apellidos'] ?? '');
    $email = InputSanitizer::sanitizeEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // No sanitizar contraseña

    // 8. Instanciar nuestra clase de lógica
    $auth = new Auth($pdo);

    // 9. Llamar a la lógica de registro
    $result = $auth->register($nombre, $apellidos, $email, $password);

    // 10. Comprobar el resultado
    if (is_int($result)) {
        // ÉXITO: $result es el nuevo user_id
        $rateLimiter->recordAttempt(true);
        
        // Usar SessionManager para autenticación segura
        SessionManager::authenticate($result, $nombre);
        
        // Registrar evento
        SecurityAudit::log('REGISTER', $result, ['email' => substr($email, 0, 3) . '***']);

        // Redirigir al dashboard
        header("Location: dashboard.php");
        exit();

    } else {
        // ERROR: $result es un string con el mensaje de error
        $rateLimiter->recordAttempt(false);
        $_SESSION['register_error'] = $result;
        header("Location: register_page.php");
        exit();
    }
    
} else {
    header("Location: index.php");
    exit();
}
?>