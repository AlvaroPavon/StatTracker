<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Session;
use App\Metrics;
use PDOException;

header('Content-Type: application/json');

Session::init();

// 1. Autenticación
if (!Session::has('user_id')) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

// 2. Validación de Token CSRF
if (!Session::validateCsrfToken($_GET['token'] ?? '')) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Error de seguridad (Token CSRF inválido)']);
    exit;
}

// 3. Obtener ID a borrar
$user_id = Session::get('user_id');
$metric_id = $_GET['id'] ?? null;

if (!is_numeric($metric_id)) {
     http_response_code(400); // Bad Request
    echo json_encode(['error' => 'No se especificó un ID de registro válido']);
    exit;
}

// 4. Procesar
try {
    $pdo = Database::getInstance();
    $metrics = new Metrics($pdo);
    
    $success = $metrics->deleteMetric((int)$metric_id, (int)$user_id);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Registro eliminado']);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'El registro no se encontró o no tiene permiso para eliminarlo']);
    }
    exit;

} catch (\PDOException $e) {
    error_log('Error en delete_data.php: ' . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Error interno al eliminar el registro.']);
    exit;
}