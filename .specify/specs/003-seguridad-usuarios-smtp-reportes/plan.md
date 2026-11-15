# TECHNICAL PLAN: Seguridad, RBAC, Usuarios, SMTP y Centro de Reportes
<!-- Spec Kit Plan Document: SPEC-003 -->

**Especificación Asociada:** `SPEC-003`  
**Objetivo Técnico:** Plan de implementación modular y secuencial para dotar al Sistema de Almacén TOI de seguridad OWASP, control de acceso por roles (RBAC), gestión de usuarios y credenciales, servicio de correo SMTP transaccional y centro de reportes con comprobantes de entrega en PDF.  

---

## 1. Arquitectura Técnica y Nuevos Componentes

```
src/
├── config/
│   ├── env.ts                 # Variables de entorno actualizadas (ENCRYPTION_KEY, SMTP defaults)
├── controllers/
│   ├── auth.controller.ts      # Flujo de recuperación de contraseñas (forgot & reset password)
│   ├── usuarios.controller.ts  # CRUD de usuarios ampliado (edición, roles, activar/desactivar)
│   ├── config.controller.ts    # [NUEVO] Controlador de configuración del sistema y servidor SMTP
│   ├── movimientos.controller.ts # Inyección de created_by y enlace a comprobante PDF
│   └── reportes.controller.ts  # Centro de reportes con filtros multi-criterio y restricción RBAC
├── db/
│   ├── migrations/
│   │   └── 003_smtp_and_audit.sql # [NUEVO] DDL tabla configuracion_smtp e índices de rendimiento
├── middlewares/
│   ├── auth.middleware.ts      # Middlewares declarativos RBAC (requireRole, requireAdmin)
│   └── rate-limit.middleware.ts # [NUEVO] Limitadores de tasa para autenticación y envíos
├── models/
│   └── types.ts               # Interfaces TypeScript para SMTP, Comprobante y Reporte
├── repositories/
│   ├── usuario.repository.ts   # Métodos para update, toggleActive, setResetToken, findByToken
│   ├── smtp.repository.ts      # [NUEVO] Persistencia y lectura de configuracion_smtp
│   └── reportes.repository.ts  # [NUEVO] Consultas dinámicas multi-criterio y por usuario (Anti-IDOR)
├── services/
│   ├── crypto.service.ts       # [NUEVO] Cifrado y descifrado simétrico AES-256-GCM
│   ├── mail.service.ts         # [NUEVO] Envíos Nodemailer (prueba, reset de clave, alertas stock)
│   └── pdf.service.ts          # [NUEVO] Generación en memoria de comprobantes de despacho y reportes
└── views/
    ├── auth/
    │   ├── forgot-password.ejs # [NUEVO] Solicitud de enlace de recuperación
    │   └── reset-password.ejs  # [NUEVO] Cambio seguro de contraseña con token
    ├── configuracion/
    │   └── smtp.ejs            # [NUEVO] Formulario y modal de prueba de correo
    ├── dashboard/
    │   └── index.ejs           # Ajuste de ancho de columna DESTINO
    ├── reportes/
    │   └── index.ejs           # [NUEVO] Centro de reportes interactivo por rol
    └── usuarios/
        └── index.ejs           # Modal de nuevo usuario, edición y control de estado
```

---

## 2. Dependencias y Paquetes NPM Requeridos

Se instalarán las dependencias oficiales de grado de producción necesarias:

```bash
npm install express-rate-limit nodemailer pdfkit
npm install --save-dev @types/nodemailer @types/pdfkit
```

1. **`express-rate-limit`**: Protección contra ataques de fuerza bruta en `/auth/login` y saturación de peticiones en endpoints de envío de correos.
2. **`nodemailer`**: Cliente SMTP estándar y confiable para transporte de correos con soporte TLS/SSL y adjuntos en Node.js.
3. **`pdfkit`**: Motor nativo server-side para generación directa de documentos PDF formateados (remisiones de despacho con tablas, logos y firmas) sin dependencias de navegadores headless pesados.

---

## 3. Modelo de Datos y Migración SQL

### 3.1. Tabla `configuracion_smtp`
```sql
CREATE TABLE IF NOT EXISTS configuracion_smtp (
  id SERIAL PRIMARY KEY,
  host VARCHAR(255) NOT NULL,
  puerto INTEGER NOT NULL DEFAULT 587,
  usuario VARCHAR(255),
  password_cifrado TEXT,
  secure BOOLEAN NOT NULL DEFAULT FALSE,
  remitente_email VARCHAR(255) NOT NULL,
  remitente_nombre VARCHAR(255) NOT NULL DEFAULT 'Almacén TOI',
  activo BOOLEAN NOT NULL DEFAULT TRUE,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

### 3.2. Optimización e Índices de Auditoría
```sql
-- Asegurar índices para acelerar consultas multi-criterio y validaciones RBAC/Anti-IDOR
CREATE INDEX IF NOT EXISTS idx_mov_equipos_created_by ON movimientos_equipos(created_by);
CREATE INDEX IF NOT EXISTS idx_mov_accesorios_created_by ON movimientos_accesorios(created_by);
CREATE INDEX IF NOT EXISTS idx_usuarios_reset_token ON usuarios(reset_token);
```

---

## 4. Secuencia Estricta de Implementación por Fases

Para evitar retrabajo de código, los desarrollos deben realizarse en este orden cronológico exacto:

### Fase 1: Auditoría SAST, Hardening de Express y Middleware RBAC
1. **Auditoría de Consultas SQL**: Revisar línea por línea cada consulta en `src/repositories/` garantizando el uso de parámetros `$1, $2, ...`.
2. **Configuración de Seguridad en `src/app.ts`**:
   - Ajuste de cabeceras seguras con `helmet`.
   - Implementación de `express-rate-limit` en rutas de autenticación.
   - Verificación de atributos de sesión `httpOnly: true`, `sameSite: 'lax'`.
3. **Desarrollo del Middleware RBAC en `src/middlewares/auth.middleware.ts`**:
   - Creación de `requireRole(['admin'])` y `requireRole(['admin', 'usuario'])`.
   - Protección de rutas administrativas actuales (`/usuarios`, rutas de eliminación).
4. **Documentación de Auditoría**: Generación del informe `vulnerabilities-audit.md`.

### Fase 2: Gestión de Usuarios y Subsistema de Correo SMTP
1. **Servicio Criptográfico `crypto.service.ts`**: Implementación de cifrado simétrico `AES-256-GCM` con clave de 256 bits (`ENCRYPTION_KEY`).
2. **Repositorio y Servicio SMTP (`smtp.repository.ts`, `mail.service.ts`)**:
   - Lectura y almacenamiento persistente de parámetros de conexión.
   - Inicialización dinámica del transporte Nodemailer con descifrado en memoria.
   - Endpoint de prueba de conexión `POST /configuracion/smtp/test`.
3. **Flujo de Recuperación de Contraseña**:
   - Endpoints `GET/POST /auth/forgot-password` y `GET/POST /auth/reset-password`.
   - Generación de token seguro con hash SHA-256 y expiración en 15 minutos.
   - Plantilla HTML para el correo de reseteo.
4. **Alerta Automática por Stock Crítico**:
   - Hook posterior a la inserción en `movimientos.controller.ts` para evaluar `stock <= 10` y despachar notificación en background.
5. **Módulo de Usuarios en AdminLTE 4**:
   - Formulario de alta y edición dentro de modal flotante.
   - Acción rápida para activar/desactivar cuentas.

### Fase 3: Reportes de Despacho (PDF) y Centro de Reportes por Rol
1. **Servicio Generador de PDF `pdf.service.ts`**:
   - Formato formal de remisión de entrega (membrete institucional, número de comprobante, series/cantidades, destino, operador y recuadros de firma).
2. **Generación de Comprobante en Despacho**:
   - Ruta `GET /movimientos/comprobante/:id` validando que pertenezca al usuario emisor o a un administrador (Anti-IDOR).
   - Enlace automático en la alerta de éxito al registrar cualquier salida.
3. **Centro de Reportes Unificado (`/reportes`)**:
   - **Para Administradores**: Vista con todos los filtros habilitados (rango de fechas, tipos de movimiento, categorías, destinos, usuarios). Exportación en PDF y Excel.
   - **Para Usuarios Operadores**: Vista titulada *"Mis Movimientos"* donde el backend fuerza la condición `WHERE created_by = $userId`.
4. **Envío Opcional por Correo**: Opción de despachar el reporte o comprobante generado como archivo adjunto.

### Fase 4: Pulido Visual WMS UI Pro y Ergonomía
1. **Homologación de Estilos**:
   - Verificar que `/usuarios`, `/configuracion/smtp`, `/reportes` y `/auth/forgot-password` usen exclusivamente clases de AdminLTE 4 y Bootstrap 5.3 (`bg-body`, `bg-body-tertiary`, `text-body`).
   - Probar que el renderizado sea perfecto en **Modo Claro**, **Modo Noche** y **Alto Contraste**.
2. **Ajuste de la Tabla de Movimientos en el Dashboard**:
   - En `src/views/dashboard/index.ejs`, corregir el dimensionamiento de las columnas de la tabla *"Últimos Movimientos Registrados"* fijando un ancho mínimo de columna y envoltorio de badge para que la columna **"DESTINO"** nunca se corte en ninguna resolución.

---

## 5. Estrategia de Pruebas y Validación de Seguridad

1. **Prueba Anti-IDOR**:
   - Iniciar sesión con un usuario estándar (`rol: usuario`).
   - Intentar descargar el comprobante de un movimiento registrado por otro usuario (`GET /movimientos/comprobante/:otroId`).
   - *Resultado esperado*: El servidor responde con código `403 Forbidden` y bloquea la entrega del PDF.
2. **Prueba Anti-Fuerza Bruta**:
   - Ejecutar un script de 6 peticiones POST erróneas consecutivas a `/auth/login`.
   - *Resultado esperado*: A partir de la 6ta petición, el servidor responde con código `429 Too Many Requests`.
3. **Prueba de Cifrado SMTP**:
   - Consultar directamente la base de datos `SELECT password_cifrado FROM configuracion_smtp`.
   - *Resultado esperado*: La columna contiene únicamente un hash/payload hexadecimal ilegible con formato `iv:tag:ciphertext`.
4. **Prueba Multi-Tema**:
   - Abrir `/reportes`, `/usuarios` y `/configuracion/smtp` alternando entre Modo Claro, Modo Noche y Alto Contraste en el Navbar, confirmando ausencia de cajas blancas fijas o textos ilegibles.
