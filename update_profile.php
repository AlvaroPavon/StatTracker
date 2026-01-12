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

// 7. Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 8. Verificar el método de solicitud
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 9. Validar CSRF token
    if (!Security::validateCsrfToken($_POST['csrf_token'] ?? null)) {
        header("Location: profile.php?error=" . urlencode("Error de seguridad."));
        exit();
    }
    
    $user_id = (int)$_SESSION['user_id'];
    $form_type = $_POST['form_type'] ?? '';
    
    // 10. Instanciar la clase User
    $user = new User($pdo);
    
    // 11. Determinar qué formulario se envió
    if ($form_type === 'photo' && isset($_FILES['profile_pic'])) {
        // Subida de foto de perfil
        $result = $user->updateProfilePicture($user_id, $_FILES['profile_pic']);
        
        if ($result === true) {
            header("Location: profile.php?success=" . urlencode("Foto actualizada con éxito."));
            exit();
        } else {
            header("Location: profile.php?error=" . urlencode($result));
            exit();
        }
        
    } elseif ($form_type === 'details') {
        // Actualización de datos del perfil
        $nombre = $_POST['nombre'] ?? '';
        $apellidos = $_POST['apellidos'] ?? '';
        $email = $_POST['email'] ?? '';

        $result = $user->updateProfile($user_id, $nombre, $apellidos, $email);

        if ($result === true) {
            $_SESSION['nombre'] = $nombre;
            header("Location: profile.php?success=" . urlencode("Perfil actualizado con éxito."));
            exit();
        } else {
            header("Location: profile.php?error=" . urlencode($result));
            exit();
        }
    } else {
        header("Location: profile.php?error=" . urlencode("Formulario inválido."));
        exit();
    }

} else {
    header("Location: profile.php");
    exit();
}
?>