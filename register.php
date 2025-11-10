<?php
// 1. Cargar el autoloader de Composer para encontrar la clase App\Auth
require 'vendor/autoload.php';

// 2. Cargar la configuración de sesión y la conexión a la BD ($pdo)
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
        $_SESSION['register_error'] = "Error de seguridad (CSRF). Por favor, inténtelo de nuevo desde el formulario.";
        header("Location: register_page.php");
        exit();
    }
    unset($_SESSION['csrf_token']);

    // 7. CORRECCIÓN: Obtener 'nombre' y 'apellidos'
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // 8. Instanciar nuestra clase de lógica
    $auth = new Auth($pdo);

    // 9. CORRECCIÓN: Llamar a la lógica de registro con los campos correctos
    $result = $auth->register($nombre, $apellidos, $email, $password);

    // 10. Comprobar el resultado
    if (is_int($result)) {
        // ÉXITO: $result es el nuevo user_id

        // Iniciar sesión
        $_SESSION['user_id'] = $result;
        
        // CORRECCIÓN: Guardar 'nombre' (de la BD 'usuarios') en la sesión.
        $_SESSION['nombre'] = $nombre; // Antes era 'username'
        
        session_regenerate_id(true);

        // Redirigir al dashboard
        header("Location: dashboard.php");
        exit();

    } else {
        // ERROR: $result es un string con el mensaje de error
        $_SESSION['register_error'] = $result;
        header("Location: register_page.php");
        exit();
    }
    
} else {
    header("Location: index.php");
    exit();
}
?>