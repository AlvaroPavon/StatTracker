<?php
/**
 * Controlador de Perfil
 * Maneja información y configuración del usuario
 */

require_once __DIR__ . '/../middleware/JWTMiddleware.php';
require_once __DIR__ . '/../../database_connection.php';
require_once __DIR__ . '/../../src/CryptoFortress.php';

class ProfileController {
    private $db;
    private $user;

    public function __construct() {
        global $pdo;
        $this->db = $pdo;
        $this->user = JWTMiddleware::verify();
        
        if ($this->user === null) {
            exit;
        }
    }

    /**
     * Obtener perfil del usuario
     * GET /api/profile
     */
    public function show(): void {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, apellidos, email, profile_pic, created_at, updated_at 
             FROM usuarios 
             WHERE id = ?'
        );
        $stmt->execute([$this->user['user_id']]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$profile) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            return;
        }

        // Obtener estadísticas del usuario
        $statsStmt = $this->db->prepare(
            'SELECT 
                COUNT(*) as total_registros,
                MIN(peso) as peso_min,
                MAX(peso) as peso_max,
                AVG(peso) as peso_promedio,
                MIN(imc) as imc_min,
                MAX(imc) as imc_max,
                AVG(imc) as imc_promedio
             FROM metricas 
             WHERE user_id = ?'
        );
        $statsStmt->execute([$this->user['user_id']]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'profile' => $profile,
            'stats' => $stats
        ]);
    }

    /**
     * Actualizar perfil del usuario
     * PUT /api/profile
     */
    public function update(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        $nombre = trim($data['nombre'] ?? '');
        $apellidos = trim($data['apellidos'] ?? '');

        if (empty($nombre)) {
            http_response_code(400);
            echo json_encode(['error' => 'El nombre es requerido']);
            return;
        }

        $stmt = $this->db->prepare(
            'UPDATE usuarios 
             SET nombre = ?, apellidos = ? 
             WHERE id = ?'
        );

        try {
            $stmt->execute([$nombre, $apellidos, $this->user['user_id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Perfil actualizado correctamente',
                'profile' => [
                    'id' => $this->user['user_id'],
                    'nombre' => $nombre,
                    'apellidos' => $apellidos
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar el perfil']);
        }
    }

    /**
     * Cambiar contraseña
     * POST /api/profile/password
     */
    public function changePassword(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            http_response_code(400);
            echo json_encode(['error' => 'Contraseña actual y nueva son requeridas']);
            return;
        }

        if (strlen($newPassword) < 8) {
            http_response_code(400);
            echo json_encode(['error' => 'La nueva contraseña debe tener al menos 8 caracteres']);
            return;
        }

        // Verificar contraseña actual
        $stmt = $this->db->prepare('SELECT password FROM usuarios WHERE id = ?');
        $stmt->execute([$this->user['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!\App\CryptoFortress::verifyPassword($currentPassword, $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Contraseña actual incorrecta']);
            return;
        }

        // Hash nueva contraseña con CryptoFortress
        $passwordHash = \App\CryptoFortress::hashPassword($newPassword);

        $stmt = $this->db->prepare('UPDATE usuarios SET password = ? WHERE id = ?');
        
        try {
            $stmt->execute([$passwordHash, $this->user['user_id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Contraseña cambiada correctamente'
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al cambiar la contraseña']);
        }
    }
}
