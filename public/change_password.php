<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Session;
use App\User;
use App\Validator;
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
$old_password = $_POST['old_password'] ?? null;
$new_password = $_POST['new_password'] ?? null;
$confirm_new_password = $_POST['confirm_new_password'] ?? null;

// 4. Validar
$errors = [];
if (!Validator::isNotEmpty($old_password) || !Validator::isNotEmpty($new_password) || !Validator::isNotEmpty($confirm_new_password)) {
    $errors[] = "Todos los campos de contraseña son obligatorios.";
}
if ($new_password !== $confirm_new_password) {
    $errors[] = "Las nuevas contraseñas no coinciden.";
}
if ($new_password && !Validator::isStrongPassword($new_password)) {
    $errors[] = 'La nueva contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.';
}

if (!empty($errors)) {
    header('Location: profile.php?error=' . urlencode($errors[0]));
    exit;
}

// 5. Procesar
try {
    $pdo = Database::getInstance();
    $user = new User($pdo);
    
    $success = $user->changePassword((int)$user_id, $old_password, $new_password);

    if ($success) {
        header('Location: profile.php?success=Contraseña actualizada con éxito.');
        exit;
    } else {
        header('Location: profile.php?error=La contraseña anterior es incorrecta.');
        exit;
    }

} catch (\PDOException $e) {
    error_log('Error en change_password.php: ' . $e->getMessage());
    header('Location: profile.php?error=Error en la base de datos. Inténtelo de nuevo.');
    exit;
}