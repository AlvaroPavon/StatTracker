<?php
// 1. Incluir la conexión a la base de datos
require 'db.php';

// 2. Verificar que la solicitud sea por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3. Limpiar y validar datos de entrada
    // trim() elimina espacios en blanco al inicio y al final
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 4. Validaciones de campos
    if (empty($nombre) || empty($apellidos) || empty($email) || empty($password)) {
        header('Location: index.php?reg_error=Todos los campos son obligatorios');
        exit; // Detener script
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: index.php?reg_error=El formato del email no es válido');
        exit; // Detener script
    }

    if (strlen($password) < 6) {
        header('Location: index.php?reg_error=La contraseña debe tener al menos 6 caracteres');
        exit; // Detener script
    }

    // 5. Refinamiento de Seguridad: Hashear la contraseña
    // PASSWORD_BCRYPT es el algoritmo recomendado actualmente
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        // 6. Refinamiento de Seguridad: Sentencia Preparada (Previene Inyección SQL)
        $sql = "INSERT INTO usuarios (nombre, apellidos, email, password) 
                VALUES (:nombre, :apellidos, :email, :password)";
        
        $stmt = $pdo->prepare($sql);
        
        // 7. Ejecutar la consulta pasando los datos de forma segura
        $stmt->execute([
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'email' => $email,
            'password' => $hashed_password
        ]);

        // 8. Redirección exitosa
        header('Location: index.php?success=Registro completado con éxito. Por favor, inicia sesión.');
        exit; // Detener script

    } catch (PDOException $e) {
        // 9. Manejo de Errores
        
        // Código de error '23000' es para violación de restricción (ej. 'UNIQUE' key)
        if ($e->getCode() == 23000) {
            header('Location: index.php?reg_error=El email introducido ya está registrado');
            exit; // Detener script
        } else {
            // Otro error de base de datos
            // En un entorno de producción, no mostrarías $e->getMessage() al usuario
            header('Location: index.php?reg_error=Error en el registro. Inténtelo de nuevo.');
            exit; // Detener script
        }
    }

} else {
    // 10. Si alguien intenta acceder a register.php directamente
    header('Location: index.php');
    exit; // Detener script
}
?>