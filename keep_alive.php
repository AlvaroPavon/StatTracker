<?php
/**
 * keep_alive.php - Endpoint para mantener la sesión activa
 * Usado por SessionTimeout.js para extender la sesión
 * @package StatTracker
 */

// Inicializar seguridad
require __DIR__ . '/security_init.php';

use App\SessionManager;
use App\SecurityAudit;

// Solo aceptar POST y AJAX
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Verificar que es una petición AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$isAjax) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

// Establecer header JSON
header('Content-Type: application/json');

// Verificar autenticación
if (!SessionManager::isAuthenticated()) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'error' => 'Not authenticated',
        'redirect' => 'index.php'
    ]);
    exit;
}

// Obtener datos del request
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

// Procesar acción
switch ($action) {
    case 'extend':
        // Actualizar tiempo de última actividad
        $_SESSION['_security']['last_activity'] = time();
        
        // Calcular tiempos
        $sessionInfo = SessionManager::getInfo();
        $idleSeconds = $sessionInfo['idle_seconds'] ?? 0;
        $remainingIdle = 1800 - $idleSeconds; // 30 minutos de idle máximo
        $remainingTotal = 3600 - ($sessionInfo['age_seconds'] ?? 0); // 1 hora de vida máxima
        
        // Registrar extensión (solo cada 5 minutos para no saturar logs)
        if (!isset($_SESSION['_last_keepalive']) || 
            time() - $_SESSION['_last_keepalive'] > 300) {
            SecurityAudit::log('SESSION_EXTENDED', SessionManager::getUserId(), [
                'idle_seconds' => $idleSeconds
            ], 'INFO');
            $_SESSION['_last_keepalive'] = time();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Session extended',
            'remaining_idle' => max(0, $remainingIdle),
            'remaining_total' => max(0, $remainingTotal),
            'server_time' => date('Y-m-d H:i:s')
        ]);
        break;
        
    case 'status':
        // Solo devolver estado de la sesión
        $sessionInfo = SessionManager::getInfo();
        
        echo json_encode([
            'success' => true,
            'authenticated' => true,
            'user_id' => SessionManager::getUserId(),
            'idle_seconds' => $sessionInfo['idle_seconds'] ?? 0,
            'age_seconds' => $sessionInfo['age_seconds'] ?? 0,
            'remaining_idle' => max(0, 1800 - ($sessionInfo['idle_seconds'] ?? 0)),
            'remaining_total' => max(0, 3600 - ($sessionInfo['age_seconds'] ?? 0))
        ]);
        break;
        
    case 'ping':
        // Simple ping para verificar conexión
        echo json_encode([
            'success' => true,
            'pong' => true,
            'time' => time()
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action'
        ]);
}
