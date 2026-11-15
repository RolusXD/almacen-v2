# 🚀 Guía Oficial de Despliegue en Coolify — Almacén TOI v4.0

Esta guía describe el procedimiento paso a paso para desplegar el **Sistema de Almacén TOI v4.0** en producción utilizando **Coolify**, incluyendo el aprovisionamiento de PostgreSQL, la restauración del backup consolidado limpio, la configuración de variables de entorno, puertos, healthcheck y recomendaciones para la prueba piloto.

---

## 📋 Requisitos Previos

1. Instancia de **Coolify** activa en tu servidor VPS (Ubuntu 22.04 / 24.04).
2. Dominio o subdominio apuntando mediante registro DNS tipo `A` a la dirección IP pública del VPS (por ejemplo: `almacen.toi.bo`).
3. Repositorio de código accesible desde Coolify (GitHub o GitLab).
4. Archivo de backup limpio generado: [`backups/almacen_toi_produccion_v1.sql`](file:///c:/Users/raul1/Desktop/soporte/proyectos%20desarrollo/almacen%20v2/backups/almacen_toi_produccion_v1.sql).

---

## 🛠️ Paso 1: Creación del Servicio de Base de Datos en Coolify

Se recomienda crear PostgreSQL como un recurso de base de datos independiente en Coolify para garantizar persistencia y respaldos automáticos aislados del ciclo de vida del contenedor de la aplicación.

1. En el panel de Coolify, ingresa a tu **Project** > **Environment** (ej: `Production`).
2. Haz clic en **+ New Resource** y selecciona **Database** > **PostgreSQL**.
3. Configura los parámetros de la base de datos:
   - **Database Name:** `almacen_toi`
   - **Username:** `almacen_toi`
   - **Password:** *(Genera una contraseña segura y guárdala)*
   - **PostgreSQL Version:** `14-alpine` o `16-alpine`
4. Haz clic en **Save** y luego en **Start Database**.
5. Copia la URL interna de conexión generada por Coolify (ejemplo: `postgresql://almacen_toi:<PASSWORD>@<CONTAINER_NAME>:5432/almacen_toi`).

---

## 📥 Paso 2: Restauración de la Base de Datos de Producción

Una vez que el contenedor de PostgreSQL esté en estado **Running / Healthy**, procede a restaurar la estructura y los datos limpios:

### Método A: Mediante la Terminal SSH del Servidor VPS (Recomendado)
Sube el archivo `almacen_toi_produccion_v1.sql` a tu servidor por SCP o SFTP y ejecútalo directamente contra el contenedor:

```bash
# 1. Copiar el archivo al VPS (desde tu máquina local)
scp backups/almacen_toi_produccion_v1.sql usuario@ip-servidor:/tmp/

# 2. Conectarte por SSH al VPS
ssh usuario@ip-servidor

# 3. Identificar el nombre del contenedor de Postgres en Coolify
docker ps | grep postgres

# 4. Restaurar el backup dentro de la base de datos limpia
cat /tmp/almacen_toi_produccion_v1.sql | docker exec -i <ID_O_NOMBRE_CONTENEDOR_POSTGRES> psql -U almacen_toi -d almacen_toi
```

### Método B: Mediante cliente PostgreSQL externo (DBeaver / pgAdmin)
1. En Coolify, habilita temporalmente **Public Port** en la configuración de la base de datos (por ejemplo puerto `5432`).
2. Conéctate con DBeaver o pgAdmin usando la IP pública del VPS y las credenciales configuradas.
3. Abre el archivo `almacen_toi_produccion_v1.sql` en el editor SQL y ejecútalo completo.
4. Por seguridad, deshabilita el **Public Port** tras finalizar la importación.

---

## 🚀 Paso 3: Creación y Conexión de la Aplicación Node.js

1. En tu mismo proyecto de Coolify, haz clic en **+ New Resource** > **Application** > **Public/Private Repository**.
2. Conecta tu repositorio de GitHub / GitLab:
   - **Repository:** `<tu-usuario>/almacen-v2`
   - **Branch:** `feature/modernizacion-nodejs` (o `main`)
   - **Build Pack:** Selecciona **Dockerfile** (Coolify detectará automáticamente el archivo `Dockerfile` multi-stage optimizado del proyecto).

---

## ⚙️ Paso 4: Configuración de Variables de Entorno

En la pestaña **Environment Variables** de la aplicación en Coolify, configura los siguientes valores (puedes basarte en `.env.example`):

```env
# Entorno de ejecución
NODE_ENV=production
PORT=3000

# Zona horaria oficial del servidor (Bolivia UTC-4)
TZ=America/La_Paz

# Dominio público oficial con protocolo HTTPS
APP_URL=https://almacen.toi.bo

# Cadena de conexión completa a la base de datos de Coolify (del Paso 1)
DATABASE_URL=postgresql://almacen_toi:TU_PASSWORD_AQUI@<NOMBRE_CONTENEDOR_POSTGRES>:5432/almacen_toi

# Llave aleatoria y segura para firma criptográfica de sesiones de usuario
SESSION_SECRET=genera_una_cadena_aleatoria_larga_y_segura_aqui_ej_64_caracteres

# Llave simétrica AES-256-GCM para cifrado de contraseñas SMTP (32 bytes = 64 hex chars)
ENCRYPTION_KEY=c2ae56f3696a3b475846e5698302d2895d03b53f39935d1a05353a4def177a85
```

> [!TIP]
> Si deseas generar una nueva `ENCRYPTION_KEY` de 32 bytes en formato hexadecimal, ejecuta en tu terminal:  
> `node -e "console.log(require('crypto').randomBytes(32).toString('hex'))"`

---

## 🌐 Paso 5: Configuración de Red, Dominio y Healthcheck

En la pestaña **General Configuration** de la aplicación en Coolify:

1. **Domain (FQDN):** Escribe la URL pública de la aplicación:
   ```text
   https://almacen.toi.bo
   ```
   *(Coolify y su reverse proxy Traefik generarán y renovarán automáticamente el certificado SSL gratuito con Let's Encrypt).*

2. **Ports Exposes:** `3000`

3. **Healthcheck:**
   - **Healthcheck Path:** `/health`
   - **Healthcheck Interval:** `20s`
   - **Healthcheck Timeout:** `5s`
   - **Healthcheck Retries:** `3`
   - **Start Period:** `15s`

4. **Reverse Proxy & Trust Proxy:**  
   La aplicación ya cuenta con `app.set('trust proxy', 1)` activado en su núcleo (`src/app.ts`), por lo que Traefik transferirá de manera nativa la IP real del operador para la bitácora de auditoría y validará las cookies seguras HTTPS sin configuración adicional.

---

## 🚢 Paso 6: Despliegue y Validación

1. Haz clic en el botón **Deploy** en la esquina superior derecha.
2. Observa la consola de construcción en tiempo real:
   - Verás la etapa `builder` instalando dependencias y compilando TypeScript con `npm run build`.
   - Se creará la imagen final ligera con `node:22-alpine` ejecutando como usuario sin privilegios `USER node`.
   - Al finalizar, el contenedor arrancará y el healthcheck en `/health` pasará a estado verde **Healthy**.
3. Ingresa a `https://almacen.toi.bo/auth/login`:
   - Inicia sesión con las credenciales de administrador iniciales:
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
