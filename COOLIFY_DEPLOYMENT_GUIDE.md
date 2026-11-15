# 🚀 Guía de Despliegue en Coolify (Almacén TOI v4.0)

La documentación oficial completa y detallada para el despliegue desacoplado en Coolify se encuentra en:

👉 **[`docs/GUIA_DESPLIEGUE_COOLIFY.md`](docs/GUIA_DESPLIEGUE_COOLIFY.md)**

---

### Resumen Rápido de Arquitectura Desacoplada:
- **Build Pack en Coolify:** `Dockerfile` (puerto `3000`).
- **Base de Datos:** Recurso independiente tipo `PostgreSQL` en el mismo proyecto de Coolify.
- **Conexión Interna:** Variable de entorno `DATABASE_URL` (usando el formato interno provisto por Coolify sin exponer puertos al exterior).
- **Restauración Inicial:** [`backups/almacen_toi_produccion_v1.sql`](backups/almacen_toi_produccion_v1.sql).
- **Desarrollo Local:** `docker-compose.local.yml`.
