# Memoria de Implementación: OWASP MASTG Resilience 🛡️

**Alumno:** Alvaro Pavón Martínez  
**Proyecto:** StatTracker Mobile  
**Fecha:** Marzo 2026

---

## 1. Objetivo del Fortalecimiento
El objetivo de este desarrollo ha sido fortalecer la aplicación StatTracker Mobile integrando controles de la categoría **MSTG-RESILIENCE** de OWASP. Se han implementado mecanismos de defensa activa para dificultar el análisis dinámico, la ingeniería inversa y la manipulación del software en tiempo de ejecución.

---

## 2. Requisitos Técnicos Implementados

### 2.1. Nivel 1: Detección de Root y Entorno Inseguro (MSTG-RES-1)
*   **Control**: Detección de privilegios de superusuario para evitar la ejecución en dispositivos comprometidos.
*   **Implementación**: Integración de la librería `RootBeer` para una detección multivariable (búsqueda de binarios `su`, chequeo de archivos de lectura/escritura en directorios del sistema, presencia de apps como Magisk o SuperSU).
*   **Política de Seguridad**: Si `SecurityCheck.isDeviceRooted(context)` devuelve `true`, la aplicación muestra un `Toast` informativo y finaliza la ejecución inmediatamente mediante `finishAffinity()`.

### 2.2. Nivel 2: Anti-Debugging y Prevención de Análisis Dinámico (MSTG-RES-2)
*   **Control**: Evitar que la aplicación sea analizada en tiempo de ejecución mediante depuradores (JDWP).
*   **Implementación**: 
    1.  Configuración estática: `android:debuggable="false"` en el manifiesto para versiones de lanzamiento.
    2.  Chequeo dinámico: En el `onCreate` de `MainActivity.kt`, se verifica el estado del flag de depuración del sistema:
        ```kotlin
        if (android.os.Debug.isDebuggerConnected()) { exitProcess(0) }
        ```
*   **Efectividad**: Si se intenta "Attachear" un depurador desde Android Studio o herramientas como JDB, la aplicación se cierra de forma proactiva.

### 2.3. Nivel 3: Verificación de Integridad y Firma (MSTG-RES-3)
*   **Control**: Detectar modificaciones en el código o recursos (Anti-Tampering).
*   **Implementación**: Se ha desarrollado una lógica en `SecurityCheck.kt` que recupera los certificados de firma (`signingInfo` para API 28+ y `signatures` para versiones anteriores). Se genera un hash SHA-256 del certificado y se compara con el valor original esperado.
*   **Efectividad**: Evita el re-empaquetado malicioso de la APK. Si el hash no coincide con el del desarrollador original, la app bloquea el acceso.

### 2.4. Nivel 4: Ofuscación de Código y Empaquetado (MSTG-RES-4)
*   **Control**: Dificultar la lectura del código tras descompilar la APK (ingeniería inversa estática).
*   **Implementación**: Activación de R8/ProGuard en `build.gradle.kts`:
    ```kotlin
    buildTypes {
        release {
            isMinifyEnabled = true
            isShrinkResources = true
            proguardFiles(getDefaultProguardFile("proguard-android-optimize.txt"), "proguard-rules.pro")
        }
    }
    ```
*   **Efectividad**: Los nombres de clases, métodos y variables se renombran a carácteres ilegibles (a, b, c...), haciendo casi imposible entender la lógica de negocio sin el código fuente.

### 2.5. Nivel 5: Respuesta Dinámica y Resiliencia UX (MSTG-RES-5)
*   **Control**: Reacción proactiva y cierre controlado sin comprometer la experiencia de usuario técnica.
*   **Implementación**: Todas las detecciones de seguridad anteriores (Root, Debug, Firma) se gestionan a través de una interfaz de respuesta clara.
*   ** UX de Seguridad**: En lugar de un "crash" (cierre brusco), la app presenta un mensaje informativo al usuario antes de salir, asegurando que el cierre sea una decisión de seguridad consciente y no un error del sistema.

---

## 3. Conectividad y Resiliencia de Red
Para garantizar la comunicación con el servidor XAMPP en el entorno de pruebas:
1.  **Protección de Tráfico**: Se ha configurado el uso de `CleartextTraffic` controlado en el manifiesto para permitir la conexión con la IP local durante la fase de desarrollo.
2.  **Configuración de IP Local**: Adaptación de `BASE_URL` a `http://192.168.137.1:8080/proyecto_imc/` para permitir el acceso desde dispositivos físicos a través del Hotspot de Windows.
3.  **Sanitización de Endpoints**: Corrección de la estructura de rutas para evitar errores 404, asegurando que la aplicación sea resiliente ante cambios en la estructura de carpetas del servidor Apache.

---

## 4. Evaluación de Resultados
| Criterio | Estado | Implementación |
| :--- | :---: | :--- |
| **MSTG-RES-1/2** | ✅ Excelente | Bloqueo efectivo de Root y Debugger con salida gestionada. |
| **MSTG-RES-3/4** | ✅ Excelente | Código ofuscado con R8 y verificación de firma activa. |
| **Resiliencia/UX** | ✅ Excelente | Cierre controlado con mensajes claros al usuario. |
