<?php
// 1. REFINAMIENTO DE ARQUITECTURA: Incluir 'db.php' ANTES de session_start()
require 'db.php';

// 2. Iniciar la sesión
session_start();

// 3. Refinamiento de Seguridad: Autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // No autorizado
    exit;
}

// 4. Verificar que la solicitud sea por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 5. REFINAMIENTO (CSRF): Validar el token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header('Location: profile.php?error=Error de seguridad. Intente de nuevo.');
        exit;
    }

    // 6. Obtener ID de usuario y contraseñas
    $user_id = $_SESSION['user_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // 7. Validaciones
    if (empty($old_password) || empty($new_password) || empty($confirm_new_password)) {
        header('Location: profile.php?error=Todos los campos de contraseña son obligatorios.');
        exit;
    }

    if ($new_password !== $confirm_new_password) {
        header('Location: profile.php?error=Las nuevas contraseñas no coinciden.');
        exit;
    }

    // 8. Validación de Contraseña Fuerte (misma que en register.php)
    $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/';
    if (strlen($new_password) < 8 || !preg_match($regex, $new_password)) {
        $error_msg = 'La nueva contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.';
        header('Location: profile.php?error=' . urlencode($error_msg));
        exit;
    }

    try {
        // 9. Obtener la contraseña actual (hash) del usuario
        $sql_select = "SELECT password FROM usuarios WHERE id = :id";
        $stmt_select = $pdo->prepare($sql_select);
        $stmt_select->execute(['id' => $user_id]);
        $user = $stmt_select->fetch();

        if (!$user) {
            header('Location: profile.php?error=Error de usuario. Intente iniciar sesión de nuevo.');
            exit;
        }

        // 10. Verificar que la contraseña anterior sea correcta
        if (password_verify($old_password, $user['password'])) {
            
            // 11. Hashear la nueva contraseña
            $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);

            // 12. Actualizar la contraseña en la BD
            $sql_update = "UPDATE usuarios SET password = :password WHERE id = :id";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([
                'password' => $hashed_new_password,
                'id' => $user_id
            ]);

            // 13. Redirección exitosa
            header('Location: profile.php?success=Contraseña actualizada con éxito.');
            exit;

        } else {
            // Contraseña anterior incorrecta
            header('Location: profile.php?error=La contraseña anterior es incorrecta.');
            exit;
        }

    } catch (PDOException $e) {
        error_log('Error en change_password.php: ' . $e->getMessage());
        header('Location: profile.php?error=Error en la base de datos. Inténtelo de nuevo.');
        exit;
    }

} else {
    // Si alguien intenta acceder directamente
    header('Location: profile.php');
    exit;
}
?>