<?php
/**
 * change_password.php - Cambio de contraseña seguro
 * @package StatTracker
 */

// 1. Inicializar seguridad
require __DIR__ . '/security_init.php';
require __DIR__ . '/db.php';

use App\User;
use App\Security;
use App\SessionManager;
use App\SecurityAudit;
use App\RateLimiter;

// 2. Verificar autenticación
require_auth();

// 3. Verificar que la solicitud sea por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. Validar el token CSRF
    verify_csrf_or_die();
    
    // 5. Rate limiting para cambio de contraseña
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
    $rateLimiter = new RateLimiter('password_reset', $clientIp);
    $rateCheck = $rateLimiter->isAllowed();
    
    if (!$rateCheck['allowed']) {
        header('Location: profile.php?error=' . urlencode($rateCheck['message']));
        exit;
    }

    // 6. Obtener ID de usuario y contraseñas
    $user_id = SessionManager::getUserId();
    $old_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_password'] ?? '';

    // 7. Instanciar nuestra clase de lógica
    $user = new User($pdo);

    // 8. Llamar a la lógica
    $result = $user->changePassword($user_id, $old_password, $new_password, $confirm_new_password);
    
    // 9. Comprobar el resultado y redirigir
    if ($result === true) {
        // Registrar cambio de contraseña exitoso
        SecurityAudit::log('PASSWORD_CHANGE', $user_id, ['success' => true]);
        $rateLimiter->recordAttempt(true);
        
        // Regenerar sesión después del cambio de contraseña
        SessionManager::regenerateId();
        Security::regenerateCsrfToken();
        
        header('Location: profile.php?success=' . urlencode("Contraseña actualizada con éxito."));
        exit;
    } else {
        // Registrar intento fallido
        SecurityAudit::log('PASSWORD_CHANGE', $user_id, ['success' => false, 'reason' => $result], 'WARNING');
        $rateLimiter->recordAttempt(false);
        
        header('Location: profile.php?error=' . urlencode($result));
        exit;
    }

} else {
    header('Location: profile.php');
    exit;
}
?>
