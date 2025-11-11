# StatTracker 📊

![Version](https://img.shields.io/badge/version-1.0-blue)
![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?logo=php)
![License](https://img.shields.io/badge/license-MIT-green)
![Tests](https://img.shields.io/badge/tests-passing-brightgreen)

## 📖 Visión General

**StatTracker** es una aplicación web moderna y segura para el registro, seguimiento y gestión de estadísticas de salud personales. Permite a los usuarios monitorizar sus métricas corporales (peso, altura, IMC) a lo largo del tiempo de manera sencilla y efectiva.

### ✨ Características Principales

* 🔐 **Sistema de autenticación seguro** - Registro y login con contraseñas cifradas
* 📈 **Registro de métricas** - Peso, altura con cálculo automático de IMC
* 📊 **Historial completo** - Visualización de toda la evolución de tus datos
* 👤 **Gestión de perfil** - Actualiza tu información y contraseña
* 🔒 **Privacidad garantizada** - Cada usuario solo accede a sus propios datos
* ✅ **Código probado** - Más de 85% de cobertura con tests unitarios

### 🎯 ¿Para quién es esta aplicación?

* Personas que desean monitorizar su estado físico
* Usuarios siguiendo programas de pérdida/ganancia de peso
* Profesionales de la salud registrando datos de pacientes
* Cualquier persona interesada en llevar un control de sus métricas corporales

---

## 📚 Documentación Completa

Este README proporciona información básica de instalación y ejecución. Para documentación completa, consulta:

| Documento | Descripción |
|-----------|-------------|
| **[Manual de Usuario](docs/manual-usuario.adoc)** | Guía completa de uso de la aplicación |
| **[Mockups](docs/mockups/)** | Diagramas visuales de las pantallas y flujos |
| **[Análisis de Cobertura](docs/coverage-analisis.adoc)** | Informe detallado de cobertura de código |
| **[Informe de Pruebas de Sistema](docs/system-test-report.adoc)** | Resultados de pruebas E2E |
| **[Notas de Entrevista](docs/entrevista-notas.adoc)** | Requisitos y decisiones del proyecto |

> 💡 **Tip**: Si eres usuario final, empieza por el [Manual de Usuario](docs/manual-usuario.adoc). Si eres desarrollador, revisa los documentos técnicos de cobertura y pruebas.

---

## 🛠️ Requisitos del Sistema

### Para Ejecutar la Aplicación

* **PHP**: 7.4 o superior
* **Servidor Web**: Apache o Nginx
* **Base de Datos**: MySQL 5.7+ / MariaDB 10.3+
* **Composer**: Para gestión de dependencias
* **Xdebug/PCOV**: (Opcional) Para generar informes de cobertura

### Para Desarrollo

* **PHPUnit**: Framework de testing (instalado via Composer)
* **Git**: Control de versiones

---

## ⚙️ Instalación y Configuración

### 1. Clonar el Repositorio

```bash
git clone [URL_DEL_REPOSITORIO]
cd StatTracker
```

### 2. Instalar Dependencias

```bash
composer install
```

### 3. Configurar Base de Datos

**a. Crear la base de datos:**

```sql
CREATE DATABASE stattracker;
```

**b. Importar el esquema:**

```bash
mysql -u tu_usuario -p stattracker < database.sql
```

**c. Configurar la conexión:**

Edita el archivo `database_connection.php` con tus credenciales:

```php
$host = 'localhost';
$db   = 'stattracker';
$user = 'tu_usuario';
$pass = 'tu_contraseña';
```

### 4. Configurar el Servidor Web

**Ejemplo para Apache (`.htaccess` ya incluido):**

```apache
<VirtualHost *:80>
    DocumentRoot "/ruta/a/StatTracker"
    ServerName stattracker.local
    <Directory "/ruta/a/StatTracker">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 5. Iniciar el Servidor

**Opción A - Servidor de desarrollo de PHP:**

```bash
php -S localhost:8000
```

**Opción B - Apache/Nginx:**

Accede a `http://localhost/StatTracker` o tu configuración de virtual host.

---

## ✅ Ejecución de Pruebas

### Ejecutar Todas las Pruebas

```bash
vendor/bin/phpunit
```

### Ejecutar con Salida Detallada

```bash
vendor/bin/phpunit --testdox
```

### Generar Informe de Cobertura (HTML)

```bash
vendor/bin/phpunit --coverage-html coverage
```

Luego abre `coverage/index.html` en tu navegador.

### Ejecutar Tests Específicos

```bash
# Solo tests de Auth
vendor/bin/phpunit tests/AuthTest.php

# Solo tests de Metrics
vendor/bin/phpunit tests/MetricsTest.php
```

> 📊 **Cobertura actual**: ~85% de líneas | 90% de funciones | 100% de clases
> 
> Ver [Análisis de Cobertura](docs/coverage-analisis.adoc) para detalles completos.

---

## 🚀 Uso Rápido

### Para Usuarios

1. Accede a la aplicación en tu navegador
2. Regístrate con tu email y contraseña
3. Inicia sesión con tus credenciales
4. Comienza a registrar tus métricas de salud

Para guía detallada, consulta el [Manual de Usuario](docs/manual-usuario.adoc).

### Para Desarrolladores

```bash
# Instalar dependencias
composer install

# Ejecutar tests
vendor/bin/phpunit

# Generar cobertura
vendor/bin/phpunit --coverage-html coverage

# Ver estructura del proyecto
tree -L 2 -I 'vendor|node_modules'
```

---
