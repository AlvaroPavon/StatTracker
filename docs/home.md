# 📚 Documentación del Proyecto StatTracker

## Bienvenido a la Documentación de StatTracker

Esta carpeta contiene toda la documentación técnica y de usuario del proyecto StatTracker. Este archivo sirve como **índice central** de navegación para acceder a cada documento.

---

## 📋 Índice de Documentación

### 🏠 Documentos Raíz (nivel /app/)

| Documento | Descripción | Audiencia |
|-----------|-------------|----------|
| [README.md](../README.md) | Visión general, instalación rápida y estructura del proyecto | Todos |
| [SECURITY.md](../SECURITY.md) | Arquitectura de seguridad completa (10 capas de defensa) | Desarrolladores, Seguridad |
| [INSTALACION_XAMPP.md](../INSTALACION_XAMPP.md) | Guía paso a paso para instalar con XAMPP | Usuarios, Estudiantes |
| [CUMPLIMIENTO_REQUISITOS.md](../CUMPLIMIENTO_REQUISITOS.md) | Verificación de requisitos académicos | Profesores, Evaluadores |

### 📂 Documentos en /docs/

| Documento | Descripción | Audiencia |
|-----------|-------------|----------|
| [manual-usuario.md](manual-usuario.md) | Manual completo para usuarios finales | Usuarios finales |
| [seguridad-tecnica.md](seguridad-tecnica.md) | Documentación técnica detallada de seguridad | Desarrolladores |
| [verificacion-owasp.md](verificacion-owasp.md) | Verificación de cumplimiento OWASP ASVS | Seguridad, Evaluadores |
| [vectores-ataque.md](vectores-ataque.md) | **🎯 Guía de vectores de ataque y pentesting** | Pentesters, Seguridad |
| [entrevista-notas.md](entrevista-notas.md) | Requisitos y decisiones del proyecto | Desarrolladores, PMs |
| [coverage-analisis.md](coverage-analisis.md) | Análisis de cobertura de código | QA, Desarrolladores |
| [system-test-report.md](system-test-report.md) | Informe de pruebas E2E | QA, Testers |
| [mockups/](mockups/) | Diagramas y mockups de interfaces | Frontend, Diseño |

---

## 🔍 Búsqueda Rápida por Tema

### 🔐 Seguridad

| Tema | Documento | Sección |
|------|-----------|--------|
| Arquitectura de 10 capas | [SECURITY.md](../SECURITY.md) | Arquitectura de Defensa |
| **Securización de contraseñas** | [seguridad-tecnica.md](seguridad-tecnica.md#securización-de-contraseñas) | CryptoFortress |
| Protección contra SQL Injection | [SECURITY.md](../SECURITY.md#1-sql-injection) | Protecciones Implementadas |
| Protección contra XSS | [SECURITY.md](../SECURITY.md#2-xss-cross-site-scripting) | Protecciones Implementadas |
| Protección CSRF | [SECURITY.md](../SECURITY.md#3-csrf) | Protecciones Implementadas |
| Rate Limiting y Fuerza Bruta | [SECURITY.md](../SECURITY.md#4-fuerza-bruta) | Protecciones Implementadas |
| Gestión de Sesiones | [seguridad-tecnica.md](seguridad-tecnica.md#sessionmanager) | SessionManager |
| Autenticación 2FA/MFA | [seguridad-tecnica.md](seguridad-tecnica.md#twofactorauth) | TwoFactorAuth |
| WAF (Firewall) | [SECURITY.md](../SECURITY.md#7-waf-web-application-firewall) | SecurityFirewall |
| Headers de Seguridad | [SECURITY.md](../SECURITY.md#-headers-de-seguridad) | SecurityHeaders |

### 🔑 Autenticación y Usuarios

| Tema | Documento | Sección |
|------|-----------|--------|
| Registro de usuarios | [manual-usuario.md](manual-usuario.md#primer-acceso-registro) | Guía de Uso |
| Inicio de sesión | [manual-usuario.md](manual-usuario.md#iniciar-sesión) | Guía de Uso |
| Cambio de contraseña | [manual-usuario.md](manual-usuario.md#cambiar-contraseña) | Gestión de Perfil |
| Validaciones de contraseña | [seguridad-tecnica.md](seguridad-tecnica.md#validaciones-de-entrada) | Security.php |

### 📊 Métricas de Salud

| Tema | Documento | Sección |
|------|-----------|--------|
| Añadir métricas | [manual-usuario.md](manual-usuario.md#añadir-un-nuevo-registro-de-salud) | Guía de Uso |
| Cálculo del IMC | [manual-usuario.md](manual-usuario.md#interpretación-del-imc) | Interpretación |
| Historial de datos | [manual-usuario.md](manual-usuario.md#visualizar-tu-historial) | Guía de Uso |

### 🧪 Testing

| Tema | Documento | Sección |
|------|-----------|--------|
| Ejecutar tests unitarios | [README.md](../README.md#-testing) | Testing |
| Cobertura de código | [coverage-analisis.md](coverage-analisis.md) | Completo |
| Casos de prueba E2E | [system-test-report.md](system-test-report.md#casos-de-prueba-ejecutados) | Casos de Prueba |

### 🛠️ Instalación

| Tema | Documento | Sección |
|------|-----------|--------|
| Instalación rápida | [README.md](../README.md#-instalación-rápida) | Instalación |
| Instalación con XAMPP | [INSTALACION_XAMPP.md](../INSTALACION_XAMPP.md) | Completo |
| Solución de problemas | [INSTALACION_XAMPP.md](../INSTALACION_XAMPP.md#-solución-de-problemas) | FAQ |

---

## 🗂️ Estructura de Archivos de Documentación

```
/app/
├── README.md                      # Punto de entrada principal
├── SECURITY.md                    # Arquitectura de seguridad (10 capas)
├── INSTALACION_XAMPP.md           # Guía de instalación XAMPP
├── CUMPLIMIENTO_REQUISITOS.md     # Verificación académica
│
└── docs/
    ├── home.md                    # ← ESTÁS AQUÍ (Índice)
    ├── manual-usuario.md          # Manual para usuarios finales
    ├── seguridad-tecnica.md       # Documentación técnica de seguridad
    ├── entrevista-notas.md        # Requisitos y decisiones
    ├── coverage-analisis.md       # Análisis de cobertura
    ├── system-test-report.md      # Informe de pruebas E2E
    └── mockups/
        ├── README.md              # Índice de mockups
        ├── login-register.md      # Login y registro
        ├── dashboard.md           # Dashboard principal
        └── profile.md             # Gestión de perfil
```

---

## 🚀 Guías Rápidas por Rol

### 👤 Para Usuarios Finales

1. **Empezar aquí**: [Manual de Usuario](manual-usuario.md)
2. Si tienes problemas: [Solución de Problemas](manual-usuario.md#solución-de-problemas)
3. FAQ: [Preguntas Frecuentes](manual-usuario.md#preguntas-frecuentes)

### 👨‍💻 Para Desarrolladores

1. **Contexto del proyecto**: [Notas de Entrevista](entrevista-notas.md)
2. **Arquitectura de seguridad**: [SECURITY.md](../SECURITY.md)
3. **Detalles técnicos de seguridad**: [seguridad-tecnica.md](seguridad-tecnica.md)
4. **Mockups y flujos**: [mockups/](mockups/)
5. **Estado del testing**: [Coverage](coverage-analisis.md)

### 🔒 Para Equipo de Seguridad

1. **Arquitectura completa**: [SECURITY.md](../SECURITY.md)
2. **Implementación técnica**: [seguridad-tecnica.md](seguridad-tecnica.md)
3. **Clases de seguridad**: Ver sección de clases en seguridad-tecnica.md

### ✅ Para QA/Testers

1. **Casos de prueba**: [system-test-report.md](system-test-report.md)
2. **Cobertura actual**: [coverage-analisis.md](coverage-analisis.md)
3. **Flujos a validar**: [mockups/](mockups/)

### 📋 Para Evaluadores/Profesores

1. **Cumplimiento de requisitos**: [CUMPLIMIENTO_REQUISITOS.md](../CUMPLIMIENTO_REQUISITOS.md)
2. **Tests y cobertura**: [coverage-analisis.md](coverage-analisis.md)
3. **Pruebas de sistema**: [system-test-report.md](system-test-report.md)

---

## 📊 Estado del Proyecto

| Métrica | Valor | Estado |
|---------|-------|--------|
| Cobertura de código | ~85% | ✅ |
| Tests unitarios | 26+ | ✅ |
| Capas de seguridad | 10 | ✅ |
| Documentación | Completa | ✅ |

---

**Última actualización**: Agosto 2025  
**Versión**: 1.1
