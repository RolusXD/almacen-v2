# SPECIFICATION: Sistema de Almacén TOI v4.0 (Node.js Edition)
<!-- Spec Kit Specification Document -->

**Identificador:** `SPEC-001`  
**Estado:** `Aprobado`  
**Fecha:** Noviembre 2026  
**Autor:** Equipo de Ingeniería & Almacén TOI  

---

## 1. Resumen Ejecutivo
Modernización integral del Sistema de Gestión de Almacén TOI para telecomunicaciones. La versión actual en PHP monolítico presenta colisiones en la hoja de estilos (tablas recortadas, botones invisibles), errores en el registro de tipos y movimientos de accesorios, y carece de contenerización limpia. Esta especificación define la reimplementación del sistema sobre **Node.js + TypeScript**, preservando al 100% el esquema y los datos existentes de PostgreSQL 14, con una interfaz densa y sobria inspirada en MikroWisp, lista para despliegue automatizado en **Coolify**.

---

## 2. Actores y Roles

| Rol | Descripción | Permisos Clave |
|---|---|---|
| **Administrador (`admin`)** | Supervisor general del almacén y sistemas | Acceso total: CRUD usuarios, gestión de maestros (proveedores, tipos, categorías, marcas), auditoría de movimientos, reportes avanzados. |
| **Operador (`usuario`)** | Personal de almacén y logística de campo | Registro y consulta de inventario (equipos y accesorios), registro de movimientos (egresos/ingresos), exportación de listados. |

---

## 3. Requerimientos Funcionales por Módulo

### 3.1. Autenticación y Sesiones
- **REQ-AUTH-01**: Inicio de sesión mediante usuario y contraseña con verificación contra los hashes bcrypt existentes en la tabla `usuarios`.
- **REQ-AUTH-02**: Usuario administrador por defecto activo: `admin` / `Admin123!`.
- **REQ-AUTH-03**: Manejo de sesiones seguras con soporte para almacenamiento en PostgreSQL (`app_sessions`) o sesión firmada resistente a reinicios en Coolify.
- **REQ-AUTH-04**: Pantalla de login limpia, independiente, sin elementos residuales del sidebar ni navbar.

### 3.2. Módulo de Equipos
- **REQ-EQ-01**: Listado completo de equipos con DataTables enriquecido (ordenación, búsqueda, selector de páginas 15/25/50/100, filtrado por estado y código).
- **REQ-EQ-02**: Alta de equipo:
  - Campos: Serie/MAC (única), Código tipo equipo, Nombre, Marca, Modelo, Precio (Bs.), Proveedor, Fecha ingreso, Calidad, Observaciones.
  - **Regla Estricta (Observación Almacén)**: El campo `calidad` debe ser estrictamente `NUEVO` y estar bloqueado para evitar selección de otras opciones.
  - El estado inicial de todo equipo nuevo es automáticamente `DISPONIBLE`.
- **REQ-EQ-03**: Edición de equipo permitiendo actualizar marca, modelo, precio, observaciones y asignaciones sin alterar la integridad referencial.
- **REQ-EQ-04**: Importación y exportación masiva en CSV y Excel.
- **REQ-EQ-05**: Búsqueda autocompletable por modelo registrado para rellenar automáticamente marca, descripción, código y proveedor.

### 3.3. Módulo de Accesorios y Materiales
- **REQ-ACC-01**: Listado con control visual de existencias, cálculo de valor de inventario y alertas visuales cuando el stock sea inferior a 10 unidades.
- **REQ-ACC-02**: Alta de accesorio:
  - Campos: Nombre, Código, Cantidad inicial, Unidad de medida (METROS, UNIDADES, CAJAS, KILOS, etc.), Precio unitario, Proveedor, Calidad (`NUEVO` por defecto y bloqueado), Observaciones.
- **REQ-ACC-03**: Sincronización automática de existencias con los movimientos (triggers o transacciones atómicas).

### 3.4. Módulo de Movimientos (Corrección de Fallas Críticas)
- **REQ-MOV-01 (Equipos)**:
  - Registro de movimiento con selección del equipo mediante búsqueda en tiempo real por Número de Serie o MAC.
  - Al seleccionar la serie, la interfaz debe mostrar inmediatamente la tarjeta de confirmación del equipo: Descripción, Código, Modelo, Marca y Estado actual.
  - Campos de movimiento obligatorios: Tipo (`SALIDA`, `ENTRADA`, `MANTENIMIENTO`, `REVISION`), Destino (obligatorio en `SALIDA`: `INSTALACIONES`, `INFRAESTRUCTURA`, `MANTENIMIENTO RED`, `SOPORTE TECNICO`, etc.), Asunto (`RCA`, `INSTALACION`, `MANTENIMIENTO`, etc.), Encargado, Fecha y Observaciones.
  - Al completar la `SALIDA`, el equipo pasa automáticamente a estado `NO DISPONIBLE` / `ASIGNADO`. Al registrar `ENTRADA`, vuelve a `DISPONIBLE`.
- **REQ-MOV-02 (Accesorios - Falla corregida)**:
  - Registro con selección del accesorio disponible con su stock visible.
  - Campo **Destino** completamente implementado en el formulario y persistido en la columna `destino` de la base de datos.
  - Validación de stock: en movimientos de `SALIDA`, el formulario debe bloquear envíos que excedan la cantidad disponible actual.
  - Tipos soportados: `SALIDA` (egreso/descuento de stock) y `ENTRADA` (ingreso/aumento de stock).

### 3.5. Módulos Maestros (Gestión)
- **REQ-GEST-01**: **Tipos de Equipos**: CRUD completo sin el error de clave primaria (`id_codigo` autogenerado o secuencial).
- **REQ-GEST-02**: **Proveedores**: Gestión con campo `nit` (específico para normativa de Bolivia), nombre de empresa, contacto, teléfono, correo y dirección.
- **REQ-GEST-03**: **Marcas y Categorías**: Vistas dedicadas e independientes (resolviendo la duplicidad donde marcas incluía la vista de categorías).
- **REQ-GEST-04**: **Mantenimientos y Préstamos**: Registro y control de fechas de retorno y equipos en taller.

### 3.6. Reportes y Exportación
- **REQ-REP-01**: Exportación de listados a Excel (`.xlsx`) y CSV nativo en todos los módulos de inventario y movimientos.
- **REQ-REP-02**: Generación de reportes PDF limpios (sin dependencias fallidas de TCPDF) generados directamente en el servidor Node.js o imprimibles con diseño optimizado.

---

## 4. Requerimientos de Interfaz de Usuario (UI/UX)
- **REQ-UI-01**: Inspirado en la interfaz MikroWisp (`almacen/fallas/modelo mas sobrio.png`):
  - Navbar superior minimalista con breadcrumbs y perfil.
  - Sidebar izquierdo colapsable oscuro con iconos definidos y submenús expandibles fluidos.
  - Tablas compactas con estilos uniformes: cabeceras en mayúsculas sutiles, bordes tenues, badges redondeados para estados (`DISPONIBLE` verde, `ASIGNADO` ámbar, etc.).
- **REQ-UI-02**: Prohibido el corte horizontal de tablas. La tabla debe adaptarse al contenedor con scroll interno encapsulado si supera el ancho de pantalla, manteniendo visibles las acciones a la derecha.

---

## 5. Requerimientos No Funcionales y Despliegue en Coolify
- **REQ-NFR-01**: **Rendimiento**: Tiempo de respuesta < 100ms para consultas estándar; arranque del contenedor en < 5 segundos.
- **REQ-NFR-02**: **Compatibilidad de Base de Datos**: Conexión con PostgreSQL 14+ usando el esquema existente en `backup_almacen_toi20261114.sql`.
- **REQ-NFR-03**: **Despliegue Coolify**:
  - `Dockerfile` con Node.js 22 LTS (Alpine/Slim).
  - `docker-compose.yml` con servicios `app` y `postgres`, volumen persistente y script de carga de base de datos en primera ejecución.
  - Variables de entorno centralizadas vía `.env.example`.
