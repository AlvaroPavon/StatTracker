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

// 7. Comprobar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 8. Validar CSRF token
    if (!Security::validateCsrfToken($_POST['csrf_token'] ?? null)) {
        $_SESSION['login_error'] = "Error de seguridad. Por favor, inténtelo de nuevo.";
        header("Location: index.php");
        exit();
    }
    unset($_SESSION['csrf_token']);

    // Obtenemos los datos
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // 9. Instanciar nuestra clase de lógica
    $auth = new Auth($pdo);

    // 10. Llamar a la lógica de login
    $result = $auth->login($email, $password);

    // 11. Comprobar el resultado
    if (is_array($result)) {
        // ÉXITO
        session_regenerate_id(true);

        $_SESSION['user_id'] = $result['id'];
        $_SESSION['nombre'] = $result['nombre'];
        
        // Activar pantalla de bienvenida
        $_SESSION['show_welcome_screen'] = true;

        // Redirigir al dashboard
        header("Location: dashboard.php");
        exit();

    } else {
        // ERROR
        $_SESSION['login_error'] = $result;
        header("Location: index.php");
        exit();
    }
    
} else {
    header("Location: index.php");
    exit();
}
?>