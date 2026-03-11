# StatTracker API v1 🚀

API REST para la aplicación móvil de StatTracker.

## 📍 Ubicación

La API está integrada en el proyecto principal:
```
StatTracker/
├── api/              ← API REST
│   ├── index.php     ← Entry point
│   ├── controllers/  ← Controladores
│   ├── middleware/   ← JWT Middleware
│   └── config/       ← Configuración
├── src/              ← Web app (se mantiene)
└── *.php             ← Web app (se mantiene)
```

## 🔌 Endpoints

### Autenticación (sin token requerido)

| Método | Endpoint | Descripción | Body |
|--------|----------|-------------|------|
| POST | `/api/auth/register` | Registro | `{"nombre", "apellidos", "email", "password"}` |
| POST | `/api/auth/login` | Login | `{"email", "password"}` |
| POST | `/api/auth/logout` | Logout | - |

### Métricas (requiere JWT)

| Método | Endpoint | Descripción | Body |
|--------|----------|-------------|------|
| GET | `/api/metrics` | Listar métricas | - |
| GET | `/api/metrics/:id` | Ver métrica | - |
| POST | `/api/metrics` | Crear métrica | `{"peso", "altura", "fecha_registro"}` |
| PUT | `/api/metrics/:id` | Actualizar | `{"peso", "altura", "fecha_registro"}` |
| DELETE | `/api/metrics/:id` | Eliminar | - |

### Perfil (requiere JWT)

| Método | Endpoint | Descripción | Body |
|--------|----------|-------------|------|
| GET | `/api/profile` | Ver perfil + stats | - |
| PUT | `/api/profile` | Actualizar perfil | `{"nombre", "apellidos"}` |
| POST | `/api/profile/password` | Cambiar contraseña | `{"current_password", "new_password"}` |

## 🔐 Autenticación

Todos los endpoints (excepto login/register/logout) requieren JWT:

```
Authorization: Bearer <tu_token_jwt>
```

## 🚀 Uso

### 1. Registrar usuario
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan",
    "apellidos": "Pérez",
    "email": "juan@example.com",
    "password": "Password123"
  }'
```

### 2. Login (obtener token)
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "juan@example.com",
    "password": "Password123"
  }'
```

**Respuesta:**
```json
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "nombre": "Juan",
    "apellidos": "Pérez",
    "email": "juan@example.com"
  }
}
```

### 3. Crear métrica (con token)
```bash
curl -X POST http://localhost:8000/api/metrics \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <tu_token>" \
  -d '{
    "peso": 75.5,
    "altura": 1.75,
    "fecha_registro": "2026-03-12"
  }'
```

### 4. Listar métricas
```bash
curl -X GET http://localhost:8000/api/metrics \
  -H "Authorization: Bearer <tu_token>"
```

## 📦 Respuestas

Todas las respuestas son JSON:

**Éxito:**
```json
{
  "success": true,
  "message": "Operación completada",
  "data": {...}
}
```

**Error:**
```json
{
  "error": "Descripción del error"
}
```

## 🗄️ Base de Datos

La API usa la misma BD que la web app:
- Database: `proyecto_imc`
- Tablas: `usuarios`, `metricas`
- Conexión: `database_connection.php` (existente)

## ⚙️ Configuración

Editar `api/config/jwt.php`:
- `JWT_SECRET`: Cambiar en producción
- `JWT_EXPIRY`: Duración del token (default: 3600s)

## 🌐 CORS

La API permite peticiones desde cualquier origen (para desarrollo móvil).
En producción, restringir en `api/config/cors.php`.

## 🧪 Testing

Probar con los usuarios de prueba de `database.sql`:
- Email: `test@example.com`
- Password: `Password123`
