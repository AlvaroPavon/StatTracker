<?php
// 1. Iniciar la sesión ANTES de cualquier salida
session_start();

// 2. Refinamiento de Seguridad: Proteger la página
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit; // Detener la ejecución del script
}

// 3. Obtener el nombre del usuario (con seguridad)
$nombreUsuario = htmlspecialchars($_SESSION['user_nombre']);

// 4. REFINAMIENTO (CSRF): Obtener el token de la sesión para usarlo
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - StatTracker</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Guardamos el token en una variable global de JS
        window.csrfToken = "<?php echo $csrf_token; ?>";
    </script>
    
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f4f5f7; margin: 0; padding: 0; }
        
        .header { display: flex; justify-content: space-between; align-items: center; background: #ffffff; padding: 15px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; font-size: 1.5em; color: #333; }
        .header a { text-decoration: none; background: #dc3545; color: white; padding: 8px 12px; border-radius: 4px; font-weight: 600; }
        .header a:hover { background: #c82333; }

        .content { display: flex; flex-wrap: wrap; gap: 30px; margin-top: 20px; padding: 30px; }
        
        .form-container { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); width: 100%; max-width: 350px; box-sizing: border-box; flex-shrink: 0; }
        .form-container h2 { margin-top: 0; }

        .chart-container { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); flex-grow: 1; min-width: 300px; box-sizing: border-box; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        .form-group input { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; }
        
        .btn-green { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: 600; }
        .btn-green:hover { background-color: #218838; }

        .message.error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Bienvenido, <?php echo $nombreUsuario; ?></h1>
        <a href="logout.php?token=<?php echo $csrf_token; ?>">Cerrar Sesión</a>
    </div>

    <div class="content">
        <div class="form-container">
            <h2>Registrar Nuevo Peso</h2>
            
            <?php 
            if (isset($_GET['error'])): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="add_data.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="peso">Peso (en KG):</label>
                    <input type="number" step="0.1" id="peso" name="peso" placeholder="Ej: 70.5" required>
                </div>
                <div class="form-group">
                    <label for="altura">Altura (en Metros):</label>
                    <input type="number" step="0.01" id="altura" name="altura" placeholder="Ej: 1.75" required>
                </div>
                <div class="form-group">
                    <label for="fecha">Fecha del Registro:</label>
                    <input type="date" id="fecha" name="fecha_registro" required>
                </div>
                <button type="submit" class="btn-green">Guardar Registro</button>
            </form>
        </div>

        <div class="chart-container">
            <h2>Evolución de tu IMC</h2>
            <canvas id="imcChart"></canvas>
        </div>
    </div>

    <script>
        // Lógica para cargar y mostrar el gráfico
        document.addEventListener('DOMContentLoaded', function() {
            
            // 6. REFINAMIENTO (CSRF): Añadir el token al 'fetch'
            
            // ----- INICIO DE LA MODIFICACIÓN -----
            // Enviamos el token como un parámetro GET (en la URL)
            // Es más fiable que enviarlo por Headers en XAMPP
            fetch('get_data.php?token=' + encodeURIComponent(window.csrfToken), {
                method: 'GET',
                headers: {
                    // Ya no necesitamos enviar el token aquí
                }
            })
            // ----- FIN DE LA MODIFICACIÓN -----

                .then(response => {
                    if (!response.ok) {
                        // Si el token falla, get_data.php devolverá un error 403
                        throw new Error('Respuesta de red no fue OK: ' + response.statusText);
                    }
                    return response.json(); 
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    const labels = data.map(item => item.fecha_registro); 
                    const imcData = data.map(item => item.imc); 

                    const ctx = document.getElementById('imcChart').getContext('2d');
                    const imcChart = new Chart(ctx, {
                        type: 'line', 
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Índice de Masa Corporal (IMC)',
                                data: imcData,
                                borderColor: 'rgba(0, 123, 255, 1)',
                                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                fill: true,
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    title: { display: true, text: 'IMC' }
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error al cargar los datos del gráfico:', error);
                    const chartContainer = document.querySelector('.chart-container');
                    chartContainer.innerHTML = '<h2>Error al cargar el gráfico</h2><p>No se pudieron obtener los datos. Intente recargar la página.</p>';
                });
        });
    </script>

</body>
</html>