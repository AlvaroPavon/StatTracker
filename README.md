# StatTracker 📊

![Version](https://img.shields.io/badge/version-1.2-blue)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php)
![License](https://img.shields.io/badge/license-MIT-green)
![Tests](https://img.shields.io/badge/tests-passing-brightgreen)
![Security](https://img.shields.io/badge/security-10%20layers-red)

## 📖 Visión General

**StatTracker** es una aplicación web moderna y **ultra-segura** para el registro, seguimiento y gestión de estadísticas de salud personales. Diseñada como laboratorio de seguridad, implementa **10 capas de defensa** contra ataques.

### ✨ Características Principales

| Categoría | Características |
|-----------|----------------|
| **Funcionalidad** | 📈 Registro de métricas • 📊 Cálculo automático de IMC • 📁 Historial completo • 👤 Gestión de perfil |
| **Seguridad** | 🔐 Contraseñas con Argon2id • 🛡️ WAF integrado • 🔒 Rate Limiting • 🚫 Anti-DDoS • 🔑 2FA/MFA |
| **Calidad** | ✅ 85%+ cobertura de tests • 📝 Documentación completa • 🏗️ Arquitectura MVC |

### 🎯 ¿Para quién es esta aplicación?

* **Usuarios**: Personas que desean monitorizar su estado físico
* **Desarrolladores**: Ejemplo de implementación de seguridad en PHP
* **Estudiantes**: Laboratorio de pruebas de penetración (con permisos)

---

## 🛠️ Requisitos del Sistema

| Requisito | Versión |
|-----------|---------|
| **PHP** | 8.0 o superior |
| **MySQL/MariaDB** | 5.7+ / 10.3+ |
| **Composer** | 2.x |
| **Extensiones PHP** | pdo_mysql, mbstring, json, openssl |

---

## 🚀 Instalación Rápida

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/stattracker.git
cd stattracker
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar la base de datos

```bash
mysql -u root -p -e "CREATE DATABASE proyecto_imc"
mysql -u root -p proyecto_imc < database.sql
```

### 4. Configurar conexión

Edita `database_connection.php` si es necesario:

```php
$host = 'localhost';
$dbname = 'proyecto_imc';
$username = 'root';
$password = '';
```

### 5. Iniciar servidor

```bash
php -S localhost:8000
```

### 6. Acceder

Abre tu navegador en: `http://localhost:8000`

> 📋 Para instrucciones detalladas con XAMPP, consulta [INSTALACION_XAMPP.md](INSTALACION_XAMPP.md)

---

## 📁 Estructura del Proyecto

```
StatTracker/
├── src/                    # Clases principales
│   ├── Auth.php            # Autenticación (registro, login)
│   ├── User.php            # Gestión de perfil
│   ├── Metrics.php         # Métricas de salud
│   ├── Security.php        # Validaciones centralizadas
│   ├── CryptoFortress.php  # Criptografía avanzada
│   ├── SessionManager.php  # Gestión segura de sesiones
│   ├── SecurityFirewall.php # WAF
│   ├── TwoFactorAuth.php   # Autenticación 2FA
│   └── ...                 # Otras clases de seguridad
├── tests/                  # Tests unitarios y de integración
├── docs/                   # Documentación completa
├── coverage/               # Informes de cobertura
├── css/                    # Estilos CSS
├── js/                     # JavaScript
├── uploads/                # Fotos de perfil
├── database.sql            # Esquema de base de datos
└── *.php                   # Archivos de interfaz
```

---

## 🔒 Arquitectura de Seguridad

StatTracker implementa **10 capas de defensa**:

```
CAPA 0:  CryptoFortress         → Verificación de integridad criptográfica
CAPA 1:  ImpenetrableDefense    → Bloqueo de IPs + Anti-DDoS
CAPA 2:  UltimateShield         → 100+ patrones de detección
CAPA 3:  AdvancedProtection     → Host Header + HTTP Parameter Pollution
CAPA 4:  SecurityFirewall       → WAF (SQL Injection, XSS, Path Traversal)
CAPA 5:  SecurityHeaders        → CSP, HSTS, X-Frame-Options
CAPA 6:  SessionManager         → Cookies seguras, fingerprinting
CAPA 7:  ImpenetrableDefense    → Account lockout, honey accounts
CAPA 8:  TwoFactorAuth          → 2FA/MFA con TOTP
CAPA 9:  CryptoFortress         → Argon2id, AES-256-GCM
CAPA 10: SupplyChainGuard       → Verificación de integridad de archivos
```

> 🔐 Para documentación detallada, consulta [SECURITY.md](SECURITY.md) y [docs/seguridad-tecnica.md](docs/seguridad-tecnica.md)

---

## 🧪 Testing

### Ejecutar Tests

```bash
# Todos los tests
vendor/bin/phpunit

# Con output detallado
vendor/bin/phpunit --testdox

# Tests específicos
vendor/bin/phpunit --filter Auth
vendor/bin/phpunit --filter Metrics

# Generar cobertura HTML
vendor/bin/phpunit --coverage-html coverage
```

### Métricas de Testing

| Métrica | Valor |
|---------|-------|
| Tests unitarios | 26+ |
| Cobertura de código | 85%+ |
| Casos de prueba E2E | 20 |

---

## 📚 Documentación

### Índice Principal

| Documento | Descripción |
|-----------|-------------|
| **[docs/home.md](docs/home.md)** | 📋 **Índice central de documentación** |
| [SECURITY.md](SECURITY.md) | Arquitectura de seguridad (10 capas) |
| [docs/seguridad-tecnica.md](docs/seguridad-tecnica.md) | Detalles técnicos de seguridad |
| [docs/manual-usuario.md](docs/manual-usuario.md) | Manual para usuarios finales |
| [INSTALACION_XAMPP.md](INSTALACION_XAMPP.md) | Guía de instalación con XAMPP |

### Por Tema

| Tema | Documento |
|------|-----------|
| 🔑 **Securización de contraseñas** | [docs/seguridad-tecnica.md#securización-de-contraseñas](docs/seguridad-tecnica.md#securización-de-contraseñas) |
| 🔐 **Gestión de sesiones** | [docs/seguridad-tecnica.md#gestión-de-sesiones](docs/seguridad-tecnica.md#gestión-de-sesiones) |
| 📱 **2FA/MFA** | [docs/seguridad-tecnica.md#autenticación-de-dos-factores-2fa](docs/seguridad-tecnica.md#autenticación-de-dos-factores-2fa) |
| 🛡️ **WAF y protecciones** | [SECURITY.md#protecciones-implementadas](SECURITY.md#-protecciones-implementadas) |
| 📊 **Cobertura de tests** | [docs/coverage-analisis.md](docs/coverage-analisis.md) |
| 🧪 **Pruebas E2E** | [docs/system-test-report.md](docs/system-test-report.md) |

---

## 💻 Comandos Útiles

```bash
# Desarrollo
composer install              # Instalar dependencias
php -S localhost:8000         # Servidor de desarrollo

# Testing
vendor/bin/phpunit            # Ejecutar tests
vendor/bin/phpunit --testdox  # Salida legible
vendor/bin/phpunit --coverage-html coverage  # Cobertura

# Base de Datos
mysql -u root -p proyecto_imc < database.sql  # Importar esquema
```

---

## 🔄 Historial de Versiones

### v1.2 (Agosto 2025)
* 🔒 **CAPTCHA matemático** en registro y login (después de intentos fallidos)
* 🚨 **Sistema de alertas de login sospechoso** (dispositivo nuevo, ubicación, hora inusual)
* 🛡️ Prevención mejorada de enumeración de usuarios
* 📝 Documentación actualizada

### v1.1 (Agosto 2025)
* 🔐 10 capas de seguridad implementadas
* 🔑 Autenticación 2FA con TOTP
* 🛡️ WAF con 100+ patrones de detección
* 📝 Documentación técnica de seguridad completa

### v1.0 (Enero 2025)
* ✨ Implementación inicial del MVP
* ✅ Sistema de autenticación completo
* ✅ Gestión de métricas de salud
* ✅ Tests unitarios (>85% cobertura)

---

## 🚀 Roadmap

### v1.2 (Planificado)
* 📧 Recuperación de contraseña por email
* 📊 Gráficos de evolución de métricas
* 📱 Mejoras responsive para móviles

### v2.0 (Futuro)
* 📤 Exportación de datos (PDF, CSV)
* 🔔 Notificaciones y recordatorios
* 🎯 Objetivos personalizados

---

## 📜 Licencia

Este proyecto fue desarrollado como parte de la práctica de **Puesta en Producción Segura** en el IES Zaidín-Vergeles.

**Uso académico y educativo.**

---

## 👥 Autores

* **Equipo StatTracker** - IES Zaidín-Vergeles

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, considera darle una estrella ⭐**

Hecho con ❤️ por el equipo StatTracker

</div>
