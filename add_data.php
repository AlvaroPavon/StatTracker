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

// 5. Aplicar headers de seguridad
SecurityHeaders::apply();

// 6. Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 7. Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?error=No+autorizado');
    exit();
}

// 8. Verificar el método de solicitud (debe ser POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php?error=Método+no+permitido');
    exit();
}

// 9. Validar Token CSRF
if (!Security::validateCsrfToken($_POST['csrf_token'] ?? null)) {
    header('Location: dashboard.php?error=Error+de+seguridad');
    exit();
}

// 10. Recoger datos del formulario
$user_id = (int)$_SESSION['user_id'];
$altura = $_POST['altura'] ?? '';
$peso = $_POST['peso'] ?? '';
$fecha_registro = $_POST['fecha_registro'] ?? '';

// 11. Las validaciones se hacen en la clase Metrics
$metrics = new Metrics($pdo);

// 12. Llamar a la lógica
$result = $metrics->addHealthData($user_id, (float)$peso, (float)$altura, $fecha_registro); 

// 13. Comprobar el resultado y redirigir
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