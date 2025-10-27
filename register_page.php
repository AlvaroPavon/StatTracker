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
    <script>
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "primary": "#4A90E2", // Professional Blue
              "secondary": "#50E3C2", // Encouraging Green
              "background-light": "#F8F9FA", // Off-white
              "background-dark": "#1F2937", // A suitable dark background
              "text-light": "#4A4A4A", // Dark Gray
              "text-dark": "#E5E7EB", // Light Gray for dark mode
              "border-light": "#E1E8ED", // Light Gray Border
              "border-dark": "#374151" // Dark mode border
            },
            fontFamily: {
              "display": ["Inter", "sans-serif"]
            },
            borderRadius: {"DEFAULT": "0.5rem", "lg": "0.75rem", "xl": "1rem", "full": "9999px"},
          },
        },
      }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings:
            'FILL' 0,
            'wght' 400,
            'GRAD' 0,
            'opsz' 24
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-text-light dark:text-text-dark">
<div class="relative flex min-h-screen w-full flex-col items-center justify-center p-4 group/design-root">
<div class="w-full max-w-md bg-white dark:bg-gray-800 p-8 rounded-xl border border-border-light dark:border-border-dark shadow-lg">
    <div class="flex flex-col items-center mb-8">
        <div class="flex items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-primary text-4xl">scale</span>
            <h1 class="text-2xl font-bold leading-tight tracking-tight">StatTracker</h1>
        </div>
        <p class="text-3xl font-bold leading-tight tracking-tighter text-text-light dark:text-text-dark">Crea Tu Cuenta</p>
        <p class="text-gray-500 dark:text-gray-400 mt-1">Comienza tu camino hacia una mejor gestión de la salud.</p>
    </div>

    <?php 
    // --- INICIO BLOQUE DE MENSAJES ---
    if (isset($_GET['reg_error'])): ?>
        <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg border border-red-300" role="alert">
            <?php echo htmlspecialchars($_GET['reg_error']); ?>
        </div>
    <?php endif; 
    // --- FIN BLOQUE DE MENSAJES ---
    ?>

    <form class="flex flex-col gap-4" action="register.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <label class="flex flex-col w-full">
            <p class="text-base font-medium leading-normal pb-2">Nombre</p>
            <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-border-light dark:border-border-dark bg-background-light dark:bg-gray-700 h-12 placeholder:text-gray-400 p-3 text-base font-normal" 
                   placeholder="Ej: Jane" 
                   type="text" 
                   name="nombre"
                   id="reg_nombre"
                   required />
        </label>
        <label class="flex flex-col w-full">
            <p class="text-base font-medium leading-normal pb-2">Apellidos</p>
            <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-border-light dark:border-border-dark bg-background-light dark:bg-gray-700 h-12 placeholder:text-gray-400 p-3 text-base font-normal" 
                   placeholder="Ej: Doe" 
                   type="text" 
                   name="apellidos"
                   id="reg_apellidos"
                   required />
        </label>
        
        <label class="flex flex-col w-full">
            <p class="text-base font-medium leading-normal pb-2">Email</p>
            <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-border-light dark:border-border-dark bg-background-light dark:bg-gray-700 h-12 placeholder:text-gray-400 p-3 text-base font-normal" 
                   placeholder="you@example.com" 
                   type="email" 
                   name="email"
                   id="reg_email"
                   required />
        </label>
        <label class="flex flex-col w-full">
            <p class="text-base font-medium leading-normal pb-2">Contraseña</p>
            <input class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-border-light dark:border-border-dark bg-background-light dark:bg-gray-700 h-12 placeholder:text-gray-400 p-3 text-base font-normal" 
                   placeholder="••••••••" 
                   type="password" 
                   name="password"
                   id="reg_password"
                   minlength="8"
                   required />
        </label>
        
        <button class="flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-5 bg-primary text-white text-base font-bold hover:bg-primary/90 mt-4 w-full" type="submit">
            <span class="truncate">Registrarse</span>
        </button>
    </form>
    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            ¿Ya tienes una cuenta? <a class="font-medium text-primary hover:underline" href="index.php">Inicia sesión aquí</a>
        </p>
    </div>
</div>
</div>

</body>
</html>