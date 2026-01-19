# StatTracker - Security Lab Application

## Descripción del Proyecto
Aplicación PHP (StatTracker) para monitorizar métricas físicas que sirve como **laboratorio de seguridad**. Implementa 10+ capas de defensa contra todo tipo de ataques.

## Estado Actual

| Categoría | Estado |
|-----------|--------|
| **Tipo** | Aplicación PHP con seguridad avanzada |
| **Backend** | PHP 8.2+ |
| **Base de Datos** | MySQL/MariaDB ✅ FUNCIONAL |
| **Testing** | PHPUnit (85%+ cobertura) |
| **Dependencias** | Composer |
| **CI/CD** | GitHub Actions |
| **Documentación** | ✅ Completa |
| **Versión** | 1.4 |

---

## Funcionalidades Implementadas y Verificadas ✅

### Core (Aplicación)
- ✅ Sistema de autenticación (registro, login, logout) - **VERIFICADO**
- ✅ Dashboard con gráfico de IMC - **VERIFICADO**
- ✅ Gestión de perfil de usuario
- ✅ Cambio de contraseña
- ✅ Subida de foto de perfil
- ✅ Registro de métricas de salud (peso, altura, IMC)
- ✅ Historial de métricas
- ✅ Eliminación de métricas

### Seguridad (10+ Capas)
- ✅ WAF (Web Application Firewall)
- ✅ CSP (Content Security Policy)
- ✅ SRI (Subresource Integrity)
- ✅ Honeypots
- ✅ Rate Limiting (por IP y por cuenta)
- ✅ Protección CSRF
- ✅ Protección XSS
- ✅ Protección SQL Injection
- ✅ File Integrity Monitoring
- ✅ Timing Attack Protection
- ✅ Criptografía con Argon2id/libsodium (CryptoFortress)
- ✅ Supply Chain Security
- ✅ Account Lockout (bloqueo por cuenta)
- ✅ Honey Accounts (cuentas trampa)
- ✅ Behavioral Analysis (detección de bots)
- ✅ Request Signing (anti-replay attacks)
- ✅ 2FA/MFA (autenticación de dos factores TOTP)
- ✅ Global Rate Limiting (anti-DDoS)
- ✅ IP Range Blocking (bloqueo de TOR, proxies)

### Nuevas Funcionalidades (v1.2-1.3)
- ✅ **CAPTCHA matemático** (SimpleCaptcha) - En registro y login
- ✅ **Alertas de login sospechoso** (LoginAlertSystem) - Detecta dispositivos nuevos, ubicaciones, etc.
- ✅ **Cierre automático por inactividad** (SessionTimeout.js) - 15 minutos con advertencia

---

## Clases de Seguridad

| Clase | Responsabilidad |
|-------|-----------------|
| `Auth` | Autenticación (registro, login) |
| `User` | Gestión de perfil |
| `Metrics` | Métricas de salud |
| `Security` | Validaciones centralizadas |
| `CryptoFortress` | Criptografía avanzada |
| `SessionManager` | Gestión segura de sesiones |
| `SecurityFirewall` | WAF |
| `SecurityHeaders` | Headers HTTP |
| `RateLimiter` | Control de tasa |
| `InputSanitizer` | Sanitización de entrada |
| `Honeypot` | Detección de bots (campos ocultos) |
| `AdvancedProtection` | Protecciones adicionales |
| `ErrorHandler` | Manejo de errores |
| `UltimateShield` | Patrones de detección |
| `FileIntegrityChecker` | Integridad de archivos |
| `TimingSafe` | Operaciones timing-safe |
| `SupplyChainGuard` | Seguridad de cadena de suministro |
| `SubresourceIntegrity` | SRI |
| `ImpenetrableDefense` | Defensa avanzada |
| `TwoFactorAuth` | 2FA/MFA |
| `SecurityAudit` | Logging de seguridad |
| `SimpleCaptcha` | **CAPTCHA matemático** (v1.2) |
| `LoginAlertSystem` | **Alertas de login sospechoso** (v1.2) |

### Archivos JavaScript
| Archivo | Responsabilidad |
|---------|-----------------|
| `session-timeout.js` | **Cierre automático por inactividad** (v1.3) |
| `form-validation.js` | Validación de formularios |

---

## Documentación

| Documento | Descripción |
|-----------|-------------|
| `/README.md` | Visión general del proyecto |
| `/SECURITY.md` | Arquitectura de seguridad (10 capas) |
| `/INSTALACION_XAMPP.md` | Guía de instalación XAMPP |
| `/CUMPLIMIENTO_REQUISITOS.md` | Verificación académica |
| `/docs/home.md` | **Índice central de documentación** |
| `/docs/seguridad-tecnica.md` | Detalles técnicos de seguridad |
| `/docs/manual-usuario.md` | Manual para usuarios |
| `/docs/verificacion-owasp.md` | Verificación OWASP ASVS |
| `/docs/vectores-ataque.md` | Guía de pentesting |
| `/docs/coverage-analisis.md` | Análisis de cobertura |
| `/docs/system-test-report.md` | Informe de pruebas E2E |
| `/docs/entrevista-notas.md` | Requisitos y decisiones |
| `/docs/mockups/` | Mockups y diagramas |

---

## Configuración de Tiempos de Sesión

| Parámetro | Valor | Ubicación |
|-----------|-------|-----------|
| Timeout inactividad (cliente) | 15 min | `session-timeout.js` |
| Advertencia antes de cierre | 60 seg | `session-timeout.js` |
| Timeout inactividad (servidor) | 30 min | `SessionManager.php` |
| Vida máxima de sesión | 1 hora | `SessionManager.php` |
| Regeneración de ID | 5 min | `SessionManager.php` |

---

## Historial de Versiones

### v1.3 (Agosto 2025)
- ⏱️ Cierre automático de sesión por inactividad
- 💬 Modal de advertencia antes del cierre
- 🔄 Detección de actividad (mouse, teclado, scroll, touch)
- 📡 Endpoint keep_alive para extender sesión

### v1.2 (Agosto 2025)
- 🔒 CAPTCHA matemático en registro y login
- 🚨 Sistema de alertas de login sospechoso
- 🛡️ Prevención mejorada de enumeración de usuarios

### v1.1 (Agosto 2025)
- 🔐 10 capas de seguridad implementadas
- 🔑 Autenticación 2FA con TOTP
- 🛡️ WAF con 100+ patrones de detección

### v1.0 (Enero 2025)
- ✨ Implementación inicial del MVP

---

## Arquitectura de Archivos

```
/app/
├── src/                    # Clases PHP (lógica y seguridad)
├── tests/                  # Tests unitarios
├── docs/                   # Documentación
├── coverage/               # Informes de cobertura
├── logs/                   # Logs de seguridad
├── uploads/                # Archivos subidos
├── css/                    # Estilos
├── js/                     # JavaScript (incluye session-timeout.js)
├── *.php                   # Puntos de entrada
├── keep_alive.php          # Endpoint para extender sesión
├── composer.json           # Dependencias
├── phpunit.xml             # Configuración de tests
└── database.sql            # Esquema de BD
```

---

## Última Actualización
- **Fecha:** Agosto 2025
- **Versión:** 1.3
- **Cambios:** Sistema de cierre automático por inactividad implementado
