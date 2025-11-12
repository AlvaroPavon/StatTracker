<?php
// 1. REFINAMIENTO DE ARQUITECTURA: Incluir 'db.php'
require 'db.php'; //

// 2. MODIFICACIÓN (BUG CSRF): Incluir 'session_config.php' ANTES de session_start()
require __DIR__ . '/session_config.php'; //

// 3. Iniciar la sesión
session_start(); //

// 4. Refinamiento de Seguridad: Proteger la página
if (!isset($_SESSION['user_id'])) { //
    header('Location: index.php'); //
    exit; // Detener la ejecución del script //
}

// 5. Obtener el ID del usuario
$user_id = $_SESSION['user_id']; //

// 6. REFINAMIENTO (CSRF): Obtener el token de la sesión para usarlo
if (empty($_SESSION['csrf_token'])) { //
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); //
}
$csrf_token = $_SESSION['csrf_token']; //

// 7. Comprobar si debemos mostrar el splash de bienvenida
$showSplash = false; //
if (isset($_SESSION['show_welcome_splash']) && $_SESSION['show_welcome_splash'] === true) { //
    $showSplash = true; //
    unset($_SESSION['show_welcome_splash']); //
}

// 8. Obtener datos del usuario (nombre y foto) para la barra lateral
$nombreUsuario = ''; // Default empty
$profilePic = null; // Default null
$userHasProfilePic = false; // Flag for checking if user has a pic

try {
    $stmt = $pdo->prepare("SELECT nombre, profile_pic FROM usuarios WHERE id = :id"); //
    $stmt->execute(['id' => $user_id]); //
    $usuario = $stmt->fetch(); //

    if ($usuario) {
        $nombreUsuario = htmlspecialchars($usuario['nombre']); //

        if (!empty($usuario['profile_pic']) && file_exists('uploads/' . $usuario['profile_pic'])) { //
            $profilePic = 'uploads/' . $usuario['profile_pic']; //
            $userHasProfilePic = true; // Set flag to true
        }
    } else {
         // Fallback if user data couldn't be fetched (should ideally not happen)
         $nombreUsuario = htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario');
    }


} catch (PDOException $e) {
    // Fallback if DB query fails
    $nombreUsuario = htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario'); //
}

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

    <link 
        rel="stylesheet" 
        href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    />
    
    <!-- Liquid Glass Effect CSS -->
    <link rel="stylesheet" href="css/liquid-glass.css"/>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        window.csrfToken = "<?php echo $csrf_token; ?>";
    </script>

    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; } /* */
        .material-symbols-outlined.fill { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; } /* */
        .btn-delete { padding: 4px 8px; border-radius: 4px; background-color: #fef2f2; color: #ef4444; font-weight: 500; transition: all 0.3s; } /* */
        .btn-delete:hover { background-color: #ef4444; color: #ffffff; } /* */

        .sidebar-profile-pic {
            width: 40px; /* */
            height: 40px; /* */
            border-radius: 50%; /* */
            object-fit: cover; /* */
            /* MODIFICADO: Fondo de icono de perfil transparente */
            background-color: rgba(255, 255, 255, 0.2); /* */
            display: inline-flex; /* */
            align-items: center; /* */
            justify-content: center; /* */
            color: #1F2937; /* */
        }
        .dark .sidebar-profile-pic {
            background-color: rgba(0, 0, 0, 0.2);
            color: #E5E7EB;
        }

         .sidebar-profile-pic img {
             width: 100%; /* */
             height: 100%; /* */
             border-radius: 50%; /* */
             object-fit: cover; /* */
         }

        :root {
            --animate-duration: 0.8s; /* */
        }

        /* ----- INICIO MODIFICACIÓN (Estilos Splash Minimalista) ----- */
        #welcome-splash {
            position: fixed; /* */
            inset: 0; /* */
            z-index: 9999; /* */
            display: flex; /* */
            align-items: center; /* */
            justify-content: center; /* */
            /* MODIFICADO: Se quita el fondo sólido, se aplicará por Tailwind */
            overflow: hidden; /* */
        }

        /* Ajustamos el texto */
        #welcome-splash h1 {
            position: relative; /* */
            z-index: 2; /* */
             /* MODIFICADO: Color de texto */
            color: #111827; /* text-gray-900 */
            font-size: 3rem; /* Tamaño ligeramente más pequeño */
            line-height: 1.1; /* */
        }
         /* Color texto modo oscuro */
        .dark #welcome-splash h1 {
             color: #F9FAFB; /* text-dark */
        }

        @media (min-width: 1024px) { /* */
             #welcome-splash h1 {
                 font-size: 4.5rem; /* */
             }
        }
        /* ----- FIN MODIFICACIÓN ----- */


        #welcome-splash .animate__fadeIn {
             --animate-duration: 1.2s; /* */
        }
        #welcome-splash.animate__fadeOut {
             --animate-duration: 0.8s; /* */
        }

        /* ----- INICIO MODIFICACIÓN (Estilo "Gota de Agua") ----- */
        .glass-card {
            /* Fondo casi transparente */
            background-color: rgba(255, 255, 255, 0.1);
            
            /* El efecto de desenfoque (muy intenso) */
            -webkit-backdrop-filter: blur(35px); /* Safari */
            backdrop-filter: blur(35px);
            
            /* Borde brillante muy sutil */
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .dark .glass-card {
            /* Fondo oscuro casi transparente */
            background-color: rgba(31, 41, 55, 0.1); /* Tono oscuro semitransparente */
            
            /* Borde oscuro más sutil */
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        /* ----- FIN MODIFICACIÓN ----- */

    </style>
    <script id="tailwind-config">
       tailwind.config = {
         darkMode: "class",
         theme: {
          extend: {
            colors: {
              "primary": "#4A90E2",
              /* MODIFICADO: Se quitan los colores de fondo y contenido, ya que ahora usamos el gradiente y el 'glass' */
              "text-light": "#333333", "text-dark": "#F9FAFB",
              "border-light": "#E0E6ED", "border-dark": "#4B5563",
              "subtle-light": "rgba(255, 255, 255, 0.1)", "subtle-dark": "rgba(0, 0, 0, 0.1)",
              "secondary-text-light": "#374151", "secondary-text-dark": "#D1D5DB",
            },
            fontFamily: { "display": ["Inter", "sans-serif"] },
            borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
          },
        }, //
       }
    </script>
</head>
<body class="font-display text-gray-900 dark:text-gray-100 bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-slate-900 dark:to-gray-800">

<?php if ($showSplash): ?>
    <div id="welcome-splash" class="animate__animated animate__fadeIn bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-slate-900 dark:to-gray-800">
        <h1 class="animate__animated">
            Bienvenido, <?php echo $nombreUsuario; ?>
        </h1>
    </div>
<?php endif; ?>

<div id="dashboard-content" class="flex h-screen w-full hidden">
<aside class="flex w-64 flex-col glass-sidebar liquid-wave-effect m-4 rounded-xl">
   <div class="flex h-full flex-col justify-between p-4">
        <div class="flex flex-col gap-6">
            <div class="flex items-center gap-3 px-2">
                 <div class="sidebar-profile-pic">
                    <?php if ($userHasProfilePic): ?>
                        <img src="<?php echo $profilePic; ?>?v=<?php echo time(); ?>" alt="Foto de perfil">
                    <?php else: ?>
                        <span class="material-symbols-outlined !text-3xl">person</span>
                    <?php endif; ?>
                 </div>
                <div class="flex flex-col">
                    <h1 class="text-gray-900 dark:text-gray-100 text-base font-medium leading-normal">Bienvenido, <?php echo $nombreUsuario; ?></h1>
                </div>
            </div>
            <nav class="flex flex-col gap-2">
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary transition-all duration-300" href="dashboard.php">
                    <span class="material-symbols-outlined fill">dashboard</span>
                    <p class="text-sm font-medium leading-normal">Dashboard</p>
                </a>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-900 dark:text-gray-100 hover:bg-subtle-light dark:hover:bg-subtle-dark transition-all duration-300" href="profile.php">
                    <span class="material-symbols-outlined">person</span>
                    <p class="text-sm font-medium leading-normal">Perfil</p>
                </a>
            </nav>
        </div>
        <div class="flex flex-col gap-1 border-t border-white/20 dark:border-white/10 pt-4">
            <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-900 dark:text-gray-100 hover:bg-subtle-light dark:hover:bg-subtle-dark transition-all duration-300"
               href="logout.php?token=<?php echo $csrf_token; ?>">
                <span class="material-symbols-outlined">logout</span>
                <p class="text-sm font-medium leading-normal">Cerrar Sesión</p>
            </a>
        </div>
    </div>
</aside>
<main class="flex-1 flex-col overflow-y-auto">
   <div class="p-4 md:p-8">
        <header class="flex flex-wrap items-center justify-between gap-4 pb-6
                       animate__animated animate__fadeInDown">
            <div class="flex min-w-72 flex-col gap-1">
                <h1 class="text-gray-900 dark:text-gray-100 text-3xl font-bold leading-tight tracking-tight">Tu Progreso</h1>
                <p class="text-gray-700 dark:text-gray-300 text-base font-normal leading-normal">Registra y visualiza tu IMC</p>
            </div>
        </header>

        <section class="flex flex-wrap gap-8 py-4
                        animate__animated animate__fadeInUp">

            <div class="flex min-w-72 flex-1 flex-col gap-2 rounded-xl p-6
                        transition-all duration-300 liquid-glass glass-reflect">
                <p class="text-gray-900 dark:text-gray-100 text-lg font-medium leading-normal">Evolución de tu IMC</G>

                <div class="flex min-h-[300px] flex-1 flex-col gap-8 py-4">
                    <canvas id="imcChart"></canvas>
                </div>
            </div>

            <div class="w-full lg:w-96 transition-all duration-300 rounded-xl">
                <div class="p-6 rounded-xl shadow-sm h-full liquid-glass-strong water-drop-effect">
                    <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">Registrar Nuevo Peso</h2>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="mb-4 p-4 text-sm text-red-900 dark:text-red-100 bg-red-500/20 rounded-lg border border-red-500/30" role="alert">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>

                    <div id="delete-success-message"
                         class="mb-4 p-4 text-sm text-green-900 dark:text-green-100 bg-green-500/20 rounded-lg border border-green-500/30 hidden"
                         role="alert">
                        Registro eliminado con éxito.
                    </div>

                    <form action="add_data.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <div class="grid grid-cols-1 gap-6 items-end">
                           <label class="flex flex-col w-full">
                                <p class="text-base font-medium leading-normal pb-2">Altura (en Metros)</p>
                                <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-600 dark:placeholder:text-gray-400 p-3 text-base font-normal
                                       transition-all duration-300"
                                       placeholder="Ej: 1.75" type="number" step="0.01" id="altura" name="altura" required />
                            </label>
                            <label class="flex flex-col w-full">
                                <p class="text-base font-medium leading-normal pb-2">Peso (kg)</p>
                                <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-600 dark:placeholder:text-gray-400 p-3 text-base font-normal
                                       transition-all duration-300"
                                       placeholder="Ej: 70.5" type="number" step="0.1" id="peso" name="peso" required />
                            </label>
                            <label class="flex flex-col w-full">
                                <p class="text-base font-medium leading-normal pb-2">Fecha del Registro</p>
                                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-white/20 dark:border-white/10 bg-white/10 dark:bg-black/10 h-12 placeholder:text-gray-600 dark:placeholder:text-gray-400 p-3 text-base font-normal
                                       transition-all duration-300"
                                       type="date" id="fecha" name="fecha_registro" required />
                            </label>
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-4 mt-6 border-t border-white/20 dark:border-white/10 pt-6">
                            <button class="flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-11 px-5 bg-primary text-white text-sm font-bold hover:bg-primary/90 w-full
                                    transition-all duration-300 hover:scale-105 dark:focus:ring-offset-slate-900"
                                    type="submit">
                                <span class="truncate">Guardar Registro</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="mt-8 rounded-xl
                        transition-all duration-300 hover:shadow-xl hover:-translate-y-1
                        animate__animated animate__fadeInUp glass-card overflow-hidden"
                        style="--animate-delay: 0.2s;">
            <h2 class="text-gray-900 dark:text-gray-100 text-xl font-bold p-6">Registros de Peso</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="border-b border-t border-white/20 dark:border-white/10 bg-transparent">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300">Fecha</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300">Peso</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300">Altura</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300">IMC</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300">Clasificación</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300">Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="historial-tabla-body" class="divide-y divide-white/20 dark:divide-white/10">
                        </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
</div> 


<?php if ($showSplash): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const splash = document.getElementById('welcome-splash'); //
        const content = document.getElementById('dashboard-content'); //
        
        setTimeout(() => {
            if (splash) { // Añadimos comprobación por si acaso
                splash.classList.remove('animate__fadeIn'); //
                splash.classList.add('animate__fadeOut'); //

                splash.addEventListener('animationend', () => {
                    splash.style.display = 'none'; //
                    if(content) content.classList.remove('hidden'); //
                    
                    if (typeof cargarDatos === 'function') {
                        cargarDatos();
                    }

                }, { once: true });
            } else {
                 if(content) content.classList.remove('hidden'); // Muestra contenido si no hay splash
            }

        }, 2000); 
    });
</script>
<?php else: ?>
 <script>
    document.addEventListener('DOMContentLoaded', function() {
        const content = document.getElementById('dashboard-content');
        if (content) {
            content.classList.remove('hidden');
        }

         if (typeof cargarDatos === 'function') {
            cargarDatos();
         }
    });
 </script>
<?php endif; ?>


<script>
    let imcChartInstance = null; //
    const chartContainer = document.getElementById('imcChart')?.parentNode; //
    const tablaBody = document.getElementById('historial-tabla-body'); //
    const deleteSuccessMessage = document.getElementById('delete-success-message'); //

    function getClasificacionIMC(imc) {
         if (imc < 18.5) return { texto: 'Bajo Peso', color: 'text-blue-800 dark:text-blue-300' };
        if (imc < 25) return { texto: 'Peso Normal', color: 'text-green-800 dark:text-green-300' };
        if (imc < 30) return { texto: 'Sobrepeso', color: 'text-yellow-800 dark:text-yellow-300' };
        if (imc < 35) return { texto: 'Obesidad I', color: 'text-orange-800 dark:text-orange-300' };
        if (imc < 40) return { texto: 'Obesidad II', color: 'text-red-800 dark:text-red-300' };
        return { texto: 'Obesidad III', color: 'text-red-900 dark:text-red-200' }; //
    }

    function cargarDatos() {
        if (!tablaBody) return; //
        tablaBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-700 dark:text-gray-300">Cargando historial...</td></tr>'; //

        fetch('get_data.php?token=' + encodeURIComponent(window.csrfToken)) //
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errData => {
                        throw new Error(errData.error || errData.message || 'Respuesta de red no fue OK');
                    });
                }
                return response.json(); //
            })
            .then(jsonResponse => { 
                if (!jsonResponse.success || !jsonResponse.data) {
                    throw new Error(jsonResponse.error || jsonResponse.message || 'Error en el formato de datos recibidos.');
                }
                
                const data = jsonResponse.data; 

                if (imcChartInstance) { 
                    imcChartInstance.destroy(); 
                }
                if (!chartContainer) return; 

                if (data.length === 0) { 
                    chartContainer.innerHTML = '<p class="text-center text-gray-700 dark:text-gray-300">No hay datos registrados todavía.</p>'; 
                    tablaBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-700 dark:text-gray-300">No hay registros.</td></tr>'; 
                    return;
                }

                const reversedData = [...data].reverse(); 
                const labels = reversedData.map(item => item.fecha_registro); 
                const imcData = reversedData.map(item => item.imc); 

                const canvas = document.getElementById('imcChart'); 
                if (!canvas) { 
                    chartContainer.innerHTML = '<canvas id="imcChart"></canvas>'; 
                }
                const ctx = document.getElementById('imcChart').getContext('2d'); 

                const isDarkMode = document.documentElement.classList.contains('dark');
                const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                const textColor = isDarkMode ? '#F9FAFB' : '#111827';


                imcChartInstance = new Chart(ctx, { 
                    type: 'line', 
                    data: {
                        labels: labels, 
                        datasets: [{
                            label: 'Índice de Masa Corporal (IMC)', 
                            data: imcData, 
                            borderColor: 'rgba(74, 144, 226, 1)', 
                            backgroundColor: 'rgba(74, 144, 226, 0.2)', 
                            fill: true, 
                            tension: 0.1 
                        }]
                    },
                    options: {
                        responsive: true, 
                        maintainAspectRatio: false, 
                        scales: { 
                            y: { 
                                beginAtZero: false, 
                                title: { display: true, text: 'IMC', color: textColor },
                                grid: { color: gridColor },
                                ticks: { color: textColor }
                            },
                            x: {
                                grid: { color: gridColor },
                                ticks: { color: textColor }
                            }
                        },
                        plugins: {
                            legend: {
                                labels: { color: textColor }
                            }
                        }
                    }
                });

                let tableHtml = ''; 
                data.forEach(item => { 
                    const clasificacion = getClasificacionIMC(item.imc); 

                    tableHtml += `
                        <tr class="hover:bg-subtle-light dark:hover:bg-subtle-dark">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">${item.fecha_registro}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">${item.peso} kg</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">${item.altura} m</td>
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
                if (chartContainer) {
                   chartContainer.innerHTML = `<h2>Error al cargar el gráfico</h2><p>${error.message || 'No se pudieron obtener los datos.'}</p>`; 
                }
                if (tablaBody) {
                   tablaBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error al cargar los registros: ${error.message || ''}</td></tr>`; 
                }
            });
    } 


    document.addEventListener('DOMContentLoaded', function() {
        if (tablaBody) { 
            tablaBody.addEventListener('click', function(e) { 
                 if (e.target.classList.contains('btn-delete')) { 
                    const button = e.target; 
                    const metricId = button.getAttribute('data-id'); 

                    if (confirm('¿Estás seguro de que quieres eliminar este registro? Esta acción no se puede deshacer.')) { 

                        // ----- INICIO DE LA MODIFICACIÓN (API Call) -----
                        
                        // 1. La URL ya no lleva parámetros
                        const url = 'delete_data.php';

                        fetch(url, {
                            // 2. Cambiar a POST
                            method: 'POST', 
                            // 3. Definir la cabecera JSON
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            // 4. Enviar el ID y el Token en el cuerpo como un string JSON
                            body: JSON.stringify({
                                id: metricId,
                                token: window.csrfToken
                            })
                        })
                        .then(response => response.json()) 
                        .then(result => {
                            if (result.success) { 
                                cargarDatos(); 
                                if (deleteSuccessMessage) { 
                                    deleteSuccessMessage.classList.remove('hidden'); 
                                    setTimeout(() => { 
                                        deleteSuccessMessage.classList.add('hidden'); 
                                    }, 3000); 
                                }
                            } else {
                                // Mostramos el error devuelto por la API (ej. "Token no válido")
                                alert('Error al eliminar: ' + (result.message || 'Error desconocido')); 
                            }
                        })
                        .catch(err => {
                            console.error('Error en fetch delete:', err); 
                            alert('Error de conexión al intentar eliminar.'); 
                        });
                        
                        // ----- FIN DE LA MODIFICACIÓN -----
                    }
                }
            });
        }
    });
</script>


</body>
</html>