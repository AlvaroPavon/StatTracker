<?php
/**
 * Controlador de Métricas
 * Maneja CRUD de métricas de salud
 */

require_once __DIR__ . '/../middleware/JWTMiddleware.php';
require_once __DIR__ . '/../../database_connection.php';

class MetricsController {
    private $db;
    private $user;

    public function __construct() {
        global $pdo;
        $this->db = $pdo;
        $this->user = JWTMiddleware::verify();
        
        if ($this->user === null) {
            exit;
        }
    }

    /**
     * Listar todas las métricas del usuario
     * GET /api/metrics
     */
    public function index(): void {
        $stmt = $this->db->prepare(
            'SELECT id, peso, altura, imc, fecha_registro, created_at 
             FROM metricas 
             WHERE user_id = ? 
             ORDER BY fecha_registro DESC, created_at DESC'
        );
        $stmt->execute([$this->user['user_id']]);
        $metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'count' => count($metrics),
            'metrics' => $metrics
        ]);
    }

    /**
     * Obtener una métrica específica
     * GET /api/metrics/:id
     */
    public function show($id): void {
        $stmt = $this->db->prepare(
            'SELECT id, peso, altura, imc, fecha_registro, created_at 
             FROM metricas 
             WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$id, $this->user['user_id']]);
        $metric = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$metric) {
            http_response_code(404);
            echo json_encode(['error' => 'Métrica no encontrada']);
            return;
        }

        echo json_encode([
            'success' => true,
            'metric' => $metric
        ]);
    }

    /**
     * Crear nueva métrica
     * POST /api/metrics
     */
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        $peso = filter_var($data['peso'] ?? '', FILTER_VALIDATE_FLOAT);
        $altura = filter_var($data['altura'] ?? '', FILTER_VALIDATE_FLOAT);
        $fecha = $data['fecha_registro'] ?? date('Y-m-d');

        if ($peso === false || $altura === false || $altura <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Peso y altura válidos son requeridos']);
            return;
        }

        // Calcular IMC
        $imc = round($peso / ($altura * $altura), 2);

        $stmt = $this->db->prepare(
            'INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) 
             VALUES (?, ?, ?, ?, ?)'
        );

        try {
            $stmt->execute([$this->user['user_id'], $peso, $altura, $imc, $fecha]);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Métrica creada correctamente',
                'metric' => [
                    'id' => $this->db->lastInsertId(),
                    'peso' => $peso,
                    'altura' => $altura,
                    'imc' => $imc,
                    'fecha_registro' => $fecha
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear la métrica']);
        }
    }

    /**
     * Actualizar métrica existente
     * PUT /api/metrics/:id
     */
    public function update($id): void {
        $data = json_decode(file_get_contents('php://input'), true);

        $peso = filter_var($data['peso'] ?? '', FILTER_VALIDATE_FLOAT);
        $altura = filter_var($data['altura'] ?? '', FILTER_VALIDATE_FLOAT);
        $fecha = $data['fecha_registro'] ?? date('Y-m-d');

        if ($peso === false || $altura === false || $altura <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Peso y altura válidos son requeridos']);
            return;
        }

        // Verificar que la métrica pertenece al usuario
        $stmt = $this->db->prepare('SELECT id FROM metricas WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $this->user['user_id']]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Métrica no encontrada']);
            return;
        }

        $imc = round($peso / ($altura * $altura), 2);

        $stmt = $this->db->prepare(
            'UPDATE metricas 
             SET peso = ?, altura = ?, imc = ?, fecha_registro = ? 
             WHERE id = ? AND user_id = ?'
        );

        try {
            $stmt->execute([$peso, $altura, $imc, $fecha, $id, $this->user['user_id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Métrica actualizada correctamente',
                'metric' => [
                    'id' => $id,
                    'peso' => $peso,
                    'altura' => $altura,
                    'imc' => $imc,
                    'fecha_registro' => $fecha
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar la métrica']);
        }
    }

    /**
     * Eliminar métrica
     * DELETE /api/metrics/:id
     */
    public function destroy($id): void {
        $stmt = $this->db->prepare('DELETE FROM metricas WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $this->user['user_id']]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Métrica no encontrada']);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Métrica eliminada correctamente'
        ]);
    }
}
