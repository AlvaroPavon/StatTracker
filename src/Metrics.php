<?php namespace App;

use PDO;
// use PDOException; // <-- Eliminamos esta línea
use DateTime;

class Metrics {
    
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todas las métricas de un usuario, ordenadas por fecha.
     */
    public function getMetricsForUser(int $userId): array {
        $sql = "SELECT id, fecha_registro, imc, peso, altura FROM metricas 
                WHERE user_id = :user_id 
                ORDER BY fecha_registro DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Añade un nuevo registro de métrica para un usuario.
     * El cálculo de IMC se hace aquí.
     */
    public function addMetric(int $userId, float $peso, float $altura, string $fechaRegistro): bool {
        // Lógica de negocio (cálculo de IMC)
        $imc = $peso / ($altura * $altura);
        $imcRedondeado = round($imc, 2);

        $sql = "INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) 
                VALUES (:user_id, :peso, :altura, :imc, :fecha_registro)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            'user_id' => $userId,
            'peso' => $peso,
            'altura' => $altura,
            'imc' => $imcRedondeado,
            'fecha_registro' => $fechaRegistro
        ]);
    }

    /**
     * Elimina una métrica específica, asegurándose de que pertenezca al usuario.
     */
    public function deleteMetric(int $metricId, int $userId): bool {
        // CRÍTICO: Comprobar el user_id previene que un usuario borre
        // métricas de otro adivinando el ID.
        $sql = "DELETE FROM metricas WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $metricId,
            'user_id' => $userId
        ]);

        // rowCount() > 0 significa que algo se borró.
        return $stmt->rowCount() > 0;
    }
}