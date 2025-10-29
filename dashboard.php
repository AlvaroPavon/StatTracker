<?php
// 1. REFINAMIENTO DE ARQUITECTURA: Incluir 'db.php' ANTES de session_start()
require 'db.php'; //

// 2. Iniciar la sesión
session_start(); //

// 3. Refinamiento de Seguridad: Proteger la página
if (!isset($_SESSION['user_id'])) { //
    header('Location: index.php'); //
    exit; // Detener la ejecución del script //
}

// 4. Obtener el ID del usuario
$user_id = $_SESSION['user_id']; //

// 5. REFINAMIENTO (CSRF): Obtener el token de la sesión para usarlo
if (empty($_SESSION['csrf_token'])) { //
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); //
}
$csrf_token = $_SESSION['csrf_token']; //

// 6. Comprobar si debemos mostrar el splash de bienvenida
$showSplash = false; //
if (isset($_SESSION['show_welcome_splash']) && $_SESSION['show_welcome_splash'] === true) { //
    $showSplash = true; //
    unset($_SESSION['show_welcome_splash']); //
}

// 7. Obtener datos del usuario (nombre y foto) para la barra lateral
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
            background-color: #e0e6ed; /* */
            display: inline-flex; /* */
            align-items: center; /* */
            justify-content: center; /* */
            color: #6B7280; /* */
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
            /* Usamos el color de fondo normal de la página */
            background-color: #F4F7FA; /* background-light */
            overflow: hidden; /* */
        }
        /* Color de fondo para modo oscuro */
        .dark #welcome-splash {
             background-color: #1F2937; /* background-dark */
        }

        /* Quitamos el SVG */
        /* #waveSvg { ... } */

        /* Ajustamos el texto */
        #welcome-splash h1 {
            position: relative; /* */
            z-index: 2; /* */
             /* Color de texto normal */
            color: #333333; /* text-light */
            /* Quitamos sombra */
            /* text-shadow: 2px 2px 4px rgba(0,0,0,0.7); */
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
        /* Quitamos pulso */
        /* #welcome-splash .animate__pulse { ... } */
        #welcome-splash.animate__fadeOut {
             --animate-duration: 0.8s; /* */
             /* El fondo ya es el de la página, así que no necesitamos hacerlo transparente */
             /* background: transparent; */
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
        }, //
       }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">

<?php if ($showSplash): ?>
    <div id="welcome-splash" class="animate__animated animate__fadeIn">
        <h1 class="animate__animated">
            Bienvenido, <?php echo $nombreUsuario; ?>
        </h1>
    </div>
<?php endif; ?>

<div id="dashboard-content" class="flex h-screen w-full <?php echo $showSplash ? 'hidden' : ''; ?>">

<aside class="flex w-64 flex-col border-r border-border-light dark:border-border-dark bg-content-light dark:bg-content-dark">
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
                    <h1 class="text-text-light dark:text-text-dark text-base font-medium leading-normal">Bienvenido, <?php echo $nombreUsuario; ?></h1>
                </div>
            </div>
            <nav class="flex flex-col gap-2">
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary transition-all duration-300" href="dashboard.php">
                    <span class="material-symbols-outlined fill">dashboard</span>
                    <p class="text-sm font-medium leading-normal">Dashboard</p>
                </a>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-text-light dark:text-text-dark hover:bg-subtle-light dark:hover:bg-subtle-dark transition-all duration-300" href="profile.php">
                    <span class="material-symbols-outlined">person</span>
                    <p class="text-sm font-medium leading-normal">Perfil</p>
                </a>
            </nav>
        </div>
        <div class="flex flex-col gap-1 border-t border-border-light dark:border-border-dark pt-4">
            <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-text-light dark:text-text-dark hover:bg-subtle-light dark:hover:bg-subtle-dark transition-all duration-300"
               href="logout.php?token=<?php echo $csrf_token; ?>">
                <span class="material-symbols-outlined">logout</span>
                <p class="text-sm font-medium leading-normal">Cerrar Sesión</p>
            </a>
        </div>
    </div>
</aside>
<main class="flex-1 flex-col overflow-y-auto">
   <div class="p-8">
        <header class="flex flex-wrap items-center justify-between gap-4 pb-6
                       animate__animated animate__fadeInDown">
            <div class="flex min-w-72 flex-col gap-1">
                <h1 class="text-text-light dark:text-text-dark text-3xl font-bold leading-tight tracking-tight">Tu Progreso</h1>
                <p class="text-secondary-text-light dark:text-secondary-text-dark text-base font-normal leading-normal">Registra y visualiza tu IMC</p>
            </div>
        </header>

        <section class="flex flex-wrap gap-8 py-4
                        animate__animated animate__fadeInUp">

            <div class="flex min-w-72 flex-1 flex-col gap-2 rounded-xl border border-border-light dark:border-border-dark bg-content-light dark:bg-content-dark p-6
                        transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <p class="text-text-light dark:text-text-dark text-lg font-medium leading-normal">Evolución de tu IMC</G>

                <div class="flex min-h-[300px] flex-1 flex-col gap-8 py-4">
                    <canvas id="imcChart"></canvas>
                </div>
            </div>

            <div class="w-full lg:w-96 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 rounded-xl">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-border-light dark:border-border-dark shadow-sm h-full">
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
                                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-border-light dark:border-border-dark bg-background-light dark:bg-gray-700 h-12 placeholder:text-gray-400 p-3 text-base font-normal
                                       transition-all duration-300"
                                       placeholder="Ej: 1.75" type="number" step="0.01" id="altura" name="altura" required />
                            </label>
                            <label class="flex flex-col w-full">
                                <p class="text-base font-medium leading-normal pb-2">Peso (kg)</p>
                                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-border-light dark:border-border-dark bg-background-light dark:bg-gray-700 h-12 placeholder:text-gray-400 p-3 text-base font-normal
                                       transition-all duration-300"
                                       placeholder="Ej: 70.5" type="number" step="0.1" id="peso" name="peso" required />
                            </label>
                            <label class="flex flex-col w-full">
                                <p class="text-base font-medium leading-normal pb-2">Fecha del Registro</p>
                                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-border-light dark:border-border-dark bg-background-light dark:bg-gray-700 h-12 placeholder:text-gray-400 p-3 text-base font-normal
                                       transition-all duration-300"
                                       type="date" id="fecha" name="fecha_registro" required />
                            </label>
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-4 mt-6 border-t border-border-light dark:border-border-dark pt-6">
                            <button class="flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-11 px-5 bg-primary text-white text-sm font-bold hover:bg-primary/90 w-full
                                    transition-all duration-300 hover:scale-105"
                                    type="submit">
                                <span class="truncate">Guardar Registro</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="mt-8 rounded-xl border border-border-light dark:border-border-dark bg-content-light dark:bg-content-dark
                        transition-all duration-300 hover:shadow-xl hover:-translate-y-1
                        animate__animated animate__fadeInUp"
                        style="--animate-delay: 0.2s;">
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
</div> <?php if ($showSplash): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const splash = document.getElementById('welcome-splash'); //
        const content = document.getElementById('dashboard-content'); //
        // Quitamos la referencia a wavePath

        // Quitamos la inicialización de Wavify

        setTimeout(() => {
            if (splash) { // Añadimos comprobación por si acaso
                splash.classList.remove('animate__fadeIn'); //
                splash.classList.add('animate__fadeOut'); //

                splash.addEventListener('animationend', () => {
                    splash.style.display = 'none'; //
                    if(content) content.classList.remove('hidden'); //
                    
                    // ----- INICIO DE LA MODIFICACIÓN (Arreglar bug) -----
                    // Llamamos a cargarDatos() DESPUÉS de mostrar el contenido
                    if (typeof cargarDatos === 'function') {
                        cargarDatos();
                    }
                    // ----- FIN DE LA MODIFICACIÓN -----

                }, { once: true });
            } else {
                 if(content) content.classList.remove('hidden'); // Muestra contenido si no hay splash
            }

        }, 2000); // Reducimos ligeramente el tiempo al quitar la onda
    });
</script>
<?php else: ?>
 <script>
    // Si no hay splash, muestra el contenido directamente
    document.addEventListener('DOMContentLoaded', function() {
        const content = document.getElementById('dashboard-content');
        if (content) {
            content.classList.remove('hidden');
        }
        // Asegurarse de que cargarDatos se llame
         if (typeof cargarDatos === 'function') {
            cargarDatos();
         } else {
             setTimeout(() => {
                if (typeof cargarDatos === 'function') {
                    cargarDatos();
                }
            }, 100);
         }
    });
 </script>
<?php endif; ?>


<script>
    // Script original del dashboard
    let cargarDatos; // Declaración global

    document.addEventListener('DOMContentLoaded', function() {
        const chartContainer = document.getElementById('imcChart')?.parentNode; //
        const tablaBody = document.getElementById('historial-tabla-body'); //
        const deleteSuccessMessage = document.getElementById('delete-success-message'); //
        let imcChartInstance = null; //

        function getClasificacionIMC(imc) {
             if (imc < 18.5) return { texto: 'Bajo Peso', color: 'text-blue-600' };
            if (imc < 25) return { texto: 'Peso Normal', color: 'text-green-600' };
            if (imc < 30) return { texto: 'Sobrepeso', color: 'text-yellow-600' };
            if (imc < 35) return { texto: 'Obesidad I', color: 'text-orange-600' };
            if (imc < 40) return { texto: 'Obesidad II', color: 'text-red-600' };
            return { texto: 'Obesidad III', color: 'text-red-800' }; //
        }

        // Asignación a la variable global
        cargarDatos = function() {
            if (!tablaBody) return; //
            tablaBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Cargando historial...</td></tr>'; //

            fetch('get_data.php?token=' + encodeURIComponent(window.csrfToken)) //
                .then(response => {
                    if (!response.ok) throw new Error('Respuesta de red no fue OK'); //
                    return response.json(); //
                })
                .then(data => {
                    if (data.error) throw new Error(data.error); //

                    if (imcChartInstance) { //
                        imcChartInstance.destroy(); //
                    }
                    if (!chartContainer) return; //

                    if (data.length === 0) { //
                        chartContainer.innerHTML = '<p class="text-center text-gray-500">No hay datos registrados todavía.</p>'; //
                        tablaBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay registros.</td></tr>'; //
                        return;
                    }

                    const reversedData = [...data].reverse(); //
                    const labels = reversedData.map(item => item.fecha_registro); //
                    const imcData = reversedData.map(item => item.imc); //

                    const canvas = document.getElementById('imcChart'); //
                    if (!canvas) { //
                        chartContainer.innerHTML = '<canvas id="imcChart"></canvas>'; //
                    }
                    const ctx = document.getElementById('imcChart').getContext('2d'); //


                    imcChartInstance = new Chart(ctx, { //
                        type: 'line', //
                        data: {
                            labels: labels, //
                            datasets: [{
                                label: 'Índice de Masa Corporal (IMC)', //
                                data: imcData, //
                                borderColor: 'rgba(74, 144, 226, 1)', //
                                backgroundColor: 'rgba(74, 144, 226, 0.1)', //
                                fill: true, //
                                tension: 0.1 //
                            }]
                        },
                        options: {
                            responsive: true, //
                            maintainAspectRatio: false, //
                            scales: { y: { beginAtZero: false, title: { display: true, text: 'IMC' } } } //
                        }
                    });

                    let tableHtml = ''; //
                    data.forEach(item => { //
                        const clasificacion = getClasificacionIMC(item.imc); //

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
                        `; //
                    });
                    tablaBody.innerHTML = tableHtml; //

                })
                .catch(error => {
                    console.error('Error al cargar los datos:', error); //
                    if (chartContainer) {
                       chartContainer.innerHTML = '<h2>Error al cargar el gráfico</h2><p>No se pudieron obtener los datos.</p>'; //
                    }
                    if (tablaBody) {
                       tablaBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error al cargar los registros.</td></tr>'; //
                    }
                });
        } // Fin de cargarDatos

        if (tablaBody) { //
            tablaBody.addEventListener('click', function(e) { //
                 if (e.target.classList.contains('btn-delete')) { //
                    const button = e.target; //
                    const metricId = button.getAttribute('data-id'); //

                    if (confirm('¿Estás seguro de que quieres eliminar este registro? Esta acción no se puede deshacer.')) { //

                        const url = `delete_data.php?id=${metricId}&token=${encodeURIComponent(window.csrfToken)}`; //

                        fetch(url, {
                            method: 'GET' //
                        })
                        .then(response => response.json()) //
                        .then(result => {
                            if (result.success) { //
                                cargarDatos(); //
                                if (deleteSuccessMessage) { //
                                    deleteSuccessMessage.classList.remove('hidden'); //
                                    setTimeout(() => { //
                                        deleteSuccessMessage.classList.add('hidden'); //
                                    }, 3000); //
                                }
                            } else {
                                alert('Error al eliminar: ' + (result.error || 'Error desconocido')); //
                            }
                        })
                        .catch(err => {
                            console.error('Error en fetch delete:', err); //
                            alert('Error de conexión al intentar eliminar.'); //
                        });
                    }
                }
            });
        }

        // Llamar a cargarDatos solo si el splash NO se está mostrando inicialmente
        if (!<?php echo json_encode($showSplash); ?>) {
           cargarDatos();
        }

    });
</script>


</body>
</html>