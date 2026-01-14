<?php
/**
 * security_init.php - Inicializador de Seguridad COMPLETO
 * Este archivo debe incluirse AL PRINCIPIO de cada script PHP
 * Activa TODAS las capas de protección
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
use App\AdvancedProtection;
use App\SecurityAudit;
use App\ErrorHandler;
use App\UltimateShield;
use App\SupplyChainGuard;
use App\CryptoFortress;
use App\ImpenetrableDefense;
use App\TwoFactorAuth;

// ==================== FASE 0: Verificaciones Críticas de Integridad ====================

// Verificar integridad criptográfica del sistema
$cryptoCheck = CryptoFortress::verifyCryptoIntegrity();
if (!$cryptoCheck['valid']) {
    error_log('CRITICAL: Crypto integrity check failed - ' . implode(', ', $cryptoCheck['errors']));
    http_response_code(503);
    exit('System security check failed');
}

// ==================== FASE 0.5: Defensa Impenetrable ====================

// Verificar IP en rangos bloqueados
$clientIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
if (ImpenetrableDefense::isIpInBlockedRange($clientIp)) {
    http_response_code(403);
    exit('Access denied');
}

// Control de tasa global (anti-DDoS)
if (!ImpenetrableDefense::checkGlobalRateLimit()) {
    http_response_code(429);
    header('Retry-After: 60');
    exit('Too many requests');
}

// ==================== FASE 0.6: Manejador de Errores Seguro ====================

// Inicializar manejador de errores (PRIMERO)
ErrorHandler::init();

// ==================== FASE 1: Configuración inicial ====================

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Configuración de errores (NUNCA mostrar en producción)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');
error_reporting(E_ALL);

// Limitar información expuesta
ini_set('expose_php', 0);

// Configuración adicional de seguridad PHP
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

// ==================== FASE 2: UltimateShield - Máxima Protección ====================

// Iniciar sesión primero para UltimateShield
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ejecutar TODAS las verificaciones de seguridad
$ultimateResult = UltimateShield::protect();

// Si se detectan amenazas, bloquear INMEDIATAMENTE
if (!$ultimateResult['safe']) {
    UltimateShield::block($ultimateResult['threats']);
    exit; // Nunca llegará aquí pero por seguridad
}

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Configuración de errores (NUNCA mostrar en producción)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');
error_reporting(E_ALL);

// Limitar información expuesta
ini_set('expose_php', 0);

// ==================== FASE 2: Validaciones Pre-WAF ====================

// Verificar Host Header Injection
if (!AdvancedProtection::validateHostHeader()) {
    http_response_code(400);
    exit('Bad Request');
}

// Verificar HTTP Parameter Pollution
if (!AdvancedProtection::checkParameterPollution()) {
    http_response_code(400);
    exit('Bad Request');
}

// Verificar timing de request (Slow HTTP attacks)
if (!AdvancedProtection::checkRequestTiming()) {
    http_response_code(408);
    exit('Request Timeout');
}

// ==================== FASE 3: WAF - Análisis de petición ====================

// Analizar la petición entrante con el Web Application Firewall
$firewallResult = SecurityFirewall::analyze();

// Si la petición es maliciosa, bloquear
if (!$firewallResult['safe']) {
    // Si la IP está bloqueada o se detectaron amenazas críticas
    if ($firewallResult['blocked']) {
        SecurityFirewall::blockResponse();
        exit;
    }
    
    // Para amenazas no críticas, podemos continuar pero con logging
    // O podemos ser más estrictos y bloquear todo:
    // SecurityFirewall::blockResponse();
    // exit;
}

// ==================== FASE 4: Headers de Seguridad ====================

// Aplicar TODOS los headers de seguridad HTTP
SecurityHeaders::apply();

// ==================== FASE 5: Sesión Segura ====================

// Iniciar sesión con todas las protecciones
SessionManager::start();

// ==================== FASE 6: Validaciones Post-Sesión ====================

// Verificar que la sesión es válida si el usuario está autenticado
if (isset($_SESSION['user_id'])) {
    if (!SessionManager::validate()) {
        // Sesión comprometida - destruir y redirigir
        $compromisedUserId = $_SESSION['user_id'];
        SessionManager::destroy();
        
        SecurityAudit::log('SESSION_INVALIDATED', $compromisedUserId, [
            'reason' => 'validation_failed'
        ], 'WARNING');
        
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
}

// Detectar proxy anónimo (solo logging, no bloquear)
if (AdvancedProtection::detectAnonymousProxy()) {
    // Podríamos añadir verificación adicional aquí
}

// ==================== FASE 7: Funciones Helper Globales ====================

/**
 * Verifica CSRF en peticiones POST - OBLIGATORIO
 */
function verify_csrf_or_die(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        // También verificar en JSON body para APIs
        if ($token === null) {
            $input = file_get_contents('php://input');
            if (!empty($input)) {
                $data = json_decode($input, true);
                $token = $data['csrf_token'] ?? $data['token'] ?? null;
            }
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
 * Requiere autenticación - redirige si no está logueado
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
 * Obtiene input sanitizado de forma segura
 */
function get_safe_input(string $key, string $method = 'POST', string $type = 'string'): mixed
{
    $source = match(strtoupper($method)) {
        'POST' => $_POST,
        'GET' => $_GET,
        'COOKIE' => $_COOKIE,
        default => $_REQUEST
    };
    
    $value = $source[$key] ?? null;
    
    if ($value === null) {
        return null;
    }
    
    return match($type) {
        'int' => \App\InputSanitizer::sanitizeInt($value),
        'float' => \App\InputSanitizer::sanitizeFloat($value),
        'email' => \App\InputSanitizer::sanitizeEmail($value),
        'url' => \App\InputSanitizer::sanitizeUrl($value),
        'html' => \App\InputSanitizer::sanitizeForHtml($value),
        default => \App\InputSanitizer::sanitizeString($value)
    };
}

/**
 * Valida un ID numérico de forma segura
 */
function get_safe_id(string $key, string $method = 'POST'): ?int
{
    $value = get_safe_input($key, $method, 'int');
    return AdvancedProtection::validateNumericId($value);
}

/**
 * Valida y sanitiza una URL de redirección
 */
function get_safe_redirect(string $key = 'redirect'): string
{
    $url = $_GET[$key] ?? $_POST[$key] ?? 'index.php';
    return AdvancedProtection::validateRedirectUrl($url);
}

/**
 * Adquiere un lock para operaciones críticas
 */
function acquire_operation_lock(string $operation): bool
{
    return AdvancedProtection::acquireLock($operation);
}

/**
 * Libera un lock de operación
 */
function release_operation_lock(string $operation): void
{
    AdvancedProtection::releaseLock($operation);
}

/**
 * Envía respuesta JSON de forma segura
 */
function send_json(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

/**
 * Envía respuesta de error JSON
 */
function send_error(string $message, int $status = 400): void
{
    send_json(['success' => false, 'message' => $message], $status);
}

/**
 * Registra un evento de seguridad
 */
function log_security_event(string $event, array $details = [], string $severity = 'INFO'): void
{
    SecurityAudit::log($event, $_SESSION['user_id'] ?? null, $details, $severity);
}
