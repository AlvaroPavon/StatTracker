<?php

namespace App;

use PDO;
use PDOException;

/**
 * Clase User para manejar la gestión del perfil de usuario.
 * VERSIÓN CORREGIDA para la BD 'usuarios'
 */
class User
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Actualiza el perfil (nombre, apellidos, email) de un usuario.
     *
     * @param int $userId
     * @param string $newNombre
     * @param string $newApellidos
     * @param string $newEmail
     * @return true|string Devuelve true si tiene éxito, o un string con el mensaje de error si falla.
     */
    public function updateProfile(int $userId, string $newNombre, string $newApellidos, string $newEmail): bool|string
    {
        // 1. Validar email
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            return "Formato de email inválido.";
        }

        // 2. Validar campos vacíos
        if (empty($newNombre) || empty($newApellidos) || empty($newEmail)) {
            return "El nombre, apellidos y email no pueden estar vacíos.";
        }

        try {
            // 3. Comprobar si el NUEVO email ya está en uso por OTRO usuario
            $stmt = $this->pdo->prepare(
                "SELECT id FROM usuarios WHERE email = ? AND id != ?"
            );
            $stmt->execute([$newEmail, $userId]);
            
            if ($stmt->fetch()) {
                return "El email ya está registrado por otro usuario.";
            }

            // 4. CORRECCIÓN: Actualizar la tabla 'usuarios'
            $stmt = $this->pdo->prepare(
                "UPDATE usuarios SET nombre = ?, apellidos = ?, email = ? WHERE id = ?"
            );
            $stmt->execute([$newNombre, $newApellidos, $newEmail, $userId]);

            return true;

        } catch (PDOException $e) {
            // El email es la única columna UNIQUE (aparte de ID) en 'usuarios'
            if ($e->getCode() == '23000' || $e->errorInfo[1] == 1062) { 
                 return "El email ya existe.";
            }
            
            return "Error al actualizar el perfil: " . $e->getMessage();
        }
    }

    /**
     * Cambia la contraseña de un usuario.
     *
     * @param int $userId
     * @param string $oldPassword
     * @param string $newPassword
     * @param string $confirmNewPassword
     * @return true|string Devuelve true si tiene éxito, o un string con el mensaje de error si falla.
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword, string $confirmNewPassword): bool|string
    {
        // 1. Validaciones
        if (empty($oldPassword) || empty($newPassword) || empty($confirmNewPassword)) {
            return "Todos los campos de contraseña son obligatorios.";
        }

        if ($newPassword !== $confirmNewPassword) {
            return "Las nuevas contraseñas no coinciden.";
        }

        if (strlen($newPassword) < 8) {
            return 'La nueva contraseña debe tener al menos 8 caracteres.';
        }

        try {
            // 2. CORRECCIÓN: Obtener hash de la tabla 'usuarios'
            $stmt_select = $this->pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmt_select->execute([$userId]);
            $user = $stmt_select->fetch();

            if (!$user) {
                return "Error de usuario. No se encontró el usuario.";
            }

            // 3. Verificar contraseña anterior
            if (password_verify($oldPassword, $user['password'])) {
                
                $hashed_new_password = password_hash($newPassword, PASSWORD_DEFAULT);

                // 4. CORRECCIÓN: Actualizar la tabla 'usuarios'
                $stmt_update = $this->pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                $stmt_update->execute([$hashed_new_password, $userId]);

                return true;

            } else {
                return "La contraseña anterior es incorrecta.";
            }

        } catch (PDOException $e) {
            return "Error en la base de datos: " . $e->getMessage();
        }
    }
}