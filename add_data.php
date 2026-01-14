<?php
/**
 * add_data.php - Agregar métricas de salud (API segura)
 * @package StatTracker
 */

// 1. Inicializar seguridad (WAF + Headers + Session)
require __DIR__ . '/security_init.php';

// 2. Cargar la conexión a la BD ($pdo)
require __DIR__ . '/db.php'; 

// 3. Usar namespaces
use App\Metrics;
use App\Security;
use App\InputSanitizer;
use App\SessionManager;

// 4. Verificar que el usuario esté logueado
require_auth();

// 5. Verificar el método de solicitud (debe ser POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php?error=Método+no+permitido');
    exit();
}

// 6. Validar Token CSRF
verify_csrf_or_die();

// 7. Recoger y sanitizar datos del formulario
$user_id = SessionManager::getUserId();
$altura = InputSanitizer::sanitizeFloat($_POST['altura'] ?? '');
$peso = InputSanitizer::sanitizeFloat($_POST['peso'] ?? '');
$fecha_registro = InputSanitizer::sanitizeString($_POST['fecha_registro'] ?? '');

// 8. Las validaciones adicionales se hacen en la clase Metrics
$metrics = new Metrics($pdo);

// 9. Llamar a la lógica
$result = $metrics->addHealthData($user_id, $peso, $altura, $fecha_registro); 

// 10. Comprobar el resultado y redirigir
if ($result === true) {
    // ÉXITO
    header('Location: dashboard.php');
    exit();
} else {
    // ERROR
    header('Location: dashboard.php?error=' . urlencode($result));
    exit();
}
?>