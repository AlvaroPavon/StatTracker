<?php
declare(strict_types=1);

use App\Metrics;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;

// Necesitas la configuración de conexión a la BD de pruebas aquí
// Asegúrate de que tu tests/bootstrap.php o un archivo similar se encargue de cargar $pdo

final class MetricsTest extends TestCase
{
    private $pdo;
    private $metrics;

    public function setUp(): void
    {
        // El runner de GitHub Actions inyecta estas variables
        $dsn = 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . getenv('MYSQL_PORT');
        
        try {
            // Uso de \PDO para evitar el conflicto con el namespace App
            $this->pdo = new \PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            $this->fail("La conexión a la base de datos no se pudo establecer en setUp(): " . $e->getMessage());
        }

        // Recrear las tablas para aislamiento de tests y evitar 'Table not found'
        $this->pdo->exec("DROP TABLE IF EXISTS metricas, usuarios");
        
        // Creación de la tabla 'usuarios' (estructura necesaria para las Foreign Keys)
        $this->pdo->exec("
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
        $this->pdo->exec("
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

        $this->metrics = new Metrics($this->pdo);

        // Insertar fixture: usuario 1 y su registro inicial (IMC 25.95)
        $this->pdo->exec("INSERT INTO usuarios (id, nombre, apellidos, email, password) VALUES (1, 'Test', 'User', 'test@example.com', 'hash')");
        $this->pdo->exec("INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) VALUES (1, 75.00, 1.70, 25.95, '2025-01-01')");
        
        // Insertar fixture: usuario 2 (sin metricas)
        $this->pdo->exec("INSERT INTO usuarios (id, nombre, apellidos, email, password) VALUES (2, 'NoData', 'User', 'nodata@example.com', 'hash')");
    }
    
    public function tearDown(): void
    {
        $this->pdo = null;
        $this->metrics = null;
    }


    // --- PRUEBAS DE addHealthData ---
    
    /**
     * @test
     */
    public function testAddHealthDataSuccess(): void
    {
        // Cálculo: 80 kg / 1.78m^2 = 25.2536...
        $weight = 80.0;
        $height = 1.78;
        $date = '2025-01-02';

        $result = $this->metrics->addHealthData(1, $weight, $height, $date);
        
        // CORRECCIÓN 1 (Fallo 1 anterior): El método devuelve bool(true) en caso de éxito.
        self::assertTrue($result, "Esperaba que addHealthData devolviera true en caso de éxito.");

        // Verificamos que se haya insertado el dato
        $stmt = $this->pdo->prepare("SELECT peso, altura, imc FROM metricas WHERE user_id = 1 AND fecha_registro = '2025-01-02'");
        $stmt->execute();
        $newRecord = $stmt->fetch();

        self::assertNotFalse($newRecord);
        self::assertEquals($weight, (float)$newRecord['peso']);
        self::assertEquals($height, (float)$newRecord['altura']);
        
        // CORRECCIÓN F2: El valor correcto para 80kg y 1.78m, redondeado a 2 decimales, es 25.25.
        // El test ahora espera el valor matemáticamente correcto.
        self::assertEquals(25.25, (float)$newRecord['imc'], "El IMC no coincide (Error de cálculo/redondeo en el test).");
    }

    /**
     * @test
     */
    public function testAddHealthDataFailsWithInvalidHeight(): void
    {
        $result = $this->metrics->addHealthData(1, 80.0, 0.00, '2025-01-01');
        
        self::assertEquals("La altura no puede ser cero.", $result);
        
        $result = $this->metrics->addHealthData(1, 80.0, -1.75, '2025-01-01');
        
        self::assertEquals("La altura no puede ser cero.", $result);
    }

    /**
     * @test
     */
    public function testAddHealthDataFailsWithInvalidUserId(): void
    {
        // Intentar añadir un registro para un user_id que no existe
        $invalidUserId = 999; 
        $result = $this->metrics->addHealthData($invalidUserId, 70.0, 1.75, '2025-01-01');
        
        self::assertEquals("ID de usuario inválido.", $result, 
            "Esperaba el mensaje de error de ID de usuario inválido.");
    }
    
    
    // --- PRUEBAS DE getHealthData ---

    /**
     * @test
     */
    public function testGetHealthDataSuccess(): void
    {
        // Añadimos un SEGUNDO registro de métricas. Ahora hay 2.
        $this->metrics->addHealthData(1, 82.0, 1.80, '2025-01-03');
        
        $data = $this->metrics->getHealthData(1);
        
        self::assertIsArray($data);
        // Ahora hay 2 registros (1 del setup + 1 de este test)
        self::assertCount(2, $data, "Debe haber 2 registros en la tabla.");

        // El registro más reciente debe ser el primero (el que acabamos de añadir)
        self::assertEquals(82.0, (float)$data[0]['peso'], "El peso del registro más reciente no coincide.");
        // El IMC del segundo registro (82kg / 1.80^2) es 25.3086..., que redondeado es 25.31.
        self::assertEquals(25.31, (float)$data[0]['imc'], "El IMC del registro más reciente no coincide.");
        
        // El registro inicial debe ser el segundo
        self::assertEquals(75.0, (float)$data[1]['peso'], "El peso del registro inicial no coincide.");
    }
    
    /**
     * @test
     */
    public function testGetHealthDataForUserWithNoDataReturnsEmptyArray(): void
    {
        // El usuario con ID 2 fue insertado en setUp y no tiene datos de métricas.
        $data = $this->metrics->getHealthData(2);
        
        self::assertIsArray($data);
        self::assertEmpty($data, "Se esperaba un array vacío para un usuario sin registros.");
    }

    // --- PRUEBAS DE deleteHealthData ---

    /**
     * @test
     */
    public function testDeleteHealthDataSuccess(): void
    {
        // Insertamos un registro para asegurarnos de que hay algo que borrar, obtenemos su ID
        // Usamos un ID diferente al inicial (id=1)
        $this->pdo->exec("INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) VALUES (1, 60.0, 1.70, 20.76, '2025-01-05')");
        $deleteId = (int)$this->pdo->lastInsertId();

        $result = $this->metrics->deleteHealthData(1, $deleteId);

        self::assertTrue($result, "Esperaba que deleteHealthData devolviera true tras el borrado exitoso.");

        // Verificamos que el registro ya no exista
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM metricas WHERE id = ?");
        $stmt->execute([$deleteId]);
        self::assertEquals(0, $stmt->fetchColumn(), "El registro no debe existir después del borrado.");
    }

    /**
     * @test
     */
    public function testDeleteHealthDataFailsIfRecordDoesNotExist(): void
    {
        $nonExistentId = 9999;
        
        $result = $this->metrics->deleteHealthData(1, $nonExistentId);

        self::assertEquals("No se encontró el registro o no tiene permiso para borrarlo.", $result);
    }
    
    /**
     * @test
     */
    public function testDeleteHealthDataFailsIfUserIdMismatch(): void
    {
        // Insertamos un registro para el usuario 2 (ID 2)
        $this->pdo->exec("INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) VALUES (2, 75.0, 1.80, 23.15, '2025-02-01')");
        $stolenId = (int)$this->pdo->lastInsertId();
        
        // Intentamos que el usuario 1 (ID 1) borre el registro del usuario 2 (ID 2)
        $result = $this->metrics->deleteHealthData(1, $stolenId);
        
        self::assertEquals("No se encontró el registro o no tiene permiso para borrarlo.", $result);
        
        // Verificamos que el registro NO haya sido borrado
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM metricas WHERE id = ?");
        $stmt->execute([$stolenId]);
        self::assertEquals(1, $stmt->fetchColumn(), "El registro de otro usuario no debe ser borrado.");
    }
}