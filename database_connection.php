<?php
/**
 * database_connection.php - Conexión segura a la base de datos
 * @package StatTracker
 */

// Evitar acceso directo
if (basename($_SERVER['SCRIPT_FILENAME']) === 'database_connection.php') {
    http_response_code(403);
    exit('Acceso denegado');
}

// Configuración de la base de datos desde variables de entorno
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_DATABASE') ?: 'proyecto_imc';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

// DSN con charset seguro
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

// Opciones de PDO SEGURAS
$options = [
    // Modo de errores: excepciones (nunca mostrar errores SQL al usuario)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    
    // Fetch mode por defecto
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    
    // CRÍTICO: Desactivar emulación de prepared statements
    // Esto fuerza prepared statements REALES en el servidor MySQL
    PDO::ATTR_EMULATE_PREPARES   => false,
    
    // Convertir valores numéricos a tipos PHP nativos
    PDO::ATTR_STRINGIFY_FETCHES  => false,
    
    // Timeout de conexión
    PDO::ATTR_TIMEOUT            => 5,
];

try {
    // Crear conexión
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Configuraciones adicionales de seguridad en MySQL
    $pdo->exec("SET SESSION sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
    
    // Migración automática segura
    $checkColumn = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'apellidos'");
    if ($checkColumn->rowCount() === 0) {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN apellidos VARCHAR(100) NOT NULL DEFAULT '' AFTER nombre");
    }
    
} catch (\PDOException $e) {
    // NUNCA mostrar detalles del error de BD al usuario
    error_log("DB Connection Error: " . $e->getMessage());
    
    // Establecer $pdo como null para indicar que la BD no está disponible
    $pdo = null;
    
    // No lanzar excepción - dejar que las páginas manejen $pdo === null
    // Las páginas que requieren BD verificarán si $pdo es null
}

// Limpiar variables sensibles de memoria
unset($password);
?>
