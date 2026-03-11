# 🚀 Quick Start - API StatTracker

## En 3 minutos

### 1. Iniciar el servidor

```bash
cd StatTracker
php -S localhost:8000
```

### 2. Login (obtener token)

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"Password123"}'
```

**Guarda el token** de la respuesta.

### 3. Probar endpoints

```bash
# Listar métricas
curl http://localhost:8000/api/metrics \
  -H "Authorization: Bearer <tu_token>"

# Crear métrica
curl -X POST http://localhost:8000/api/metrics \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <tu_token>" \
  -d '{"peso":75.5,"altura":1.75}'

# Ver perfil
curl http://localhost:8000/api/profile \
  -H "Authorization: Bearer <tu_token>"
```

## 📱 Desde App Móvil

### Ejemplo en JavaScript/React Native:

```javascript
// Login
const login = async (email, password) => {
  const response = await fetch('http://localhost:8000/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  const data = await response.json();
  return data.token; // Guardar en AsyncStorage
};

// Obtener métricas
const getMetrics = async (token) => {
  const response = await fetch('http://localhost:8000/api/metrics', {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  return await response.json();
};
```

### Ejemplo en Flutter/Dart:

```dart
// Login
Future<String> login(String email, String password) async {
  final response = await http.post(
    Uri.parse('http://localhost:8000/api/auth/login'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({'email': email, 'password': password}),
  );
  final data = jsonDecode(response.body);
  return data['token'];
}

// Obtener métricas
Future<List> getMetrics(String token) async {
  final response = await http.get(
    Uri.parse('http://localhost:8000/api/metrics'),
    headers: {'Authorization': 'Bearer $token'},
  );
  final data = jsonDecode(response.body);
  return data['metrics'];
}
```

## 🧪 Test Automático

```bash
php api/test.php
```

## ✅ Endpoints Resumen

| Endpoint | Método | Auth |
|----------|--------|------|
| `/api/auth/register` | POST | ❌ |
| `/api/auth/login` | POST | ❌ |
| `/api/auth/logout` | POST | ✅ |
| `/api/metrics` | GET/POST | ✅ |
| `/api/metrics/:id` | GET/PUT/DELETE | ✅ |
| `/api/profile` | GET/PUT | ✅ |
| `/api/profile/password` | POST | ✅ |

## 🆘 Problemas Comunes

**401 Unauthorized:** Token expirado o inválido → hacer login de nuevo

**404 Not Found:** Verificar que la ruta empieza con `/api/`

**CORS Error:** En desarrollo móvil, usar HTTP en vez de HTTPS

---

Más info: [README.md](README.md)
