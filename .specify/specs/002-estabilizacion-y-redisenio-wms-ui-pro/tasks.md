# TASKS & ITERATIONS: Estabilización de Flujos y Rediseño WMS UI Pro
<!-- Spec Kit Tasks and Work Breakdown Structure: TASKS-002 -->

**Identificador:** `TASKS-002`  
**Referencia:** `SPEC-002` / `PLAN-002`  
**Skill de Referencia:** `.agents/skills/wms-ui-pro/SKILL.md`  
**Rama de Trabajo:** `feature/modernizacion-nodejs`  
**Estado:** ✅ **Completado y Verificado**  

---

## Fases e Iteraciones de Trabajo

### Fase 1: Estabilización Backend y Correcciones de Esquema
- [x] **TASK-STAB-01**: Corregir la consulta en `MaestroRepository.getCategorias()` reemplazando `accesorios.id_tipo_accesorio` por `accesorios.id_categoria`.
- [x] **TASK-STAB-02**: Adaptar `AccesorioRepository.create` para asegurar que `fecha_entrada` nunca sea nulo, asignando `CURRENT_DATE` por defecto.
- [x] **TASK-STAB-03**: Refactorizar modelo y repositorio de `Mantenimientos` (`tipo_mantenimiento`, `descripcion`, `costo`, `tecnico_responsable`, `fecha_ingreso`, `estado`).
- [x] **TASK-STAB-04**: Refactorizar modelo y repositorio de `Prestamos` (`nombre_solicita`, `motivo_prestamo`, `fecha_prestamo`, `fecha_devolucion_esperada`, `observaciones_salida`).
- [x] **TASK-STAB-05**: Normalizar estados de equipos al valor canónico de PostgreSQL `'OCUPADO'` en lugar de `'ASIGNADO'`.
- [x] **TASK-STAB-06**: Crear middleware centralizado de manejo de errores con vista EJS consistente en lugar de respuestas `text/plain`.

### Fase 2: Implementación del Sistema de Diseño WMS UI Pro en CSS
- [x] **TASK-UI-01**: Refactorizar `src/public/css/mikrowisp-style.css` implementando la paleta Clean Industrial (`#F8FAFC`, `#FFFFFF`, `#0F172A`, `#E2E8F0`).
- [x] **TASK-UI-02**: Implementar clases de badges semánticos de inventario (`.badge-wms-optimo`, `.badge-wms-bajo`, `.badge-wms-critico`, `.badge-wms-transito`).
- [x] **TASK-UI-03**: Implementar utilidades tipográficas: `.tabular-nums` (`font-variant-numeric: tabular-nums`) y `.font-mono` para SKUs y códigos con botón de copiado.
- [x] **TASK-UI-04**: Implementar clases de tabla de alto rendimiento con encabezados fijos (`.mw-table thead th` sticky top-0) y alineación estricta de columnas.
- [x] **TASK-UI-05**: Ajustar responsividad y scroll horizontal contenido en `.mw-table-container` para tablets y monitores.

### Fase 3: Rediseño de Vistas y Layouts
- [x] **TASK-VIEW-01**: Rediseñar Layouts principales (`header.ejs`, `sidebar.ejs`, `footer.ejs`) con navegación limpia y atajo rápido de búsqueda `Ctrl + K`.
- [x] **TASK-VIEW-02**: Reestructurar el **Dashboard** (`src/views/dashboard/index.ejs`) con exactamente 4 tarjetas KPI operativas y tabla de últimos movimientos.
- [x] **TASK-VIEW-03**: Rediseñar la vista de **Categorías** (`src/views/maestros/categorias.ejs`) para confirmar resolución del error 500 y mostrar datos limpios.
- [x] **TASK-VIEW-04**: Rediseñar las vistas de **Inventario** (`equipos/index.ejs`, `accesorios/index.ejs`) con números tabulares y badges de stock.
- [x] **TASK-VIEW-05**: Optimizar las vistas de **Movimientos** (`movimientos/equipos.ejs`, `movimientos/accesorios.ejs`) con validación inmediata de stock y autofocus.
- [x] **TASK-VIEW-06**: Actualizar vistas de **Mantenimientos** y **Préstamos** para sincronizarse con los campos reales de la base de datos.

### Fase 4: Compilación, Despliegue en Contenedor y Verificación
- [x] **TASK-VERIF-01**: Ejecutar `npm run build` y verificar que no existan errores de compilación TypeScript.
- [x] **TASK-VERIF-02**: Reconstruir y reiniciar el contenedor Docker `toi_almacen_app`.
- [x] **TASK-VERIF-03**: Ejecutar suite de pruebas con `curl` verificando HTTP 200 en `/maestros/categorias`, `/dashboard`, `/equipos`, `/accesorios`.
- [x] **TASK-VERIF-04**: Validar visualmente en navegador la ergonomía WMS y ausencia de scroll horizontal no deseado.
- [x] **TASK-VERIF-05**: Hacer commit de los cambios en la rama `feature/modernizacion-nodejs`.
