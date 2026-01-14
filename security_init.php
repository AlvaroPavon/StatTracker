<?php
/**
 * security_init.php - Inicializador de Seguridad
 * Este archivo debe incluirse AL PRINCIPIO de cada script PHP
 * Activa el WAF (Web Application Firewall) y las protecciones de sesión
 * 
 * @package App
 */

// Evitar acceso directo
if (basename($_SERVER['SCRIPT_FILENAME']) === 'security_init.php') {
    http_response_code(403);
    exit('Acceso denegado');
}

// Cargar autoloader si no está cargado
if (!class_exists('App\\SecurityFirewall')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use App\SecurityFirewall;
use App\SecurityHeaders;
use App\SessionManager;

// ==================== FASE 1: Configuración inicial ====================

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Configuración de errores (NO mostrar en producción)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');
error_reporting(E_ALL);

// Limitar información expuesta
ini_set('expose_php', 0);

// ==================== FASE 2: WAF - Análisis de petición ====================

// Analizar la petición entrante
$firewallResult = SecurityFirewall::analyze();

// Si la petición es maliciosa, bloquear
if (!$firewallResult['safe']) {
    // Si la IP está bloqueada o se detectaron amenazas críticas
    if ($firewallResult['blocked']) {
        SecurityFirewall::blockResponse();
        exit;
    }
    
    // Si hay amenazas pero no críticas, continuar pero con advertencia
    // (podríamos también bloquear, dependiendo de la política)
}

// ==================== FASE 3: Headers de Seguridad ====================

// Aplicar headers de seguridad HTTP
SecurityHeaders::apply();

// ==================== FASE 4: Sesión Segura ====================

// Iniciar sesión segura
SessionManager::start();

// ==================== FASE 5: Validaciones adicionales ====================

// Verificar que la sesión es válida si el usuario está autenticado
if (isset($_SESSION['user_id']) && !SessionManager::validate()) {
    // Sesión comprometida - destruir y redirigir
    SessionManager::destroy();
    
    // Solo redirigir si no es una petición AJAX
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Location: index.php?error=' . urlencode('Sesión inválida. Por favor, inicie sesión nuevamente.'));
        exit;
    } else {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Sesión expirada']);
        exit;
    }
}

// ==================== FASE 6: Protección CSRF global ====================

/**
 * Helper para verificar CSRF en peticiones POST
 */
function verify_csrf_or_die(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        // También verificar en JSON body para APIs
        if ($token === null) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $token = $data['csrf_token'] ?? $data['token'] ?? null;
        }
        
        if (!\App\Security::validateCsrfToken($token)) {
            \App\SecurityAudit::logCsrfInvalid($_SESSION['user_id'] ?? null);
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido']);
            } else {
                http_response_code(403);
                echo 'Error de seguridad: Token CSRF inválido';
            }
            exit;
        }
    }
}

/**
 * Helper para requerir autenticación
 */
function require_auth(): void
{
    if (!SessionManager::isAuthenticated()) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
        } else {
            header('Location: index.php?error=' . urlencode('Debe iniciar sesión'));
        }
        exit;
    }
}

/**
 * Helper para obtener input sanitizado
 */
function get_safe_input(string $key, string $method = 'POST', string $type = 'string'): mixed
{
    $source = $method === 'POST' ? $_POST : $_GET;
    $value = $source[$key] ?? null;
    
    if ($value === null) {
        return null;
    }
    
    return match($type) {
        'int' => \App\InputSanitizer::sanitizeInt($value),
        'float' => \App\InputSanitizer::sanitizeFloat($value),
        'email' => \App\InputSanitizer::sanitizeEmail($value),
        'url' => \App\InputSanitizer::sanitizeUrl($value),
        default => \App\InputSanitizer::sanitizeString($value)
    };
}
