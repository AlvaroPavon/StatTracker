# Mockup: Pantalla de Login y Registro

## Diagrama de Flujo de Autenticación

```mermaid
flowchart TD
    A[Usuario accede a la aplicación] --> B{¿Tiene cuenta?}
    B -->|Sí| C[Mostrar formulario de Login]
    B -->|No| D[Mostrar formulario de Registro]
    C --> E[Ingresar email y contraseña]
    E --> F{¿Credenciales válidas?}
    F -->|Sí| G[Iniciar sesión]
    F -->|No| H[Mostrar error]
    H --> C
    D --> I[Ingresar nombre, apellidos, email, contraseña]
    I --> J{¿Datos válidos?}
    J -->|Sí| K[Crear cuenta]
    J -->|No| L[Mostrar errores de validación]
    L --> D
    K --> G
    G --> M[Redirigir al Dashboard]
```

## Requisitos de la Pantalla de Login

```mermaid
requirementDiagram

    requirement R001 {
        id: R001
        text: El sistema debe permitir login con email y contraseña
        risk: high
        verifymethod: test
    }

    requirement R002 {
        id: R002
        text: Las contraseñas deben estar cifradas con bcrypt
        risk: high
        verifymethod: test
    }

    requirement R003 {
        id: R003
        text: Debe mostrar mensajes de error claros si las credenciales son incorrectas
        risk: medium
        verifymethod: inspection
    }

    element LoginForm {
        type: interface
    }

    LoginForm - satisfies -> R001
    LoginForm - satisfies -> R002
    LoginForm - satisfies -> R003
```

## Mockup de la Interfaz de Login

```
┌─────────────────────────────────────────┐
│         StatTracker 📊                  │
│                                         │
│  ┌───────────────────────────────────┐ │
│  │  Email:                           │ │
│  │  [____________________________]   │ │
│  │                                   │ │
│  │  Contraseña:                      │ │
│  │  [____________________________]   │ │
│  │                                   │ │
│  │  [  Iniciar Sesión  ]             │ │
│  │                                   │ │
│  │  ¿No tienes cuenta? Regístrate    │ │
│  └───────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

## Requisitos de Registro

```mermaid
requirementDiagram

    requirement R004 {
        id: R004
        text: El formulario de registro debe solicitar nombre, apellidos, email y contraseña
        risk: high
        verifymethod: test
    }

    requirement R005 {
        id: R005
        text: El email debe ser único en el sistema
        risk: high
        verifymethod: test
    }

    requirement R006 {
        id: R006
        text: Debe validar formato de email
        risk: medium
        verifymethod: test
    }

    requirement R007 {
        id: R007
        text: La contraseña debe tener al menos 6 caracteres
        risk: medium
        verifymethod: test
    }

    element RegisterForm {
        type: interface
    }

    RegisterForm - satisfies -> R004
    RegisterForm - satisfies -> R005
    RegisterForm - satisfies -> R006
    RegisterForm - satisfies -> R007
```
