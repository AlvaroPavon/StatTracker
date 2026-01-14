# StatTracker - Security Lab Application

## Descripción del Proyecto
Aplicación PHP (StatTracker) para monitorizar métricas físicas que sirve como **laboratorio de seguridad** para que estudiantes intenten atacarla. El objetivo es tener una aplicación extremadamente fortificada.

## Estado Actual
- **Tipo:** Aplicación PHP con seguridad avanzada
- **Backend:** PHP 8.2
- **Base de Datos:** MySQL
- **Testing:** PHPUnit
- **Dependencias:** Composer
- **CI/CD:** GitHub Actions

## Implementado

### Seguridad (Completado)
- ✅ WAF (Web Application Firewall)
- ✅ CSP (Content Security Policy)
- ✅ SRI (Subresource Integrity)
- ✅ Honeypots
- ✅ Rate Limiting
- ✅ Protección CSRF
- ✅ Protección XSS
- ✅ Protección SQL Injection
- ✅ File Integrity Monitoring
- ✅ Timing Attack Protection
- ✅ Criptografía con libsodium (CryptoFortress)
- ✅ Supply Chain Security
- ✅ Documentación SECURITY.md

### Clases de Seguridad Creadas
- `SecurityFirewall`
- `SessionManager`
- `Honeypot`
- `AdvancedProtection`
- `ErrorHandler`
- `UltimateShield`
- `FileIntegrityChecker`
- `TimingSafe`
- `SupplyChainGuard`
- `CryptoFortress`
- `SubresourceIntegrity`

### GitHub Actions Workflows
- `php-ci.yml` - CI principal
- `release-production.yml` - Releases de producción
- `security-audit.yml` - Auditoría de seguridad
- `owasp-scan.yml` - Escaneo OWASP ZAP
- `supply-chain-security.yml` - Seguridad de cadena de suministro

## Problemas Resueltos

### Issue #1: composer.lock desincronizado (Diciembre 2025)
- **Problema:** `phpstan/phpstan` fue añadido a `composer.json` pero `composer.lock` no se actualizó
- **Causa:** El agente anterior no ejecutó `composer update`
- **Solución:** Se añadió manualmente la entrada de `phpstan/phpstan` al `composer.lock` y se actualizó el `content-hash`

## Validación Pendiente por Usuario

1. **Subir composer.lock a GitHub** - El usuario debe hacer commit del archivo actualizado
2. **Verificar workflows** - Confirmar que GitHub Actions se ejecutan correctamente
3. **Crear release de prueba** - Validar workflow `release-production.yml`

## Arquitectura de Archivos
```
/app/
├── src/                    # Lógica de negocio y clases de seguridad
├── *.php                   # Puntos de entrada (index.php, login.php, etc.)
├── .github/workflows/      # GitHub Actions
├── vendor/                 # Dependencias (Composer)
├── composer.json           # Definición de dependencias
├── composer.lock           # Lock file de dependencias (ACTUALIZADO)
├── security_init.php       # Inicialización de seguridad
└── SECURITY.md             # Documentación de seguridad
```

## Última Actualización
- **Fecha:** Diciembre 2025
- **Acción:** Sincronización de composer.lock con composer.json (phpstan/phpstan añadido)
