<?php namespace App;

use PDO;
// use PDOException; // <-- Eliminamos esta línea

class Auth {
    
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function login(string $email, string $password): ?array {
        if (empty($email) || empty($password)) {
            return null;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT id, nombre, password FROM usuarios WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                unset($user['password']);
                return $user;
            }
        } catch (\PDOException $e) { // <-- AÑADIR LA BARRA INVERTIDA
            error_log('Error en Auth::login: ' . $e->getMessage());
            throw $e; // Relanzamos la excepción
        }

        return null; 
    }

    public function register(string $nombre, string $apellidos, string $email, string $password): bool {
        if ($this->findUserByEmail($email)) {
            return false; 
        }
        
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios (nombre, apellidos, email, password) 
                VALUES (:nombre, :apellidos, :email, :password)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'email' => $email,
            'password' => $hashed_password
        ]);
    }

    public function findUserByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT id, email FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
}