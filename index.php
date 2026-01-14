<?php
/**
 * index.php - Página de login
 * @package StatTracker
 */

// 1. Inicializar seguridad
require __DIR__ . '/security_init.php';
require __DIR__ . '/db.php';

use App\Security;
use App\SessionManager;
use App\Honeypot;

// 2. Redirigir si el usuario ya está logueado
if (SessionManager::isAuthenticated()) {
    header('Location: dashboard.php');
    exit;
}

// 3. Generar Token CSRF
$csrf_token = Security::generateCsrfToken();

// 4. Generar Honeypot
$honeypot_html = Honeypot::generate();
$js_check = Honeypot::generateJsCheck();

// Constantes de validación para el frontend
$maxEmail = Security::MAX_EMAIL;
$maxPassword = Security::MAX_PASSWORD;
$minPassword = Security::MIN_PASSWORD;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login - StatTracker</title>
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
    
    <!-- Welcome Screen CSS -->
    <link rel="stylesheet" href="css/welcome-screen.css"/>

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
            z-index: 9999; /* Asegura que esté por encima de todo */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #F8F9FA; /* background-light */
        }
        .dark #splash-screen {
            background-color: #1F2937; /* background-dark */
        }
        
        /* Ajustar duraciones de las animaciones específicas */
        #splash-screen .animate__bounceIn {
            --animate-duration: 1.2s; /* Duración del bote de entrada */
        }
        #splash-screen.animate__fadeOut {
            --animate-duration: 0.8s; /* Duración del desvanecimiento */
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
<div class="relative flex min-h-screen w-full flex-col items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="flex flex-col items-center mb-8 animate__animated animate__fadeInDown">
            <div class="flex items-center gap-3 p-2">
                <span class="material-symbols-outlined text-primary text-5xl">scale</span>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-text-light dark:text-text-dark">StatTracker</h1>
            </div>
            <p class="text-gray-500 dark:text-gray-400 mt-2">¡Bienvenido! Por favor, inicia sesión.</p>
        </div>
        
        <div class="liquid-glass-strong liquid-shine water-drop-effect p-8 rounded-xl 
                    transition-all duration-300
                    animate__animated animate__fadeInUp">
            
            <?php 
            if (isset($_SESSION['login_error'])): ?>
                <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg border border-red-300 animate__animated animate__shakeX" role="alert">
                    <?php 
                    echo htmlspecialchars($_SESSION['login_error']); 
                    // Limpiamos el error para que no se muestre de nuevo
                    unset($_SESSION['login_error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg border border-green-300" role="alert">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>

            <form class="flex flex-col gap-6" action="login.php" method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo Security::escapeHtml($csrf_token); ?>">
                
                <label class="flex flex-col w-full">
                    <p class="text-base font-medium leading-normal pb-2">Email</p>
                    <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-400 p-3 text-base font-normal
                        transition-all duration-300" 
                        placeholder="you@example.com" type="email" name="email" id="login_email" 
                        maxlength="<?php echo $maxEmail; ?>" autocomplete="email" required />
                </label>
                <label class="flex flex-col w-full">
                    <p class="text-base font-medium leading-normal pb-2">Contraseña</p>
                    <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-400 p-3 text-base font-normal
                        transition-all duration-300"
                        placeholder="••••••••" type="password" name="password" id="login_password" 
                        minlength="<?php echo $minPassword; ?>" maxlength="<?php echo $maxPassword; ?>" autocomplete="current-password" required />
                </label>
                
                <button class="glass-button flex w-full cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-12 px-5 text-white text-base font-bold focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/50 dark:focus:ring-offset-background-dark
                        transition-all duration-300"
                        type="submit">
                    <span class="relative z-10">Iniciar Sesión</span>
                </button>
            </form>
        </div>
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">¿No tienes una cuenta? <a class="font-medium text-primary hover:underline" href="register_page.php">Regístrate ahora</a></p>
        </div>
    </div>
    </div>

</div>

<!-- Cursor Spotlight Script -->
<script src="js/cursor-spotlight.js"></script>

<!-- Welcome Screen Script -->
<script src="js/welcome-screen.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const splashScreen = document.getElementById('splash-screen');
        const mainContent = document.getElementById('main-content');

        // 1. Esperar un tiempo total para el splash
        // (p.ej., 1.2s para el 'bounceIn' + 1.3s de pausa = 2.5s)
        setTimeout(() => {
            
            // 2. Iniciar animación de desvanecimiento (fadeOut)
            splashScreen.classList.remove('animate__bounceIn'); // Quita la animación de entrada por si acaso
            splashScreen.classList.add('animate__fadeOut');

            // 3. Escuchar cuándo termina la animación de desvanecimiento
            splashScreen.addEventListener('animationend', () => {
                
                // 4. Ocultar permanentemente el splash screen
                splashScreen.style.display = 'none';
                
                // 5. Mostrar el contenido principal
                mainContent.classList.remove('hidden');
                
                // Las animaciones 'fadeInDown' y 'fadeInUp' que están
                // dentro de 'main-content' se activarán ahora.
            });

        }, 2500); // 2.5 segundos de duración total del splash
    });
</script>

<!-- Script de Validación de Formularios -->
<script src="js/form-validation.js"></script>
</body>
</html>