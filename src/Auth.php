<?php

namespace App;

use PDO;
use PDOException;

/**
 * Clase Auth para manejar la autenticación (registro y login)
 * VERSIÓN CORREGIDA para la BD 'usuarios'
 */
class Auth
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Registra un nuevo usuario.
     *
     * @param string $nombre
     * @param string $apellidos
     * @param string $email
     * @param string $password
     * @return int|string Devuelve el ID del nuevo usuario si tiene éxito, o un string con el mensaje de error si falla.
     */
    public function register(string $nombre, string $apellidos, string $email, string $password): int|string
    {
        // 1. Validar campos vacíos
        if (empty($nombre) || empty($apellidos) || empty($email) || empty($password)) {
            return "Todos los campos son obligatorios.";
        }

        // 2. Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Formato de email inválido.";
        }

        try {
            // 3. Comprobar si el email ya existe (en la tabla 'usuarios')
            $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return "El email ya está registrado.";
            }
        } catch (PDOException $e) {
             return "Error al verificar el email: " . $e->getMessage();
        }

        // 4. Hashear la contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if ($hashed_password === false) {
            return "Error al hashear la contraseña.";
        }

        // 5. Insertar usuario (en la tabla 'usuarios')
        try {
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellidos, email, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellidos, $email, $hashed_password]);
            
            // 6. Devolver el ID del nuevo usuario
            return (int)$this->pdo->lastInsertId();

        } catch (PDOException $e) {
            // Devolver un error genérico
            return "Error al registrar el usuario: " . $e->getMessage();
        }
    }

    /**
     * Valida las credenciales de un usuario.
     *
     * @param string $email
     * @param string $password
     * @return array|string Devuelve un array con [id, nombre] si tiene éxito, o un string con el mensaje de error si falla.
     */
    public function login(string $email, string $password): array|string
    {
        try {
            // 1. Buscar al usuario por email (en la tabla 'usuarios')
            $stmt = $this->pdo->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Verificar si el usuario existe y la contraseña es correcta
            if ($user && password_verify($password, $user['password'])) {
                // 3. Éxito: Devolver los datos del usuario
                return [
                    'id' => (int)$user['id'],
                    'nombre' => $user['nombre'] // Devolvemos 'nombre'
                ];
            } else {
                // 4. Fallo: Credenciales incorrectas
                return "Email o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            return "Error al intentar iniciar sesión: " . $e->getMessage();
        }
    }
}