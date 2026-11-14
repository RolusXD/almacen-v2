# Despliegue en Vercel

Este proyecto es PHP MVC. Vercel ejecuta PHP mediante el runtime comunitario `vercel-php`, configurado en `vercel.json`.

## Variables de entorno

Configurar en Vercel Project Settings > Environment Variables:

```env
APP_ENV=production
APP_URL=https://tu-dominio.vercel.app
DB_DRIVER=pgsql
DB_HOST=host-remoto-postgresql
DB_PORT=5432
DB_NAME=almacen_toi
DB_USER=usuario
DB_PASS=password
DB_SSLMODE=require
SESSION_DRIVER=database
```

También puede usarse una sola variable `DATABASE_URL` con formato:

```env
DATABASE_URL=postgresql://usuario:password@host-remoto-postgresql:5432/almacen_toi?sslmode=require
```

Si usa Vercel Postgres/Neon mediante integración, el proyecto también acepta las variables generadas por el proveedor: `POSTGRES_URL`, `POSTGRES_HOST`, `POSTGRES_DATABASE`, `POSTGRES_USER`, `POSTGRES_PASSWORD` y `POSTGRES_PORT`.

## Base de datos

Vercel no incluye base de datos local. Usar una base PostgreSQL externa accesible por red, por ejemplo Vercel Postgres, Neon, Supabase, Railway, Aiven o DigitalOcean Managed PostgreSQL.

El manejador de sesiones crea automáticamente la tabla `app_sessions` cuando `SESSION_DRIVER=database`.

Para crear la estructura y los datos iniciales, ejecutar `database/almacen_toi_postgresql.sql` en la consola SQL del proveedor o con:

```bash
psql "$DATABASE_URL" -f database/almacen_toi_postgresql.sql
```

## Notas

- `.htaccess` no se usa en Vercel; las rutas están en `vercel.json`.
- Los assets se sirven desde `/assets/*`.
- Todo lo demás se envía a `api/index.php`, que carga la aplicación existente.
- Los reportes PDF requieren instalar TCPDF con Composer si se van a usar en producción.

## Error común

Si aparece `Error de conexión a la base de datos`, revisar:

1. `DB_HOST` no debe ser `localhost`.
2. La base PostgreSQL debe aceptar conexiones externas desde Vercel.
3. El usuario debe tener permisos sobre la base `almacen_toi`.
4. Si el proveedor exige SSL, configurar `DB_SSLMODE=require`.
5. Activar temporalmente `DB_DEBUG=true` solo para diagnóstico y retirarlo después.
