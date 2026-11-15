# SPECIFICATION: Estabilización de Flujos y Rediseño Operativo WMS UI Pro
<!-- Spec Kit Specification Document: SPEC-002 -->

**Identificador:** `SPEC-002`  
**Estado:** `Aprobado`  
**Fecha:** Noviembre 2026  
**Skill de Referencia:** `.agents/skills/wms-ui-pro/SKILL.md`  
**Base Técnica:** Node.js 22 LTS / Express / PostgreSQL 14+ / EJS / WMS Clean Industrial UI  

---

## 1. Resumen Ejecutivo y Motivación

Tras la migración inicial del sistema de almacén desde PHP hacia Node.js/TypeScript, se identificaron dos necesidades críticas para alcanzar la madurez productiva en bodega y despacho:

1. **Estabilización Funcional y Corrección de Esquema**: Se detectaron fallas internas heredadas de la base de datos de PostgreSQL (como el error 500 en *"Gestión Maestra > Categorías"* provocado por una columna inexistente `id_tipo_accesorio`, restricciones `NOT NULL` sin valor por defecto en `accesorios.fecha_entrada`, desajustes en las columnas de `mantenimientos` y `prestamos`, y mapeo de estados con check constraints como `chk_equipos_estado`).
2. **Transformación Visual y Operativa (WMS UI Pro)**: La interfaz actual carece de la ergonomía, densidad y convenciones de visualización requeridas para trabajo intensivo de almacén. Se exige aplicar obligatoriamente la skill `.agents/skills/wms-ui-pro/SKILL.md` para instaurar un diseño **Clean Industrial / Linear-inspired**, con paleta descansada (`#F8FAFC` / `#FFFFFF`), badges semánticos de inventario, números tabulares alineados a la derecha (`tabular-nums`), códigos/series en fuente monoespaciada (`font-mono`), cabeceras pegajosas (`sticky top-0`), un dashboard reestructurado a exactamente 4 KPIs esenciales y adaptabilidad total para tablets y computadoras de escritorio.

---

## 2. Historias de Usuario (User Stories)

### US-1: Operador de Almacén - Consulta y Movimientos sin Fricción
* **Como** operador de almacén en turno de recepción o despacho,  
* **Quiero** registrar ingresos y salidas de equipos y materiales con validación en tiempo real de stock, selección de destino y lectura clara de series y cantidades,  
* **Para** completar las transferencias en menos de 10 segundos por ítem sin errores humanos ni bloqueos del sistema.

### US-2: Jefe de Almacén / Auditor - Control de Stock Crítico y Dashboard
* **Como** jefe de almacén y supervisor de logística,  
* **Quiero** acceder a un Dashboard operativo con exactamente 4 métricas clave y acceso directo a los ítems en estado crítico o agotado,  
* **Para** tomar decisiones de reabastecimiento inmediatas y auditar los movimientos del día sin saturarme de información irrelevante.

### US-3: Administrador de Sistemas - Gestión Maestra Robusta y Cero Errores 500
* **Como** administrador del sistema,  
* **Quiero** gestionar Categorías, Proveedores (con NIT), Tipos de Equipos y Marcas sin que ninguna consulta falle por discrepancias de columnas o claves foráneas,  
* **Para** garantizar la integridad referencial de los catálogos y la continuidad operativa sin caídas de servidor.

### US-4: Operador en Bodega - Ergonomía Visual en Tablet o Monitor de Alta Densidad
* **Como** usuario que pasa jornadas de más de 8 horas frente a la pantalla de almacén,  
* **Quiero** una interfaz con paleta neutra antifatiga, números alineados verticalmente (`tabular-nums`), series legibles en fuente monoespaciada y tablas con cabecera fija que nunca se desborden ni corten los botones de acción,  
* **Para** mantener una alta precisión de lectura visual sin agotamiento y poder utilizar la interfaz en tablets con botones táctiles amplios.

---

## 3. Alcance 1: Estabilización Backend y Auditoría de Flujos

### 3.1. Corrección Crítica: Módulo Categorías (Gestión Maestra)
- **REQ-STAB-01**: Corregir de forma inmediata la consulta en `MaestroRepository.getCategorias()`:
  - **Causa raíz detectada**: La consulta SQL intentaba referenciar la columna inexistente `accesorios.id_tipo_accesorio`. La clave foránea real en la base de datos es `accesorios.id_categoria`.
  - **Corrección obligatoria**:
    ```sql
    SELECT c.*,
      (SELECT COUNT(*) FROM equipos WHERE id_categoria = c.id_categoria) as total_equipos,
      (SELECT COUNT(*) FROM accesorios WHERE id_categoria = c.id_categoria) as total_accesorios
    FROM categorias c
    WHERE c.activo = 1
    ORDER BY c.nombre ASC;
    ```
  - **Validación**: La ruta `/maestros/categorias` debe responder con código HTTP 200, mostrando las 7 categorías reales existentes (`Router`, `Switch`, `ONT`, `Cable`, `Conector`, `Accesorio`, `ANTENA CLIENTE`) con sus respectivos conteos.

### 3.2. Auditoría y Corrección de Esquema en Accesorios
- **REQ-STAB-02**: En la tabla `accesorios`, la columna `fecha_entrada` tiene restricción `NOT NULL` sin valor predeterminado.
  - El repositorio `AccesorioRepository.create` y el formulario `accesorios/form.ejs` deben incluir y validar el campo `fecha_entrada` (por defecto la fecha actual `CURRENT_DATE`), evitando que cualquier alta de accesorio falle con error de base de datos.

### 3.3. Auditoría y Corrección de Esquema en Mantenimientos
- **REQ-STAB-03**: En la tabla `mantenimientos`, las columnas reales en PostgreSQL son:
  - `tipo_mantenimiento` (con check constraint: `PREVENTIVO`, `CORRECTIVO`, `EVOLUTIVO`).
  - `descripcion` (texto obligatorio `NOT NULL`).
  - `tecnico_responsable` (`NOT NULL`).
  - `fecha_ingreso` (`NOT NULL`).
  - `costo` (numérico, no `costo_reparacion`).
  - `estado` (check constraint: `EN_PROCESO`, `COMPLETADO`, `CANCELADO`).
  - **Alineación**: Refactorizar `MaestroRepository.createMantenimiento`, `src/models/types.ts` y la vista `mantenimientos.ejs` para alinearse 100% con este esquema y sus restricciones `CHECK`.

### 3.4. Auditoría y Corrección de Esquema en Préstamos
- **REQ-STAB-04**: En la tabla `prestamos`, las columnas reales en PostgreSQL son:
  - `nombre_solicita` (no `solicitante`, campo `NOT NULL`).
  - `motivo_prestamo` (no `motivo`, campo `NOT NULL`).
  - `fecha_prestamo` (`NOT NULL`).
  - `fecha_devolucion_esperada` (`NOT NULL`).
  - `observaciones_salida` (no `observaciones`).
  - `estado` (check constraint: `PREVENTIVO` -> `PRESTADO`, `DEVUELTO`, `ATRASADO`, `PERDIDO`).
  - **Alineación**: Refactorizar `MaestroRepository.createPrestamo`, interfaces y vista `prestamos.ejs` garantizando inserciones limpias sin violaciones de integridad.

### 3.5. Compatibilidad de Estados de Equipos
- **REQ-STAB-05**: En la tabla `equipos`, la restricción `chk_equipos_estado` restringe los valores a:
  `'DISPONIBLE'`, `'OCUPADO'`, `'MANTENIMIENTO'`, `'RETIRADO'`.
  - El trigger automático `sync_equipo_estado_from_movimiento` actualiza a `'OCUPADO'` cuando hay `SALIDA` y a `'DISPONIBLE'` cuando hay `ENTRADA`.
  - La aplicación debe usar internamente el valor canónico `'OCUPADO'` en lugar de `'ASIGNADO'`, mostrando visualmente la etiqueta operativa *"OCUPADO / EN SERVICIO"* con badge semántico.

### 3.6. Manejo Global de Errores y Páginas Informativas
- **REQ-STAB-06**: Reemplazar respuestas de error en texto plano (`res.status(500).send('Error...')`) por un middleware de error centralizado que renderice una vista visualmente consistente con el sistema de diseño, capturando trazas de depuración solo en entorno de desarrollo.

---

## 4. Alcance 2: Rediseño Visual Operativo (WMS UI Pro)

Todas las vistas deben cumplir con los estándares definidos en `.agents/skills/wms-ui-pro/SKILL.md`:

### 4.1. Filosofía Visual "Clean Industrial"
- **REQ-UI-01**: **Superficie y Contraste**:
  - Fondo de página en gris tenue antifatiga: `#F8FAFC`.
  - Contenedores y tarjetas operativas en blanco puro: `#FFFFFF`.
  - Bordes estructurados de `1px` en `#E2E8F0`.
  - Prohibidos degradados violetas o sombras difusas exageradas; utilizar sombras de ingeniería sutiles: `0 1px 2px 0 rgba(0, 0, 0, 0.05)`.
- **REQ-UI-02**: **Color Primario y Jerarquía**:
  - Color de acción y acento primario: Azul cobalto industrial / acero profundo (`#0F172A` o `#1D4ED8`).
  - Botones secundarios en superficie blanca con borde `#CBD5E1` y texto `#334155`.

### 4.2. Badges Semánticos de Inventario (Regla Estricta WMS)
- **REQ-UI-03**: Los estados de inventario deben aplicar exclusivamente la cuádrupla cromática de almacén:
  1. **Stock Óptimo / Disponible**:  
     Fondo `#DCFCE7` | Texto `#166534` | Borde `#BBF7D0`
  2. **Stock Bajo / Reorden / Advertencia**:  
     Fondo `#FEF3C7` | Texto `#92400E` | Borde `#FDE68A`
  3. **Agotado / Ocupado / Crítico / Retirado**:  
     Fondo `#FEE2E2` | Texto `#991B1B` | Borde `#FECACA`
  4. **En Tránsito / Mantenimiento / Revisión**:  
     Fondo `#E0F2FE` | Texto `#075985` | Borde `#BAE6FD`

### 4.3. Tipografía y Alineación Numérica
- **REQ-UI-04**: **Números Tabulares Obligatorios**:  
  Aplicar la propiedad CSS `font-variant-numeric: tabular-nums;` en toda celda que contenga cantidades, precios, fechas, porcentajes o conteos de inventario, asegurando alineación vertical perfecta en columnas numéricas.
- **REQ-UI-05**: **Códigos y SKUs Monoespaciados**:  
  Toda serie, dirección MAC, código de equipo o SKU de accesorio debe presentarse en fuente monoespaciada (`font-mono`, `ui-monospace`, `JetBrains Mono` o `Consolas`), con botón rápido para copiar al portapapeles.

### 4.4. Arquitectura de Tablas de Inventario
- **REQ-UI-06**: **Encabezados Fijos (`sticky top-0`)**:  
  Las cabeceras de tabla deben fijarse al hacer scroll vertical en listas densas, con fondo sólido `#F8FAFC` y línea divisoria inferior de `2px` en `#CBD5E1`.
- **REQ-UI-07**: **Alineación Estricta por Tipo de Dato**:
  - Textos y nombres descriptivos: **Izquierda**.
  - Cantidades, precios en Bs., stocks y totales: **Derecha**.
  - Badges de estado y fechas ISO: **Centrados o Izquierda coherente**.
  - Acciones operativas: **Derecha fija**.
- **REQ-UI-08**: **Acciones Rápidas por Fila**:  
  Cada fila debe incorporar accesos directos compactos con iconos claros (ej. botón de *Movimiento*, *Editar*, *Eliminar*), evitando menús anidados innecesarios.

### 4.5. Dashboard Operativo Reestructurado (Exactamente 4 KPIs)
- **REQ-UI-09**: La fila superior del Dashboard debe contener exactamente **4 tarjetas operativas primarias**:
  1. **Total de Equipos Registrados**: Conteo general y desglose de disponibles vs ocupados.
  2. **Stock Crítico de Materiales**: Ítems de accesorios con existencias `< 10`, destacada con enlace directo filtrado.
  3. **Movimientos Operativos (24h / Histórico)**: Registro de egresos vs ingresos.
  4. **Equipos en Mantenimiento / Taller**: Cantidad de equipos fuera de servicio con acceso a taller.
- Cada tarjeta debe contar con tipografía grande (`text-2xl font-bold`), etiqueta descriptiva superior y microindicador de contexto.

### 4.6. Formularios de Movimiento Optimizados para Bodega
- **REQ-UI-10**:
  - Enfoque automático (`autofocus`) en el campo de búsqueda de Serie/MAC para facilitar lectores de código de barras.
  - Validación visual inmediata: Bloqueo de envío y mensaje en rojo si la salida de accesorio supera el stock en bodega.
  - Altura mínima de controles táctiles de `40px`–`44px` para compatibilidad con tablets industriales de almacén.

### 4.7. Navegación, Barra Superior y Responsividad
- **REQ-UI-11**:
  - Sidebar lateral colapsable con transiciones limpias y estructura organizada: *Dashboard*, *Inventario* (Equipos, Accesorios), *Operaciones* (Mover Equipos, Mover Accesorios), *Gestión Maestra* (Tipos, Proveedores, Categorías, Marcas), *Control* (Mantenimientos, Préstamos) y *Reportes*.
  - Barra superior con atajo visual de búsqueda rápida (`Ctrl + K` o `/`).
  - Contenedor con `overflow-x: auto` controlado para evitar desbordes de página en pantallas de cualquier resolución (cero scroll horizontal en la ventana principal).

---

## 5. Criterios de Aceptación (Gherkin / Test Scenarios)

### Escenario 1: Navegación exitosa al catálogo de Categorías
* **Dado** que un usuario autenticado accede al sistema,
* **Cuando** navega a la URL `/maestros/categorias` o hace clic en "Gestión Maestra > Categorías",
* **Entonces** el servidor debe responder con código HTTP 200 (sin error 500),
* **Y** la tabla debe mostrar las categorías existentes con las columnas NOMBRE, DESCRIPCIÓN, EQUIPOS VINCULADOS y ACCIONES,
* **Y** los conteos de equipos y accesorios vinculados deben reflejar la consulta correcta sobre `id_categoria`.

### Escenario 2: Visualización de números tabulares y códigos monoespaciados
* **Dado** que el usuario visualiza la tabla de inventario de equipos o accesorios,
* **Cuando** inspecciona las celdas de N° Serie/MAC, Código y Precios,
* **Entonces** las series y códigos deben usar tipografía monoespaciada con botón de copiado rápido,
* **Y** las cantidades y precios deben tener la propiedad `font-variant-numeric: tabular-nums;` y alinearse a la derecha.

### Escenario 3: Badges de estado según estándar WMS UI Pro
* **Dado** que un equipo se encuentra en estado `DISPONIBLE`,
* **Entonces** su badge debe tener fondo `#DCFCE7`, texto `#166534` y borde `#BBF7D0`,
* **Y** si se encuentra en estado `OCUPADO`, debe tener fondo `#FEE2E2`, texto `#991B1B` y borde `#FECACA`,
* **Y** si se encuentra en `MANTENIMIENTO`, debe tener fondo `#E0F2FE`, texto `#075985` y borde `#BAE6FD`.

### Escenario 4: Dashboard restringido a 4 KPIs operativos
* **Dado** que un usuario ingresa a `/dashboard`,
* **Entonces** la fila superior debe contener exactamente 4 tarjetas KPI,
* **Y** la segunda tarjeta debe mostrar los accesorios con stock bajo (< 10) con badge de advertencia y enlace directo al filtro.

### Escenario 5: Registro de nuevo accesorio con fecha de entrada obligatoria
* **Dado** que el usuario completa el formulario en `/accesorios/nuevo`,
* **Cuando** envía los datos sin ingresar fecha manual,
* **Entonces** el sistema debe asignar automáticamente la fecha actual (`CURRENT_DATE`) en `fecha_entrada`,
* **Y** la inserción en la base de datos debe completarse exitosamente sin error de restricción `NOT NULL`.

### Escenario 6: Validación de salidas de almacén para accesorios
* **Dado** que un accesorio tiene 5 unidades en stock,
* **Cuando** el operador intenta registrar una salida de 6 unidades,
* **Entonces** el sistema debe deshabilitar el botón de envío y mostrar una alerta roja indicando que la cantidad supera el stock disponible (máximo 5).

---

## 6. Requerimientos No Funcionales

1. **Rendimiento**: Tiempo de renderizado de vistas en servidor < 40ms.
2. **Compatibilidad de Navegadores**: Chrome, Edge, Firefox y navegadores móviles de tablets Android/iPad.
3. **Resiliencia de Conexión**: Todas las consultas a la base de datos deben utilizar consultas parametrizadas con gestión de excepciones controlada.
4. **Ergonomía WMS**: Cero degradados agresivos; contraste de texto sobre fondos superior a 4.5:1 (WCAG AA).
