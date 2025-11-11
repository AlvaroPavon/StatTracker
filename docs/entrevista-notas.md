# Notas de Entrevista con el Cliente

## Información General

**Fecha de la entrevista**: Diciembre 2024  
**Cliente/Stakeholder**: Profesor del módulo Puesta en Producción Segura  
**Entrevistadores**: Equipo de desarrollo StatTracker  
**Duración**: 45 minutos

---

## Contexto del Proyecto

### Descripción Inicial del Cliente

El cliente solicitó el desarrollo de una aplicación web para el registro y seguimiento de estadísticas o métricas personales. La aplicación debe ser sencilla de usar pero funcional, enfocándose en la calidad del código y la implementación de buenas prácticas de desarrollo.

### Objetivo Principal

> "Necesito una aplicación que permita a los usuarios registrar y hacer seguimiento de sus métricas personales de salud, específicamente peso y altura, con cálculo automático del IMC."

---

## Requisitos Funcionales Identificados

### RF-001: Sistema de Autenticación

**Necesidad del cliente**:

* Los usuarios deben poder registrarse en el sistema
* Cada usuario debe tener acceso exclusivo a sus propios datos
* La autenticación debe ser segura

**Preguntas realizadas**:

**Q**: ¿Se requiere autenticación de dos factores?  
**R**: No es necesario en la versión inicial, pero la seguridad básica es fundamental.

**Q**: ¿Hay roles de usuario (admin, usuario normal)?  
**R**: No, todos los usuarios tienen el mismo nivel de acceso a sus propios datos.

**Q**: ¿Se necesita recuperación de contraseña?  
**R**: No es prioritario para la primera versión, puede implementarse después.

**Criterios de aceptación**:

* ✅ Formulario de registro con validación
* ✅ Formulario de login funcional
* ✅ Contraseñas almacenadas de forma segura (hash)
* ✅ Sesiones persistentes
* ❌ Recuperación de contraseña (futuro)

---

### RF-002: Gestión de Perfil de Usuario

**Necesidad del cliente**:

* Los usuarios deben poder actualizar su información personal
* Capacidad de cambiar la contraseña

**Preguntas realizadas**:

**Q**: ¿Qué información del perfil se puede modificar?  
**R**: Nombre, apellidos, email. La contraseña debe cambiarse por separado por seguridad.

**Q**: ¿Se requiere foto de perfil?  
**R**: Sería un plus, pero no es crítico para el MVP.

**Q**: ¿Pueden los usuarios eliminar su cuenta?  
**R**: No es necesario en la primera versión.

**Criterios de aceptación**:

* ✅ Actualizar nombre y apellidos
* ✅ Cambiar email (con validación de duplicados)
* ✅ Cambiar contraseña (verificando la actual)
* ✅ Foto de perfil (implementado)
* ❌ Eliminar cuenta (futuro)

---

### RF-003: Registro de Métricas de Salud

**Necesidad del cliente**:

> "Lo más importante es que los usuarios puedan añadir sus datos de peso y altura regularmente, y que el sistema calcule el IMC automáticamente."

**Preguntas realizadas**:

**Q**: ¿Qué métricas específicas se deben registrar?  
**R**: Peso, altura y fecha. El IMC debe calcularse automáticamente.

**Q**: ¿En qué unidades?  
**R**: Kilogramos para peso, metros para altura. El sistema debe ser claro en esto.

**Q**: ¿Se pueden registrar métricas de fechas pasadas?  
**R**: Sí, el usuario debe poder especificar la fecha del registro.

**Q**: ¿Hay límites en los valores?  
**R**: Deben ser valores realistas y positivos. La altura no puede ser cero (división por cero).

**Criterios de aceptación**:

* ✅ Formulario para añadir peso (kg)
* ✅ Formulario para añadir altura (m)
* ✅ Selección de fecha de registro
* ✅ Cálculo automático de IMC
* ✅ Validación de valores positivos
* ✅ Prevención de división por cero

---

### RF-004: Visualización de Historial

**Necesidad del cliente**:

* Los usuarios deben poder ver todos sus registros históricos
* El historial debe estar ordenado cronológicamente

**Preguntas realizadas**:

**Q**: ¿Cómo deben visualizarse los datos?  
**R**: Una tabla simple es suficiente, mostrando todos los datos claramente.

**Q**: ¿Se necesitan gráficos?  
**R**: No es prioritario para el MVP, pero sería una mejora futura interesante.

**Q**: ¿Cuántos registros se deben mostrar?  
**R**: Todos los registros del usuario, sin paginación es aceptable en esta versión.

**Criterios de aceptación**:

* ✅ Tabla con todos los registros del usuario
* ✅ Mostrar: peso, altura, IMC, fecha
* ✅ Ordenación por fecha (más reciente primero)
* ❌ Gráficos de evolución (futuro)
* ❌ Paginación (si se necesita en futuro)

---

### RF-005: Gestión de Registros

**Necesidad del cliente**:

* Los usuarios deben poder eliminar registros incorrectos

**Preguntas realizadas**:

**Q**: ¿Se pueden editar registros existentes?  
**R**: No es necesario. Pueden eliminar y crear uno nuevo si se equivocaron.

**Q**: ¿Se requiere confirmación antes de eliminar?  
**R**: Sí, para evitar eliminaciones accidentales.

**Q**: ¿Se guardan registros eliminados (papelera)?  
**R**: No, la eliminación puede ser permanente.

**Criterios de aceptación**:

* ✅ Botón de eliminar por registro
* ✅ Solo el propietario puede eliminar sus registros
* ✅ Eliminación permanente
* ❌ Confirmación de eliminación (recomendado en frontend)
* ❌ Edición de registros (futuro)

---

## Requisitos No Funcionales

### RNF-001: Seguridad

**Necesidades identificadas**:

* Contraseñas cifradas (bcrypt o similar)
* Protección contra SQL injection
* Validación de datos en servidor
* Sesiones seguras
* Cada usuario solo puede acceder a sus propios datos

**Implementación**:

* ✅ Password hashing con `password_hash()` de PHP (bcrypt)
* ✅ Prepared statements en todas las consultas SQL
* ✅ Validación de inputs en backend
* ✅ Sesiones PHP configuradas correctamente
* ✅ Verificación de user_id en todas las operaciones

---

### RNF-002: Usabilidad

**Necesidades del cliente**:

> "La aplicación debe ser intuitiva. Un usuario nuevo debe poder empezar a usarla sin manual."

**Requisitos**:

* Interfaz limpia y clara
* Mensajes de error descriptivos
* Formularios simples
* Feedback inmediato de acciones

**Implementación**:

* ✅ Diseño responsive
* ✅ Mensajes de error claros
* ✅ Validación en tiempo real (frontend)
* ✅ Confirmaciones de acciones exitosas

---

### RNF-003: Rendimiento

**Expectativas**:

* Tiempos de respuesta rápidos (<2 segundos)
* Base de datos optimizada

**Implementación**:

* ✅ Índices en base de datos (email, user_id)
* ✅ Consultas optimizadas
* ✅ Sin N+1 queries

---

### RNF-004: Mantenibilidad

**Necesidad del cliente**:

> "El código debe ser limpio y estar bien probado. Es un proyecto académico, así que la calidad es importante."

**Requisitos**:

* Código organizado en clases
* Separación de responsabilidades
* Pruebas unitarias
* Documentación clara

**Implementación**:

* ✅ Arquitectura MVC básica
* ✅ Clases específicas (Auth, User, Metrics)
* ✅ PHPUnit para testing
* ✅ Cobertura de código >70%
* ✅ Comentarios en código
* ✅ README con instrucciones

---

## Restricciones y Limitaciones

### Técnicas

* **Lenguaje**: PHP (7.4+)
* **Base de datos**: MySQL/MariaDB
* **Framework**: Ninguno (PHP vanilla)
* **Testing**: PHPUnit

### Temporales

* **Plazo**: 2-3 semanas para el MVP
* **Entrega**: Código + documentación + pruebas

### Presupuestarias

* Proyecto académico, sin presupuesto real
* Uso de herramientas gratuitas/open source

---

## Ambigüedades y Aclaraciones

### Puntos Ambiguos Identificados

1. **Unidades de medida**
   * Inicialmente no estaba claro si se usarían libras o kilogramos
   * **Aclaración**: Se usará sistema métrico (kg, metros)

2. **Formato de fecha**
   * ¿Permitir fechas futuras?
   * **Aclaración**: Idealmente no, pero no es crítico validarlo

3. **Límites de datos**
   * ¿Cuánto peso/altura son valores realistas?
   * **Aclaración**: Validación básica, confiar en el usuario

4. **Interpretación del IMC**
   * ¿Debe la aplicación dar consejos de salud?
   * **Aclaración**: No, solo calcular y mostrar el número

---

## Prioridades del Cliente

### Prioridad Alta (Must Have)

1. Sistema de autenticación funcional
2. Registro de peso, altura y fecha
3. Cálculo automático de IMC
4. Visualización de historial
5. Eliminación de registros

### Prioridad Media (Should Have)

1. Actualización de perfil
2. Cambio de contraseña
3. Validaciones robustas
4. Mensajes de error claros

### Prioridad Baja (Could Have)

1. Foto de perfil
2. Gráficos de evolución
3. Exportación de datos
4. Recuperación de contraseña

### Fuera del Alcance (Won't Have)

1. Aplicación móvil nativa
2. Integración con wearables
3. Red social / compartir datos
4. Planes de nutrición o ejercicio
5. Múltiples idiomas

---

## Conclusiones de la Entrevista

### Resumen de Requisitos Acordados

Se acordó desarrollar una aplicación web de seguimiento de métricas de salud con:

* Autenticación segura de usuarios
* Registro de peso, altura e IMC
* Historial completo de mediciones
* Gestión básica de perfil
* Interfaz limpia y responsive
* Código bien estructurado y probado

### Próximos Pasos

1. Crear mockups de las pantallas principales
2. Diseñar el esquema de base de datos
3. Implementar las clases básicas (Auth, User, Metrics)
4. Desarrollar las interfaces web
5. Escribir pruebas unitarias
6. Generar informe de cobertura
7. Documentar el proyecto

### Cambios Esperados

El cliente advirtió que podría haber cambios en los requisitos durante el desarrollo, simulando un entorno real de trabajo. El equipo debe estar preparado para adaptar el código de manera flexible.

---

## Anexos

### Anexo A: Esquema de Base de Datos Propuesto

```sql
-- Tabla de usuarios
CREATE TABLE usuarios (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100),
  apellidos VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  created_at TIMESTAMP
);

-- Tabla de métricas
CREATE TABLE metricas (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  peso DECIMAL(5,2),
  altura DECIMAL(3,2),
  imc DECIMAL(5,2),
  fecha_registro DATE,
  created_at TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES usuarios(id)
);
```

### Anexo B: Bocetos Iniciales

Durante la entrevista se realizaron bocetos a mano alzada de:

* Pantalla de login
* Dashboard principal
* Formulario de registro de datos
* Tabla de historial

Estos bocetos se digitalizaron posteriormente en mockups formales con Mermaid.

---

_Documento de trabajo interno. Estas notas sirvieron de base para el desarrollo del proyecto StatTracker._
