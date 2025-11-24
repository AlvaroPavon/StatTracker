# Mockup: Gestión de Perfil

## Diagrama de Flujo del Perfil

```mermaid
flowchart TD
    A[Usuario accede a perfil] --> B[Cargar datos del usuario]
    B --> C[Mostrar formulario con datos actuales]
    C --> D{¿Qué quiere hacer?}
    D -->|Actualizar datos| E[Modificar nombre, apellidos, email]
    D -->|Cambiar contraseña| F[Ir a formulario de cambio de contraseña]
    D -->|Volver| G[Regresar al Dashboard]
    E --> H{¿Datos válidos?}
    H -->|Sí| I[Actualizar información]
    H -->|No| J[Mostrar errores]
    J --> C
    I --> K[Mostrar mensaje de éxito]
    K --> C
    F --> L[Ingresar contraseña actual]
    L --> M[Ingresar nueva contraseña]
    M --> N[Confirmar nueva contraseña]
    N --> O{¿Contraseñas coinciden?}
    O -->|Sí| P[Cambiar contraseña]
    O -->|No| Q[Mostrar error]
    Q --> F
    P --> R[Mostrar mensaje de éxito]
    R --> C
```

## Requisitos de Gestión de Perfil

```mermaid
requirementDiagram

    requirement R012 {
        id: R012
        text: El usuario debe poder actualizar su nombre, apellidos y email
        risk: high
        verifymethod: test
    }

    requirement R013 {
        id: R013
        text: El sistema debe validar que el nuevo email no esté en uso por otro usuario
        risk: high
        verifymethod: test
    }

    requirement R014 {
        id: R014
        text: El usuario debe poder cambiar su contraseña proporcionando la actual
        risk: high
        verifymethod: test
    }

    requirement R015 {
        id: R015
        text: El sistema debe verificar que la contraseña actual sea correcta antes de cambiarla
        risk: high
        verifymethod: test
    }

    requirement R016 {
        id: R016
        text: Las nuevas contraseñas deben coincidir y tener al menos 6 caracteres
        risk: medium
        verifymethod: test
    }

    element ProfilePage {
        type: interface
    }

    ProfilePage - satisfies -> R012
    ProfilePage - satisfies -> R013
    ProfilePage - satisfies -> R014
    ProfilePage - satisfies -> R015
    ProfilePage - satisfies -> R016
```

## Mockup de la Interfaz de Perfil

```
┌─────────────────────────────────────────────────────┐
│  StatTracker 📊          Usuario: Juan Pérez  [🚪]  │
├─────────────────────────────────────────────────────┤
│  [← Volver al Dashboard]                            │
├─────────────────────────────────────────────────────┤
│  Mi Perfil                                          │
│                                                     │
│  ┌───────────────────────────────────────────────┐ │
│  │ Datos Personales                              │ │
│  │                                               │ │
│  │ Nombre:                                       │ │
│  │ [Juan                     ]                   │ │
│  │                                               │ │
│  │ Apellidos:                                    │ │
│  │ [Pérez García             ]                   │ │
│  │                                               │ │
│  │ Email:                                        │ │
│  │ [juan.perez@example.com   ]                   │ │
│  │                                               │ │
│  │ [  Actualizar Perfil  ]                       │ │
│  └───────────────────────────────────────────────┘ │
│                                                     │
│  ┌───────────────────────────────────────────────┐ │
│  │ Cambiar Contraseña                            │ │
│  │                                               │ │
│  │ Contraseña actual:                            │ │
│  │ [____________________________]                │ │
│  │                                               │ │
│  │ Nueva contraseña:                             │ │
│  │ [____________________________]                │ │
│  │                                               │ │
│  │ Confirmar nueva contraseña:                   │ │
│  │ [____________________________]                │ │
│  │                                               │ │
│  │ [  Cambiar Contraseña  ]                      │ │
│  └───────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────┘
```
