<?php

use PHPUnit\Framework\TestCase;
use App\Auth; // Usamos la clase que creamos en src/

class AuthTest extends TestCase
{
    private $pdo;
    private $auth;

    /**
     * setUp() se ejecuta ANTES de cada método de prueba.
     */
    protected function setUp(): void
    {
        // 1. Crear una conexión PDO a una base de datos SQLite en memoria
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 2. CORRECCIÓN: Crear la tabla 'usuarios' (versión SQLite)
        $this->pdo->exec("
            CREATE TABLE usuarios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL,
                apellidos TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL
            )
        ");

        // 3. Instanciar nuestra clase Auth
        $this->auth = new Auth($this->pdo);
    }

    /**
     * tearDown() se ejecuta DESPUÉS de cada método de prueba.
     */
    protected function tearDown(): void
    {
        $this->pdo = null;
        $this->auth = null;
    }

    // --- PRUEBAS DE REGISTRO (CORREGIDAS) ---

    /**
     * @covers App\Auth::register
     * Prueba un registro exitoso.
     */
    public function testRegisterSuccess()
    {
        // 1. Arrange
        $nombre = 'Test';
        $apellidos = 'User';
        $email = 'test@example.com';
        $password = 'password123';

        // 2. Act: Ejecutamos el método a probar
        $result = $this->auth->register($nombre, $apellidos, $email, $password);

        // 3. Assert
        $this->assertIsInt($result, "El registro exitoso debe devolver un ID de usuario (entero).");
        $this->assertGreaterThan(0, $result, "El ID de usuario debe ser mayor que 0.");

        // Afirmación extra: verificamos que el usuario existe en la BD
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($user, "El usuario no se encontró en la base de datos.");
        $this->assertEquals($nombre, $user['nombre']);
        $this->assertEquals($apellidos, $user['apellidos']);
        $this->assertTrue(
            password_verify($password, $user['password']), 
            "La contraseña en la base de datos no coincide con la hasheada."
        );
    }

    /**
     * @covers App\Auth::register
     * Prueba que el registro falla si el email ya existe.
     */
    public function testRegisterFailsWithDuplicateEmail()
    {
        // 1. Arrange: Registrar un usuario primero
        $this->auth->register('First', 'User', 'duplicate@example.com', 'password123');

        // 2. Act: Intentar registrar otro usuario con el mismo email
        $result = $this->auth->register('Second', 'User', 'duplicate@example.com', 'password456');

        // 3. Assert
        $this->assertEquals(
            "El email ya está registrado.", 
            $result
        );
    }

    /**
     * @covers App\Auth::register
     * Prueba que el registro falla si el formato de email es inválido.
     */
    public function testRegisterFailsWithInvalidEmailFormat()
    {
        $result = $this->auth->register('Test', 'User', 'email-invalido', 'password123');
        $this->assertEquals("Formato de email inválido.", $result);
    }

    // --- PRUEBAS DE LOGIN (CORREGIDAS) ---

    /**
     * @covers App\Auth::login
     * Prueba un inicio de sesión exitoso.
     */
    public function testLoginSuccess()
    {
        // 1. Arrange
        $nombre = 'TestLogin';
        $apellidos = 'User';
        $email = 'login@example.com';
        $password = 'password123';
        
        // Registrar un usuario
        $userId = $this->auth->register($nombre, $apellidos, $email, $password);
        $this->assertIsInt($userId);

        // 2. Act: Intentar iniciar sesión
        $result = $this->auth->login($email, $password);

        // 3. Assert
        $this->assertIsArray($result, "El login exitoso debe devolver un array.");
        // Comprobamos que devuelve [id, nombre]
        $this->assertEquals(['id' => $userId, 'nombre' => $nombre], $result);
    }

    /**
     * @covers App\Auth::login
     * Prueba que el login falla con una contraseña incorrecta.
     */
    public function testLoginFailsWithWrongPassword()
    {
        // 1. Arrange
        $this->auth->register('Test', 'User', 'login@example.com', 'password_correcta');

        // 2. Act
        $result = $this->auth->login('login@example.com', 'password_incorrecta');

        // 3. Assert
        $this->assertEquals("Email o contraseña incorrectos.", $result);
    }

    /**
     * @covers App\Auth::login
     * Prueba que el login falla si el usuario no existe.
     */
    public function testLoginFailsWithUserNotFound()
    {
        $result = $this->auth->login('nouser@example.com', 'password123');
        $this->assertEquals("Email o contraseña incorrectos.", $result);
    }
}