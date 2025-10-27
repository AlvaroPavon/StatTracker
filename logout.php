<?php
// 1. Iniciar la sesión ANTES de cualquier salida
// (Es necesario para acceder a la sesión actual y destruirla)
session_start();

// 2. Refinamiento: Borrar todas las variables de la sesión
// Esto vacía el array $_SESSION
$_SESSION = array();

// 3. Refinamiento: Destruir la cookie de sesión (Opcional pero recomendado)
// Si la sesión usa cookies (lo estándar), eliminamos la cookie del navegador
// enviando una con fecha de caducidad en el pasado.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finalmente, destruir la sesión en el servidor
session_destroy();

// 5. Redirigir al formulario de login (index.php)
header('Location: index.php');
exit; // Detener el script
?>