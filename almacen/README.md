# Sistema de Almacén TOI - Versión Mejorada

## 📋 Descripción

Sistema moderno de gestión de almacén para TOI (Telecomunicaciones Oportunas Inteligentes), desarrollado con arquitectura MVC y las mejores prácticas de seguridad.

## ✨ Mejoras Implementadas

### Seguridad
- ✅ **Autenticación segura** con password_hash (bcrypt)
- ✅ **Protección SQL Injection** mediante PDO con prepared statements
- ✅ **Sistema de sesiones** seguro
- ✅ **Sanitización de datos** en todos los formularios
- ✅ **Protección XSS** con htmlspecialchars

### Arquitectura
- ✅ **Patrón MVC** (Modelo-Vista-Controlador)
- ✅ **Código modular** y reutilizable
- ✅ **Separación de responsabilidades**
- ✅ **Base de datos normalizada** con integridad referencial

### Interfaz de Usuario
- ✅ **Bootstrap 5** para diseño responsivo
- ✅ **DataTables** para tablas interactivas
- ✅ **Dashboard** con estadísticas en tiempo real
- ✅ **Navegación intuitiva** con sidebar

### Funcionalidades
- ✅ Gestión de equipos (CRUD completo)
- ✅ Gestión de accesorios (CRUD completo)
- ✅ Movimientos de equipos con actualización automática de estados
- ✅ Movimientos de accesorios con control de stock
- ✅ Búsqueda y filtrado avanzado
- ✅ Reportes y estadísticas

## 🚀 Instalación

### Requisitos Previos
- PHP 8.0 o superior con las extensiones PDO y pdo_pgsql habilitadas
- PostgreSQL 13 o superior
- Navegador web moderno (Chrome, Firefox, Edge)

### Pasos de Instalación

1. **Copiar la carpeta**
   ```
   Copie la carpeta `almacen_moderno` a: C:\xampp\htdocs\
   ```

2. **Crear la base de datos**
   - Cree una base PostgreSQL llamada `almacen_toi`
   - Importe el archivo: `database/almacen_toi_postgresql.sql`
   - Esto creará la estructura, datos iniciales, vistas y triggers

3. **Configurar la conexión** (opcional)
   - Edite `config/database.php` si necesita cambiar las credenciales:
   ```env
   DB_DRIVER=pgsql
   DB_HOST=localhost
   DB_PORT=5432
   DB_NAME=almacen_toi
   DB_USER=postgres
   DB_PASS=su_password
   ```

4. **Acceder al sistema**
   - Abra su navegador y vaya a: `http://localhost/almacen_moderno`

## 🔐 Credenciales de Acceso

**Usuario Administrador por defecto:**
- Usuario: `admin`
- Contraseña: `Admin123!`
- Carnet: `00000000`
- Email: `admin@toi.bo`

**⚠️ IMPORTANTE:** 
1. Cambie la contraseña después del primer acceso
2. El sistema requiere contraseñas seguras (8+ caracteres, mayúscula, minúscula, número)
3. Solo los administradores pueden crear nuevos usuarios

## 📁 Estructura de Directorios

```
almacen_moderno/
├── config/                 # Configuración
│   ├── config.php         # Configuración general
│   ├── database.php       # Credenciales de BD
│   └── databasepdo.php    # Clase de conexión PDO
├── core/                   # Núcleo del sistema
│   ├── Controller.php     # Controlador base
│   └── Model.php          # Modelo base
├── controllers/            # Controladores
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── EquiposController.php
│   ├── AccesoriosController.php
│   └── MovimientosController.php
├── models/                 # Modelos de datos
│   ├── Usuario.php
│   ├── Equipo.php
│   ├── Accesorio.php
│   └── Movimiento.php
├── views/                  # Vistas
│   ├── layouts/
│   │   ├── header.php
│   │   └── footer.php
│   ├── auth/
│   │   └── login.php
│   ├── dashboard/
│   │   └── index.php
│   ├── equipos/
│   │   ├── index.php
│   │   └── form.php
│   ├── accesorios/
│   │   ├── index.php
│   │   └── form.php
│   └── movimientos/
│       ├── equipos.php
│       └── accesorios.php
├── database/               # Base de datos
│   └── almacen_toi_postgresql.sql
└── index.php               # Punto de entrada
```

## 📊 Estructura de la Base de Datos

### Tablas Principales

1. **usuarios** - Gestión de usuarios del sistema
   - Campos: id_usuario, nombre_completo, usuario, password_hash, carnet, email, fecha_nacimiento, rol, activo, ultimo_acceso
   - Roles: admin, usuario
   - Contraseñas hasheadas con bcrypt

2. **equipos** - Registro de equipos de red
3. **accesorios** - Registro de accesorios
4. **movimientos_equipos** - Historial de movimientos de equipos
5. **movimientos_accesorios** - Historial de movimientos de accesorios
6. **equipos_retirados** - Equipos dados de baja
7. **accesorios_retirados** - Accesorios dados de baja
8. **actividades** - Calendario de actividades

### Vistas y Triggers

- **vista_equipos_disponibles** - Equipos disponibles para uso
- **vista_accesorios_stock_bajo** - Accesorios con stock menor a 10
- **Triggers** - Actualización automática de estados

## 🔧 Migración desde el Sistema Antiguo

Si ya tiene datos en el sistema anterior, puede migrarlos:

1. Exporte los datos de la base de datos antigua (`almacenequiposinternet`)
2. Use el script de migración (en desarrollo) o importe manualmente:
   ```sql
   INSERT INTO almacen_toi.equipos (nombre, numero_serie_mac, modelo, marca, fecha_entrada, estado, calidad, observaciones)
   SELECT nombre, numero_serie_mac, modelo, marca, fecha_entrada, estado, calidad, observaciones
   FROM almacenequiposinternet.equipos;
   
   INSERT INTO almacen_toi.accesorios (nombre, cantidad, modelo, marca, fecha_entrada, estado, calidad, observaciones)
   SELECT nombre, cantidad, modelo, marca, fecha_entrada, estado, calidad, observaciones
   FROM almacenequiposinternet.accesorios;
   ```

## 🛠️ Funcionalidades

### Dashboard
- Estadísticas en tiempo real
- Accesos rápidos
- Últimos movimientos
- Alertas de stock bajo

### Equipos
- Alta, baja y modificación
- Búsqueda por múltiples criterios
- Filtro por estado
- Historial de movimientos

### Accesorios
- Control de stock
- Alertas de reposición
- Agrupación por tipo
- Movimientos con cantidades

### Movimientos
- Registro de salidas y entradas
- Actualización automática de estados
- Historial completo
- Búsqueda y filtrado

## 🔒 Seguridad

### Mejoras de Seguridad Implementadas

1. **Contraseñas hasheadas** con bcrypt
2. **Prepared statements** para todas las consultas SQL
3. **Sanitización** de entradas de usuario
4. **Escape de salidas** para prevenir XSS
5. **Validación de sesiones** en cada página
6. **Tokens CSRF** (en implementación)

### Recomendaciones Adicionales

1. Cambie las credenciales por defecto inmediatamente
2. Use HTTPS en producción
3. Realice copias de seguridad periódicas
4. Mantenga PHP y PostgreSQL actualizados
5. Restrinja el acceso por IP si es posible

## 📝 Notas de la Versión

### Cambios desde el Sistema Antiguo

| Característica | Sistema Antiguo | Nuevo Sistema |
|---------------|-----------------|---------------|
| Arquitectura | Código espagueti | MVC |
| Seguridad | SQL Injection vulnerable | PDO preparado |
| Contraseñas | Texto plano | Hash bcrypt |
| UI | HTML mezclado | Bootstrap 5 |
| Base de datos | Sin FK consistentes | Integridad referencial |
| Sesiones | Hardcodeadas | Sistema seguro |

## 🤝 Soporte

Para reportar errores o solicitar nuevas funcionalidades, contacte al equipo de desarrollo.

## 📄 Licencia

Sistema desarrollado para uso interno de TOI.

---

**Desarrollado con ❤️ para TOI**
*Telecomunicaciones Oportunas Inteligentes*
