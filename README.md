# StatTracker: Tu Rastreador de Progreso Físico 📊

<p align="center">
  <span style="font-size: 80px;">⚖️</span> </p>

Una aplicación web para monitorizar tu peso, estatura e Índice de Masa Corporal (IMC) a lo largo del tiempo. Registra tus métricas, gestiona tu perfil y visualiza tu progreso con gráficos y tablas. ¡Cuida tu salud día a día! 💪

---

## 📝 Descripción

StatTracker es una aplicación web intuitiva diseñada para que puedas llevar un control detallado de tus métricas físicas (peso, altura, IMC) de forma sencilla y segura. La aplicación requiere registro y autenticación para proteger tu información. Una vez dentro, podrás:

* Registrar nuevas mediciones.
* Consultar tu historial completo.
* Visualizar la evolución de tu IMC en un gráfico interactivo. 📈
* Actualizar tus datos personales (¡incluyendo tu foto de perfil! 📸).
* Cambiar tu contraseña de forma segura.

Construida con PHP y MySQL, y con un diseño moderno gracias a Tailwind CSS y Chart.js.

---

## ✨ Características Principales

* **👤 Sistema de Usuarios:** Registro seguro y autenticación (login/logout).
* **✏️ Gestión de Perfil:** Actualiza tu nombre, apellidos, email y foto de perfil fácilmente.
* **🔑 Cambio de Contraseña:** Modifica tu contraseña cuando lo necesites con seguridad.
* **📏 Registro de Métricas:** Formulario simple para añadir peso, altura y fecha.
* **⚖️ Cálculo de IMC Automático:** El IMC se calcula y guarda con cada registro. ¡Sin complicaciones!
* **📈 Dashboard Visual:**
    * Gráfico dinámico que muestra la evolución de tu IMC.
    * Tabla clara con tu historial de registros, incluyendo clasificación del IMC (Bajo Peso, Normal, Sobrepeso...).
* **🗑️ Eliminación de Registros:** ¿Un error? Elimina registros individuales fácilmente.
* **🔒 Seguridad:**
    * Contraseñas cifradas (bcrypt). 🛡️
    * Protección Anti-CSRF con tokens.
    * Configuración de sesión segura (HttpOnly, UseOnlyCookies).
    * Sentencias preparadas (PDO) contra Inyección SQL.
    * Validaciones robustas en el servidor. ✅
* **🎨 Interfaz de Usuario:** Diseño moderno y *responsive* con Tailwind CSS, animaciones suaves y pantallas de bienvenida. ✨

---

## 🛠️ Tecnologías Utilizadas

* **Backend:** PHP
* **Base de Datos:** MySQL (con PDO)
* **Frontend:** HTML5, Tailwind CSS (vía CDN), JavaScript
* **Librerías JS:**
    * Chart.js (gráficos) 📊
    * Animate.css (animaciones) 🎬

---

## 🗺️ Hoja de Ruta (Roadmap)

Estado actual del proyecto y próximos pasos:

* [x] **(MVP)** Sistema de registro y login.
* [x] **(MVP)** Base de datos funcional.
* [x] **(MVP)** Formulario de registro de métricas.
* [x] **(MVP)** Guardado y recuperación de datos.
* [x] **(MVP)** Tabla de historial.
* [x] Cálculo e inclusión del IMC.
* [x] Gráfico de evolución del IMC.
* [x] Funcionalidad de eliminación.
* [x] Sección de perfil (actualizar datos + foto).
* [x] Cambio de contraseña.
* [x] Medidas de seguridad implementadas.
* [ ] **(Mejora)** Exportar datos a CSV. 📄
* [ ] **(Mejora)** Usar Tailwind CSS con PostCSS/CLI (¡adiós CDN en producción!). 🚀
* [ ] **(Mejora)** Añadir más gráficos (ej. evolución del peso). 🧐
* [ ] **(Mejora)** Implementar pruebas unitarias/integración. ✔️

---
