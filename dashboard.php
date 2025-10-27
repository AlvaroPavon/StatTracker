<?php
// 1. REFINAMIENTO DE ARQUITECTURA: Incluir 'db.php' ANTES de session_start()
require 'db.php';

// 2. Iniciar la sesión
session_start();

// 3. Refinamiento de Seguridad: Proteger la página
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit; // Detener la ejecución del script
}

// 4. Obtener el ID del usuario
$user_id = $_SESSION['user_id'];

// 5. REFINAMIENTO (CSRF): Obtener el token de la sesión para usarlo
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// ----- INICIO DE MODIFICACIÓN -----
// 6. Obtener datos del usuario (nombre y foto) para la barra lateral
try {
    $stmt = $pdo->prepare("SELECT nombre, profile_pic FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $usuario = $stmt->fetch();
    
    $nombreUsuario = htmlspecialchars($usuario['nombre']);
    
    // Asignar ruta de foto de perfil
    $profilePic = 'default_profile.png'; // Imagen por defecto
    if (!empty($usuario['profile_pic']) && file_exists('uploads/' . $usuario['profile_pic'])) {
        $profilePic = 'uploads/' . $usuario['profile_pic'];
    }

} catch (PDOException $e) {
    // Si falla la consulta, usamos los datos de la sesión
    $nombreUsuario = htmlspecialchars($_SESSION['user_nombre']);
    $profilePic = 'default_profile.png'; // Imagen por defecto
}
// ----- FIN DE MODIFICACIÓN -----

?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Dashboard - StatTracker</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        window.csrfToken = "<?php echo $csrf_token; ?>";
    </script>
    
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .material-symbols-outlined.fill { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .btn-delete { padding: 4px 8px; border-radius: 4px; background-color: #fef2f2; color: #ef4444; font-weight: 500; transition: all 0.2s; }
        .btn-delete:hover { background-color: #ef4444; color: #ffffff; }
        
        /* Estilo para la foto de perfil en la barra lateral */
        .sidebar-profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #e0e6ed; /* bg-border-light */
        }
    </style>
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "primary": "#4A90E2",
              "background-light": "#F4F7FA", "background-dark": "#1F2937",
              "content-light": "#ffffff", "content-dark": "#374151",
              "text-light": "#333333", "text-dark": "#F9FAFB",
              "border-light": "#E0E6ED", "border-dark": "#4B5563",
              "subtle-light": "#F4F7FA", "subtle-dark": "#4B5563",
              "secondary-text-light": "#6B7280", "secondary-text-dark": "#D1D5DB",
            },
            fontFamily: { "display": ["Inter", "sans-serif"] },
            borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
          },
        },
      }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">
<div class="flex h-screen w-full">
<aside class="flex w-64 flex-col border-r border-border-light dark:border-border-dark bg-content-light dark:bg-content-dark">
    <div class="flex h-full flex-col justify-between p-4">
        <div class="flex flex-col gap-6">
            
            <div class="flex items-center gap-3 px-2">
                <img src="<?php echo $profilePic; ?>?v=<?php echo time(); ?>" alt="Foto de perfil" class="sidebar-profile-pic">
                <div class="flex flex-col">
                    <h1 class="text-text-light dark:text-text-dark text-base font-medium leading-normal">Bienvenido, <?php echo $nombreUsuario; ?></h1>
                </div>
            </div>
            <nav class="flex flex-col gap-2">
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary" href="dashboard.php">
                    <span class="material-symbols-outlined fill">dashboard</span>
                    <p class="text-sm font-medium leading-normal">Dashboard</p>
                </a>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-text-light dark:text-text-dark hover:bg-subtle-light dark:hover:bg-subtle-dark" href="profile.php">
                    <span class="material-symbols-outlined">person</span>
                    <p class="text-sm font-medium leading-normal">Perfil</p>
                </a>
            </nav>
            </div>
        <div class="flex flex-col gap-1 border-t border-border-light dark:border-border-dark pt-4">
            <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-text-light dark:text-text-dark hover:bg-subtle-light dark:hover:bg-subtle-dark" 
               href="logout.php?token=<?php echo $csrf_token; ?>">
                <span class="material-symbols-outlined">logout</span>
                <p class="text-sm font-medium leading-normal">Cerrar Sesión</p>
            </a>
        </div>
    </div>
</aside>
<main class="flex-1 flex-col overflow-y-auto">
    <div class="p-8">
        <header class="flex flex-wrap items-center justify-between gap-4 pb-6">
            <div class="flex min-w-72 flex-col gap-1">
                <h1 class="text-text-light dark:text-text-dark text-3xl font-bold leading-tight tracking-tight">Tu Progreso</h1>
                <p class="text-secondary-text-light dark:text-secondary-text-dark text-base font-normal leading-normal">Registra y visualiza tu IMC</p>
            </div>
        </header>
        
        <section class="flex flex-wrap gap-8 py-4">
            
            <div class="flex min-w-72 flex-1 flex-col gap-2 rounded-xl border border-border-light dark:border-border-dark bg-content-light dark:bg-content-dark p-6">
                <p class="text-text-light dark:text-text-dark text-lg font-medium leading-normal">Evolución de tu IMC</G>
                
                <div class="flex min-h-[300px] flex-1 flex-col gap-8 py-4">
                    <canvas id="imcChart"></canvas>
                </div>
            </div>

            <div class="w-full lg:w-96">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-border-light dark:border-border-dark shadow-sm">
                    <h2 class="text-xl font-bold mb-4">Registrar Nuevo Peso</h2>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg border border-red-300" role="alert">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div id="delete-success-message" 
                         class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg border border-green-300 hidden" 
                         role="alert">
                        Registro eliminado con éxito.
                    </div>

                    <form action="add_data.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <div class="grid grid-cols-1 gap-6 items-end">
                            <label class="flex flex-col w-full">
                                <p class="text-base font-medium leading-normal pb-2">Altura (en Metros)</p>
                                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-border-light dark:border-border-dark bg-background-light dark:bg-gray-700 h-12 placeholder:text-gray-400 p-3 text-base font-normal" 
                                       placeholder="Ej: 1.75" type="number" step="0.01" id="altura" name="altura" required />
                            </label>
                            <label class="flex flex-col w-full">
                                <p class="text-base font-medium leading-normal pb-2">Peso (kg)</p>
                                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-border-light dark:border-border-dark bg-background-light dark:bg-gray-700 h-12 placeholder:text-gray-400 p-3 text-base font-normal" 
                                       placeholder="Ej: 70.5" type="number" step="0.1" id="peso" name="peso" required />
                            </label>
                            <label class="flex flex-col w-full">
                                <p class="text-base font-medium leading-normal pb-2">Fecha del Registro</p>
                                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-border-light dark:border-border-dark bg-background-light dark:bg-gray-700 h-12 placeholder:text-gray-400 p-3 text-base font-normal" 
                                       type="date" id="fecha" name="fecha_registro" required />
                            </label>
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-4 mt-6 border-t border-border-light dark:border-border-dark pt-6">
                            <button class="flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-11 px-5 bg-primary text-white text-sm font-bold hover:bg-primary/90 w-full"
                                    type="submit">
                                <span class="truncate">Guardar Registro</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="mt-8 rounded-xl border border-border-light dark:border-border-dark bg-content-light dark:bg-content-dark">
            <h2 class="text-text-light dark:text-text-dark text-xl font-bold p-6">Registros de Peso</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="border-b border-t border-border-light dark:border-border-dark bg-subtle-light dark:bg-content-dark">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-secondary-text-light dark:text-secondary-text-dark">Fecha</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-secondary-text-light dark:text-secondary-text-dark">Peso</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-secondary-text-light dark:text-secondary-text-dark">Altura</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-secondary-text-light dark:text-secondary-text-dark">IMC</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-secondary-text-light dark:text-secondary-text-dark">Clasificación</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-secondary-text-light dark:text-secondary-text-dark">Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="historial-tabla-body" class="divide-y divide-border-light dark:divide-border-dark">
                        </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const chartContainer = document.getElementById('imcChart').parentNode;
        const tablaBody = document.getElementById('historial-tabla-body');
        const deleteSuccessMessage = document.getElementById('delete-success-message');
        let imcChartInstance = null; 

        function getClasificacionIMC(imc) {
            if (imc < 18.5) return { texto: 'Bajo Peso', color: 'text-blue-600' };
            if (imc < 25) return { texto: 'Peso Normal', color: 'text-green-600' };
            if (imc < 30) return { texto: 'Sobrepeso', color: 'text-yellow-600' };
            if (imc < 35) return { texto: 'Obesidad I', color: 'text-orange-600' };
            if (imc < 40) return { texto: 'Obesidad II', color: 'text-red-600' };
            return { texto: 'Obesidad III', color: 'text-red-800' };
        }

        function cargarDatos() {
            tablaBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Cargando historial...</td></tr>';
            
            fetch('get_data.php?token=' + encodeURIComponent(window.csrfToken))
                .then(response => {
                    if (!response.ok) throw new Error('Respuesta de red no fue OK');
                    return response.json(); 
                })
                .then(data => {
                    if (data.error) throw new Error(data.error);
                    
                    if (imcChartInstance) {
                        imcChartInstance.destroy();
                    }
                    
                    if (data.length === 0) {
                        chartContainer.innerHTML = '<p class="text-center text-gray-500">No hay datos registrados todavía.</p>';
                        tablaBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay registros.</td></tr>';
                        return;
                    }

                    // --- 1. LÓGICA DEL GRÁFICO (Datos ASC) ---
                    const reversedData = [...data].reverse();
                    const labels = reversedData.map(item => item.fecha_registro); 
                    const imcData = reversedData.map(item => item.imc); 

                    if (!document.getElementById('imcChart')) {
                         chartContainer.innerHTML = '<canvas id="imcChart"></canvas>';
                    }
                    const ctx = document.getElementById('imcChart').getContext('2d');
                    
                    imcChartInstance = new Chart(ctx, {
                        type: 'line', 
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Índice de Masa Corporal (IMC)',
                                data: imcData,
                                borderColor: 'rgba(74, 144, 226, 1)',
                                backgroundColor: 'rgba(74, 144, 226, 0.1)',
                                fill: true,
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { y: { beginAtZero: false, title: { display: true, text: 'IMC' } } }
                        }
                    });

                    // --- 2. LÓGICA DE LA TABLA (Datos DESC) ---
                    let tableHtml = '';
                    data.forEach(item => {
                        const clasificacion = getClasificacionIMC(item.imc);
                        
                        tableHtml += `
                            <tr class="hover:bg-subtle-light dark:hover:bg-subtle-dark">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-text-light dark:text-text-dark">${item.fecha_registro}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-text-light dark:text-secondary-text-dark">${item.peso} kg</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-text-light dark:text-secondary-text-dark">${item.altura} m</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold ${clasificacion.color}">${item.imc}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${clasificacion.color}">${clasificacion.texto}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button class="btn-delete" data-id="${item.id}" type="button">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    tablaBody.innerHTML = tableHtml;

                })
                .catch(error => {
                    console.error('Error al cargar los datos:', error);
                    chartContainer.innerHTML = '<h2>Error al cargar el gráfico</h2><p>No se pudieron obtener los datos.</p>';
                    tablaBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error al cargar los registros.</td></tr>';
                });
        }

        tablaBody.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete')) {
                const button = e.target;
                const metricId = button.getAttribute('data-id');
                
                if (confirm('¿Estás seguro de que quieres eliminar este registro? Esta acción no se puede deshacer.')) {
                    
                    const url = `delete_data.php?id=${metricId}&token=${encodeURIComponent(window.csrfToken)}`;
                    
                    fetch(url, {
                        method: 'GET'
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            cargarDatos();
                            deleteSuccessMessage.classList.remove('hidden');
                            setTimeout(() => {
                                deleteSuccessMessage.classList.add('hidden');
                            }, 3000);
                        } else {
                            alert('Error al eliminar: ' + (result.error || 'Error desconocido'));
                        }
                    })
                    .catch(err => {
                        console.error('Error en fetch delete:', err);
                        alert('Error de conexión al intentar eliminar.');
                    });
                }
            }
        });

        cargarDatos();
    });
</script>

</body>
</html>