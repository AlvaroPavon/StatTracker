<?php
// 1. Cargar el autoloader de Composer
require 'vendor/autoload.php';

// 2. Cargar la conexión a la BD ($pdo)
require 'db.php'; 

// 3. Usar el namespace de nuestra clase
use App\Auth;

// 4. Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 5. Comprobar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 6. Validar CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['login_error'] = "Error de seguridad (CSRF). Por favor, inténtelo de nuevo desde el formulario.";
        header("Location: index.php");
        exit();
    }
    unset($_SESSION['csrf_token']);

    // Obtenemos los datos
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // 7. Instanciar nuestra clase de lógica
    $auth = new Auth($pdo);

    // 8. Llamar a la lógica de login (Auth::login ahora devuelve [id, nombre])
    $result = $auth->login($email, $password);

    // 9. Comprobar el resultado
    if (is_array($result)) {
        // ÉXITO

        session_regenerate_id(true);

        // CORRECCIÓN: Guardar 'nombre' (de la BD 'usuarios') en la sesión.
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['nombre'] = $result['nombre']; // Antes era 'username'

        // Redirigir al dashboard
        header("Location: dashboard.php");
        exit();

    } else {
        // ERROR
        $_SESSION['login_error'] = $result;
        header("Location: index.php");
        exit();
    }
    
} else {
    header("Location: index.php");
    exit();
}
?>