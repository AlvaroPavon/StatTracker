<?php
// 1. Iniciar la sesión ANTES de cualquier salida
session_start();

// 2. Incluir la conexión a la BD
require 'db.php';

// 3. Refinamiento de Seguridad: Proteger el script
// Solo usuarios logueados pueden añadir datos
if (!isset($_SESSION['user_id'])) {
    // Si no está logueado, no le damos acceso
    header('Location: index.php');
    exit;
}

// 4. Verificar que la solicitud sea por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 5. Obtener el ID del usuario de la sesión
    $user_id = $_SESSION['user_id'];
    
    // 6. Obtener y validar datos del formulario
    $peso = $_POST['peso'];
    $altura = $_POST['altura'];
    $fecha_registro = $_POST['fecha_registro'];

    // 7. Refinamiento de Validación: Comprobar que no estén vacíos
    if (empty($peso) || empty($altura) || empty($fecha_registro)) {
        header('Location: dashboard.php?error=Todos los campos son obligatorios.');
        exit;
    }

    // 8. Convertir a números flotantes (decimales)
    $peso_num = floatval($peso);
    $altura_num = floatval($altura);

    // 9. Refinamiento de Validación Lógica:
    // No se puede calcular el IMC si la altura o el peso es 0 o negativo
    if ($peso_num <= 0 || $altura_num <= 0) {
        header('Location: dashboard.php?error=El peso y la altura deben ser valores positivos.');
        exit;
    }

    // 10. Lógica de Negocio: Calcular el IMC
    // Fórmula: peso / (altura * altura)
    $imc = $peso_num / ($altura_num * $altura_num);
    // Redondear a 2 decimales
    $imc_redondeado = round($imc, 2);

    try {
        // 11. Refinamiento de Seguridad: Sentencia Preparada (Previene Inyección SQL)
        $sql = "INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) 
                VALUES (:user_id, :peso, :altura, :imc, :fecha_registro)";
        
        $stmt = $pdo->prepare($sql);
        
        // 12. Ejecutar la inserción
        $stmt->execute([
            'user_id' => $user_id,
            'peso' => $peso_num,
            'altura' => $altura_num,
            'imc' => $imc_redondeado,
            'fecha_registro' => $fecha_registro
        ]);

        // 13. Redirección exitosa: Devolver al dashboard
        // El dashboard se recargará y el script del gráfico (fetch) obtendrá los nuevos datos
        header('Location: dashboard.php');
        exit;

    } catch (PDOException $e) {
        // 14. Manejar errores de base de datos
        header('Location: dashboard.php?error=Error al guardar los datos. Inténtelo de nuevo.');
        exit;
    }
} else {
    // 15. Si alguien intenta acceder a add_data.php directamente (GET)
    header('Location: dashboard.php');
    exit;
}
?>