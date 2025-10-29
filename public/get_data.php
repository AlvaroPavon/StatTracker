<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Session;
use App\Metrics;
use PDOException;

// Establecer cabecera JSON desde el principio
header('Content-Type: application/json');

Session::init();

// 1. Autenticación de API
if (!Session::has('user_id')) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Acceso no autorizado. Inicie sesión.']);
    exit;
}

// 2. Validación de Token CSRF
if (!Session::validateCsrfToken($_GET['token'] ?? '')) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Error de seguridad (Token CSRF inválido)']);
    exit;
}

// 3. Obtener Datos
$user_id = Session::get('user_id');

try {
    $pdo = Database::getInstance();
    $metrics = new Metrics($pdo);
    
    $datos = $metrics->getMetricsForUser((int)$user_id);
    
    echo json_encode($datos);
    exit;

} catch (\PDOException $e) {
    error_log('Error en get_data.php: ' . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Error interno al obtener los datos.']);
    exit;
}