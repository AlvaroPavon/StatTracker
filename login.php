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
use App\SimpleCaptcha;
use App\LoginAlertSystem;

// 4. Comprobar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 5. Validar CSRF token
    if (!Security::validateCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['login_error'] = "Error de seguridad. Por favor, inténtelo de nuevo.";
        header("Location: index.php");
        exit();
    }

    // 5.1 Validar Honeypot (detección de bots)
    $honeypotResult = \App\Honeypot::validate();
    if (!$honeypotResult['valid']) {
        // Es un bot - registrar y bloquear silenciosamente
        SecurityAudit::log('BOT_LOGIN_ATTEMPT', null, [
            'reason' => $honeypotResult['reason'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ], 'WARNING');
        
        // Simular un error genérico (no revelar que detectamos el bot)
        $_SESSION['login_error'] = "Ha ocurrido un error. Por favor, inténtelo de nuevo.";
        header("Location: index.php");
        exit();
    }

    // 5.2 Validar CAPTCHA si es requerido (después de intentos fallidos)
    if (isset($_SESSION['require_captcha']) && $_SESSION['require_captcha'] === true) {
        $captchaResult = SimpleCaptcha::validate();
        if (!$captchaResult['valid']) {
            $_SESSION['login_error'] = $captchaResult['error'];
            header("Location: index.php");
            exit();
        }
    }

    // 6. Sanitizar y obtener datos
    $email = InputSanitizer::sanitizeEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // No sanitizar contraseña

    // 7. Rate Limiting avanzado (por IP y email)
    $clientIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $rateLimiter = new RateLimiter('login', $email . ':' . $clientIp, $pdo);
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
        
        // Limpiar flag de CAPTCHA requerido
        unset($_SESSION['require_captcha']);
        unset($_SESSION['failed_login_count']);
        
        // Analizar login para detectar actividad sospechosa
        $loginAnalysis = LoginAlertSystem::analyzeLogin($result['id'], $email);
        
        // Usar SessionManager para autenticación segura
        SessionManager::authenticate($result['id'], $result['nombre']);
        
        // Guardar alerta de seguridad si es necesario
        if ($loginAnalysis['suspicious']) {
            $_SESSION['security_alert'] = LoginAlertSystem::generateAlertMessage($loginAnalysis);
        }
        
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
        
        // Incrementar contador de intentos fallidos
        $_SESSION['failed_login_count'] = ($_SESSION['failed_login_count'] ?? 0) + 1;
        
        // Requerir CAPTCHA después de 3 intentos fallidos
        if ($_SESSION['failed_login_count'] >= 3) {
            $_SESSION['require_captcha'] = true;
        }
        
        $_SESSION['login_error'] = $result;
        header("Location: index.php");
        exit();
    }
    
} else {
    header("Location: index.php");
    exit();
}
?>