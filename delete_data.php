<?php
// 1. Cargar el autoloader de Composer
require __DIR__ . '/vendor/autoload.php';

// 2. Cargar la configuración de sesión ANTES de session_start()
require __DIR__ . '/session_config.php';

// 3. Cargar la conexión a la BD ($pdo)
require __DIR__ . '/db.php'; 

// 4. Usar namespaces
use App\Metrics;
use App\Security;
use App\SecurityHeaders;

// 5. Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 6. Aplicar headers de seguridad para JSON
SecurityHeaders::applyJsonHeaders();

// 7. Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado.'], JSON_UNESCAPED_UNICODE);
    exit();
}

// 8. Verificar el método de solicitud (debe ser POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    exit();
}

// 9. Leer los datos JSON crudos
$data = json_decode(file_get_contents('php://input'), true);

// 10. Validar Token CSRF
if (!Security::validateCsrfToken($data['token'] ?? null)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Error de seguridad.'], JSON_UNESCAPED_UNICODE);
    exit();
}

// 11. Validar que el ID del registro a borrar fue enviado
if (!isset($data['id']) || !is_numeric($data['id']) || (int)$data['id'] <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de registro inválido.'], JSON_UNESCAPED_UNICODE);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$data_id = (int)$data['id'];

// 12. Instanciar nuestra clase de lógica
$metrics = new Metrics($pdo);

// 13. Llamar a la lógica
$result = $metrics->deleteHealthData($user_id, $data_id);

// 14. Comprobar el resultado y devolver JSON
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