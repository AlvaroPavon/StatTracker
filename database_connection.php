<?php
// MODIFICADO: Configuración de la base de datos
// Priorizamos variables de entorno (usadas por GitHub Actions) y si no existen, usamos valores locales por defecto
$host = getenv('DB_HOST') ?: 'localhost';          // Servidor de base de datos
$dbname = getenv('DB_DATABASE') ?: 'proyecto_imc'; // El nombre de la base de datos
$username = getenv('DB_USERNAME') ?: 'root';       // Usuario de MySQL
$password = getenv('DB_PASSWORD') ?: '';           // Contraseña de MySQL

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
    // Loguear el error para depuración
    error_log("Fallo de conexión a la BD: " . $e->getMessage());
    throw new \PDOException("Error de conexión a la base de datos.", (int)$e->getCode());
}
?>