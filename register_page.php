<?php
// 1. REFINAMIENTO DE ARQUITECTURA: Incluir 'db.php' ANTES de session_start()
require 'db.php';

// 2. Iniciar la sesión
session_start();

// 3. Refinamiento: Redirigir si el usuario ya está logueado
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// 4. REFINAMIENTO DE SEGURIDAD (CSRF): Generar Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Registro - StatTracker</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    />

    <script>
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "primary": "#4A90E2", "secondary": "#50E3C2",
              /* MODIFICADO: Colores de fondo no usados */
              "background-light": "#F8F9FA", "background-dark": "#1F2937",
              "text-light": "#4A4A4A", "text-dark": "#E5E7EB",
              "border-light": "#E1E8ED", "border-dark": "#374151"
            },
            fontFamily: { "display": ["Inter", "sans-serif"] },
            borderRadius: {"DEFAULT": "0.5rem", "lg": "0.75rem", "xl": "1rem", "full": "9999px"},
          },
        },
      }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24 }

        :root {
            --animate-duration: 0.8s;
        }

        /* ----- INICIO MODIFICACIÓN (Estilos Splash Screen) ----- */
        #splash-screen {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            /* MODIFICADO: Fondo se aplica por Tailwind */
        }
        #splash-screen .animate__bounceIn {
            --animate-duration: 1.2s;
        }
        #splash-screen.animate__fadeOut {
            --animate-duration: 0.8s;
        }
        /* ----- FIN MODIFICACIÓN ----- */

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
</head>
<body class="font-display text-gray-900 dark:text-gray-100 bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-slate-900 dark:to-gray-800">

<div id="splash-screen" class="animate__animated animate__bounceIn bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-slate-900 dark:to-gray-800">
    <div class="flex items-center gap-3 p-2">
        <span class="material-symbols-outlined text-primary text-7xl">scale</span>
    </div>
    <h1 class="text-4xl font-bold leading-tight tracking-tight text-gray-900 dark:text-white mt-4">StatTracker</h1>
</div>
<div id="main-content" class="hidden">
<div class="relative flex min-h-screen w-full flex-col items-center justify-center p-4 group/design-root">

    <div class="w-full max-w-md p-8 rounded-xl shadow-lg
                transition-all duration-300 hover:shadow-xl
                animate__animated animate__fadeInUp glass-card">

        <div class="flex flex-col items-center mb-8 animate__animated animate__fadeInDown">
            <div class="flex items-center gap-3 mb-2">
                <span class="material-symbols-outlined text-primary text-4xl">scale</span>
                <h1 class="text-2xl font-bold leading-tight tracking-tight text-gray-900 dark:text-white">StatTracker</h1>
            </div>
            <p class="text-3xl font-bold leading-tight tracking-tighter text-gray-900 dark:text-white">Crea Tu Cuenta</p>
            <p class="text-gray-700 dark:text-gray-300 mt-1">Comienza tu camino hacia una mejor gestión de la salud.</p>
        </div>

        <?php if (isset($_GET['reg_error'])): ?>
            <div class="mb-4 p-4 text-sm text-red-900 dark:text-red-100 bg-red-500/20 rounded-lg border border-red-500/30" role="alert">
                <?php echo htmlspecialchars($_GET['reg_error']); ?>
            </div>
        <?php endif; ?>

        <form class="flex flex-col gap-4" action="register.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <label class="flex flex-col w-full">
                <p class="text-base font-medium leading-normal pb-2">Nombre</p>
                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-white/20 dark:border-white/10 bg-white/10 dark:bg-black/10 h-12 placeholder:text-gray-600 dark:placeholder:text-gray-400 p-3 text-base font-normal
                    transition-all duration-300"
                    placeholder="Ej: Jane" type="text" name="nombre" id="reg_nombre" required />
            </label>
            <label class="flex flex-col w-full">
                <p class="text-base font-medium leading-normal pb-2">Apellidos</p>
                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-white/20 dark:border-white/10 bg-white/10 dark:bg-black/10 h-12 placeholder:text-gray-600 dark:placeholder:text-gray-400 p-3 text-base font-normal
                    transition-all duration-300"
                    placeholder="Ej: Doe" type="text" name="apellidos" id="reg_apellidos" required />
            </label>

            <label class="flex flex-col w-full">
                <p class="text-base font-medium leading-normal pb-2">Email</p>
                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-white/20 dark:border-white/10 bg-white/10 dark:bg-black/10 h-12 placeholder:text-gray-600 dark:placeholder:text-gray-400 p-3 text-base font-normal
                    transition-all duration-300"
                    placeholder="you@example.com" type="email" name="email" id="reg_email" required />
            </label>
            <label class="flex flex-col w-full">
                <p class="text-base font-medium leading-normal pb-2">Contraseña</p>
                <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-white/20 dark:border-white/10 bg-white/10 dark:bg-black/10 h-12 placeholder:text-gray-600 dark:placeholder:text-gray-400 p-3 text-base font-normal
                    transition-all duration-300"
                    placeholder="••••••••" type="password" name="password" id="reg_password" minlength="8" required />
            </label>

            <button class="flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-5 bg-primary text-white text-base font-bold hover:bg-primary/90 mt-4 w-full
                        transition-all duration-300 hover:scale-105" type="submit">
                <span class="truncate">Registrarse</span>
            </button>
        </form>
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-700 dark:text-gray-300">
                ¿Ya tienes una cuenta? <a class="font-medium text-primary hover:underline" href="index.php">Inicia sesión aquí</a>
            </p>
        </div>
    </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const splashScreen = document.getElementById('splash-screen');
        const mainContent = document.getElementById('main-content');

        // Mismo script que en index.php
        setTimeout(() => {
            splashScreen.classList.remove('animate__bounceIn');
            splashScreen.classList.add('animate__fadeOut');

            splashScreen.addEventListener('animationend', () => {
                splashScreen.style.display = 'none';
                mainContent.classList.remove('hidden');
            });

        }, 2500); // 2.5 segundos de duración total del splash
    });
</script>
</body>
</html>