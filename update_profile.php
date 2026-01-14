<?php
/**
 * update_profile.php - Actualización de perfil segura
 * @package StatTracker
 */

// 1. Inicializar seguridad
require __DIR__ . '/security_init.php';
require __DIR__ . '/db.php';

use App\User;
use App\Security;
use App\SessionManager;
use App\InputSanitizer;
use App\SecurityAudit;
use App\RateLimiter;

// 2. Verificar autenticación
require_auth();

// 3. Verificar el método de solicitud
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 4. Validar CSRF token
    verify_csrf_or_die();
    
    // 5. Rate limiting para cambios de perfil
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
    $rateLimiter = new RateLimiter('api', $clientIp);
    $rateCheck = $rateLimiter->isAllowed();
    
    if (!$rateCheck['allowed']) {
        header("Location: profile.php?error=" . urlencode($rateCheck['message']));
        exit();
    }
    
    $user_id = SessionManager::getUserId();
    $form_type = InputSanitizer::sanitizeString($_POST['form_type'] ?? '');
    
    // 6. Instanciar la clase User
    $user = new User($pdo);
    
    // 7. Determinar qué formulario se envió
    if ($form_type === 'photo' && isset($_FILES['profile_pic'])) {
        // Subida de foto de perfil
        $result = $user->updateProfilePicture($user_id, $_FILES['profile_pic']);
        
        if ($result === true) {
            SecurityAudit::log('PROFILE_PHOTO_UPDATED', $user_id);
            header("Location: profile.php?success=" . urlencode("Foto actualizada correctamente."));
        } else {
            header("Location: profile.php?error=" . urlencode($result));
        }
        exit();
        
    } elseif ($form_type === 'info') {
        // Actualización de datos personales
        $nombre = InputSanitizer::sanitizeString($_POST['nombre'] ?? '');
        $apellidos = InputSanitizer::sanitizeString($_POST['apellidos'] ?? '');
        $email = InputSanitizer::sanitizeEmail($_POST['email'] ?? '');
        
        $result = $user->updateProfile($user_id, $nombre, $apellidos, $email);
        
        if ($result === true) {
            // Actualizar nombre en sesión
            $_SESSION['nombre'] = $nombre;
            SecurityAudit::log('PROFILE_INFO_UPDATED', $user_id);
            header("Location: profile.php?success=" . urlencode("Perfil actualizado correctamente."));
        } else {
            header("Location: profile.php?error=" . urlencode($result));
        }
        exit();
    }
    
    // Si no se reconoce el tipo de formulario
    header("Location: profile.php?error=" . urlencode("Formulario no válido."));
    exit();
    
} else {
    header("Location: profile.php");
    exit();
}
?>
            header("Location: profile.php?success=" . urlencode("Foto actualizada con éxito."));
            exit();
        } else {
            header("Location: profile.php?error=" . urlencode($result));
            exit();
        }
        
    } elseif ($form_type === 'details') {
        // Actualización de datos del perfil
        $nombre = $_POST['nombre'] ?? '';
        $apellidos = $_POST['apellidos'] ?? '';
        $email = $_POST['email'] ?? '';

        $result = $user->updateProfile($user_id, $nombre, $apellidos, $email);

        if ($result === true) {
            $_SESSION['nombre'] = $nombre;
            header("Location: profile.php?success=" . urlencode("Perfil actualizado con éxito."));
            exit();
        } else {
            header("Location: profile.php?error=" . urlencode($result));
            exit();
        }
    } else {
        header("Location: profile.php?error=" . urlencode("Formulario inválido."));
        exit();
    }

} else {
    header("Location: profile.php");
    exit();
}
?>