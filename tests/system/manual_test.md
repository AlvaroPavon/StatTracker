# Guía de Pruebas de Sistema (Manuales)

Este documento detalla los casos de prueba paso a paso para validar el sistema StatTracker en un entorno de producción simulado.

## Entorno de Prueba
* **Navegador**: Chrome / Firefox (Última versión)
* **Servidor**: PHP Built-in Server (localhost:8000)
* **Base de Datos**: MySQL/MariaDB con esquema `stattracker` limpio.

## Casos de Prueba (End-to-End)

### TC-01: Flujo Completo de Registro e Inicio de Sesión
**Objetivo**: Verificar que un usuario nuevo puede registrarse y acceder.

1. **Navegar** a `http://localhost:8000`.
2. **Click** en "Registrarse".
3. **Rellenar** formulario:
   - Nombre: `Test`
   - Apellidos: `User`
   - Email: `test@example.com`
   - Password: `password123`
   - Confirmar: `password123`
4. **Enviar**.
5. **Esperado**: Redirección al Login con mensaje de éxito.
6. **Acción**: Ingresar con `test@example.com` / `password123`.
7. **Esperado**: Acceso correcto al Dashboard.

### TC-02: Gestión de Métricas (CRUD)
**Objetivo**: Verificar la creación y visualización de datos de salud.

1. **Precondición**: Estar logueado como `test@example.com`.
2. **Acción**: En el Dashboard, formulario "Añadir Datos":
   - Peso: `80`
   - Altura: `1.80`
   - Fecha: `Hoy`
3. **Click** en "Añadir Datos".
4. **Esperado**:
   - La página se recarga.
   - Aparece una nueva fila en la tabla.
   - El IMC calculado muestra `24.69`.
5. **Acción**: Click en el botón "Eliminar" del registro creado.
6. **Esperado**: El registro desaparece de la tabla.

### TC-03: Validación de Errores
**Objetivo**: Verificar que el sistema maneja datos incorrectos.

1. **Acción**: Intentar añadir datos con Peso negativo `-5`.
2. **Esperado**: El sistema muestra un error o no permite el envío (validación HTML5/PHP).
3. **Acción**: Intentar Login con contraseña incorrecta.
4. **Esperado**: Mensaje de "Credenciales inválidas".

### TC-04: Persistencia y Sesión
**Objetivo**: Verificar seguridad básica.

1. **Acción**: Copiar la URL del Dashboard.
2. **Acción**: Click en "Cerrar Sesión".
3. **Acción**: Intentar acceder a la URL del Dashboard pegándola en la barra.
4. **Esperado**: Redirección forzada al Login (no permite acceso sin sesión).