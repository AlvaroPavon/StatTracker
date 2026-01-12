# 📋 Guía de Instalación con XAMPP

## 🔧 Requisitos Previos

### Software Necesario:
1. **XAMPP** (incluye Apache, MySQL y PHP)
   - Descarga: https://www.apachefriends.org/es/download.html
   - Versión recomendada: 8.0 o superior

2. **Composer** (gestor de dependencias PHP)
   - Descarga: https://getcomposer.org/download/
   - Es **OBLIGATORIO** para que funcione el proyecto

---

## 📥 Instalación Paso a Paso

### Paso 1: Instalar XAMPP
1. Descarga XAMPP desde https://www.apachefriends.org/es/download.html
2. Ejecuta el instalador
3. Selecciona los componentes: **Apache**, **MySQL**, **PHP**, **phpMyAdmin**
4. Instala en la ruta por defecto (`C:\xampp` en Windows)

### Paso 2: Instalar Composer
1. Descarga Composer desde https://getcomposer.org/download/
2. Ejecuta el instalador `Composer-Setup.exe`
3. Selecciona el PHP de XAMPP: `C:\xampp\php\php.exe`
4. Completa la instalación

### Paso 3: Copiar el Proyecto
1. Copia la carpeta del proyecto a `C:\xampp\htdocs\`
2. Ejemplo: `C:\xampp\htdocs\stattracker\`

### Paso 4: Instalar Dependencias PHP
1. Abre una terminal (CMD o PowerShell)
2. Navega a la carpeta del proyecto:
   ```bash
   cd C:\xampp\htdocs\stattracker
   ```
3. Ejecuta Composer:
   ```bash
   composer install
   ```
   > ⚠️ **Este paso es OBLIGATORIO**. Sin él, la aplicación NO funcionará.

### Paso 5: Crear la Base de Datos
1. Abre **XAMPP Control Panel**
2. Inicia **Apache** y **MySQL** (clic en "Start")
3. Abre el navegador y ve a: http://localhost/phpmyadmin
4. Clic en **"Nueva"** (panel izquierdo)
5. Nombre de la base de datos: `proyecto_imc`
6. Cotejamiento: `utf8mb4_unicode_ci`
7. Clic en **"Crear"**

### Paso 6: Importar las Tablas
1. En phpMyAdmin, selecciona la base de datos `proyecto_imc`
2. Clic en la pestaña **"Importar"**
3. Clic en **"Seleccionar archivo"**
4. Selecciona el archivo `database.sql` del proyecto
5. Clic en **"Importar"** (abajo)

### Paso 7: Acceder a la Aplicación
1. Abre el navegador
2. Ve a: http://localhost/stattracker/
3. ¡Listo! Ya puedes registrarte y usar la aplicación

---

## ⚙️ Configuración (Opcional)

### Si tu MySQL tiene contraseña:
Edita el archivo `database_connection.php`:
```php
$username = 'root';       // Usuario de MySQL
$password = 'TU_CONTRASEÑA';  // Cambia esto si tienes contraseña
```

### Si usas otro nombre de base de datos:
```php
$dbname = 'tu_nombre_de_bd';
```

---

## ❓ Solución de Problemas

### Error: "Class not found" o "autoload"
**Causa:** No se ejecutó `composer install`
**Solución:** 
```bash
cd C:\xampp\htdocs\stattracker
composer install
```

### Error: "Connection refused" o "Access denied"
**Causa:** MySQL no está iniciado o credenciales incorrectas
**Solución:**
1. Verifica que MySQL esté iniciado en XAMPP
2. Revisa usuario/contraseña en `database_connection.php`

### Error: "Table doesn't exist"
**Causa:** No se importó el archivo `database.sql`
**Solución:** Importa `database.sql` en phpMyAdmin

### Error: "Unknown column 'apellidos'"
**Causa:** Base de datos antigua sin la columna
**Solución:** La aplicación lo corrige automáticamente al cargar cualquier página. Si persiste, ejecuta en phpMyAdmin:
```sql
ALTER TABLE usuarios ADD COLUMN apellidos VARCHAR(100) NOT NULL DEFAULT '' AFTER nombre;
```

### Página en blanco
**Causa:** Error de PHP no mostrado
**Solución:** 
1. Revisa el archivo `C:\xampp\php\logs\php_error_log`
2. O activa errores en `php.ini`: `display_errors = On`

---

## 📁 Estructura de Carpetas Final

```
C:\xampp\htdocs\stattracker\
├── css/                    # Estilos CSS
├── js/                     # JavaScript
├── src/                    # Clases PHP (lógica)
├── uploads/                # Fotos de perfil
├── vendor/                 # Dependencias (creado por Composer)
├── database.sql            # Script de base de datos
├── database_connection.php # Configuración de BD
├── index.php               # Página de login
├── register_page.php       # Página de registro
├── dashboard.php           # Panel principal
├── profile.php             # Perfil de usuario
└── ... otros archivos PHP
```

---

## ✅ Checklist de Instalación

- [ ] XAMPP instalado
- [ ] Composer instalado
- [ ] Proyecto copiado a `htdocs`
- [ ] `composer install` ejecutado
- [ ] Base de datos `proyecto_imc` creada
- [ ] `database.sql` importado
- [ ] Apache y MySQL iniciados
- [ ] Acceso a http://localhost/stattracker/ funciona

---

## 🎉 ¡Listo!

Si seguiste todos los pasos, la aplicación debería funcionar correctamente.

**Credenciales de prueba** (si importaste los datos de prueba):
- Email: `test@example.com`
- Contraseña: Necesitas crear una cuenta nueva porque la de prueba tiene hash inválido

**Para crear tu cuenta:**
1. Ve a http://localhost/stattracker/
2. Clic en "Regístrate ahora"
3. Completa el formulario (contraseña debe tener mayúscula, minúscula y número)
