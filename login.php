<?php
/**
 * login.php - Procesamiento de login seguro
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
        $_SESSION['login_error'] = "Error de seguridad. Por favor, inténtelo de nuevo.";
        header("Location: index.php");
        exit();
    }

    // 6. Sanitizar y obtener datos
    $email = InputSanitizer::sanitizeEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // No sanitizar contraseña

    // 7. Rate Limiting avanzado (por IP y email)
    $clientIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $rateLimiter = new RateLimiter('login', $email . ':' . $clientIp);
    $rateCheck = $rateLimiter->isAllowed();
    
    if (!$rateCheck['allowed']) {
        SecurityAudit::logLoginBlocked($email);
        $_SESSION['login_error'] = $rateCheck['message'];
        header("Location: index.php");
        exit();
    }

    // 8. Instanciar nuestra clase de lógica
    $auth = new Auth($pdo);

    // 9. Llamar a la lógica de login
    $result = $auth->login($email, $password);

    // 10. Comprobar el resultado
    if (is_array($result)) {
        // ÉXITO - Registrar intento exitoso
        $rateLimiter->recordAttempt(true);
        
        // Usar SessionManager para autenticación segura
        SessionManager::authenticate($result['id'], $result['nombre']);
        
        // Activar pantalla de bienvenida
        $_SESSION['show_welcome_screen'] = true;

        // Regenerar token CSRF después del login
        Security::regenerateCsrfToken();
        
        // Redirigir al dashboard
        header("Location: dashboard.php");
        exit();

    } else {
        // ERROR - Registrar intento fallido
        $rateLimiter->recordAttempt(false);
        SecurityAudit::logLoginFailed($email, $result);
        
        $_SESSION['login_error'] = $result;
        header("Location: index.php");
        exit();
    }
    
} else {
    header("Location: index.php");
    exit();
}
?>