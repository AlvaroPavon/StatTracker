# Documentación Técnica: Aplicación Móvil StatTracker 📱

Este documento describe la estructura y funcionalidades de la aplicación móvil nativa desarrollada para consumir los servicios de la API StatTracker.

## 1. Stack Tecnológico

La aplicación ha sido desarrollada siguiendo los estándares modernos de la industria:
- **Lenguaje**: Kotlin (Nativo).
- **UI Framework**: Jetpack Compose (Declarativo).
- **Arquitectura**: MVVM (Model-View-ViewModel).
- **Red**: Retrofit 2 + OkHttp.
- **Persistencia**: DataStore Preferences (para el Token JWT).

## 2. Capas de la Aplicación

### Capa de Datos (Data Layer)
- **Retrofit Service**: Define las interfaces de comunicación con el servidor.
- **Repository**: Actúa como fuente única de verdad, gestionando si los datos vienen de la red o de la caché.
- **TokenManager**: Encapsula la seguridad del token JWT utilizando encriptación de preferencias de Android.

### Capa de Interfaz (UI Layer)
- **Navigation**: Gestión centralizada de rutas (Login -> Dashboard -> Perfil).
- **Screens**: Componentes Compose para cada funcionalidad.
- **Theme**: Sistema de diseño basado en Material Design 3.

## 3. Funcionalidades Implementadas

1.  **Gestión de Acceso**:
    - Pantalla de Login con persistencia de sesión.
    - Pantalla de Registro integrada con validación en tiempo real.
2.  **Dashboard de Salud**:
    - Visualización de métricas en formato de lista (Cards).
    - Cálculo visual de IMC y estado.
3.  **Seguridad**:
    - Interceptor de red que añade automáticamente el header `Authorization` a todas las peticiones salientes una vez que el usuario ha iniciado sesión.

## 4. Configuración para Desarrollo

Para conectar con el servidor local desde el emulador de Android, se ha configurado la dirección `10.0.2.2`, que mapea directamente al `127.0.0.1` de la máquina anfitriona.

---
*Documento generado para soporte académico - Proyecto StatTracker*
