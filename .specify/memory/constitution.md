# CONSTITUTION: Sistema de Almacén TOI (v4.0 Node.js)
<!-- Spec Kit Governance Document -->

## 1. Propósito y Filosofía
Este documento establece las directrices inmutables de ingeniería, arquitectura y reglas de negocio para la modernización del **Sistema de Almacén TOI** (Telecomunicaciones Oportunas Inteligentes), migrándolo desde PHP heredado hacia una arquitectura moderna, reactiva y robusta basada en **Node.js (TypeScript)**, preparada para despliegue de alta disponibilidad en **Coolify**.

---

## 2. Principios Arquitectónicos Inmutables

### Principio 1: Cero Regresión Funcional
Todas las capacidades operativas del sistema de almacén previo deben preservarse intactas:
- Autenticación segura con verificación de hash bcrypt compatible con los usuarios existentes en la base de datos (`Admin123!`).
- Catálogo y control de inventario de **Equipos** (series, MACs, estados, importación/exportación).
- Catálogo y control de inventario de **Accesorios y Materiales** (unidades de medida, control estricto de stock).
- **Movimientos de Equipos** con trazabilidad de egresos/ingresos, destino y responsable.
- **Movimientos de Accesorios** con validación y descuento/reingreso automático de existencias y registro de destino.
- Módulos auxiliares: Tipos de Equipos, Proveedores (con campo NIT para Bolivia), Categorías, Marcas, Mantenimientos, Préstamos y Reportes.

### Principio 2: Interfaz Sobria y de Alta Densidad (Estilo MikroWisp)
- **Estética Empresarial**: Paleta corporativa sobria (sidebar azul pizarra `#1e293b`, fondo neutro `#f1f5f9`, tarjetas y tablas en blanco `#ffffff`).
- **Tablas Compactas**: Densidad de información profesional (tipografía 12-13px, padding reducido `py-1` a `py-2`).
- **Cero Desbordes**: Prohibido el desbordamiento o corte de tablas. Las columnas de acciones y estados deben estar siempre visibles y accesibles.
- **Barra de Acciones Unificada**: Búsqueda global, selector de tamaño de página, botón "+ Nuevo", importación CSV, exportación y filtros de columna.

### Principio 3: Reglas de Negocio Estrictas
1. **Calidad Estricta "NUEVO"**:
   - Al registrar cualquier equipo o accesorio nuevo, el campo `calidad` queda bloqueado y fijado estrictamente en `NUEVO`. No se debe permitir seleccionar `BUENO`, `REGULAR` ni `MALO` en altas.
2. **Búsqueda Instantánea en Movimiento de Equipos**:
   - El operador debe poder buscar por Número de Serie o MAC y obtener inmediatamente la ficha del equipo (código, modelo, marca y estado actual) antes de confirmar el movimiento.
3. **Movimientos de Accesorios con Destino y Validación de Existencias**:
   - Todo movimiento de accesorio debe exigir: accesorio, tipo de movimiento (`SALIDA`/`ENTRADA`), cantidad, destino, encargado y asunto.
   - En salidas, el sistema no debe permitir registrar cantidades superiores al stock disponible.

### Principio 4: Estándar Tecnológico Node.js
- **Runtime**: Node.js v20+ / v22+ LTS.
- **Lenguaje**: TypeScript con verificación estricta de tipos.
- **Base de Datos**: PostgreSQL 14+ utilizando el volcado oficial existente (`backup_almacen_toi20261114.sql`) con pool de conexiones optimizado.
- **Seguridad**: Rate limiting, protección CSRF/CORS, cabeceras de seguridad Helmet, cookies `HttpOnly` y `SameSite: Lax`.

### Principio 5: Operatividad y Despliegue en Coolify
- **Contenerización Nativa**: Dockerfile multi-etapa (*multi-stage build*) con usuario sin privilegios (*non-root*).
- **Docker Compose Autocontenido**: Definición con servicio de aplicación Node.js y servicio PostgreSQL con carga automática del archivo de backup.
- **Configuración mediante 12-Factor App**: Todo parámetro sensible o dependiente del entorno se gestiona vía variables de entorno (`.env`).
