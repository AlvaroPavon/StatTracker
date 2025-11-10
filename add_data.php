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

// 8. Leer los datos
$user_id = (int)$_SESSION['user_id'];
$peso = (float)($_POST['peso'] ?? 0);
$altura = (float)($_POST['altura'] ?? 0);
$imc = (float)($_POST['imc'] ?? 0);

// 9. Instanciar nuestra clase de lógica
$metrics = new Metrics($pdo);

// 10. Llamar a la lógica (que ahora usa la tabla 'metricas')
$result = $metrics->addHealthData($user_id, $peso, $altura, $imc);

// 11. Comprobar el resultado y devolver JSON
if (is_int($result)) {
    http_response_code(201); // 201 Creado
    echo json_encode([
        'success' => true, 
        'message' => 'Datos guardados con éxito.',
        'new_data_id' => $result
    ]);
} else {
    // ERROR
    http_response_code(400); // 400 Bad Request
    echo json_encode([
        'success' => false, 
        'message' => $result
    ]);
}
exit();
?>