<?php
/**
 * Clase RateLimiter - Control avanzado de tasa de peticiones
 * Protección contra ataques de fuerza bruta y DoS
 * @package App
 */

namespace App;

class RateLimiter
{
    // Configuraciones por defecto
    private const DEFAULT_MAX_ATTEMPTS = 5;
    private const DEFAULT_WINDOW_SECONDS = 900; // 15 minutos
    private const DEFAULT_BLOCK_DURATION = 3600; // 1 hora
    
    // Configuraciones por tipo de acción
    private const LIMITS = [
        'login' => [
            'max_attempts' => 5,
            'window_seconds' => 900,
            'block_duration' => 1800,
        ],
        'register' => [
            'max_attempts' => 3,
            'window_seconds' => 3600,
            'block_duration' => 7200,
        ],
        'password_reset' => [
            'max_attempts' => 3,
            'window_seconds' => 3600,
            'block_duration' => 3600,
        ],
        'api' => [
            'max_attempts' => 100,
            'window_seconds' => 60,
            'block_duration' => 300,
        ],
        'file_upload' => [
            'max_attempts' => 10,
            'window_seconds' => 300,
            'block_duration' => 600,
        ],
    ];

    private string $action;
    private string $identifier;
    private \PDO $pdo;
    private bool $useDatabase;

    public function __construct(
        string $action,
        string $identifier,
        ?\PDO $pdo = null
    ) {
        $this->action = $action;
        $this->identifier = $this->hashIdentifier($identifier);
        $this->pdo = $pdo;
        $this->useDatabase = ($pdo !== null);
    }

    /**
     * Verifica si la acción está permitida
     */
    public function isAllowed(): array
    {
        $config = $this->getConfig();
        
        // Verificar bloqueo
        $blockInfo = $this->checkBlock();
        if ($blockInfo['blocked']) {
            return [
                'allowed' => false,
                'reason' => 'blocked',
                'remaining_time' => $blockInfo['remaining_time'],
                'message' => $this->formatBlockMessage($blockInfo['remaining_time'])
            ];
        }
        
        // Contar intentos
        $attempts = $this->getAttemptCount($config['window_seconds']);
        
        if ($attempts >= $config['max_attempts']) {
            // Aplicar bloqueo
            $this->applyBlock($config['block_duration']);
            
            return [
                'allowed' => false,
                'reason' => 'too_many_attempts',
                'attempts' => $attempts,
                'message' => 'Demasiados intentos. Por favor, espere antes de intentar de nuevo.'
            ];
        }
        
        return [
            'allowed' => true,
            'attempts' => $attempts,
            'remaining' => $config['max_attempts'] - $attempts
        ];
    }

    /**
     * Registra un intento
     */
    public function recordAttempt(bool $success = false): void
    {
        if ($this->useDatabase) {
            $this->recordAttemptDb($success);
        } else {
            $this->recordAttemptSession($success);
        }
        
        // Si fue exitoso, resetear intentos
        if ($success) {
            $this->reset();
        }
    }

    /**
     * Resetea los intentos (después de éxito)
     */
    public function reset(): void
    {
        if ($this->useDatabase) {
            $this->resetDb();
        } else {
            $this->resetSession();
        }
    }

    /**
     * Bloquea manualmente el identificador
     */
    public function block(int $duration = null): void
    {
        $config = $this->getConfig();
        $this->applyBlock($duration ?? $config['block_duration']);
    }

    // ==================== Implementación con Sesión ====================

    private function recordAttemptSession(bool $success): void
    {
        $key = $this->getSessionKey('attempts');
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }
        
        $_SESSION[$key][] = [
            'time' => time(),
            'success' => $success
        ];
    }

    private function getAttemptCountSession(int $windowSeconds): int
    {
        $key = $this->getSessionKey('attempts');
        $cutoff = time() - $windowSeconds;
        $count = 0;
        
        if (isset($_SESSION[$key]) && is_array($_SESSION[$key])) {
            foreach ($_SESSION[$key] as $attempt) {
                if ($attempt['time'] >= $cutoff && !$attempt['success']) {
                    $count++;
                }
            }
            
            // Limpiar intentos antiguos
            $_SESSION[$key] = array_filter($_SESSION[$key], function($a) use ($cutoff) {
                return $a['time'] >= $cutoff;
            });
        }
        
        return $count;
    }

    private function checkBlockSession(): array
    {
        $key = $this->getSessionKey('block');
        
        if (isset($_SESSION[$key])) {
            $remaining = $_SESSION[$key] - time();
            if ($remaining > 0) {
                return ['blocked' => true, 'remaining_time' => $remaining];
            }
            unset($_SESSION[$key]);
        }
        
        return ['blocked' => false, 'remaining_time' => 0];
    }

    private function applyBlockSession(int $duration): void
    {
        $key = $this->getSessionKey('block');
        $_SESSION[$key] = time() + $duration;
    }

    private function resetSession(): void
    {
        unset($_SESSION[$this->getSessionKey('attempts')]);
        unset($_SESSION[$this->getSessionKey('block')]);
    }

    // ==================== Implementación con Base de Datos ====================

    private function recordAttemptDb(bool $success): void
    {
        $this->ensureTableExists();
        
        $stmt = $this->pdo->prepare("
            INSERT INTO rate_limits (identifier, action, success, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$this->identifier, $this->action, $success ? 1 : 0]);
    }

    private function getAttemptCountDb(int $windowSeconds): int
    {
        $this->ensureTableExists();
        
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM rate_limits
            WHERE identifier = ? 
            AND action = ?
            AND success = 0
            AND created_at >= DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$this->identifier, $this->action, $windowSeconds]);
        
        return (int) $stmt->fetchColumn();
    }

    private function checkBlockDb(): array
    {
        $this->ensureTableExists();
        
        $stmt = $this->pdo->prepare("
            SELECT blocked_until FROM rate_limit_blocks
            WHERE identifier = ? AND action = ?
            AND blocked_until > NOW()
        ");
        $stmt->execute([$this->identifier, $this->action]);
        
        $result = $stmt->fetch();
        if ($result) {
            $remaining = strtotime($result['blocked_until']) - time();
            return ['blocked' => true, 'remaining_time' => $remaining];
        }
        
        return ['blocked' => false, 'remaining_time' => 0];
    }

    private function applyBlockDb(int $duration): void
    {
        $this->ensureTableExists();
        
        $stmt = $this->pdo->prepare("
            INSERT INTO rate_limit_blocks (identifier, action, blocked_until)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))
            ON DUPLICATE KEY UPDATE blocked_until = DATE_ADD(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$this->identifier, $this->action, $duration, $duration]);
    }

    private function resetDb(): void
    {
        $this->ensureTableExists();
        
        $stmt = $this->pdo->prepare("
            DELETE FROM rate_limits WHERE identifier = ? AND action = ?
        ");
        $stmt->execute([$this->identifier, $this->action]);
        
        $stmt = $this->pdo->prepare("
            DELETE FROM rate_limit_blocks WHERE identifier = ? AND action = ?
        ");
        $stmt->execute([$this->identifier, $this->action]);
    }

    private function ensureTableExists(): void
    {
        static $checked = false;
        if ($checked) return;
        
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS rate_limits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                identifier VARCHAR(64) NOT NULL,
                action VARCHAR(50) NOT NULL,
                success TINYINT(1) DEFAULT 0,
                created_at DATETIME NOT NULL,
                INDEX idx_identifier_action (identifier, action),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB
        ");
        
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS rate_limit_blocks (
                identifier VARCHAR(64) NOT NULL,
                action VARCHAR(50) NOT NULL,
                blocked_until DATETIME NOT NULL,
                PRIMARY KEY (identifier, action)
            ) ENGINE=InnoDB
        ");
        
        $checked = true;
    }

    // ==================== Helpers ====================

    private function getConfig(): array
    {
        return self::LIMITS[$this->action] ?? [
            'max_attempts' => self::DEFAULT_MAX_ATTEMPTS,
            'window_seconds' => self::DEFAULT_WINDOW_SECONDS,
            'block_duration' => self::DEFAULT_BLOCK_DURATION,
        ];
    }

    private function getAttemptCount(int $windowSeconds): int
    {
        return $this->useDatabase 
            ? $this->getAttemptCountDb($windowSeconds)
            : $this->getAttemptCountSession($windowSeconds);
    }

    private function checkBlock(): array
    {
        return $this->useDatabase
            ? $this->checkBlockDb()
            : $this->checkBlockSession();
    }

    private function applyBlock(int $duration): void
    {
        if ($this->useDatabase) {
            $this->applyBlockDb($duration);
        } else {
            $this->applyBlockSession($duration);
        }
        
        // Registrar en log de seguridad
        SecurityAudit::log(
            SecurityAudit::EVENT_RATE_LIMIT,
            null,
            [
                'action' => $this->action,
                'duration' => $duration
            ],
            'WARNING'
        );
    }

    private function hashIdentifier(string $identifier): string
    {
        return hash('sha256', $identifier . '_' . $this->action);
    }

    private function getSessionKey(string $suffix): string
    {
        return 'rate_limit_' . $this->action . '_' . $this->identifier . '_' . $suffix;
    }

    private function formatBlockMessage(int $seconds): string
    {
        if ($seconds > 3600) {
            $hours = ceil($seconds / 3600);
            return "Por favor, espere {$hours} hora(s) antes de intentar de nuevo.";
        } elseif ($seconds > 60) {
            $minutes = ceil($seconds / 60);
            return "Por favor, espere {$minutes} minuto(s) antes de intentar de nuevo.";
        } else {
            return "Por favor, espere {$seconds} segundo(s) antes de intentar de nuevo.";
        }
    }

    /**
     * Limpia registros antiguos (para cron job)
     */
    public static function cleanup(\PDO $pdo, int $olderThanDays = 7): int
    {
        $stmt = $pdo->prepare("
            DELETE FROM rate_limits 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$olderThanDays]);
        $deleted = $stmt->rowCount();
        
        $stmt = $pdo->prepare("
            DELETE FROM rate_limit_blocks 
            WHERE blocked_until < NOW()
        ");
        $stmt->execute();
        $deleted += $stmt->rowCount();
        
        return $deleted;
    }
}
