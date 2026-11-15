# REPORTE DE AUDITORÍA DE SEGURIDAD ESTÁTICA (SAST) Y PLAN DE HARDENING
<!-- Spec Kit Security Audit Document: SPEC-003 -->

**Fecha de Ejecución:** Noviembre 2026  
**Skill Aplicada:** [`.agents/skills/nodejs-security-audit/SKILL.md`](file:///c:/Users/raul1/Desktop/soporte/proyectos%20desarrollo/almacen%20v2/.agents/skills/nodejs-security-audit/SKILL.md)  
**Alcance:** Código fuente Node.js / Express / PostgreSQL del Sistema de Almacén TOI.  

---

## 1. Resumen de Hallazgos

| ID | Categoría OWASP | Severidad | Componente / Archivo | Estado |
| :--- | :--- | :---: | :--- | :---: |
| **SEC-01** | A01:2021 - Broken Access Control (RBAC) | **ALTA** | `src/app.ts`, `src/controllers/` | Pendiente de Mitigación (Fase 1) |
| **SEC-02** | A07:2021 - Identification & Authentication Failures | **ALTA** | `/auth/login`, `src/app.ts` | Pendiente de Mitigación (Fase 1) |
| **SEC-03** | A01:2021 - Insecure Direct Object References (IDOR) | **ALTA** | `ReportesController`, `MovimientosController` | Pendiente de Mitigación (Fase 1/3) |
| **SEC-04** | A05:2021 - Security Misconfiguration (Helmet CSP) | **MEDIA** | `src/app.ts` | Pendiente de Mitigación (Fase 1) |
| **SEC-05** | A02:2021 - Cryptographic Failures (Secrets in DB) | **ALTA** | Módulo SMTP (Por implementar) | Especificado con AES-256-GCM (Fase 2) |
| **SEC-06** | A03:2021 - Injection (SQL Injection) | **BAJA** | `src/repositories/` | Verificado (Consultas Parametrizadas) |

---

## 2. Detalle de Vulnerabilidades y Soluciones Requeridas

### SEC-01: Control de Acceso Incompleto en Rutas Sensibles (RBAC)
- **Severidad:** **ALTA**
- **Descripción:** Actualmente, el middleware `requireAdmin` solo protege `/usuarios`. Rutas sensibles como `/equipos/eliminar/:id`, `/accesorios/eliminar/:id`, `/maestros/tipos/eliminar/:id`, `/maestros/categorias/eliminar/:id` y `/maestros/proveedores/eliminar/:id` no validan el rol de administrador, permitiendo que cualquier usuario autenticado con rol `usuario` elimine registros de inventario o catálogos maestros.
- **Solución Obligatoria:** Implementar `requireRole(['admin'])` y proteger todas las rutas mutacionales y destructivas en `src/app.ts`.

---

### SEC-02: Ausencia de Rate Limiting en Endpoints de Autenticación
- **Severidad:** **ALTA**
- **Descripción:** La ruta `/auth/login` carece de limitación de tasa (*rate limiting*). Un atacante puede ejecutar ataques de fuerza bruta automatizados o ataques de diccionario contra contraseñas de usuarios sin que el servidor limite los intentos fallidos.
- **Solución Obligatoria:** Implementar `express-rate-limit` restringiendo a un máximo de 5 intentos por ventana de 15 minutos por IP en `/auth/login` y `/auth/forgot-password`.

---

### SEC-03: Exposición Potencial a IDOR en Reportes y Comprobantes
- **Severidad:** **ALTA**
- **Descripción:** En la descarga de reportes y futuros comprobantes de entrega, si un usuario operador pasa parámetros en la query o URL para consultar datos de otro usuario, el sistema podría responder si no se valida explícitamente la titularidad del registro en el backend.
- **Solución Obligatoria:** En el backend, las consultas para el rol `usuario` deben inyectar forzosamente `WHERE created_by = $userId` tomando el valor directamente de `req.session.userId`, ignorando cualquier parámetro del cliente.

---

### SEC-04: Ajuste de Cabeceras HTTP en Helmet
- **Severidad:** **MEDIA**
- **Descripción:** En `src/app.ts`, `helmet({ contentSecurityPolicy: false })` tiene CSP deshabilitado por compatibilidad con CDNs y scripts inline.
- **Solución Obligatoria:** Mantener CSP flexible para CDNs locales pero garantizar que el resto de protecciones (`xContentTypeOptions`, `dnsPrefetchControl`, `frameguard: { action: 'sameorigin' }`, `hidePoweredBy`) permanezcan activas y configuradas de manera explícita.

---

### SEC-05: Cifrado Requerido para Contraseñas de Correo SMTP
- **Severidad:** **ALTA**
- **Descripción:** Al implementar la configuración del servidor SMTP, almacenar la contraseña del buzón en texto plano en la base de datos representaría un riesgo crítico en caso de volcado o acceso no autorizado a PostgreSQL.
- **Solución Obligatoria:** Implementar cifrado simétrico robusto mediante `AES-256-GCM` utilizando una clave secreta (`ENCRYPTION_KEY`) de 32 bytes provista en `.env`.
