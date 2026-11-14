# TASKS & ITERATIONS: Sistema de Almacén TOI v4.0 (Node.js)
<!-- Spec Kit Tasks and Work Breakdown Structure -->

**Identificador:** `TASKS-001`  
**Referencia:** `SPEC-001` / `PLAN-001`  
**Rama de Trabajo:** `feature/modernizacion-nodejs`  
**Estado:** ✅ **Completado y Verificado**  

---

## Estado de Ejecución por Iteraciones

### Iteración 0: Gobernanza Spec Kit y Scaffolding Base
- [x] **TASK-001**: Crear snapshot seguro del sistema legado PHP y volcado SQL en rama `main`.
- [x] **TASK-002**: Crear y posicionar el entorno de trabajo en la rama `feature/modernizacion-nodejs`.
- [x] **TASK-003**: Establecer la gobernanza Spec Kit: `constitution.md`, `spec.md`, `plan.md`, `tasks.md`.
- [x] **TASK-004**: Inicializar proyecto Node.js (`package.json`, `tsconfig.json`, instalación de dependencias `npm install`).

### Iteración 1: Capa de Base de Datos y Autenticación
- [x] **TASK-101**: Implementar `src/db/index.ts` con pool de conexiones `pg` resiliente compatible con `DATABASE_URL` y variables desglosadas.
- [x] **TASK-102**: Crear repositorio de usuarios `usuario.repository.ts` con soporte para verificación de contraseñas hasheadas en bcrypt.
- [x] **TASK-103**: Configurar middleware de sesiones respaldadas en la tabla `app_sessions` de PostgreSQL.
- [x] **TASK-104**: Implementar controlador y rutas de login (`/auth/login`, `/auth/logout`) con redirección segura y pantalla de login limpia e independiente.
- [x] **TASK-105**: Validar acceso con usuario administrador predeterminado (`admin` / `Admin123!`).

### Iteración 2: UI Sobria MikroWisp y Dashboard
- [x] **TASK-201**: Construir la hoja de estilos unificada `src/public/css/mikrowisp-style.css`:
  - Paleta sobria (azul marino oscuro `#1e293b`, fondo `#f1f5f9`, tarjetas `#ffffff`).
  - Clases compactas para tablas (tamaño 12.5px, padding fino, cabeceras en mayúsculas sutiles).
  - Badges suaves para estados (`DISPONIBLE`, `ASIGNADO`, `MANTENIMIENTO`, `REVISION`).
  - Cero desbordes horizontales: estructura fluida y contenida sin scripts agresivos de resize.
- [x] **TASK-202**: Crear layouts modulares en EJS: `header.ejs`, `sidebar.ejs`, `footer.ejs`.
- [x] **TASK-203**: Implementar sidebar colapsable nativo, con soporte para submenús desplegables y perfil de usuario compacto.
- [x] **TASK-204**: Implementar Dashboard con tarjetas estadísticas en tiempo real y accesos rápidos a operaciones críticas.

### Iteración 3: Módulos de Inventario (Equipos y Accesorios)
- [x] **TASK-301**: Implementar CRUD de **Equipos**:
  - Repositorio con consultas optimizadas a `equipos` y `tipos_equipos`.
  - Listado con tablas compactas y filtros rápidos por código y estado.
  - Formulario de alta con **campo Calidad fijado estrictamente en `NUEVO` y bloqueado** (requisito explícito del almacén).
  - Autocompletado inteligente por modelo registrado.
- [x] **TASK-302**: Implementar CRUD de **Accesorios y Materiales**:
  - Listado con cálculo de existencias y alertas visuales de stock bajo (< 10).
  - Formulario con calidad fijada en `NUEVO` y selector de unidades de medida estándar.
- [x] **TASK-303**: Funciones de exportación Excel (.xlsx) nativas con `exceljs`.

### Iteración 4: Módulo de Movimientos (Resolución de Errores Reportados)
- [x] **TASK-401**: Implementar **Movimientos de Equipos**:
  - Endpoint de autocompletado y búsqueda en tiempo real por Número de Serie o MAC.
  - Tarjeta de vista previa inmediata que muestra: Descripción, Código, Marca, Modelo y Estado actual.
  - Formulario con tipos: `SALIDA`, `ENTRADA`, `MANTENIMIENTO`, `REVISION`.
  - Campo Destino obligatorio en salidas y actualización automática del estado del equipo.
- [x] **TASK-402**: Implementar **Movimientos de Accesorios**:
  - Selector de accesorio con stock actual visible.
  - **Campo Destino completamente integrado y persistido en `movimientos_accesorios.destino`**.
  - Validación de cantidad contra stock real para impedir egresos negativos.
  - Sincronización automática de existencias.

### Iteración 5: Módulos Maestros y Generador de Reportes
- [x] **TASK-501**: Implementar CRUD de **Tipos de Equipos** (resolviendo el fallo de inserción de `id_codigo` mediante secuencias o identidades).
- [x] **TASK-502**: Implementar CRUD de **Proveedores** con soporte para el campo `nit` de Bolivia.
- [x] **TASK-503**: Implementar CRUD independiente para **Marcas** y **Categorías** (eliminando la vista combinada rota).
- [x] **TASK-504**: Implementar módulos de **Mantenimientos** y **Préstamos**.
- [x] **TASK-505**: Implementar generador de reportes en Excel (`.xlsx`) sin dependencias externas del sistema.

### Iteración 6: Empaquetado Docker y Preparación para Coolify
- [x] **TASK-601**: Crear `Dockerfile` multi-etapa optimizado para producción Node.js.
- [x] **TASK-602**: Crear `docker-compose.yml` para Coolify con persistencia de base de datos y montaje automático del volcado SQL.
- [x] **TASK-603**: Crear `.env.example`, `.dockerignore` y guía detallada `COOLIFY_DEPLOYMENT_GUIDE.md`.
- [x] **TASK-604**: Pruebas de compilación TypeScript (`npm run build`) verificadas con 0 errores.
