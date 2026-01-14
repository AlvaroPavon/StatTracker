<?php
/**
 * profile.php - Perfil del usuario
 * @package StatTracker
 */

// 1. Inicializar seguridad
require __DIR__ . '/security_init.php';
require __DIR__ . '/db.php';

use App\Security;
use App\SessionManager;

// 2. Proteger la página
require_auth();

// 3. Obtener el ID del usuario y generar token CSRF
$user_id = SessionManager::getUserId();
$csrf_token = Security::generateCsrfToken();

// Constantes de validación para el frontend
$maxNombre = Security::MAX_NOMBRE;
$maxApellidos = Security::MAX_APELLIDOS;
$maxEmail = Security::MAX_EMAIL;
$maxPassword = Security::MAX_PASSWORD;
$minPassword = Security::MIN_PASSWORD;
$maxFileSize = Security::MAX_FILE_SIZE / 1024 / 1024; // En MB

// 6. Obtener datos completos del usuario
$nombreUsuario = '';
$apellidosUsuario = '';
$emailUsuario = '';
$profilePic = null;
$userHasProfilePic = false;

try {
    $stmt = $pdo->prepare("SELECT nombre, apellidos, email, profile_pic FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        $nombreUsuario = Security::escapeHtml($usuario['nombre']);
        $apellidosUsuario = Security::escapeHtml($usuario['apellidos'] ?? '');
        $emailUsuario = Security::escapeHtml($usuario['email']);

        if (!empty($usuario['profile_pic']) && file_exists('uploads/' . $usuario['profile_pic'])) {
            $profilePic = 'uploads/' . Security::escapeHtml($usuario['profile_pic']);
            $userHasProfilePic = true;
        }
    } else {
         $nombreUsuario = Security::escapeHtml($_SESSION['nombre'] ?? 'Usuario');
         $emailUsuario = 'Error al cargar email';
    }

} catch (PDOException $e) {
    $nombreUsuario = Security::escapeHtml($_SESSION['nombre'] ?? 'Usuario');
    error_log('Error en profile.php: ' . $e->getMessage());
}

?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Perfil - StatTracker</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- Liquid Glass Effect CSS -->
    <link rel="stylesheet" href="css/liquid-glass.css"/>
    
    <!-- Cursor Spotlight Effect CSS -->
    <link rel="stylesheet" href="css/cursor-spotlight.css"/>
    
    <script>
        window.csrfToken = "<?php echo $csrf_token; ?>";
    </script>
    
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .material-symbols-outlined.fill { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }

        /* MODIFICADO: Estilos Sidebar Profile Pic (como en dashboard) */
        .sidebar-profile-pic {
            width: 40px; height: 40px; border-radius: 50%; object-fit: cover;
            background-color: rgba(255, 255, 255, 0.2); display: inline-flex; align-items: center;
            justify-content: center; color: #1F2937;
        }
        .dark .sidebar-profile-pic {
            background-color: rgba(0, 0, 0, 0.2);
            color: #E5E7EB;
        }
        .sidebar-profile-pic img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }

        /* MODIFICADO: Estilos Profile Pic Grande (Glass) */
        .profile-pic-large {
            width: 128px; height: 128px; border-radius: 50%; object-fit: cover;
            background-color: rgba(255, 255, 255, 0.2); 
            display: flex; align-items: center; justify-content: center; 
            color: #1F2937; 
            border: 4px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .dark .profile-pic-large {
            background-color: rgba(0, 0, 0, 0.2);
            color: #E5E7EB;
            border: 4px solid rgba(255, 255, 255, 0.2);
        }
        .profile-pic-large img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        
        /* ----- INICIO MODIFICACIÓN (Estilo "Gota de Agua") ----- */
        .glass-card {
            background-color: rgba(255, 255, 255, 0.1);
            -webkit-backdrop-filter: blur(35px);
            backdrop-filter: blur(35px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .dark .glass-card {
            background-color: rgba(31, 41, 55, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        /* ----- FIN MODIFICACIÓN ----- */

        /* INICIO MODIFICACIÓN: Animación de entrada más rápida */
        :root {
            --animate-duration: 0.8s;
        }
        /* FIN MODIFICACIÓN */
    </style>
    
    <script id="tailwind-config">
       tailwind.config = {
         darkMode: "class",
         theme: {
          extend: {
            colors: {
              "primary": "#4A90E2",
              "text-light": "#333333", "text-dark": "#F9FAFB",
              "border-light": "#E0E6ED", "border-dark": "#4B5563",
              "subtle-light": "rgba(255, 255, 255, 0.1)", "subtle-dark": "rgba(0, 0, 0, 0.1)",
              "secondary-text-light": "#374151", "secondary-text-dark": "#D1D5DB",
            },
            fontFamily: { "display": ["Inter", "sans-serif"] },
            borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
          },
         }, 
       }
    </script>
</head>
<body class="font-display text-gray-900 dark:text-gray-100 bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-slate-900 dark:to-gray-800">

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
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-900 dark:text-gray-100 hover:bg-subtle-light dark:hover:bg-subtle-dark transition-all duration-300" href="dashboard.php">
                    <span class="material-symbols-outlined">dashboard</span>
                    <p class="text-sm font-medium leading-normal">Dashboard</p>
                </a>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary transition-all duration-300" href="profile.php">
                    <span class="material-symbols-outlined fill">person</span>
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
                <h1 class="text-gray-900 dark:text-gray-100 text-3xl font-bold leading-tight tracking-tight">Tu Perfil</h1>
                <p class="text-gray-700 dark:text-gray-300 text-base font-normal leading-normal">Gestiona tu información personal.</p>
            </div>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8
                    animate__animated animate__fadeInUp">
        <div class="xl:col-span-1 flex flex-col gap-8">
                <div class="p-6 rounded-xl shadow-sm liquid-glass-strong water-drop-effect">
                    <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">Información General</h2>
                    
                    <div class="flex flex-col items-center gap-4">
                        <div class="profile-pic-large">
                            <?php if ($userHasProfilePic): ?>
                                <img src="<?php echo $profilePic; ?>?v=<?php echo time(); ?>" alt="Foto de perfil" id="profilePicPreview">
                            <?php else: ?>
                                <span class="material-symbols-outlined !text-7xl" id="profilePicIcon">person</span>
                                <img src="" alt="Foto de perfil" id="profilePicPreview" class="hidden">
                            <?php endif; ?>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100"><?php echo $nombreUsuario . ' ' . $apellidosUsuario; ?></h3>
                        <p class="text-gray-700 dark:text-gray-300"><?php echo $emailUsuario; ?></p>
                    </div>
                    
                    <hr class="my-6 border-white/20 dark:border-white/10">

                    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo Security::escapeHtml($csrf_token); ?>">
                        <input type="hidden" name="form_type" value="photo">
                        
                        <label class="flex flex-col w-full">
                            <p class="text-base font-medium leading-normal pb-2 text-gray-900 dark:text-gray-100">Cambiar Foto de Perfil <span class="text-xs text-gray-500">(máx. <?php echo $maxFileSize; ?>MB)</span></p>
                            <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 placeholder:text-gray-600 dark:placeholder:text-gray-400 p-3 text-base font-normal transition-all duration-300
                                       file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20"
                                type="file" name="profile_pic" id="profile_pic_input" accept="image/png, image/jpeg, image/gif, image/webp" />
                            <p class="text-xs text-gray-500 mt-1">Formatos permitidos: JPG, PNG, GIF, WebP</p>
                        </label>
                        <button class="glass-button flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-11 px-5 text-white text-sm font-bold w-full mt-4
                                transition-all duration-300 hover:scale-105 dark:focus:ring-offset-slate-900"
                                type="submit">
                            <span class="truncate">Actualizar Foto</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="xl:col-span-2 flex flex-col gap-8">
                <?php if (isset($_GET['success'])): ?>
                    <div class="p-4 text-sm text-green-900 dark:text-green-100 bg-green-500/20 rounded-lg border border-green-500/30" role="alert">
                        <?php echo Security::escapeHtml($_GET['success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="p-4 text-sm text-red-900 dark:text-red-100 bg-red-500/20 rounded-lg border border-red-500/30" role="alert">
                        <?php echo Security::escapeHtml($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="p-6 rounded-xl shadow-sm liquid-glass-strong water-drop-effect">
                    <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">Actualizar Información</h2>
                    <form action="update_profile.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Security::escapeHtml($csrf_token); ?>">
                        <input type="hidden" name="form_type" value="details">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <label class="flex flex-col w-full">
                                <p class="text-base font-medium leading-normal pb-2 text-gray-900 dark:text-gray-100">Nombre <span class="text-xs text-gray-500">(máx. <?php echo $maxNombre; ?>)</span></p>
                                <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-600 dark:placeholder:text-gray-400 p-3 text-base font-normal transition-all duration-300"
                                       type="text" name="nombre" value="<?php echo $nombreUsuario; ?>" 
                                       maxlength="<?php echo $maxNombre; ?>" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+" required />
                            </label>
                            <label class="flex flex-col w-full">
                                <p class="text-base font-medium leading-normal pb-2 text-gray-900 dark:text-gray-100">Apellidos <span class="text-xs text-gray-500">(máx. <?php echo $maxApellidos; ?>)</span></p>
                                <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-600 dark:placeholder:text-gray-400 p-3 text-base font-normal transition-all duration-300"
                                       type="text" name="apellidos" value="<?php echo $apellidosUsuario; ?>" 
                                       maxlength="<?php echo $maxApellidos; ?>" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+" required />
                            </label>
                            <label class="flex flex-col w-full md:col-span-2">
                                <p class="text-base font-medium leading-normal pb-2 text-gray-900 dark:text-gray-100">Email</p>
                                <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-600 dark:placeholder:text-gray-400 p-3 text-base font-normal transition-all duration-300"
                                       type="email" name="email" value="<?php echo $emailUsuario; ?>" 
                                       maxlength="<?php echo $maxEmail; ?>" required />
                            </label>
                        </div>
                        <div class="flex justify-end gap-4 mt-6 border-t border-white/20 dark:border-white/10 pt-6">
                            <button class="glass-button flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-11 px-5 text-white text-sm font-bold
                                    transition-all duration-300 hover:scale-105 dark:focus:ring-offset-slate-900" type="submit">
                                <span class="truncate relative z-10">Guardar Cambios</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="p-6 rounded-xl shadow-sm liquid-glass-strong water-drop-effect">
                    <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">Cambiar Contraseña</h2>
                    <form action="change_password.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Security::escapeHtml($csrf_token); ?>">
                        <div class="grid grid-cols-1 gap-6">
                            <label class="flex flex-col w-full">
                                <p class="text-base font-medium leading-normal pb-2 text-gray-900 dark:text-gray-100">Contraseña Actual</p>
                                <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-600 dark:placeholder:text-gray-400 p-3 text-base font-normal transition-all duration-300"
                                       placeholder="••••••••" type="password" name="current_password" 
                                       maxlength="<?php echo $maxPassword; ?>" autocomplete="current-password" required />
                            </label>
                            <label class="flex flex-col w-full">
                                <p class="text-base font-medium leading-normal pb-2 text-gray-900 dark:text-gray-100">Nueva Contraseña <span class="text-xs text-gray-500">(<?php echo $minPassword; ?>-<?php echo $maxPassword; ?> caracteres)</span></p>
                                <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-600 dark:placeholder:text-gray-400 p-3 text-base font-normal transition-all duration-300"
                                       placeholder="Mínimo 1 mayúscula, 1 minúscula y 1 número" type="password" name="new_password" 
                                       minlength="<?php echo $minPassword; ?>" maxlength="<?php echo $maxPassword; ?>" autocomplete="new-password" required />
                            </label>
                            <label class="flex flex-col w-full">
                                <p class="text-base font-medium leading-normal pb-2 text-gray-900 dark:text-gray-100">Confirmar Nueva Contraseña</p>
                                <input class="glass-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-900 dark:text-gray-100 focus:outline-0 focus:ring-2 focus:ring-primary/50 h-12 placeholder:text-gray-600 dark:placeholder:text-gray-400 p-3 text-base font-normal transition-all duration-300"
                                       placeholder="••••••••" type="password" name="confirm_password" 
                                       minlength="<?php echo $minPassword; ?>" maxlength="<?php echo $maxPassword; ?>" autocomplete="new-password" required />
                            </label>
                        </div>
                        <div class="flex justify-end gap-4 mt-6 border-t border-white/20 dark:border-white/10 pt-6">
                            <button class="glass-button flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-11 px-5 text-white text-sm font-bold
                                    transition-all duration-300 hover:scale-105 dark:focus:ring-offset-slate-900" type="submit">
                                <span class="truncate relative z-10">Actualizar Contraseña</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
   </div>
</main>
</div>

<script>
// Script para la vista previa de la foto de perfil
document.getElementById('profile_pic_input')?.addEventListener('change', function(event) {
    const [file] = event.target.files;
    if (file) {
        const preview = document.getElementById('profilePicPreview');
        const icon = document.getElementById('profilePicIcon');
        
        // Asegurarse de que el preview (img) exista
        if (preview) {
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
        }
        
        // Ocultar el icono si existe
        if (icon) {
            icon.classList.add('hidden');
        }
    }
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const content = document.getElementById('dashboard-content');
        if (content) {
            content.classList.remove('hidden');
        }
    });
</script>

<!-- Cursor Spotlight Script -->
<script src="js/cursor-spotlight.js"></script>

<!-- Script de Validación de Formularios -->
<script src="js/form-validation.js"></script>

</body>
</html>