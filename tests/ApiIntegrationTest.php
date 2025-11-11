<?php

use PHPUnit\Framework\TestCase;

class ApiIntegrationTest extends TestCase
{
    private static $pdo;
    private $originalCwd;

    /**
     * Conectarse a la BD y LIMPIAR antes de CADA prueba.
     */
    protected function setUp(): void
    {
        // Guardar el directorio de trabajo original
        $this->originalCwd = getcwd();

        // Incluimos la conexión real para obtener $pdo
        if (self::$pdo === null) {
            include __DIR__ . '/../database_connection.php';
            
            if (!$pdo) {
                $this->fail("La conexión a la base de datos ($pdo) no se pudo establecer en setUp().");
            }
            
            self::$pdo = $pdo;
        }
        
        // Limpiamos CUALQUIER dato de prueba anterior ANTES de ejecutar el test.
        if (self::$pdo) {
            self::$pdo->exec("DELETE FROM usuarios WHERE id = 9999");
        }
    }

    /**
     * Limpiar la BD DESPUÉS de cada prueba.
     */
    protected function tearDown(): void
    {
        if (self::$pdo) {
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
        
        // Insertar un usuario de prueba
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
        $_SESSION['user_id'] = $testUserId;

        // Capturar la salida (el JSON) del script
        ob_start();
        
        // Inyectar la conexión PDO del test en el ámbito local
        $pdo = self::$pdo;

        // Cambiamos al directorio raíz del proyecto.
        chdir(__DIR__ . '/..');

        // ***** CORRECCIÓN CLAVE *****
        // Desactivamos FORZOSAMENTE la visualización de errores.
        // El script 'get_data.php' intenta reactivarlos con ini_set('display_errors', 1),
        // lo que causa que los warnings de 'session_config.php' se impriman
        // y fallen el test debido a 'beStrictAboutOutputDuringTests'.
        
        $oldErrorReporting = error_reporting();
        $oldDisplayErrors = ini_get('display_errors');
        $oldDisplayStartupErrors = ini_get('display_startup_errors');

        error_reporting(0); // Desactivar todos los reportes
        ini_set('display_errors', 0); // No mostrar errores
        ini_set('display_startup_errors', 0); // No mostrar errores de arranque

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
            "La respuesta JSON no indicó 'success' => true."
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