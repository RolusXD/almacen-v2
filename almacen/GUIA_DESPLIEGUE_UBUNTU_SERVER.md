# Guía breve de despliegue en Ubuntu Server

Proyecto: Sistema de Almacén TOI  
Base de datos: PostgreSQL  
Archivo SQL recomendado: `database/almacen_toi_postgresql_ubuntu_20260417.sql`

## 1. Requisitos del servidor

Servidor recomendado:

- Ubuntu Server 22.04 LTS o 24.04 LTS
- PHP 8.2 o superior
- PostgreSQL 14 o superior
- Apache 2 o Nginx
- Extensión PHP `pdo_pgsql`

Actualizar paquetes:

```bash
sudo apt update
sudo apt upgrade -y
```

Instalar Apache, PHP y PostgreSQL:

```bash
sudo apt install -y apache2 postgresql postgresql-contrib \
  php php-cli php-pgsql php-mbstring php-xml php-curl php-zip php-gd unzip
```

Verificar PHP:

```bash
php -v
php -m | grep -E "PDO|pdo_pgsql|mbstring"
```

## 2. Crear base de datos PostgreSQL

Entrar como usuario `postgres`:

```bash
sudo -u postgres psql
```

Crear usuario y base:

```sql
CREATE USER almacen_user WITH PASSWORD 'CAMBIAR_PASSWORD_SEGURA';
CREATE DATABASE almacen_toi OWNER almacen_user;
GRANT ALL PRIVILEGES ON DATABASE almacen_toi TO almacen_user;
\q
```

Importar la copia actualizada:

```bash
psql "postgresql://almacen_user:CAMBIAR_PASSWORD_SEGURA@localhost:5432/almacen_toi" \
  -f database/almacen_toi_postgresql_ubuntu_20260417.sql
```

Verificar datos:

```bash
psql "postgresql://almacen_user:CAMBIAR_PASSWORD_SEGURA@localhost:5432/almacen_toi" \
  -c "SELECT COUNT(*) AS equipos FROM equipos; SELECT COUNT(*) AS accesorios FROM accesorios; SELECT COUNT(*) AS usuarios FROM usuarios;"
```

## 3. Copiar el proyecto

Ejemplo usando `/var/www/almacen`:

```bash
sudo mkdir -p /var/www/almacen
sudo cp -R . /var/www/almacen
sudo chown -R www-data:www-data /var/www/almacen
sudo find /var/www/almacen -type d -exec chmod 755 {} \;
sudo find /var/www/almacen -type f -exec chmod 644 {} \;
```

No copiar al servidor carpetas innecesarias como `.git`, `.qwen`, `node_modules` o archivos temporales.

## 4. Configurar variables de entorno en Apache

Crear virtual host:

```bash
sudo nano /etc/apache2/sites-available/almacen.conf
```

Contenido sugerido:

```apache
<VirtualHost *:80>
    ServerName almacen.local
    DocumentRoot /var/www/almacen

    SetEnv APP_ENV production
    SetEnv APP_URL http://almacen.local
    SetEnv DB_DRIVER pgsql
    SetEnv DB_HOST localhost
    SetEnv DB_PORT 5432
    SetEnv DB_NAME almacen_toi
    SetEnv DB_USER almacen_user
    SetEnv DB_PASS CAMBIAR_PASSWORD_SEGURA
    SetEnv DB_SSLMODE prefer
    SetEnv SESSION_DRIVER database

    <Directory /var/www/almacen>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/almacen_error.log
    CustomLog ${APACHE_LOG_DIR}/almacen_access.log combined
</VirtualHost>
```

Activar sitio y módulos:

```bash
sudo a2enmod rewrite env
sudo a2ensite almacen.conf
sudo systemctl reload apache2
```

Si se usará una IP o dominio real, reemplazar `almacen.local` y `APP_URL`.

## 5. Reglas de rutas

El proyecto incluye `.htaccess`. Si no existe en el servidor o Apache no redirige correctamente, crear en la raíz del proyecto:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

## 6. Credenciales iniciales

Usuario inicial incluido en la base:

- Usuario: `admin`
- Email: `admin@toi.bo`

Cambiar la contraseña del administrador después del primer acceso.

## 7. Revisión rápida post-despliegue

Probar en navegador:

```text
http://almacen.local
```

Revisar logs si hay error 500:

```bash
sudo tail -f /var/log/apache2/almacen_error.log
```

Validar conexión a base:

```bash
psql "postgresql://almacen_user:CAMBIAR_PASSWORD_SEGURA@localhost:5432/almacen_toi" -c "\dt"
```

## 8. Recomendaciones finales

- Usar contraseña fuerte para `almacen_user`.
- No dejar `APP_ENV=development` en producción.
- Hacer backup diario de PostgreSQL:

```bash
pg_dump "postgresql://almacen_user:CAMBIAR_PASSWORD_SEGURA@localhost:5432/almacen_toi" \
  > /backups/almacen_toi_$(date +%Y%m%d).sql
```

- Proteger el servidor con firewall:

```bash
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

- Si el acceso será externo, configurar HTTPS con Certbot.
