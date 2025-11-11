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

## 📁 Estructura del Proyecto

```
StatTracker/
├── src/                    # Clases principales (lógica de negocio)
│   ├── Auth.php           # Autenticación (registro, login)
│   ├── User.php           # Gestión de perfil y contraseñas
│   └── Metrics.php        # Gestión de métricas de salud
├── tests/                  # Tests unitarios y de integración
│   ├── AuthTest.php       # Tests de autenticación
│   ├── UserTest.php       # Tests de usuario
│   ├── MetricsTest.php    # Tests de métricas
│   └── ApiIntegrationTest.php
├── docs/                   # Documentación completa
│   ├── manual-usuario.adoc         # Manual de usuario
│   ├── coverage-analisis.adoc      # Análisis de cobertura
│   ├── system-test-report.adoc     # Informe de pruebas
│   ├── entrevista-notas.adoc       # Notas del proyecto
│   └── mockups/                    # Diagramas Mermaid
├── coverage/               # Informes de cobertura (generado)
├── database.sql           # Esquema de base de datos
├── database_connection.php # Configuración de BD
├── composer.json          # Dependencias de PHP
├── phpunit.xml            # Configuración de PHPUnit
└── README.md              # Este archivo

# Archivos de interfaz:
├── index.php              # Página de inicio
├── login.php              # Procesamiento de login
├── register.php           # Procesamiento de registro
├── dashboard.php          # Panel principal
├── profile.php            # Página de perfil
├── add_data.php           # Añadir métricas
├── get_data.php           # Obtener métricas
├── delete_data.php        # Eliminar métricas
└── update_profile.php     # Actualizar perfil
```

---

## 🏗️ Arquitectura

StatTracker sigue una arquitectura **MVC simplificada**:

### Modelo (src/)
* **Auth.php**: Lógica de autenticación
* **User.php**: Lógica de gestión de usuarios
* **Metrics.php**: Lógica de métricas de salud

### Vista (archivos .php raíz)
* Archivos PHP con HTML que renderizan la interfaz

### Controlador (archivos de procesamiento)
* Scripts PHP que procesan requests y llaman a los modelos

### Base de Datos

**Tablas principales:**

```sql
usuarios (id, nombre, apellidos, email, password, profile_pic, ...)
metricas (id, user_id, peso, altura, imc, fecha_registro, ...)
```

Ver `database.sql` para el esquema completo.

---

## 🔒 Seguridad

### Medidas Implementadas

* ✅ **Contraseñas cifradas**: Usando `password_hash()` (bcrypt)
* ✅ **Prepared Statements**: Protección contra SQL injection
* ✅ **Validación de inputs**: En servidor
* ✅ **Sesiones seguras**: Configuración PHP adecuada
* ✅ **Aislamiento de datos**: Cada usuario solo accede a lo suyo
* ✅ **Verificación de permisos**: En todas las operaciones

### Recomendaciones para Producción

* Implementar tokens CSRF
* Activar HTTPS
* Configurar headers de seguridad (CSP, HSTS)
* Implementar rate limiting
* Logs de auditoría
* Backups automáticos

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para más detalles.

---

## 🧪 Testing

### Estrategia de Testing

El proyecto implementa múltiples niveles de testing:

#### 1. Tests Unitarios (PHPUnit)

**Cobertura**: ~85% de líneas

* `AuthTest.php`: Registro, login, validaciones
* `UserTest.php`: Perfil, cambio de contraseña
* `MetricsTest.php`: CRUD de métricas, cálculo de IMC

#### 2. Tests de Integración

* `ApiIntegrationTest.php`: Pruebas de endpoints completos
* `DatabaseTest.php`: Conexión a base de datos

#### 3. Tests de Sistema (Manual/Automatizado)

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para casos de prueba E2E.

### Ejecutar Tests por Categoría

```bash
# Tests de autenticación
vendor/bin/phpunit --filter Auth

# Tests de métricas
vendor/bin/phpunit --filter Metrics

# Tests de integración
vendor/bin/phpunit --filter Integration
```

---

## 📊 Métricas del Proyecto

| Métrica | Valor |
|---------|-------|
| Líneas de código (src/) | ~350 |
| Tests unitarios | 24+ |
| Cobertura de código | 85%+ |
| Clases principales | 3 |
| Endpoints API | 8 |
| Casos de prueba E2E | 20 |

---

## 🤝 Contribuir

### Proceso de Contribución

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guía de Estilo

* Seguir PSR-12 para código PHP
* Escribir tests para nuevas funcionalidades
* Mantener cobertura >80%
* Documentar funciones públicas
* Validar inputs en servidor

---

## 🐛 Reportar Problemas

Si encuentras un bug o tienes una sugerencia:

1. Verifica que no exista un issue similar
2. Crea un nuevo issue con:
   * Descripción clara del problema
   * Pasos para reproducir
   * Comportamiento esperado vs actual
   * Screenshots (si aplica)
   * Versión de PHP y navegador

---

## 📜 Licencia

Este proyecto fue desarrollado como parte de la práctica de **Puesta en Producción Segura** en el IES Zaidín-Vergeles.

**Uso académico y educativo.**

---

## 👥 Autores

* **Equipo StatTracker** - *Desarrollo inicial* - IES Zaidín-Vergeles

---

## 🙏 Agradecimientos

* Profesor del módulo de Puesta en Producción Segura
* IES Zaidín-Vergeles
* Comunidad de PHP y PHPUnit
* Stack Overflow y documentación oficial

---

## 📞 Contacto y Soporte

* **Documentación**: Consulta la carpeta `docs/`
* **Issues**: Usa el sistema de issues de GitHub
* **Wiki**: Para más información, consulta la wiki del proyecto

---

## 🔄 Historial de Versiones

### v1.0 (Enero 2025)
* ✨ Implementación inicial del MVP
* ✅ Sistema de autenticación completo
* ✅ Gestión de métricas de salud
* ✅ Tests unitarios (>85% cobertura)
* ✅ Documentación completa
* ✅ Mockups y diagramas

---

## 🚀 Roadmap (Futuras Versiones)

### v1.1 (Planificado)
* 📧 Recuperación de contraseña por email
* 📊 Gráficos de evolución de métricas
* 📱 Mejoras responsive para móviles
* 🌐 Internacionalización (i18n)

### v2.0 (Futuro)
* 📤 Exportación de datos (PDF, CSV)
* 🔔 Notificaciones y recordatorios
* 🎯 Objetivos y metas personalizadas
* 📈 Estadísticas avanzadas

---

## 💻 Comandos Útiles

```bash
# Desarrollo
composer install              # Instalar dependencias
php -S localhost:8000         # Servidor de desarrollo

# Testing
vendor/bin/phpunit            # Ejecutar todos los tests
vendor/bin/phpunit --testdox  # Salida legible
vendor/bin/phpunit --coverage-html coverage  # Generar cobertura

# Base de Datos
mysql -u root -p stattracker < database.sql  # Importar esquema

# Ver logs (si usas Apache)
tail -f /var/log/apache2/error.log
```

---

**¿Necesitas ayuda?** Consulta el [Manual de Usuario](docs/manual-usuario.adoc) o revisa la documentación técnica en la carpeta `docs/`.

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, considera darle una estrella ⭐**

Hecho con ❤️ por el equipo StatTracker

</div>

## 📚 Documentación Completa

Este README proporciona información básica de instalación y ejecución. Para documentación completa, consulta:

| Documento | Descripción |
|## 📁 Estructura del Proyecto

```
StatTracker/
├── src/                    # Clases principales (lógica de negocio)
│   ├── Auth.php           # Autenticación (registro, login)
│   ├── User.php           # Gestión de perfil y contraseñas
│   └── Metrics.php        # Gestión de métricas de salud
├── tests/                  # Tests unitarios y de integración
│   ├── AuthTest.php       # Tests de autenticación
│   ├── UserTest.php       # Tests de usuario
│   ├── MetricsTest.php    # Tests de métricas
│   └── ApiIntegrationTest.php
├── docs/                   # Documentación completa
│   ├── manual-usuario.adoc         # Manual de usuario
│   ├── coverage-analisis.adoc      # Análisis de cobertura
│   ├── system-test-report.adoc     # Informe de pruebas
│   ├── entrevista-notas.adoc       # Notas del proyecto
│   └── mockups/                    # Diagramas Mermaid
├── coverage/               # Informes de cobertura (generado)
├── database.sql           # Esquema de base de datos
├── database_connection.php # Configuración de BD
├── composer.json          # Dependencias de PHP
├── phpunit.xml            # Configuración de PHPUnit
└── README.md              # Este archivo

# Archivos de interfaz:
├── index.php              # Página de inicio
├── login.php              # Procesamiento de login
├── register.php           # Procesamiento de registro
├── dashboard.php          # Panel principal
├── profile.php            # Página de perfil
├── add_data.php           # Añadir métricas
├── get_data.php           # Obtener métricas
├── delete_data.php        # Eliminar métricas
└── update_profile.php     # Actualizar perfil
```

---

## 🏗️ Arquitectura

StatTracker sigue una arquitectura **MVC simplificada**:

### Modelo (src/)
* **Auth.php**: Lógica de autenticación
* **User.php**: Lógica de gestión de usuarios
* **Metrics.php**: Lógica de métricas de salud

### Vista (archivos .php raíz)
* Archivos PHP con HTML que renderizan la interfaz

### Controlador (archivos de procesamiento)
* Scripts PHP que procesan requests y llaman a los modelos

### Base de Datos

**Tablas principales:**

```sql
usuarios (id, nombre, apellidos, email, password, profile_pic, ...)
metricas (id, user_id, peso, altura, imc, fecha_registro, ...)
```

Ver `database.sql` para el esquema completo.

---

## 🔒 Seguridad

### Medidas Implementadas

* ✅ **Contraseñas cifradas**: Usando `password_hash()` (bcrypt)
* ✅ **Prepared Statements**: Protección contra SQL injection
* ✅ **Validación de inputs**: En servidor
* ✅ **Sesiones seguras**: Configuración PHP adecuada
* ✅ **Aislamiento de datos**: Cada usuario solo accede a lo suyo
* ✅ **Verificación de permisos**: En todas las operaciones

### Recomendaciones para Producción

* Implementar tokens CSRF
* Activar HTTPS
* Configurar headers de seguridad (CSP, HSTS)
* Implementar rate limiting
* Logs de auditoría
* Backups automáticos

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para más detalles.

---

## 🧪 Testing

### Estrategia de Testing

El proyecto implementa múltiples niveles de testing:

#### 1. Tests Unitarios (PHPUnit)

**Cobertura**: ~85% de líneas

* `AuthTest.php`: Registro, login, validaciones
* `UserTest.php`: Perfil, cambio de contraseña
* `MetricsTest.php`: CRUD de métricas, cálculo de IMC

#### 2. Tests de Integración

* `ApiIntegrationTest.php`: Pruebas de endpoints completos
* `DatabaseTest.php`: Conexión a base de datos

#### 3. Tests de Sistema (Manual/Automatizado)

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para casos de prueba E2E.

### Ejecutar Tests por Categoría

```bash
# Tests de autenticación
vendor/bin/phpunit --filter Auth

# Tests de métricas
vendor/bin/phpunit --filter Metrics

# Tests de integración
vendor/bin/phpunit --filter Integration
```

---

## 📊 Métricas del Proyecto

| Métrica | Valor |
|---------|-------|
| Líneas de código (src/) | ~350 |
| Tests unitarios | 24+ |
| Cobertura de código | 85%+ |
| Clases principales | 3 |
| Endpoints API | 8 |
| Casos de prueba E2E | 20 |

---

## 🤝 Contribuir

### Proceso de Contribución

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guía de Estilo

* Seguir PSR-12 para código PHP
* Escribir tests para nuevas funcionalidades
* Mantener cobertura >80%
* Documentar funciones públicas
* Validar inputs en servidor

---

## 🐛 Reportar Problemas

Si encuentras un bug o tienes una sugerencia:

1. Verifica que no exista un issue similar
2. Crea un nuevo issue con:
   * Descripción clara del problema
   * Pasos para reproducir
   * Comportamiento esperado vs actual
   * Screenshots (si aplica)
   * Versión de PHP y navegador

---

## 📜 Licencia

Este proyecto fue desarrollado como parte de la práctica de **Puesta en Producción Segura** en el IES Zaidín-Vergeles.

**Uso académico y educativo.**

---

## 👥 Autores

* **Equipo StatTracker** - *Desarrollo inicial* - IES Zaidín-Vergeles

---

## 🙏 Agradecimientos

* Profesor del módulo de Puesta en Producción Segura
* IES Zaidín-Vergeles
* Comunidad de PHP y PHPUnit
* Stack Overflow y documentación oficial

---

## 📞 Contacto y Soporte

* **Documentación**: Consulta la carpeta `docs/`
* **Issues**: Usa el sistema de issues de GitHub
* **Wiki**: Para más información, consulta la wiki del proyecto

---

## 🔄 Historial de Versiones

### v1.0 (Enero 2025)
* ✨ Implementación inicial del MVP
* ✅ Sistema de autenticación completo
* ✅ Gestión de métricas de salud
* ✅ Tests unitarios (>85% cobertura)
* ✅ Documentación completa
* ✅ Mockups y diagramas

---

## 🚀 Roadmap (Futuras Versiones)

### v1.1 (Planificado)
* 📧 Recuperación de contraseña por email
* 📊 Gráficos de evolución de métricas
* 📱 Mejoras responsive para móviles
* 🌐 Internacionalización (i18n)

### v2.0 (Futuro)
* 📤 Exportación de datos (PDF, CSV)
* 🔔 Notificaciones y recordatorios
* 🎯 Objetivos y metas personalizadas
* 📈 Estadísticas avanzadas

---

## 💻 Comandos Útiles

```bash
# Desarrollo
composer install              # Instalar dependencias
php -S localhost:8000         # Servidor de desarrollo

# Testing
vendor/bin/phpunit            # Ejecutar todos los tests
vendor/bin/phpunit --testdox  # Salida legible
vendor/bin/phpunit --coverage-html coverage  # Generar cobertura

# Base de Datos
mysql -u root -p stattracker < database.sql  # Importar esquema

# Ver logs (si usas Apache)
tail -f /var/log/apache2/error.log
```

---

**¿Necesitas ayuda?** Consulta el [Manual de Usuario](docs/manual-usuario.adoc) o revisa la documentación técnica en la carpeta `docs/`.

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, considera darle una estrella ⭐**

Hecho con ❤️ por el equipo StatTracker

</div>## 📁 Estructura del Proyecto

```
StatTracker/
├── src/                    # Clases principales (lógica de negocio)
│   ├── Auth.php           # Autenticación (registro, login)
│   ├── User.php           # Gestión de perfil y contraseñas
│   └── Metrics.php        # Gestión de métricas de salud
├── tests/                  # Tests unitarios y de integración
│   ├── AuthTest.php       # Tests de autenticación
│   ├── UserTest.php       # Tests de usuario
│   ├── MetricsTest.php    # Tests de métricas
│   └── ApiIntegrationTest.php
├── docs/                   # Documentación completa
│   ├── manual-usuario.adoc         # Manual de usuario
│   ├── coverage-analisis.adoc      # Análisis de cobertura
│   ├── system-test-report.adoc     # Informe de pruebas
│   ├── entrevista-notas.adoc       # Notas del proyecto
│   └── mockups/                    # Diagramas Mermaid
├── coverage/               # Informes de cobertura (generado)
├── database.sql           # Esquema de base de datos
├── database_connection.php # Configuración de BD
├── composer.json          # Dependencias de PHP
├── phpunit.xml            # Configuración de PHPUnit
└── README.md              # Este archivo

# Archivos de interfaz:
├── index.php              # Página de inicio
├── login.php              # Procesamiento de login
├── register.php           # Procesamiento de registro
├── dashboard.php          # Panel principal
├── profile.php            # Página de perfil
├── add_data.php           # Añadir métricas
├── get_data.php           # Obtener métricas
├── delete_data.php        # Eliminar métricas
└── update_profile.php     # Actualizar perfil
```

---

## 🏗️ Arquitectura

StatTracker sigue una arquitectura **MVC simplificada**:

### Modelo (src/)
* **Auth.php**: Lógica de autenticación
* **User.php**: Lógica de gestión de usuarios
* **Metrics.php**: Lógica de métricas de salud

### Vista (archivos .php raíz)
* Archivos PHP con HTML que renderizan la interfaz

### Controlador (archivos de procesamiento)
* Scripts PHP que procesan requests y llaman a los modelos

### Base de Datos

**Tablas principales:**

```sql
usuarios (id, nombre, apellidos, email, password, profile_pic, ...)
metricas (id, user_id, peso, altura, imc, fecha_registro, ...)
```

Ver `database.sql` para el esquema completo.

---

## 🔒 Seguridad

### Medidas Implementadas

* ✅ **Contraseñas cifradas**: Usando `password_hash()` (bcrypt)
* ✅ **Prepared Statements**: Protección contra SQL injection
* ✅ **Validación de inputs**: En servidor
* ✅ **Sesiones seguras**: Configuración PHP adecuada
* ✅ **Aislamiento de datos**: Cada usuario solo accede a lo suyo
* ✅ **Verificación de permisos**: En todas las operaciones

### Recomendaciones para Producción

* Implementar tokens CSRF
* Activar HTTPS
* Configurar headers de seguridad (CSP, HSTS)
* Implementar rate limiting
* Logs de auditoría
* Backups automáticos

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para más detalles.

---

## 🧪 Testing

### Estrategia de Testing

El proyecto implementa múltiples niveles de testing:

#### 1. Tests Unitarios (PHPUnit)

**Cobertura**: ~85% de líneas

* `AuthTest.php`: Registro, login, validaciones
* `UserTest.php`: Perfil, cambio de contraseña
* `MetricsTest.php`: CRUD de métricas, cálculo de IMC

#### 2. Tests de Integración

* `ApiIntegrationTest.php`: Pruebas de endpoints completos
* `DatabaseTest.php`: Conexión a base de datos

#### 3. Tests de Sistema (Manual/Automatizado)

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para casos de prueba E2E.

### Ejecutar Tests por Categoría

```bash
# Tests de autenticación
vendor/bin/phpunit --filter Auth

# Tests de métricas
vendor/bin/phpunit --filter Metrics

# Tests de integración
vendor/bin/phpunit --filter Integration
```

---

## 📊 Métricas del Proyecto

| Métrica | Valor |
|---------|-------|
| Líneas de código (src/) | ~350 |
| Tests unitarios | 24+ |
| Cobertura de código | 85%+ |
| Clases principales | 3 |
| Endpoints API | 8 |
| Casos de prueba E2E | 20 |

---

## 🤝 Contribuir

### Proceso de Contribución

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guía de Estilo

* Seguir PSR-12 para código PHP
* Escribir tests para nuevas funcionalidades
* Mantener cobertura >80%
* Documentar funciones públicas
* Validar inputs en servidor

---

## 🐛 Reportar Problemas

Si encuentras un bug o tienes una sugerencia:

1. Verifica que no exista un issue similar
2. Crea un nuevo issue con:
   * Descripción clara del problema
   * Pasos para reproducir
   * Comportamiento esperado vs actual
   * Screenshots (si aplica)
   * Versión de PHP y navegador

---

## 📜 Licencia

Este proyecto fue desarrollado como parte de la práctica de **Puesta en Producción Segura** en el IES Zaidín-Vergeles.

**Uso académico y educativo.**

---

## 👥 Autores

* **Equipo StatTracker** - *Desarrollo inicial* - IES Zaidín-Vergeles

---

## 🙏 Agradecimientos

* Profesor del módulo de Puesta en Producción Segura
* IES Zaidín-Vergeles
* Comunidad de PHP y PHPUnit
* Stack Overflow y documentación oficial

---

## 📞 Contacto y Soporte

* **Documentación**: Consulta la carpeta `docs/`
* **Issues**: Usa el sistema de issues de GitHub
* **Wiki**: Para más información, consulta la wiki del proyecto

---

## 🔄 Historial de Versiones

### v1.0 (Enero 2025)
* ✨ Implementación inicial del MVP
* ✅ Sistema de autenticación completo
* ✅ Gestión de métricas de salud
* ✅ Tests unitarios (>85% cobertura)
* ✅ Documentación completa
* ✅ Mockups y diagramas

---

## 🚀 Roadmap (Futuras Versiones)

### v1.1 (Planificado)
* 📧 Recuperación de contraseña por email
* 📊 Gráficos de evolución de métricas
* 📱 Mejoras responsive para móviles
* 🌐 Internacionalización (i18n)

### v2.0 (Futuro)
* 📤 Exportación de datos (PDF, CSV)
* 🔔 Notificaciones y recordatorios
* 🎯 Objetivos y metas personalizadas
* 📈 Estadísticas avanzadas

---

## 💻 Comandos Útiles

```bash
# Desarrollo
composer install              # Instalar dependencias
php -S localhost:8000         # Servidor de desarrollo

# Testing
vendor/bin/phpunit            # Ejecutar todos los tests
vendor/bin/phpunit --testdox  # Salida legible
vendor/bin/phpunit --coverage-html coverage  # Generar cobertura

# Base de Datos
mysql -u root -p stattracker < database.sql  # Importar esquema

# Ver logs (si usas Apache)
tail -f /var/log/apache2/error.log
```

---

**¿Necesitas ayuda?** Consulta el [Manual de Usuario](docs/manual-usuario.adoc) o revisa la documentación técnica en la carpeta `docs/`.

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, considera darle una estrella ⭐**

Hecho con ❤️ por el equipo StatTracker

</div>## 📁 Estructura del Proyecto

```
StatTracker/
├── src/                    # Clases principales (lógica de negocio)
│   ├── Auth.php           # Autenticación (registro, login)
│   ├── User.php           # Gestión de perfil y contraseñas
│   └── Metrics.php        # Gestión de métricas de salud
├── tests/                  # Tests unitarios y de integración
│   ├── AuthTest.php       # Tests de autenticación
│   ├── UserTest.php       # Tests de usuario
│   ├── MetricsTest.php    # Tests de métricas
│   └── ApiIntegrationTest.php
├── docs/                   # Documentación completa
│   ├── manual-usuario.adoc         # Manual de usuario
│   ├── coverage-analisis.adoc      # Análisis de cobertura
│   ├── system-test-report.adoc     # Informe de pruebas
│   ├── entrevista-notas.adoc       # Notas del proyecto
│   └── mockups/                    # Diagramas Mermaid
├── coverage/               # Informes de cobertura (generado)
├── database.sql           # Esquema de base de datos
├── database_connection.php # Configuración de BD
├── composer.json          # Dependencias de PHP
├── phpunit.xml            # Configuración de PHPUnit
└── README.md              # Este archivo

# Archivos de interfaz:
├── index.php              # Página de inicio
├── login.php              # Procesamiento de login
├── register.php           # Procesamiento de registro
├── dashboard.php          # Panel principal
├── profile.php            # Página de perfil
├── add_data.php           # Añadir métricas
├── get_data.php           # Obtener métricas
├── delete_data.php        # Eliminar métricas
└── update_profile.php     # Actualizar perfil
```

---

## 🏗️ Arquitectura

StatTracker sigue una arquitectura **MVC simplificada**:

### Modelo (src/)
* **Auth.php**: Lógica de autenticación
* **User.php**: Lógica de gestión de usuarios
* **Metrics.php**: Lógica de métricas de salud

### Vista (archivos .php raíz)
* Archivos PHP con HTML que renderizan la interfaz

### Controlador (archivos de procesamiento)
* Scripts PHP que procesan requests y llaman a los modelos

### Base de Datos

**Tablas principales:**

```sql
usuarios (id, nombre, apellidos, email, password, profile_pic, ...)
metricas (id, user_id, peso, altura, imc, fecha_registro, ...)
```

Ver `database.sql` para el esquema completo.

---

## 🔒 Seguridad

### Medidas Implementadas

* ✅ **Contraseñas cifradas**: Usando `password_hash()` (bcrypt)
* ✅ **Prepared Statements**: Protección contra SQL injection
* ✅ **Validación de inputs**: En servidor
* ✅ **Sesiones seguras**: Configuración PHP adecuada
* ✅ **Aislamiento de datos**: Cada usuario solo accede a lo suyo
* ✅ **Verificación de permisos**: En todas las operaciones

### Recomendaciones para Producción

* Implementar tokens CSRF
* Activar HTTPS
* Configurar headers de seguridad (CSP, HSTS)
* Implementar rate limiting
* Logs de auditoría
* Backups automáticos

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para más detalles.

---

## 🧪 Testing

### Estrategia de Testing

El proyecto implementa múltiples niveles de testing:

#### 1. Tests Unitarios (PHPUnit)

**Cobertura**: ~85% de líneas

* `AuthTest.php`: Registro, login, validaciones
* `UserTest.php`: Perfil, cambio de contraseña
* `MetricsTest.php`: CRUD de métricas, cálculo de IMC

#### 2. Tests de Integración

* `ApiIntegrationTest.php`: Pruebas de endpoints completos
* `DatabaseTest.php`: Conexión a base de datos

#### 3. Tests de Sistema (Manual/Automatizado)

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para casos de prueba E2E.

### Ejecutar Tests por Categoría

```bash
# Tests de autenticación
vendor/bin/phpunit --filter Auth

# Tests de métricas
vendor/bin/phpunit --filter Metrics

# Tests de integración
vendor/bin/phpunit --filter Integration
```

---

## 📊 Métricas del Proyecto

| Métrica | Valor |
|---------|-------|
| Líneas de código (src/) | ~350 |
| Tests unitarios | 24+ |
| Cobertura de código | 85%+ |
| Clases principales | 3 |
| Endpoints API | 8 |
| Casos de prueba E2E | 20 |

---

## 🤝 Contribuir

### Proceso de Contribución

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guía de Estilo

* Seguir PSR-12 para código PHP
* Escribir tests para nuevas funcionalidades
* Mantener cobertura >80%
* Documentar funciones públicas
* Validar inputs en servidor

---

## 🐛 Reportar Problemas

Si encuentras un bug o tienes una sugerencia:

1. Verifica que no exista un issue similar
2. Crea un nuevo issue con:
   * Descripción clara del problema
   * Pasos para reproducir
   * Comportamiento esperado vs actual
   * Screenshots (si aplica)
   * Versión de PHP y navegador

---

## 📜 Licencia

Este proyecto fue desarrollado como parte de la práctica de **Puesta en Producción Segura** en el IES Zaidín-Vergeles.

**Uso académico y educativo.**

---

## 👥 Autores

* **Equipo StatTracker** - *Desarrollo inicial* - IES Zaidín-Vergeles

---

## 🙏 Agradecimientos

* Profesor del módulo de Puesta en Producción Segura
* IES Zaidín-Vergeles
* Comunidad de PHP y PHPUnit
* Stack Overflow y documentación oficial

---

## 📞 Contacto y Soporte

* **Documentación**: Consulta la carpeta `docs/`
* **Issues**: Usa el sistema de issues de GitHub
* **Wiki**: Para más información, consulta la wiki del proyecto

---

## 🔄 Historial de Versiones

### v1.0 (Enero 2025)
* ✨ Implementación inicial del MVP
* ✅ Sistema de autenticación completo
* ✅ Gestión de métricas de salud
* ✅ Tests unitarios (>85% cobertura)
* ✅ Documentación completa
* ✅ Mockups y diagramas

---

## 🚀 Roadmap (Futuras Versiones)

### v1.1 (Planificado)
* 📧 Recuperación de contraseña por email
* 📊 Gráficos de evolución de métricas
* 📱 Mejoras responsive para móviles
* 🌐 Internacionalización (i18n)

### v2.0 (Futuro)
* 📤 Exportación de datos (PDF, CSV)
* 🔔 Notificaciones y recordatorios
* 🎯 Objetivos y metas personalizadas
* 📈 Estadísticas avanzadas

---

## 💻 Comandos Útiles

```bash
# Desarrollo
composer install              # Instalar dependencias
php -S localhost:8000         # Servidor de desarrollo

# Testing
vendor/bin/phpunit            # Ejecutar todos los tests
vendor/bin/phpunit --testdox  # Salida legible
vendor/bin/phpunit --coverage-html coverage  # Generar cobertura

# Base de Datos
mysql -u root -p stattracker < database.sql  # Importar esquema

# Ver logs (si usas Apache)
tail -f /var/log/apache2/error.log
```

---

**¿Necesitas ayuda?** Consulta el [Manual de Usuario](docs/manual-usuario.adoc) o revisa la documentación técnica en la carpeta `docs/`.

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, considera darle una estrella ⭐**

Hecho con ❤️ por el equipo StatTracker

</div>--|## 📁 Estructura del Proyecto

```
StatTracker/
├── src/                    # Clases principales (lógica de negocio)
│   ├── Auth.php           # Autenticación (registro, login)
│   ├── User.php           # Gestión de perfil y contraseñas
│   └── Metrics.php        # Gestión de métricas de salud
├── tests/                  # Tests unitarios y de integración
│   ├── AuthTest.php       # Tests de autenticación
│   ├── UserTest.php       # Tests de usuario
│   ├── MetricsTest.php    # Tests de métricas
│   └── ApiIntegrationTest.php
├── docs/                   # Documentación completa
│   ├── manual-usuario.adoc         # Manual de usuario
│   ├── coverage-analisis.adoc      # Análisis de cobertura
│   ├── system-test-report.adoc     # Informe de pruebas
│   ├── entrevista-notas.adoc       # Notas del proyecto
│   └── mockups/                    # Diagramas Mermaid
├── coverage/               # Informes de cobertura (generado)
├── database.sql           # Esquema de base de datos
├── database_connection.php # Configuración de BD
├── composer.json          # Dependencias de PHP
├── phpunit.xml            # Configuración de PHPUnit
└── README.md              # Este archivo

# Archivos de interfaz:
├── index.php              # Página de inicio
├── login.php              # Procesamiento de login
├── register.php           # Procesamiento de registro
├── dashboard.php          # Panel principal
├── profile.php            # Página de perfil
├── add_data.php           # Añadir métricas
├── get_data.php           # Obtener métricas
├── delete_data.php        # Eliminar métricas
└── update_profile.php     # Actualizar perfil
```

---

## 🏗️ Arquitectura

StatTracker sigue una arquitectura **MVC simplificada**:

### Modelo (src/)
* **Auth.php**: Lógica de autenticación
* **User.php**: Lógica de gestión de usuarios
* **Metrics.php**: Lógica de métricas de salud

### Vista (archivos .php raíz)
* Archivos PHP con HTML que renderizan la interfaz

### Controlador (archivos de procesamiento)
* Scripts PHP que procesan requests y llaman a los modelos

### Base de Datos

**Tablas principales:**

```sql
usuarios (id, nombre, apellidos, email, password, profile_pic, ...)
metricas (id, user_id, peso, altura, imc, fecha_registro, ...)
```

Ver `database.sql` para el esquema completo.

---

## 🔒 Seguridad

### Medidas Implementadas

* ✅ **Contraseñas cifradas**: Usando `password_hash()` (bcrypt)
* ✅ **Prepared Statements**: Protección contra SQL injection
* ✅ **Validación de inputs**: En servidor
* ✅ **Sesiones seguras**: Configuración PHP adecuada
* ✅ **Aislamiento de datos**: Cada usuario solo accede a lo suyo
* ✅ **Verificación de permisos**: En todas las operaciones

### Recomendaciones para Producción

* Implementar tokens CSRF
* Activar HTTPS
* Configurar headers de seguridad (CSP, HSTS)
* Implementar rate limiting
* Logs de auditoría
* Backups automáticos

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para más detalles.

---

## 🧪 Testing

### Estrategia de Testing

El proyecto implementa múltiples niveles de testing:

#### 1. Tests Unitarios (PHPUnit)

**Cobertura**: ~85% de líneas

* `AuthTest.php`: Registro, login, validaciones
* `UserTest.php`: Perfil, cambio de contraseña
* `MetricsTest.php`: CRUD de métricas, cálculo de IMC

#### 2. Tests de Integración

* `ApiIntegrationTest.php`: Pruebas de endpoints completos
* `DatabaseTest.php`: Conexión a base de datos

#### 3. Tests de Sistema (Manual/Automatizado)

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para casos de prueba E2E.

### Ejecutar Tests por Categoría

```bash
# Tests de autenticación
vendor/bin/phpunit --filter Auth

# Tests de métricas
vendor/bin/phpunit --filter Metrics

# Tests de integración
vendor/bin/phpunit --filter Integration
```

---

## 📊 Métricas del Proyecto

| Métrica | Valor |
|---------|-------|
| Líneas de código (src/) | ~350 |
| Tests unitarios | 24+ |
| Cobertura de código | 85%+ |
| Clases principales | 3 |
| Endpoints API | 8 |
| Casos de prueba E2E | 20 |

---

## 🤝 Contribuir

### Proceso de Contribución

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guía de Estilo

* Seguir PSR-12 para código PHP
* Escribir tests para nuevas funcionalidades
* Mantener cobertura >80%
* Documentar funciones públicas
* Validar inputs en servidor

---

## 🐛 Reportar Problemas

Si encuentras un bug o tienes una sugerencia:

1. Verifica que no exista un issue similar
2. Crea un nuevo issue con:
   * Descripción clara del problema
   * Pasos para reproducir
   * Comportamiento esperado vs actual
   * Screenshots (si aplica)
   * Versión de PHP y navegador

---

## 📜 Licencia

Este proyecto fue desarrollado como parte de la práctica de **Puesta en Producción Segura** en el IES Zaidín-Vergeles.

**Uso académico y educativo.**

---

## 👥 Autores

* **Equipo StatTracker** - *Desarrollo inicial* - IES Zaidín-Vergeles

---

## 🙏 Agradecimientos

* Profesor del módulo de Puesta en Producción Segura
* IES Zaidín-Vergeles
* Comunidad de PHP y PHPUnit
* Stack Overflow y documentación oficial

---

## 📞 Contacto y Soporte

* **Documentación**: Consulta la carpeta `docs/`
* **Issues**: Usa el sistema de issues de GitHub
* **Wiki**: Para más información, consulta la wiki del proyecto

---

## 🔄 Historial de Versiones

### v1.0 (Enero 2025)
* ✨ Implementación inicial del MVP
* ✅ Sistema de autenticación completo
* ✅ Gestión de métricas de salud
* ✅ Tests unitarios (>85% cobertura)
* ✅ Documentación completa
* ✅ Mockups y diagramas

---

## 🚀 Roadmap (Futuras Versiones)

### v1.1 (Planificado)
* 📧 Recuperación de contraseña por email
* 📊 Gráficos de evolución de métricas
* 📱 Mejoras responsive para móviles
* 🌐 Internacionalización (i18n)

### v2.0 (Futuro)
* 📤 Exportación de datos (PDF, CSV)
* 🔔 Notificaciones y recordatorios
* 🎯 Objetivos y metas personalizadas
* 📈 Estadísticas avanzadas

---

## 💻 Comandos Útiles

```bash
# Desarrollo
composer install              # Instalar dependencias
php -S localhost:8000         # Servidor de desarrollo

# Testing
vendor/bin/phpunit            # Ejecutar todos los tests
vendor/bin/phpunit --testdox  # Salida legible
vendor/bin/phpunit --coverage-html coverage  # Generar cobertura

# Base de Datos
mysql -u root -p stattracker < database.sql  # Importar esquema

# Ver logs (si usas Apache)
tail -f /var/log/apache2/error.log
```

---

**¿Necesitas ayuda?** Consulta el [Manual de Usuario](docs/manual-usuario.adoc) o revisa la documentación técnica en la carpeta `docs/`.

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, considera darle una estrella ⭐**

Hecho con ❤️ por el equipo StatTracker

</div>## 📁 Estructura del Proyecto

```
StatTracker/
├── src/                    # Clases principales (lógica de negocio)
│   ├── Auth.php           # Autenticación (registro, login)
│   ├── User.php           # Gestión de perfil y contraseñas
│   └── Metrics.php        # Gestión de métricas de salud
├── tests/                  # Tests unitarios y de integración
│   ├── AuthTest.php       # Tests de autenticación
│   ├── UserTest.php       # Tests de usuario
│   ├── MetricsTest.php    # Tests de métricas
│   └── ApiIntegrationTest.php
├── docs/                   # Documentación completa
│   ├── manual-usuario.adoc         # Manual de usuario
│   ├── coverage-analisis.adoc      # Análisis de cobertura
│   ├── system-test-report.adoc     # Informe de pruebas
│   ├── entrevista-notas.adoc       # Notas del proyecto
│   └── mockups/                    # Diagramas Mermaid
├── coverage/               # Informes de cobertura (generado)
├── database.sql           # Esquema de base de datos
├── database_connection.php # Configuración de BD
├── composer.json          # Dependencias de PHP
├── phpunit.xml            # Configuración de PHPUnit
└── README.md              # Este archivo

# Archivos de interfaz:
├── index.php              # Página de inicio
├── login.php              # Procesamiento de login
├── register.php           # Procesamiento de registro
├── dashboard.php          # Panel principal
├── profile.php            # Página de perfil
├── add_data.php           # Añadir métricas
├── get_data.php           # Obtener métricas
├── delete_data.php        # Eliminar métricas
└── update_profile.php     # Actualizar perfil
```

---

## 🏗️ Arquitectura

StatTracker sigue una arquitectura **MVC simplificada**:

### Modelo (src/)
* **Auth.php**: Lógica de autenticación
* **User.php**: Lógica de gestión de usuarios
* **Metrics.php**: Lógica de métricas de salud

### Vista (archivos .php raíz)
* Archivos PHP con HTML que renderizan la interfaz

### Controlador (archivos de procesamiento)
* Scripts PHP que procesan requests y llaman a los modelos

### Base de Datos

**Tablas principales:**

```sql
usuarios (id, nombre, apellidos, email, password, profile_pic, ...)
metricas (id, user_id, peso, altura, imc, fecha_registro, ...)
```

Ver `database.sql` para el esquema completo.

---

## 🔒 Seguridad

### Medidas Implementadas

* ✅ **Contraseñas cifradas**: Usando `password_hash()` (bcrypt)
* ✅ **Prepared Statements**: Protección contra SQL injection
* ✅ **Validación de inputs**: En servidor
* ✅ **Sesiones seguras**: Configuración PHP adecuada
* ✅ **Aislamiento de datos**: Cada usuario solo accede a lo suyo
* ✅ **Verificación de permisos**: En todas las operaciones

### Recomendaciones para Producción

* Implementar tokens CSRF
* Activar HTTPS
* Configurar headers de seguridad (CSP, HSTS)
* Implementar rate limiting
* Logs de auditoría
* Backups automáticos

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para más detalles.

---

## 🧪 Testing

### Estrategia de Testing

El proyecto implementa múltiples niveles de testing:

#### 1. Tests Unitarios (PHPUnit)

**Cobertura**: ~85% de líneas

* `AuthTest.php`: Registro, login, validaciones
* `UserTest.php`: Perfil, cambio de contraseña
* `MetricsTest.php`: CRUD de métricas, cálculo de IMC

#### 2. Tests de Integración

* `ApiIntegrationTest.php`: Pruebas de endpoints completos
* `DatabaseTest.php`: Conexión a base de datos

#### 3. Tests de Sistema (Manual/Automatizado)

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para casos de prueba E2E.

### Ejecutar Tests por Categoría

```bash
# Tests de autenticación
vendor/bin/phpunit --filter Auth

# Tests de métricas
vendor/bin/phpunit --filter Metrics

# Tests de integración
vendor/bin/phpunit --filter Integration
```

---

## 📊 Métricas del Proyecto

| Métrica | Valor |
|---------|-------|
| Líneas de código (src/) | ~350 |
| Tests unitarios | 24+ |
| Cobertura de código | 85%+ |
| Clases principales | 3 |
| Endpoints API | 8 |
| Casos de prueba E2E | 20 |

---

## 🤝 Contribuir

### Proceso de Contribución

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guía de Estilo

* Seguir PSR-12 para código PHP
* Escribir tests para nuevas funcionalidades
* Mantener cobertura >80%
* Documentar funciones públicas
* Validar inputs en servidor

---

## 🐛 Reportar Problemas

Si encuentras un bug o tienes una sugerencia:

1. Verifica que no exista un issue similar
2. Crea un nuevo issue con:
   * Descripción clara del problema
   * Pasos para reproducir
   * Comportamiento esperado vs actual
   * Screenshots (si aplica)
   * Versión de PHP y navegador

---

## 📜 Licencia

Este proyecto fue desarrollado como parte de la práctica de **Puesta en Producción Segura** en el IES Zaidín-Vergeles.

**Uso académico y educativo.**

---

## 👥 Autores

* **Equipo StatTracker** - *Desarrollo inicial* - IES Zaidín-Vergeles

---

## 🙏 Agradecimientos

* Profesor del módulo de Puesta en Producción Segura
* IES Zaidín-Vergeles
* Comunidad de PHP y PHPUnit
* Stack Overflow y documentación oficial

---

## 📞 Contacto y Soporte

* **Documentación**: Consulta la carpeta `docs/`
* **Issues**: Usa el sistema de issues de GitHub
* **Wiki**: Para más información, consulta la wiki del proyecto

---

## 🔄 Historial de Versiones

### v1.0 (Enero 2025)
* ✨ Implementación inicial del MVP
* ✅ Sistema de autenticación completo
* ✅ Gestión de métricas de salud
* ✅ Tests unitarios (>85% cobertura)
* ✅ Documentación completa
* ✅ Mockups y diagramas

---

## 🚀 Roadmap (Futuras Versiones)

### v1.1 (Planificado)
* 📧 Recuperación de contraseña por email
* 📊 Gráficos de evolución de métricas
* 📱 Mejoras responsive para móviles
* 🌐 Internacionalización (i18n)

### v2.0 (Futuro)
* 📤 Exportación de datos (PDF, CSV)
* 🔔 Notificaciones y recordatorios
* 🎯 Objetivos y metas personalizadas
* 📈 Estadísticas avanzadas

---

## 💻 Comandos Útiles

```bash
# Desarrollo
composer install              # Instalar dependencias
php -S localhost:8000         # Servidor de desarrollo

# Testing
vendor/bin/phpunit            # Ejecutar todos los tests
vendor/bin/phpunit --testdox  # Salida legible
vendor/bin/phpunit --coverage-html coverage  # Generar cobertura

# Base de Datos
mysql -u root -p stattracker < database.sql  # Importar esquema

# Ver logs (si usas Apache)
tail -f /var/log/apache2/error.log
```

---

**¿Necesitas ayuda?** Consulta el [Manual de Usuario](docs/manual-usuario.adoc) o revisa la documentación técnica en la carpeta `docs/`.

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, considera darle una estrella ⭐**

Hecho con ❤️ por el equipo StatTracker

</div>## 📁 Estructura del Proyecto

```
StatTracker/
├── src/                    # Clases principales (lógica de negocio)
│   ├── Auth.php           # Autenticación (registro, login)
│   ├── User.php           # Gestión de perfil y contraseñas
│   └── Metrics.php        # Gestión de métricas de salud
├── tests/                  # Tests unitarios y de integración
│   ├── AuthTest.php       # Tests de autenticación
│   ├── UserTest.php       # Tests de usuario
│   ├── MetricsTest.php    # Tests de métricas
│   └── ApiIntegrationTest.php
├── docs/                   # Documentación completa
│   ├── manual-usuario.adoc         # Manual de usuario
│   ├── coverage-analisis.adoc      # Análisis de cobertura
│   ├── system-test-report.adoc     # Informe de pruebas
│   ├── entrevista-notas.adoc       # Notas del proyecto
│   └── mockups/                    # Diagramas Mermaid
├── coverage/               # Informes de cobertura (generado)
├── database.sql           # Esquema de base de datos
├── database_connection.php # Configuración de BD
├── composer.json          # Dependencias de PHP
├── phpunit.xml            # Configuración de PHPUnit
└── README.md              # Este archivo

# Archivos de interfaz:
├── index.php              # Página de inicio
├── login.php              # Procesamiento de login
├── register.php           # Procesamiento de registro
├── dashboard.php          # Panel principal
├── profile.php            # Página de perfil
├── add_data.php           # Añadir métricas
├── get_data.php           # Obtener métricas
├── delete_data.php        # Eliminar métricas
└── update_profile.php     # Actualizar perfil
```

---

## 🏗️ Arquitectura

StatTracker sigue una arquitectura **MVC simplificada**:

### Modelo (src/)
* **Auth.php**: Lógica de autenticación
* **User.php**: Lógica de gestión de usuarios
* **Metrics.php**: Lógica de métricas de salud

### Vista (archivos .php raíz)
* Archivos PHP con HTML que renderizan la interfaz

### Controlador (archivos de procesamiento)
* Scripts PHP que procesan requests y llaman a los modelos

### Base de Datos

**Tablas principales:**

```sql
usuarios (id, nombre, apellidos, email, password, profile_pic, ...)
metricas (id, user_id, peso, altura, imc, fecha_registro, ...)
```

Ver `database.sql` para el esquema completo.

---

## 🔒 Seguridad

### Medidas Implementadas

* ✅ **Contraseñas cifradas**: Usando `password_hash()` (bcrypt)
* ✅ **Prepared Statements**: Protección contra SQL injection
* ✅ **Validación de inputs**: En servidor
* ✅ **Sesiones seguras**: Configuración PHP adecuada
* ✅ **Aislamiento de datos**: Cada usuario solo accede a lo suyo
* ✅ **Verificación de permisos**: En todas las operaciones

### Recomendaciones para Producción

* Implementar tokens CSRF
* Activar HTTPS
* Configurar headers de seguridad (CSP, HSTS)
* Implementar rate limiting
* Logs de auditoría
* Backups automáticos

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para más detalles.

---

## 🧪 Testing

### Estrategia de Testing

El proyecto implementa múltiples niveles de testing:

#### 1. Tests Unitarios (PHPUnit)

**Cobertura**: ~85% de líneas

* `AuthTest.php`: Registro, login, validaciones
* `UserTest.php`: Perfil, cambio de contraseña
* `MetricsTest.php`: CRUD de métricas, cálculo de IMC

#### 2. Tests de Integración

* `ApiIntegrationTest.php`: Pruebas de endpoints completos
* `DatabaseTest.php`: Conexión a base de datos

#### 3. Tests de Sistema (Manual/Automatizado)

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para casos de prueba E2E.

### Ejecutar Tests por Categoría

```bash
# Tests de autenticación
vendor/bin/phpunit --filter Auth

# Tests de métricas
vendor/bin/phpunit --filter Metrics

# Tests de integración
vendor/bin/phpunit --filter Integration
```

---

## 📊 Métricas del Proyecto

| Métrica | Valor |
|---------|-------|
| Líneas de código (src/) | ~350 |
| Tests unitarios | 24+ |
| Cobertura de código | 85%+ |
| Clases principales | 3 |
| Endpoints API | 8 |
| Casos de prueba E2E | 20 |

---

## 🤝 Contribuir

### Proceso de Contribución

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guía de Estilo

* Seguir PSR-12 para código PHP
* Escribir tests para nuevas funcionalidades
* Mantener cobertura >80%
* Documentar funciones públicas
* Validar inputs en servidor

---

## 🐛 Reportar Problemas

Si encuentras un bug o tienes una sugerencia:

1. Verifica que no exista un issue similar
2. Crea un nuevo issue con:
   * Descripción clara del problema
   * Pasos para reproducir
   * Comportamiento esperado vs actual
   * Screenshots (si aplica)
   * Versión de PHP y navegador

---

## 📜 Licencia

Este proyecto fue desarrollado como parte de la práctica de **Puesta en Producción Segura** en el IES Zaidín-Vergeles.

**Uso académico y educativo.**

---

## 👥 Autores

* **Equipo StatTracker** - *Desarrollo inicial* - IES Zaidín-Vergeles

---

## 🙏 Agradecimientos

* Profesor del módulo de Puesta en Producción Segura
* IES Zaidín-Vergeles
* Comunidad de PHP y PHPUnit
* Stack Overflow y documentación oficial

---

## 📞 Contacto y Soporte

* **Documentación**: Consulta la carpeta `docs/`
* **Issues**: Usa el sistema de issues de GitHub
* **Wiki**: Para más información, consulta la wiki del proyecto

---

## 🔄 Historial de Versiones

### v1.0 (Enero 2025)
* ✨ Implementación inicial del MVP
* ✅ Sistema de autenticación completo
* ✅ Gestión de métricas de salud
* ✅ Tests unitarios (>85% cobertura)
* ✅ Documentación completa
* ✅ Mockups y diagramas

---

## 🚀 Roadmap (Futuras Versiones)

### v1.1 (Planificado)
* 📧 Recuperación de contraseña por email
* 📊 Gráficos de evolución de métricas
* 📱 Mejoras responsive para móviles
* 🌐 Internacionalización (i18n)

### v2.0 (Futuro)
* 📤 Exportación de datos (PDF, CSV)
* 🔔 Notificaciones y recordatorios
* 🎯 Objetivos y metas personalizadas
* 📈 Estadísticas avanzadas

---

## 💻 Comandos Útiles

```bash
# Desarrollo
composer install              # Instalar dependencias
php -S localhost:8000         # Servidor de desarrollo

# Testing
vendor/bin/phpunit            # Ejecutar todos los tests
vendor/bin/phpunit --testdox  # Salida legible
vendor/bin/phpunit --coverage-html coverage  # Generar cobertura

# Base de Datos
mysql -u root -p stattracker < database.sql  # Importar esquema

# Ver logs (si usas Apache)
tail -f /var/log/apache2/error.log
```

---

**¿Necesitas ayuda?** Consulta el [Manual de Usuario](docs/manual-usuario.adoc) o revisa la documentación técnica en la carpeta `docs/`.

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, considera darle una estrella ⭐**

Hecho con ❤️ por el equipo StatTracker

</div>## 📁 Estructura del Proyecto

```
StatTracker/
├── src/                    # Clases principales (lógica de negocio)
│   ├── Auth.php           # Autenticación (registro, login)
│   ├── User.php           # Gestión de perfil y contraseñas
│   └── Metrics.php        # Gestión de métricas de salud
├── tests/                  # Tests unitarios y de integración
│   ├── AuthTest.php       # Tests de autenticación
│   ├── UserTest.php       # Tests de usuario
│   ├── MetricsTest.php    # Tests de métricas
│   └── ApiIntegrationTest.php
├── docs/                   # Documentación completa
│   ├── manual-usuario.adoc         # Manual de usuario
│   ├── coverage-analisis.adoc      # Análisis de cobertura
│   ├── system-test-report.adoc     # Informe de pruebas
│   ├── entrevista-notas.adoc       # Notas del proyecto
│   └── mockups/                    # Diagramas Mermaid
├── coverage/               # Informes de cobertura (generado)
├── database.sql           # Esquema de base de datos
├── database_connection.php # Configuración de BD
├── composer.json          # Dependencias de PHP
├── phpunit.xml            # Configuración de PHPUnit
└── README.md              # Este archivo

# Archivos de interfaz:
├── index.php              # Página de inicio
├── login.php              # Procesamiento de login
├── register.php           # Procesamiento de registro
├── dashboard.php          # Panel principal
├── profile.php            # Página de perfil
├── add_data.php           # Añadir métricas
├── get_data.php           # Obtener métricas
├── delete_data.php        # Eliminar métricas
└── update_profile.php     # Actualizar perfil
```

---

## 🏗️ Arquitectura

StatTracker sigue una arquitectura **MVC simplificada**:

### Modelo (src/)
* **Auth.php**: Lógica de autenticación
* **User.php**: Lógica de gestión de usuarios
* **Metrics.php**: Lógica de métricas de salud

### Vista (archivos .php raíz)
* Archivos PHP con HTML que renderizan la interfaz

### Controlador (archivos de procesamiento)
* Scripts PHP que procesan requests y llaman a los modelos

### Base de Datos

**Tablas principales:**

```sql
usuarios (id, nombre, apellidos, email, password, profile_pic, ...)
metricas (id, user_id, peso, altura, imc, fecha_registro, ...)
```

Ver `database.sql` para el esquema completo.

---

## 🔒 Seguridad

### Medidas Implementadas

* ✅ **Contraseñas cifradas**: Usando `password_hash()` (bcrypt)
* ✅ **Prepared Statements**: Protección contra SQL injection
* ✅ **Validación de inputs**: En servidor
* ✅ **Sesiones seguras**: Configuración PHP adecuada
* ✅ **Aislamiento de datos**: Cada usuario solo accede a lo suyo
* ✅ **Verificación de permisos**: En todas las operaciones

### Recomendaciones para Producción

* Implementar tokens CSRF
* Activar HTTPS
* Configurar headers de seguridad (CSP, HSTS)
* Implementar rate limiting
* Logs de auditoría
* Backups automáticos

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para más detalles.

---

## 🧪 Testing

### Estrategia de Testing

El proyecto implementa múltiples niveles de testing:

#### 1. Tests Unitarios (PHPUnit)

**Cobertura**: ~85% de líneas

* `AuthTest.php`: Registro, login, validaciones
* `UserTest.php`: Perfil, cambio de contraseña
* `MetricsTest.php`: CRUD de métricas, cálculo de IMC

#### 2. Tests de Integración

* `ApiIntegrationTest.php`: Pruebas de endpoints completos
* `DatabaseTest.php`: Conexión a base de datos

#### 3. Tests de Sistema (Manual/Automatizado)

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para casos de prueba E2E.

### Ejecutar Tests por Categoría

```bash
# Tests de autenticación
vendor/bin/phpunit --filter Auth

# Tests de métricas
vendor/bin/phpunit --filter Metrics

# Tests de integración
vendor/bin/phpunit --filter Integration
```

---

## 📊 Métricas del Proyecto

| Métrica | Valor |
|---------|-------|
| Líneas de código (src/) | ~350 |
| Tests unitarios | 24+ |
| Cobertura de código | 85%+ |
| Clases principales | 3 |
| Endpoints API | 8 |
| Casos de prueba E2E | 20 |

---

## 🤝 Contribuir

### Proceso de Contribución

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guía de Estilo

* Seguir PSR-12 para código PHP
* Escribir tests para nuevas funcionalidades
* Mantener cobertura >80%
* Documentar funciones públicas
* Validar inputs en servidor

---

## 🐛 Reportar Problemas

Si encuentras un bug o tienes una sugerencia:

1. Verifica que no exista un issue similar
2. Crea un nuevo issue con:
   * Descripción clara del problema
   * Pasos para reproducir
   * Comportamiento esperado vs actual
   * Screenshots (si aplica)
   * Versión de PHP y navegador

---

## 📜 Licencia

Este proyecto fue desarrollado como parte de la práctica de **Puesta en Producción Segura** en el IES Zaidín-Vergeles.

**Uso académico y educativo.**

---

## 👥 Autores

* **Equipo StatTracker** - *Desarrollo inicial* - IES Zaidín-Vergeles

---

## 🙏 Agradecimientos

* Profesor del módulo de Puesta en Producción Segura
* IES Zaidín-Vergeles
* Comunidad de PHP y PHPUnit
* Stack Overflow y documentación oficial

---

## 📞 Contacto y Soporte

* **Documentación**: Consulta la carpeta `docs/`
* **Issues**: Usa el sistema de issues de GitHub
* **Wiki**: Para más información, consulta la wiki del proyecto

---

## 🔄 Historial de Versiones

### v1.0 (Enero 2025)
* ✨ Implementación inicial del MVP
* ✅ Sistema de autenticación completo
* ✅ Gestión de métricas de salud
* ✅ Tests unitarios (>85% cobertura)
* ✅ Documentación completa
* ✅ Mockups y diagramas

---

## 🚀 Roadmap (Futuras Versiones)

### v1.1 (Planificado)
* 📧 Recuperación de contraseña por email
* 📊 Gráficos de evolución de métricas
* 📱 Mejoras responsive para móviles
* 🌐 Internacionalización (i18n)

### v2.0 (Futuro)
* 📤 Exportación de datos (PDF, CSV)
* 🔔 Notificaciones y recordatorios
* 🎯 Objetivos y metas personalizadas
* 📈 Estadísticas avanzadas

---

## 💻 Comandos Útiles

```bash
# Desarrollo
composer install              # Instalar dependencias
php -S localhost:8000         # Servidor de desarrollo

# Testing
vendor/bin/phpunit            # Ejecutar todos los tests
vendor/bin/phpunit --testdox  # Salida legible
vendor/bin/phpunit --coverage-html coverage  # Generar cobertura

# Base de Datos
mysql -u root -p stattracker < database.sql  # Importar esquema

# Ver logs (si usas Apache)
tail -f /var/log/apache2/error.log
```

---

**¿Necesitas ayuda?** Consulta el [Manual de Usuario](docs/manual-usuario.adoc) o revisa la documentación técnica en la carpeta `docs/`.

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, considera darle una estrella ⭐**

Hecho con ❤️ por el equipo StatTracker

</div>-|
| **[Manual de Usuario](docs/manual-usuario.adoc)** | Guía completa de uso de la aplicación |
| **[Mockups](docs/mockups/)** | Diagramas visuales de las pantallas y flujos |
| **[Análisis de Cobertura](docs/coverage-analisis.adoc)** | Informe detallado de cobertura de código |
| **[Informe de Pruebas de Sistema](docs/system-test-report.adoc)** | Resultados de pruebas E2E |
| **[Notas de Entrevista](docs/entrevista-notas.adoc)** | Requisitos y decisiones del proyecto |

> 💡 **Tip**: Si eres usuario final, empieza por el [Manual de Usuario](docs/manual-usuario.adoc). Si eres desarrollador, revisa los documentos técnicos de cobertura y pruebas.

## 📁 Estructura del Proyecto

```
StatTracker/
├── src/                    # Clases principales (lógica de negocio)
│   ├── Auth.php           # Autenticación (registro, login)
│   ├── User.php           # Gestión de perfil y contraseñas
│   └── Metrics.php        # Gestión de métricas de salud
├── tests/                  # Tests unitarios y de integración
│   ├── AuthTest.php       # Tests de autenticación
│   ├── UserTest.php       # Tests de usuario
│   ├── MetricsTest.php    # Tests de métricas
│   └── ApiIntegrationTest.php
├── docs/                   # Documentación completa
│   ├── manual-usuario.adoc         # Manual de usuario
│   ├── coverage-analisis.adoc      # Análisis de cobertura
│   ├── system-test-report.adoc     # Informe de pruebas
│   ├── entrevista-notas.adoc       # Notas del proyecto
│   └── mockups/                    # Diagramas Mermaid
├── coverage/               # Informes de cobertura (generado)
├── database.sql           # Esquema de base de datos
├── database_connection.php # Configuración de BD
├── composer.json          # Dependencias de PHP
├── phpunit.xml            # Configuración de PHPUnit
└── README.md              # Este archivo

# Archivos de interfaz:
├── index.php              # Página de inicio
├── login.php              # Procesamiento de login
├── register.php           # Procesamiento de registro
├── dashboard.php          # Panel principal
├── profile.php            # Página de perfil
├── add_data.php           # Añadir métricas
├── get_data.php           # Obtener métricas
├── delete_data.php        # Eliminar métricas
└── update_profile.php     # Actualizar perfil
```

---

## 🏗️ Arquitectura

StatTracker sigue una arquitectura **MVC simplificada**:

### Modelo (src/)
* **Auth.php**: Lógica de autenticación
* **User.php**: Lógica de gestión de usuarios
* **Metrics.php**: Lógica de métricas de salud

### Vista (archivos .php raíz)
* Archivos PHP con HTML que renderizan la interfaz

### Controlador (archivos de procesamiento)
* Scripts PHP que procesan requests y llaman a los modelos

### Base de Datos

**Tablas principales:**

```sql
usuarios (id, nombre, apellidos, email, password, profile_pic, ...)
metricas (id, user_id, peso, altura, imc, fecha_registro, ...)
```

Ver `database.sql` para el esquema completo.

---

## 🔒 Seguridad

### Medidas Implementadas

* ✅ **Contraseñas cifradas**: Usando `password_hash()` (bcrypt)
* ✅ **Prepared Statements**: Protección contra SQL injection
* ✅ **Validación de inputs**: En servidor
* ✅ **Sesiones seguras**: Configuración PHP adecuada
* ✅ **Aislamiento de datos**: Cada usuario solo accede a lo suyo
* ✅ **Verificación de permisos**: En todas las operaciones

### Recomendaciones para Producción

* Implementar tokens CSRF
* Activar HTTPS
* Configurar headers de seguridad (CSP, HSTS)
* Implementar rate limiting
* Logs de auditoría
* Backups automáticos

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para más detalles.

---

## 🧪 Testing

### Estrategia de Testing

El proyecto implementa múltiples niveles de testing:

#### 1. Tests Unitarios (PHPUnit)

**Cobertura**: ~85% de líneas

* `AuthTest.php`: Registro, login, validaciones
* `UserTest.php`: Perfil, cambio de contraseña
* `MetricsTest.php`: CRUD de métricas, cálculo de IMC

#### 2. Tests de Integración

* `ApiIntegrationTest.php`: Pruebas de endpoints completos
* `DatabaseTest.php`: Conexión a base de datos

#### 3. Tests de Sistema (Manual/Automatizado)

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para casos de prueba E2E.

### Ejecutar Tests por Categoría

```bash
# Tests de autenticación
vendor/bin/phpunit --filter Auth

# Tests de métricas
vendor/bin/phpunit --filter Metrics

# Tests de integración
vendor/bin/phpunit --filter Integration
```

---

## 📊 Métricas del Proyecto

| Métrica | Valor |
|---------|-------|
| Líneas de código (src/) | ~350 |
| Tests unitarios | 24+ |
| Cobertura de código | 85%+ |
| Clases principales | 3 |
| Endpoints API | 8 |
| Casos de prueba E2E | 20 |

---

## 🤝 Contribuir

### Proceso de Contribución

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guía de Estilo

* Seguir PSR-12 para código PHP
* Escribir tests para nuevas funcionalidades
* Mantener cobertura >80%
* Documentar funciones públicas
* Validar inputs en servidor

---

## 🐛 Reportar Problemas

Si encuentras un bug o tienes una sugerencia:

1. Verifica que no exista un issue similar
2. Crea un nuevo issue con:
   * Descripción clara del problema
   * Pasos para reproducir
   * Comportamiento esperado vs actual
   * Screenshots (si aplica)
   * Versión de PHP y navegador

---

## 📜 Licencia

Este proyecto fue desarrollado como parte de la práctica de **Puesta en Producción Segura** en el IES Zaidín-Vergeles.

**Uso académico y educativo.**

---

## 👥 Autores

* **Equipo StatTracker** - *Desarrollo inicial* - IES Zaidín-Vergeles

---

## 🙏 Agradecimientos

* Profesor del módulo de Puesta en Producción Segura
* IES Zaidín-Vergeles
* Comunidad de PHP y PHPUnit
* Stack Overflow y documentación oficial

---

## 📞 Contacto y Soporte

* **Documentación**: Consulta la carpeta `docs/`
* **Issues**: Usa el sistema de issues de GitHub
* **Wiki**: Para más información, consulta la wiki del proyecto

---

## 🔄 Historial de Versiones

### v1.0 (Enero 2025)
* ✨ Implementación inicial del MVP
* ✅ Sistema de autenticación completo
* ✅ Gestión de métricas de salud
* ✅ Tests unitarios (>85% cobertura)
* ✅ Documentación completa
* ✅ Mockups y diagramas

---

## 🚀 Roadmap (Futuras Versiones)

### v1.1 (Planificado)
* 📧 Recuperación de contraseña por email
* 📊 Gráficos de evolución de métricas
* 📱 Mejoras responsive para móviles
* 🌐 Internacionalización (i18n)

### v2.0 (Futuro)
* 📤 Exportación de datos (PDF, CSV)
* 🔔 Notificaciones y recordatorios
* 🎯 Objetivos y metas personalizadas
* 📈 Estadísticas avanzadas

---

## 💻 Comandos Útiles

```bash
# Desarrollo
composer install              # Instalar dependencias
php -S localhost:8000         # Servidor de desarrollo

# Testing
vendor/bin/phpunit            # Ejecutar todos los tests
vendor/bin/phpunit --testdox  # Salida legible
vendor/bin/phpunit --coverage-html coverage  # Generar cobertura

# Base de Datos
mysql -u root -p stattracker < database.sql  # Importar esquema

# Ver logs (si usas Apache)
tail -f /var/log/apache2/error.log
```

---

**¿Necesitas ayuda?** Consulta el [Manual de Usuario](docs/manual-usuario.adoc) o revisa la documentación técnica en la carpeta `docs/`.

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, considera darle una estrella ⭐**

Hecho con ❤️ por el equipo StatTracker

</div>

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

## 📁 Estructura del Proyecto

```
StatTracker/
├── src/                    # Clases principales (lógica de negocio)
│   ├── Auth.php           # Autenticación (registro, login)
│   ├── User.php           # Gestión de perfil y contraseñas
│   └── Metrics.php        # Gestión de métricas de salud
├── tests/                  # Tests unitarios y de integración
│   ├── AuthTest.php       # Tests de autenticación
│   ├── UserTest.php       # Tests de usuario
│   ├── MetricsTest.php    # Tests de métricas
│   └── ApiIntegrationTest.php
├── docs/                   # Documentación completa
│   ├── manual-usuario.adoc         # Manual de usuario
│   ├── coverage-analisis.adoc      # Análisis de cobertura
│   ├── system-test-report.adoc     # Informe de pruebas
│   ├── entrevista-notas.adoc       # Notas del proyecto
│   └── mockups/                    # Diagramas Mermaid
├── coverage/               # Informes de cobertura (generado)
├── database.sql           # Esquema de base de datos
├── database_connection.php # Configuración de BD
├── composer.json          # Dependencias de PHP
├── phpunit.xml            # Configuración de PHPUnit
└── README.md              # Este archivo

# Archivos de interfaz:
├── index.php              # Página de inicio
├── login.php              # Procesamiento de login
├── register.php           # Procesamiento de registro
├── dashboard.php          # Panel principal
├── profile.php            # Página de perfil
├── add_data.php           # Añadir métricas
├── get_data.php           # Obtener métricas
├── delete_data.php        # Eliminar métricas
└── update_profile.php     # Actualizar perfil
```

---

## 🏗️ Arquitectura

StatTracker sigue una arquitectura **MVC simplificada**:

### Modelo (src/)
* **Auth.php**: Lógica de autenticación
* **User.php**: Lógica de gestión de usuarios
* **Metrics.php**: Lógica de métricas de salud

### Vista (archivos .php raíz)
* Archivos PHP con HTML que renderizan la interfaz

### Controlador (archivos de procesamiento)
* Scripts PHP que procesan requests y llaman a los modelos

### Base de Datos

**Tablas principales:**

```sql
usuarios (id, nombre, apellidos, email, password, profile_pic, ...)
metricas (id, user_id, peso, altura, imc, fecha_registro, ...)
```

Ver `database.sql` para el esquema completo.

---

## 🔒 Seguridad

### Medidas Implementadas

* ✅ **Contraseñas cifradas**: Usando `password_hash()` (bcrypt)
* ✅ **Prepared Statements**: Protección contra SQL injection
* ✅ **Validación de inputs**: En servidor
* ✅ **Sesiones seguras**: Configuración PHP adecuada
* ✅ **Aislamiento de datos**: Cada usuario solo accede a lo suyo
* ✅ **Verificación de permisos**: En todas las operaciones

### Recomendaciones para Producción

* Implementar tokens CSRF
* Activar HTTPS
* Configurar headers de seguridad (CSP, HSTS)
* Implementar rate limiting
* Logs de auditoría
* Backups automáticos

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para más detalles.

---

## 🧪 Testing

### Estrategia de Testing

El proyecto implementa múltiples niveles de testing:

#### 1. Tests Unitarios (PHPUnit)

**Cobertura**: ~85% de líneas

* `AuthTest.php`: Registro, login, validaciones
* `UserTest.php`: Perfil, cambio de contraseña
* `MetricsTest.php`: CRUD de métricas, cálculo de IMC

#### 2. Tests de Integración

* `ApiIntegrationTest.php`: Pruebas de endpoints completos
* `DatabaseTest.php`: Conexión a base de datos

#### 3. Tests de Sistema (Manual/Automatizado)

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para casos de prueba E2E.

### Ejecutar Tests por Categoría

```bash
# Tests de autenticación
vendor/bin/phpunit --filter Auth

# Tests de métricas
vendor/bin/phpunit --filter Metrics

# Tests de integración
vendor/bin/phpunit --filter Integration
```

---

## 📊 Métricas del Proyecto

| Métrica | Valor |
|---------|-------|
| Líneas de código (src/) | ~350 |
| Tests unitarios | 24+ |
| Cobertura de código | 85%+ |
| Clases principales | 3 |
| Endpoints API | 8 |
| Casos de prueba E2E | 20 |

---

## 🤝 Contribuir

### Proceso de Contribución

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guía de Estilo

* Seguir PSR-12 para código PHP
* Escribir tests para nuevas funcionalidades
* Mantener cobertura >80%
* Documentar funciones públicas
* Validar inputs en servidor

---

## 🐛 Reportar Problemas

Si encuentras un bug o tienes una sugerencia:

1. Verifica que no exista un issue similar
2. Crea un nuevo issue con:
   * Descripción clara del problema
   * Pasos para reproducir
   * Comportamiento esperado vs actual
   * Screenshots (si aplica)
   * Versión de PHP y navegador

---

## 📜 Licencia

Este proyecto fue desarrollado como parte de la práctica de **Puesta en Producción Segura** en el IES Zaidín-Vergeles.

**Uso académico y educativo.**

---

## 👥 Autores

* **Equipo StatTracker** - *Desarrollo inicial* - IES Zaidín-Vergeles

---

## 🙏 Agradecimientos

* Profesor del módulo de Puesta en Producción Segura
* IES Zaidín-Vergeles
* Comunidad de PHP y PHPUnit
* Stack Overflow y documentación oficial

---

## 📞 Contacto y Soporte

* **Documentación**: Consulta la carpeta `docs/`
* **Issues**: Usa el sistema de issues de GitHub
* **Wiki**: Para más información, consulta la wiki del proyecto

---

## 🔄 Historial de Versiones

### v1.0 (Enero 2025)
* ✨ Implementación inicial del MVP
* ✅ Sistema de autenticación completo
* ✅ Gestión de métricas de salud
* ✅ Tests unitarios (>85% cobertura)
* ✅ Documentación completa
* ✅ Mockups y diagramas

---

## 🚀 Roadmap (Futuras Versiones)

### v1.1 (Planificado)
* 📧 Recuperación de contraseña por email
* 📊 Gráficos de evolución de métricas
* 📱 Mejoras responsive para móviles
* 🌐 Internacionalización (i18n)

### v2.0 (Futuro)
* 📤 Exportación de datos (PDF, CSV)
* 🔔 Notificaciones y recordatorios
* 🎯 Objetivos y metas personalizadas
* 📈 Estadísticas avanzadas

---

## 💻 Comandos Útiles

```bash
# Desarrollo
composer install              # Instalar dependencias
php -S localhost:8000         # Servidor de desarrollo

# Testing
vendor/bin/phpunit            # Ejecutar todos los tests
vendor/bin/phpunit --testdox  # Salida legible
vendor/bin/phpunit --coverage-html coverage  # Generar cobertura

# Base de Datos
mysql -u root -p stattracker < database.sql  # Importar esquema

# Ver logs (si usas Apache)
tail -f /var/log/apache2/error.log
```

---

**¿Necesitas ayuda?** Consulta el [Manual de Usuario](docs/manual-usuario.adoc) o revisa la documentación técnica en la carpeta `docs/`.

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, considera darle una estrella ⭐**

Hecho con ❤️ por el equipo StatTracker

</div>

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

## 📁 Estructura del Proyecto

```
StatTracker/
├── src/                    # Clases principales (lógica de negocio)
│   ├── Auth.php           # Autenticación (registro, login)
│   ├── User.php           # Gestión de perfil y contraseñas
│   └── Metrics.php        # Gestión de métricas de salud
├── tests/                  # Tests unitarios y de integración
│   ├── AuthTest.php       # Tests de autenticación
│   ├── UserTest.php       # Tests de usuario
│   ├── MetricsTest.php    # Tests de métricas
│   └── ApiIntegrationTest.php
├── docs/                   # Documentación completa
│   ├── manual-usuario.adoc         # Manual de usuario
│   ├── coverage-analisis.adoc      # Análisis de cobertura
│   ├── system-test-report.adoc     # Informe de pruebas
│   ├── entrevista-notas.adoc       # Notas del proyecto
│   └── mockups/                    # Diagramas Mermaid
├── coverage/               # Informes de cobertura (generado)
├── database.sql           # Esquema de base de datos
├── database_connection.php # Configuración de BD
├── composer.json          # Dependencias de PHP
├── phpunit.xml            # Configuración de PHPUnit
└── README.md              # Este archivo

# Archivos de interfaz:
├── index.php              # Página de inicio
├── login.php              # Procesamiento de login
├── register.php           # Procesamiento de registro
├── dashboard.php          # Panel principal
├── profile.php            # Página de perfil
├── add_data.php           # Añadir métricas
├── get_data.php           # Obtener métricas
├── delete_data.php        # Eliminar métricas
└── update_profile.php     # Actualizar perfil
```

---

## 🏗️ Arquitectura

StatTracker sigue una arquitectura **MVC simplificada**:

### Modelo (src/)
* **Auth.php**: Lógica de autenticación
* **User.php**: Lógica de gestión de usuarios
* **Metrics.php**: Lógica de métricas de salud

### Vista (archivos .php raíz)
* Archivos PHP con HTML que renderizan la interfaz

### Controlador (archivos de procesamiento)
* Scripts PHP que procesan requests y llaman a los modelos

### Base de Datos

**Tablas principales:**

```sql
usuarios (id, nombre, apellidos, email, password, profile_pic, ...)
metricas (id, user_id, peso, altura, imc, fecha_registro, ...)
```

Ver `database.sql` para el esquema completo.

---

## 🔒 Seguridad

### Medidas Implementadas

* ✅ **Contraseñas cifradas**: Usando `password_hash()` (bcrypt)
* ✅ **Prepared Statements**: Protección contra SQL injection
* ✅ **Validación de inputs**: En servidor
* ✅ **Sesiones seguras**: Configuración PHP adecuada
* ✅ **Aislamiento de datos**: Cada usuario solo accede a lo suyo
* ✅ **Verificación de permisos**: En todas las operaciones

### Recomendaciones para Producción

* Implementar tokens CSRF
* Activar HTTPS
* Configurar headers de seguridad (CSP, HSTS)
* Implementar rate limiting
* Logs de auditoría
* Backups automáticos

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para más detalles.

---

## 🧪 Testing

### Estrategia de Testing

El proyecto implementa múltiples niveles de testing:

#### 1. Tests Unitarios (PHPUnit)

**Cobertura**: ~85% de líneas

* `AuthTest.php`: Registro, login, validaciones
* `UserTest.php`: Perfil, cambio de contraseña
* `MetricsTest.php`: CRUD de métricas, cálculo de IMC

#### 2. Tests de Integración

* `ApiIntegrationTest.php`: Pruebas de endpoints completos
* `DatabaseTest.php`: Conexión a base de datos

#### 3. Tests de Sistema (Manual/Automatizado)

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para casos de prueba E2E.

### Ejecutar Tests por Categoría

```bash
# Tests de autenticación
vendor/bin/phpunit --filter Auth

# Tests de métricas
vendor/bin/phpunit --filter Metrics

# Tests de integración
vendor/bin/phpunit --filter Integration
```

---

## 📊 Métricas del Proyecto

| Métrica | Valor |
|---------|-------|
| Líneas de código (src/) | ~350 |
| Tests unitarios | 24+ |
| Cobertura de código | 85%+ |
| Clases principales | 3 |
| Endpoints API | 8 |
| Casos de prueba E2E | 20 |

---

## 🤝 Contribuir

### Proceso de Contribución

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guía de Estilo

* Seguir PSR-12 para código PHP
* Escribir tests para nuevas funcionalidades
* Mantener cobertura >80%
* Documentar funciones públicas
* Validar inputs en servidor

---

## 🐛 Reportar Problemas

Si encuentras un bug o tienes una sugerencia:

1. Verifica que no exista un issue similar
2. Crea un nuevo issue con:
   * Descripción clara del problema
   * Pasos para reproducir
   * Comportamiento esperado vs actual
   * Screenshots (si aplica)
   * Versión de PHP y navegador

---

## 📜 Licencia

Este proyecto fue desarrollado como parte de la práctica de **Puesta en Producción Segura** en el IES Zaidín-Vergeles.

**Uso académico y educativo.**

---

## 👥 Autores

* **Equipo StatTracker** - *Desarrollo inicial* - IES Zaidín-Vergeles

---

## 🙏 Agradecimientos

* Profesor del módulo de Puesta en Producción Segura
* IES Zaidín-Vergeles
* Comunidad de PHP y PHPUnit
* Stack Overflow y documentación oficial

---

## 📞 Contacto y Soporte

* **Documentación**: Consulta la carpeta `docs/`
* **Issues**: Usa el sistema de issues de GitHub
* **Wiki**: Para más información, consulta la wiki del proyecto

---

## 🔄 Historial de Versiones

### v1.0 (Enero 2025)
* ✨ Implementación inicial del MVP
* ✅ Sistema de autenticación completo
* ✅ Gestión de métricas de salud
* ✅ Tests unitarios (>85% cobertura)
* ✅ Documentación completa
* ✅ Mockups y diagramas

---

## 🚀 Roadmap (Futuras Versiones)

### v1.1 (Planificado)
* 📧 Recuperación de contraseña por email
* 📊 Gráficos de evolución de métricas
* 📱 Mejoras responsive para móviles
* 🌐 Internacionalización (i18n)

### v2.0 (Futuro)
* 📤 Exportación de datos (PDF, CSV)
* 🔔 Notificaciones y recordatorios
* 🎯 Objetivos y metas personalizadas
* 📈 Estadísticas avanzadas

---

## 💻 Comandos Útiles

```bash
# Desarrollo
composer install              # Instalar dependencias
php -S localhost:8000         # Servidor de desarrollo

# Testing
vendor/bin/phpunit            # Ejecutar todos los tests
vendor/bin/phpunit --testdox  # Salida legible
vendor/bin/phpunit --coverage-html coverage  # Generar cobertura

# Base de Datos
mysql -u root -p stattracker < database.sql  # Importar esquema

# Ver logs (si usas Apache)
tail -f /var/log/apache2/error.log
```

---

**¿Necesitas ayuda?** Consulta el [Manual de Usuario](docs/manual-usuario.adoc) o revisa la documentación técnica en la carpeta `docs/`.

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, considera darle una estrella ⭐**

Hecho con ❤️ por el equipo StatTracker

</div>

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

## 📁 Estructura del Proyecto

```
StatTracker/
├── src/                    # Clases principales (lógica de negocio)
│   ├── Auth.php           # Autenticación (registro, login)
│   ├── User.php           # Gestión de perfil y contraseñas
│   └── Metrics.php        # Gestión de métricas de salud
├── tests/                  # Tests unitarios y de integración
│   ├── AuthTest.php       # Tests de autenticación
│   ├── UserTest.php       # Tests de usuario
│   ├── MetricsTest.php    # Tests de métricas
│   └── ApiIntegrationTest.php
├── docs/                   # Documentación completa
│   ├── manual-usuario.adoc         # Manual de usuario
│   ├── coverage-analisis.adoc      # Análisis de cobertura
│   ├── system-test-report.adoc     # Informe de pruebas
│   ├── entrevista-notas.adoc       # Notas del proyecto
│   └── mockups/                    # Diagramas Mermaid
├── coverage/               # Informes de cobertura (generado)
├── database.sql           # Esquema de base de datos
├── database_connection.php # Configuración de BD
├── composer.json          # Dependencias de PHP
├── phpunit.xml            # Configuración de PHPUnit
└── README.md              # Este archivo

# Archivos de interfaz:
├── index.php              # Página de inicio
├── login.php              # Procesamiento de login
├── register.php           # Procesamiento de registro
├── dashboard.php          # Panel principal
├── profile.php            # Página de perfil
├── add_data.php           # Añadir métricas
├── get_data.php           # Obtener métricas
├── delete_data.php        # Eliminar métricas
└── update_profile.php     # Actualizar perfil
```

---

## 🏗️ Arquitectura

StatTracker sigue una arquitectura **MVC simplificada**:

### Modelo (src/)
* **Auth.php**: Lógica de autenticación
* **User.php**: Lógica de gestión de usuarios
* **Metrics.php**: Lógica de métricas de salud

### Vista (archivos .php raíz)
* Archivos PHP con HTML que renderizan la interfaz

### Controlador (archivos de procesamiento)
* Scripts PHP que procesan requests y llaman a los modelos

### Base de Datos

**Tablas principales:**

```sql
usuarios (id, nombre, apellidos, email, password, profile_pic, ...)
metricas (id, user_id, peso, altura, imc, fecha_registro, ...)
```

Ver `database.sql` para el esquema completo.

---

## 🔒 Seguridad

### Medidas Implementadas

* ✅ **Contraseñas cifradas**: Usando `password_hash()` (bcrypt)
* ✅ **Prepared Statements**: Protección contra SQL injection
* ✅ **Validación de inputs**: En servidor
* ✅ **Sesiones seguras**: Configuración PHP adecuada
* ✅ **Aislamiento de datos**: Cada usuario solo accede a lo suyo
* ✅ **Verificación de permisos**: En todas las operaciones

### Recomendaciones para Producción

* Implementar tokens CSRF
* Activar HTTPS
* Configurar headers de seguridad (CSP, HSTS)
* Implementar rate limiting
* Logs de auditoría
* Backups automáticos

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para más detalles.

---

## 🧪 Testing

### Estrategia de Testing

El proyecto implementa múltiples niveles de testing:

#### 1. Tests Unitarios (PHPUnit)

**Cobertura**: ~85% de líneas

* `AuthTest.php`: Registro, login, validaciones
* `UserTest.php`: Perfil, cambio de contraseña
* `MetricsTest.php`: CRUD de métricas, cálculo de IMC

#### 2. Tests de Integración

* `ApiIntegrationTest.php`: Pruebas de endpoints completos
* `DatabaseTest.php`: Conexión a base de datos

#### 3. Tests de Sistema (Manual/Automatizado)

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para casos de prueba E2E.

### Ejecutar Tests por Categoría

```bash
# Tests de autenticación
vendor/bin/phpunit --filter Auth

# Tests de métricas
vendor/bin/phpunit --filter Metrics

# Tests de integración
vendor/bin/phpunit --filter Integration
```

---

## 📊 Métricas del Proyecto

| Métrica | Valor |
|---------|-------|
| Líneas de código (src/) | ~350 |
| Tests unitarios | 24+ |
| Cobertura de código | 85%+ |
| Clases principales | 3 |
| Endpoints API | 8 |
| Casos de prueba E2E | 20 |

---

## 🤝 Contribuir

### Proceso de Contribución

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guía de Estilo

* Seguir PSR-12 para código PHP
* Escribir tests para nuevas funcionalidades
* Mantener cobertura >80%
* Documentar funciones públicas
* Validar inputs en servidor

---

## 🐛 Reportar Problemas

Si encuentras un bug o tienes una sugerencia:

1. Verifica que no exista un issue similar
2. Crea un nuevo issue con:
   * Descripción clara del problema
   * Pasos para reproducir
   * Comportamiento esperado vs actual
   * Screenshots (si aplica)
   * Versión de PHP y navegador

---

## 📜 Licencia

Este proyecto fue desarrollado como parte de la práctica de **Puesta en Producción Segura** en el IES Zaidín-Vergeles.

**Uso académico y educativo.**

---

## 👥 Autores

* **Equipo StatTracker** - *Desarrollo inicial* - IES Zaidín-Vergeles

---

## 🙏 Agradecimientos

* Profesor del módulo de Puesta en Producción Segura
* IES Zaidín-Vergeles
* Comunidad de PHP y PHPUnit
* Stack Overflow y documentación oficial

---

## 📞 Contacto y Soporte

* **Documentación**: Consulta la carpeta `docs/`
* **Issues**: Usa el sistema de issues de GitHub
* **Wiki**: Para más información, consulta la wiki del proyecto

---

## 🔄 Historial de Versiones

### v1.0 (Enero 2025)
* ✨ Implementación inicial del MVP
* ✅ Sistema de autenticación completo
* ✅ Gestión de métricas de salud
* ✅ Tests unitarios (>85% cobertura)
* ✅ Documentación completa
* ✅ Mockups y diagramas

---

## 🚀 Roadmap (Futuras Versiones)

### v1.1 (Planificado)
* 📧 Recuperación de contraseña por email
* 📊 Gráficos de evolución de métricas
* 📱 Mejoras responsive para móviles
* 🌐 Internacionalización (i18n)

### v2.0 (Futuro)
* 📤 Exportación de datos (PDF, CSV)
* 🔔 Notificaciones y recordatorios
* 🎯 Objetivos y metas personalizadas
* 📈 Estadísticas avanzadas

---

## 💻 Comandos Útiles

```bash
# Desarrollo
composer install              # Instalar dependencias
php -S localhost:8000         # Servidor de desarrollo

# Testing
vendor/bin/phpunit            # Ejecutar todos los tests
vendor/bin/phpunit --testdox  # Salida legible
vendor/bin/phpunit --coverage-html coverage  # Generar cobertura

# Base de Datos
mysql -u root -p stattracker < database.sql  # Importar esquema

# Ver logs (si usas Apache)
tail -f /var/log/apache2/error.log
```

---

**¿Necesitas ayuda?** Consulta el [Manual de Usuario](docs/manual-usuario.adoc) o revisa la documentación técnica en la carpeta `docs/`.

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, considera darle una estrella ⭐**

Hecho con ❤️ por el equipo StatTracker

</div>

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

## 📁 Estructura del Proyecto

```
StatTracker/
├── src/                    # Clases principales (lógica de negocio)
│   ├── Auth.php           # Autenticación (registro, login)
│   ├── User.php           # Gestión de perfil y contraseñas
│   └── Metrics.php        # Gestión de métricas de salud
├── tests/                  # Tests unitarios y de integración
│   ├── AuthTest.php       # Tests de autenticación
│   ├── UserTest.php       # Tests de usuario
│   ├── MetricsTest.php    # Tests de métricas
│   └── ApiIntegrationTest.php
├── docs/                   # Documentación completa
│   ├── manual-usuario.adoc         # Manual de usuario
│   ├── coverage-analisis.adoc      # Análisis de cobertura
│   ├── system-test-report.adoc     # Informe de pruebas
│   ├── entrevista-notas.adoc       # Notas del proyecto
│   └── mockups/                    # Diagramas Mermaid
├── coverage/               # Informes de cobertura (generado)
├── database.sql           # Esquema de base de datos
├── database_connection.php # Configuración de BD
├── composer.json          # Dependencias de PHP
├── phpunit.xml            # Configuración de PHPUnit
└── README.md              # Este archivo

# Archivos de interfaz:
├── index.php              # Página de inicio
├── login.php              # Procesamiento de login
├── register.php           # Procesamiento de registro
├── dashboard.php          # Panel principal
├── profile.php            # Página de perfil
├── add_data.php           # Añadir métricas
├── get_data.php           # Obtener métricas
├── delete_data.php        # Eliminar métricas
└── update_profile.php     # Actualizar perfil
```

---

## 🏗️ Arquitectura

StatTracker sigue una arquitectura **MVC simplificada**:

### Modelo (src/)
* **Auth.php**: Lógica de autenticación
* **User.php**: Lógica de gestión de usuarios
* **Metrics.php**: Lógica de métricas de salud

### Vista (archivos .php raíz)
* Archivos PHP con HTML que renderizan la interfaz

### Controlador (archivos de procesamiento)
* Scripts PHP que procesan requests y llaman a los modelos

### Base de Datos

**Tablas principales:**

```sql
usuarios (id, nombre, apellidos, email, password, profile_pic, ...)
metricas (id, user_id, peso, altura, imc, fecha_registro, ...)
```

Ver `database.sql` para el esquema completo.

---

## 🔒 Seguridad

### Medidas Implementadas

* ✅ **Contraseñas cifradas**: Usando `password_hash()` (bcrypt)
* ✅ **Prepared Statements**: Protección contra SQL injection
* ✅ **Validación de inputs**: En servidor
* ✅ **Sesiones seguras**: Configuración PHP adecuada
* ✅ **Aislamiento de datos**: Cada usuario solo accede a lo suyo
* ✅ **Verificación de permisos**: En todas las operaciones

### Recomendaciones para Producción

* Implementar tokens CSRF
* Activar HTTPS
* Configurar headers de seguridad (CSP, HSTS)
* Implementar rate limiting
* Logs de auditoría
* Backups automáticos

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para más detalles.

---

## 🧪 Testing

### Estrategia de Testing

El proyecto implementa múltiples niveles de testing:

#### 1. Tests Unitarios (PHPUnit)

**Cobertura**: ~85% de líneas

* `AuthTest.php`: Registro, login, validaciones
* `UserTest.php`: Perfil, cambio de contraseña
* `MetricsTest.php`: CRUD de métricas, cálculo de IMC

#### 2. Tests de Integración

* `ApiIntegrationTest.php`: Pruebas de endpoints completos
* `DatabaseTest.php`: Conexión a base de datos

#### 3. Tests de Sistema (Manual/Automatizado)

Ver [Informe de Pruebas de Sistema](docs/system-test-report.adoc) para casos de prueba E2E.

### Ejecutar Tests por Categoría

```bash
# Tests de autenticación
vendor/bin/phpunit --filter Auth

# Tests de métricas
vendor/bin/phpunit --filter Metrics

# Tests de integración
vendor/bin/phpunit --filter Integration
```

---

## 📊 Métricas del Proyecto

| Métrica | Valor |
|---------|-------|
| Líneas de código (src/) | ~350 |
| Tests unitarios | 24+ |
| Cobertura de código | 85%+ |
| Clases principales | 3 |
| Endpoints API | 8 |
| Casos de prueba E2E | 20 |

---

## 🤝 Contribuir

### Proceso de Contribución

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guía de Estilo

* Seguir PSR-12 para código PHP
* Escribir tests para nuevas funcionalidades
* Mantener cobertura >80%
* Documentar funciones públicas
* Validar inputs en servidor

---

## 🐛 Reportar Problemas

Si encuentras un bug o tienes una sugerencia:

1. Verifica que no exista un issue similar
2. Crea un nuevo issue con:
   * Descripción clara del problema
   * Pasos para reproducir
   * Comportamiento esperado vs actual
   * Screenshots (si aplica)
   * Versión de PHP y navegador

---

## 📜 Licencia

Este proyecto fue desarrollado como parte de la práctica de **Puesta en Producción Segura** en el IES Zaidín-Vergeles.

**Uso académico y educativo.**

---

## 👥 Autores

* **Equipo StatTracker** - *Desarrollo inicial* - IES Zaidín-Vergeles

---

## 🙏 Agradecimientos

* Profesor del módulo de Puesta en Producción Segura
* IES Zaidín-Vergeles
* Comunidad de PHP y PHPUnit
* Stack Overflow y documentación oficial

---

## 📞 Contacto y Soporte

* **Documentación**: Consulta la carpeta `docs/`
* **Issues**: Usa el sistema de issues de GitHub
* **Wiki**: Para más información, consulta la wiki del proyecto

---

## 🔄 Historial de Versiones

### v1.0 (Enero 2025)
* ✨ Implementación inicial del MVP
* ✅ Sistema de autenticación completo
* ✅ Gestión de métricas de salud
* ✅ Tests unitarios (>85% cobertura)
* ✅ Documentación completa
* ✅ Mockups y diagramas

---

## 🚀 Roadmap (Futuras Versiones)

### v1.1 (Planificado)
* 📧 Recuperación de contraseña por email
* 📊 Gráficos de evolución de métricas
* 📱 Mejoras responsive para móviles
* 🌐 Internacionalización (i18n)

### v2.0 (Futuro)
* 📤 Exportación de datos (PDF, CSV)
* 🔔 Notificaciones y recordatorios
* 🎯 Objetivos y metas personalizadas
* 📈 Estadísticas avanzadas

---

## 💻 Comandos Útiles

```bash
# Desarrollo
composer install              # Instalar dependencias
php -S localhost:8000         # Servidor de desarrollo

# Testing
vendor/bin/phpunit            # Ejecutar todos los tests
vendor/bin/phpunit --testdox  # Salida legible
vendor/bin/phpunit --coverage-html coverage  # Generar cobertura

# Base de Datos
mysql -u root -p stattracker < database.sql  # Importar esquema

# Ver logs (si usas Apache)
tail -f /var/log/apache2/error.log
```

---

**¿Necesitas ayuda?** Consulta el [Manual de Usuario](docs/manual-usuario.adoc) o revisa la documentación técnica en la carpeta `docs/`.

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, considera darle una estrella ⭐**

Hecho con ❤️ por el equipo StatTracker

</div>
