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
2. **Transformación Visual y Operativa (WMS UI Pro Obligatorio)**: La interfaz debe cumplir estrictamente con los estándares y principios de diseño definidos en [`.agents/skills/wms-ui-pro/SKILL.md`](file:///c:/Users/raul1/Desktop/soporte/proyectos%20desarrollo/almacen%20v2/.agents/skills/wms-ui-pro/SKILL.md):
   - **Estética Clean Industrial / Linear-inspired**: Cero degradados violetas o neón, superficies limpias con fondo `#F8FAFC`, tarjetas `#FFFFFF`, bordes estructurados de `1px` en `#E2E8F0` y sombras sutiles de ingeniería.
   - **Cuádrupla Semántica de Inventario**: Badges suaves de alto contraste para Stock Óptimo (`#DCFCE7`/`#166534`), Stock Bajo (`#FEF3C7`/`#92400E`), Agotado/Crítico (`#FEE2E2`/`#991B1B`) y Tránsito/Taller (`#E0F2FE`/`#075985`).
   - **Tipografía y Lectura de Datos**: Aplicación obligatoria de `font-variant-numeric: tabular-nums;` en cantidades, montos, precios y fechas; códigos/series/SKUs en fuente monoespaciada (`JetBrains Mono`, `Consolas`) con botón de copiado rápido al portapapeles.
   - **Tablas de Alto Rendimiento**: Encabezados fijos (`sticky top-0`), alineación estricta por tipo de dato (números a la derecha, texto a la izquierda), filas compactas con hover sutil (`py-2.5`) y acciones rápidas directas por fila.
   - **Dashboard Operativo**: Restricción estricta a un máximo de 4 tarjetas KPI principales con tipografía grande (`text-2xl font-bold`), etiqueta superior e indicador de contexto inferior.
   - **Formularios de Bodega**: Enfoque automático (`autofocus`), soporte para lectores de código de barras (`Enter`), validación visual inmediata bloqueando envíos si salida > stock disponible y botones táctiles amplios (`40px`–`44px`) para tablets.
   - **Navegación y Cero Desbordes**: Sidebar lateral colapsable, barra superior con atajo global `Ctrl + K` o `/`, y contenedor con scroll horizontal controlado para evitar desbordamientos en la ventana principal.

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
  - **Validación**: La ruta `/maestros/categorias` debe responder con código HTTP 200, mostrando las 7 categorías reales existentes (`Router`, `Switch`, `ONT`, `Cable`, `Conector`, `Accesorio`, `ANTENA CLIENTE`) con sus respectivos conteos de equipos y accesorios vinculados.

### 3.2. Auditoría y Corrección de Esquema en Accesorios
- **REQ-STAB-02**: En la tabla `accesorios`, la columna `fecha_entrada` tiene restricción `NOT NULL` sin valor predeterminado.
  - El repositorio `AccesorioRepository.create` y el formulario `accesorios/form.ejs` deben incluir y validar el campo `fecha_entrada` (por defecto la fecha actual `CURRENT_DATE`), evitando que cualquier alta de accesorio falle con error de base de datos.
  - Asegurar la asociación opcional con `id_categoria` y `id_proveedor`.

### 3.3. Auditoría y Corrección de Esquema en Mantenimientos
- **REQ-STAB-03**: En la tabla `mantenimientos`, las columnas reales en PostgreSQL son:
  - `tipo_mantenimiento` (con check constraint: `PREVENTIVO`, `CORRECTIVO`, `EVOLUTIVO`).
  - `descripcion` (texto obligatorio `NOT NULL`).
  - `tecnico_responsable` (`NOT NULL`).
  - `fecha_ingreso` (`NOT NULL`).
  - `costo` (numérico, no `costo_reparacion`).
  - `estado` (check constraint: `EN_PROCESO`, `COMPLETADO`, `CANCELADO`).
  - `repuestos_cambiados` y `observaciones`.
  - **Alineación**: Refactorizar `MaestroRepository.createMantenimiento`, `completeMantenimiento`, `src/models/types.ts` y la vista `mantenimientos.ejs` para alinearse 100% con este esquema y sus restricciones `CHECK`, permitiendo dar de alta reparaciones y cerrarlas restaurando el equipo a `DISPONIBLE`.

### 3.4. Auditoría y Corrección de Esquema en Préstamos
- **REQ-STAB-04**: En la tabla `prestamos`, las columnas reales en PostgreSQL son:
  - `nombre_solicita` (no `solicitante`, campo `NOT NULL`).
  - `motivo_prestamo` (no `motivo`, campo `NOT NULL`).
  - `fecha_prestamo` (`NOT NULL`).
  - `fecha_devolucion_esperada` (`NOT NULL`).
  - `observaciones_salida` (no `observaciones`).
  - `estado` (check constraint: `PRESTADO`, `DEVUELTO`, `ATRASADO`, `PERDIDO`).
  - **Alineación**: Refactorizar `MaestroRepository.createPrestamo`, `returnPrestamo`, interfaces y vista `prestamos.ejs` garantizando inserciones limpias y flujo de devolución sin violaciones de integridad.

### 3.5. Compatibilidad de Estados de Equipos
- **REQ-STAB-05**: En la tabla `equipos`, la restricción `chk_equipos_estado` restringe los valores a:
  `'DISPONIBLE'`, `'OCUPADO'`, `'MANTENIMIENTO'`, `'RETIRADO'`.
  - El trigger automático `sync_equipo_estado_from_movimiento` actualiza a `'OCUPADO'` cuando hay `SALIDA` y a `'DISPONIBLE'` cuando hay `ENTRADA`.
  - La aplicación debe usar internamente el valor canónico `'OCUPADO'` en lugar de `'ASIGNADO'`, mostrando visualmente la etiqueta operativa *"OCUPADO / EN SERVICIO"* con badge semántico y reflejando las estadísticas reales (90 disponibles vs 17 ocupados).

### 3.6. Manejo Global de Errores y Páginas Informativas
- **REQ-STAB-06**: Reemplazar respuestas de error en texto plano (`res.status(500).send('Error...')`) por un middleware de error centralizado que renderice una vista visualmente consistente con el sistema de diseño (`views/error.ejs`), capturando trazas de depuración solo en entorno de desarrollo.

---

## 4. Alcance 2: Rediseño Visual Operativo (WMS UI Pro)

Todas las vistas deben cumplir de manera estricta y sin excepciones con los principios de diseño establecidos en [`.agents/skills/wms-ui-pro/SKILL.md`](file:///c:/Users/raul1/Desktop/soporte/proyectos%20desarrollo/almacen%20v2/.agents/skills/wms-ui-pro/SKILL.md):

### 4.1. Filosofía Visual "Clean Industrial" y Anti-Fatiga
- **REQ-UI-01**: **Eliminación de Estética Genérica de IA**:
  - Prohibido terminantemente el uso de degradados violetas o neón, sombras infladas, bordes con radio exagerado o tarjetas flotantes innecesarias.
- **REQ-UI-02**: **Superficie, Bordes y Contraste**:
  - Fondo base en blanco humo o gris tenue antifatiga: `#F8FAFC`.
  - Superficies de tarjetas operativas y tablas en blanco puro: `#FFFFFF`.
  - Bordes estructurados y sutiles de `1px` en `#E2E8F0` (modo claro) / `#1E293B` (modo oscuro).
  - Sombras de ingeniería limpias: `0 1px 2px 0 rgba(0, 0, 0, 0.05)`.
- **REQ-UI-03**: **Color Primario y Jerarquía Industrial**:
  - Color primario para acciones clave: Azul cobalto industrial / acero profundo (`#0F172A` o `#1D4ED8`).
  - Botones secundarios en superficie blanca con borde `#CBD5E1` y texto `#334155`.

### 4.2. Cuádrupla Semántica de Estados de Inventario (Regla Estricta WMS)
- **REQ-UI-04**: Todos los estados de inventario en equipos, accesorios, movimientos y préstamos deben adoptar exclusivamente la siguiente cuádrupla cromática de alto contraste:
  1. **Stock Óptimo / Disponible**:  
     Fondo `#DCFCE7` | Texto `#166534` | Borde `#BBF7D0`
  2. **Stock Bajo / Reorden / Advertencia**:  
     Fondo `#FEF3C7` | Texto `#92400E` | Borde `#FDE68A`
  3. **Agotado / Ocupado / Crítico / Retirado**:  
     Fondo `#FEE2E2` | Texto `#991B1B` | Borde `#FECACA`
  4. **En Tránsito / Mantenimiento / Revisión / Préstamo**:  
     Fondo `#E0F2FE` | Texto `#075985` | Borde `#BAE6FD`

### 4.3. Tipografía y Lectura de Datos
- **REQ-UI-05**: **Familias Tipográficas**:
  - Fuentes sans-serif geométricas y legibles: `Inter`, `Plus Jakarta Sans` o `Geist`.
- **REQ-UI-06**: **Números Tabulares Obligatorios**:  
  - Aplicar `font-variant-numeric: tabular-nums;` en todas las cantidades de stock, precios en Bs., costos, porcentajes, fechas ISO y conteos, asegurando alineación vertical perfecta en columnas de inventario.
- **REQ-UI-07**: **Códigos, MACs y SKUs Monoespaciados con Copiado Rápido**:  
  - Toda serie, dirección MAC, código de barra, lote, código de equipo o SKU de accesorio debe presentarse en fuente monoespaciada (`JetBrains Mono`, `Consolas` o `ui-monospace`) con un botón rápido de "copiar al portapapeles" e indicador discreto tipo Toast.

### 4.4. Arquitectura de Tablas de Inventario
- **REQ-UI-08**: **Encabezados Fijos (`sticky top-0`)**:  
  - Las cabeceras de tabla deben fijarse al hacer scroll vertical en listas largas de productos, con fondo sólido `#F8FAFC`, `z-index: 10` y línea divisoria inferior de `2px` en `#CBD5E1`.
- **REQ-UI-09**: **Alineación Estricta por Tipo de Dato**:
  - Textos y nombres descriptivos: **Izquierda (`text-left`)**.
  - Cantidades, precios en Bs., stocks y totales numéricos: **Derecha (`text-right`)**.
  - Badges de estado y fechas ISO: **Centrados o Izquierda coherente**.
  - Acciones operativas: **Derecha fija (`text-right actions-cell`)**.
- **REQ-UI-10**: **Filas Compactas y Escaneables**:
  - Padding vertical equilibrado (`py-2.5` o `py-3`, `0.5rem 0.75rem`), efecto `hover` sutil (`#F8FAFC`) y líneas divisorias finas de `1px` en `#E2E8F0`.
- **REQ-UI-11**: **Acciones Rápidas Directas por Fila**:  
  - Cada fila debe incluir botones de acción directa con iconos claros (ej. `Mover`, `Editar`, `Eliminar`), evitando menús anidados que retrasen la operativa de almacén.

### 4.5. Dashboard Operativo Reestructurado (Exactamente 4 KPIs)
- **REQ-UI-12**: La fila superior del Dashboard debe contener exactamente **4 tarjetas operativas primarias**:
  1. **Total de Equipos Registrados**: Conteo general con desglose directo de unidades disponibles vs ocupadas.
  2. **Stock Crítico de Materiales**: Accesorios con existencias `< 10` con enlace directo filtrado (`/accesorios?stock_bajo=true`).
  3. **Equipos en Mantenimiento / Taller**: Cantidad de equipos en revisión o reparación con acceso directo a taller.
  4. **Total de Materiales en Bodega**: Unidades físicas totales y rubros activos.
- Cada tarjeta debe exhibir el dato principal en tipografía grande (`text-2xl font-bold`, `1.85rem`), etiqueta superior en mayúsculas sutiles e indicador inferior de contexto o tendencia.

### 4.6. Formularios de Movimiento Optimizados para Bodega
- **REQ-UI-13**:
  - **Optimización para lectores de código de barras**: Campo de búsqueda/serie con enfoque automático (`autofocus`) y soporte para agregar al presionar `Enter` sin recarga de página.
  - **Validación visual inmediata**: Si una salida supera las existencias disponibles, el sistema debe bloquear el botón de envío y desplegar una alerta roja destacada indicando el stock máximo disponible.
  - **Áreas táctiles amplias**: Altura mínima de controles táctiles y botones de `40px`–`44px` (`.mw-btn-touch`) para compatibilidad fluida con tablets industriales de almacén.

### 4.7. Navegación, Barra Superior y Responsividad
- **REQ-UI-14**:
  - **Sidebar lateral colapsable** con secciones ordenadas: *Dashboard*, *Inventario* (Equipos, Accesorios), *Operaciones* (Mover Equipos, Mover Accesorios), *Gestión Maestra* (Tipos, Proveedores, Categorías, Marcas), *Control* (Mantenimientos, Préstamos) y *Reportes*.
  - **Barra superior con buscador global rápido**: Atajo de teclado **`Ctrl + K`** o tecla **`/`** para enfocar el buscador desde cualquier pantalla.
  - **Cero desbordamiento horizontal**: Contenedores de tabla con `overflow-x: auto` controlado para garantizar cero scroll horizontal en la ventana principal del navegador en cualquier resolución.

### 4.8. Buenas Prácticas Técnicas y Usabilidad
- **REQ-UI-15**:
  - **Estados vacíos ilustrados y limpios**: Ninguna pantalla o tabla debe quedar en blanco; incluir mensajes útiles con iconos claros (ej. *"No se registran movimientos recientes en el sistema"*).
  - **Feedback inmediato al usuario**: Toda acción de guardar, editar o mover inventario debe confirmar el resultado mediante mensajes Toast o alertas visuales discretas.
  - **Regla de los 2 segundos**: La densidad y jerarquía visual deben permitir que un operario de almacén interprete el estado de un producto en menos de 2 segundos.

---

## 5. Criterios de Aceptación (Gherkin / Test Scenarios)

### Escenario 1: Navegación y resolución del catálogo de Categorías
* **Dado** que un usuario autenticado accede al sistema,
* **Cuando** navega a la URL `/maestros/categorias` o hace clic en "Gestión Maestra > Categorías",
* **Entonces** el servidor debe responder con código HTTP 200 (sin error 500),
* **Y** la tabla debe mostrar las 7 categorías reales existentes (`Router`, `Switch`, `ONT`, `Cable`, `Conector`, `Accesorio`, `ANTENA CLIENTE`),
* **Y** los conteos de equipos y accesorios vinculados deben reflejar la consulta correcta sobre `id_categoria` con números tabulares.

### Escenario 2: Visualización de números tabulares y códigos monoespaciados con botón de copiado
* **Dado** que el usuario visualiza la tabla de inventario de equipos o accesorios,
* **Cuando** inspecciona las celdas de N° Serie/MAC, Código y Precios,
* **Entonces** las series y códigos deben usar tipografía monoespaciada con botón de copiado al portapapeles,
* **Y** al hacer clic en el botón de copiado debe mostrarse una notificación Toast confirmando la acción,
* **Y** las cantidades, precios y fechas deben tener la propiedad `font-variant-numeric: tabular-nums;` y alinearse a la derecha.

### Escenario 3: Badges semánticos de inventario WMS UI Pro
* **Dado** que un equipo o material tiene estado `DISPONIBLE`,
* **Entonces** su badge debe tener fondo `#DCFCE7`, texto `#166534` y borde `#BBF7D0`,
* **Y** si se encuentra en estado `OCUPADO` o `AGOTADO`, debe tener fondo `#FEE2E2`, texto `#991B1B` y borde `#FECACA`,
* **Y** si se encuentra en `MANTENIMIENTO` o `PRESTADO`, debe tener fondo `#E0F2FE`, texto `#075985` y borde `#BAE6FD`,
* **Y** si tiene stock crítico (< 10), debe tener fondo `#FEF3C7`, texto `#92400E` y borde `#FDE68A`.

### Escenario 4: Dashboard restringido a exactamente 4 KPIs operativos
* **Dado** que un usuario ingresa a `/dashboard`,
* **Entonces** la fila superior debe contener exactamente 4 tarjetas KPI,
* **Y** el número principal debe tener tipografía de gran tamaño (`font-size: 1.85rem; font-weight: 800; font-variant-numeric: tabular-nums;`),
* **Y** la tarjeta de stock crítico debe enlazar directamente a `/accesorios?stock_bajo=true`.

### Escenario 5: Registro de nuevo accesorio con fecha de entrada obligatoria
* **Dado** que el usuario completa el formulario en `/accesorios/nuevo`,
* **Cuando** envía los datos sin ingresar fecha manual,
* **Entonces** el sistema debe asignar automáticamente la fecha actual (`CURRENT_DATE`) en `fecha_entrada`,
* **Y** la inserción en la base de datos debe completarse exitosamente con calidad fijada estrictamente en `NUEVO`.

### Escenario 6: Validación visual inmediata en salidas de accesorios
* **Dado** que un accesorio tiene 5 unidades en stock en bodega,
* **Cuando** el operador ingresa 6 unidades en el formulario de salida en `/movimientos/accesorios`,
* **Entonces** el sistema debe deshabilitar inmediatamente el botón de envío y mostrar una alerta roja indicando que la cantidad supera el stock disponible.

### Escenario 7: Atajo de teclado global para búsqueda
* **Dado** que el usuario se encuentra en cualquier vista del sistema,
* **Cuando** presiona la combinación de teclas `Ctrl + K` o presiona la tecla `/`,
* **Entonces** el foco del cursor debe posicionarse automáticamente en el campo de búsqueda de la pantalla activa.

---

## 6. Requerimientos No Funcionales

1. **Rendimiento**: Tiempo de renderizado de vistas en servidor < 40ms; tamaño de bundle CSS ligero y sin frameworks redundantes.
2. **Compatibilidad de Navegadores**: Chrome, Edge, Firefox, Safari y navegadores móviles de tablets Android/iPad.
3. **Resiliencia y Concurrencia**: Todas las transacciones de inventario deben utilizar consultas parametrizadas dentro de transacciones SQL (`BEGIN ... COMMIT`) con bloqueos de fila (`FOR UPDATE`) para evitar condiciones de carrera.
4. **Ergonomía WMS**: Contraste de texto sobre fondos superior a 4.5:1 (cumplimiento WCAG AA), sin saturación visual por colores agresivos.
