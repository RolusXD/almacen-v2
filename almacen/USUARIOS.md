# Módulo de Gestión de Usuarios - Sistema de Almacén TOI

## 📋 Descripción

El módulo de usuarios permite administrar de forma segura el acceso al sistema, con roles diferenciados y control de permisos.

## 🔐 Sistema de Autenticación

### Campos del Usuario

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_usuario` | INT | Identificador único autoincremental |
| `nombre_completo` | VARCHAR(150) | Nombre completo del usuario |
| `usuario` | VARCHAR(50) | Nombre de usuario para login (único) |
| `password_hash` | VARCHAR(255) | Contraseña hasheada con bcrypt |
| `carnet` | VARCHAR(20) | Número de identificación personal (único) |
| `email` | VARCHAR(100) | Correo electrónico (único) |
| `fecha_nacimiento` | DATE | Fecha de nacimiento |
| `rol` | ENUM | 'admin' o 'usuario' |
| `activo` | TINYINT | 1 = Activo, 0 = Inactivo |
| `ultimo_acceso` | DATETIME | Fecha y hora del último acceso |
| `created_at` | TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | Fecha de última actualización |

### Roles del Sistema

#### 👤 Administrador (`admin`)
- Acceso completo al sistema
- Puede gestionar usuarios (CRUD completo)
- Puede ver todas las estadísticas
- Acceso a todas las funcionalidades

#### 👤 Usuario (`usuario`)
- Acceso limitado al sistema
- No puede gestionar usuarios
- Puede registrar equipos, accesorios y movimientos
- Puede ver el dashboard

## 🔒 Seguridad de Contraseñas

### Requisitos de Contraseña

El sistema valida que las contraseñas cumplan con:
- ✅ Mínimo 8 caracteres
- ✅ Al menos 1 letra mayúscula
- ✅ Al menos 1 letra minúscula
- ✅ Al menos 1 número

### Algoritmo de Hasheado

```php
// Se usa bcrypt con costo 10
password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);

// Verificación
password_verify($password, $hash);
```

### ¿Por qué bcrypt y no MD5?

| Característica | MD5 | bcrypt |
|---------------|-----|--------|
| Velocidad | Muy rápida (malo para passwords) | Lenta intencionalmente |
| Salt | No tiene | Integrado |
| Costo ajustable | No | Sí |
| Resistente a GPU | No | Sí |
| Recomendado 2024 | ❌ No | ✅ Sí |

**MD5 está obsoleto para contraseñas desde 2011.** Es vulnerable a:
- Ataques de fuerza bruta con GPU
- Rainbow tables
- Colisiones

## 📝 Gestión de Usuarios

### Crear Nuevo Usuario

1. Inicie sesión como administrador
2. Vaya a **Usuarios** en el menú lateral
3. Click en **Nuevo Usuario**
4. Complete el formulario:
   - Nombre completo *
   - Nombre de usuario *
   - Carnet (ID personal) *
   - Email *
   - Fecha de nacimiento *
   - Rol (admin/usuario)
   - Contraseña * (mínimo 8 caracteres)
   - Confirmar contraseña *
   - Activo (checkbox)
5. Click en **Guardar**

### Editar Usuario

1. En la lista de usuarios, click en el ícono ✏️ (lápiz)
2. Modifique los campos necesarios
3. Puede cambiar la contraseña (opcional)
4. Click en **Actualizar**

### Activar/Desactivar Usuario

- Click en el botón ⏸️ (pausa) para desactivar
- Click en el botón ▶️ (play) para activar
- Los usuarios inactivos no pueden iniciar sesión

### Eliminar Usuario

1. Click en el botón 🗑️ (papelera)
2. Confirme la eliminación
3. **Nota:** El sistema no elimina físicamente, solo desactiva
4. **Excepción:** El usuario `admin` no se puede eliminar

## 🚀 Inicio de Sesión

### Primer Acceso

```
Usuario: admin
Contraseña: Admin123!
```

### Pasos para Iniciar Sesión

1. Abra `http://localhost/almacen_moderno`
2. Ingrese su **nombre de usuario** (NO carnet)
3. Ingrese su **contraseña**
4. Click en **Iniciar Sesión**

### Si Olvidó su Contraseña

Actualmente, contacte al administrador para restablecerla manualmente:

```sql
-- Actualizar contraseña directamente en BD
-- Nueva contraseña: Temporal123!
UPDATE usuarios 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE usuario = 'nombre_usuario';
```

## 📊 Estadísticas de Usuarios

El dashboard muestra:
- Total de usuarios registrados
- Usuarios activos vs inactivos
- Administradores vs usuarios normales
- Último acceso de cada usuario

## 🔧 Consultas SQL Útiles

### Ver todos los usuarios activos
```sql
SELECT id_usuario, usuario, nombre_completo, email, rol, ultimo_acceso
FROM usuarios
WHERE activo = 1
ORDER BY created_at DESC;
```

### Usuarios que nunca han iniciado sesión
```sql
SELECT usuario, nombre_completo, email, created_at
FROM usuarios
WHERE ultimo_acceso IS NULL;
```

### Contar usuarios por rol
```sql
SELECT rol, COUNT(*) as total, 
       SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos
FROM usuarios
GROUP BY rol;
```

### Usuarios creados este mes
```sql
SELECT usuario, nombre_completo, email, created_at
FROM usuarios
WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
  AND YEAR(created_at) = YEAR(CURRENT_DATE());
```

## ⚠️ Buenas Prácticas

### Para Administradores

1. ✅ Cambiar contraseñas por defecto inmediatamente
2. ✅ Usar contraseñas únicas para cada usuario
3. ✅ Desactivar usuarios que ya no necesiten acceso
4. ✅ No compartir cuentas de usuario
5. ✅ Revisar periódicamente los últimos accesos

### Para Usuarios

1. ✅ Usar contraseñas seguras y únicas
2. ✅ No compartir su contraseña
3. ✅ Cerrar sesión al terminar
4. ✅ Reportar accesos sospechosos

## 🛡️ Características de Seguridad Implementadas

| Característica | Descripción |
|---------------|-------------|
| **Hash bcrypt** | Contraseñas hasheadas con costo ajustable |
| **Salt automático** | Cada password tiene salt único |
| **Preparado statements** | Previene SQL Injection |
| **Validación de entrada** | Sanitización de todos los datos |
| **Sesión segura** | Session ID regenerado en login |
| **Timeout de sesión** | Expira después de inactividad |
| **Registro de acceso** | Track del último login |
| **Roles y permisos** | Control de acceso basado en roles |

## 📈 Métricas del Módulo

| Métrica | Valor |
|---------|-------|
| Campos por usuario | 12 |
| Roles disponibles | 2 (admin, usuario) |
| Longitud mínima password | 8 caracteres |
| Costo de hash bcrypt | 10 |
| Tiempo de hasheado | ~100ms |
| Resistencia a fuerza bruta | Alta |

## 🔄 Migración desde Sistema Antiguo

Si viene del sistema anterior que usaba `numero_carnet` y `carnet` (contraseña):

```sql
-- Migrar usuarios antiguos (si existen)
INSERT INTO usuarios (nombre_completo, usuario, password_hash, carnet, email, fecha_nacimiento, rol, activo)
SELECT 
    nombre_completo,
    numero_carnet as usuario,
    password_hash, -- Asegúrese que ya esté hasheado
    numero_carnet as carnet,
    email,
    '1990-01-01' as fecha_nacimiento, -- Valor por defecto
    rol,
    activo
FROM sistema_antiguo.usuarios;
```

---

**Documentación actualizada:** Marzo 2026  
**Versión del módulo:** 2.0
