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

// 3. Validar el token CSRF
if (!Security::validateCsrfToken($_GET['token'] ?? null)) {
    header('Location: dashboard.php?error=' . urlencode('Error de seguridad.'));
    exit;
}

// 4. Registrar logout antes de destruir sesión
$userId = SessionManager::getUserId();
if ($userId) {
    SecurityAudit::log('LOGOUT', $userId, ['method' => 'user_initiated']);
}

// 5. Destruir sesión de forma segura
SessionManager::destroy();

// 6. Redirigir al formulario de login
header('Location: index.php');
exit;
?>