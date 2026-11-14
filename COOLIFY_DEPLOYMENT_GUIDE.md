# 🚀 GUÍA DE DESPLIEGUE EN COOLIFY (Almacén TOI v4.0)

Esta guía explica paso a paso cómo desplegar el **Sistema de Almacén TOI v4.0** en **Coolify** de forma automatizada mediante Git y Docker Compose.

---

## 📋 Métodos de Despliegue en Coolify

Tienes **2 opciones**:
1. **Opción A (Recomendada - Autocontenida)**: Desplegar mediante **Docker Compose** en Coolify (crea la aplicación Node.js y la base de datos PostgreSQL 14 automáticamente, importando los datos de `backup_almacen_toi20261114.sql`).
2. **Opción B**: Conectar a una base de datos PostgreSQL existente (externa o creada previamente en Coolify) y desplegar la app mediante el **Dockerfile**.

---

## Opción A: Despliegue con Docker Compose (1 solo clic)

1. **En el panel de Coolify**:
   - Ingresa a tu Proyecto / Ambiente.
   - Haz clic en **+ New Service** o **+ New Resource**.
   - Selecciona **Docker Compose**.
2. **Origen del Código**:
   - Selecciona tu repositorio de GitHub / GitLab.
   - En **Branch**, selecciona: `feature/modernizacion-nodejs`.
   - El archivo Compose se detectará automáticamente (`docker-compose.yml`).
3. **Variables de Entorno (Environment Variables)**:
   Configura en la pestaña de variables:
   ```env
   PORT=3000
   APP_URL=https://almacen.tu-dominio.com
   SESSION_SECRET=genera_una_clave_aleatoria_muy_segura_aqui
   DB_NAME=almacen_toi
   DB_USER=almacen_toi
   DB_PASS=tu_clave_segura_de_postgres
   ```
4. **Dominio & SSL**:
   - En la configuración del servicio `app`, asigna tu dominio (ej: `https://almacen.toi.bo`). Coolify generará automáticamente los certificados SSL con Let's Encrypt mediante Traefik.
5. **Haz clic en "Deploy"**:
   - PostgreSQL 14 se iniciará y cargará automáticamente los esquemas y datos desde `backup_almacen_toi20261114.sql`.
   - Node.js compilará el código TypeScript y se iniciará en el puerto 3000.
   - El healthcheck integrado en `/health` verificará la disponibilidad del servicio.

---

## Opción B: Despliegue con Dockerfile (BD Externa)

Si ya tienes un PostgreSQL corriendo en Coolify o en un servidor dedicado:

1. **Crea el recurso tipo "Public/Private Repository"** en Coolify.
2. Selecciona la rama `feature/modernizacion-nodejs` y tipo de build **Dockerfile**.
3. En las **Variables de Entorno**, define la cadena de conexión:
   ```env
   NODE_ENV=production
   PORT=3000
   APP_URL=https://almacen.tu-dominio.com
   SESSION_SECRET=tu_clave_secreta_aqui
   DATABASE_URL=postgresql://usuario:password@tu-postgres-host:5432/almacen_toi
   ```
4. Despliega y listo.

---

## 🔐 Credenciales Iniciales de Acceso

- **URL de Acceso**: `https://tu-dominio/auth/login`
- **Usuario**: `admin`
- **Contraseña**: `Admin123!`

*(Al iniciar sesión como admin, puedes crear nuevos usuarios o editar las contraseñas en el módulo de Usuarios).*

---

## 🛠️ Comandos de Desarrollo Local

Si deseas probar el proyecto localmente antes de desplegar:

```bash
# 1. Instalar dependencias
npm install

# 2. Ejecutar en modo desarrollo con recarga en caliente
npm run dev

# 3. Compilar para producción
npm run build

# 4. Iniciar en modo producción
npm start
```
