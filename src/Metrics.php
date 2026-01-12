<?php

namespace App;

use PDO;
use \PDOException;

/**
 * Clase Metrics para manejar registros de salud (IMC)
 * VERSIÓN SEGURA con validaciones mejoradas
 */
class Metrics
{
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
        // 1. Validar altura
        $alturaValidation = Security::validateAltura($altura);
        if (!$alturaValidation['valid']) {
            return $alturaValidation['error'];
        }
        $altura = $alturaValidation['value'];

        // 2. Validar peso
        $pesoValidation = Security::validatePeso($peso);
        if (!$pesoValidation['valid']) {
            return $pesoValidation['error'];
        }
        $peso = $pesoValidation['value'];

        // 3. Validar fecha
        $fechaValidation = Security::validateFecha($fecha_registro);
        if (!$fechaValidation['valid']) {
            return $fechaValidation['error'];
        }
        $fecha_registro = $fechaValidation['value'];

        // 4. Calcular IMC
        $imc = round($peso / ($altura * $altura), 2);

        // 5. Preparar la inserción
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
            error_log("Error al guardar métricas: " . $e->getMessage());
            
            if ($e->getCode() === '23000') {
                return "ID de usuario inválido.";
            }
            return "Error al guardar los datos. Inténtelo más tarde.";
        }
    }

    /**
     * Obtiene todos los registros de salud de un usuario.
     */
    public function getHealthData(int $user_id): array|string
    {
        $sql = "SELECT id, peso, altura, imc, fecha_registro 
                FROM metricas 
                WHERE user_id = :user_id 
                ORDER BY fecha_registro DESC, id DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error al obtener métricas: " . $e->getMessage());
            return "Error al obtener los datos. Inténtelo más tarde.";
        }
    }

    /**
     * Elimina un registro de salud específico.
     */
    public function deleteHealthData(int $user_id, int $data_id): bool|string
    {
        // Validar que el ID sea positivo
        if ($data_id <= 0) {
            return "ID de registro inválido.";
        }

        $sql = "DELETE FROM metricas 
                WHERE id = :data_id AND user_id = :user_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'data_id' => $data_id,
                'user_id' => $user_id
            ]);

            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                return "No se encontró el registro o no tiene permiso para borrarlo.";
            }
        } catch (\PDOException $e) {
            error_log("Error al borrar métrica: " . $e->getMessage());
            return "Error al borrar los datos. Inténtelo más tarde.";
        }
    }
}
