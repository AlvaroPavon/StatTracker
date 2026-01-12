<?php
// 1. Cargar el autoloader de Composer
require 'vendor/autoload.php';

// 2. Cargar la conexión a la BD ($pdo)
require 'db.php'; 

// 3. Usar namespaces
use App\User;
use App\Security;
use App\SecurityHeaders;

// 4. Cargar configuración de sesión
require 'session_config.php';

// 5. Aplicar headers de seguridad
SecurityHeaders::apply();

// 6. Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 7. Autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// 8. Verificar que la solicitud sea por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 9. Validar el token CSRF
    if (!Security::validateCsrfToken($_POST['csrf_token'] ?? null)) {
        header('Location: profile.php?error=' . urlencode("Error de seguridad. Intente de nuevo."));
        exit;
    }

    // 10. Obtener ID de usuario y contraseñas
    $user_id = (int)$_SESSION['user_id'];
    $old_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_password'] ?? '';

    // 11. Instanciar nuestra clase de lógica
    $user = new User($pdo);

    // 12. Llamar a la lógica
    $result = $user->changePassword($user_id, $old_password, $new_password, $confirm_new_password);
    
    // 13. Comprobar el resultado y redirigir
    if ($result === true) {
        header('Location: profile.php?success=' . urlencode("Contraseña actualizada con éxito."));
        exit;
    } else {
        header('Location: profile.php?error=' . urlencode($result));
        exit;
    }

} else {
    header('Location: profile.php');
    exit;
}
?>