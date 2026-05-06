# StatTracker Mobile 📱

Aplicación Android nativa para StatTracker usando Kotlin y Jetpack Compose.

## 🏗️ Arquitectura

- **MVVM** (Model-View-ViewModel)
- **Jetpack Compose** para UI
- **Retrofit** para llamadas API
- **DataStore** para almacenamiento local (JWT token)
- **Coroutines + Flow** para asíncrono

## 📁 Estructura

```
app/src/main/java/com/stattracker/mobile/
├── data/
│   ├── api/           # Retrofit API service
│   ├── model/         # Data classes
│   └── repository/    # Repositorios
├── ui/
│   ├── theme/         # Tema y estilos
│   ├── navigation/    # Navegación
│   ├── screens/       # Pantallas (Login, Dashboard, etc.)
│   └── components/    # Componentes reutilizables
├── domain/
│   └── model/         # Modelos de dominio
└── util/
    └── Constants.kt   # Constantes y config
```

## 🚀 Configuración

### Requisitos
- Android Studio Hedgehog o superior
- SDK 26+ (Android 8.0+)
- Kotlin 1.9+

### API Base URL

La URL base por defecto apunta a la VM de Proxmox:

```text
http://192.168.5.34/
```

`StatTrackerApi` ya anade el prefijo `api/`, por lo que la URL base debe ser la raiz web, no `/api`.

Para compilar contra otro entorno se puede sobreescribir con una propiedad Gradle:

```bash
./gradlew :app:assembleDebug -PstattrackerBaseUrl=http://10.0.2.2:8000/
./gradlew :app:assembleDebug -PstattrackerBaseUrl=http://192.168.5.34/
```

## 📦 Dependencias Principales

- Retrofit 2.9.0
- OkHttp 4.12.0
- Gson 2.10.1
- Jetpack Compose BOM 2024.02.00
- Navigation Compose
- DataStore Preferences
- ViewModel Compose
- Coil (imágenes)

## 🎨 Pantallas

1. **Login** - Inicio de sesión
2. **Register** - Registro de usuario
3. **Dashboard** - Lista de métricas
4. **AddMetric** - Añadir nueva métrica
5. **Profile** - Perfil de usuario

## 🔐 Autenticación

El token JWT se guarda en DataStore y se incluye automáticamente en todas las peticiones.

## 🏃 Ejecutar

1. Abrir proyecto en Android Studio
2. Verificar o sobreescribir `stattrackerBaseUrl`
3. Asegurar que la API esta corriendo en Proxmox (`http://192.168.5.34/api`)
4. Run → Emulator o dispositivo físico

## 📱 Screenshots

_(Espacio para capturas)_

## 🐛 Conocido

- En emulator Android, usar `-PstattrackerBaseUrl=http://10.0.2.2:8000/` en vez de `localhost`
- En dispositivo fisico, usar la IP de la VM o del servidor accesible desde la misma red

## 📄 Licencia

MIT - IES Zaidín-Vergeles
