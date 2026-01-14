<?php
/**
 * Clase ErrorHandler - Manejo seguro de errores
 * Evita exposición de información sensible
 * @package App
 */

namespace App;

class ErrorHandler
{
    private static bool $initialized = false;
    
    /**
     * Inicializa el manejador de errores seguro
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }
        
        // Registrar manejador de errores personalizado
        set_error_handler([self::class, 'handleError']);
        
        // Registrar manejador de excepciones
        set_exception_handler([self::class, 'handleException']);
        
        // Registrar manejador de shutdown para errores fatales
        register_shutdown_function([self::class, 'handleShutdown']);
        
        self::$initialized = true;
    }
    
    /**
     * Maneja errores PHP
     */
    public static function handleError(
        int $errno, 
        string $errstr, 
        string $errfile, 
        int $errline
    ): bool {
        // No manejar errores suprimidos con @
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        // Convertir a excepción para manejo uniforme
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    
    /**
     * Maneja excepciones no capturadas
     */
    public static function handleException(\Throwable $e): void
    {
        // Loguear el error completo (solo en servidor)
        $logMessage = sprintf(
            "[%s] %s in %s:%d\nStack trace:\n%s",
            get_class($e),
            $e->getMessage(),
            self::sanitizePath($e->getFile()),
            $e->getLine(),
            self::sanitizeStackTrace($e->getTraceAsString())
        );
        
        error_log($logMessage);
        
        // Registrar en log de seguridad si parece un ataque
        if (self::looksLikeAttack($e)) {
            SecurityAudit::log('EXCEPTION_ATTACK_PATTERN', null, [
                'type' => get_class($e),
                'message' => substr($e->getMessage(), 0, 200)
            ], 'WARNING');
        }
        
        // Mostrar mensaje genérico al usuario
        self::showErrorPage($e);
    }
    
    /**
     * Maneja errores fatales en shutdown
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [
            E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE
        ])) {
            // Loguear error fatal
            error_log(sprintf(
                "[FATAL] %s in %s:%d",
                $error['message'],
                self::sanitizePath($error['file']),
                $error['line']
            ));
            
            // Si aún no se ha enviado output
            if (!headers_sent()) {
                self::showErrorPage(null, 500);
            }
        }
    }
    
    /**
     * Muestra página de error segura
     */
    private static function showErrorPage(?\Throwable $e, int $code = 500): void
    {
        // Limpiar cualquier output previo
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        http_response_code($code);
        
        // Para peticiones AJAX, devolver JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => self::getPublicMessage($code)
            ]);
            exit;
        }
        
        // Para peticiones normales, mostrar HTML
        header('Content-Type: text/html; charset=utf-8');
        ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - StatTracker</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #eee;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 500px;
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #e74c3c;
            text-shadow: 0 4px 20px rgba(231, 76, 60, 0.3);
        }
        .error-message {
            font-size: 1.5rem;
            margin: 1rem 0;
            color: #bbb;
        }
        .error-description {
            color: #888;
            margin-bottom: 2rem;
        }
        .back-button {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code"><?= $code ?></div>
        <h1 class="error-message"><?= self::getPublicMessage($code) ?></h1>
        <p class="error-description">
            Ha ocurrido un error inesperado. Por favor, inténtelo de nuevo más tarde.
        </p>
        <a href="index.php" class="back-button">Volver al inicio</a>
    </div>
</body>
</html>
        <?php
        exit;
    }
    
    /**
     * Obtiene mensaje público seguro según código de error
     */
    private static function getPublicMessage(int $code): string
    {
        return match($code) {
            400 => 'Solicitud incorrecta',
            401 => 'No autorizado',
            403 => 'Acceso denegado',
            404 => 'Página no encontrada',
            405 => 'Método no permitido',
            408 => 'Tiempo de espera agotado',
            429 => 'Demasiadas solicitudes',
            500 => 'Error interno del servidor',
            502 => 'Error de servidor',
            503 => 'Servicio no disponible',
            default => 'Ha ocurrido un error'
        };
    }
    
    /**
     * Sanitiza rutas de archivo para no exponer estructura
     */
    private static function sanitizePath(string $path): string
    {
        // Reemplazar ruta base con [ROOT]
        $basePath = dirname(__DIR__);
        return str_replace($basePath, '[ROOT]', $path);
    }
    
    /**
     * Sanitiza stack trace
     */
    private static function sanitizeStackTrace(string $trace): string
    {
        $basePath = dirname(__DIR__);
        $trace = str_replace($basePath, '[ROOT]', $trace);
        
        // Ocultar argumentos de funciones que podrían contener datos sensibles
        $trace = preg_replace('/\(.*?\)/', '(...)', $trace);
        
        return $trace;
    }
    
    /**
     * Detecta si la excepción parece ser resultado de un ataque
     */
    private static function looksLikeAttack(\Throwable $e): bool
    {
        $message = strtolower($e->getMessage());
        
        $attackPatterns = [
            'sql', 'injection', 'union', 'select',
            'script', 'javascript', 'xss',
            '../', 'traversal', 'passwd',
            'exec', 'system', 'shell'
        ];
        
        foreach ($attackPatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
}
