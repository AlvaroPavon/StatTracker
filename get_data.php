<?php
// 1. Iniciar la sesión ANTES de cualquier salida
session_start();

// 2. Incluir la conexión a la BD
require 'db.php';

// 3. Establecer el tipo de contenido de la respuesta
// Hacemos esto al principio para asegurar que el navegador siempre reciba JSON,
// incluso si hay un error.
header('Content-Type: application/json');

// 4. Refinamiento de Seguridad: Proteger la API
// Asegurarse de que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    // Si no está logueado, devolver un error 403 (Prohibido)
    http_response_code(403); 
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit; // Detener script
}

// 5. Obtener el ID del usuario de la sesión de forma segura
$user_id = $_SESSION['user_id'];

try {
    // 6. Refinamiento de Seguridad: Sentencia Preparada
    // Consultar todas las métricas DEL USUARIO LOGUEADO, ordenadas por fecha
    $sql = "SELECT fecha_registro, imc FROM metricas 
            WHERE user_id = :user_id 
            ORDER BY fecha_registro ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    
    // 7. Obtener todos los resultados
    $datos = $stmt->fetchAll();

    // 8. Devolver los datos en formato JSON
    // El 'fetch' de dashboard.php recibirá este JSON
    echo json_encode($datos);

} catch (PDOException $e) {
    // 9. Refinamiento: Manejar errores de base de datos
    // Si la consulta falla, devolver un error 500 (Error Interno del Servidor)
    http_response_code(500); 
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    exit; // Detener script
}
?>