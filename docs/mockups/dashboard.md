# Mockup: Dashboard Principal

## Diagrama de Flujo del Dashboard

```mermaid
flowchart TD
    A[Usuario autenticado] --> B[Cargar Dashboard]
    B --> C[Mostrar métricas recientes]
    B --> D[Mostrar botón 'Añadir Métrica']
    B --> E[Mostrar botón 'Ver Perfil']
    D --> F[Abrir formulario de nueva métrica]
    F --> G[Ingresar peso y altura]
    G --> H[Calcular IMC automáticamente]
    H --> I[Guardar métrica]
    I --> B
    E --> J[Ir a página de perfil]
    C --> K{¿Tiene métricas?}
    K -->|Sí| L[Mostrar lista con peso, altura, IMC y fecha]
    K -->|No| M[Mostrar mensaje 'No hay datos']
    L --> N[Botón eliminar por métrica]
    N --> O[Confirmar eliminación]
    O --> B
```

## Requisitos del Dashboard

```mermaid
requirementDiagram

    requirement R008 {
        id: R008
        text: El dashboard debe mostrar todas las métricas del usuario autenticado
        risk: high
        verifymethod: test
    }

    requirement R009 {
        id: R009
        text: El sistema debe calcular automáticamente el IMC (peso/altura²)
        risk: high
        verifymethod: test
    }

    requirement R010 {
        id: R010
        text: Las métricas deben mostrarse ordenadas por fecha (más reciente primero)
        risk: medium
        verifymethod: test
    }

    requirement R011 {
        id: R011
        text: Solo el propietario puede eliminar sus propias métricas
        risk: high
        verifymethod: test
    }

    element Dashboard {
        type: interface
    }

    Dashboard - satisfies -> R008
    Dashboard - satisfies -> R009
    Dashboard - satisfies -> R010
    Dashboard - satisfies -> R011
```

## Mockup de la Interfaz del Dashboard

```
┌─────────────────────────────────────────────────────┐
│  StatTracker 📊          Usuario: Juan Pérez  [🚪]  │
├─────────────────────────────────────────────────────┤
│  [+ Añadir Métrica]  [👤 Ver Perfil]                │
├─────────────────────────────────────────────────────┤
│  Mis Métricas de Salud                              │
│  ┌───────────────────────────────────────────────┐ │
│  │ Fecha      Peso    Altura   IMC      [Acciones]│ │
│  │ 2025-01-15 75 kg   1.75 m   24.5     [🗑️]      │ │
│  │ 2025-01-10 76 kg   1.75 m   24.8     [🗑️]      │ │
│  │ 2025-01-05 77 kg   1.75 m   25.1     [🗑️]      │ │
│  └───────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────┘
```

## Formulario de Añadir Métrica

```
┌─────────────────────────────────────┐
│  Añadir Nueva Métrica               │
│                                     │
│  Peso (kg):                         │
│  [__________]                       │
│                                     │
│  Altura (m):                        │
│  [__________]                       │
│                                     │
│  IMC: [Calculado automáticamente]   │
│                                     │
│  [  Guardar  ]  [  Cancelar  ]      │
└─────────────────────────────────────┘
```
