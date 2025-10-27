<?php
// 1. Iniciar la sesión ANTES de cualquier salida
session_start();

// 2. Incluir la conexión a la BD
require 'db.php';

// 3. Establecer el tipo de contenido de la respuesta
header('Content-Type: application/json');

// 4. Refinamiento de Seguridad: Proteger la API (Autenticación)
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit; // Detener script
}

// 5. REFINAMIENTO (CSRF): Validar el token enviado por 'fetch'

// --- INICIO DE LA CORRECCIÓN ROBUSTA ---
$token_enviado = '';

// Método 1: getallheaders() (El mejor, si está disponible)
// CORRECCIÓN: Comprobar si la función existe antes de llamarla
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    if (isset($headers['X-CSRF-TOKEN'])) {
        $token_enviado = $headers['X-CSRF-TOKEN'];
    } elseif (isset($headers['x-csrf-token'])) {
        $token_enviado = $headers['x-csrf-token'];
    }
}
// Método 2: $_SERVER (Si getallheaders no funciona o no existe)
if (empty($token_enviado) && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    $token_enviado = $_SERVER['HTTP_X_CSRF_TOKEN'];
}
// --- FIN DE LA CORRECCIÓN ROBUSTA ---


// Validar el token
if (empty($token_enviado) || !hash_equals($_SESSION['csrf_token'], $token_enviado)) {
    // Si el token no coincide, es un ataque CSRF
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Error de seguridad (Token CSRF inválido)']);
    exit;
}


// 6. Obtener el ID del usuario de la sesión de forma segura
$user_id = $_SESSION['user_id'];

try {
    // 7. Refinamiento de Seguridad: Sentencia Preparada
    $sql = "SELECT fecha_registro, imc FROM metricas 
            WHERE user_id = :user_id 
            ORDER BY fecha_registro ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    
    // 8. Obtener todos los resultados
    $datos = $stmt->fetchAll();

    // 9. Devolver los datos en formato JSON
    echo json_encode($datos);

} catch (PDOException $e) {
    // 10. REFINAMIENTO: Manejo de Errores de Producción
    error_log('Error en get_data.php: ' . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Error interno al obtener los datos.']);
    exit; // Detener script
}
?>