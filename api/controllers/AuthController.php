<?php
/**
 * Controlador de Autenticación
 * Maneja registro, login y logout
 */

require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../middleware/JWTMiddleware.php';
require_once __DIR__ . '/../../database_connection.php';

class AuthController {
    private $db;

    public function __construct() {
        global $pdo;
        $this->db = $pdo;
    }

    /**
     * Registro de nuevo usuario
     * POST /api/auth/register
     */
    public function register(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        $nombre = trim($data['nombre'] ?? '');
        $apellidos = trim($data['apellidos'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        // Validaciones
        if (empty($nombre) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre, email y contraseña son requeridos']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email inválido']);
            return;
        }

        if (strlen($password) < 8) {
            http_response_code(400);
            echo json_encode(['error' => 'La contraseña debe tener al menos 8 caracteres']);
            return;
        }

        // Verificar si email ya existe
        $stmt = $this->db->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'El email ya está registrado']);
            return;
        }

        // Hash de contraseña con Argon2id (compatible con el proyecto original)
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 4
        ]);

        // Insertar usuario
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nombre, apellidos, email, password) VALUES (?, ?, ?, ?)'
        );
        
        try {
            $stmt->execute([$nombre, $apellidos, $email, $passwordHash]);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Usuario registrado correctamente',
                'user_id' => $this->db->lastInsertId()
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al registrar usuario']);
        }
    }

    /**
     * Login de usuario
     * POST /api/auth/login
     */
    public function login(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email y contraseña son requeridos']);
            return;
        }

        // Buscar usuario
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciales inválidas']);
            return;
        }

        // Generar JWT
        $token = JWTMiddleware::generateToken([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'nombre' => $user['nombre']
        ]);

        echo json_encode([
            'success' => true,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWT_EXPIRY,
            'user' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'apellidos' => $user['apellidos'],
                'email' => $user['email']
            ]
        ]);
    }

    /**
     * Logout (el token se invalida del lado del cliente)
     * POST /api/auth/logout
     */
    public function logout(): void {
        echo json_encode([
            'success' => true,
            'message' => 'Sesión cerrada correctamente'
        ]);
    }
}
