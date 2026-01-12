<?php
// 1. Cargar autoloader y clases de seguridad
require 'vendor/autoload.php';
require 'session_config.php';
require 'db.php';

use App\Security;
use App\SecurityHeaders;

// 2. Aplicar headers de seguridad
SecurityHeaders::apply();

// 3. Iniciar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 4. Redirigir si el usuario ya está logueado
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// 5. Generar Token CSRF
$csrf_token = Security::generateCsrfToken();

// Constantes de validación para el frontend
$maxNombre = Security::MAX_NOMBRE;
$maxApellidos = Security::MAX_APELLIDOS;
$maxEmail = Security::MAX_EMAIL;
$maxPassword = Security::MAX_PASSWORD;
$minPassword = Security::MIN_PASSWORD;
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
    
    <!-- Liquid Glass Effect CSS -->
    <link rel="stylesheet" href="css/liquid-glass.css"/>
    
    <!-- Cursor Spotlight Effect CSS -->
    <link rel="stylesheet" href="css/cursor-spotlight.css"/>

    <script>
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "primary": "#4A90E2", "secondary": "#50E3C2",
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
            background-color: #F8F9FA; /* background-light */
        }
        .dark #splash-screen {
            background-color: #1F2937; /* background-dark */
        }
        #splash-screen .animate__bounceIn {
            --animate-duration: 1.2s;
        }
        #splash-screen.animate__fadeOut {
            --animate-duration: 0.8s;
        }
        /* ----- FIN MODIFICACIÓN ----- */
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-text-light dark:text-text-dark">

<div id="splash-screen" class="animate__animated animate__bounceIn">
    <div class="flex items-center gap-3 p-2">
        <span class="material-symbols-outlined text-primary text-7xl">scale</span>
    </div>
    <h1 class="text-4xl font-bold leading-tight tracking-tight text-text-light dark:text-text-dark mt-4">StatTracker</h1>
</div>
<div id="main-content" class="hidden">
<div class="relative flex min-h-screen w-full flex-col items-center justify-center p-4 group/design-root">

    <div class="w-full max-w-md liquid-glass-strong liquid-shine water-drop-effect p-8 rounded-xl
                transition-all duration-300
                animate__animated animate__fadeInUp">

        <div class="flex flex-col items-center mb-8 animate__animated animate__fadeInDown">
            <div class="flex items-center gap-3 mb-2">
                <span class="material-symbols-outlined text-primary text-4xl">scale</span>
                <h1 class="text-2xl font-bold leading-tight tracking-tight">StatTracker</h1>
            </div>
            <p class="text-3xl font-bold leading-tight tracking-tighter text-text-light dark:text-text-dark">Crea Tu Cuenta</p>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Comienza tu camino hacia una mejor gestión de la salud.</p>
        </div>

        <?php 
        if (isset($_SESSION['register_error'])): ?>
            <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg border border-red-300 animate__animated animate__shakeX" role="alert">
                <?php 
                echo Security::escapeHtml($_SESSION['register_error']); 
                unset($_SESSION['register_error']);
                ?>
            </div>
        <?php endif; ?>

        <form class="flex flex-col gap-4" action="register.php" method="POST" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo Security::escapeHtml($csrf_token); ?>">

            <label class="flex flex-col w-full">
                <p class="text-base font-medium leading-normal pb-2">Nombre <span class="text-xs text-gray-500">(máx. <?php echo $maxNombre; ?> caracteres)</span></p>
                <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-400 p-3 text-base font-normal
                    transition-all duration-300"
                    placeholder="Ej: María" type="text" name="nombre" id="reg_nombre" 
                    maxlength="<?php echo $maxNombre; ?>" autocomplete="given-name" 
                    pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+" title="Solo letras, espacios y guiones" required />
            </label>
            <label class="flex flex-col w-full">
                <p class="text-base font-medium leading-normal pb-2">Apellidos <span class="text-xs text-gray-500">(máx. <?php echo $maxApellidos; ?> caracteres)</span></p>
                <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-400 p-3 text-base font-normal
                    transition-all duration-300"
                    placeholder="Ej: García López" type="text" name="apellidos" id="reg_apellidos" 
                    maxlength="<?php echo $maxApellidos; ?>" autocomplete="family-name"
                    pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+" title="Solo letras, espacios y guiones" required />
            </label>

            <label class="flex flex-col w-full">
                <p class="text-base font-medium leading-normal pb-2">Email</p>
                <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-400 p-3 text-base font-normal
                    transition-all duration-300"
                    placeholder="correo@ejemplo.com" type="email" name="email" id="reg_email" 
                    maxlength="<?php echo $maxEmail; ?>" autocomplete="email" required />
            </label>
            <label class="flex flex-col w-full">
                <p class="text-base font-medium leading-normal pb-2">Contraseña <span class="text-xs text-gray-500">(<?php echo $minPassword; ?>-<?php echo $maxPassword; ?> caracteres)</span></p>
                <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-400 p-3 text-base font-normal
                    transition-all duration-300"
                    placeholder="Mínimo 1 mayúscula, 1 minúscula y 1 número" type="password" name="password" id="reg_password" 
                    minlength="<?php echo $minPassword; ?>" maxlength="<?php echo $maxPassword; ?>" 
                    autocomplete="new-password" required />
                <p class="text-xs text-gray-500 mt-1">Debe contener mayúsculas, minúsculas y números</p>
            </label>

            <button class="glass-button flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-5 text-white text-base font-bold mt-4 w-full
                        transition-all duration-300" type="submit">
                <span class="truncate relative z-10">Registrarse</span>
            </button>
        </form>
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-300">
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

<!-- Cursor Spotlight Script -->
<script src="js/cursor-spotlight.js"></script>

</body>
</html>