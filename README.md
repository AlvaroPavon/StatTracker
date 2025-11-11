# StatTracker

## 🚀 Visión General

Plataforma para el registro, visualización y gestión de estadísticas o métricas personales/profesionales de manera sencilla.

## 🛠️ Requisitos del Sistema

* **Entorno:** Servidor web (Apache/Nginx) compatible con PHP.
* **Lenguaje:** PHP (Recomendado 7.4 o superior).
* **Base de Datos:** MySQL/MariaDB (Configurada según `database_connection.php`).
* **Dependencias:** Composer para la gestión de librerías y **PHPUnit** para pruebas.

## ⚙️ Instalación y Configuración

Sigue estos pasos para configurar y ejecutar el proyecto localmente:

1.  **Clonar el Repositorio:**
    El comando es:
    git clone [URL_DEL_REPOSITORIO]
    cd StatTracker
2.  **Instalar Dependencias:**
    Instala las dependencias de PHP necesarias (principalmente PHPUnit) ejecutando:
    composer install
3.  **Configuración de Base de Datos:**
    * Crea una base de datos en tu servidor MySQL/MariaDB.
    * Importa el esquema de la base de datos usando el archivo **`database.sql`**.
    * Configura la conexión a la base de datos con tus credenciales en el archivo `database_connection.php`.

## ✅ Pruebas y Cobertura de Código

El proyecto utiliza **PHPUnit** para las pruebas unitarias y de integración. Los archivos de prueba se encuentran en la carpeta `tests/`.

### 1. Ejecutar Pruebas Unitarias

Para ejecutar todos los tests configurados en `phpunit.xml`:

vendor/bin/phpunit

### 2. Generar Informe de Cobertura de Código

Para generar el informe de cobertura en formato **HTML** (requiere tener la extensión **Xdebug** o **PCOV** habilitada en tu instalación de PHP):

vendor/bin/phpunit --coverage-html coverage

Una vez ejecutado, el informe detallado se almacenará en la carpeta `coverage/`. Puedes acceder al informe principal abriendo el archivo **`coverage/index.html`** en cualquier navegador web.

---
