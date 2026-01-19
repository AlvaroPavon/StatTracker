# 🔐 Documentación Técnica de Seguridad - StatTracker

Este documento proporciona información técnica detallada sobre la implementación de seguridad en StatTracker. Para una visión general de la arquitectura de seguridad, consulta [SECURITY.md](../SECURITY.md).

---

## 📋 Índice

1. [Securización de Contraseñas](#securización-de-contraseñas)
2. [Clases de Seguridad](#clases-de-seguridad)
3. [Validaciones de Entrada](#validaciones-de-entrada)
4. [Gestión de Sesiones](#gestión-de-sesiones)
5. [Autenticación de Dos Factores (2FA)](#autenticación-de-dos-factores-2fa)
6. [Tokens y Criptografía](#tokens-y-criptografía)

---

## Securización de Contraseñas

StatTracker implementa un sistema de securización de contraseñas de **nivel bancario** a través de la clase `CryptoFortress`.

### Algoritmo de Hashing

**Algoritmo principal**: Argon2id (con fallback a bcrypt)

```php
// Configuración de Argon2id
ARGON2_MEMORY = 65536   // 64 MB de memoria
ARGON2_TIME = 4          // 4 iteraciones
ARGON2_THREADS = 4       // 4 hilos paralelos
```

**¿Por qué Argon2id?**
- Resistente a ataques GPU/ASIC
- Ganador de la Password Hashing Competition (2015)
- Recomendado por OWASP para 2024+
- Preparación para computación cuántica

### Sistema de Pepper

Además del salt automático de Argon2id/bcrypt, se aplica un **pepper secreto**:

```php
// El pepper se aplica antes del hash
$pepperedPassword = hash_hmac('sha256', $password, PEPPER);
$hash = password_hash($pepperedPassword, PASSWORD_ARGON2ID, $options);
```

**Ventajas del pepper**:
- Si se roba la BD, las contraseñas siguen seguras
- El pepper está en código, separado de la BD
- Añade entropía adicional

### Protección contra Timing Attacks

```php
public static function verifyPassword(string $password, string $hash): bool
{
    $startTime = hrtime(true);
    
    $result = password_verify($pepperedPassword, $hash);
    
    // Tiempo mínimo de 250ms para dificultar timing attacks
    $elapsed = (hrtime(true) - $startTime) / 1e6;
    if ($elapsed < 250) {
        usleep((int)((250 - $elapsed) * 1000));
    }
    
    return $result;
}
```

### Rehashing Automático

Cuando un usuario inicia sesión, el sistema verifica si el hash necesita actualizarse:

```php
if (CryptoFortress::needsRehash($user['password'])) {
    // Actualizar a algoritmo más fuerte automáticamente
    $newHash = CryptoFortress::hashPassword($password);
    // Guardar nuevo hash
}
```

### Limpieza de Memoria

Las contraseñas en texto plano se limpian de memoria después de usarse:

```php
CryptoFortress::secureClear($password);
```

Esto usa `sodium_memzero()` si está disponible, o sobrescribe con datos aleatorios.

### Requisitos de Contraseña

| Requisito | Valor |
|-----------|-------|
| Longitud mínima | 8 caracteres |
| Longitud máxima | 72 caracteres (límite bcrypt) |
| Letra minúscula | Obligatorio |
| Letra mayúscula | Obligatorio |
| Número | Obligatorio |

---

## Clases de Seguridad

StatTracker implementa múltiples clases de seguridad, cada una con responsabilidad específica:

### CryptoFortress

**Ubicación**: `/src/CryptoFortress.php`

**Responsabilidad**: Criptografía de alto nivel

| Método | Descripción |
|--------|-------------|
| `hashPassword()` | Hashea contraseñas con Argon2id/bcrypt + pepper |
| `verifyPassword()` | Verifica con timing constante |
| `needsRehash()` | Detecta si el hash necesita actualización |
| `encrypt()` | Cifra datos con AES-256-GCM |
| `decrypt()` | Descifra datos |
| `generateToken()` | Genera tokens criptográficamente seguros |
| `generateSignedToken()` | Tokens firmados con HMAC |
| `deriveKey()` | Deriva claves con PBKDF2/Argon2 |
| `secureClear()` | Limpia datos sensibles de memoria |

### Security

**Ubicación**: `/src/Security.php`

**Responsabilidad**: Validaciones centralizadas

| Método | Descripción |
|--------|-------------|
| `validateNombre()` | Valida nombres (solo letras, espacios, guiones) |
| `validateApellidos()` | Valida apellidos |
| `validateEmail()` | Valida formato de email |
| `validatePassword()` | Valida requisitos de contraseña |
| `validateAltura()` | Valida altura (0.50 - 2.50 m) |
| `validatePeso()` | Valida peso (1 - 500 kg) |
| `validateFecha()` | Valida fecha (no futura, no muy antigua) |
| `validateImageUpload()` | Valida archivos de imagen |
| `generateCsrfToken()` | Genera tokens CSRF |
| `validateCsrfToken()` | Valida tokens CSRF |
| `checkLoginAttempts()` | Verifica rate limiting |
| `recordFailedLogin()` | Registra intentos fallidos |
| `escapeHtml()` | Sanitiza output HTML |

### SessionManager

**Ubicación**: `/src/SessionManager.php`

**Responsabilidad**: Gestión segura de sesiones

| Método | Descripción |
|--------|-------------|
| `start()` | Inicia sesión con configuración segura |
| `validate()` | Valida integridad de sesión |
| `regenerateId()` | Regenera ID de sesión |
| `destroy()` | Destruye sesión de forma segura |
| `authenticate()` | Autentica usuario |
| `isAuthenticated()` | Verifica autenticación |

### SecurityFirewall

**Ubicación**: `/src/SecurityFirewall.php`

**Responsabilidad**: WAF (Web Application Firewall)

- Detecta SQL Injection (42+ patrones)
- Detecta XSS (26+ patrones)
- Detecta Path Traversal
- Detecta Command Injection
- Bloquea IPs maliciosas

### SecurityHeaders

**Ubicación**: `/src/SecurityHeaders.php`

**Responsabilidad**: Headers HTTP de seguridad

- Content-Security-Policy
- Strict-Transport-Security (HSTS)
- X-Frame-Options
- X-Content-Type-Options
- Permissions-Policy

### RateLimiter

**Ubicación**: `/src/RateLimiter.php`

**Responsabilidad**: Control de tasa de peticiones

- Limita intentos de login (5 intentos / 15 min)
- Bloqueo progresivo (30 min tras exceder límite)
- Rate limiting por IP y por cuenta

### ImpenetrableDefense

**Ubicación**: `/src/ImpenetrableDefense.php`

**Responsabilidad**: Defensa avanzada

- Bloqueo de rangos IP (TOR, proxies maliciosos)
- Rate limiting global (anti-DDoS)
- Análisis de comportamiento (detección de bots)
- Account lockout por cuenta (no solo por IP)
- Honey accounts (cuentas trampa)
- Request signing (anti-replay)

### TwoFactorAuth

**Ubicación**: `/src/TwoFactorAuth.php`

**Responsabilidad**: Autenticación 2FA

- TOTP (RFC 6238)
- Compatible con Google Authenticator, Authy, etc.
- Códigos de recuperación cifrados
- Anti-replay de códigos

### Honeypot

**Ubicación**: `/src/Honeypot.php`

**Responsabilidad**: Detección de bots

- Campos honeypot en formularios
- Detección de tiempo mínimo de envío

### SecurityAudit

**Ubicación**: `/src/SecurityAudit.php`

**Responsabilidad**: Logging de seguridad

- Registra eventos de seguridad
- Formato JSON estructurado
- Almacena en `/logs/security.log`

---

## Validaciones de Entrada

### Constantes de Validación

```php
class Security
{
    public const MAX_NOMBRE = 50;
    public const MAX_APELLIDOS = 100;
    public const MAX_EMAIL = 255;
    public const MIN_PASSWORD = 8;
    public const MAX_PASSWORD = 72; // Límite de bcrypt
    
    public const MIN_ALTURA = 0.50; // metros
    public const MAX_ALTURA = 2.50;
    public const MIN_PESO = 1.0;    // kg
    public const MAX_PESO = 500.0;
    
    public const MAX_LOGIN_ATTEMPTS = 5;
    public const LOCKOUT_TIME = 900; // 15 minutos
    
    public const MAX_FILE_SIZE = 2097152; // 2MB
}
```

### Patrón de Validación

Todas las validaciones retornan un array consistente:

```php
[
    'valid' => bool,
    'error' => string,  // Mensaje de error si valid es false
    'value' => mixed    // Valor sanitizado si valid es true
]
```

### Ejemplo de Uso

```php
$nombreValidation = Security::validateNombre($nombre);
if (!$nombreValidation['valid']) {
    return $nombreValidation['error'];
}
$nombre = $nombreValidation['value']; // Valor sanitizado
```

---

## Gestión de Sesiones

### Configuración de Sesión Segura

```php
// Configuración automática al iniciar sesión
ini_set('session.use_only_cookies', 1);     // Solo cookies
ini_set('session.use_strict_mode', 1);      // Modo estricto
ini_set('session.use_trans_sid', 0);        // No IDs en URL
ini_set('session.cookie_httponly', 1);      // HttpOnly
ini_set('session.cookie_samesite', 'Strict'); // SameSite
ini_set('session.cookie_secure', 1);        // Solo HTTPS
```

### Fingerprinting de Sesión

Se genera una huella digital basada en:
- User-Agent
- Accept-Language
- Accept-Encoding

```php
if (!hash_equals($security['fingerprint'], $currentFingerprint)) {
    // Posible session hijacking
    SecurityAudit::logSessionHijackAttempt($userId);
    return false;
}
```

### Tiempos de Sesión

| Parámetro | Valor | Descripción |
|-----------|-------|-------------|
| SESSION_LIFETIME | 3600s (1h) | Tiempo máximo de vida |
| MAX_IDLE_TIME | 1800s (30min) | Tiempo máximo de inactividad |
| SESSION_REGENERATE_TIME | 300s (5min) | Regeneración automática de ID |

---

## Autenticación de Dos Factores (2FA)

### Algoritmo TOTP

Implementación según RFC 6238:

```php
const SECRET_LENGTH = 20;    // 160 bits
const CODE_LENGTH = 6;       // 6 dígitos
const TIME_STEP = 30;        // 30 segundos
const ALLOWED_DRIFT = 1;     // ±1 paso de tiempo
```

### Flujo de Habilitación

1. Generar secreto: `TwoFactorAuth::generateSecret()`
2. Mostrar QR code: `TwoFactorAuth::getQRCodeUri($secret, $email)`
3. Usuario escanea con Google Authenticator / Authy
4. Usuario ingresa código de verificación
5. Si es válido: `TwoFactorAuth::enable($userId, $secret, $recoveryCodes)`

### Códigos de Recuperación

- Se generan 10 códigos
- Formato: `XXXX-XXXX-XXXX`
- Almacenados como hashes bcrypt
- Uso único (se eliminan al usar)

### Almacenamiento Seguro

- Secretos cifrados con AES-256-GCM
- Almacenados en archivo separado de la BD
- Anti-replay: códigos usados se marcan temporalmente

---

## Tokens y Criptografía

### Generación de Tokens

```php
// Token simple (32 bytes = 64 caracteres hex)
$token = CryptoFortress::generateToken(32);

// Token firmado (con expiración)
$signedToken = CryptoFortress::generateSignedToken($data, 3600); // 1 hora
```

### Cifrado de Datos

**Algoritmo**: AES-256-GCM (autenticado)

```php
// Cifrar
$encrypted = CryptoFortress::encrypt($plaintext, $key);

// Descifrar
$plaintext = CryptoFortress::decrypt($encrypted, $key);
```

**Formato del cifrado**: `Base64(IV + TAG + CIPHERTEXT)`

### Derivación de Claves

**Algoritmo principal**: Argon2 (sodium_crypto_pwhash)
**Fallback**: PBKDF2 con SHA-512 y 600,000 iteraciones

---

## Referencias

- [OWASP Password Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)
- [RFC 6238 - TOTP](https://tools.ietf.org/html/rfc6238)
- [PHP password_hash documentation](https://www.php.net/manual/en/function.password-hash.php)

---

## Nuevas Clases de Seguridad (v1.2)

### SimpleCaptcha

**Ubicación**: `/src/SimpleCaptcha.php`

**Responsabilidad**: CAPTCHA matemático sin dependencias externas

| Método | Descripción |
|--------|-------------|
| `generate()` | Genera operación matemática (suma, resta, multiplicación) |
| `validate()` | Valida respuesta del usuario |
| `generateImage()` | Alternativa: CAPTCHA de imagen (requiere GD) |

**Configuración**:
- Tiempo de expiración: 5 minutos
- Números máximos: 1-20
- Respuesta cifrada en sesión

**Uso**:
```php
// En formulario
$captcha = SimpleCaptcha::generate();
echo $captcha['html'];

// En procesamiento
$result = SimpleCaptcha::validate();
if (!$result['valid']) {
    echo $result['error'];
}
```

### LoginAlertSystem

**Ubicación**: `/src/LoginAlertSystem.php`

**Responsabilidad**: Detección de logins sospechosos

| Detección | Descripción | Puntos |
|-----------|-------------|--------|
| `new_device` | Dispositivo no reconocido | +2 |
| `different_ip_range` | IP en rango diferente | +2 |
| `new_country` | País nuevo (si hay geoloc) | +3 |
| `unusual_time` | Hora fuera del patrón habitual | +1 |
| `multiple_ips_recently` | 3+ IPs en 2 horas | +2 |
| `user_agent_changed` | Cambio de navegador/SO | +1 |
| `recent_failed_attempts` | Intentos fallidos recientes | +1 |

**Umbral de sospecha**: 3+ puntos = Login sospechoso

**Uso**:
```php
$analysis = LoginAlertSystem::analyzeLogin($userId, $email);
if ($analysis['suspicious']) {
    $_SESSION['security_alert'] = LoginAlertSystem::generateAlertMessage($analysis);
}
```

---

## Cierre Automático de Sesión por Inactividad

### Sistema de Timeout de Sesión

StatTracker implementa un sistema de cierre automático de sesión para proteger contra accesos no autorizados cuando el usuario deja el equipo desatendido.

### Configuración de Tiempos

| Parámetro | Valor | Descripción |
|-----------|-------|-------------|
| **Timeout por inactividad** | 15 minutos | Tiempo sin actividad antes de cerrar |
| **Tiempo de advertencia** | 60 segundos | Alerta antes del cierre |
| **Tiempo de vida máximo** | 1 hora | Sesión absoluta máxima |
| **Regeneración de ID** | 5 minutos | Regeneración automática del session ID |

### Componentes

#### SessionTimeout.js (Frontend)

**Ubicación**: `/js/session-timeout.js`

Detecta inactividad del usuario monitoreando:
- Movimiento del ratón
- Pulsaciones de teclado
- Clics
- Scroll
- Eventos táctiles

**Flujo**:
1. Usuario inactivo durante 14 minutos → Muestra modal de advertencia
2. Usuario puede hacer clic en "Continuar sesión" → Extiende sesión
3. Si no hay respuesta en 60 segundos → Cierra sesión automáticamente

#### keep_alive.php (Backend)

**Ubicación**: `/keep_alive.php`

Endpoint AJAX para extender la sesión sin recargar la página.

**Acciones**:
- `extend`: Extiende la sesión
- `status`: Devuelve estado de la sesión
- `ping`: Verificación de conexión

#### SessionManager (Backend)

**Ubicación**: `/src/SessionManager.php`

Gestiona validación de sesiones en el servidor:
- Verifica tiempo de vida máximo
- Verifica tiempo de inactividad
- Valida fingerprint del navegador
- Regenera ID de sesión periódicamente

### Uso en Código

```javascript
// Inicializar en páginas protegidas
window.sessionTimeout = new SessionTimeout({
    idleTimeout: 900,      // 15 minutos
    warningTime: 60,       // 60 segundos de advertencia
    checkInterval: 10,     // Verificar cada 10 segundos
    logoutUrl: 'logout.php',
    keepAliveUrl: 'keep_alive.php',
    csrfToken: window.csrfToken
});
```

### Personalización

El sistema se puede personalizar editando los valores en:
- `SessionManager.php`: Tiempos del servidor
- `session-timeout.js`: Tiempos del cliente (deben ser menores o iguales al servidor)

---

**Última actualización**: Agosto 2025  
**Versión**: 1.2
