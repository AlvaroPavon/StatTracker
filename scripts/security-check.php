#!/usr/bin/env php
<?php
/**
 * Script de verificación de seguridad local
 * Ejecutar: php scripts/security-check.php
 */

echo "🔐 StatTracker - Verificación de Seguridad\n";
echo str_repeat("=", 50) . "\n\n";

$errors = [];
$warnings = [];
$passed = [];

// 1. Verificar versión de PHP
$phpVersion = PHP_VERSION;
if (version_compare($phpVersion, '7.4', '<')) {
    $errors[] = "❌ PHP {$phpVersion} es muy antiguo. Se requiere PHP 7.4+";
} else {
    $passed[] = "✅ PHP {$phpVersion}";
}

// 2. Verificar extensiones necesarias
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'session'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        $passed[] = "✅ Extensión {$ext} instalada";
    } else {
        $errors[] = "❌ Extensión {$ext} no instalada";
    }
}

// 3. Verificar configuración de PHP
$phpConfigs = [
    'display_errors' => ['expected' => false, 'severity' => 'warning'],
    'expose_php' => ['expected' => false, 'severity' => 'warning'],
    'allow_url_fopen' => ['expected' => true, 'severity' => 'info'],
    'allow_url_include' => ['expected' => false, 'severity' => 'error'],
];

foreach ($phpConfigs as $config => $info) {
    $value = ini_get($config);
    $actual = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool)$value;
    
    if ($actual === $info['expected']) {
        $passed[] = "✅ {$config} = " . ($actual ? 'On' : 'Off');
    } else {
        $msg = "{$config} debería ser " . ($info['expected'] ? 'On' : 'Off');
        if ($info['severity'] === 'error') {
            $errors[] = "❌ {$msg}";
        } else {
            $warnings[] = "⚠️ {$msg}";
        }
    }
}

// 4. Verificar permisos de directorios
$dirs = [
    'uploads' => ['writable' => true, 'readable' => true],
    'logs' => ['writable' => true, 'readable' => false],
    'src' => ['writable' => false, 'readable' => true],
];

$basePath = dirname(__DIR__);
foreach ($dirs as $dir => $perms) {
    $fullPath = $basePath . '/' . $dir;
    if (!is_dir($fullPath)) {
        $warnings[] = "⚠️ Directorio {$dir}/ no existe";
        continue;
    }
    
    if ($perms['writable'] && !is_writable($fullPath)) {
        $errors[] = "❌ {$dir}/ debería ser escribible";
    } elseif (!$perms['writable'] && is_writable($fullPath)) {
        $warnings[] = "⚠️ {$dir}/ no debería ser escribible en producción";
    } else {
        $passed[] = "✅ {$dir}/ permisos correctos";
    }
}

// 5. Verificar archivos de seguridad
$securityFiles = [
    '.htaccess' => 'Configuración de Apache',
    'src/.htaccess' => 'Protección de código fuente',
    'logs/.htaccess' => 'Protección de logs',
    'uploads/.htaccess' => 'Protección de uploads',
];

foreach ($securityFiles as $file => $desc) {
    $fullPath = $basePath . '/' . $file;
    if (file_exists($fullPath)) {
        $passed[] = "✅ {$file} ({$desc})";
    } else {
        $warnings[] = "⚠️ {$file} no existe ({$desc})";
    }
}

// 6. Verificar clases de seguridad
$securityClasses = [
    'App\\Security',
    'App\\SecurityHeaders',
    'App\\SecurityAudit',
    'App\\InputSanitizer',
    'App\\RateLimiter',
];

require_once $basePath . '/vendor/autoload.php';

foreach ($securityClasses as $class) {
    if (class_exists($class)) {
        $passed[] = "✅ Clase {$class} disponible";
    } else {
        $errors[] = "❌ Clase {$class} no encontrada";
    }
}

// 7. Verificar funciones peligrosas
$dangerousFunctions = ['eval', 'exec', 'shell_exec', 'system', 'passthru'];
$enabledDangerous = [];

foreach ($dangerousFunctions as $func) {
    if (function_exists($func)) {
        $disabled = explode(',', ini_get('disable_functions'));
        if (!in_array($func, array_map('trim', $disabled))) {
            $enabledDangerous[] = $func;
        }
    }
}

if (!empty($enabledDangerous)) {
    $warnings[] = "⚠️ Funciones peligrosas habilitadas: " . implode(', ', $enabledDangerous);
} else {
    $passed[] = "✅ Funciones peligrosas deshabilitadas o no disponibles";
}

// Mostrar resultados
echo "📋 RESULTADOS\n";
echo str_repeat("-", 50) . "\n\n";

if (!empty($errors)) {
    echo "🔴 ERRORES (" . count($errors) . "):\n";
    foreach ($errors as $error) {
        echo "   {$error}\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "🟡 ADVERTENCIAS (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "   {$warning}\n";
    }
    echo "\n";
}

echo "🟢 PASADOS (" . count($passed) . "):\n";
foreach ($passed as $pass) {
    echo "   {$pass}\n";
}

echo "\n" . str_repeat("=", 50) . "\n";

$total = count($errors) + count($warnings) + count($passed);
$score = round((count($passed) / $total) * 100);

echo "📊 Puntuación de seguridad: {$score}%\n";

if (empty($errors)) {
    echo "✅ El sistema cumple los requisitos mínimos de seguridad.\n";
    exit(0);
} else {
    echo "❌ El sistema tiene problemas de seguridad que deben resolverse.\n";
    exit(1);
}
