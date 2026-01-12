<?php
// 1. Cargar el autoloader de Composer
require 'vendor/autoload.php';

// 2. Cargar la configuración de sesión
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
        $_SESSION['register_error'] = "Error de seguridad. Por favor, inténtelo de nuevo.";
        header("Location: register_page.php");
        exit();
    }
    unset($_SESSION['csrf_token']);

    // 9. Obtener datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // 10. Instanciar nuestra clase de lógica
    $auth = new Auth($pdo);

    // 11. Llamar a la lógica de registro
    $result = $auth->register($nombre, $apellidos, $email, $password);

    // 12. Comprobar el resultado
    if (is_int($result)) {
        // ÉXITO: $result es el nuevo user_id
        $_SESSION['user_id'] = $result;
        $_SESSION['nombre'] = $nombre;
        
        session_regenerate_id(true);

        // Redirigir al dashboard
        header("Location: dashboard.php");
        exit();

    } else {
        // ERROR: $result es un string con el mensaje de error
        $_SESSION['register_error'] = $result;
        header("Location: register_page.php");
        exit();
    }
    
} else {
    header("Location: index.php");
    exit();
}
?>