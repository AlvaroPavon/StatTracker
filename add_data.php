<?php
// 1. Cargar el autoloader de Composer para encontrar la clase App\Metrics
require __DIR__ . '/vendor/autoload.php';

// 2. Cargar la configuración de sesión ANTES de session_start()
require __DIR__ . '/session_config.php';

// 3. Cargar la conexión a la BD ($pdo)
require __DIR__ . '/db.php'; 

// 4. Usar el namespace de nuestra clase
use App\Metrics;

// 5. Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 6. Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    // Si no está logueado, redirigir al index con error
    header('Location: index.php?error=No+autorizado');
    exit();
}

// 7. Verificar el método de solicitud (debe ser POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php?error=Método+no+permitido');
    exit();
}

// 8. Validar Token CSRF
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header('Location: dashboard.php?error=Token+CSRF+no+válido');
    exit();
}

// 9. Recoger y validar los datos del formulario
$user_id = (int)$_SESSION['user_id'];
$altura = filter_input(INPUT_POST, 'altura', FILTER_VALIDATE_FLOAT);
$peso = filter_input(INPUT_POST, 'peso', FILTER_VALIDATE_FLOAT);
$fecha_registro = filter_input(INPUT_POST, 'fecha_registro', FILTER_SANITIZE_STRING); // Usamos SANITIZE para la fecha

// 10. Validaciones
if ($altura === false || $altura <= 0) {
    header('Location: dashboard.php?error=Altura+inválida');
    exit();
}
if ($peso === false || $peso <= 0) {
    header('Location: dashboard.php?error=Peso+inválido');
    exit();
}

// Validación de fecha (formato YYYY-MM-DD)
$date_format = 'Y-m-d';
$d = DateTime::createFromFormat($date_format, $fecha_registro);
if (!$fecha_registro || $d === false || $d->format($date_format) !== $fecha_registro) {
     header('Location: dashboard.php?error=Fecha+inválida');
     exit();
}

// 11. Lógica de negocio (Calcular IMC)
// El cálculo se moverá a la clase Metrics (ya lo hace addHealthData)
    
// 12. MODIFICACIÓN (BUG): Instanciar nuestra clase de lógica
// Esta es la línea que faltaba.
$metrics = new Metrics($pdo);


// 13. Llamar a la lógica
// Esta línea (la 68 en tu error) ahora funcionará
$result = $metrics->addHealthData($user_id, $peso, $altura, $fecha_registro); 

// 14. Comprobar el resultado y redirigir
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