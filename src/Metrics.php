<?php

namespace App;

use PDO;
// Importamos la excepción con el prefijo global \
use \PDOException;

class Metrics
{
    // Usamos el type hint global para asegurar que no se busca App\PDO
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Añade un nuevo registro de salud (peso, altura, imc) para un usuario.
     */
    public function addHealthData(int $user_id, float $peso, float $altura, string $fecha_registro): bool|string
    {
        // La validación de altura <= 0 es necesaria para prevenir divisiones por cero.
        if ($altura <= 0) {
            return "La altura no puede ser cero.";
        }
        // Corrección de redondeo F1: Asegura que el IMC se calcula con 2 decimales.
        $imc = round($peso / ($altura * $altura), 2);

        // 2. Preparar la inserción (Usando la tabla 'metricas' de tu esquema)
        $sql = "INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) 
                VALUES (:user_id, :peso, :altura, :imc, :fecha_registro)";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'user_id' => $user_id,
                'peso' => $peso,
                'altura' => $altura,
                'imc' => $imc,
                'fecha_registro' => $fecha_registro
            ]);
        } catch (\PDOException $e) {
            // Manejo de error para user_id inválido (Foreign Key Constraint Violation)
            if ($e->getCode() === '23000') {
                 // Este mensaje es el que la prueba MetricsTest.php espera.
                return "ID de usuario inválido."; 
            }
            return "Error al guardar los datos: " . $e->getMessage();
        }
    }

    /**
     * Obtiene todos los registros de salud de un usuario.
     */
    public function getHealthData(int $user_id): array|string
    {
        // Ordenación de más reciente a más antiguo
        $sql = "SELECT id, peso, altura, imc, fecha_registro 
                FROM metricas 
                WHERE user_id = :user_id 
                ORDER BY fecha_registro DESC, id DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return "Error al obtener los datos: " . $e->getMessage();
        }
    }

    /**
     * Elimina un registro de salud específico.
     */
    public function deleteHealthData(int $user_id, int $data_id): bool|string
    {
        // La consulta se asegura de que solo el usuario propietario pueda borrar su registro
        $sql = "DELETE FROM metricas 
                WHERE id = :data_id AND user_id = :user_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'data_id' => $data_id,
                'user_id' => $user_id
            ]);

            // Comprobar si realmente se borró algo
            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                // Este mensaje es el que la prueba MetricsTest.php espera.
                return "No se encontró el registro o no tiene permiso para borrarlo.";
            }
        } catch (\PDOException $e) {
            return "Error al borrar los datos: " . $e->getMessage();
        }
    }
}