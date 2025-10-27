<?php
// 1. REFINAMIENTO (CSRF): Iniciar la sesión para acceder al token
session_start(); 

// 2. Incluir la conexión a la BD
require 'db.php'; 

// 3. Verificar que la solicitud sea por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. REFINAMIENTO (CSRF): Validar el token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // Si el token no coincide, es un ataque CSRF o una sesión expirada
        header('Location: index.php?login_error=Error de seguridad. Intente de nuevo.');
        exit;
    }
    
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 5. Validación de campos vacíos
    if (empty($email) || empty($password)) {
        header('Location: index.php?login_error=Email y contraseña son requeridos');
        exit; // Detener script
    }

    try {
        // 6. Refinamiento de Seguridad: Sentencia Preparada
        $sql = "SELECT id, nombre, password FROM usuarios WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        // 7. Obtener el usuario
        $user = $stmt->fetch();

        // 8. Refinamiento de Seguridad: Verificar usuario y contraseña
        if ($user && password_verify($password, $user['password'])) {
            
            // 9. ¡Refinamiento de Seguridad CRÍTICO!
            // Regenerar el ID de la sesión para prevenir fijación de sesión
            // Borra el token CSRF antiguo y regenera uno nuevo.
            session_regenerate_id(true);
            
            // 10. Guardar datos en la sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];

            // 11. REFINAMIENTO (CSRF): Generar un nuevo token para el dashboard
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
            // 12. Redirigir al panel de control (dashboard)
            header('Location: dashboard.php');
            exit; // Detener script

        } else {
            // 13. Credenciales incorrectas
            header('Location: index.php?login_error=Email o contraseña incorrectos');
            exit; // Detener script
        }

    } catch (PDOException $e) {
        // 14. REFINAMIENTO: Manejo de Errores de Producción
        error_log('Error en login.php: ' . $e->getMessage());
        header('Location: index.php?login_error=Error interno. Inténtelo de nuevo.');
        exit; // Detener script
    }

} else {
    // 15. Si alguien intenta acceder a login.php directamente
    header('Location: index.php');
    exit; // Detener script
}
?>