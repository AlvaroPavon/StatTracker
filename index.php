<?php
// 1. REFINAMIENTO DE ARQUITECTURA: Incluir 'db.php' ANTES de session_start()
// (db.php ahora incluye session_config.php y database_connection.php)
require 'db.php';

// 2. Iniciar la sesión
// MODIFICACIÓN: Asegurarnos de que solo se inicie si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Refinamiento: Redirigir si el usuario ya está logueado
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit; // Detener la ejecución del script
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
        
        <div class="bg-white dark:bg-gray-800 p-8 rounded-xl border border-border-light dark:border-border-dark shadow-lg 
                    transition-all duration-300 hover:shadow-xl
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

            <form class="flex flex-col gap-6" action="login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <label class="flex flex-col w-full">
                    <p class="text-base font-medium leading-normal pb-2">Email</p>
                    <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-border-light dark:border-border-dark bg-background-light dark:bg-gray-700 h-12 placeholder:text-gray-400 p-3 text-base font-normal
                        transition-all duration-300" 
                        placeholder="you@example.com" type="email" name="email" id="login_email" required />
                </label>
                <label class="flex flex-col w-full">
                    <p class="text-base font-medium leading-normal pb-2">Contraseña</p>
                    <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-border-light dark:border-border-dark bg-background-light dark:bg-gray-700 h-12 placeholder:text-gray-400 p-3 text-base font-normal
                        transition-all duration-300"
                        placeholder="••••••••" type="password" name="password" id="login_password" required />
                </label>
                
                <button class="flex w-full cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-12 px-5 bg-primary text-white text-base font-bold hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary/50 dark:focus:ring-offset-background-dark
                        transition-all duration-300 hover:scale-105"
                        type="submit">
                    <span>Iniciar Sesión</span>
                </button>
            </form>
        </div>
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">¿No tienes una cuenta? <a class="font-medium text-primary hover:underline" href="register_page.php">Regístrate ahora</a></p>
        </div>
    </div>
    </div>

</div>

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
</body>
</html>