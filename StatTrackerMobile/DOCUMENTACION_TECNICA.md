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

## 3.1. Integracion con Proxmox
La API desplegada en Proxmox queda disponible en:

```text
http://192.168.5.34/api
```

La aplicacion Android toma la URL base desde `BuildConfig.STATTRACKER_BASE_URL`, generada en Gradle. El valor por defecto es:

```text
http://192.168.5.34/
```

Las rutas Retrofit ya incluyen `api/...`, por lo que no se debe poner `/api` al final de la URL base. Para otro entorno:

```bash
./gradlew :app:assembleDebug -PstattrackerBaseUrl=http://10.0.2.2:8000/
```

## 4. Gestión de Seguridad
- El token JWT se almacena de forma asíncrona mediante DataStore Preferences.
- Las llamadas a endpoints protegidos incluyen el token en el header `Authorization`.
- El interceptor HTTP solo registra cuerpos completos en builds `debug`; en `release` queda desactivado para no exponer credenciales o JWT en logs.

---
*Documento generado por OpenClaw para soporte del proyecto final.*
