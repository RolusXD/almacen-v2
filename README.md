# 📦 Sistema de Gestión de Almacén TOI v4.0 (Enterprise WMS)

Sistema web integral para el control de inventario, trazabilidad de equipos de telecomunicaciones por Número de Serie/MAC, existencias de materiales y fibra óptica, y emisión de órdenes de despacho general unificado con transaccionalidad ACID.

---

## 🏗️ Arquitectura y Tecnologías

- **Runtime & Backend:** Node.js 22 (LTS) con **TypeScript** estricto y Express.
- **Motor de Base de Datos:** PostgreSQL 14 / 16 con transacciones ACID y bloqueos pesimistas (`FOR UPDATE`).
- **Diseño de Interfaz:** **AdminLTE v4** basado en **Bootstrap 5.3** y Bootstrap Icons, con soporte multi-paleta en tiempo real (**Modo Claro**, **Modo Noche** y **Alto Contraste**).
- **Motor de Vistas:** EJS (Embedded JavaScript Templates) modularizado con layouts reutilizables.
- **Reportes y Exportación:**
  - **Excel:** Generación de archivos nativos `.xlsx` con estilos corporativos mediante `exceljs`.
  - **PDF:** Comprobantes oficiales de despacho con membrete dinámico y doble firma mediante `pdfkit`.
- **Seguridad & Hardening (OWASP):**
  - Control de Acceso Basado en Roles (**RBAC**) a nivel de middleware backend.
  - Protección estricta contra **IDOR** (Insecure Direct Object References).
  - Cabeceras de seguridad HTTP con **Helmet**.
  - Control de tasa (**Rate Limiting**) en autenticación y envío de correos.
  - Sesiones persistentes en PostgreSQL con cookies firmadas (`connect-pg-simple`).
  - Cifrado simétrico **AES-256-GCM** para credenciales sensibles (servidor SMTP).
  - Bitácora de auditoría detallada con marcas de tiempo milimétricas e IP real (`trust proxy`).

---

## 🚀 Módulos Principales

### 1. Dashboard General
- **4 Tarjetas KPI AdminLTE:** Equipos Disponibles, Equipos en Servicio, Materiales/Fibra en Catálogo y Alertas de Stock Crítico.
- **Estado General de Bodega:** Resumen consolidado del inventario, valor monetario estimado de existencias en Bolivianos (`Bs.`) y acceso directo a `+ Nuevo Despacho`.
- **Kardex Rápido:** Visualización de los últimos movimientos con estado, destino y responsable.

### 2. Inventario de Equipos de Red
- Catálogo completo de ONUs, Routers, Switches, Antenas y OLTs.
- Trazabilidad unitaria por **Número de Serie o MAC**.
- Ciclo de vida por estados: `DISPONIBLE`, `OCUPADO` (en servicio), `MANTENIMIENTO` y `RETIRADO`.
- Filtros por estado, código de tipo de equipo y buscador reactivo.

### 3. Materiales, Cables y Fibra Óptica
- Gestión de ítems por unidad de medida (`METROS`, `BOBINAS`, `UNIDADES`, `KILOS`, `PIEZAS`).
- Alerta visual y automática cuando el stock cae por debajo del umbral mínimo configurado (`<= COALESCE(stock_minimo, 10)`).
- Registro de costo unitario para la valorización de inventario en almacén.

### 4. Despacho General Unificado (Orden de Salida ACID)
- **Salida Simultánea:** Permite despachar equipos específicos por Serie/MAC y materiales por cantidad dentro de una misma orden correlativa (`DSP-YYYYMMDD-XXXX`).
- **Carrito Reactivo:** Validación en tiempo real contra duplicados de series y verificación de saldo disponible en bodega.
- **Transacción Atómica:** Bloqueo de filas pesimista que descuenta stock, actualiza equipos a `OCUPADO`, crea el comprobante y emite alertas por correo de forma atómica.
- **Comprobante PDF Oficial:** Descarga directa de la orden de salida con datos institucionales, listado de ítems y recuadros formales para entrega y recepción conforme.

### 5. Control y Taller
- **Mantenimientos:** Registro de ingresos a taller (preventivo/correctivo), técnico asignado, costos y entrega de equipos reparados.
- **Préstamos:** Control temporal de herramientas y equipos a cuadrillas con fecha esperada de devolución y actas de entrega.

### 6. Centro de Reportes y Kardex
- Exportación en tiempo real a **Excel (.xlsx)** y **PDF** de: Catálogo de Equipos, Existencias de Materiales e Historial de Movimientos.
- Filtros multicriterio por rango de fechas, destinos y operadores.

### 7. Administración Centralizada (Solo Administradores)
- **Gestión de Usuarios:** Creación de operadores, asignación de roles (`admin` / `usuario`), activación/desactivación de cuentas y cambio seguro de contraseñas.
- **Configuración General:** Definición de Razón Social, NIT, Dirección, Teléfono, Pie de Firma, zona horaria oficial (`America/La_Paz`) y correos receptores de alertas.
- **Servidor SMTP:** Configuración del servidor de correo corporativo con prueba interactiva de conexión y contraseñas cifradas con AES-256-GCM.
- **Logs y Auditoría:** Bitácora completa de eventos del sistema (inicios de sesión, despachos, anulaciones, configuraciones) con opción de exportación a Excel.

---

## 👥 Matriz de Roles y Permisos (RBAC)

| Módulo / Acción | Administrador (`admin`) | Operador (`usuario`) |
|---|:---:|:---:|
| Ver Dashboard e Inventario | ✅ Completo | ✅ Completo |
| Emitir Despacho General | ✅ Completo | ✅ Completo |
| Ver Historial de Despachos | ✅ Todas las órdenes | 🔒 Solo sus propias órdenes (Anti-IDOR) |
| Descargar Comprobante PDF | ✅ Cualquier orden | 🔒 Solo sus propias órdenes (Anti-IDOR) |
| Anular Órdenes de Despacho | ✅ Permitido (restituye stock) | ❌ Bloqueado (403) |
| Registrar Equipos / Materiales | ✅ Permitido | ✅ Permitido |
| Eliminar Registros Maestros | ✅ Permitido | ❌ Bloqueado (403) |
| Gestión de Usuarios | ✅ Completo | ❌ Bloqueado (403) |
| Configuración General y SMTP | ✅ Completo | ❌ Bloqueado (403) |
| Bitácora de Auditoría (Logs) | ✅ Completo | ❌ Bloqueado (403) |

---

## 📂 Estructura del Proyecto

```text
almacen-v2/
├── .env.example              # Plantilla de variables de entorno para producción
├── Dockerfile                # Compilación multi-stage optimizada (Node 22 Alpine)
├── docker-compose.yml        # Orquestación local de App y PostgreSQL 14
├── package.json              # Dependencias y scripts de construcción
├── tsconfig.json             # Configuración TypeScript
├── backups/                  # Backups consolidados (.gitignore)
├── docs/                     # Guías operativas y de despliegue
│   └── GUIA_DESPLIEGUE_COOLIFY.md
└── src/
    ├── app.ts                # Inicialización Express, Middlewares, Helmet y Rutas
    ├── server.ts             # Arranque de servidor y sincronización de zona horaria
    ├── config/               # Variables de entorno validadas (env.ts)
    ├── db/                   # Pool de PostgreSQL y migraciones SQL (001 a 004)
    ├── controllers/          # Controladores HTTP y lógica de presentación
    ├── models/               # Interfaces y tipos de TypeScript
    ├── repositories/         # Capa de datos con consultas SQL y transacciones ACID
    ├── services/             # Servicios auxiliares (Audit, Mail, PDF)
    ├── middlewares/          # Autenticación, RBAC y Rate Limiting
    ├── utils/                # Cifrado AES, fechas y formateadores
    ├── public/               # Estilos CSS (AdminLTE 4, multi-tema) y scripts cliente
    └── views/                # Plantillas EJS estructuradas por módulo
```

---

## ⚙️ Puesta en Marcha Local

### Requisitos Previos
- Node.js >= 20.x
- Docker y Docker Compose

### Pasos de Ejecución
```bash
# 1. Clonar el repositorio
git clone <URL_REPOSITORIO>
cd almacen-v2

# 2. Configurar variables de entorno
cp .env.example .env

# 3. Levantar servicios con Docker Compose
docker compose up -d --build

# 4. Acceder al sistema
# Abrir en el navegador: http://localhost:3000
# Credenciales por defecto: admin / Admin123!
```

---

## 📄 Licencia y Créditos
Desarrollado para **TOI TELECOMUNICACIONES BOLIVIA S.R.L.** — Sistema de Almacén v4.0.
