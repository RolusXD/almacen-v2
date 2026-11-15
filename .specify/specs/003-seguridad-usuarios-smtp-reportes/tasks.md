# TASKS: Seguridad, RBAC, Usuarios, SMTP y Centro de Reportes
<!-- Spec Kit Tasks Document: SPEC-003 -->

**Especificación:** [`SPEC-003`](file:///c:/Users/raul1/Desktop/soporte/proyectos%20desarrollo/almacen%20v2/.specify/specs/003-seguridad-usuarios-smtp-reportes/spec.md)  
**Plan Técnico:** [`plan.md`](file:///c:/Users/raul1/Desktop/soporte/proyectos%20desarrollo/almacen%20v2/.specify/specs/003-seguridad-usuarios-smtp-reportes/plan.md)  
**Skills Aplicadas:** `.agents/skills/nodejs-security-audit/SKILL.md` y `.agents/skills/wms-ui-pro/SKILL.md`  

---

## FASE 1: Auditoría de Seguridad y Control de Acceso (Skill: nodejs-security-audit)

- [x] **TASK-SEC-01**: [P] Auditar el 100% de las consultas SQL en repositorios (`src/repositories/`) para erradicar cualquier riesgo de inyección SQL, asegurando el uso exclusivo de parámetros `$1, $2, ...`.
- [x] **TASK-SEC-02**: [P] Auditar todas las plantillas EJS en `src/views/` para verificar que la interpolación de datos de usuario use `<%= %>` con escape automático contra XSS.
- [x] **TASK-SEC-03**: Instalar y configurar `express-rate-limit` en `src/app.ts`, protegiendo `/auth/login` y rutas de autenticación contra ataques de fuerza bruta (máximo 5 intentos cada 15 min).
- [x] **TASK-SEC-04**: Configurar cabeceras de seguridad HTTP con `helmet` y validar los atributos de las cookies de sesión (`httpOnly: true`, `sameSite: 'lax'`).
- [x] **TASK-SEC-05**: Implementar el middleware centralizado de Control de Acceso Basado en Roles (RBAC) en `src/middlewares/auth.middleware.ts` con funciones `requireRole(['admin'])` y `requireAdmin`.
- [x] **TASK-SEC-06**: Proteger con `requireAdmin` todas las rutas administrativas de gestión (`/usuarios`, rutas de eliminación en `/equipos`, `/accesorios` y `/maestros`).
- [x] **TASK-SEC-07**: Generar el documento formal de auditoría de seguridad [`.specify/specs/003-seguridad-usuarios-smtp-reportes/vulnerabilities-audit.md`](file:///c:/Users/raul1/Desktop/soporte/proyectos%20desarrollo/almacen%20v2/.specify/specs/003-seguridad-usuarios-smtp-reportes/vulnerabilities-audit.md) clasificando los hallazgos por severidad.

---

## FASE 2: Gestión de Usuarios y Servidor SMTP Transaccional

- [x] **TASK-USR-01**: [DEP: TASK-SEC-05] Ampliar el modelo y repositorio de usuarios (`UsuarioRepository`) con soporte para actualizar datos de cuenta, cambiar rol y alternar el estado `activo` (1 / 0).
- [x] **TASK-USR-02**: [DEP: TASK-USR-01] Refactorizar la vista `src/views/usuarios/index.ejs` agregando modales flotantes centrados (`modal fade modal-dialog-centered`) para editar datos, cambiar rol y resetear contraseñas con hash `bcrypt`.
- [x] **TASK-USR-03**: [DEP: TASK-USR-02] Implementar la destrucción forzada de sesión en `attachUserMiddleware` cuando una cuenta sea marcada como inactiva (`activo = 0`).
- [x] **TASK-SMTP-01**: Crear la migración SQL para la tabla `configuracion_smtp` con campos para host, puerto, usuario, password cifrado, seguridad TLS/SSL y datos de remitente.
- [x] **TASK-SMTP-02**: Desarrollar el servicio criptográfico `src/services/crypto.service.ts` para cifrado y descifrado simétrico de contraseñas con `AES-256-GCM` y clave `ENCRYPTION_KEY`.
- [x] **TASK-SMTP-03**: [DEP: TASK-SMTP-01, TASK-SMTP-02] Crear el repositorio `SmtpRepository` y el controlador `ConfigController` para consultar y actualizar la configuración SMTP.
- [x] **TASK-SMTP-04**: [DEP: TASK-SMTP-03] Desarrollar la vista `src/views/configuracion/smtp.ejs` en AdminLTE 4 con formulario protegido y botón interactivo de prueba de conexión (`/configuracion/smtp/test`).
- [x] **TASK-SMTP-05**: [DEP: TASK-SMTP-03] Desarrollar el servicio de correo `src/services/mail.service.ts` utilizando `nodemailer`.
- [x] **TASK-SMTP-06**: [DEP: TASK-SMTP-05] Implementar el flujo de **Recuperación de Contraseña**:
  - Enlace *"¿Olvidaste tu contraseña?"* en login (`/auth/forgot-password`).
  - Generación de token criptográfico seguro de 15 minutos guardado con hash SHA-256 en `usuarios.reset_token`.
  - Despacho de correo HTML con enlace de reseteo.
  - Pantalla `/auth/reset-password` para registrar la nueva clave y anular el token.
- [x] **TASK-SMTP-07**: [DEP: TASK-SMTP-05] Implementar el hook de **Alerta por Stock Crítico**: al completarse una salida de inventario cuyo saldo sea `<= COALESCE(stock_minimo, 10)`, enviar automáticamente un correo en background a los supervisores.

---

## FASE 3: Reportes en Despacho y Centro de Reportes por Rol

- [x] **TASK-REP-01**: Instalar y configurar `pdfkit` y `exceljs` para la generación de documentos PDF y hojas de cálculo Excel en el servidor.
- [x] **TASK-REP-02**: [DEP: TASK-REP-01] Desarrollar el servicio `src/services/pdf.service.ts` para renderizar el **Comprobante de Entrega / Remisión de Despacho**:
  - Membrete formal de Almacén TOI con número de remisión único.
  - Detalle de series/MACs, modelos, accesorios y cantidades despachadas.
  - Destino, responsable, operador emisor y recuadros formales de firma (*Entregado por* y *Recibido Conforme*).
- [x] **TASK-REP-03**: [DEP: TASK-REP-02] Conectar el registro de salidas de equipos y accesorios con la generación del comprobante en la ruta `GET /movimientos/comprobante/:id`.
- [x] **TASK-REP-04**: [DEP: TASK-REP-03] Implementar la validación **Anti-IDOR estricta**: un usuario con rol `usuario` solo puede descargar comprobantes donde `created_by == session.userId`, mientras que el `admin` puede descargar cualquiera.
- [x] **TASK-REP-05**: Desarrollar el **Centro de Reportes Multi-criterio** (`src/views/reportes/index.ejs` y `ReportesController`):
  - **Para Administradores**: Vista con todos los filtros habilitados (rango de fechas, tipos de movimiento, categorías, destinos, operadores) con exportación a PDF y Excel.
  - **Para Usuarios**: Vista adaptada a *"Mis Movimientos"* donde la consulta SQL inyecta forzosamente `WHERE created_by = $userId` proveniente de la sesión activa.
- [x] **TASK-REP-06**: [DEP: TASK-SMTP-05, TASK-REP-02] Agregar la opción de enviar por correo electrónico el comprobante de entrega o reporte generado como archivo adjunto.

---

## FASE 4: Pulido Visual y Ergonomía (Skill: wms-ui-pro)

- [x] **TASK-UI-01**: Homologar todas las nuevas pantallas (`/usuarios`, `/configuracion/smtp`, `/reportes`, `/auth/forgot-password`) con AdminLTE 4 / Bootstrap 5.3, garantizando que usen clases semánticas (`bg-body`, `bg-body-tertiary`, `text-body`).
- [x] **TASK-UI-02**: Verificar que todas las nuevas vistas mantengan contraste perfecto y legibilidad al alternar entre **Modo Claro**, **Modo Noche** y **Alto Contraste**.
- [x] **TASK-UI-03**: Corregir el dimensionamiento de las columnas en la tabla *"Últimos Movimientos Registrados"* en el Dashboard (`src/views/dashboard/index.ejs`), asignando ancho mínimo y estilo de contenedor para que la columna **"DESTINO"** nunca se corte ni se desborde horizontalmente.
- [x] **TASK-UI-04**: Verificar que todas las tablas ocupen el 100% del ancho (`col-12`) y que todos los formularios de acción operen mediante modales flotantes centrados.
