<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Session;
use App\User;
use App\Validator;
use App\Uploader;
use PDOException;

Session::init();

// 1. Autenticación
if (!Session::has('user_id')) {
    header('Location: index.php');
    exit;
}

// 2. Verificar Método y Token
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: profile.php');
    exit;
}
if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: profile.php?error=Error de seguridad. Intente de nuevo.');
    exit;
}

// 3. Obtener Datos
$user_id = Session::get('user_id');
$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$email = trim($_POST['email'] ?? '');
$profile_pic_filename = null; // Asumir que no hay subida de archivo

// 4. Validar Datos de Texto
$errors = [];
if (!Validator::isNotEmpty($nombre) || !Validator::isNotEmpty($apellidos) || !Validator::isNotEmpty($email)) {
    $errors[] = "Nombre, apellidos y email son obligatorios.";
}
if ($email && !Validator::isValidEmail($email)) {
    $errors[] = "El formato del email no es válido.";
}
if (!empty($errors)) {
    header('Location: profile.php?error=' . urlencode($errors[0]));
    exit;
}

// 5. Procesar Subida de Archivo (si existe)
// __DIR__ . '/uploads/' apunta a la carpeta /public/uploads/
$uploader = new Uploader(__DIR__ . '/uploads/'); 

if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
    $profile_pic_filename = $uploader->upload($_FILES['profile_pic'], 'user_' . $user_id);
    
    if ($profile_pic_filename === null) {
        $error = $uploader->getError() ?? 'Error desconocido al subir archivo.';
        header('Location: profile.php?error=' . urlencode($error));
        exit;
    }
}

// 6. Actualizar Base de Datos
try {
    $pdo = Database::getInstance();
    $user = new User($pdo);
    $success = false;

    if ($profile_pic_filename) {
        $success = $user->updateProfileWithPic((int)$user_id, $nombre, $apellidos, $email, $profile_pic_filename);
    } else {
        $success = $user->updateProfile((int)$user_id, $nombre, $apellidos, $email);
    }

    if ($success) {
        // Actualizar el nombre en la sesión
        Session::set('user_nombre', $nombre);
        header('Location: profile.php?success=Perfil actualizado con éxito.');
        exit;
    } else {
         header('Location: profile.php?error=No se pudo actualizar el perfil.');
         exit;
    }

} catch (\PDOException $e) {
    // Manejo de error de email duplicado
    if ($e->getCode() == 23000) {
        header('Location: profile.php?error=El email introducido ya está registrado por otro usuario.');
    } else {
        error_log('Error en update_profile.php: ' . $e->getMessage());
        header('Location: profile.php?error=Error en la base de datos. Inténtelo de nuevo.');
    }
    exit;
}