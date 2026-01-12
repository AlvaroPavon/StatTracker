<?php
// 1. Cargar el autoloader de Composer
require 'vendor/autoload.php';

// 2. Cargar la configuración de sesión ANTES de iniciarla
require 'session_config.php';

// 3. Cargar la conexión a la BD ($pdo)
require 'db.php'; 

// 4. Usar namespaces
use App\Auth;
use App\Security;
use App\SecurityHeaders;

// 5. Aplicar headers de seguridad
SecurityHeaders::apply();

// 6. Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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