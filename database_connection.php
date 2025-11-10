<?php
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
     * Esto será capturado por nuestro Test.
     */
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>