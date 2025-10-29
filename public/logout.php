<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Session;

Session::init();

// Validar token CSRF para prevenir logout forzado por GET
if (!Session::validateCsrfToken($_GET['token'] ?? '')) {
    header('Location: dashboard.php?error=Error de seguridad.');
    exit;
}

Session::destroy();
header('Location: index.php?success=Has cerrado sesión.');
exit;