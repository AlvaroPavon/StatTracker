<?php
/**
 * Configuración JWT para la API
 */

// Secret key para JWT - CAMBIAR EN PRODUCCIÓN
define('JWT_SECRET', 'StatTracker_API_Secret_Key_2026_ChangeInProduction');

// Tiempo de expiración del token (en segundos) - 1 hora
define('JWT_EXPIRY', 3600);

// Algoritmo de encriptación
define('JWT_ALGO', 'HS256');
