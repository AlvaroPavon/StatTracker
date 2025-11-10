<?php
// 1. Cargar el autoloader
require 'vendor/autoload.php';

// 2. Usar el namespace de nuestra clase
use App\Metrics;

// 3. CORRECCIÓN: Mover la lógica de sesión DENTRO del IF
if (session_status() === PHP_SESSION_NONE) {
    // Solo cargamos la config si la sesión no está activa
    require 'session_config.php'; 
    session_start();
}

// 4. Cargar la conexión a la BD ($pdo)
// (db.php ahora solo trae $pdo)
require 'db.php'; 

// 5. Establecer la cabecera de respuesta como JSON
header('Content-Type: application/json');

// 6. Verificar que el usuario esté logueado
// (La prueba ya seteó $_SESSION['user_id'] en la sesión activa)
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // No autorizado
    echo json_encode(['success' => false, 'message' => 'Error: No autorizado. Inicie sesión.']);
    exit();
}

// 7. Verificar el método de solicitud (debe ser GET)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Método no permitido
    echo json_encode(['success' => false, 'message' => 'Error: Método no permitido, se esperaba GET.']);
    exit();
}

// 8. Obtener el ID de usuario de la sesión
$user_id = (int)$_SESSION['user_id'];

// 9. Instanciar nuestra clase de lógica
$metrics = new Metrics($pdo);

// 10. Llamar a la lógica (que ahora usa la tabla 'metricas')
$result = $metrics->getHealthData($user_id);

// 11. Comprobar el resultado y devolver JSON
if (is_array($result)) {
    // ÉXITO
    http_response_code(200); // OK
    echo json_encode([
        'success' => true, 
        'data' => $result
    ]);
} else {
    // ERROR
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false, 
        'message' => $result
    ]);
}
exit();
?>