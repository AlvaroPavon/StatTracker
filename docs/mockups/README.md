# Mockups de StatTracker

## Introducción

Este directorio contiene los diagramas de mockups de la aplicación StatTracker, creados con Mermaid. Estos mockups representan los flujos de interacción del usuario con la aplicación.

---

## Diagramas Disponibles

### 01 - Pantalla de Login

**Archivo**: `01-login.mmd`

**Descripción**: Muestra el flujo de inicio de sesión, incluyendo:

* Formulario de login con campos email y contraseña
* Botón de inicio de sesión
* Enlace a la página de registro
* Flujo de validación de credenciales
* Mensajes de error en caso de credenciales inválidas
* Redirección al dashboard en caso de éxito

**Requisito relacionado**: R002

---

### 02 - Pantalla de Registro

**Archivo**: `02-registro.mmd`

**Descripción**: Flujo de registro de nuevos usuarios:

* Formulario con campos: nombre, apellidos, email, contraseña, confirmación
* Botón de registro
* Enlace a la página de login para usuarios existentes
* Validaciones:
  * Email duplicado
  * Formato de datos
  * Coincidencia de contraseñas
* Mensajes de éxito/error
* Redirección a login tras registro exitoso

**Requisito relacionado**: R001

---

### 03 - Dashboard Principal

**Archivo**: `03-dashboard.mmd`

**Descripción**: Pantalla principal después del login:

* Barra de navegación con:
  * Logo de la aplicación
  * Saludo personalizado
  * Enlaces a perfil y logout
* Sección de añadir datos:
  * Campos: peso, altura, fecha
  * Botón para guardar
  * Validaciones en tiempo real
* Tabla de historial:
  * Columnas: ID, Peso, Altura, IMC, Fecha, Acciones
  * Botón de eliminar por cada registro
  * Ordenación por fecha descendente
* Flujo de confirmación de eliminación

**Requisitos relacionados**: R004, R005, R006

---

### 04 - Página de Perfil

**Archivo**: `04-perfil.mmd`

**Descripción**: Gestión del perfil de usuario:

* Barra de navegación
* Sección de datos personales:
  * Foto de perfil (opcional)
  * Campos: nombre, apellidos, email
  * Botón de actualizar perfil
* Sección de cambio de contraseña:
  * Contraseña actual
  * Nueva contraseña
  * Confirmación de nueva contraseña
  * Botón de cambiar contraseña
* Validaciones específicas para cada sección
* Mensajes de éxito/error independientes

**Requisito relacionado**: R003

---

## Diagrama de Requisitos con Mermaid

```mermaid
requirementDiagram

    requirement R001 {
        id: R001
        text: Registro de Usuarios
        risk: alto
        verifymethod: pruebas unitarias
    }

    requirement R002 {
        id: R002
        text: Inicio de Sesión
        risk: alto
        verifymethod: pruebas unitarias
    }

    requirement R003 {
        id: R003
        text: Gestión de Perfil
        risk: medio
        verifymethod: pruebas unitarias
    }

    requirement R004 {
        id: R004
        text: Registro de Métricas
        risk: alto
        verifymethod: pruebas unitarias
    }

    requirement R005 {
        id: R005
        text: Visualización de Historial
        risk: bajo
        verifymethod: pruebas de sistema
    }

    requirement R006 {
        id: R006
        text: Eliminación de Registros
        risk: medio
        verifymethod: pruebas unitarias
    }

    element Auth {
        type: clase
        docref: src/Auth.php
    }

    element User {
        type: clase
        docref: src/User.php
    }

    element Metrics {
        type: clase
        docref: src/Metrics.php
    }

    element BaseDatos {
        type: database
        docref: database.sql
    }

    Auth - satisfies -> R001
    Auth - satisfies -> R002
    User - satisfies -> R003
    Metrics - satisfies -> R004
    Metrics - satisfies -> R005
    Metrics - satisfies -> R006
    BaseDatos - contains -> Auth
    BaseDatos - contains -> User
    BaseDatos - contains -> Metrics
```

---

## Cómo Visualizar los Mockups

### En GitHub

GitHub renderiza automáticamente los archivos `.mmd` (Mermaid) en su interfaz web. Simplemente navega al archivo y GitHub mostrará el diagrama.

### En un Editor Local

1. **Visual Studio Code**: Instala la extensión "Mermaid Preview"
2. **IntelliJ IDEA**: Instala el plugin "Mermaid"
3. **Online**: Usa https://mermaid.live para visualizar y editar

### Generar Imágenes

Puedes usar la CLI de Mermaid para generar imágenes:

```bash
# Instalar mermaid-cli
npm install -g @mermaid-js/mermaid-cli

# Generar PNG desde un archivo .mmd
mmdc -i 01-login.mmd -o 01-login.png

# Generar todos los diagramas
for file in *.mmd; do mmdc -i "$file" -o "${file%.mmd}.png"; done
```

---

## Convenciones de Diseño

### Colores Utilizados

* **Azul (#4a90e2)**: Encabezados y títulos principales
* **Verde (#28a745)**: Acciones exitosas y botones de confirmación
* **Rojo (#dc3545)**: Mensajes de error y advertencias
* **Azul claro (#007bff)**: Botones de acción secundarios
* **Verde claro (#90ee90)**: Estados de éxito

### Elementos de UI

* Campos de texto: Representados como `___________`
* Botones: Representados como `[Texto del Botón]`
* Enlaces: Representados como `<u>texto subrayado</u>`
* Mensajes: Con iconos ✓ (éxito) o ✗ (error)

---

## Relación con el Manual de Usuario

Estos mockups complementan el manual de usuario (`manual-usuario.md`) proporcionando una representación visual de los flujos descritos textualmente en el manual.

Cada sección del manual referencia implícitamente estos mockups para facilitar la comprensión del usuario.

---

## Historial de Cambios

| Versión | Fecha | Cambios |
|---------|-------|---------|
| 1.0 | Enero 2025 | Creación inicial de los 4 mockups principales |

---

_Para más información sobre la funcionalidad de cada pantalla, consulta el manual de usuario._
