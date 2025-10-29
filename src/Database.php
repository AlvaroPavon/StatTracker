<?php namespace App;

use PDO;
// use PDOException; // <-- Eliminamos esta línea

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}
    public function __wakeup() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $host = 'localhost';
            $dbname = 'proyecto_imc';
            $username = 'root';
            $password = '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $username, $password, $options);
            } catch (\PDOException $e) { // <-- AÑADIR LA BARRA INVERTIDA
                error_log('Connection Error: ' . $e->getMessage());
                // Lanzamos la excepción con la barra invertida también
                throw new \PDOException("Database connection error.", (int)$e->getCode());
            }
        }
        return self::$instance;
    }
}