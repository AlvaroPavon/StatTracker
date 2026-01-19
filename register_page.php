<?php
/**
 * register_page.php - Página de registro
 * @package StatTracker
 */

// 1. Inicializar seguridad
require __DIR__ . '/security_init.php';
require __DIR__ . '/db.php';

use App\Security;
use App\SessionManager;
use App\Honeypot;
use App\SimpleCaptcha;

// Verificar si la BD está disponible
$dbAvailable = isset($pdo) && $pdo !== null;

// 2. Redirigir si el usuario ya está logueado
if ($dbAvailable && SessionManager::isAuthenticated()) {
    header('Location: dashboard.php');
    exit;
}

// 3. Generar Token CSRF
$csrf_token = Security::generateCsrfToken();

// 4. Generar Honeypot
$honeypot_html = Honeypot::generate();
$js_check = Honeypot::generateJsCheck();

// 5. Generar CAPTCHA
$captcha = SimpleCaptcha::generate();

// 6. Recuperar datos del formulario si hubo error de CAPTCHA
$formData = $_SESSION['register_form_data'] ?? [];
unset($_SESSION['register_form_data']);

// Constantes de validación para el frontend
$maxNombre = Security::MAX_NOMBRE;
$maxApellidos = Security::MAX_APELLIDOS;
$maxEmail = Security::MAX_EMAIL;
$maxPassword = Security::MAX_PASSWORD;

// Error de BD
$dbError = !$dbAvailable ? "⚠️ Base de datos no disponible. Verifica la configuración de MySQL." : null;
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
        if ($dbError): ?>
            <div class="mb-4 p-4 text-sm text-orange-700 bg-orange-100 rounded-lg border border-orange-300 animate__animated animate__fadeIn" role="alert">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">warning</span>
                    <span><?php echo htmlspecialchars($dbError); ?></span>
                </div>
            </div>
        <?php endif; ?>

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
            
            <!-- Honeypot anti-bot (campos ocultos) -->
            <?php echo $honeypot_html; ?>

            <label class="flex flex-col w-full">
                <p class="text-base font-medium leading-normal pb-2">Nombre <span class="text-xs text-gray-500">(máx. <?php echo $maxNombre; ?> caracteres)</span></p>
                <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-400 p-3 text-base font-normal
                    transition-all duration-300"
                    placeholder="Ej: María" type="text" name="nombre" id="reg_nombre" 
                    maxlength="<?php echo $maxNombre; ?>" autocomplete="given-name" 
                    value="<?php echo Security::escapeHtml($formData['nombre'] ?? ''); ?>"
                    pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+" title="Solo letras, espacios y guiones" required />
            </label>
            <label class="flex flex-col w-full">
                <p class="text-base font-medium leading-normal pb-2">Apellidos <span class="text-xs text-gray-500">(máx. <?php echo $maxApellidos; ?> caracteres)</span></p>
                <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-400 p-3 text-base font-normal
                    transition-all duration-300"
                    placeholder="Ej: García López" type="text" name="apellidos" id="reg_apellidos" 
                    maxlength="<?php echo $maxApellidos; ?>" autocomplete="family-name"
                    value="<?php echo Security::escapeHtml($formData['apellidos'] ?? ''); ?>"
                    pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+" title="Solo letras, espacios y guiones" required />
            </label>

            <label class="flex flex-col w-full">
                <p class="text-base font-medium leading-normal pb-2">Email</p>
                <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-400 p-3 text-base font-normal
                    transition-all duration-300"
                    placeholder="correo@ejemplo.com" type="email" name="email" id="reg_email" 
                    maxlength="<?php echo $maxEmail; ?>" autocomplete="email" 
                    value="<?php echo Security::escapeHtml($formData['email'] ?? ''); ?>"
                    required />
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

            <!-- CAPTCHA matemático -->
            <?php echo $captcha['html']; ?>

            <button class="glass-button flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-5 text-white text-base font-bold mt-4 w-full
                        transition-all duration-300" type="submit">
                <span class="truncate relative z-10">Registrarse</span>
            </button>
        </form>
        
        <!-- JavaScript check anti-bot -->
        <?php echo $js_check; ?>
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

<!-- Script de Validación de Formularios -->
<script src="js/form-validation.js"></script>

</body>
</html>