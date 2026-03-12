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

Editar `app/src/main/java/com/stattracker/mobile/util/Constants.kt`:

```kotlin
const val BASE_URL = "http://10.0.2.2:8000"  // Emulator
// o
const val BASE_URL = "http://TU_IP:8000"     // Dispositivo real
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
2. Configurar `BASE_URL` en Constants.kt
3. Asegurar que la API está corriendo (`php -S localhost:8000`)
4. Run → Emulator o dispositivo físico

## 📱 Screenshots

_(Espacio para capturas)_

## 🐛 Conocido

- En emulator Android, usar `10.0.2.2` en vez de `localhost`
- En dispositivo físico, usar la IP de tu máquina

## 📄 Licencia

MIT - IES Zaidín-Vergeles
