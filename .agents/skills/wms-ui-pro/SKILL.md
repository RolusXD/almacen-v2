---
name: wms-ui-pro
description: >-
  Diseña interfaces administrativas estilo ERP / AdminLTE / Mikrowisp para sistemas de almacenes e inventarios en Node.js. Úsalo para crear sidebars oscuros con perfil de usuario, topbars blancos, tarjetas KPI de colores sólidos, gráficos limpios y tablas administrativas responsivas.
---

# WMS Admin UI - Estilo ERP Clásico (Inspirado en Mikrowisp / AdminLTE)

Eres un desarrollador frontend experto en paneles administrativos empresariales (ERP/WMS) basados en el estándar visual clásico de **AdminLTE / Color Admin / Mikrowisp**. Tu objetivo es construir una interfaz familiar, estructurada, de alto contraste y muy cómoda para la gestión diaria.

## 1. Estructura General del Layout (Admin Shell)
- **Sidebar Izquierdo Oscuro (`#222D32` o `#1E282C`):**
  - **Encabezado de marca (Top-Left):** Fondo blanco (`#FFFFFF`) o azul corporativo (`#3C8DBC`) de `50px`–`60px` de alto con el logotipo y nombre del sistema.
  - **Panel de Perfil de Usuario:** Debajo del logo, muestra avatar circular a la izquierda, nombre completo en blanco en negrita y rol/cargo debajo en gris claro (`#B8C7CE`).
  - **Menú de Navegación Vertical:** Etiqueta `MENÚ` en gris tenue, ítems con icono a la izquierda + texto + flecha (`>`) a la derecha para submenús desplegables. El ítem activo lleva fondo `#1E282C` y borde izquierdo de acento (`3px solid #3C8DBC`). Incluye botón colapsable (`<<`) al pie.
- **Barra Superior Blanca (`Topbar`, `#FFFFFF`, altura `55px`):**
  - Borde inferior sutil (`1px solid #E5E7EB`).
  - Buscador rápido a la derecha con icono de lupa, iconos de acceso directo/notificaciones con badge numérico y menú desplegable del usuario (`ADMIN ▼`).
- **Área de Contenido Principal (`#ECF0F5`):**
  - Fondo gris claro clásico que hace resaltar las tarjetas blancas y los bloques de datos.
  - Título de página o saludo arriba (ej. `Bienvenido [Usuario]` o `Gestión de Categorías`).

## 2. Tarjetas KPI Superiores (Estilo Mikrowisp - 4 columnas)
En el Dashboard, usa exactamente **4 tarjetas horizontales** (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4`) con colores sólidos vibrantes, texto blanco, icono grande semi-transparente a la derecha (`opacity-20`) y barra inferior ligeramente más oscura con enlace (`Ver detalle →`):
1. **Tarjeta 1 (Turquesa / Teal `#00A65A` o `#17A2B8`):** Ej. Total Productos / Stock Disponible.
2. **Tarjeta 2 (Azul `#3498DB` o `#0073B7`):** Ej. Entradas / Movimientos Hoy.
3. **Tarjeta 3 (Violeta / Índigo `#605CA8` o `#6F42C1`):** Ej. Categorías / Órdenes Pendientes.
4. **Tarjeta 4 (Gris Oscuro / Pizarra `#343A40` o Naranja `#F39C12`):** Ej. Alertas de Stock Crítico.

## 3. Bloques de Gráficos y Resúmenes (Widgets)
- **Tarjetas Oscuras de Analítica (Opcional para Dashboard):** Contenedores con fondo `#2C3B41`, texto blanco, gráfico de líneas/áreas suavizadas en tonos cian/azul y gráfico de dona (donut) para porcentajes de ocupación o movimientos.
- **Panel de Resumen Lateral (Lista con Badges):** Tarjeta blanca con borde superior limpio, lista numerada (`1. ...`, `2. ...`) y píldoras redondeadas de colores a la derecha (`#17A2B8`, `#28A745`, `#DC3545`, `#FFC107`) mostrando conteos rápidos.

## 4. Tablas Administrativas y Formularios (Gestión Maestra y Almacén)
- **Contenedor de Tabla (Box / Card):** Fondo `#FFFFFF`, borde superior de color (`border-t-4 border-[#3498DB]`), cabecera con título a la izquierda y botón primario `+ Nuevo Registro` (`#28A745` o `#3498DB`) a la derecha.
- **Controles de Tabla:** Selector `Mostrar [10] registros` a la izquierda y barra `Buscar:` a la derecha (estilo DataTables).
- **Estilo de Tabla:**
  - Cabecera clara (`#F8F9FA`) con texto oscuro en negrita y bordes definidos (`1px solid #DEE2E6`).
  - Filas alternadas (`striped` sutil) y efecto `hover` (`#F1F5F9`).
  - Números y cantidades alineados a la derecha con `tabular-nums`.
  - Botones de acción compactos por fila con iconos claros: Editar (Azul `#3498DB`), Eliminar/Desactivar (Rojo `#DC3545`), Ver/Kardex (Turquesa `#17A2B8`).
- **Responsividad:** Envolver siempre las tablas en `overflow-x-auto` y colapsar el sidebar en pantallas menores a `992px`.

## 5. Reglas de Ejecución en Node.js
- Crea un **layout maestro reutilizable** (por ejemplo `views/partials/header.ejs`, `sidebar.ejs`, `footer.ejs` o el sistema de layouts que use el proyecto) para que **todas** las pantallas (Dashboard, Categorías, Productos, Entradas, Salidas, Reportes) compartan exactamente el mismo menú lateral oscuro y barra superior blanca sin duplicar código.
