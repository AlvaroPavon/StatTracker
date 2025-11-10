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

// 5. Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 6. Verificar el método de solicitud
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 7. Validar CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header("Location: profile.php?error=" . urlencode("Error de seguridad (CSRF)."));
        exit();
    }
    
    // 8. CORRECCIÓN: Obtener 'nombre' y 'apellidos'
    // (El formulario en profile.php envía 'nombre' y 'apellidos')
    $user_id = (int)$_SESSION['user_id'];
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $email = $_POST['email'] ?? '';

    // 9. Instanciar nuestra clase de lógica
    $user = new User($pdo);

    // 10. CORRECCIÓN: Llamar a la lógica con los campos correctos
    $result = $user->updateProfile($user_id, $nombre, $apellidos, $email);

    // 11. Comprobar el resultado y redirigir
    if ($result === true) {
        // ÉXITO
        // CORRECCIÓN: Actualizar la variable de sesión 'nombre'
        $_SESSION['nombre'] = $nombre; 
        
        header("Location: profile.php?success=" . urlencode("Perfil actualizado con éxito."));
        exit();
    } else {
        // ERROR: $result es un string con el mensaje de error
        header("Location: profile.php?error=" . urlencode($result));
        exit();
    }

} else {
    // Si alguien accede directamente sin POST
    header("Location: profile.php");
    exit();
}
?>