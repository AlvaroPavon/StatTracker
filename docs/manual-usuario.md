# Manual de Usuario - StatTracker

## Tabla de Contenido

1. [Introducción](#introducción)
2. [Requisitos del Sistema](#requisitos-del-sistema)
3. [Funcionalidades Principales](#funcionalidades-principales)
4. [Guía de Uso](#guía-de-uso)
5. [Interpretación del IMC](#interpretación-del-imc)
6. [Preguntas Frecuentes](#preguntas-frecuentes)
7. [Solución de Problemas](#solución-de-problemas)
8. [Glosario](#glosario)

---

## Introducción

### ¿Qué es StatTracker?

StatTracker es una plataforma web diseñada para el registro, visualización y gestión de estadísticas de salud personales. Permite a los usuarios realizar un seguimiento de sus métricas corporales como peso, altura e Índice de Masa Corporal (IMC) a lo largo del tiempo.

### Objetivo de la Aplicación

El objetivo principal de StatTracker es proporcionar una herramienta sencilla y eficaz para:

* Registrar datos de salud de forma periódica
* Visualizar la evolución de las métricas a lo largo del tiempo
* Calcular automáticamente el IMC
* Gestionar el perfil de usuario de forma segura
* Mantener un historial completo de mediciones

### Público Objetivo

Esta aplicación está dirigida a:

* Personas que desean monitorizar su estado físico
* Usuarios que siguen programas de pérdida o ganancia de peso
* Profesionales de la salud que necesitan registrar datos de pacientes
* Cualquier persona interesada en llevar un registro histórico de sus métricas corporales

---

## Requisitos del Sistema

### Requisitos Técnicos

* **Navegador web moderno**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
* **Conexión a Internet**: Requerida para acceder a la aplicación
* **JavaScript**: Debe estar habilitado en el navegador

### Requisitos para el Servidor

* PHP 7.4 o superior
* Servidor web (Apache/Nginx)
* MySQL 5.7+ o MariaDB 10.3+
* Composer para gestión de dependencias

---

## Funcionalidades Principales

### R001: Registro de Usuarios

El sistema permite a nuevos usuarios crear una cuenta proporcionando:

* Nombre
* Apellidos
* Dirección de correo electrónico
* Contraseña segura

**Validaciones**:

* El email debe tener un formato válido
* Todos los campos son obligatorios
* El email no puede estar duplicado en el sistema
* La contraseña se almacena de forma segura (hash)

### R002: Inicio de Sesión

Los usuarios registrados pueden acceder al sistema mediante:

* Email registrado
* Contraseña

**Características de seguridad**:

* Verificación de credenciales cifradas
* Sesión persistente después del login
* Mensaje de error genérico para evitar enumeration attacks
* **CAPTCHA matemático después de 3 intentos fallidos**
* **Alerta de seguridad si se detecta actividad sospechosa**

### R002.1: Cierre Automático de Sesión

El sistema cierra automáticamente la sesión después de **15 minutos de inactividad** para proteger tu cuenta:

* Se muestra una advertencia 60 segundos antes del cierre
* Puedes hacer clic en "Continuar sesión" para extenderla
* Si no respondes, la sesión se cierra automáticamente
* Al volver a iniciar sesión, verás un mensaje indicando que la sesión expiró por inactividad

### R003: Gestión de Perfil

Los usuarios autenticados pueden:

* Actualizar su nombre y apellidos
* Cambiar su dirección de email
* Modificar su contraseña
* Subir o actualizar foto de perfil

**Validaciones de actualización de perfil**:

* El nuevo email debe ser válido
* El email no puede estar en uso por otro usuario
* Todos los campos son obligatorios

**Validaciones de cambio de contraseña**:

* La contraseña actual debe ser correcta
* La nueva contraseña debe tener al menos 8 caracteres
* La confirmación de contraseña debe coincidir
* Todos los campos son obligatorios

### R004: Registro de Métricas de Salud

Los usuarios pueden añadir registros de sus métricas corporales:

* **Peso**: En kilogramos (Ej: 75.5)
* **Altura**: En metros (Ej: 1.75)
* **Fecha de registro**: Fecha de la medición
* **IMC**: Calculado automáticamente por el sistema

**Cálculo del IMC**:

```
IMC = Peso (kg) / (Altura (m))²
```

El sistema redondea el IMC a 2 decimales.

**Validaciones**:

* La altura debe ser mayor que 0 (evita división por cero)
* Todos los campos son obligatorios
* El peso y altura deben ser valores numéricos positivos

### R005: Visualización de Historial

Los usuarios pueden ver todos sus registros históricos:

* Listado ordenado por fecha (más reciente primero)
* Visualización de: peso, altura, IMC y fecha de registro
* Interfaz clara y fácil de leer

### R006: Eliminación de Registros

Los usuarios pueden eliminar registros específicos de su historial:

* Solo el propietario del registro puede eliminarlo
* Confirmación antes de eliminar (previene borrado accidental)
* Eliminación permanente de la base de datos

---

## Guía de Uso

### Primer Acceso: Registro

1. Accede a la página principal de StatTracker
2. Haz clic en el botón **"Registrarse"** o enlace **"¿No tienes cuenta? Regístrate aquí"**
3. Completa el formulario de registro:
   * Nombre: Tu nombre
   * Apellidos: Tus apellidos
   * Email: Tu correo electrónico
   * Contraseña: Una contraseña segura (mínimo 8 caracteres, con mayúsculas, minúsculas y números)
4. **Resuelve la verificación de seguridad (CAPTCHA matemático)**
   * Ejemplo: "¿Cuánto es 7 + 12?" → Escribe "19"
5. Haz clic en **"Registrarse"**
6. Si el registro es exitoso, serás redirigido automáticamente al dashboard

> **Nota**: El CAPTCHA matemático ayuda a proteger contra bots y registros automatizados.

### Iniciar Sesión

1. Accede a la página de login de StatTracker
2. Introduce tu email y contraseña
3. Haz clic en **"Iniciar Sesión"**
4. Serás redirigido al panel principal (Dashboard)

### Uso del Dashboard

Una vez autenticado, verás el panel principal con:

* **Barra de navegación superior**: Con tu nombre y opciones de perfil/logout
* **Formulario de añadir datos**: Para registrar nuevas métricas
* **Tabla de historial**: Con todos tus registros anteriores

### Añadir un Nuevo Registro de Salud

1. En el Dashboard, localiza el formulario **"Añadir Datos de Salud"**
2. Completa los campos:
   * **Peso (kg)**: Introduce tu peso en kilogramos (Ej: 72.5)
   * **Altura (m)**: Introduce tu altura en metros (Ej: 1.75)
   * **Fecha**: Selecciona la fecha de la medición
3. Haz clic en **"Añadir Datos"**
4. El sistema calculará automáticamente tu IMC
5. Los datos aparecerán inmediatamente en tu tabla de historial

> **Tip**: Para obtener la altura en metros, divide tu altura en centímetros entre 100. Ejemplo: 175 cm = 1.75 m

### Visualizar tu Historial

En el Dashboard, la tabla de historial muestra:

* **ID**: Identificador único del registro
* **Peso**: Tu peso registrado
* **Altura**: Tu altura registrada
* **IMC**: Índice de Masa Corporal calculado
* **Fecha de Registro**: Cuándo se tomó la medición
* **Acciones**: Botón para eliminar el registro

Los registros se ordenan automáticamente mostrando los más recientes primero.

### Eliminar un Registro

1. Localiza el registro que deseas eliminar en la tabla de historial
2. Haz clic en el botón **"Eliminar"** (icono de papelera o botón rojo)
3. Confirma la eliminación en el diálogo que aparece
4. El registro desaparecerá de tu historial

> **Advertencia**: La eliminación es permanente y no se puede deshacer. Asegúrate de que realmente deseas eliminar el registro.

### Actualizar tu Perfil

1. Haz clic en tu nombre o en el menú de usuario en la barra superior
2. Selecciona **"Mi Perfil"** o **"Perfil"**
3. Actualiza los campos que desees modificar:
   * Nombre
   * Apellidos
   * Email
   * Foto de perfil (si está disponible)
4. Haz clic en **"Actualizar Perfil"**
5. Recibirás una confirmación de que los cambios se guardaron correctamente

### Cambiar Contraseña

1. Accede a tu perfil (menú de usuario → "Perfil")
2. Busca la sección **"Cambiar Contraseña"**
3. Completa los campos:
   * **Contraseña Actual**: Tu contraseña actual
   * **Nueva Contraseña**: Tu nueva contraseña (mínimo 8 caracteres)
   * **Confirmar Nueva Contraseña**: Repite la nueva contraseña
4. Haz clic en **"Cambiar Contraseña"**
5. Recibirás confirmación del cambio

> **Importante**: Asegúrate de recordar tu nueva contraseña. El sistema no puede recuperar contraseñas olvidadas (están cifradas).

### Cerrar Sesión

1. Haz clic en tu nombre en la barra superior
2. Selecciona **"Cerrar Sesión"** o **"Logout"**
3. Serás redirigido a la página de inicio de sesión
4. Tu sesión se cerrará de forma segura

---

## Interpretación del IMC

### ¿Qué es el IMC?

El Índice de Masa Corporal (IMC) es una medida de asociación entre el peso y la altura de una persona. Se utiliza como indicador general del estado nutricional.

### Tabla de Clasificación del IMC (Adultos)

| IMC | Clasificación | Descripción |
|-----|---------------|-------------|
| < 18.5 | Bajo peso | Puede indicar desnutrición o problemas de salud |
| 18.5 - 24.9 | Peso normal | Rango de peso saludable |
| 25.0 - 29.9 | Sobrepeso | Peso por encima del rango saludable |
| 30.0 - 34.9 | Obesidad Clase I | Obesidad moderada |
| 35.0 - 39.9 | Obesidad Clase II | Obesidad severa |
| ≥ 40.0 | Obesidad Clase III | Obesidad mórbida |

> **Nota**: El IMC es solo un indicador y no considera factores como:
> * Masa muscular
> * Densidad ósea
> * Distribución de grasa
> * Edad y sexo
> 
> Consulta siempre con un profesional de la salud para una evaluación completa.

---

## Preguntas Frecuentes

### ¿Puedo usar StatTracker en mi móvil?

Sí, StatTracker es una aplicación web responsive que funciona en navegadores móviles modernos. Solo necesitas acceso a Internet.

### ¿Con qué frecuencia debo registrar mis datos?

La frecuencia depende de tus objetivos:

* **Pérdida/ganancia de peso**: Semanal o quincenal
* **Mantenimiento**: Mensual
* **Seguimiento médico**: Según indicaciones de tu profesional de salud

### ¿Puedo editar un registro después de crearlo?

Actualmente, la aplicación no permite editar registros existentes. Si cometiste un error:

1. Elimina el registro incorrecto
2. Crea uno nuevo con los datos correctos

### ¿Mis datos están seguros?

Sí, StatTracker implementa medidas de seguridad:

* Contraseñas cifradas con algoritmos de hash seguros (bcrypt)
* Sesiones seguras
* Validación de datos en servidor
* Protección contra inyección SQL mediante prepared statements
* Solo tú puedes ver y modificar tus datos

### ¿Qué hago si olvidé mi contraseña?

Actualmente, necesitarás contactar con el administrador del sistema para restablecer tu contraseña.

> **Nota**: En futuras versiones se planea implementar un sistema de recuperación automática de contraseñas.

### ¿Puedo exportar mis datos?

En la versión actual, la exportación de datos no está disponible. Esta funcionalidad está planificada para versiones futuras.

### ¿Cuántos registros puedo almacenar?

No hay límite específico en el número de registros que puedes almacenar. Puedes registrar tus métricas tantas veces como desees.

### ¿La aplicación calcula el IMC automáticamente?

Sí, el sistema calcula automáticamente el IMC cuando introduces tu peso y altura. No necesitas calcularlo manualmente.

---

## Solución de Problemas

### No puedo iniciar sesión

**Posibles causas y soluciones**:

1. **Credenciales incorrectas**
   * Verifica que el email esté escrito correctamente
   * Asegúrate de que la contraseña sea la correcta (mayúsculas/minúsculas)
   * Intenta restablecer la contraseña si es necesario

2. **Cuenta no registrada**
   * Verifica que completaste el registro
   * Si es necesario, regístrate nuevamente

3. **Problemas del navegador**
   * Borra las cookies y caché del navegador
   * Intenta con otro navegador
   * Asegúrate de que JavaScript esté habilitado

### No aparecen mis datos después de añadirlos

**Posibles soluciones**:

1. Refresca la página (F5 o Ctrl+R)
2. Cierra sesión y vuelve a iniciar sesión
3. Verifica que no haya mensajes de error en pantalla
4. Comprueba tu conexión a Internet

### Error al actualizar mi perfil

**Verifica lo siguiente**:

1. El email que intentas usar no esté ya registrado por otro usuario
2. Todos los campos estén completos
3. El formato del email sea válido (ejemplo@dominio.com)
4. No haya caracteres especiales no permitidos

### El IMC calculado parece incorrecto

**Revisa**:

1. Que hayas introducido la altura en **metros** (no centímetros)
   * Correcto: 1.75
   * Incorrecto: 175

2. Que el peso esté en **kilogramos**
   * Correcto: 70.5
   * Incorrecto: 70500 (gramos)

3. Usa el punto como separador decimal, no la coma
   * Correcto: 1.75
   * Incorrecto: 1,75

### La página no carga correctamente

1. Verifica tu conexión a Internet
2. Intenta refrescar la página (Ctrl+Shift+R para forzar recarga)
3. Borra la caché del navegador
4. Prueba con otro navegador
5. Contacta con el soporte técnico si el problema persiste

---

## Glosario

**IMC**: Índice de Masa Corporal. Medida que relaciona peso y altura para evaluar el estado nutricional.

**Dashboard**: Panel principal donde se visualizan los datos y se interactúa con la aplicación.

**Perfil**: Información personal del usuario (nombre, apellidos, email, foto).

**Métrica**: Medición específica de salud (peso, altura, IMC en una fecha determinada).

**Registro**: Conjunto de métricas guardadas en una fecha específica.

**Sesión**: Periodo de tiempo en el que un usuario está autenticado en el sistema.

**Hash**: Técnica de cifrado unidireccional usada para proteger contraseñas.

---

## Contacto y Soporte

### Reportar Problemas

Si encuentras algún problema o error en la aplicación:

1. Anota el mensaje de error exacto (si lo hay)
2. Describe los pasos que seguiste antes del error
3. Indica el navegador y versión que estás usando
4. Contacta con el administrador del sistema

### Sugerencias y Mejoras

Tus sugerencias son bienvenidas. Si tienes ideas para mejorar StatTracker, contacta con el equipo de desarrollo.

---

**Versión**: 1.0  
**Fecha de última actualización**: Agosto 2025  
**Autor**: Equipo StatTracker

---

_Este manual es un documento vivo y se actualiza regularmente. Para la versión más reciente, consulta la wiki del proyecto._
