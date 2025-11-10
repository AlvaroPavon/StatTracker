<?php
/**
 * Refinamiento de Seguridad: Configuración de Sesiones Seguras
 * Estas directivas deben ejecutarse ANTES de session_start()
 */

// 1. Previene que JavaScript acceda a la cookie de sesión (mitiga XSS)
ini_set('session.cookie_httponly', 1);

// 2. Asegura que el ID de sesión solo se propague mediante cookies
ini_set('session.use_only_cookies', 1);

// 3. (SOLO PARA PRODUCCIÓN CON SSL/HTTPS)
// Comenta la siguiente línea si estás probando en local (http://localhost)
// Descoméntala cuando tu sitio use https://
// ini_set('session.cookie_secure', 1);
?>