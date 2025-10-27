<?php
// Configuración de la base de datos
$host = 'localhost';      // Servidor de base de datos (XAMPP usa 'localhost')
$dbname = 'proyecto_imc'; // El nombre de la base de datos
$username = 'root';       // Usuario de MySQL (XAMPP usa 'root')
$password = '';           // Contraseña de MySQL (XAMPP la deja vacía)

/**
 * Data Source Name (DSN)
 * Define el host, la base de datos y el juego de caracteres.
 * Usar utf8mb4 es una buena práctica para soportar todos los caracteres (incluyendo emojis).
 */
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

/**
 * Opciones de PDO
 */
$options = [
    // 1. Refinamiento: Modo de Error
    // Lanza excepciones en lugar de Warnings/Notices. Esto evita que el script continúe si la BD falla.
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    
    // 2. Refinamiento: Modo de Fetch
    // Devuelve los resultados como arrays asociativos (ej: $fila['nombre'])
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    
    // 3. Refinamiento: Emulación de 'Prepares'
    // Se desactiva para usar las sentencias preparadas nativas de MySQL, que son más seguras.
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    /**
     * Crear la instancia de PDO (la conexión)
     * Esta variable $pdo se usará en todos los demás archivos (login, register, etc.)
     */
    $pdo = new PDO($dsn, $username, $password, $options);
    
} catch (\PDOException $e) {
    /**
     * Si la conexión falla (ej. MySQL no está encendido o la contraseña es incorrecta),
     * el script se detiene aquí y muestra un mensaje de error claro.
     */
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>