<?php
declare(strict_types=1);

// CORRECCIÓN: Se envuelve la declaración de la clase para evitar el error "Cannot declare class Metrics" 
// que ocurre por la doble carga (autoloader de PHPUnit y inclusión manual en el script de la API).
if (!class_exists('Metrics')) {
    class Metrics
    {
        private $pdo;

        public function __construct(PDO $pdo)
        {
            $this->pdo = $pdo;
        }

        public function addHealthData(int $userId, float $weight, float $height, string $date): bool
        {
            if ($weight <= 0 || $height <= 0) {
                return false;
            }

            try {
                $sql = "INSERT INTO health_data (user_id, weight, height, date) VALUES (?, ?, ?, ?)";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute([$userId, $weight, $height, $date]);
            } catch (PDOException $e) {
                // Manejo de errores de base de datos
                return false;
            }
        }

        public function calculateHealthData(float $weight, float $height): array
        {
            if ($height <= 0) {
                return ['bmi' => 0.0];
            }

            // Formula IMC: peso / (altura * altura)
            $bmi = $weight / ($height * $height);

            // CORRECCIÓN F1 (anterior): Redondear la IMC a dos decimales para que coincida con el valor esperado en los tests.
            $roundedBmi = round($bmi, 2);

            return [
                'bmi' => $roundedBmi, // Usar el valor redondeado
            ];
        }
        
        public function getHealthData(int $userId, string $metric, int $limit = 1): array
        {
            if (!in_array($metric, ['weight', 'height', 'bmi', 'date'])) {
                return [];
            }

            // Para IMC (BMI) necesitarás tanto weight como height. Si no se pide bmi, se puede simplificar
            // Pero por ahora, devolveremos los datos crudos o calcularemos la IMC al vuelo
            
            $sql = "SELECT * FROM health_data WHERE user_id = ? ORDER BY date DESC LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $data = [];
            foreach ($results as $row) {
                $row['bmi'] = $this->calculateHealthData((float)$row['weight'], (float)$row['height'])['bmi'];
                $data[] = $row;
            }

            return $data;
        }
    }
}