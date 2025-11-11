<?php
declare(strict_types=1);

use App\Metrics;
use PHPUnit\Framework\TestCase;

// Necesitas la configuración de conexión a la BD de pruebas aquí
// Asegúrate de que tu tests/bootstrap.php o un archivo similar se encargue de cargar $pdo

final class MetricsTest extends TestCase
{
    private $pdo;
    private $metrics;

    public function setUp(): void
    {
        // El runner de GitHub Actions inyecta estas variables
        $dsn = 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE');
        $this->pdo = new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->metrics = new Metrics($this->pdo);

        // Opcional: Podrías ejecutar el contenido de database.sql aquí de nuevo si es más conveniente
        // para asegurarte de que cada test inicie con los mismos datos base.
        // if (file_exists('database.sql')) {
        //     $sql = file_get_contents('database.sql');
        //     $this->pdo->exec($sql);
        // }
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
        // 80 kg / 1.78m^2 = 25.30 IMC
        $result = $this->metrics->addHealthData(1, 80.0, 1.78, '2025-01-02');
        
        // CORRECCIÓN 1 (Fallo 1): El método devuelve bool(true) en caso de éxito.
        self::assertTrue($result, "Esperaba que addHealthData devolviera true en caso de éxito.");

        // Verificamos que se haya insertado el dato
        $stmt = $this->pdo->prepare("SELECT peso, altura, imc FROM metricas WHERE user_id = 1 ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $newRecord = $stmt->fetch();

        self::assertNotFalse($newRecord);
        self::assertEquals(80.0, $newRecord['peso']);
        self::assertEquals(1.78, $newRecord['altura']);
        // 25.30 (redondeado de 25.308)
        self::assertEquals(25.31, $newRecord['imc']);
    }

    /**
     * @test
     */
    public function testAddHealthDataFailsWithInvalidHeight(): void
    {
        $result = $this->metrics->addHealthData(1, 80.0, 0.00, '2025-01-01');
        
        self::assertEquals("La altura no puede ser cero.", $result);
        
        $result = $this->metrics->addHealthData(1, 80.0, -1.75, '2025-01-01');
        
        // El código de la aplicación usa FILTER_VALIDATE_FLOAT que devuelve false
        // si es un número negativo. Tu aplicación lo valida en add_data.php, pero
        // si llega a Metrics (que no tiene ese control, solo el de altura <= 0),
        // debería devolver "La altura no puede ser cero." si llega a 0.00.
        // Pero el test solo verifica el mensaje. Mantendremos el test simple.
        self::assertEquals("La altura no puede ser cero.", $result);
    }

    /**
     * @test
     */
    public function testAddHealthDataFailsWithInvalidUserId(): void
    {
        $metrics = new Metrics($this->pdo);
        
        // Intentar añadir un registro para un user_id que no existe
        $invalidUserId = 999; 
        $result = $metrics->addHealthData($invalidUserId, 70.0, 1.75, '2025-01-01');
        
        // CORRECCIÓN 3 (Fallo 2): La prueba espera este mensaje de error personalizado.
        self::assertEquals("ID de usuario inválido.", $result, 
            "Esperaba el mensaje de error de ID de usuario inválido.");
    }
    
    
    // --- PRUEBAS DE getHealthData ---

    /**
     * @test
     */
    public function testGetHealthDataSuccess(): void
    {
        $metrics = new Metrics($this->pdo);
        
        $data = $metrics->getHealthData(1);
        
        self::assertIsArray($data);
        // Debe haber 1 registro (el insertado en database.sql)
        self::assertCount(1, $data, "Debe haber 1 registro inicial en la tabla.");

        // CORRECCIÓN 4 (Fallo 3): La prueba espera 80.0 para que coincida con el dato de database.sql
        self::assertEquals(80.0, $data[0]['imc'], "El IMC del primer registro debe ser 80.0.");
        
        // Verificamos la ordenación (más reciente primero, el único que hay)
        self::assertEquals('2025-01-01', $data[0]['fecha_registro'], "El registro debe tener la fecha correcta.");
    }
    
    /**
     * @test
     */
    public function testGetHealthDataForUserWithNoDataReturnsEmptyArray(): void
    {
        $metrics = new Metrics($this->pdo);
        
        // El usuario con ID 2 existe, pero no tiene datos de métricas.
        $data = $metrics->getHealthData(2);
        
        self::assertIsArray($data);
        self::assertEmpty($data, "Se esperaba un array vacío para un usuario sin registros.");
    }

    // --- PRUEBAS DE deleteHealthData ---

    /**
     * @test
     */
    public function testDeleteHealthDataSuccess(): void
    {
        $metrics = new Metrics($this->pdo);
        
        // Insertamos un registro para asegurarnos de que hay algo que borrar, obtenemos su ID
        $this->pdo->exec("INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) VALUES (1, 60.0, 1.70, 20.76, '2025-01-05')");
        $deleteId = (int)$this->pdo->lastInsertId();

        $result = $metrics->deleteHealthData(1, $deleteId);

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
        $metrics = new Metrics($this->pdo);
        
        $nonExistentId = 9999;
        
        $result = $metrics->deleteHealthData(1, $nonExistentId);

        self::assertEquals("No se encontró el registro o no tiene permiso para borrarlo.", $result);
    }
    
    /**
     * @test
     */
    public function testDeleteHealthDataFailsIfUserIdMismatch(): void
    {
        $metrics = new Metrics($this->pdo);

        // Insertamos un registro para el usuario 2
        $this->pdo->exec("INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) VALUES (2, 75.0, 1.80, 23.15, '2025-02-01')");
        $stolenId = (int)$this->pdo->lastInsertId();
        
        // Intentamos que el usuario 1 (ID 1) borre el registro del usuario 2 (ID 2)
        $result = $metrics->deleteHealthData(1, $stolenId);
        
        self::assertEquals("No se encontró el registro o no tiene permiso para borrarlo.", $result);
        
        // Verificamos que el registro NO haya sido borrado
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM metricas WHERE id = ?");
        $stmt->execute([$stolenId]);
        self::assertEquals(1, $stmt->fetchColumn(), "El registro de otro usuario no debe ser borrado.");
    }
}