<?php
/**
 * StatTracker API v1
 * Punto de entrada único para todas las peticiones API
 * 
 * Endpoints disponibles:
 * - POST   /api/auth/register     Registro de usuario
 * - POST   /api/auth/login        Login (devuelve JWT)
 * - POST   /api/auth/logout       Logout
 * - GET    /api/metrics           Listar métricas
 * - GET    /api/metrics/:id       Ver métrica
 * - POST   /api/metrics           Crear métrica
 * - PUT    /api/metrics/:id       Actualizar métrica
 * - DELETE /api/metrics/:id       Eliminar métrica
 * - GET    /api/profile           Ver perfil
 * - PUT    /api/profile           Actualizar perfil
 * - POST   /api/profile/password  Cambiar contraseña
 */

// CORS y headers
require_once __DIR__ . '/config/cors.php';

// Helper function para headers (si no existe)
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

// Obtener método y URI
$method = $_SERVER['REQUEST_METHOD'];
$request = $_SERVER['REQUEST_URI'];

// Limpiar URI de query strings y normalizar
$request = explode('?', $request)[0];
$request = rtrim($request, '/');

// Identificar ruta base de la API
$basePath = '/proyecto_imc/api';
if (strpos($request, $basePath) !== 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint no encontrado']);
    exit;
}

// Extraer path relativo a /api
$path = substr($request, strlen($basePath));
$path = trim($path, '/');

// Router simple
$route = $method . ' ' . $path;

// Match rutas estáticas
$routes = [
    'POST auth/register' => ['AuthController', 'register'],
    'POST auth/login' => ['AuthController', 'login'],
    'POST auth/logout' => ['AuthController', 'logout'],
    'GET metrics' => ['MetricsController', 'index'],
    'POST metrics' => ['MetricsController', 'store'],
    'GET profile' => ['ProfileController', 'show'],
    'PUT profile' => ['ProfileController', 'update'],
    'POST profile/password' => ['ProfileController', 'changePassword'],
];

// Match rutas dinámicas con ID
if (preg_match('#^(metrics)/(\d+)$#', $path, $matches)) {
    $controller = $matches[1] . 'Controller';
    $id = $matches[2];
    
    $controllerClass = ucfirst($controller);
    
    require_once __DIR__ . '/controllers/' . $controllerClass . '.php';
    $controllerInstance = new $controllerClass();
    
    switch ($method) {
        case 'GET':
            $controllerInstance->show($id);
            break;
        case 'PUT':
            $controllerInstance->update($id);
            break;
        case 'DELETE':
            $controllerInstance->destroy($id);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
    exit;
}

// Ejecutar ruta matcheada
if (isset($routes[$route])) {
    [$controllerName, $action] = $routes[$route];
    $controllerClass = $controllerName;
    
    require_once __DIR__ . '/controllers/' . $controllerClass . '.php';
    $controllerInstance = new $controllerClass();
    $controllerInstance->$action();
    exit;
}

// 404 - Ruta no encontrada
http_response_code(404);
echo json_encode([
    'error' => 'Endpoint no encontrado',
    'available_endpoints' => [
        'POST /api/auth/register',
        'POST /api/auth/login',
        'POST /api/auth/logout',
        'GET /api/metrics',
        'GET /api/metrics/:id',
        'POST /api/metrics',
        'PUT /api/metrics/:id',
        'DELETE /api/metrics/:id',
        'GET /api/profile',
        'PUT /api/profile',
        'POST /api/profile/password'
    ]
]);
