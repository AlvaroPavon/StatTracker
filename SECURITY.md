# 🔐 StatTracker - Guía de Seguridad

Este documento detalla las medidas de seguridad implementadas en StatTracker para proteger contra los ataques más comunes de OWASP Top 10.

## 📋 Índice de Protecciones

| Ataque | Clase/Archivo | Estado |
|--------|---------------|--------|
| SQL Injection | `SecurityFirewall`, Prepared Statements | ✅ |
| XSS (Cross-Site Scripting) | `InputSanitizer`, `SecurityFirewall`, CSP | ✅ |
| CSRF (Cross-Site Request Forgery) | `Security::validateCsrfToken()` | ✅ |
| Session Hijacking | `SessionManager` | ✅ |
| Session Fixation | `SessionManager::regenerateId()` | ✅ |
| Brute Force | `RateLimiter` | ✅ |
| Path Traversal | `SecurityFirewall`, `InputSanitizer` | ✅ |
| Command Injection | `SecurityFirewall` | ✅ |
| File Upload Attacks | `Security::validateImage()` | ✅ |
| Clickjacking | `SecurityHeaders` (X-Frame-Options) | ✅ |
| MIME Sniffing | `SecurityHeaders` (X-Content-Type-Options) | ✅ |
| Bot Detection | `Honeypot` | ✅ |

---

## 🏗️ Arquitectura de Seguridad

### Flujo de una Petición

```
Cliente → SecurityFirewall (WAF) → SecurityHeaders → SessionManager → Aplicación
              ↓                         ↓               ↓
         Bloqueo IP              CSP/HSTS/etc      Anti-hijacking
         si detecta              Headers HTTP      Fingerprinting
         ataque
```

### Clases de Seguridad

| Clase | Responsabilidad |
|-------|-----------------|
| `SecurityFirewall` | WAF (Web Application Firewall) - Detecta y bloquea ataques |
| `SecurityHeaders` | Configura headers HTTP de seguridad |
| `SessionManager` | Gestión segura de sesiones |
| `Security` | Validaciones CSRF, contraseñas, entrada |
| `SecurityAudit` | Logging de eventos de seguridad |
| `InputSanitizer` | Sanitización de entrada |
| `RateLimiter` | Control de tasa de peticiones |
| `Honeypot` | Detección de bots |

---

## 🛡️ Protecciones Implementadas

### 1. SQL Injection

**Mitigación:**
- ✅ Prepared Statements en TODAS las consultas SQL
- ✅ Detección de patrones SQL en `SecurityFirewall`
- ✅ Sanitización de entrada numérica

**Ejemplo de código protegido:**
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
```

### 2. XSS (Cross-Site Scripting)

**Mitigación:**
- ✅ `htmlspecialchars()` en TODA salida HTML
- ✅ Content Security Policy (CSP) estricto
- ✅ Detección de patrones XSS en WAF
- ✅ Sanitización con `InputSanitizer`

**Headers:**
```
Content-Security-Policy: default-src 'self'; script-src 'self' ...
X-XSS-Protection: 1; mode=block
```

### 3. CSRF

**Mitigación:**
- ✅ Token CSRF en TODOS los formularios
- ✅ Validación obligatoria en cada petición POST
- ✅ Tokens con expiración (1 hora)
- ✅ Logging de intentos inválidos

**Uso:**
```php
// Generar token
$token = Security::generateCsrfToken();

// En formulario
<input type="hidden" name="csrf_token" value="<?= $token ?>">

// Validar
if (!Security::validateCsrfToken($_POST['csrf_token'])) {
    die('CSRF inválido');
}
```

### 4. Fuerza Bruta

**Mitigación:**
- ✅ Rate limiting por IP y email
- ✅ Bloqueo progresivo (5 intentos = 30 min bloqueo)
- ✅ Logging de intentos fallidos
- ✅ CAPTCHA implícito (tiempo mínimo de envío)

**Configuración:**
```php
// En RateLimiter
'login' => [
    'max_attempts' => 5,
    'window_seconds' => 900,    // 15 minutos
    'block_duration' => 1800,   // 30 minutos
]
```

### 5. Session Security

**Mitigación:**
- ✅ Cookies HttpOnly y Secure
- ✅ SameSite=Strict
- ✅ Regeneración de ID tras login
- ✅ Fingerprinting de sesión (User-Agent, Accept-Language)
- ✅ Timeout de inactividad (30 min)
- ✅ Tiempo de vida máximo (1 hora)

### 6. File Upload

**Mitigación:**
- ✅ Verificación de tipo MIME real
- ✅ Límite de tamaño (2MB)
- ✅ Solo extensiones permitidas (.jpg, .png, .gif, .webp)
- ✅ Nombres de archivo aleatorios
- ✅ Directorio uploads protegido con .htaccess

### 7. WAF (Web Application Firewall)

**Detecta:**
- SQL Injection
- XSS
- Path Traversal
- Command Injection
- File Inclusion (LFI/RFI)
- Null Byte Injection
- User-Agents de herramientas de ataque

**Acción:**
- Bloqueo automático de IP por 1 hora
- Logging detallado de todos los intentos

---

## 📊 Headers de Seguridad

```
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=()
Cross-Origin-Opener-Policy: same-origin
Cross-Origin-Resource-Policy: same-origin
Content-Security-Policy: [ver detalle abajo]
Strict-Transport-Security: max-age=31536000; includeSubDomains
```

---

## 🧪 GitHub Actions de Seguridad

### Workflows Disponibles

1. **`security-audit.yml`** - Auditoría semanal
   - Composer audit (dependencias)
   - PHPStan (análisis estático)
   - Escaneo de secretos
   - Funciones peligrosas

2. **`owasp-scan.yml`** - Escaneo OWASP
   - Dependency-Check
   - Análisis de código
   - Verificación de configuración

3. **`php-ci.yml`** - CI con tests
   - PHPUnit
   - Cobertura de código

4. **`release-production.yml`** - Release automático
   - Genera ZIP sin archivos de desarrollo
   - Se activa al crear release

---

## 🔍 Logging de Seguridad

Todos los eventos de seguridad se registran en `/logs/security.log`:

```json
{
  "timestamp": "2025-01-14 13:30:00",
  "severity": "WARNING",
  "event": "LOGIN_FAILED",
  "user_id": null,
  "ip": "192.168.1.100",
  "details": {
    "email": "at***@example.com",
    "reason": "Contraseña incorrecta"
  }
}
```

**Eventos registrados:**
- `LOGIN_SUCCESS`, `LOGIN_FAILED`, `LOGIN_BLOCKED`
- `LOGOUT`
- `REGISTER`
- `PASSWORD_CHANGE`
- `CSRF_INVALID`
- `SESSION_HIJACK_ATTEMPT`
- `RATE_LIMIT_EXCEEDED`
- `THREAT_DETECTED`
- `BOT_DETECTED`
- `IP_BLOCKED`

---

## 🚨 Respuesta a Incidentes

### Si detectas un ataque:

1. **Revisar logs:**
   ```bash
   tail -f /app/logs/security.log | grep CRITICAL
   ```

2. **Ver IPs bloqueadas:**
   ```bash
   cat /app/logs/blocked_ips.json
   ```

3. **Desbloquear IP (si es necesario):**
   ```php
   SecurityFirewall::unblockIp('192.168.1.100');
   ```

---

## 🎯 Vectores de Ataque a Probar (Para la Clase)

### SQL Injection
```
' OR '1'='1
' UNION SELECT * FROM usuarios--
'; DROP TABLE metricas;--
```

### XSS
```html
<script>alert('XSS')</script>
<img src=x onerror=alert('XSS')>
javascript:alert('XSS')
```

### Path Traversal
```
../../../etc/passwd
....//....//etc/passwd
%2e%2e%2f%2e%2e%2fetc/passwd
```

### CSRF
- Crear formulario en otro dominio apuntando a la aplicación
- Intentar enviar sin token CSRF

### Brute Force
- Intentar login más de 5 veces con contraseña incorrecta
- Verificar que se bloquea el acceso

### Bot Detection
- Enviar formulario en menos de 3 segundos
- Rellenar campos honeypot

---

## ✅ Checklist de Seguridad

- [x] Prepared Statements en todas las queries
- [x] CSRF tokens en todos los formularios
- [x] Validación de entrada en servidor
- [x] Sanitización de salida (XSS)
- [x] Headers de seguridad HTTP
- [x] Rate limiting en login/registro
- [x] Sesiones seguras (HttpOnly, Secure, SameSite)
- [x] Logging de eventos de seguridad
- [x] WAF para detección de ataques
- [x] File upload seguro
- [x] Password hashing (bcrypt)
- [x] Honeypot anti-bot
- [x] Error handling sin exponer información

---

## 📚 Referencias

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)

---

**Última actualización:** Enero 2025
