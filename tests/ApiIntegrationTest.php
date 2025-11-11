<?php

use PHPUnit\Framework\TestCase;

class ApiIntegrationTest extends TestCase
{
    private static $pdo;
    private $originalCwd;

    /**
     * Conectarse a la BD y CREAR las tablas necesarias antes de cada prueba.
     */
    protected function setUp(): void
    {
        // Guardar el directorio de trabajo original
        $this->originalCwd = getcwd();

        // 1. Conexión a la BD de pruebas (Usamos las variables de entorno de phpunit.xml / CI)
        $dsn = 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . getenv('MYSQL_PORT');
        
        // Usar self::$pdo en lugar de self::$pdo para aislar la conexión.
        try {
            self::$pdo = new \PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            $this->fail("La conexión a la base de datos no se pudo establecer en setUp(): " . $e->getMessage());
        }

        // 2. CORRECCIÓN E1 (anterior): Recrear las tablas para aislamiento de tests y evitar 'Table not found'
        self::$pdo->exec("DROP TABLE IF EXISTS metricas, usuarios");
        
        // Creación de la tabla 'usuarios' (estructura necesaria para las Foreign Keys)
        self::$pdo->exec("
            CREATE TABLE usuarios (
                id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL,
                apellidos VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                foto_perfil VARCHAR(255) NULL
            )
        ");
        
        // Creación de la tabla 'metricas'
        self::$pdo->exec("
            CREATE TABLE metricas (
                id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT(11) UNSIGNED NOT NULL,
                peso DECIMAL(5, 2) NOT NULL,
                altura DECIMAL(3, 2) NOT NULL,
                imc DECIMAL(5, 2) NOT NULL,
                fecha_registro DATE NOT NULL,
                FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
            )
        ");
        
        // Limpiamos los datos de prueba del ID 9999 por si acaso la prueba falla a mitad
        self::$pdo->exec("DELETE FROM metricas WHERE user_id = 9999");
        self::$pdo->exec("DELETE FROM usuarios WHERE id = 9999");
    }

    /**
     * Limpiar la BD DESPUÉS de cada prueba.
     */
    protected function tearDown(): void
    {
        if (self::$pdo) {
            // Limpieza de datos específicos del test, ya que setUp recrea las tablas
            self::$pdo->exec("DELETE FROM metricas WHERE user_id = 9999");
            self::$pdo->exec("DELETE FROM usuarios WHERE id = 9999");
        }
        
        // Restaurar el directorio de trabajo original
        if ($this->originalCwd) {
            chdir($this->originalCwd);
        }
    }

    /**
     * @covers get_data.php
     *
     * @runInSeparateProcess
     * Esta anotación es CRUCIAL. Evita errores de "headers already sent".
     */
    public function testGetDataApiSuccess()
    {
        // 1. Arrange (Preparar la BD real)
        $testUserId = 9999;
        
        // Insertar un usuario de prueba (usando 'apellidos' como está en tu test)
        $stmt = self::$pdo->prepare(
            "INSERT INTO usuarios (id, nombre, apellidos, email, password) 
             VALUES (?, 'api_user', 'lastname', 'api@test.com', 'hash')"
        );
        $stmt->execute([$testUserId]);

        // Insertar datos de prueba
        $fecha = date('Y-m-d');
        $stmt = self::$pdo->prepare(
            "INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) 
             VALUES (?, 80.5, 1.8, 24.8, ?)"
        );
        $stmt->execute([$testUserId, $fecha]);

        
        // 2. Act (Simular la solicitud)
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        // Iniciar la sesión para el test
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // CORRECCIÓN F1: Simular el token CSRF en la sesión y en la solicitud 
        // para pasar el chequeo de session_config.php y evitar el error "Token CSRF no válido o ausente."
        $csrfToken = 'test_csrf_token_fix';
        $_SESSION['csrf_token'] = $csrfToken;
        // Inyectar el token en $_GET ya que el método es GET
        $_GET['csrf_token'] = $csrfToken; 
        
        $_SESSION['user_id'] = $testUserId;

        // Capturar la salida (el JSON) del script
        ob_start();
        
        // Inyectar la conexión PDO del test en el ámbito local (necesario si get_data.php la requiere)
        $pdo = self::$pdo;

        // Cambiamos al directorio raíz del proyecto.
        chdir(__DIR__ . '/..');

        // Desactivamos FORZOSAMENTE la visualización de errores.
        $oldErrorReporting = error_reporting();
        $oldDisplayErrors = ini_get('display_errors');
        $oldDisplayStartupErrors = ini_get('display_startup_errors');

        error_reporting(0); 
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');

        // Incluimos el script
        include 'get_data.php'; 
        
        // Restaurar la configuración original
        ini_set('display_startup_errors', $oldDisplayStartupErrors);
        ini_set('display_errors', $oldDisplayErrors);
        error_reporting($oldErrorReporting);
        
        $output = ob_get_clean();

        
        // 3. Assert (Verificar la respuesta)
        
        // Verificar que la salida es JSON válido
        $this->assertJson($output, "La salida de get_data.php no fue un JSON válido. Salida: " . $output);

        // Decodificar el JSON
        $response = json_decode($output, true);

        // Verificar que la API devolvió éxito
        $this->assertTrue(
            isset($response['success']) && $response['success'] === true, 
            "La respuesta JSON no indicó 'success' => true. Respuesta: " . $output
        );
        
        // Verificar que los datos están presentes y son correctos
        $this->assertCount(
            1, 
            isset($response['data']) ? $response['data'] : [], 
            "La respuesta JSON no devolvió 1 registro de datos."
        );
        
        $this->assertEquals(
            80.5,
            $response['data'][0]['peso'],
            "El peso en la respuesta JSON no coincide."
        );
    }
}