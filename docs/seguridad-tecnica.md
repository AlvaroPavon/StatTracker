# 🔐 Documentación Técnica de Seguridad - StatTracker

Este documento proporciona información técnica detallada sobre la implementación de seguridad en StatTracker. Para una visión general de la arquitectura de seguridad, consulta [SECURITY.md](../SECURITY.md).

---

## 📋 Índice

1. [Securización de Contraseñas](#securización-de-contraseñas)
2. [Clases de Seguridad](#clases-de-seguridad)
3. [Validaciones de Entrada](#validaciones-de-entrada)
4. [Gestión de Sesiones](#gestión-de-sesiones)
5. [Cierre Automático de Sesión por Inactividad](#cierre-automático-de-sesión-por-inactividad)
6. [Autenticación de Dos Factores (2FA)](#autenticación-de-dos-factores-2fa)
7. [CAPTCHA Matemático](#simplecaptcha)
8. [Sistema de Alertas de Login](#loginalertsystem)
9. [Tokens y Criptografía](#tokens-y-criptografía)

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
| `getInfo()` | Obtiene información de la sesión actual |

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

### Tiempos de Sesión (Servidor)

| Parámetro | Valor | Descripción |
|-----------|-------|-------------|
| SESSION_LIFETIME | 3600s (1h) | Tiempo máximo de vida |
| MAX_IDLE_TIME | 1800s (30min) | Tiempo máximo de inactividad (servidor) |
| SESSION_REGENERATE_TIME | 300s (5min) | Regeneración automática de ID |

---

## Cierre Automático de Sesión por Inactividad

StatTracker implementa un sistema de cierre automático de sesión para proteger contra accesos no autorizados cuando el usuario deja el equipo desatendido.

### Configuración de Tiempos

| Parámetro | Valor | Descripción |
|-----------|-------|-------------|
| **Timeout por inactividad (cliente)** | 15 minutos | Tiempo sin actividad antes de mostrar advertencia |
| **Tiempo de advertencia** | 60 segundos | Tiempo para responder antes del cierre |
| **Intervalo de verificación** | 10 segundos | Frecuencia de verificación de inactividad |
| **Timeout del servidor** | 30 minutos | Timeout de respaldo en el servidor |
| **Tiempo de vida máximo** | 1 hora | Sesión absoluta máxima |

### Componentes del Sistema

#### SessionTimeout.js (Frontend)

**Ubicación**: `/js/session-timeout.js`

**Responsabilidad**: Detectar inactividad del usuario en el navegador

**Eventos monitoreados**:
- `mousedown` - Clics del ratón
- `mousemove` - Movimiento del ratón
- `keydown` / `keypress` - Pulsaciones de teclado
- `scroll` - Desplazamiento
- `touchstart` - Eventos táctiles
- `click` - Clics
- `wheel` - Rueda del ratón

**Métodos principales**:

| Método | Descripción |
|--------|-------------|
| `constructor(options)` | Inicializa con configuración personalizable |
| `registerActivity()` | Registra actividad del usuario |
| `checkSession()` | Verifica el estado de la sesión |
| `showWarning()` | Muestra modal de advertencia |
| `hideWarning()` | Oculta modal de advertencia |
| `extendSession()` | Envía ping al servidor para extender sesión |
| `logout()` | Redirige al logout |
| `getRemainingTime()` | Obtiene tiempo restante en segundos |
| `pause()` / `resume()` | Pausa/reanuda el sistema |
| `destroy()` | Destruye el sistema y limpia recursos |

**Ejemplo de uso**:

```javascript
window.sessionTimeout = new SessionTimeout({
    idleTimeout: 900,        // 15 minutos en segundos
    warningTime: 60,         // 60 segundos de advertencia
    checkInterval: 10,       // Verificar cada 10 segundos
    logoutUrl: 'logout.php',
    keepAliveUrl: 'keep_alive.php',
    csrfToken: window.csrfToken,
    onWarning: function(seconds) {
        console.log('Sesión expira en ' + seconds + ' segundos');
    },
    onLogout: function(reason) {
        console.log('Cerrando sesión por: ' + reason);
    },
    onActivity: function() {
        // Callback cuando se detecta actividad
    }
});
```

#### keep_alive.php (Backend)

**Ubicación**: `/keep_alive.php`

**Responsabilidad**: Endpoint AJAX para extender la sesión sin recargar la página

**Acciones soportadas**:

| Acción | Descripción | Respuesta |
|--------|-------------|-----------|
| `extend` | Extiende la sesión | `remaining_idle`, `remaining_total`, `server_time` |
| `status` | Devuelve estado de la sesión | `idle_seconds`, `age_seconds`, `remaining_idle`, `remaining_total` |
| `ping` | Simple verificación de conexión | `pong: true`, `time` |

**Ejemplo de respuesta (extend)**:

```json
{
    "success": true,
    "message": "Session extended",
    "remaining_idle": 1800,
    "remaining_total": 3200,
    "server_time": "2025-08-15 10:30:00"
}
```

**Seguridad del endpoint**:
- Solo acepta método POST
- Solo acepta peticiones AJAX (X-Requested-With)
- Requiere autenticación
- Registra extensiones en log de auditoría

#### Modal de Advertencia

Cuando queda 1 minuto para el cierre, se muestra un modal con:

- **Icono animado** de reloj
- **Cuenta regresiva** visible (60, 59, 58...)
- **Botón "Continuar sesión"** - Extiende la sesión
- **Botón "Cerrar sesión"** - Logout inmediato
- **Sonido de alerta** sutil (si el navegador lo permite)

**Estilos**:
- Compatible con modo claro y oscuro
- Animación de entrada suave
- Backdrop con blur

### Flujo Completo

```
1. Usuario inicia sesión
   ↓
2. SessionTimeout.js se inicializa (en dashboard.php y profile.php)
   ↓
3. Sistema monitorea actividad constantemente
   ↓
4. [Si hay actividad] → Reinicia contador de inactividad
   ↓
5. [Sin actividad por 14 minutos]
   ↓
6. Muestra modal de advertencia con cuenta regresiva de 60s
   ↓
7. [Usuario hace clic en "Continuar"]
   ↓
   7a. Envía AJAX a keep_alive.php
   7b. Servidor actualiza last_activity
   7c. Oculta modal
   7d. Reinicia contador
   ↓
   [O bien]
   ↓
8. [Usuario no responde en 60s]
   ↓
9. Redirige a logout.php?reason=timeout
   ↓
10. Muestra mensaje en login: "Tu sesión se cerró por inactividad"
```

### Personalización

**En el cliente** (dashboard.php, profile.php):

```javascript
new SessionTimeout({
    idleTimeout: 600,    // Cambiar a 10 minutos
    warningTime: 120,    // Advertencia 2 minutos antes
});
```

**En el servidor** (SessionManager.php):

```php
private const MAX_IDLE_TIME = 1200; // 20 minutos
```

> **Importante**: El timeout del cliente debe ser menor o igual al del servidor para evitar desincronizaciones.

### Logs de Auditoría

El sistema registra en `/logs/security.log`:

```json
{"event": "SESSION_EXTENDED", "user_id": 1, "idle_seconds": 850, "timestamp": "..."}
{"event": "LOGOUT", "user_id": 1, "method": "timeout", "ip": "192.168.1.1"}
```

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

## SimpleCaptcha

**Ubicación**: `/src/SimpleCaptcha.php`

**Responsabilidad**: CAPTCHA matemático sin dependencias externas (no requiere reCAPTCHA, hCaptcha, etc.)

### Configuración

| Parámetro | Valor | Descripción |
|-----------|-------|-------------|
| `EXPIRY_TIME` | 300s (5 min) | Tiempo de validez del CAPTCHA |
| `MAX_NUMBER` | 20 | Número máximo en operaciones |
| Operaciones | suma, resta, multiplicación | Tipos de operaciones generadas |

### Métodos

| Método | Descripción |
|--------|-------------|
| `generate()` | Genera operación matemática y HTML |
| `validate()` | Valida respuesta del usuario |
| `isValid()` | Versión simplificada que retorna bool |
| `generateImage()` | Alternativa: CAPTCHA de imagen (requiere GD) |

### Uso en Formularios

**En la página del formulario (PHP)**:

```php
use App\SimpleCaptcha;

$captcha = SimpleCaptcha::generate();
// $captcha['question'] = "¿Cuánto es 7 + 12?"
// $captcha['html'] = HTML del campo de entrada
```

```html
<!-- En el formulario -->
<?php echo $captcha['html']; ?>
```

**En el procesamiento (PHP)**:

```php
use App\SimpleCaptcha;

$result = SimpleCaptcha::validate();
if (!$result['valid']) {
    $_SESSION['error'] = $result['error'];
    header("Location: form.php");
    exit();
}
// Continuar con el procesamiento...
```

### Dónde se usa

| Formulario | Comportamiento |
|------------|----------------|
| **Registro** | CAPTCHA siempre requerido |
| **Login** | CAPTCHA requerido después de 3 intentos fallidos |

### Seguridad del CAPTCHA

- Respuesta cifrada con AES-256-CBC en la sesión
- Incluye sal aleatoria para cada generación
- Un solo uso (se invalida después de validar)
- Tiempo de expiración de 5 minutos

---

## LoginAlertSystem

**Ubicación**: `/src/LoginAlertSystem.php`

**Responsabilidad**: Detección de logins sospechosos y alertas al usuario

### Sistema de Puntuación

Cada factor de riesgo suma puntos. Si el total alcanza el umbral (3 puntos), se considera sospechoso:

| Factor | Descripción | Puntos |
|--------|-------------|--------|
| `new_device` | Dispositivo no reconocido (fingerprint diferente) | +2 |
| `different_ip_range` | IP en rango diferente (primeros 2 octetos) | +2 |
| `new_country` | País nuevo (si hay geolocalización) | +3 |
| `unusual_time` | Hora fuera del patrón habitual del usuario | +1 |
| `multiple_ips_recently` | 3+ IPs diferentes en las últimas 2 horas | +2 |
| `user_agent_changed` | Cambio de navegador o sistema operativo | +1 |
| `recent_failed_attempts` | Intentos fallidos recientes en la cuenta | +1 |

**Umbral de sospecha**: 3+ puntos

### Métodos Principales

| Método | Descripción |
|--------|-------------|
| `analyzeLogin($userId, $email)` | Analiza un login y devuelve resultado |
| `generateAlertMessage($analysis)` | Genera mensaje de alerta para el usuario |
| `cleanup($days)` | Limpia registros antiguos (para cron) |

### Ejemplo de Uso

```php
use App\LoginAlertSystem;

// Después de login exitoso
$analysis = LoginAlertSystem::analyzeLogin($userId, $email);

if ($analysis['suspicious']) {
    $_SESSION['security_alert'] = LoginAlertSystem::generateAlertMessage($analysis);
}

// $analysis contiene:
// [
//     'suspicious' => true/false,
//     'reasons' => ['new_device', 'different_ip_range'],
//     'score' => 4,
//     'is_new_device' => true,
//     'is_new_location' => true
// ]
```

### Almacenamiento de Datos

| Archivo | Contenido |
|---------|-----------|
| `/logs/known_devices.json` | Dispositivos conocidos por usuario (fingerprints) |
| `/logs/login_history.json` | Historial de logins (últimos 50 por usuario) |

### Fingerprint de Dispositivo

Se genera un hash SHA-256 basado en:
- User-Agent
- Accept-Language
- Accept-Encoding

### Mensaje de Alerta

Cuando se detecta login sospechoso, se muestra en el dashboard:

```
⚠️ Alerta de seguridad: nuevo dispositivo detectado, ubicación diferente a la habitual.
Si no reconoces esta actividad, cambia tu contraseña inmediatamente.
```

Con enlace directo a cambio de contraseña.

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

## Resumen de Archivos de Seguridad

### Clases PHP (/src/)

| Archivo | Responsabilidad |
|---------|-----------------|
| `Auth.php` | Autenticación (registro, login) |
| `User.php` | Gestión de perfil |
| `Metrics.php` | Métricas de salud |
| `Security.php` | Validaciones centralizadas |
| `CryptoFortress.php` | Criptografía avanzada |
| `SessionManager.php` | Gestión segura de sesiones |
| `SecurityFirewall.php` | WAF |
| `SecurityHeaders.php` | Headers HTTP |
| `RateLimiter.php` | Control de tasa |
| `InputSanitizer.php` | Sanitización de entrada |
| `Honeypot.php` | Detección de bots (campos ocultos) |
| `AdvancedProtection.php` | Protecciones adicionales |
| `UltimateShield.php` | 100+ patrones de detección |
| `ImpenetrableDefense.php` | Defensa avanzada |
| `TwoFactorAuth.php` | 2FA/MFA con TOTP |
| `SimpleCaptcha.php` | CAPTCHA matemático |
| `LoginAlertSystem.php` | Alertas de login sospechoso |
| `SecurityAudit.php` | Logging de seguridad |

### Archivos JavaScript (/js/)

| Archivo | Responsabilidad |
|---------|-----------------|
| `session-timeout.js` | Cierre automático por inactividad |
| `form-validation.js` | Validación de formularios en cliente |

### Endpoints PHP (raíz)

| Archivo | Responsabilidad |
|---------|-----------------|
| `keep_alive.php` | Extender sesión (AJAX) |
| `logout.php` | Cierre de sesión (normal y timeout) |
| `security_init.php` | Inicialización de seguridad |

---

## Referencias

- [OWASP Password Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)
- [OWASP Session Management Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html)
- [RFC 6238 - TOTP](https://tools.ietf.org/html/rfc6238)
- [PHP password_hash documentation](https://www.php.net/manual/en/function.password-hash.php)

---

**Última actualización**: Agosto 2025  
**Versión**: 1.3
