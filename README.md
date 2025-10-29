# StatTracker: Tu Rastreador de Progreso Físico 📊

Una aplicación web para monitorizar tu peso, estatura e Índice de Masa Corporal (IMC) a lo largo del tiempo. Registra tus métricas, gestiona tu perfil y visualiza tu progreso con gráficos y tablas.

---

## 📝 Descripción

StatTracker es una aplicación web diseñada para que los usuarios puedan llevar un control de sus métricas físicas (peso, altura, IMC). La aplicación requiere registro y autenticación de usuarios. Una vez dentro, los usuarios pueden registrar nuevas métricas, ver su historial, visualizar la evolución de su IMC en un gráfico, actualizar su información personal (incluyendo foto de perfil) y cambiar su contraseña. La aplicación está construida con PHP y MySQL, utilizando Tailwind CSS para el diseño y Chart.js para la visualización de datos.

---

## ✨ Características Principales

* **👤 Sistema de Usuarios:** Registro seguro de nuevos usuarios y autenticación (login/logout).
* **✏️ Gestión de Perfil:** Los usuarios pueden ver y actualizar su nombre, apellidos, email y foto de perfil.
* **🔑 Cambio de Contraseña:** Funcionalidad segura para cambiar la contraseña.
* **📏 Registro de Métricas:** Formulario para registrar peso, altura y fecha.
* **⚖️ Cálculo de IMC Automático:** Calcula y guarda el IMC con cada nuevo registro.
* **📈 Dashboard Visual:**
    * Muestra la evolución del IMC en un gráfico de líneas.
    * Presenta el historial completo de registros en una tabla ordenada cronológicamente.
    * Incluye la clasificación del IMC (Bajo Peso, Normal, Sobrepeso, etc.).
* **🗑️ Eliminación de Registros:** Permite eliminar registros individuales del historial.
* **🔒 Seguridad:**
    * Contraseñas hasheadas (bcrypt).
    * Protección contra CSRF mediante tokens.
    * Configuración segura de sesiones (HttpOnly, UseOnlyCookies).
    * Uso de sentencias preparadas PDO para prevenir inyección SQL.
    * Validaciones de entrada en el lado del servidor.
* **🎨 Interfaz de Usuario:** Diseño moderno y responsivo utilizando Tailwind CSS, con animaciones y splash screens.

---

## 🛠️ Tecnologías Utilizadas

* **Backend:** PHP
* **Base de Datos:** MySQL (con PDO para la conexión)
* **Frontend:** HTML5, Tailwind CSS (vía CDN), JavaScript
* **Librerías JavaScript:**
    * Chart.js (para gráficos)
    * Animate.css (para animaciones CSS)

---

## 🗺️ Hoja de Ruta (Roadmap)

Este es el plan de desarrollo, reflejando el estado actual:

* [x] **(MVP)** Sistema de registro y login de usuarios.
* [x] **(MVP)** Base de datos para almacenar usuarios y métricas.
* [x] **(MVP)** Formulario de entrada de datos (peso, altura, fecha) y validaciones.
* [x] **(MVP)** Lógica para guardar y recuperar registros de la base de datos.
* [x] **(MVP)** Visualización del historial en una tabla ordenada por fecha.
* [x] Cálculo e inclusión del IMC en los registros.
* [x] Implementación de un gráfico de líneas para visualizar el progreso del IMC.
* [x] Funcionalidad para eliminar registros.
* [x] Sección de perfil de usuario (actualizar datos, foto).
* [x] Funcionalidad para cambiar contraseña.
* [x] Medidas de seguridad implementadas (Hashing, CSRF, Prepared Statements, Session Security).
* [ ] **(Mejora Futura)** Opción para exportar los datos a un archivo CSV. 📄
* [ ] **(Mejora Futura)** Implementación de Tailwind CSS mediante PostCSS/CLI en lugar de CDN para producción. 🚀
* [ ] **(Mejora Futura)** Más opciones de visualización (ej. gráfico de peso). 🧐
* [ ] **(Mejora Futura)** Pruebas unitarias y de integración. ✅

---
