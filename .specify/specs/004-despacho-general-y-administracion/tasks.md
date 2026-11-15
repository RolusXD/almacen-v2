# TASKS: Despacho General Unificado, Transaccionalidad ACID y Administración Centralizada
<!-- Spec Kit Tasks Document: SPEC-004 -->

**Especificación:** [`SPEC-004`](file:///c:/Users/raul1/Desktop/soporte/proyectos%20desarrollo/almacen%20v2/.specify/specs/004-despacho-general-y-administracion/spec.md)  
**Plan Técnico:** [`plan.md`](file:///c:/Users/raul1/Desktop/soporte/proyectos%20desarrollo/almacen%20v2/.specify/specs/004-despacho-general-y-administracion/plan.md)  
**Skills Aplicadas:** `.agents/skills/wms-ui-pro/SKILL.md` y `.agents/skills/nodejs-security-audit/SKILL.md`  

---

## FASE 1: Modelo de Datos, Migración SQL y Tipos TypeScript

- [x] **TASK-DB-01**: Crear el archivo de migración `src/db/migrations/004_despacho_and_admin.sql` con las tablas:
  - `ordenes_despacho` e índices por fecha, created_by y número correlativo.
  - `ordenes_despacho_detalles` con discriminación de ítem (`EQUIPO` / `ACCESORIO`), series y cantidades.
  - `configuracion_general` con parámetros institucionales, zona horaria y stock mínimo por defecto.
  - `auditoria_logs` con índices optimizados por fecha, módulo, acción y usuario.
  - Alteración de `movimientos_equipos` y `movimientos_accesorios` agregando columnas `id_orden` y `numero_orden`.
- [x] **TASK-DB-02**: Ejecutar la migración SQL contra el contenedor PostgreSQL `toi_almacen_db` y validar la estructura de tablas.
- [x] **TASK-DB-03**: Actualizar `src/models/types.ts` con las interfaces TypeScript correspondientes:
  - `OrdenDespacho`, `OrdenDespachoDetalle`, `ConfiguracionGeneral`, `AuditoriaLog`, `ItemCarritoDespacho`.

---

## FASE 2: Configuración General del Sistema y Control de Zona Horaria

- [x] **TASK-CFG-01**: Crear `src/repositories/configuracion.repository.ts` con métodos para consultar y actualizar la tabla `configuracion_general`.
- [x] **TASK-CFG-02**: Implementar el formateador de fechas y zona horaria en `src/utils/date.helper.ts` asegurando que el proceso Node.js y las respuestas adopten `America/La_Paz` (UTC-4) por defecto.
- [x] **TASK-CFG-03**: Ampliar `src/controllers/config.controller.ts` con los métodos `generalIndex` y `generalSubmit`, protegidos con `requireAdmin`.
- [x] **TASK-CFG-04**: Diseñar la vista `src/views/administracion/configuracion.ejs` en AdminLTE 4:
  - Selector de Zona Horaria (lista estándar IANA).
  - Datos institucionales (Nombre de empresa, NIT, dirección, teléfono, correo, pie de firma en reportes).
  - Parámetros operativos (Stock mínimo global por defecto, lista de correos para alertas de stock bajo).
  - Clases semánticas adaptativas (`bg-body`, `bg-body-tertiary`, `text-body`).

---

## FASE 3: Servicio de Auditoría y Bitácora de Eventos

- [x] **TASK-AUD-01**: Crear el servicio `src/services/audit.service.ts` con el método estático `log(req, { modulo, accion, detalle })` que capture IP del cliente, usuario en sesión y fecha/hora con precisión milimétrica.
- [x] **TASK-AUD-02**: Instrumentar el registro de eventos en los controladores existentes:
  - `AuthController`: Inicios de sesión exitosos (`LOGIN_SUCCESS`) y fallidos (`LOGIN_FAILED`), cierres de sesión (`LOGOUT`).
  - `UsuariosController`: Altas, ediciones de perfil, cambios de clave y conmutación de estado activo.
  - `ConfigController`: Actualizaciones de configuración SMTP y Configuración General.
  - `MaestrosController`: Creación y eliminación de categorías, marcas, proveedores y tipos de equipo.
- [x] **TASK-AUD-03**: Desarrollar la vista `src/views/administracion/logs.ejs` con tabla a ancho 100% (`col-12`), paginación y toolbar con filtros por fechas, usuario, módulo y tipo de acción.
- [x] **TASK-AUD-04**: Crear el endpoint `/administracion/logs/exportar/excel` en `ConfigController` utilizando `exceljs` para exportar la bitácora filtrada a `.xlsx`.

---

## FASE 4: Módulo de Despacho General (Orden de Salida Unificada)

- [x] **TASK-DSP-01**: Crear el repositorio `src/repositories/despacho.repository.ts` implementando la transacción atómica `crearDespachoTransaccional()`:
  - Generación de correlativo único `DSP-YYYYMMDD-XXXX`.
  - Bloqueo y validación de disponibilidad de equipos (`FOR UPDATE`).
  - Bloqueo y validación de saldo disponible en accesorios (`FOR UPDATE`).
  - Inserción en `ordenes_despacho` y `ordenes_despacho_detalles`.
  - Inserción sincronizada en `movimientos_equipos` y `movimientos_accesorios` vinculando `id_orden`.
  - Actualización de estado de equipos a `'OCUPADO'` y descuento de stock de accesorios.
  - Disparo de alerta por correo si el stock resultante es `<= COALESCE(stock_minimo, 10)`.
  - Registro de auditoría con la acción `DESPACHO_CONFIRMADO`.
- [x] **TASK-DSP-02**: Desarrollar `src/controllers/despacho.controller.ts`:
  - `nuevoForm`: Provee listas de equipos disponibles y accesorios en stock para el constructor interactivo.
  - `confirmarSubmit`: Valida payload y ejecuta la transacción ACID.
  - `index`: Lista las órdenes emitidas aplicando filtro Anti-IDOR (Admin ve todas; Operador solo las propias).
  - `apiDetalle`: Retorna el desglose de una orden en JSON para previsualización en modal flotante.
  - `anular`: Transacción reversiva para anular órdenes (exclusivo Admin).
- [x] **TASK-DSP-03**: Diseñar la vista `src/views/despachos/nuevo.ejs`:
  - Cabecera: Destino, encargado receptor, asunto y observaciones.
  - Buscador de equipos por Serie/MAC con autocompletado y validación de disponibilidad.
  - Selector de accesorios con contador de stock en tiempo real y bloqueo si la cantidad supera el stock.
  - Carrito interactivo de despacho: tabla de ítems agregados con badges semánticos, series en `font-mono`, cantidades tabulares y botón de eliminar fila.
  - Botón primario de confirmación: `Emitir Orden de Despacho`.
- [x] **TASK-DSP-04**: Diseñar la vista `src/views/despachos/index.ejs`:
  - Tabla a ancho completo (`col-12`) con historial de despachos.
  - Modal flotante centrado (`modal-lg modal-dialog-centered`) para previsualizar el detalle de la orden.
  - Botón de reimpresión rápida de comprobante PDF.

---

## FASE 5: Comprobante de Despacho Unificado en PDF

- [x] **TASK-PDF-01**: Desarrollar en `src/services/pdf.service.ts` el método `generarComprobanteDespachoUnificado()`:
  - Membrete dinámico cargando los datos corporativos de `configuracion_general`.
  - Número de orden correlativo, fecha y hora en zona horaria boliviana (`America/La_Paz`), destino y encargado.
  - Sección consolidada de Equipos (código, marca, modelo, serie/MAC en fuente monoespaciada).
  - Sección consolidada de Materiales y Accesorios (descripción, unidad de medida, cantidad en números tabulares).
  - Recuadros formales de firma: *"Entregado por (Almacén)"* y *"Recibido Conforme (Cuadrilla / Proyecto)"*.
- [x] **TASK-PDF-02**: Conectar el endpoint `GET /despachos/comprobante/:id` con validación estricta Anti-IDOR (403 Forbidden para usuarios que intenten descargar comprobantes de otros operadores).
- [x] **TASK-PDF-03**: Integrar en la alerta flash de éxito tras emitir una orden el botón de descarga directa del PDF.

---

## FASE 6: Reestructuración de Menú, Terminología y Pulido Visual

- [x] **TASK-UI-01**: Unificar la terminología en toda la aplicación:
  - Reemplazar "Mover / Movimientos" por "Despachar / Despachos" en menús, botones, alertas y títulos.
- [x] **TASK-UI-02**: Reestructurar el menú lateral en `src/views/layouts/sidebar.ejs`:
  - Agrupador **"Despacho y Operaciones"**:
    - `+ Nuevo Despacho General` (`/despachos/nuevo`)
    - `Historial de Despachos` (`/despachos`)
    - `Despacho de Equipos` (`/movimientos/equipos`)
    - `Despacho de Materiales` (`/movimientos/accesorios`)
    - `Kardex y Reportes` (`/reportes`)
  - Agrupador **"Administración"** (solo administradores):
    - `Gestión de Usuarios` (`/usuarios`)
    - `Configuración del Sistema` (`/administracion/configuracion`)
    - `Servidor SMTP` (`/configuracion/smtp`)
    - `Logs y Auditoría` (`/administracion/logs`)
- [x] **TASK-UI-03**: Auditar el contraste y responsividad de todas las nuevas vistas en los 3 temas (Modo Claro, Modo Noche y Alto Contraste) eliminando cualquier color hardcodeado.
- [x] **TASK-UI-04**: Ejecutar la batería de pruebas de validación automatizada (Atomicidad ACID, Anti-IDOR, Formato PDF, Zona Horaria, Logs de Auditoría y Rate Limiting).
