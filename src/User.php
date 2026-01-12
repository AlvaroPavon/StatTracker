<?php

namespace App;

use PDO;
use PDOException;

/**
 * Clase User para manejar la gestión del perfil de usuario.
 * VERSIÓN SEGURA con validaciones mejoradas
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
     * @return true|string Devuelve true si tiene éxito, o un string con el mensaje de error.
     */
    public function updateProfile(int $userId, string $newNombre, string $newApellidos, string $newEmail): bool|string
    {
        // 1. Validar nombre
        $nombreValidation = Security::validateNombre($newNombre);
        if (!$nombreValidation['valid']) {
            return $nombreValidation['error'];
        }
        $newNombre = $nombreValidation['value'];

        // 2. Validar apellidos
        $apellidosValidation = Security::validateApellidos($newApellidos);
        if (!$apellidosValidation['valid']) {
            return $apellidosValidation['error'];
        }
        $newApellidos = $apellidosValidation['value'];

        // 3. Validar email
        $emailValidation = Security::validateEmail($newEmail);
        if (!$emailValidation['valid']) {
            return $emailValidation['error'];
        }
        $newEmail = $emailValidation['value'];

        try {
            // 4. Comprobar si el NUEVO email ya está en uso por OTRO usuario
            $stmt = $this->pdo->prepare(
                "SELECT id FROM usuarios WHERE email = ? AND id != ?"
            );
            $stmt->execute([$newEmail, $userId]);
            
            if ($stmt->fetch()) {
                return "El email ya está registrado por otro usuario.";
            }

            // 5. Actualizar la tabla 'usuarios'
            $stmt = $this->pdo->prepare(
                "UPDATE usuarios SET nombre = ?, apellidos = ?, email = ? WHERE id = ?"
            );
            $stmt->execute([$newNombre, $newApellidos, $newEmail, $userId]);

            return true;

        } catch (PDOException $e) {
            error_log("Error al actualizar perfil: " . $e->getMessage());
            
            if ($e->getCode() == '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) { 
                return "El email ya existe.";
            }
            
            return "Error al actualizar el perfil. Inténtelo más tarde.";
        }
    }

    /**
     * Cambia la contraseña de un usuario con validaciones de seguridad.
     *
     * @param int $userId
     * @param string $oldPassword
     * @param string $newPassword
     * @param string $confirmNewPassword
     * @return true|string Devuelve true si tiene éxito, o un string con el mensaje de error.
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword, string $confirmNewPassword): bool|string
    {
        // 1. Validar que los campos no estén vacíos
        if (empty($oldPassword) || empty($newPassword) || empty($confirmNewPassword)) {
            return "Todos los campos de contraseña son obligatorios.";
        }

        // 2. Validar que las nuevas contraseñas coincidan
        if ($newPassword !== $confirmNewPassword) {
            return "Las nuevas contraseñas no coinciden.";
        }

        // 3. Validar requisitos de la nueva contraseña
        $passwordValidation = Security::validatePassword($newPassword);
        if (!$passwordValidation['valid']) {
            return $passwordValidation['error'];
        }

        // 4. Verificar que la nueva contraseña sea diferente a la anterior
        if ($oldPassword === $newPassword) {
            return "La nueva contraseña debe ser diferente a la actual.";
        }

        try {
            // 5. Obtener hash de la contraseña actual
            $stmt_select = $this->pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmt_select->execute([$userId]);
            $user = $stmt_select->fetch();

            if (!$user) {
                return "Usuario no encontrado.";
            }

            // 6. Verificar contraseña anterior
            if (password_verify($oldPassword, $user['password'])) {
                
                $hashed_new_password = password_hash($newPassword, PASSWORD_DEFAULT, ['cost' => 12]);

                // 7. Actualizar contraseña
                $stmt_update = $this->pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                $stmt_update->execute([$hashed_new_password, $userId]);

                return true;

            } else {
                return "La contraseña actual es incorrecta.";
            }

        } catch (PDOException $e) {
            error_log("Error al cambiar contraseña: " . $e->getMessage());
            return "Error al procesar la solicitud. Inténtelo más tarde.";
        }
    }

    /**
     * Actualiza la foto de perfil de un usuario.
     *
     * @param int $userId
     * @param array $file Array $_FILES['profile_pic']
     * @return true|string Devuelve true si tiene éxito, o un string con el mensaje de error.
     */
    public function updateProfilePicture(int $userId, array $file): bool|string
    {
        // 1. Validar el archivo
        $validation = Security::validateImageUpload($file);
        if (!$validation['valid']) {
            return $validation['error'];
        }

        // 2. Generar nombre seguro
        $newFilename = Security::generateSecureFilename($userId, $validation['extension']);
        $uploadDir = __DIR__ . '/../uploads/';
        $uploadPath = $uploadDir . $newFilename;

        // 3. Crear directorio si no existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        try {
            // 4. Obtener foto anterior para eliminarla
            $stmt = $this->pdo->prepare("SELECT profile_pic FROM usuarios WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            // 5. Mover archivo subido
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                return "Error al guardar la imagen.";
            }

            // 6. Actualizar en base de datos
            $stmt = $this->pdo->prepare("UPDATE usuarios SET profile_pic = ? WHERE id = ?");
            $stmt->execute([$newFilename, $userId]);

            // 7. Eliminar foto anterior si existe
            if ($user && !empty($user['profile_pic'])) {
                $oldPath = $uploadDir . $user['profile_pic'];
                if (file_exists($oldPath) && $user['profile_pic'] !== $newFilename) {
                    @unlink($oldPath);
                }
            }

            return true;

        } catch (PDOException $e) {
            error_log("Error al actualizar foto de perfil: " . $e->getMessage());
            // Eliminar archivo si falló la BD
            if (file_exists($uploadPath)) {
                @unlink($uploadPath);
            }
            return "Error al actualizar la foto de perfil.";
        }
    }
}
