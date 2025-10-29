<?php declare(strict_types=1);

// 1. Cargar Autoloader y Clases
// __DIR__ . '/../' sube un nivel desde /public/ a la raíz del proyecto
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
    header('Location: index.php'); // Redirigir si no es POST
    exit;
}

if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: index.php?login_error=' . urlencode('Error de seguridad. Intente de nuevo.'));
    exit;
}

// 4. Obtener Datos
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// 5. Validar Entradas Simples
if (!Validator::isNotEmpty($email) || !Validator::isNotEmpty($password)) {
    header('Location: index.php?login_error=' . urlencode('Email y contraseña son requeridos'));
    exit;
}

// 6. Lógica de Autenticación
try {
    $pdo = Database::getInstance();
    $auth = new Auth($pdo);

    $user = $auth->login($email, $password);

    if ($user) {
        // ¡Éxito! Regenerar sesión y guardar datos
        Session::regenerateId(); // Previene fijación de sesión
        
        Session::set('user_id', $user['id']);
        Session::set('user_nombre', $user['nombre']);
        Session::set('show_welcome_splash', true);
        
        // Generar nuevo token CSRF para el dashboard
        Session::regenerateCsrfToken(); 

        header('Location: dashboard.php');
        exit;

    } else {
        // Fallo de login (email o contraseña incorrectos)
        header('Location: index.php?login_error=' . urlencode('Email o contraseña incorrectos'));
        exit;
    }

} catch (\PDOException $e) {
    // Error de base de datos
    error_log('Error en login.php: ' . $e->getMessage());
    header('Location: index.php?login_error=' . urlencode('Error interno. Inténtelo de nuevo.'));
    exit;
}