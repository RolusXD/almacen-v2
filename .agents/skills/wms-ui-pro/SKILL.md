---
name: wms-ui-pro
description: "Integra y aplica el framework oficial AdminLTE v4 (Bootstrap 5.3) en proyectos Node.js. Úsalo siempre para estructurar layouts, tarjetas KPI (small-box), formularios (.card, .form-control, .form-select), tablas administrativas y modales."
---

# Integración Oficial AdminLTE 4 (Bootstrap 5) - Sistema de Almacenes

Eres un desarrollador experto en **Node.js** y **AdminLTE v4 (Bootstrap 5.3)**. Tu objetivo es que TODAS las vistas del sistema utilicen exclusivamente los archivos CSS/JS oficiales de `admin-lte` instalados vía npm y sus clases estándar de Bootstrap 5, eliminando cualquier estilo roto o sin formato.

## 1. Configuración Base en Node.js
- Asegúrate de que `admin-lte` (v4) y `bootstrap-icons` (o `@fortawesome/fontawesome-free`) estén instalados en `package.json` y servidos como archivos estáticos en Express (ej. `app.use('/adminlte', express.static(path.join(__dirname, 'node_modules/admin-lte/dist')))`).
- El layout principal (`header.ejs`, `sidebar.ejs`, `footer.ejs`) debe cargar obligatoriamente `adminlte.min.css` en el `<head>` y `adminlte.min.js` + `bootstrap.bundle.min.js` antes de cerrar `</body>`.
- Usa la estructura oficial de AdminLTE 4: `<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">`, `<div class="app-wrapper">`, `<nav class="app-header navbar navbar-expand bg-body">`, `<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">` y `<main class="app-main">`.

## 2. Componentes Obligatorios en TODAS las Vistas
1. **Tarjetas KPI en Dashboard (`small-box`):**
   - Usa la clase nativa `<div class="small-box text-bg-info">`, `text-bg-success`, `text-bg-primary`, `text-bg-warning` con `<div class="inner">`, icono grande y `<a href="#" class="small-box-footer">Ver más <i class="bi bi-arrow-right-circle"></i></a>`.
2. **Contenedores y Formularios (`card` + Bootstrap 5):**
   - Envuelve cada formulario o sección dentro de `<div class="card card-primary card-outline mb-4">` con `<div class="card-header"><h3 class="card-title">...</h3></div>` y `<div class="card-body">`.
   - **Prohibido dejar inputs sin clase:** Todo `<input>` debe llevar `class="form-control"`, todo `<select>` debe llevar `class="form-select"`, todo `<label>` debe llevar `class="form-label fw-semibold"` y los botones deben llevar `btn btn-primary`, `btn btn-success`, `btn btn-danger` o `btn btn-secondary`.
3. **Tablas Administrativas:**
   - Envuelve cada tabla en `<div class="table-responsive">`.
   - Usa `<table class="table table-bordered table-striped table-hover align-middle mb-0">` con `<thead class="table-light">`.
   - Usa `<span class="badge text-bg-success">`, `text-bg-warning` o `text-bg-danger` para los estados.
