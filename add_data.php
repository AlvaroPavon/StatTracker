<?php
// 1. REFINAMIENTO DE ARQUITECTURA: Incluir 'db.php' ANTES de session_start()
require 'db.php';

// 2. REFINAMIENTO (CSRF): Iniciar la sesión
session_start();

// 3. Refinamiento de Seguridad: Proteger el script
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// 4. Verificar que la solicitud sea por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 5. REFINAMIENTO (CSRF): Validar el token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // Si el token no coincide, es un ataque CSRF o una sesión expirada
        header('Location: dashboard.php?error=Error de seguridad. Intente de nuevo.');
        exit;
    }
    
    // 6. Obtener el ID del usuario de la sesión
    $user_id = $_SESSION['user_id'];
    
    // 7. Obtener y validar datos del formulario
    $peso = $_POST['peso'];
    $altura = $_POST['altura'];
    $fecha_registro = $_POST['fecha_registro'];

    // 8. Refinamiento de Validación: Comprobar que no estén vacíos
    if (empty($peso) || empty($altura) || empty($fecha_registro)) {
        header('Location: dashboard.php?error=Todos los campos son obligatorios.');
        exit;
    }

    // 9. Convertir a números flotantes (decimales)
    $peso_num = floatval($peso);
    $altura_num = floatval($altura);

    // 10. Refinamiento de Validación Lógica (Peso y Altura):
    if ($peso_num <= 0 || $altura_num <= 0) {
        header('Location: dashboard.php?error=El peso y la altura deben ser valores positivos.');
        exit;
    }

    // 11. REFINAMIENTO: Validación de Fecha
    try {
        $fecha_obj = new DateTime($fecha_registro);
        $hoy = new DateTime(); 
        
        if ($fecha_obj > $hoy) {
            header('Location: dashboard.php?error=La fecha de registro no puede ser en el futuro.');
            exit;
        }
    } catch (Exception $e) {
        header('Location: dashboard.php?error=El formato de la fecha no es válido.');
        exit;
    }


    // 12. Lógica de Negocio: Calcular el IMC
    $imc = $peso_num / ($altura_num * $altura_num);
    $imc_redondeado = round($imc, 2);

    try {
        // 13. Refinamiento de Seguridad: Sentencia Preparada
        $sql = "INSERT INTO metricas (user_id, peso, altura, imc, fecha_registro) 
                VALUES (:user_id, :peso, :altura, :imc, :fecha_registro)";
        
        $stmt = $pdo->prepare($sql);
        
        // 14. Ejecutar la inserción
        $stmt->execute([
            'user_id' => $user_id,
            'peso' => $peso_num,
            'altura' => $altura_num,
            'imc' => $imc_redondeado,
            'fecha_registro' => $fecha_registro
        ]);

        // 15. Redirección exitosa: Devolver al dashboard
        header('Location: dashboard.php');
        exit;

    } catch (PDOException $e) {
        // 16. REFINAMIENTO: Manejo de Errores de Producción
        error_log('Error en add_data.php: ' . $e->getMessage());
        header('Location: dashboard.php?error=Error al guardar los datos. Inténtelo de nuevo.');
        exit;
    }
} else {
    // 17. Si alguien intenta acceder a add_data.php directamente (GET)
    header('Location: dashboard.php');
    exit;
}
?>