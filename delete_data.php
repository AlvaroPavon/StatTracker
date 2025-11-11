<?php
// 1. Cargar el autoloader de Composer para encontrar la clase App\Metrics
require __DIR__ . '/vendor/autoload.php';

// 2. MODIFICACIÓN (BUG): Cargar la configuración de sesión ANTES de session_start()
require __DIR__ . '/session_config.php';

// 3. Cargar la conexión a la BD ($pdo)
require __DIR__ . '/db.php'; 

// 4. Usar el namespace de nuestra clase
use App\Metrics;

// 5. Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 6. Establecer la cabecera de respuesta como JSON
header('Content-Type: application/json');

// 7. Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // No autorizado
    echo json_encode(['success' => false, 'message' => 'Error: No autorizado. Inicie sesión.']);
    exit();
}

// 8. Verificar el método de solicitud (debe ser POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    echo json_encode(['success' => false, 'message' => 'Error: Método no permitido, se esperaba POST.']);
    exit();
}

// 9. Leer los datos JSON crudos
$data = json_decode(file_get_contents('php://input'), true);

// 10. MODIFICACIÓN (SEGURIDAD): Validar Token CSRF
// Ahora leemos el token desde el cuerpo JSON
if (!isset($data['token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $data['token'])) {
    http_response_code(403); // Prohibido
    echo json_encode(['success' => false, 'message' => 'Error: Token CSRF no válido o ausente.']);
    exit();
}

// 11. Validar que el ID del registro a borrar fue enviado
if (!isset($data['id']) || !is_numeric($data['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Error: ID de registro inválido o no proporcionado.']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$data_id = (int)$data['id'];

// 12. Instanciar nuestra clase de lógica
$metrics = new Metrics($pdo);

// 13. Llamar a la lógica (que ahora usa la tabla 'metricas')
$result = $metrics->deleteHealthData($user_id, $data_id);

// 14. Comprobar el resultado y devolver JSON
if ($result === true) {
    // ÉXITO
    http_response_code(200); // OK
    echo json_encode([
        'success' => true, 
        'message' => 'Registro borrado con éxito.'
    ]);
} else {
    // ERROR
    http_response_code(404); // No Encontrado (o 403 Prohibido)
    echo json_encode([
        'success' => false, 
        'message' => $result
    ]);
}
exit();
?>