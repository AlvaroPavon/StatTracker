<?php
// 1. Cargar configuración de sesión
require 'session_config.php';

// 2. Cargar autoloader
require 'vendor/autoload.php';

use App\Security;
use App\SecurityHeaders;

// 3. Aplicar headers de seguridad
SecurityHeaders::noCache();

// 4. Iniciar la sesión para acceder al token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 5. Validar el token CSRF
if (!Security::validateCsrfToken($_GET['token'] ?? null)) {
    header('Location: dashboard.php?error=' . urlencode('Error de seguridad.'));
    exit;
}

// 6. Borrar todas las variables de la sesión
$_SESSION = array();

// 7. Destruir la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 8. Destruir la sesión en el servidor
session_destroy();

// 9. Redirigir al formulario de login
header('Location: index.php');
exit;
?>