<?php

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    /**
     * @coversNothing
     *
     * Nota: Idealmente, no deberíamos incluir 'db.php' directamente
     * porque ejecuta código (se conecta) en lugar de solo definir.
     * Pero para la estructura actual, es nuestro punto de partida.
     */
    public function testDatabaseConnection()
    {
        // Usamos output buffering para capturar el 'die()' si la conexión falla.
        ob_start();
        
        // Definimos una variable para chequear si el test falló por el 'die'
        $conn = null;
        $error = '';

        try {
            include __DIR__ . '/../db.php';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $output = ob_get_clean();

        // Si db.php llamó a die(), $conn no será un objeto mysqli
        
        // $conn debe estar definido en el scope de db.php
        $this->assertInstanceOf(
            mysqli::class, 
            $conn, 
            "La variable \$conn no es una instancia de mysqli. ¿Falló la inclusión de db.php?"
        );

        // Verificamos que no haya error de conexión
        $this->assertEmpty(
            $conn->connect_error, 
            "La conexión a la BD falló: " . $conn->connect_error . " (Output: " . $output . ")"
        );

        // Si llegamos aquí, la conexión fue exitosa.
        $conn->close();
    }
}