# 🚀 Guía Oficial de Despliegue Desacoplado en Coolify — Almacén TOI v4.0

Esta guía describe el procedimiento paso a paso para desplegar el **Sistema de Almacén TOI v4.0** en producción utilizando la arquitectura desacoplada recomendada por **Coolify**, donde la aplicación Node.js y la base de datos PostgreSQL corren como **servicios independientes** dentro del mismo proyecto.

> [!NOTE]
> El archivo `docker-compose.yml` fue renombrado a `docker-compose.local.yml` para uso exclusivo en desarrollo local. De este modo, Coolify detecta directamente el `Dockerfile` oficial optimizado mediante **Build Pack: Dockerfile**, evitando conflictos de redes o creación de bases de datos duplicadas.

---

## 📋 Requisitos Previos

1. Instancia de **Coolify v4** activa en tu servidor VPS (Ubuntu 22.04 / 24.04).
2. Dominio o subdominio con registro DNS tipo `A` apuntando a la IP pública del VPS (ej: `almacen.toi.bo`).
3. Repositorio del proyecto conectado a Coolify (GitHub o GitLab).
4. Archivo de backup limpio generado: [`backups/almacen_toi_produccion_v1.sql`](file:///c:/Users/raul1/Desktop/soporte/proyectos%20desarrollo/almacen%20v2/backups/almacen_toi_produccion_v1.sql).

---

## 🛠️ Paso 1: Creación del Servicio de Base de Datos en Coolify

PostgreSQL se crea como un recurso independiente para garantizar la persistencia de datos y copias de seguridad aisladas del ciclo de vida del contenedor Node.js:

1. En el panel de Coolify, ingresa a tu **Project** > **Environment** (ej: `Production`).
2. Haz clic en **+ New Resource** y selecciona **Database** > **PostgreSQL**.
3. Configura los parámetros:
   - **Database Name:** `almacen_toi`
   - **Username:** `almacen_toi`
   - **Password:** *(Genera una contraseña segura y guárdala)*
   - **PostgreSQL Version:** `14-alpine` o `16-alpine`
4. Haz clic en **Save** y luego en **Start Database**.
5. En la pestaña de configuración del servicio PostgreSQL, copia el valor de **Postgres URL (internal)**, que tiene la forma:  
   `postgresql://almacen_toi:<PASSWORD>@<CONTAINER_NAME>:5432/almacen_toi`  
   *(Esta es la URL que usará la aplicación dentro de la red interna de Docker en Coolify sin exponer puertos al exterior).*

---

## 📥 Paso 2: Restauración de la Base de Datos de Producción

Una vez que el contenedor de PostgreSQL esté en estado **Running / Healthy**, procede a restaurar la estructura y los datos limpios:

### Método A: Mediante la Terminal SSH del Servidor VPS (Recomendado)
Sube el archivo `almacen_toi_produccion_v1.sql` al VPS por SCP y ejecútalo directamente contra el contenedor:

```bash
# 1. Copiar el backup limpio al VPS (desde tu terminal local)
scp backups/almacen_toi_produccion_v1.sql usuario@ip-servidor:/tmp/

# 2. Conectarte por SSH al VPS
ssh usuario@ip-servidor

# 3. Identificar el nombre del contenedor de PostgreSQL en Coolify
docker ps | grep postgres

# 4. Restaurar el backup dentro de la base de datos limpia
cat /tmp/almacen_toi_produccion_v1.sql | docker exec -i <ID_O_NOMBRE_CONTENEDOR_POSTGRES> psql -U almacen_toi -d almacen_toi
```

### Método B: Mediante cliente PostgreSQL externo (DBeaver / pgAdmin)
1. En Coolify, activa temporalmente **Public Port** en la configuración de la base de datos (puerto `5432`).
2. Conéctate con DBeaver o pgAdmin usando la IP pública del VPS y las credenciales configuradas.
3. Ejecuta el contenido completo de `almacen_toi_produccion_v1.sql`.
4. Por seguridad, deshabilita el **Public Port** al finalizar la importación.

---

## 🚀 Paso 3: Creación del Servicio de Aplicación Node.js

1. En tu mismo proyecto y entorno de Coolify, haz clic en **+ New Resource** > **Application** > **Public/Private Repository**.
2. Conecta tu repositorio de GitHub / GitLab:
   - **Repository:** `<tu-usuario>/almacen-v2`
   - **Branch:** `feature/modernizacion-nodejs` (o `main`)
   - **Build Pack:** Selecciona **Dockerfile**.
     - Coolify utilizará automáticamente el archivo [`Dockerfile`](file:///c:/Users/raul1/Desktop/soporte/proyectos%20desarrollo/almacen%20v2/Dockerfile) multi-stage optimizado del proyecto.

---

## ⚙️ Paso 4: Configuración de Variables de Entorno

En la pestaña **Environment Variables** de la aplicación en Coolify, configura:

```env
# Entorno y Puerto
NODE_ENV=production
PORT=3000

# Zona horaria oficial del servidor (Bolivia UTC-4)
TZ=America/La_Paz

# Dominio público oficial con protocolo HTTPS (sin puerto :3000)
APP_URL=https://almacen.toi.bo

# Cadena de conexión interna de Coolify (copiada en el Paso 1)
DATABASE_URL=postgresql://almacen_toi:TU_PASSWORD_AQUI@<NOMBRE_CONTENEDOR_POSTGRES>:5432/almacen_toi

# Manejo de SSL en BD (false para la red interna de Coolify; true si es base externa)
DB_SSL=false

# Llave aleatoria y segura para firma criptográfica de sesiones de usuario
SESSION_SECRET=genera_una_cadena_aleatoria_larga_y_segura_aqui_ej_64_caracteres

# Llave simétrica AES-256-GCM para cifrado de contraseñas SMTP (32 bytes = 64 hex chars)
ENCRYPTION_KEY=c2ae56f3696a3b475846e5698302d2895d03b53f39935d1a05353a4def177a85
```

> [!TIP]
> Si deseas generar una nueva `ENCRYPTION_KEY` de 32 bytes en formato hexadecimal, ejecuta:  
> `node -e "console.log(require('crypto').randomBytes(32).toString('hex'))"`

---

## 🌐 Paso 5: Configuración de Red, Dominio y Healthcheck

En la pestaña **General Configuration** de la aplicación en Coolify:

1. **Domains (FQDN):** Escribe el dominio público limpio con protocolo HTTPS:
   ```text
   https://almacen.toi.bo
   ```
   *(No agregues el puerto `:3000` en el dominio; Traefik enrutará el tráfico HTTPS en el puerto estándar 443 hacia el puerto interno 3000 de Node.js y generará automáticamente el certificado SSL Let's Encrypt).*

2. **Ports Exposes:** `3000`

3. **Healthcheck:**
   - **Healthcheck Path:** `/health`
   - **Healthcheck Interval:** `20s`
   - **Healthcheck Timeout:** `5s`
   - **Healthcheck Retries:** `3`
   - **Start Period:** `15s`

4. **Trust Proxy & Seguridad de Sesión:**  
   La aplicación tiene configurado `app.set('trust proxy', 1)` y `secure: 'auto'` en `src/app.ts`, lo que permite:
   - Que Traefik comunique la IP pública real del cliente (evitando bloqueos cruzados en el Rate Limiter de 15 intentos).
   - Que las cookies de sesión se emitan con la directiva `Secure` al servirse bajo HTTPS.

---

## 🚢 Paso 6: Despliegue y Verificación en Logs

1. Haz clic en **Deploy** en la esquina superior derecha.
2. Sigue los logs de construcción en tiempo real:
   - Se compila TypeScript con `npm run build` y se copian `dist/views` y `dist/public`.
   - Se crea la imagen ligera con usuario no privilegiado `USER node`.
3. Al arrancar, verifica en los logs del contenedor la confirmación de base de datos desacoplada:
   ```text
   =======================================================
   🚀 Sistema de Almacén TOI v4.0 (Node.js & TypeScript)
   🌐 Servidor iniciado en: https://almacen.toi.bo
   📡 Puerto de escucha: 3000
   ⚙️  Entorno: production
   =======================================================
   ✅ Base de datos conectada: [Host: <NOMBRE_CONTENEDOR_POSTGRES>] [Base: almacen_toi]
   🕒 Zona horaria activa: America/La_Paz
   ```
4. El healthcheck en `/health` responderá con HTTP 200 y el estado del servicio pasará a **Healthy**.
5. Ingresa a `https://almacen.toi.bo/auth/login`:
   - **Usuario:** `admin`
   - **Contraseña:** `Admin123!`

---

## 🎯 Paso 7: Recomendaciones para la Prueba Piloto con Almacén

Para iniciar con éxito el periodo de prueba piloto con el personal operativo de bodega:

1. **Creación de Cuentas Individuales para el Personal:**
   - Ingresa al menú **Administración > Gestión de Usuarios**.
   - Crea cuentas nominativas para cada operario y técnico con el rol **Usuario (Operador)**.
   - *Nunca compartas la cuenta `admin` para operaciones rutinarias*; de esta forma, cada despacho, salida y movimiento quedará registrado con el nombre e IP exacta del responsable en la bitácora de auditoría.
   - Cambia la contraseña por defecto del usuario `admin`.

2. **Verificación de la Identidad Institucional:**
   - Ingresa a **Administración > Configuración General**.
   - Confirma que la Razón Social, NIT, Dirección, Teléfono y Pie de Firma correspondan exactamente a los datos que deben imprimirse en los comprobantes PDF.
   - Verifica que los correos de notificación de stock bajo (`alertas_emails`) apunten a los jefes de almacén y compras.

3. **Prueba Integral de Despacho en Bodega:**
   - Ingresa a **Despacho y Operaciones > + Nuevo Despacho**.
   - Realiza un despacho real seleccionando una cuadrilla o destino oficial (ej. `INSTALACIONES`).
   - Agrega un equipo escaneando su Serie/MAC y un accesorio por cantidad.
   - Confirma la orden y descarga el Comprobante PDF unificado (`/despachos/comprobante/:id`).
   - Imprime el comprobante en la impresora de bodega y verifica la legibilidad de las firmas y membrete.

4. **Programación de Respaldos Diarios:**
   - En el recurso de PostgreSQL en Coolify, activa la pestaña **Backups**.
   - Configura una política de respaldo automática diaria (ejemplo cron: `0 3 * * *` a las 03:00 AM) almacenada en almacenamiento local o S3 compatible.

---

## 🆘 Resolución de Problemas Frecuentes

- **Error al iniciar sesión (Cookies no persisten):**  
  Asegúrate de que `APP_URL` comience con `https://` y que el certificado SSL de Traefik se haya emitido correctamente en Coolify. Si accedes por HTTP en desarrollo, el navegador rechazará la cookie segura.
- **Error de conexión a PostgreSQL:**  
  Verifica que el nombre del host en `DATABASE_URL` coincida con el nombre del contenedor de la base de datos dentro de la red interna de Docker de Coolify.
- **La zona horaria de los reportes no coincide:**  
  Verifica que la variable de entorno `TZ=America/La_Paz` esté configurada en la aplicación en Coolify y que la zona horaria en **Administración > Configuración General** esté en `America/La_Paz`.
