<?php
/**
 * delete_data.php - Eliminar métricas de salud (API JSON segura)
 * @package StatTracker
 */

// 1. Inicializar seguridad (WAF + Headers + Session)
require __DIR__ . '/security_init.php';

// 2. Cargar la conexión a la BD ($pdo)
require __DIR__ . '/db.php'; 

// 3. Usar namespaces
use App\Metrics;
use App\Security;
use App\SecurityHeaders;
use App\SessionManager;
use App\InputSanitizer;

// 4. Aplicar headers de seguridad para JSON
SecurityHeaders::applyJsonHeaders();

// 5. Verificar que el usuario esté logueado
if (!SessionManager::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado.'], JSON_UNESCAPED_UNICODE);
    exit();
}

// 6. Verificar el método de solicitud (debe ser POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    exit();
}

// 7. Leer los datos JSON crudos
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// Verificar que el JSON es válido
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'JSON inválido.'], JSON_UNESCAPED_UNICODE);
    exit();
}

// 8. Validar Token CSRF
if (!Security::validateCsrfToken($data['token'] ?? null)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Error de seguridad.'], JSON_UNESCAPED_UNICODE);
    exit();
}

// 9. Validar que el ID del registro a borrar fue enviado
$dataId = InputSanitizer::sanitizeInt($data['id'] ?? 0);
if ($dataId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de registro inválido.'], JSON_UNESCAPED_UNICODE);
    exit();
}

$user_id = SessionManager::getUserId();

// 10. Instanciar nuestra clase de lógica
$metrics = new Metrics($pdo);

// 11. Llamar a la lógica (incluye verificación de propiedad)
$result = $metrics->deleteHealthData($user_id, $dataId);

// 12. Comprobar el resultado y devolver JSON
if ($result === true) {
    http_response_code(200);
    echo json_encode([
        'success' => true, 
        'message' => 'Registro eliminado con éxito.'
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(404);
    echo json_encode([
        'success' => false, 
        'message' => $result
    ], JSON_UNESCAPED_UNICODE);
}
exit();
?>