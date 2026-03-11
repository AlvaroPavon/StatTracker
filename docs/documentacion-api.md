# Documentación Técnica: API REST StatTracker 🔌

Esta documentación detalla la implementación de la API REST añadida al proyecto StatTracker para permitir la interconexión con aplicaciones móviles y otros servicios externos.

## 1. Arquitectura y Diseño

La API ha sido diseñada siguiendo los principios **RESTful**, utilizando JSON como formato de intercambio de datos. Se integra directamente en el núcleo del proyecto para compartir la lógica de negocio y la base de datos existente.

### Componentes Clave:
- **Router (`api/index.php`)**: Punto de entrada único que gestiona el enrutamiento de peticiones.
- **Controladores (`api/controllers/`)**: Separan la lógica de autenticación, métricas y gestión de perfil.
- **Middleware (`api/middleware/`)**: Capa de seguridad para la validación de tokens.
- **Configuración (`api/config/`)**: Gestión centralizada de JWT y políticas CORS.

## 2. Autenticación (JWT)

A diferencia de la aplicación web que utiliza sesiones PHP, la API implementa **JSON Web Tokens (JWT)**. Esto permite una comunicación apátrida (stateless), ideal para aplicaciones móviles.

- **Flujo**: El usuario envía credenciales → La API valida → Devuelve un Token → El móvil guarda el token y lo envía en el Header `Authorization: Bearer <token>` en cada petición.
- **Seguridad**: Implementación nativa de JWT con firma HMAC-SHA256.

## 3. Catálogo de Endpoints

### Autenticación
- `POST /api/auth/register`: Permite la creación de nuevas cuentas desde el móvil.
- `POST /api/auth/login`: Valida credenciales y genera el token de acceso.
- `POST /api/auth/logout`: Informa del cierre de sesión (el cliente debe eliminar el token).

### Gestión de Métricas (Protegido)
- `GET /api/metrics`: Recupera el historial completo del usuario autenticado.
- `POST /api/metrics`: Registra nuevo peso y altura, calculando el IMC en el servidor.
- `PUT /api/metrics/{id}`: Permite corregir registros previos.
- `DELETE /api/metrics/{id}`: Eliminación de registros.

### Usuario y Perfil (Protegido)
- `GET /api/profile`: Devuelve los datos del usuario y estadísticas agregadas (mínimos, máximos y promedios).
- `PUT /api/profile`: Actualización de nombre y apellidos.
- `POST /api/profile/password`: Cambio de contraseña validando la anterior.

## 4. Integración con Base de Datos

La API utiliza el archivo `database_connection.php` original del proyecto, asegurando que:
1. No haya duplicidad de datos.
2. Los hashes de contraseñas sean compatibles (**Argon2id**).
3. Las restricciones de integridad (Foreign Keys) se mantengan.

---
*Documento generado para soporte académico - Proyecto StatTracker*
