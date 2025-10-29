<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Session;
use App\Metrics;
use App\Validator;
use PDOException;

Session::init();

// 1. Autenticación
if (!Session::has('user_id')) {
    header('Location: index.php');
    exit;
}

// 2. Verificar Método y Token
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: dashboard.php');
    exit;
}
if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: dashboard.php?error=Error de seguridad. Intente de nuevo.');
    exit;
}

// 3. Obtener Datos
$user_id = Session::get('user_id');
$peso = $_POST['peso'] ?? null;
$altura = $_POST['altura'] ?? null;
$fecha_registro = $_POST['fecha_registro'] ?? null;

// 4. Validar
$errors = [];
if (!Validator::isPositiveNumber($peso)) $errors[] = "El peso debe ser un valor positivo.";
if (!Validator::isPositiveNumber($altura)) $errors[] = "La altura debe ser un valor positivo.";
if (!Validator::isNotEmpty($fecha_registro) || !Validator::isDateNotInFuture($fecha_registro)) {
     $errors[] = "La fecha no es válida o es una fecha futura.";
}

if (!empty($errors)) {
    header('Location: dashboard.php?error=' . urlencode($errors[0]));
    exit;
}

// 5. Procesar
try {
    $pdo = Database::getInstance();
    $metrics = new Metrics($pdo);
    
    $metrics->addMetric(
        (int)$user_id,
        (float)$peso,
        (float)$altura,
        $fecha_registro
    );

    header('Location: dashboard.php?success=Registro añadido');
    exit;

} catch (\PDOException $e) {
    error_log('Error en add_data.php: ' . $e->getMessage());
    header('Location: dashboard.php?error=Error al guardar los datos. Inténtelo de nuevo.');
    exit;
}