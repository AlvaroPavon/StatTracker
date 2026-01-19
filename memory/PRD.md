# StatTracker - Security Lab Application

## Descripción del Proyecto
Aplicación PHP (StatTracker) para monitorizar métricas físicas que sirve como **laboratorio de seguridad**. Implementa 10 capas de defensa contra todo tipo de ataques.

## Estado Actual

| Categoría | Estado |
|-----------|--------|
| **Tipo** | Aplicación PHP con seguridad avanzada |
| **Backend** | PHP 8.0+ |
| **Base de Datos** | MySQL/MariaDB |
| **Testing** | PHPUnit (85%+ cobertura) |
| **Dependencias** | Composer |
| **CI/CD** | GitHub Actions |
| **Documentación** | ✅ Completa |

---

## Funcionalidades Implementadas

### Core (Aplicación)
- ✅ Sistema de autenticación (registro, login, logout)
- ✅ Gestión de perfil de usuario
- ✅ Cambio de contraseña
- ✅ Subida de foto de perfil
- ✅ Registro de métricas de salud (peso, altura, IMC)
- ✅ Historial de métricas
- ✅ Eliminación de métricas

### Seguridad (10 Capas)
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
| `Honeypot` | Detección de bots |
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
| `/docs/coverage-analisis.md` | Análisis de cobertura |
| `/docs/system-test-report.md` | Informe de pruebas E2E |
| `/docs/entrevista-notas.md` | Requisitos y decisiones |
| `/docs/mockups/` | Mockups y diagramas |

---

## GitHub Actions Workflows

- `php-ci.yml` - CI principal con tests
- `release-production.yml` - Releases de producción
- `security-audit.yml` - Auditoría de seguridad
- `owasp-scan.yml` - Escaneo OWASP ZAP
- `supply-chain-security.yml` - Seguridad de cadena de suministro

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
├── js/                     # JavaScript
├── *.php                   # Puntos de entrada
├── composer.json           # Dependencias
├── phpunit.xml             # Configuración de tests
└── database.sql            # Esquema de BD
```

---

## Última Actualización
- **Fecha:** Agosto 2025
- **Versión:** 1.1
- **Cambios:** Documentación completa actualizada y centralizada
