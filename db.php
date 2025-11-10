<?php
/**
 * Este archivo ahora SOLO carga la conexión a la base de datos ($pdo).
 * La configuración de sesión (session_config.php) debe cargarse
 * por separado en cada script ANTES de session_start().
 */

// Cargar la conexión de la base de datos
require_once __DIR__ . '/database_connection.php';
?>