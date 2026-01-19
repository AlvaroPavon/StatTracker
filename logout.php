<?php
/**
 * logout.php - Cierre de sesión seguro
 * @package StatTracker
 */

// 1. Inicializar seguridad
require __DIR__ . '/security_init.php';

use App\Security;
use App\SecurityHeaders;
use App\SessionManager;
use App\SecurityAudit;

// 2. Aplicar headers de no-cache
SecurityHeaders::noCache();

// 3. Obtener razón del logout
$reason = $_GET['reason'] ?? 'user_initiated';
$validReasons = ['user_initiated', 'timeout', 'user_requested', 'security'];

if (!in_array($reason, $validReasons)) {
    $reason = 'user_initiated';
}

// 4. Para logouts por timeout, no requerir CSRF (la sesión puede haber expirado)
if ($reason !== 'timeout') {
    // Validar el token CSRF para logouts manuales
    if (!Security::validateCsrfToken($_GET['token'] ?? null)) {
        header('Location: dashboard.php?error=' . urlencode('Error de seguridad.'));
        exit;
    }
}

// 5. Registrar logout antes de destruir sesión
$userId = SessionManager::getUserId();
if ($userId) {
    SecurityAudit::log('LOGOUT', $userId, [
        'method' => $reason,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]);
}

// 6. Destruir sesión de forma segura
SessionManager::destroy();

// 7. Mensaje según la razón
$messages = [
    'timeout' => 'Tu sesión se cerró automáticamente por inactividad.',
    'user_initiated' => null,
    'user_requested' => 'Has cerrado sesión correctamente.',
    'security' => 'Tu sesión se cerró por motivos de seguridad.'
];

$message = $messages[$reason] ?? null;

// 8. Redirigir al formulario de login
if ($message) {
    $_SESSION['logout_message'] = $message;
}

header('Location: index.php' . ($reason === 'timeout' ? '?timeout=1' : ''));
exit;
?>