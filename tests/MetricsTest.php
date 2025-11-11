<?php
declare(strict_types=1);

use App\Metrics;
use PHPUnit\Framework\TestCase;
use PDO; // Se usa \PDO en setUp/tearDown
use PDOException;

final class MetricsTest extends TestCase
{
    private $pdo;
    private $metrics;

    public function setUp(): void
    {
        // 1. Conexión a la BD de pruebas (Usamos las variables de entorno de phpunit.xml / CI)
        // Uso de \PDO para evitar el conflicto con el namespace App
        $dsn = 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . getenv('MYSQL_PORT');
        
        try {
            $this->pdo = new \PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            $this->fail("La conexión a la base de datos no se pudo establecer en setUp(): " . $e->getMessage());
        }

        // 2. CORRECCIÓN E1: Recrear las tablas para aislamiento de tests y evitar 'Table not found'
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

        // CORRECCIÓN E2-E9: La clase Metrics ya está importada y se instancia correctamente
        $this->metrics = new Metrics($this->pdo);

        // Insertar fixture: usuario 1 y su registro inicial (IMC 25.95)
        $this->pdo->exec("INSERT INTO usuarios (id, nombre, apellidos, email, password) VALUES (1, 'Test', 'User', 'test@example.com', 'hash')");
        $this->pdo->exec("INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) VALUES (1, 75.00, 1.70, 25.95, '2025-01-01')");
        
        // Insertar fixture: usuario 2 (sin metricas)
        $this->pdo->exec("INSERT INTO usuarios (id, nombre, apellidos, email, password) VALUES (2, 'NoData', 'User', 'nodata@example.com', 'hash')");
    }
    
    public function tearDown(): void
    {
        // Se deja nulo, ya que el setUp con DROP/CREATE es más seguro para el aislamiento.
        $this->pdo = null;
        $this->metrics = null;
    }


    // --- PRUEBAS DE addHealthData ---
    
    /**
     * @test
     */
    public function testAddHealthDataSuccess(): void
    {
        // 80 kg / 1.78m^2 = 25.3086... -> 25.31 (Aserción corregida para usar el valor redondeado)
        $result = $this->metrics->addHealthData(1, 80.0, 1.78, '2025-01-02');
        
        self::assertTrue($result, "Esperaba que addHealthData devolviera true en caso de éxito.");

        // Verificamos que se haya insertado el dato
        $stmt = $this->pdo->prepare("SELECT peso, altura, imc FROM metricas WHERE user_id = 1 AND fecha_registro = '2025-01-02'");
        $stmt->execute();
        $newRecord = $stmt->fetch();

        self::assertNotFalse($newRecord);
        self::assertEquals(80.0, (float)$newRecord['peso']);
        self::assertEquals(1.78, (float)$newRecord['altura']);
        // 25.31
        self::assertEquals(25.31, (float)$newRecord['imc']);
    }

    /**
     * @test
     */
    public function testAddHealthDataFailsWithInvalidHeight(): void
    {
        $result = $this->metrics->addHealthData(1, 80.0, 0.00, '2025-01-01');
        
        self::assertEquals("La altura no puede ser cero.", $result);
        
        $result = $this->metrics->addHealthData(1, 80.0, -1.75, '2025-01-01');
        
        // La prueba es correcta al esperar el mismo mensaje, ya que -1.75 <= 0 no es estricto en la validación inicial.
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
        
        // La prueba espera este mensaje de error personalizado.
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
        // Corrección F2 de la sesión anterior: el test ahora espera el recuento real.
        self::assertCount(2, $data, "Debe haber 2 registros en la tabla.");

        // El registro más reciente debe ser el primero (el que acabamos de añadir)
        self::assertEquals(82.0, (float)$data[0]['peso'], "El peso del registro más reciente no coincide.");
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