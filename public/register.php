<?php declare(strict_types=1);

// 1. Cargar Autoloader y Clases
require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Session;
use App\Auth;
use App\Validator;
use PDOException;

// 2. Iniciar Sesión Segura
Session::init();

// 3. Verificar Método y Token CSRF
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: register_page.php');
    exit;
}
if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: register_page.php?reg_error=' . urlencode('Error de seguridad. Intente de nuevo.'));
    exit;
}

// 4. Obtener y Limpiar Datos
$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// 5. Validar Datos
$errors = [];
if (!Validator::isNotEmpty($nombre) || !Validator::isNotEmpty($apellidos) || !Validator::isNotEmpty($email) || !Validator::isNotEmpty($password)) {
    $errors[] = "Todos los campos son obligatorios";
}
if ($email && !Validator::isValidEmail($email)) {
    $errors[] = "El formato del email no es válido";
}
if ($password && !Validator::isStrongPassword($password)) {
    $errors[] = 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.';
}

// 6. Si hay errores, redirigir
if (!empty($errors)) {
    // Redirigir con el primer error encontrado
    header('Location: register_page.php?reg_error=' . urlencode($errors[0]));
    exit;
}

// 7. Lógica de Registro
try {
    $pdo = Database::getInstance();
    $auth = new Auth($pdo);

    $result = $auth->register($nombre, $apellidos, $email, $password);

    if ($result) {
        // Éxito
        header('Location: index.php?success=' . urlencode('Registro completado con éxito. Por favor, inicia sesión.'));
        exit;
    } else {
        // Email duplicado (es el único caso en que Auth::register() devuelve false)
        header('Location: register_page.php?reg_error=' . urlencode('El email introducido ya está registrado'));
        exit;
    }

} catch (\PDOException $e) {
    // Manejo de error de BD
    error_log('Error en register.php: ' . $e->getMessage());
    header('Location: register_page.php?reg_error=' . urlencode('Error en el registro. Inténtelo de nuevo.'));
    exit;
}