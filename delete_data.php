<?php
// 1. REFINAMIENTO DE ARQUITECTURA: Incluir 'db.php' ANTES de session_start()
require 'db.php';

// 2. Iniciar la sesión
session_start();

// 3. Establecer el tipo de contenido de la respuesta
header('Content-Type: application/json');

// 4. Refinamiento de Seguridad: Autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

// 5. Verificar que el método sea DELETE (o POST si se prefiere, pero DELETE es semántico)
// Por simplicidad de fetch, aceptaremos GET pero validaremos todo.
// Usaremos la misma validación de token que get_data.php
$token_enviado = '';
if (isset($_GET['token'])) {
    $token_enviado = $_GET['token'];
}

if (empty($token_enviado) || !hash_equals($_SESSION['csrf_token'], $token_enviado)) {
    http_response_code(403);
    echo json_encode(['error' => 'Error de seguridad (Token CSRF inválido)']);
    exit;
}

// 6. Obtener el ID del registro a eliminar
if (!isset($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'No se especificó el ID del registro']);
    exit;
}

$metric_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // 7. Sentencia Preparada para eliminar
    // CRÍTICO: Nos aseguramos de que el 'user_id' coincida.
    // Esto previene que un usuario borre registros de otro usuario.
    $sql = "DELETE FROM metricas WHERE id = :id AND user_id = :user_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'id' => $metric_id,
        'user_id' => $user_id
    ]);

    // 8. Verificar si se eliminó algo
    if ($stmt->rowCount() > 0) {
        // Éxito
        echo json_encode(['success' => true, 'message' => 'Registro eliminado']);
    } else {
        // No se eliminó nada (probablemente el ID no existía o no pertenecía al usuario)
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'El registro no se encontró o no tiene permiso para eliminarlo']);
    }

} catch (PDOException $e) {
    // 9. Manejo de Errores de Producción
    error_log('Error en delete_data.php: ' . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Error interno al eliminar el registro.']);
    exit;
}
?>