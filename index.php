<?php
// 1. Iniciar la sesión ANTES de cualquier salida HTML
session_start();

// 2. Refinamiento: Redirigir si el usuario ya está logueado
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit; // Detener la ejecución del script
}

// 3. REFINAMIENTO DE SEGURIDAD (CSRF): Generar Token
// Genera un token aleatorio y único y lo guarda en la sesión.
// hash_equals() previene ataques de temporización al comparar tokens.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login y Registro - StatTracker</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f4f5f7; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { display: flex; flex-wrap: wrap; gap: 40px; justify-content: center; padding: 20px; }
        .form-box { background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); width: 320px; box-sizing: border-box; }
        h2 { text-align: center; margin-top: 0; margin-bottom: 25px; color: #333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        .form-group input { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; }
        .btn { width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: 600; }
        .btn:hover { background-color: #0056b3; }
        .message { padding: 10px; border-radius: 4px; text-align: center; margin-bottom: 15px; font-size: 14px; }
        .message.error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .message.success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>

    <div class="container">
        
        <div class="form-box">
            <h2>Registro</h2>
            
            <?php 
            if (isset($_GET['reg_error'])): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($_GET['reg_error']); ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="reg_nombre">Nombre:</label>
                    <input type="text" id="reg_nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="reg_apellidos">Apellidos:</label>
                    <input type="text" id="reg_apellidos" name="apellidos" required>
                </div>
                <div class="form-group">
                    <label for="reg_email">Email (será tu login):</label>
                    <input type="email" id="reg_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="reg_password">Contraseña:</label>
                    <input type="password" id="reg_password" name="password" required minlength="8">
                </div>
                <button type="submit" class="btn">Registrarse</button>
            </form>
        </div>

        <div class="form-box">
            <h2>Login</h2>

            <?php 
            if (isset($_GET['login_error'])): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($_GET['login_error']); ?>
                </div>
            <?php endif; ?>

            <?php 
            if (isset($_GET['success'])): ?>
                <div class="message success">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="login_email">Email:</label>
                    <input type="email" id="login_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="login_password">Contraseña:</label>
                    <input type="password" id="login_password" name="password" required>
                </div>
                <button type="submit" class="btn">Iniciar Sesión</button>
            </form>
        </div>
    </div>

</body>
</html>