# Documentación del Proyecto StatTracker

## 📚 Bienvenido a la Documentación de StatTracker

Esta carpeta contiene toda la documentación técnica y de usuario del proyecto StatTracker. Este archivo sirve como índice y guía de navegación para acceder a cada documento.

---

## 📚 Documentos Disponibles

### Para Usuarios Finales

#### [Manual de Usuario](manual-usuario.md)

**🎯 Público**: Usuarios finales de la aplicación

**📄 Contenido**:

* Introducción a StatTracker
* Requisitos del sistema
* Guía paso a paso de todas las funcionalidades
* Preguntas frecuentes (FAQ)
* Solución de problemas comunes
* Interpretación del IMC
* Glosario de términos

**📌 Cuándo consultar**: 

* Primera vez usando la aplicación
* Dudas sobre cómo usar una funcionalidad
* Problemas al registrar datos
* Entender qué significa tu IMC

---

### Para Desarrolladores

#### [Notas de Entrevista con el Cliente](entrevista-notas.md)

**🎯 Público**: Equipo de desarrollo, Project Managers

**📄 Contenido**:

* Contexto del proyecto
* Requisitos funcionales y no funcionales identificados
* Preguntas y respuestas de la entrevista inicial
* Prioridades del cliente
* Restricciones y limitaciones
* Ambigüedades aclaradas

**📌 Cuándo consultar**:

* Entender el origen de los requisitos
* Justificar decisiones de diseño
* Planificar nuevas funcionalidades
* Resolver ambigüedades en especificaciones

---

#### [Mockups y Diagramas](mockups/)

**🎯 Público**: Desarrolladores frontend, diseñadores, testers

**📄 Contenido**:

* Mockups de todas las pantallas (Mermaid)
  * 01-login.mmd - Pantalla de inicio de sesión
  * 02-registro.mmd - Pantalla de registro
  * 03-dashboard.mmd - Dashboard principal
  * 04-perfil.mmd - Página de perfil
* Diagramas de flujo de usuario
* Diagrama de requisitos
* README de mockups con instrucciones

**📌 Cuándo consultar**:

* Implementar nuevas pantallas
* Entender flujos de usuario
* Diseñar tests E2E
* Crear prototipos

**💡 Cómo visualizar**:

* GitHub renderiza automáticamente archivos .mmd
* Localmente: usar extensión Mermaid en VS Code
* Online: https://mermaid.live

---

#### [Análisis de Cobertura de Código](coverage-analisis.md)

**🎯 Público**: Desarrolladores, QA, Tech Leads

**📄 Contenido**:

* Métricas globales de cobertura (~85%)
* Análisis detallado por clase:
  * Auth.php
  * User.php
  * Metrics.php
* Líneas/funciones NO cubiertas (con justificación)
* Código muerto identificado
* Refactorizaciones sugeridas
* Recomendaciones de testing
* Instrucciones para generar informes

**📌 Cuándo consultar**:

* Después de añadir nuevo código
* Antes de un release
* Durante code reviews
* Planificar mejoras en testing

**🔗 Relacionado**:

* Ejecutar: `vendor/bin/phpunit --coverage-html coverage`
* Ver: `coverage/index.html`

---

#### [Informe de Pruebas de Sistema](system-test-report.md)

**🎯 Público**: QA, Testers, Product Owners, Desarrolladores

**📄 Contenido**:

* 20 casos de prueba E2E ejecutados
* Resultados detallados (100% PASS)
* Flujos completos de usuario testeados
* Defectos encontrados (menores)
* Sugerencias de mejora UX
* Pruebas de compatibilidad (navegadores)
* Pruebas de rendimiento básicas
* Pruebas de seguridad
* Scripts de automatización (Playwright/Selenium)
* Recomendaciones para producción

**📌 Cuándo consultar**:

* Antes de desplegar a producción
* Después de cambios importantes
* Planificar automatización de tests
* Validar nuevas funcionalidades
* Reportar bugs

**✅ Estado del Sistema**: APTO PARA PRODUCCIÓN

---

## 🗂️ Estructura de la Documentación

```
docs/
├── README.md                      # Este archivo (índice)
├── manual-usuario.md              # Manual completo para usuarios
├── entrevista-notas.md            # Requisitos y decisiones
├── coverage-analisis.md           # Análisis de cobertura
├── system-test-report.md          # Informe de pruebas E2E
└── mockups/                       # Diagramas visuales
    ├── README.md                  # Índice de mockups
    ├── 01-login.mmd               # Mockup: Login
    ├── 02-registro.mmd            # Mockup: Registro
    ├── 03-dashboard.mmd           # Mockup: Dashboard
    └── 04-perfil.mmd              # Mockup: Perfil
```

---

## 🚀 Guías Rápidas

### Para Nuevos Desarrolladores

1. Leer [Notas de Entrevista](entrevista-notas.md) para contexto
2. Revisar [Mockups](mockups/) para entender la UI
3. Consultar [Análisis de Cobertura](coverage-analisis.md) para ver estado del código
4. Leer [Informe de Pruebas](system-test-report.md) para conocer funcionalidades validadas

### Para Nuevos Usuarios

1. Empezar por [Manual de Usuario](manual-usuario.md)
2. Seguir la sección "Primer Acceso: Registro"
3. Consultar FAQ si tienes dudas

### Para QA/Testers

1. Revisar [Informe de Pruebas](system-test-report.md) para casos de prueba existentes
2. Consultar [Mockups](mockups/) para flujos a validar
3. Verificar [Cobertura](coverage-analisis.md) para áreas sin tests

### Para Product Owners

1. Leer [Notas de Entrevista](entrevista-notas.md) para requisitos implementados
2. Revisar [Informe de Pruebas](system-test-report.md) para estado del proyecto
3. Consultar roadmap en el README principal

---

## 📋 Cumplimiento de Requisitos del Proyecto

Según el documento de requisitos de la práctica:

| Requisito | Estado | Documento |
|-----------|--------|------------|
| Manual de usuario con mockups | ✅ Completo | [manual-usuario.md](manual-usuario.md) + [mockups/](mockups/) |
| Mockups con PlantUML/Mermaid | ✅ Completo | [mockups/*.mmd](mockups/) |
| Documentación de requisitos (R001-R006) | ✅ Completo | [entrevista-notas.md](entrevista-notas.md) |
| Código fuente | ✅ Completo | ../src/ |
| Pruebas unitarias | ✅ Completo | ../tests/ |
| Informe de cobertura | ✅ Completo | [coverage-analisis.md](coverage-analisis.md) + ../coverage/ |
| Pruebas de sistema | ✅ Completo | [system-test-report.md](system-test-report.md) |
| README con instrucciones | ✅ Completo | ../README.md |

**Cobertura de líneas**: ~85% (Objetivo: >=70%) ✅

---

## 🔍 Búsqueda Rápida de Información

### ¿Cómo hago X?

* **Registrarme en la app**: [Manual de Usuario - Registro](manual-usuario.md#primer-acceso-registro)
* **Añadir métricas**: [Manual - Añadir Datos](manual-usuario.md#añadir-un-nuevo-registro-de-salud)
* **Cambiar mi contraseña**: [Manual - Cambiar Contraseña](manual-usuario.md#cambiar-contraseña)
* **Interpretar mi IMC**: [Manual - IMC](manual-usuario.md#interpretación-del-imc)

### ¿Dónde está documentado X?

* **Requisitos funcionales**: [Notas de Entrevista - RF](entrevista-notas.md#requisitos-funcionales-identificados)
* **Casos de prueba**: [Informe de Pruebas - Casos](system-test-report.md#casos-de-prueba-ejecutados)
* **Cobertura de Auth.php**: [Análisis - Auth](coverage-analisis.md#clase-auth-srcauthphp)
* **Flujo de login**: [mockups/01-login.mmd](mockups/01-login.mmd)

### ¿Cómo testeo X?

* **Ejecutar tests unitarios**: `vendor/bin/phpunit` (ver [Análisis - Generar Cobertura](coverage-analisis.md#cómo-generar-el-informe-de-cobertura))
* **Pruebas de sistema manual**: [Informe - TC-001 a TC-020](system-test-report.md#casos-de-prueba-ejecutados)
* **Automatizar con Playwright**: [Informe - Automatización](system-test-report.md#automatización-de-pruebas)

---

## 📝 Formato de los Documentos

Todos los documentos están escritos en **Markdown** (.md):

* ✅ Compatible con GitHub Wiki (renderizado automático)
* ✅ Fácil de leer y editar
* ✅ Soporta tablas, código, imágenes, enlaces
* ✅ Estándar de la industria

---

## 🔄 Mantenimiento de la Documentación

### Cuándo Actualizar

* **Manual de Usuario**: Cuando se añadan/modifiquen funcionalidades
* **Mockups**: Cuando cambie el diseño/flujo de UI
* **Análisis de Cobertura**: Después de cambios significativos en el código
* **Informe de Pruebas**: Después de cada ciclo de testing mayor

### Versionado

La documentación debe seguir el versionado del código:

* v1.0: Documentación actual (Enero 2025)
* v1.1: Actualizaciones planificadas

---

## 💡 Mejores Prácticas

### Para Documentar Código

1. Mantener sincronización con el código
2. Actualizar tests junto con funcionalidades
3. Documentar decisiones de diseño importantes
4. Incluir ejemplos prácticos

### Para Usar Esta Documentación

1. **Usuarios**: Empezar por el Manual de Usuario
2. **Desarrolladores**: Leer primero Notas de Entrevista y Mockups
3. **QA**: Enfocarse en Informe de Pruebas y Cobertura
4. **Todos**: El README.md principal es el punto de entrada

---

## 📞 Contacto

Si encuentras errores en la documentación o tienes sugerencias:

1. Crea un issue en GitHub etiquetado como "documentation"
2. Incluye el nombre del documento afectado
3. Describe el problema o mejora sugerida

---

## 🏆 Calidad de la Documentación

Esta documentación cumple con:

* ✅ Requisitos académicos del proyecto
* ✅ Estándares de documentación técnica
* ✅ Usabilidad para diferentes audiencias
* ✅ Completitud y detalle adecuado
* ✅ Mantenibilidad a largo plazo

---

## 📚 Referencias Externas

* [Markdown Guide](https://www.markdownguide.org/)
* [Mermaid Documentation](https://mermaid.js.org/)
* [PHPUnit Documentation](https://phpunit.de/)
* [Playwright Documentation](https://playwright.dev/)

---

_Esta documentación es parte del proyecto StatTracker, desarrollado para la práctica de Puesta en Producción Segura en el IES Zaidín-Vergeles._

---

**Última actualización**: Enero 2025  
**Versión de la documentación**: 1.0
