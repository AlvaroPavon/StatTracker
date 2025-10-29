<?php namespace App;

use PDO;
use PDOException;

class User {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Busca un usuario por su ID.
     */
    public function findById(int $userId): ?array {
        $stmt = $this->pdo->prepare("SELECT id, nombre, apellidos, email, profile_pic FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Actualiza el perfil de un usuario (sin foto).
     */
    public function updateProfile(int $userId, string $nombre, string $apellidos, string $email): bool {
        $sql = "UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, email = :email 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'email' => $email,
            'id' => $userId
        ]);
    }

    /**
     * Actualiza el perfil de un usuario (con foto nueva).
     */
    public function updateProfileWithPic(int $userId, string $nombre, string $apellidos, string $email, string $picFilename): bool {
        $sql = "UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, email = :email, profile_pic = :profile_pic 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'email' => $email,
            'profile_pic' => $picFilename,
            'id' => $userId
        ]);
    }

    /**
     * Cambia la contraseña de un usuario.
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): bool {
        // 1. Obtener el hash actual
        $stmt_select = $this->pdo->prepare("SELECT password FROM usuarios WHERE id = :id");
        $stmt_select->execute(['id' => $userId]);
        $user = $stmt_select->fetch();

        if (!$user) {
            return false; // Usuario no encontrado
        }

        // 2. Verificar la contraseña anterior
        if (password_verify($oldPassword, $user['password'])) {
            // 3. Hashear y actualizar la nueva contraseña
            $hashed_new_password = password_hash($newPassword, PASSWORD_BCRYPT);
            
            $sql_update = "UPDATE usuarios SET password = :password WHERE id = :id";
            $stmt_update = $this->pdo->prepare($sql_update);
            
            return $stmt_update->execute([
                'password' => $hashed_new_password,
                'id' => $userId
            ]);
        }

        return false; // La contraseña anterior no coincidió
    }
}