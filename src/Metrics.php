<?php

namespace App;

use PDO;

class Metrics
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Añade un nuevo registro de salud (peso, altura, imc) para un usuario.
     */
    public function addHealthData(int $user_id, float $peso, float $altura, string $fecha_registro): bool|string
    {
        // 1. Calcular IMC
        if ($altura <= 0) {
            return "La altura no puede ser cero.";
        }
        $imc = round($peso / ($altura * $altura), 2);

        // 2. Preparar la inserción
        $sql = "INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) 
                VALUES (:user_id, :peso, :altura, :imc, :fecha_registro)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'peso' => $peso,
                'altura' => $altura,
                'imc' => $imc,
                'fecha_registro' => $fecha_registro
            ]);
            return true;
        } catch (\PDOException $e) {
            // CORRECCIÓN CRÍTICA: Capturar el SQLSTATE 23000 (Violación de restricción de integridad, incluye FK)
            if ($e->getCode() === '23000') {
                 // Devuelve el mensaje esperado por la prueba fallida
                return "ID de usuario inválido."; 
            }
            // Devolver mensaje de error genérico si no es un error que esperemos
            return "Error al guardar los datos: " . $e->getMessage();
        }
    }

    /**
     * Obtiene todos los registros de salud de un usuario.
     */
    public function getHealthData(int $user_id): array|string
    {
        // Ordenación de más reciente a más antiguo, usando ID como desempate (Corregido en la sesión anterior)
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
                return "No se encontró el registro o no tiene permiso para borrarlo.";
            }
        } catch (\PDOException $e) {
            return "Error al borrar los datos: " . $e->getMessage();
        }
    }
}