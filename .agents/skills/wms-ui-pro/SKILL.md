---
name: wms-ui-pro
description: "Integra AdminLTE v4 (Bootstrap 5.3) con soporte multi-tema (Claro, Noche, Alto Contraste) y operaciones basadas en Modales flotantes para maximizar el ancho de tablas en sistemas de almacenes."
---

# AdminLTE 4 Multi-Tema y Tablas Full-Width (Modales)

Eres un desarrollador experto en **Node.js** y **AdminLTE v4 (Bootstrap 5.3)**. Debes garantizar contrastes impecables en todos los temas y maximizar el espacio horizontal de las tablas.

## 1. Sistema de Temas (Claro / Noche / Alto Contraste)
- **Prohibido hardcodear colores:** Nunca uses clases fijas como `bg-white`, `bg-light`, `text-dark` o estilos inline con colores fijos en tarjetas, filtros o tablas. Usa siempre variables/clases adaptables de Bootstrap 5.3 (`bg-body`, `bg-body-tertiary`, `text-body`, `border`) para que respondan automáticamente al tema activo.
- **Selector de Tema en Navbar:** Incluye en la barra superior (al lado de Alertas/Admin) un dropdown con 3 opciones persistidas en `localStorage`:
  1. **Modo Claro (`light`):** `document.documentElement.setAttribute('data-bs-theme', 'light')` (Estilo clásico AdminLTE con fondo `#F4F6F9` y tarjetas blancas).
  2. **Modo Noche (`dark`):** `document.documentElement.setAttribute('data-bs-theme', 'dark')` (Fondo oscuro uniforme sin cajas blancas sueltas).
  3. **Alto Contraste (`high-contrast`):** `data-bs-theme="dark"` + clase `theme-high-contrast` en `<body>` con CSS dedicado (fondo `#000000`, tarjetas `#0A0A0A`, bordes `#FFFFFF` o `#FFD700` de `2px`, y textos `#FFFFFF` al 100% de contraste).

## 2. Tablas al 100% de Ancho y Formularios en Modales Flotantes
- **Vista Principal = Tabla Completa:** En todas las pantallas de operaciones y gestión (`Equipos`, `Accesorios`, `Movimientos de Equipos`, `Movimientos de Accesorios`, `Categorías`, `Tipos`, `Proveedores`, `Marcas`, `Taller`, `Préstamos`), la tabla debe ocupar el **100% del ancho (`col-12`)**.
- **Acciones en Ventanas Flotantes (`Bootstrap Modal`):**
  - Nunca pongas formularios fijos al costado de una tabla.
  - Coloca un botón principal en la cabecera de la tarjeta de la tabla (ej. `+ Registrar Movimiento`, `+ Nuevo Equipo`, `+ Nueva Categoría`).
  - Al hacer clic, abre un `<div class="modal fade" tabindex="-1">` centrado (`modal-lg` o `modal-dialog-centered`) con el formulario completo adentro.
