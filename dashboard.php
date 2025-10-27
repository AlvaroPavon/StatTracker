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

// 6. Comprobar si debemos mostrar el splash de bienvenida
$showSplash = false;
if (isset($_SESSION['show_welcome_splash']) && $_SESSION['show_welcome_splash'] === true) {
    $showSplash = true;
    unset($_SESSION['show_welcome_splash']);
}

// 7. Obtener datos del usuario (nombre y foto) para la barra lateral
try {
    $stmt = $pdo->prepare("SELECT nombre, profile_pic FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $usuario = $stmt->fetch();

    $nombreUsuario = htmlspecialchars($usuario['nombre']);

    $profilePic = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzZCNzI4MCI+CiAgPHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00cy0xLjc5LTQtNC00LTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYy0yLjY3IDAtOCAxLjM0LTggNHYyaDE2di0yYzAtMi42Ni01LjMzLTQtOC00eiIvPgo8L3N2Zz4=';
    if (!empty($usuario['profile_pic']) && file_exists('uploads/' . $usuario['profile_pic'])) {
        $profilePic = 'uploads/' . $usuario['profile_pic'];
    }

} catch (PDOException $e) {
    $nombreUsuario = htmlspecialchars($_SESSION['user_nombre']);
    $profilePic = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzZCNzI4MCI+CiAgPHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00cy0xLjc5LTQtNC00LTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYy0yLjY3IDAtOCAxLjM0LTggNHYyaDE2di0yYzAtMi42Ni01LjMzLTQtOC00eiIvPgo8L3N2Zz4=';
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/2.1.3/TweenMax.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/wavify@1.0.6/dist/wavify.js"></script>
    <script>
        window.csrfToken = "<?php echo $csrf_token; ?>";
    </script>

    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .material-symbols-outlined.fill { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .btn-delete { padding: 4px 8px; border-radius: 4px; background-color: #fef2f2; color: #ef4444; font-weight: 500; transition: all 0.3s; }
        .btn-delete:hover { background-color: #ef4444; color: #ffffff; }

        .sidebar-profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #e0e6ed;
        }

        :root {
            --animate-duration: 0.8s;
        }

        /* ----- INICIO MODIFICACIÓN (Estilos Splash Bienvenida con SVG) ----- */
        #welcome-splash {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            /* El fondo ahora será el SVG */
            background-color: #1a1a2e; /* Fondo oscuro azulado base */
            overflow: hidden;
        }

        #waveSvg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1; /* Detrás del texto */
        }

        #welcome-splash h1 {
            position: relative;
            z-index: 2; /* Encima del SVG */
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.7); /* Sombra más fuerte para contraste */
            font-size: 3.5rem; /* Más grande */
            line-height: 1.1;
        }
        @media (min-width: 1024px) { /* lg */
             #welcome-splash h1 {
                 font-size: 5rem; /* Aún más grande en pantallas grandes */
             }
        }


        /* Duración de entrada y pulso (sin cambios) */
        #welcome-splash .animate__fadeIn {
             --animate-duration: 1.2s;
        }
        #welcome-splash .animate__pulse {
             --animate-duration: 2s;
        }
        /* Duración de salida (sin cambios) */
        #welcome-splash.animate__fadeOut {
             --animate-duration: 0.8s;
             background: transparent; /* Hace que el fondo se desvanezca */
        }
        /* ----- FIN MODIFICACIÓN ----- */
    </style>
    <script id="tailwind-config">
       tailwind.config = {
         darkMode: "class",
         theme: { /* ... Tu config de Tailwind ... */ }
       }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">

<?php if ($showSplash): ?>
    <div id="welcome-splash" class="animate__animated animate__fadeIn">
        <svg id="waveSvg" width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg">
            <defs></defs>
            <path id="wavePath" d=""/>
        </svg>
        <h1 class="animate__animated animate__pulse animate__infinite">
            Bienvenido, <?php echo $nombreUsuario; ?>
        </h1>
    </div>
<?php endif; ?>

<div id="dashboard-content" class="flex h-screen w-full <?php echo $showSplash ? 'hidden' : ''; ?>">

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
        const splash = document.getElementById('welcome-splash');
        const content = document.getElementById('dashboard-content');
        const wavePath = document.getElementById('wavePath');

        // ----- INICIO MODIFICACIÓN (Inicializar Wavify) -----
        if (wavePath) {
             var wave = wavify(wavePath, {
                height: 80, // Altura base de la ola
                bones: 4, // Complejidad de la curva
                amplitude: 60, // Variación de altura
                color: 'rgba(74, 144, 226, 0.6)', // Color primario semi-transparente
                speed: .15 // Velocidad de la animación
            });
            // Wavify no necesita ser destruido explícitamente como el canvas
        }
        // ----- FIN MODIFICACIÓN -----


        // Lógica para ocultar el splash (sin cambios)
        setTimeout(() => {
            splash.classList.remove('animate__fadeIn');
            splash.classList.add('animate__fadeOut');

            splash.addEventListener('animationend', () => {
                splash.style.display = 'none';
                content.classList.remove('hidden');
            }, { once: true });

        }, 2200); // Duración total del splash (ajusta si es necesario)
    });
</script>
<?php endif; ?>


<script>
    // Script original del dashboard (sin cambios)
    document.addEventListener('DOMContentLoaded', function() { /* ... */ });
</script>

</body>
</html>