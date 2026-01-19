<?php
/**
 * diagnostico.php - Herramienta de diagnóstico para StatTracker
 * Usa este archivo para verificar que todo esté configurado correctamente
 * ¡ELIMINAR DESPUÉS DE USAR EN PRODUCCIÓN!
 */

// Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Diagnóstico StatTracker</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.ok { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
.section { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
h1 { color: #333; }
h2 { color: #4A90E2; border-bottom: 2px solid #4A90E2; padding-bottom: 10px; }
pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style></head><body>";

echo "<h1>🔧 Diagnóstico de StatTracker</h1>";

// 1. Versión de PHP
echo "<div class='section'>";
echo "<h2>1. PHP</h2>";
$phpVersion = phpversion();
echo "<p>Versión de PHP: <strong>$phpVersion</strong> ";
if (version_compare($phpVersion, '8.0.0', '>=')) {
    echo "<span class='ok'>✓ OK</span>";
} else {
    echo "<span class='error'>✗ Se requiere PHP 8.0+</span>";
}
echo "</p>";
echo "</div>";

// 2. Extensiones PHP requeridas
echo "<div class='section'>";
echo "<h2>2. Extensiones PHP</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'session'];
$optional_extensions = ['sodium', 'gd'];

echo "<h3>Requeridas:</h3><ul>";
foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? "<span class='ok'>✓ Cargada</span>" : "<span class='error'>✗ NO CARGADA</span>";
    echo "<li><strong>$ext</strong>: $status</li>";
}
echo "</ul>";

echo "<h3>Opcionales (recomendadas):</h3><ul>";
foreach ($optional_extensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? "<span class='ok'>✓ Cargada</span>" : "<span class='warning'>⚠ No cargada (funciona sin ella)</span>";
    echo "<li><strong>$ext</strong>: $status</li>";
}
echo "</ul>";

// Verificar SHA3
echo "<h3>Algoritmos de Hash:</h3><ul>";
$sha3Available = in_array('sha3-512', hash_algos());
if ($sha3Available) {
    echo "<li><strong>SHA3-512</strong>: <span class='ok'>✓ Disponible</span></li>";
} else {
    echo "<li><strong>SHA3-512</strong>: <span class='warning'>⚠ No disponible (usará SHA-512)</span></li>";
}
echo "<li><strong>SHA-512</strong>: <span class='ok'>✓ Siempre disponible</span></li>";
echo "</ul>";
echo "</div>";

// 3. Archivos críticos
echo "<div class='section'>";
echo "<h2>3. Archivos Críticos</h2>";
$critical_files = [
    'vendor/autoload.php' => 'Autoloader de Composer',
    'database_connection.php' => 'Conexión a BD (legacy)',
    'db.php' => 'Conexión a BD',
    'security_init.php' => 'Inicialización de seguridad',
    'src/Auth.php' => 'Clase de autenticación',
    'src/Security.php' => 'Clase de seguridad',
    'src/SessionManager.php' => 'Gestor de sesiones',
    'src/CryptoFortress.php' => 'Criptografía',
    'src/SimpleCaptcha.php' => 'CAPTCHA',
    'src/LoginAlertSystem.php' => 'Alertas de login',
    'js/session-timeout.js' => 'Timeout de sesión (JS)',
    'keep_alive.php' => 'Endpoint keep alive',
];

echo "<ul>";
foreach ($critical_files as $file => $description) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $status = $exists ? "<span class='ok'>✓ Existe</span>" : "<span class='error'>✗ NO EXISTE</span>";
    echo "<li><strong>$file</strong> ($description): $status</li>";
}
echo "</ul>";
echo "</div>";

// 4. Directorios con permisos de escritura
echo "<div class='section'>";
echo "<h2>4. Permisos de Escritura</h2>";
$writable_dirs = ['logs', 'uploads'];

echo "<ul>";
foreach ($writable_dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) {
        echo "<li><strong>$dir/</strong>: <span class='warning'>⚠ No existe (se creará automáticamente)</span></li>";
    } else {
        $writable = is_writable($path);
        $status = $writable ? "<span class='ok'>✓ Escribible</span>" : "<span class='error'>✗ NO ESCRIBIBLE</span>";
        echo "<li><strong>$dir/</strong>: $status</li>";
    }
}
echo "</ul>";
echo "</div>";

// 5. Verificar autoloader
echo "<div class='section'>";
echo "<h2>5. Autoloader de Composer</h2>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    try {
        require_once __DIR__ . '/vendor/autoload.php';
        echo "<p><span class='ok'>✓ Autoloader cargado correctamente</span></p>";
        
        // Verificar clases
        $classes_to_check = [
            'App\\Auth',
            'App\\Security', 
            'App\\SessionManager',
            'App\\CryptoFortress',
            'App\\SimpleCaptcha',
            'App\\LoginAlertSystem'
        ];
        
        echo "<h3>Clases disponibles:</h3><ul>";
        foreach ($classes_to_check as $class) {
            $exists = class_exists($class);
            $status = $exists ? "<span class='ok'>✓</span>" : "<span class='error'>✗</span>";
            echo "<li>$status $class</li>";
        }
        echo "</ul>";
        
    } catch (Exception $e) {
        echo "<p><span class='error'>✗ Error al cargar autoloader: " . htmlspecialchars($e->getMessage()) . "</span></p>";
    }
} else {
    echo "<p><span class='error'>✗ vendor/autoload.php no existe</span></p>";
    echo "<p><strong>Solución:</strong> Ejecuta <code>composer install</code> en el directorio del proyecto</p>";
}
echo "</div>";

// 6. Base de datos
echo "<div class='section'>";
echo "<h2>6. Conexión a Base de Datos</h2>";
if (file_exists(__DIR__ . '/db.php')) {
    try {
        // No incluir db.php directamente porque puede tener dependencias
        // Solo verificar que existe database_connection.php
        if (file_exists(__DIR__ . '/database_connection.php')) {
            echo "<p><span class='ok'>✓ Archivo de conexión existe</span></p>";
            echo "<p><strong>Nota:</strong> Verifica que los datos de conexión en <code>database_connection.php</code> sean correctos:</p>";
            echo "<pre>";
            echo "\$host = 'localhost';\n";
            echo "\$dbname = 'proyecto_imc';\n";
            echo "\$username = 'root';\n";
            echo "\$password = ''; // O tu contraseña de MySQL";
            echo "</pre>";
        }
    } catch (Exception $e) {
        echo "<p><span class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span></p>";
    }
} else {
    echo "<p><span class='error'>✗ db.php no existe</span></p>";
}
echo "</div>";

// 7. Configuración de sesiones
echo "<div class='section'>";
echo "<h2>7. Configuración de Sesiones</h2>";
$session_path = session_save_path();
echo "<p>Directorio de sesiones: <code>" . ($session_path ?: 'Por defecto del sistema') . "</code></p>";

if ($session_path && !is_writable($session_path)) {
    echo "<p><span class='warning'>⚠ El directorio de sesiones podría no ser escribible</span></p>";
} else {
    echo "<p><span class='ok'>✓ Sesiones configuradas</span></p>";
}
echo "</div>";

// 8. Resumen y pasos siguientes
echo "<div class='section'>";
echo "<h2>8. Pasos para Solucionar Problemas</h2>";
echo "<ol>";
echo "<li><strong>Si falta vendor/autoload.php:</strong><br>
      Abre una terminal en el directorio del proyecto y ejecuta:<br>
      <code>composer install</code></li>";
echo "<li><strong>Si faltan extensiones PHP:</strong><br>
      Edita <code>php.ini</code> en XAMPP y descomenta las extensiones necesarias:<br>
      <code>extension=pdo_mysql</code><br>
      <code>extension=mbstring</code><br>
      <code>extension=openssl</code><br>
      Luego reinicia Apache.</li>";
echo "<li><strong>Si hay error de base de datos:</strong><br>
      - Asegúrate de que MySQL esté corriendo<br>
      - Crea la base de datos: <code>CREATE DATABASE proyecto_imc;</code><br>
      - Importa el esquema: <code>mysql -u root -p proyecto_imc < database.sql</code></li>";
echo "<li><strong>Si hay errores de permisos:</strong><br>
      Asegúrate de que las carpetas <code>logs/</code> y <code>uploads/</code> tengan permisos de escritura</li>";
echo "</ol>";
echo "</div>";

// 9. Información del servidor
echo "<div class='section'>";
echo "<h2>9. Información del Servidor</h2>";
echo "<ul>";
echo "<li>Sistema operativo: " . PHP_OS . "</li>";
echo "<li>Software del servidor: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido') . "</li>";
echo "<li>Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Desconocido') . "</li>";
echo "<li>Directorio actual: " . __DIR__ . "</li>";
echo "</ul>";
echo "</div>";

echo "<p style='margin-top: 30px; padding: 15px; background: #fff3cd; border-radius: 8px;'>";
echo "⚠️ <strong>IMPORTANTE:</strong> Elimina este archivo (<code>diagnostico.php</code>) después de solucionar los problemas.";
echo "</p>";

echo "</body></html>";
