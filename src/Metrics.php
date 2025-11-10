<?php

namespace App;

use PDO;
use PDOException;

/**
 * Clase Metrics para manejar la lógica de datos de salud (peso, altura, etc.)
 * VERSIÓN CORREGIDA para la BD 'metricas'
 */
class Metrics
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Añade un nuevo registro de salud para un usuario.
     *
     * @param int $userId
     * @param float $peso
     * @param float $altura
     * @param float $imc
     * @return int|string Devuelve el ID del nuevo registro si tiene éxito, o un string con el mensaje de error si falla.
     */
    public function addHealthData(int $userId, float $peso, float $altura, float $imc): int|string
    {
        // 1. Validación simple
        if ($userId <= 0) {
             return "ID de usuario inválido.";
        }
        if ($peso <= 0 || $altura <= 0 || $imc <= 0) {
            return "Los valores de peso, altura e IMC deben ser positivos.";
        }
        
        // 2. Obtener la fecha actual (formato YYYY-MM-DD para la BD)
        $fecha_registro = date('Y-m-d');

        try {
            // 3. CORRECCIÓN: Insertar en la tabla 'metricas'
            $stmt = $this->pdo->prepare(
                "INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) VALUES (?, ?, ?, ?, ?)"
            );
            
            // 4. Ejecutamos la inserción
            $stmt->execute([$userId, $peso, $altura, $imc, $fecha_registro]);

            // 5. Devolvemos el ID
            return (int)$this->pdo->lastInsertId();

        } catch (PDOException $e) {
            return "Error al guardar los datos: " . $e->getMessage();
        }
    }

    /**
     * Obtiene todos los registros de salud para un usuario.
     *
     * @param int $userId
     * @return array|string Devuelve un array de datos si tiene éxito, o un string con el mensaje de error si falla.
     */
    public function getHealthData(int $userId): array|string
    {
        if ($userId <= 0) {
            return "ID de usuario inválido.";
        }

        try {
            // 2. CORRECCIÓN: Seleccionar de 'metricas' y usar 'fecha_registro'
            $stmt = $this->pdo->prepare(
                "SELECT id, peso, altura, imc, fecha_registro FROM metricas WHERE user_id = ? ORDER BY fecha_registro ASC"
            );
            $stmt->execute([$userId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            return "Error al obtener los datos: " . $e->getMessage();
        }
    }

    /**
     * Borra un registro de salud específico.
     *
     * @param int $userId El ID del usuario (desde la sesión).
     * @param int $dataId El ID del registro (metricas.id) a borrar.
     * @return bool|string Devuelve true si tiene éxito, o un string con el mensaje de error si falla.
     */
    public function deleteHealthData(int $userId, int $dataId): bool|string
    {
        if ($userId <= 0 || $dataId <= 0) {
            return "ID de usuario o ID de registro inválido.";
        }

        try {
            // 2. CORRECCIÓN: Borrar de 'metricas'
            $stmt = $this->pdo->prepare(
                "DELETE FROM metricas WHERE id = ? AND user_id = ?"
            );
            $stmt->execute([$dataId, $userId]);

            // 3. Verificar si el borrado fue exitoso
            if ($stmt->rowCount() > 0) {
                return true; // Borrado exitoso
            } else {
                return "No se encontró el registro o no tiene permiso para borrarlo.";
            }

        } catch (PDOException $e) {
            return "Error al borrar los datos: " . $e->getMessage();
        }
    }
}