<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ApiIntegrationTest extends TestCase
{
    private $pdo;
    private $testUser;
    private $baseUrl = 'http://localhost/'; // Asume que los endpoints están en la raíz

    protected function setUp(): void
    {
        // Configuración de la base de datos de prueba (debe coincidir con phpunit.xml y CI)
        $this->pdo = new PDO(
            'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . getenv('MYSQL_PORT'),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD')
        );

        // Crear un usuario de prueba y obtener su API Key
        $this->testUser = $this->createTestUser();
    }

    protected function tearDown(): void
    {
        // Limpiar la base de datos después de cada prueba
        $this->pdo->exec("DELETE FROM users WHERE email = 'test_api@example.com'");
        $this->pdo->exec("DELETE FROM health_data WHERE user_id = {$this->testUser['id']}");
        $this->pdo = null;
        $this->testUser = null;
    }

    private function createTestUser(): array
    {
        $email = 'test_api@example.com';
        $password = 'secure_password';
        $nombre = 'TestApi';
        $apellido = 'UserApi';
        $apiKey = bin2hex(random_bytes(16));
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insertar usuario
        $stmt = $this->pdo->prepare("INSERT INTO users (nombre, apellido, email, password_hash, api_key) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $apellido, $email, $passwordHash, $apiKey]);
        
        $id = $this->pdo->lastInsertId();

        // Insertar datos de salud para este usuario (fixture)
        $stmt = $this->pdo->prepare("INSERT INTO health_data (user_id, weight, height, date) VALUES (?, ?, ?, ?)");
        // Los datos para la prueba de "get"
        $stmt->execute([$id, 70.0, 1.75, '2023-01-01']); 
        
        return ['id' => $id, 'email' => $email, 'api_key' => $apiKey];
    }

    /** @test */
    public function testGetDataApiSuccess(): void
    {
        $response = $this->makeApiRequest('get_data.php', ['metric' => 'weight'], $this->testUser['api_key']);
        $this->assertEquals(200, $response['status_code']);
        $responseData = json_decode($response['body'], true);

        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
        
        // Verifica que la estructura del usuario se devuelva correctamente
        // CORRECCIÓN: Cambiado 'apellidos' a 'apellido' para coincidir con database.sql
        $this->assertArrayHasKey('nombre', $responseData['data'][0]);
        $this->assertArrayHasKey('apellido', $responseData['data'][0]);
        $this->assertArrayHasKey('weight', $responseData['data'][0]);
    }

    // ... otros métodos de prueba
    
    // Método auxiliar para simular una solicitud a la API
    private function makeApiRequest(string $endpoint, array $postData = [], string $apiKey = ''): array
    {
        // Simular la solicitud sin curl/http, solo con los headers
        // Esto asume que tu endpoint PHP usa $_SERVER['HTTP_X_API_KEY'] o similar
        
        $path = __DIR__ . '/../' . $endpoint;
        
        // Simular headers y POST data
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_API_KEY'] = $apiKey;
        $_POST = $postData;

        // Capturar la salida del script
        ob_start();
        require $path;
        $output = ob_get_clean();

        // Si el script maneja su propio código de respuesta HTTP, 
        // la manera más fácil de saber el "status" es buscarlo en el JSON
        $responseData = json_decode($output, true);
        
        // Si hay una respuesta JSON, asumimos 200 a menos que el JSON diga lo contrario
        $statusCode = ($responseData !== null) ? 200 : 500;
        
        return [
            'status_code' => $statusCode,
            'body' => $output
        ];
    }
}