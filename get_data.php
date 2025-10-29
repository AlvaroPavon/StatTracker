<?php
// 1. REFINAMIENTO DE ARQUITECTURA:
// Incluir la conexión (que tiene los 'ini_set') ANTES de iniciar la sesión.
require 'db.php';

// 2. Iniciar la sesión DESPUÉS de cargar la configuración.
session_start();

// 3. Establecer el tipo de contenido de la respuesta
header('Content-Type: application/json');

// 4. Refinamiento de Seguridad: Proteger la API (Autenticación)
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit; // Detener script
}

// 5. REFINAMIENTO (CSRF): Validar el token enviado por 'fetch'
$token_enviado = '';
if (isset($_GET['token'])) {
    $token_enviado = $_GET['token'];
}

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
    
    // ----- INICIO DE LA MODIFICACIÓN -----
    // Ahora seleccionamos también el 'id' de la métrica
    $sql = "SELECT id, fecha_registro, imc, peso, altura FROM metricas 
            WHERE user_id = :user_id 
            ORDER BY fecha_registro DESC";
    // ----- FIN DE LA MODIFICACIÓN -----
            
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