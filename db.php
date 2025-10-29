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


// Configuración de la base de datos
$host = 'localhost';      // Servidor de base de datos (XAMPP usa 'localhost')
$dbname = 'proyecto_imc'; // El nombre de la base de datos
$username = 'root';       // Usuario de MySQL (XAMPP usa 'root')
$password = '';           // Contraseña de MySQL (XAMPP la deja vacía)

/**
 * Data Source Name (DSN)
 */
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

/**
 * Opciones de PDO
 */
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    /**
     * Crear la instancia de PDO (la conexión)
     */
    $pdo = new PDO($dsn, $username, $password, $options);
    
} catch (\PDOException $e) {
    /**
     * Si la conexión falla, el script se detiene.
     */
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>