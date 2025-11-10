<?php
// 1. Cargar el autoloader de Composer
require 'vendor/autoload.php';

// 2. Cargar la conexión a la BD ($pdo)
require 'db.php'; 

// 3. Usar el namespace de nuestra clase
use App\User;

// 4. Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 5. Autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // No autorizado
    exit;
}

// 6. Verificar que la solicitud sea por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 7. Validar el token CSRF
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header('Location: profile.php?error=' . urlencode("Error de seguridad (CSRF). Intente de nuevo."));
        exit;
    }

    // 8. Obtener ID de usuario y contraseñas
    $user_id = (int)$_SESSION['user_id'];
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    // 9. Instanciar nuestra clase de lógica
    $user = new User($pdo);

    // 10. Llamar a la lógica (ya corregida para usar la tabla 'usuarios')
    $result = $user->changePassword($user_id, $old_password, $new_password, $confirm_new_password);
    
    // 11. Comprobar el resultado y redirigir
    if ($result === true) {
        // ÉXITO
        header('Location: profile.php?success=' . urlencode("Contraseña actualizada con éxito."));
        exit;
    } else {
        // ERROR: $result es un string con el mensaje de error
        header('Location: profile.php?error=' . urlencode($result));
        exit;
    }

} else {
    // Si alguien intenta acceder directamente
    header('Location: profile.php');
    exit;
}
?>