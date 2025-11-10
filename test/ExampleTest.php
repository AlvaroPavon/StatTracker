<?php

// Asegúrate de que el namespace coincida con la estructura de directorios
// (si 'tests' está en la raíz, no necesita namespace, pero usamos TestCase)

use PHPUnit\Framework\TestCase;

/**
 * Clase ExampleTest
 *
 * Esta es una prueba unitaria de ejemplo para verificar que PHPUnit está configurado.
 */
class ExampleTest extends TestCase
{
    /**
     * Prueba que una aserción básica (true es true) funciona.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}