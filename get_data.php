<?php
// 1. Autoloader (Composer)
require __DIR__ . '/vendor/autoload.php';

use App\Metrics;
use App\Security;
use App\SecurityHeaders;

// 2. Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    require __DIR__ . '/session_config.php';
    session_start();
}

// 3. Cargar conexión a la BD ($pdo)
require __DIR__ . '/db.php';

// 4. Aplicar headers de seguridad para JSON
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

// 5. Validar sesión
if (!isset($_SESSION['user_id'])) {
    send_json_response(
        ['success' => false, 'message' => 'No autorizado. Inicie sesión.'],
        401
    );
    return;
}

// 6. Validar Token CSRF
if (!Security::validateCsrfToken($_GET['token'] ?? null)) {
    send_json_response(
        ['success' => false, 'message' => 'Error de seguridad.'],
        403
    );
    return;
}

// 7. Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json_response(
        ['success' => false, 'message' => 'Método no permitido.'],
        405
    );
    return;
}

// 8. Obtener el ID de usuario y lógica principal
$user_id = (int) $_SESSION['user_id'];
$metrics = new Metrics($pdo);
$result = $metrics->getHealthData($user_id);

// 9. Respuesta final
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