<?php
/**
 * get_data.php - Obtener métricas de salud (API JSON segura)
 * @package StatTracker
 */

// 1. Inicializar seguridad (WAF + Headers + Session)
require __DIR__ . '/security_init.php';

// 2. Cargar la conexión a la BD ($pdo)
require __DIR__ . '/db.php';

use App\Metrics;
use App\Security;
use App\SecurityHeaders;
use App\SessionManager;

// 3. Aplicar headers de seguridad para JSON
SecurityHeaders::applyJsonHeaders();

/**
 * Función auxiliar para devolver respuestas JSON limpias
 */
function send_json_response(array $data, int $statusCode = 200): void
{
    if (!headers_sent()) {
        http_response_code($statusCode);
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}

// 4. Validar sesión
if (!SessionManager::isAuthenticated()) {
    send_json_response(
        ['success' => false, 'message' => 'No autorizado. Inicie sesión.'],
        401
    );
    return;
}

// 5. Validar Token CSRF
if (!Security::validateCsrfToken($_GET['token'] ?? null)) {
    send_json_response(
        ['success' => false, 'message' => 'Error de seguridad.'],
        403
    );
    return;
}

// 6. Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json_response(
        ['success' => false, 'message' => 'Método no permitido.'],
        405
    );
    return;
}

// 7. Obtener el ID de usuario y lógica principal
$user_id = SessionManager::getUserId();
$metrics = new Metrics($pdo);
$result = $metrics->getHealthData($user_id);

// 8. Respuesta final
if (is_array($result)) {
    send_json_response([
        'success' => true,
        'data' => $result
    ], 200);
} else {
    send_json_response([
        'success' => false,
        'message' => $result
    ], 500);
}

return;