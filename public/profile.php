<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Session;
use App\User;
use PDOException;

Session::init();

// 1. Autenticación
if (!Session::has('user_id')) {
    header('Location: index.php');
    exit;
}

$user_id = Session::get('user_id');

// 2. Generar Token CSRF para los formularios
$csrf_token = Session::generateCsrfToken();

// 3. Obtener TODOS los datos del usuario para rellenar el formulario
$nombreUsuario = '';
$apellidosUsuario = '';
$emailUsuario = '';
$profilePic = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iIzZCNzI4MCI+CiAgPHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00cy0xLjc5LTQtNC00LTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYy0yLjY3IDAtOCAxLjM0LTggNHYyaDE2di0yYzAtMi42Ni01LjMzLTQtOC00eiIvPgo8L3N2Zz4='; // Default SVG

try {
    $pdo = Database::getInstance();
    $userRepo = new User($pdo);
    $usuario = $userRepo->findById((int)$user_id);
    
    if ($usuario) {
        $nombreUsuario = htmlspecialchars($usuario['nombre']);
        $apellidosUsuario = htmlspecialchars($usuario['apellidos']);
        $emailUsuario = htmlspecialchars($usuario['email']);
        
        if (!empty($usuario['profile_pic']) && file_exists(__DIR__ . '/uploads/' . $usuario['profile_pic'])) {
            $profilePic = 'uploads/' . $usuario['profile_pic'];
        }
    } else {
        // Esto no debería pasar si la sesión es válida, pero es un buen fallback
        Session::destroy();
        header('Location: index.php?login_error=Error de usuario.');
        exit;
    }

} catch (PDOException $e) {
    error_log('Error en profile.php (get user): ' . $e->getMessage());
    header('Location: dashboard.php?error=Error al cargar el perfil.');
    exit;
}
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Mi Perfil - StatTracker</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    />
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .material-symbols-outlined.fill { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        
        .sidebar-profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #e0e6ed;
        }
        
        .profile-pic-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #e0e6ed;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        .profile-pic-large:hover {
            transform: scale(1.05);
        }

        .form-input-profile {
            display: block;
            width: 100%;
            border-radius: 0.25rem;
            border: 1px solid #E0E6ED; 
            padding: 0.75rem; 
            font-size: 1rem;
            color: #333333; 
            background-color: #F4F7FA; 
            transition: all 0.3s;
        }
        .form-input-profile:focus {
            outline: 2px solid #4A90E2; 
            border-color: #4A90E2;
        }
        
        :root {
            --animate-duration: 0.8s;
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
        },
      }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">
<div class="flex h-screen w-full">
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
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-text-light dark:text-text-dark hover:bg-subtle-light dark:hover:bg-subtle-dark transition-all duration-300" href="dashboard.php">
                    <span class="material-symbols-outlined">dashboard</span>
                    <p class="text-sm font-medium leading-normal">Dashboard</p>
                </a>
                <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary transition-all duration-300" href="profile.php">
                    <span class="material-symbols-outlined fill">person</span>
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
                <h1 class="text-text-light dark:text-text-dark text-3xl font-bold leading-tight tracking-tight">Información Personal</h1>
                <p class="text-secondary-text-light dark:text-secondary-text-dark text-base font-normal leading-normal">Actualiza tus datos personales y contraseña.</p>
            </div>
        </header>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg border border-red-300" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg border border-green-300" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <section class="rounded-xl border border-border-light dark:border-border-dark bg-content-light dark:bg-content-dark
                        transition-all duration-300 hover:shadow-xl hover:-translate-y-1
                        animate__animated animate__fadeInUp">
        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <img src="<?php echo $profilePic; ?>?v=<?php echo time(); ?>" alt="Foto de perfil" class="profile-pic-large">
                        <div>
                            <h2 class="text-xl font-bold text-text-light dark:text-text-dark">Actualizar Foto</h2>
                            <input type="file" name="profile_pic" accept="image/png, image/jpeg" class="mt-2 text-sm transition-all duration-300">
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-border-light dark:border-border-dark p-6">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium leading-6 text-text-light dark:text-text-dark" for="nombre">Nombre</label>
                            <div class="mt-2">
                                <input type="text" name="nombre" id="nombre" class="form-input-profile" value="<?php echo $nombreUsuario; ?>" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium leading-6 text-text-light dark:text-text-dark" for="apellidos">Apellidos</label>
                            <div class="mt-2">
                                <input type="text" name="apellidos" id="apellidos" class="form-input-profile" value="<?php echo $apellidosUsuario; ?>" required>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium leading-6 text-text-light dark:text-text-dark" for="email">Email</label>
                            <div class="mt-2">
                                <input type="email" name="email" id="email" class="form-input-profile" value="<?php echo $emailUsuario; ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-end gap-x-4 border-t border-border-light dark:border-border-dark px-6 py-4">
                    <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-primary/90
                                           transition-all duration-300 hover:scale-105">Guardar Cambios</button>
                </div>
            </form>
        </section>

        <section class="mt-8 rounded-xl border border-border-light dark:border-border-dark bg-content-light dark:bg-content-dark
                        transition-all duration-300 hover:shadow-xl hover:-translate-y-1
                        animate__animated animate__fadeInUp"
                        style="--animate-delay: 0.2s;">
        <form action="change_password.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="p-6">
                    <h2 class="text-xl font-bold text-text-light dark:text-text-dark">Cambiar Contraseña</h2>
                </div>
                
                <div class="border-t border-border-light dark:border-border-dark p-6">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-6">
                        <div>
                            <label class="block text-sm font-medium leading-6 text-text-light dark:text-text-dark" for="old_password">Contraseña Anterior</label>
                            <div class="mt-2">
                                <input type="password" name="old_password" id="old_password" class="form-input-profile" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium leading-6 text-text-light dark:text-text-dark" for="new_password">Nueva Contraseña</label>
                            <div class="mt-2">
                                <input type="password" name="new_password" id="new_password" class="form-input-profile" minlength="8" required>
                                <p class="mt-1 text-sm text-secondary-text-light">Debe tener al menos 8 caracteres, 1 mayúscula, 1 minúscula y 1 número.</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium leading-6 text-text-light dark:text-text-dark" for="confirm_new_password">Confirmar Nueva Contraseña</label>
                            <div class="mt-2">
                                <input type="password" name="confirm_new_password" id="confirm_new_password" class="form-input-profile" minlength="8" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-end gap-x-4 border-t border-border-light dark:border-border-dark px-6 py-4">
                    <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-primary/90
                                           transition-all duration-300 hover:scale-105">Actualizar Contraseña</button>
                </div>
            </form>
        </section>

    </div>
</main>
</div>

</body>
</html>