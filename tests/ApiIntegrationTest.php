<?php

use PHPUnit\Framework\TestCase;

class ApiIntegrationTest extends TestCase
{
    private static $pdo;

    /**
     * Conectarse a la BD real ANTES de todas las pruebas de esta clase.
     */
    public static function setUpBeforeClass(): void
    {
        // Incluimos la conexión real para obtener $pdo
        include __DIR__ . '/../database_connection.php';
        self::$pdo = $pdo;
    }

    /**
     * Limpiar la BD DESPUÉS de cada prueba.
     */
    protected function tearDown(): void
    {
        if (self::$pdo) {
            // CORRECCIÓN: Limpiar la tabla 'usuarios'
            // (Las 'metricas' se borran por "ON DELETE CASCADE")
            self::$pdo->exec("DELETE FROM usuarios WHERE id = 9999");
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
        
        // CORRECCIÓN: Insertar un usuario de prueba en 'usuarios'
        $stmt = self::$pdo->prepare(
            "INSERT INTO usuarios (id, nombre, apellidos, email, password) 
             VALUES (?, 'api_user', 'lastname', 'api@test.com', 'hash')"
        );
        $stmt->execute([$testUserId]);

        // CORRECCIÓN: Insertar datos de prueba en 'metricas'
        $fecha = date('Y-m-d');
        $stmt = self::$pdo->prepare(
            "INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) 
             VALUES (?, 80.5, 1.8, 24.8, ?)"
        );
        $stmt->execute([$testUserId, $fecha]);

        
        // 2. Act (Simular la solicitud)
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        // Simular el login
        session_start();
        $_SESSION['user_id'] = $testUserId;

        // Capturar la salida (el JSON) del script
        ob_start();
        
        // Ejecutar el script real (el 'get_data.php' refactorizado)
        include __DIR__ . '/../get_data.php'; 
        
        $output = ob_get_clean();

        
        // 3. Assert (Verificar la respuesta)
        
        // Verificar que la salida es JSON válido
        $this->assertJson($output, "La salida de get_data.php no fue un JSON válido.");

        // Decodificar el JSON
        $response = json_decode($output, true);

        // Verificar que la API devolvió éxito
        $this->assertTrue(
            $response['success'], 
            "La respuesta JSON no indicó 'success' => true."
        );
        
        // Verificar que los datos están presentes y son correctos
        $this->assertCount(
            1, 
            $response['data'], 
            "La respuesta JSON no devolvió 1 registro de datos."
        );
        
        $this->assertEquals(
            80.5, 
            $response['data'][0]['peso'], 
            "El peso en la respuesta JSON no coincide."
        );
    }
}