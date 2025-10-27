<?php
// 1. Iniciar la sesión ANTES de cualquier salida
session_start(); 

// 2. Incluir la conexión a la BD
require 'db.php'; 

// 3. Verificar que la solicitud sea por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 4. Validación de campos vacíos
    if (empty($email) || empty($password)) {
        header('Location: index.php?login_error=Email y contraseña son requeridos');
        exit; // Detener script
    }

    try {
        // 5. Refinamiento de Seguridad: Sentencia Preparada (Previene Inyección SQL)
        $sql = "SELECT id, nombre, password FROM usuarios WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        // 6. Obtener el usuario
        $user = $stmt->fetch();

        // 7. Refinamiento de Seguridad: Verificar usuario y contraseña
        // Primero, comprueba si se encontró un usuario ($user)
        // Segundo, usa password_verify() para comparar la contraseña de forma segura
        if ($user && password_verify($password, $user['password'])) {
            
            // 8. ¡Refinamiento de Seguridad CRÍTICO!
            // Regenerar el ID de la sesión para prevenir fijación de sesión
            session_regenerate_id(true);
            
            // 9. Guardar datos en la sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            
            // 10. Redirigir al panel de control (dashboard)
            header('Location: dashboard.php');
            exit; // Detener script

        } else {
            // 11. Credenciales incorrectas (Email o contraseña no válidos)
            header('Location: index.php?login_error=Email o contraseña incorrectos');
            exit; // Detener script
        }

    } catch (PDOException $e) {
        // 12. Error de base de datos
        header('Location: index.php?login_error=Error interno. Inténtelo de nuevo.');
        exit; // Detener script
    }

} else {
    // 13. Si alguien intenta acceder a login.php directamente
    header('Location: index.php');
    exit; // Detener script
}
?>