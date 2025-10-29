<?php
// 1. REFINAMIENTO DE ARQUITECTURA: Incluir 'db.php' ANTES de session_start()
// (Aunque db.php no se usa aquí, sí configura los parámetros de la sesión)
require 'db.php';

// 2. REFINAMIENTO (CSRF): Iniciar la sesión para acceder al token
session_start();

// 3. REFINAMIENTO (CSRF): Validar el token
// Comprobamos el token enviado por la URL (GET) contra el de la sesión
if (!isset($_GET['token']) || !hash_equals($_SESSION['csrf_token'], $_GET['token'])) {
    // Si el token no coincide, no cerramos la sesión.
    // Simplemente redirigimos al dashboard.
    header('Location: dashboard.php?error=Error de seguridad.');
    exit;
}

// --- Si el token es válido, continuamos cerrando la sesión ---

// 4. Refinamiento: Borrar todas las variables de la sesión
$_SESSION = array();

// 5. Refinamiento: Destruir la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 6. Finalmente, destruir la sesión en el servidor
session_destroy();

// 7. Redirigir al formulario de login (index.php)
header('Location: index.php');
exit; // Detener el script
?>