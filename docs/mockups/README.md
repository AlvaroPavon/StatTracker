# Mockups de StatTracker

Esta carpeta contiene los mockups y diagramas de requisitos de la aplicación StatTracker.

## Archivos

- **login-register.md**: Pantallas de inicio de sesión y registro
- **dashboard.md**: Dashboard principal con métricas de salud
- **profile.md**: Gestión de perfil y cambio de contraseña

## Herramientas Utilizadas

- **Mermaid**: Para diagramas de flujo y diagramas de requisitos
- **ASCII Art**: Para mockups visuales de las interfaces

## Visualización

Los diagramas Mermaid pueden visualizarse en:
- GitHub (renderiza Mermaid automáticamente)
- VS Code (con extensión Mermaid Preview)
- [Mermaid Live Editor](https://mermaid.live/)

## Requisitos Cubiertos

Según el documento de requisitos del proyecto, estos mockups cumplen con:

- **R001**: Manual de usuario con mockups usando PlantUML/Mermaid ✅
- Diagramas de requisitos numerados ✅
- Flujos de usuario documentados ✅
- Especificaciones visuales de las pantallas ✅

## Requisitos Funcionales Documentados

### Autenticación (R001-R007)
- R001: Login con email y contraseña
- R002: Contraseñas cifradas con bcrypt
- R003: Mensajes de error claros
- R004: Formulario de registro completo
- R005: Email único
- R006: Validación de formato de email
- R007: Contraseña mínima de 6 caracteres

### Dashboard (R008-R011)
- R008: Mostrar métricas del usuario autenticado
- R009: Cálculo automático de IMC
- R010: Métricas ordenadas por fecha
- R011: Solo el propietario puede eliminar sus métricas

### Perfil (R012-R016)
- R012: Actualización de datos personales
- R013: Validación de email único al actualizar
- R014: Cambio de contraseña con verificación
- R015: Verificación de contraseña actual
- R016: Validación de nueva contraseña
