<?php
// 1. Cargar el autoloader de Composer para encontrar la clase App\Metrics
require 'vendor/autoload.php';

// 2. Cargar la configuración de sesión y la conexión a la BD ($pdo)
require 'db.php'; 

// 3. Usar el namespace de nuestra clase
use App\Metrics;

// 4. Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 5. Establecer la cabecera de respuesta como JSON
header('Content-Type: application/json');

// 6. Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // No autorizado
    echo json_encode(['success' => false, 'message' => 'Error: No autorizado. Inicie sesión.']);
    exit();
}

// 7. Verificar el método de solicitud (debe ser POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    echo json_encode(['success' => false, 'message' => 'Error: Método no permitido.']);
    exit();
}

// 8. Leer los datos JSON crudos
$data = json_decode(file_get_contents('php://input'), true);

// Validar que el ID del registro a borrar fue enviado
if (!isset($data['id']) || !is_numeric($data['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Error: ID de registro inválido o no proporcionado.']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$data_id = (int)$data['id'];

// 9. Instanciar nuestra clase de lógica
$metrics = new Metrics($pdo);

// 10. Llamar a la lógica (que ahora usa la tabla 'metricas')
$result = $metrics->deleteHealthData($user_id, $data_id);

// 11. Comprobar el resultado y devolver JSON
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