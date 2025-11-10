<?php

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    /**
     * @covers database_connection.php
     *
     * Probamos que el script database_connection.php se ejecuta sin excepciones
     * y que define correctamente la variable $pdo.
     */
    public function testDatabaseConnection()
    {
        $pdo = null;
        $error = '';
        
        ob_start();

        try {
            // Incluimos SOLAMENTE el script de conexión a la BD
            include __DIR__ . '/../database_connection.php';
            
        } catch (\PDOException $e) {
            // Capturamos la excepción que $pdo lanza si falla la conexión
            $error = $e->getMessage();
        }

        ob_get_clean();

        // 1. Verificamos que no hubo ninguna excepción PDO durante la conexión.
        $this->assertEmpty(
            $error,
            "La conexión PDO lanzó una excepción: " . $error
        );

        // 2. Verificamos que la variable $pdo (de database_connection.php) fue definida.
        $this->assertNotNull(
            $pdo, 
            "La variable \$pdo es nula. ¿El archivo database_connection.php se incluyó correctamente?"
        );

        // 3. Verificamos que $pdo es realmente un objeto de la clase PDO.
        $this->assertInstanceOf(
            PDO::class, 
            $pdo, 
            "La variable \$pdo no es una instancia de PDO."
        );
    }
}