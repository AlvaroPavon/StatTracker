# Mockups de StatTracker

Esta carpeta contiene los mockups y diagramas de requisitos de la aplicación StatTracker.

---

## 📁 Archivos

| Archivo | Descripción | Requisitos |
|---------|-------------|------------|
| [login-register.md](login-register.md) | Pantallas de login y registro | R001-R007 |
| [dashboard.md](dashboard.md) | Dashboard principal con métricas | R008-R011 |
| [profile.md](profile.md) | Gestión de perfil y contraseña | R012-R016 |

---

## 🛠️ Herramientas Utilizadas

- **Mermaid**: Para diagramas de flujo y diagramas de requisitos
- **ASCII Art**: Para mockups visuales de las interfaces

## 👀 Visualización

Los diagramas Mermaid pueden visualizarse en:
- **GitHub**: Renderiza Mermaid automáticamente
- **VS Code**: Con extensión "Mermaid Preview"
- **Online**: [Mermaid Live Editor](https://mermaid.live/)

---

## 📋 Requisitos Funcionales Documentados

### Autenticación (R001-R007)

| ID | Requisito | Estado |
|----|-----------|--------|
| R001 | Login con email y contraseña | ✅ |
| R002 | Contraseñas cifradas con Argon2id/bcrypt + pepper | ✅ |
| R003 | Mensajes de error claros | ✅ |
| R004 | Formulario de registro completo | ✅ |
| R005 | Email único | ✅ |
| R006 | Validación de formato de email | ✅ |
| R007 | Contraseña con requisitos de complejidad (mín 8 chars, mayúscula, minúscula, número) | ✅ |

### Dashboard (R008-R011)

| ID | Requisito | Estado |
|----|-----------|--------|
| R008 | Mostrar métricas del usuario autenticado | ✅ |
| R009 | Cálculo automático de IMC | ✅ |
| R010 | Métricas ordenadas por fecha | ✅ |
| R011 | Solo el propietario puede eliminar sus métricas | ✅ |

### Perfil (R012-R016)

| ID | Requisito | Estado |
|----|-----------|--------|
| R012 | Actualización de datos personales | ✅ |
| R013 | Validación de email único al actualizar | ✅ |
| R014 | Cambio de contraseña con verificación | ✅ |
| R015 | Verificación de contraseña actual | ✅ |
| R016 | Validación de nueva contraseña | ✅ |

---

## 🔗 Documentación Relacionada

- [Índice de documentación](../home.md)
- [Manual de usuario](../manual-usuario.md)
- [Seguridad técnica](../seguridad-tecnica.md)

---

**Última actualización**: Agosto 2025
