<?php
// 1. REFINAMIENTO DE ARQUITECTURA: Incluir 'db.php' ANTES de session_start()
require 'db.php';

// 2. REFINAMIENTO (CSRF): Iniciar la sesión para acceder al token
session_start();

// 3. Verificar que la solicitud sea por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. REFINAMIENTO (CSRF): Validar el token
    // Comprobar que el token enviado coincida con el de la sesión
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // Si el token no coincide, es un ataque CSRF o una sesión expirada
        header('Location: index.php?reg_error=Error de seguridad. Intente de nuevo.');
        exit;
    }
    
    // 5. Limpiar y validar datos de entrada
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 6. Validaciones de campos
    if (empty($nombre) || empty($apellidos) || empty($email) || empty($password)) {
        header('Location: index.php?reg_error=Todos los campos son obligatorios');
        exit; // Detener script
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: index.php?reg_error=El formato del email no es válido');
        exit; // Detener script
    }

    // 7. Validación de Contraseña Fuerte (Refinamiento anterior)
    $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/';
    if (strlen($password) < 8 || !preg_match($regex, $password)) {
        $error_msg = 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.';
        header('Location: index.php?reg_error=' . urlencode($error_msg));
        exit;
    }

    // 8. Refinamiento de Seguridad: Hashear la contraseña
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        // 9. Refinamiento de Seguridad: Sentencia Preparada
        $sql = "INSERT INTO usuarios (nombre, apellidos, email, password) 
                VALUES (:nombre, :apellidos, :email, :password)";
        
        $stmt = $pdo->prepare($sql);
        
        // 10. Ejecutar la consulta
        $stmt->execute([
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'email' => $email,
            'password' => $hashed_password
        ]);

        // 11. Redirección exitosa
        header('Location: index.php?success=Registro completado con éxito. Por favor, inicia sesión.');
        exit; // Detener script

    } catch (PDOException $e) {
        // 12. REFINAMIENTO: Manejo de Errores de Producción
        if ($e->getCode() == 23000) {
            header('Location: index.php?reg_error=El email introducido ya está registrado');
            exit; // Detener script
        } else {
            error_log('Error en register.php: ' . $e->getMessage());
            header('Location: index.php?reg_error=Error en el registro. Inténtelo de nuevo.');
            exit; // Detener script
        }
    }

} else {
    // 13. Si alguien intenta acceder a register.php directamente
    header('Location: index.php');
    exit; // Detener script
}
?>