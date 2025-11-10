<?php

use PHPUnit\Framework\TestCase;
use App\Metrics; // Usamos la clase que creamos en src/

class MetricsTest extends TestCase
{
    private $pdo;
    private $metrics;

    /**
     * setUp() se ejecuta ANTES de cada método de prueba.
     */
    protected function setUp(): void
    {
        // 1. Crear una conexión PDO a una base de datos SQLite en memoria
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 2. Activar las claves foráneas en SQLite
        $this->pdo->exec('PRAGMA foreign_keys = ON;');

        // 3. CORRECCIÓN: Crear la tabla 'usuarios' (requerida por la FK)
        $this->pdo->exec("
            CREATE TABLE usuarios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL,
                apellidos TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL
            )
        ");

        // 4. CORRECCIÓN: Crear la tabla 'metricas' (la que vamos a probar)
        // (Tu BD usa DECIMAL, pero SQLite usa REAL para números flotantes)
        // (Tu BD usa DATE, pero SQLite usa TEXT para fechas)
        $this->pdo->exec("
            CREATE TABLE metricas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                peso REAL NOT NULL,
                altura REAL NOT NULL,
                imc REAL NOT NULL,
                fecha_registro TEXT NOT NULL,
                FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
            )
        ");

        // 5. Instanciar nuestra clase Metrics
        $this->metrics = new Metrics($this->pdo);

        // 6. (Helper) Insertar usuarios de prueba
        $this->pdo->exec("INSERT INTO usuarios (id, nombre, apellidos, email, password) VALUES (1, 'user1', 'lastname1', 'user1@test.com', 'hash')");
        $this->pdo->exec("INSERT INTO usuarios (id, nombre, apellidos, email, password) VALUES (2, 'user2', 'lastname2', 'user2@test.com', 'hash')");
    }

    /**
     * tearDown() se ejecuta DESPUÉS de cada método de prueba.
     */
    protected function tearDown(): void
    {
        $this->pdo = null;
        $this->metrics = null;
    }

    // --- PRUEBAS DE ADD (CORREGIDAS: 4) ---

    /**
     * @covers App\Metrics::addHealthData
     */
    public function testAddHealthDataSuccess()
    {
        // 1. Arrange
        $userId = 1;
        $peso = 80.5;
        $altura = 1.80;
        $imc = 24.8;
        $fecha_esperada = date('Y-m-d');

        // 2. Act
        $result = $this->metrics->addHealthData($userId, $peso, $altura, $imc);
        
        // 3. Assert
        $this->assertIsInt($result);

        $stmt = $this->pdo->prepare("SELECT * FROM metricas WHERE id = ?");
        $stmt->execute([$result]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($data);
        $this->assertEquals($peso, $data['peso']);
        $this->assertEquals($fecha_esperada, $data['fecha_registro']);
    }

    /**
     * @covers App\Metrics::addHealthData
     */
    public function testAddHealthDataFailsWithNegativeValues()
    {
        $result = $this->metrics->addHealthData(1, -80.0, 1.80, 24.8);
        $this->assertEquals("Los valores de peso, altura e IMC deben ser positivos.", $result);
    }

    /**
     * @covers App\Metrics::addHealthData
     */
    public function testAddHealthDataFailsWithInvalidUserId()
    {
        $result = $this->metrics->addHealthData(0, 80.0, 1.80, 24.8);
        $this->assertEquals("ID de usuario inválido.", $result);
    }

    /**
     * @covers App\Metrics::addHealthData
     */
    public function testAddHealthDataFailsForeignKeyConstraint()
    {
        $result = $this->metrics->addHealthData(99, 80.0, 1.80, 24.8);
        $this->assertStringContainsString("Error al guardar los datos", $result);
    }

    // --- PRUEBAS DE GET (CORREGIDAS: 3) ---

    /**
     * @covers App\Metrics::getHealthData
     */
    public function testGetHealthDataSuccess()
    {
        // 1. Arrange
        $this->metrics->addHealthData(1, 80, 1.8, 24.7);
        $this->metrics->addHealthData(1, 81, 1.8, 25.0);

        // 2. Act
        $result = $this->metrics->getHealthData(1);

        // 3. Assert
        $this->assertIsArray($result);
        $this->assertCount(2, $result, "Debería haber 2 registros para el usuario 1.");
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertEquals(80, $result[0]['peso']);
        $this->assertEquals(81, $result[1]['peso']);
    }

    /**
     * @covers App\Metrics::getHealthData
     */
    public function testGetHealthDataReturnsEmptyArrayForUserWithNoData()
    {
        $result = $this->metrics->getHealthData(1);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @covers App\Metrics::getHealthData
     */
    public function testGetHealthDataIsIsolated()
    {
        // 1. Arrange
        $this->metrics->addHealthData(1, 80, 1.8, 24.7); // User 1
        $this->metrics->addHealthData(2, 95, 1.9, 26.4); // User 2

        // 2. Act
        $result = $this->metrics->getHealthData(1);

        // 3. Assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(80, $result[0]['peso']);
    }

    // --- PRUEBAS DE DELETE (CORREGIDAS: 3) ---

    /**
     * @covers App\Metrics::deleteHealthData
     */
    public function testDeleteHealthDataSuccess()
    {
        // 1. Arrange
        $dataId = $this->metrics->addHealthData(1, 80, 1.8, 24.7);

        // 2. Act
        $result = $this->metrics->deleteHealthData(1, $dataId);

        // 3. Assert
        $this->assertTrue($result);

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM metricas WHERE id = ?");
        $stmt->execute([$dataId]);
        $this->assertEquals(0, $stmt->fetchColumn());
    }

    /**
     * @covers App\Metrics::deleteHealthData
     */
    public function testDeleteHealthDataFailsWhenDeletingOthersData()
    {
        // 1. Arrange
        $dataId = $this->metrics->addHealthData(1, 80, 1.8, 24.7);

        // 2. Act: User 2 intenta borrar el dato de User 1
        $result = $this->metrics->deleteHealthData(2, $dataId);

        // 3. Assert
        $this->assertEquals(
            "No se encontró el registro o no tiene permiso para borrarlo.", 
            $result
        );
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM metricas WHERE id = ?");
        $stmt->execute([$dataId]);
        $this->assertEquals(1, $stmt->fetchColumn());
    }

    /**
     * @covers App\Metrics::deleteHealthData
     */
    public function testDeleteHealthDataFailsWithNonExistentId()
    {
        $result = $this->metrics->deleteHealthData(1, 999);
        $this->assertEquals(
            "No se encontró el registro o no tiene permiso para borrarlo.", 
            $result
        );
    }
}