# Documentación de Arquitectura - StatTracker Mobile (Android)

Este documento explica cómo se ha estructurado la aplicación móvil nativa.

## 1. Patrón Arquitectónico: MVVM
La aplicación utiliza **Model-View-ViewModel** para separar la lógica de negocio de la interfaz de usuario:
- **Model**: En `data/model/`, define la estructura de datos que viaja por la API.
- **View**: En `ui/screens/`, componentes de Jetpack Compose que reaccionan al estado.
- **ViewModel**: En cada carpeta de pantalla, gestiona la lógica y expone un `StateFlow` a la vista.

## 2. Inyección de Dependencias
Para mantener el proyecto simple pero profesional, se ha utilizado un enfoque de **Manual Dependency Injection**:
- La configuración de Retrofit y el Repositorio se instancian de forma centralizada.
- Se utiliza `TokenManager` con **DataStore** para manejar la persistencia del JWT.

## 3. Flujo de Datos
1. El usuario interactúa con la **View**.
2. La View llama a un método en el **ViewModel**.
3. El ViewModel lanza una **Corrutina** y llama al **Repository**.
4. El Repository usa **Retrofit** para hacer la petición HTTP.
5. La respuesta actualiza el `UiState` del ViewModel.
6. La View se recompone automáticamente al detectar el cambio en el estado.

## 4. Gestión de Seguridad
- El token JWT se almacena de forma asíncrona mediante DataStore Preferences.
- Las llamadas a endpoints protegidos incluyen el token en el header `Authorization`.

---
*Documento generado por OpenClaw para soporte del proyecto final.*
