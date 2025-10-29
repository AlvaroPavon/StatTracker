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

    // 6. Obtener ID de usuario y datos del formulario
    $user_id = $_SESSION['user_id'];
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);

    // 7. Validaciones de campos
    if (empty($nombre) || empty($apellidos) || empty($email)) {
        header('Location: profile.php?error=Nombre, apellidos y email son obligatorios.');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: profile.php?error=El formato del email no es válido.');
        exit;
    }

    // 8. Manejo de la subida de la foto de perfil
    $profile_pic_filename = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
        
        $file = $_FILES['profile_pic'];
        $upload_dir = 'uploads/'; // La carpeta que creamos
        
        // Validar tipo de archivo (MIME)
        $allowed_types = ['image/jpeg', 'image/png'];
        if (!in_array($file['type'], $allowed_types)) {
            header('Location: profile.php?error=Formato de imagen no válido. Solo se permite JPG o PNG.');
            exit;
        }

        // Validar tamaño (ej: max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            header('Location: profile.php?error=La imagen es demasiado grande (máx 2MB).');
            exit;
        }

        // Generar un nombre de archivo único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $profile_pic_filename = 'user_' . $user_id . '_' . uniqid() . '.' . $extension;
        $upload_path = $upload_dir . $profile_pic_filename;

        // Mover el archivo
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            error_log('Error al mover el archivo subido a: ' . $upload_path);
            header('Location: profile.php?error=Error interno al guardar la imagen.');
            exit;
        }
    }

    try {
        // 9. Actualizar la base de datos
        
        // Si se subió una nueva foto, la consulta la incluye
        if ($profile_pic_filename) {
            $sql = "UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, email = :email, profile_pic = :profile_pic 
                    WHERE id = :id";
            $params = [
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'email' => $email,
                'profile_pic' => $profile_pic_filename,
                'id' => $user_id
            ];
        } else {
            // Si no se subió foto, la consulta no toca esa columna
            $sql = "UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, email = :email 
                    WHERE id = :id";
            $params = [
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'email' => $email,
                'id' => $user_id
            ];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // 10. Actualizar el nombre en la sesión (por si cambió)
        $_SESSION['user_nombre'] = $nombre;

        // 11. Redirección exitosa
        header('Location: profile.php?success=Perfil actualizado con éxito.');
        exit;

    } catch (PDOException $e) {
        // Manejo de error de email duplicado
        if ($e->getCode() == 23000) {
            header('Location: profile.php?error=El email introducido ya está registrado por otro usuario.');
            exit;
        } else {
            error_log('Error en update_profile.php: ' . $e->getMessage());
            header('Location: profile.php?error=Error en la base de datos. Inténtelo de nuevo.');
            exit;
        }
    }

} else {
    // Si alguien intenta acceder directamente
    header('Location: profile.php');
    exit;
}
?>