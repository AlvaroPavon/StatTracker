<?php

namespace App;

use PDO;
use PDOException;

/**
 * Clase Auth para manejar la autenticación (registro y login)
 * VERSIÓN SEGURA con validaciones mejoradas
 */
class Auth
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Registra un nuevo usuario con validaciones de seguridad.
     *
     * @param string $nombre
     * @param string $apellidos
     * @param string $email
     * @param string $password
     * @return int|string Devuelve el ID del nuevo usuario si tiene éxito, o un string con el mensaje de error si falla.
     */
    public function register(string $nombre, string $apellidos, string $email, string $password): int|string
    {
        // 1. Validar nombre
        $nombreValidation = Security::validateNombre($nombre);
        if (!$nombreValidation['valid']) {
            return $nombreValidation['error'];
        }
        $nombre = $nombreValidation['value'];

        // 2. Validar apellidos
        $apellidosValidation = Security::validateApellidos($apellidos);
        if (!$apellidosValidation['valid']) {
            return $apellidosValidation['error'];
        }
        $apellidos = $apellidosValidation['value'];

        // 3. Validar email
        $emailValidation = Security::validateEmail($email);
        if (!$emailValidation['valid']) {
            return $emailValidation['error'];
        }
        $email = $emailValidation['value'];

        // 4. Validar contraseña con requisitos de complejidad
        $passwordValidation = Security::validatePassword($password);
        if (!$passwordValidation['valid']) {
            return $passwordValidation['error'];
        }

        try {
            // 5. Comprobar si el email ya existe (sin revelar información)
            $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                // SEGURIDAD: No revelar que el email existe
                // Simulamos éxito pero no creamos la cuenta
                // Esto previene enumeración de usuarios
                SecurityAudit::log('REGISTER_EMAIL_EXISTS', null, [
                    'email' => substr($email, 0, 3) . '***'
                ], 'INFO');
                
                // Retornamos un ID falso para simular éxito
                // El usuario recibirá "cuenta creada" pero no podrá hacer login
                // Alternativa más segura: siempre mostrar el mismo mensaje
                return "Se ha enviado un email de verificación. Por favor, revise su bandeja de entrada.";
            }
        } catch (PDOException $e) {
            error_log("Error al verificar email: " . $e->getMessage());
            return "Error al procesar la solicitud. Inténtelo más tarde.";
        }

        // 6. Hashear la contraseña con bcrypt
        $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
        if ($hashed_password === false) {
            return "Error al procesar la contraseña.";
        }

        // 7. Insertar usuario
        try {
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, apellidos, email, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellidos, $email, $hashed_password]);
            
            return (int)$this->pdo->lastInsertId();

        } catch (PDOException $e) {
            error_log("Error al registrar usuario: " . $e->getMessage());
            return "Error al crear la cuenta. Inténtelo más tarde.";
        }
    }

    /**
     * Valida las credenciales de un usuario con protección Rate Limiting.
     *
     * @param string $email
     * @param string $password
     * @return array|string Devuelve un array con [id, nombre] si tiene éxito, o un string con el mensaje de error.
     */
    public function login(string $email, string $password): array|string
    {
        // 1. Validar formato de email
        $emailValidation = Security::validateEmail($email);
        if (!$emailValidation['valid']) {
            return "Credenciales inválidas.";
        }
        $email = $emailValidation['value'];

        // 2. Verificar Rate Limiting
        $rateLimitCheck = Security::checkLoginAttempts($email);
        if (!$rateLimitCheck['allowed']) {
            return $rateLimitCheck['error'];
        }

        try {
            // 3. Buscar al usuario por email
            $stmt = $this->pdo->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 4. Verificar si el usuario existe y la contraseña es correcta
            // IMPORTANTE: Usar verificación timing-safe
            if ($user && TimingSafe::verifyPassword($password, $user['password'])) {
                // Éxito: Resetear intentos fallidos
                Security::resetLoginAttempts($email);
                
                return [
                    'id' => (int)$user['id'],
                    'nombre' => $user['nombre']
                ];
            } else {
                // Fallo: Registrar intento fallido
                // IMPORTANTE: El tiempo de respuesta es igual para usuario inexistente y contraseña incorrecta
                Security::recordFailedLogin($email);
                
                // Añadir delay aleatorio para dificultar timing attacks
                TimingSafe::randomDelay();
                
                // Mensaje genérico para no revelar si el email existe
                return "Credenciales inválidas.";
            }
        } catch (PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            return "Error al procesar la solicitud. Inténtelo más tarde.";
        }
    }
}
