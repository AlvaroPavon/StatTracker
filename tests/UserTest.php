<?php

use PHPUnit\Framework\TestCase;
use App\User; // Usamos la clase que creamos en src/

class UserTest extends TestCase
{
    private $pdo;
    private $user;

    /**
     * setUp() se ejecuta ANTES de cada método de prueba.
     */
    protected function setUp(): void
    {
        // 1. Crear una conexión PDO a una base de datos SQLite en memoria
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 2. CORRECCIÓN: Crear la tabla 'usuarios'
        $this->pdo->exec("
            CREATE TABLE usuarios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL,
                apellidos TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL
            )
        ");

        // 3. Instanciar nuestra clase User
        $this->user = new User($this->pdo);

        // 4. (Helper) Insertar usuarios de prueba
        
        // User 1 tiene una contraseña conocida para probar el cambio
        $known_password_hash = password_hash('password123', PASSWORD_DEFAULT);
        
        // CORRECCIÓN: Insertar en 'usuarios'
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (id, nombre, apellidos, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([1, 'user1', 'lastname1', 'user1@test.com', $known_password_hash]);
        
        // User 2 es para pruebas de duplicados
        $stmt->execute([2, 'user2', 'lastname2', 'user2@test.com', 'hash2']);
    }

    /**
     * tearDown() se ejecuta DESPUÉS de cada método de prueba.
     */
    protected function tearDown(): void
    {
        $this->pdo = null;
        $this->user = null;
    }

    // --- PRUEBAS DE UPDATE PROFILE (CORREGIDAS: 4) ---

    /**
     * @covers App\User::updateProfile
     * Prueba el "camino feliz": actualización exitosa.
     */
    public function testUpdateProfileSuccess()
    {
        // 1. Act: CORRECCIÓN: Usar nombre, apellidos, email
        $result = $this->user->updateProfile(1, 'user1_nuevo', 'lastname_nuevo', 'user1_nuevo@test.com');

        // 2. Assert
        $this->assertTrue($result, "Debe devolver 'true' si la actualización es exitosa.");

        // Afirmación extra: verificar que los datos están en la BD
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = 1");
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('user1_nuevo', $data['nombre']);
        $this->assertEquals('lastname_nuevo', $data['apellidos']);
        $this->assertEquals('user1_nuevo@test.com', $data['email']);
    }

    /**
     * @covers App\User::updateProfile
     * Prueba que falla si el email ya está en uso por OTRO usuario.
     */
    public function testUpdateProfileFailsWithDuplicateEmail()
    {
        // 1. Act: User 1 intenta tomar el email de User 2
        $result = $this->user->updateProfile(1, 'user1_nuevo', 'lastname_nuevo', 'user2@test.com');

        // 2. Assert
        $this->assertEquals("El email ya está registrado por otro usuario.", $result);
    }

    /**
     * @covers App\User::updateProfile
     * Prueba que falla si el formato de email es inválido.
     */
    public function testUpdateProfileFailsWithInvalidEmail()
    {
        // 1. Act
        $result = $this->user->updateProfile(1, 'user1_nuevo', 'lastname_nuevo', 'email-invalido');

        // 2. Assert
        $this->assertEquals("Formato de email inválido.", $result);
    }

    /**
     * @covers App\User::updateProfile
     * Prueba que el usuario PUEDE "actualizar" su perfil con su propio email.
     */
    public function testUpdateProfileSuccessWhenKeepingOwnEmail()
    {
        // 1. Act: User 1 actualiza su nombre PERO mantiene su email actual
        $result = $this->user->updateProfile(1, 'user1_nuevo', 'lastname_nuevo', 'user1@test.com');

        // 2. Assert
        $this->assertTrue($result, "Debe devolver 'true' aunque el email no cambie.");
    }


    // --- PRUEBAS DE CHANGE PASSWORD (CORREGIDAS: 5) ---
    // (Estas pruebas no cambian, ya que se basan en el setUp corregido)

    /**
     * @covers App\User::changePassword
     * Prueba el "camino feliz": cambio de contraseña exitoso.
     */
    public function testChangePasswordSuccess()
    {
        // 1. Act: Usamos la contraseña correcta 'password123'
        $result = $this->user->changePassword(1, 'password123', 'nuevaPasswordSegura', 'nuevaPasswordSegura');

        // 2. Assert
        $this->assertTrue($result, "Debe devolver 'true' si el cambio es exitoso.");

        // 3. Afirmación extra: verificar que la nueva contraseña está en la BD
        $stmt = $this->pdo->prepare("SELECT password FROM usuarios WHERE id = 1");
        $stmt->execute();
        $hash = $stmt->fetchColumn();
        
        $this->assertTrue(
            password_verify('nuevaPasswordSegura', $hash),
            "La nueva contraseña no se guardó (o no se hasheó) correctamente."
        );
        $this->assertFalse(
            password_verify('password123', $hash),
            "La contraseña antigua sigue funcionando (no debería)."
        );
    }

    /**
     * @covers App\User::changePassword
     * Prueba que falla si la contraseña antigua es incorrecta.
     */
    public function testChangePasswordFailsWrongOldPassword()
    {
        $result = $this->user->changePassword(1, 'password_incorrecta', 'nuevaPasswordSegura', 'nuevaPasswordSegura');
        $this->assertEquals("La contraseña anterior es incorrecta.", $result);
    }

    /**
     * @covers App\User::changePassword
     * Prueba que falla si las nuevas contraseñas no coinciden.
     */
    public function testChangePasswordFailsNewPasswordsDoNotMatch()
    {
        $result = $this->user->changePassword(1, 'password123', 'nueva1', 'nueva2');
        $this->assertEquals("Las nuevas contraseñas no coinciden.", $result);
    }

    /**
     * @covers App\User::changePassword
     * Prueba la validación de longitud de la nueva contraseña.
     */
    public function testChangePasswordFailsNewPasswordTooShort()
    {
        $result = $this->user->changePassword(1, 'password123', 'short', 'short');
        $this->assertEquals("La nueva contraseña debe tener al menos 8 caracteres.", $result);
    }

    /**
     * @covers App\User::changePassword
     * Prueba que falla si algún campo está vacío.
     */
    public function testChangePasswordFailsWithEmptyFields()
    {
        $result = $this->user->changePassword(1, '', 'nueva', 'nueva');
        $this->assertEquals("Todos los campos de contraseña son obligatorios.", $result);

        $result2 = $this->user->changePassword(1, 'password123', '', '');
        $this->assertEquals("Todos los campos de contraseña son obligatorios.", $result2);
    }
}