# Manual De Implementacion En Ubuntu Produccion

Proyecto: `Sistema de Almacen TOI / TOI NEXUS`  
Fecha de respaldo: `2026-04-21`  
Base exportada: [database/almacen_toi_neon_20260421.sql](C:/Users/raul1/.lmstudio/conversations/almacen/database/almacen_toi_neon_20260421.sql)  
Hash SHA256: `1002EA62AAA456E4CFE9835630E962B4F942BB63FE3B0FAC87598C7B5625A344`

## 1. Objetivo

Este manual sirve para desplegar el sistema en un servidor Ubuntu con:

- Apache
- PHP 8.2+
- PostgreSQL
- Base restaurada desde el dump actual

## 2. Requisitos del servidor

Sistema recomendado:

- Ubuntu Server 22.04 LTS o 24.04 LTS
- Apache 2.4
- PHP 8.2 o superior
- PostgreSQL 14 o superior

Actualizar el servidor:

```bash
sudo apt update
sudo apt upgrade -y
```

Instalar dependencias:

```bash
sudo apt install -y apache2 postgresql postgresql-contrib \
  php php-cli php-pgsql php-mbstring php-xml php-curl php-zip php-gd unzip
```

Verificar PHP:

```bash
php -v
php -m | grep -E "PDO|pdo_pgsql|pgsql|mbstring|xml|curl|zip|gd"
```

## 3. Subir el proyecto al servidor

Ejemplo de ruta:

```bash
sudo mkdir -p /var/www/almacen
sudo chown -R $USER:$USER /var/www/almacen
```

Copiar el proyecto desde tu equipo al servidor.

Ejemplo usando `scp` desde Windows:

```powershell
scp -r C:\ruta\del\proyecto\almacen usuario@IP_DEL_SERVIDOR:/var/www/
```

Luego ajustar propietario:

```bash
sudo chown -R www-data:www-data /var/www/almacen
sudo find /var/www/almacen -type d -exec chmod 755 {} \;
sudo find /var/www/almacen -type f -exec chmod 644 {} \;
```

## 4. Restaurar la base de datos

Entrar a PostgreSQL:

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

Restaurar el dump:

```bash
psql "postgresql://almacen_user:CAMBIAR_PASSWORD_SEGURA@localhost:5432/almacen_toi" \
  -f /var/www/almacen/database/almacen_toi_neon_20260421.sql
```

Validar que cargó:

```bash
psql "postgresql://almacen_user:CAMBIAR_PASSWORD_SEGURA@localhost:5432/almacen_toi" -c "\dt"

psql "postgresql://almacen_user:CAMBIAR_PASSWORD_SEGURA@localhost:5432/almacen_toi" -c "
SELECT COUNT(*) AS equipos FROM equipos;
SELECT COUNT(*) AS accesorios FROM accesorios;
SELECT COUNT(*) AS usuarios FROM usuarios;
"
```

## 5. Configurar Apache

Crear virtual host:

```bash
sudo nano /etc/apache2/sites-available/almacen.conf
```

Contenido sugerido:

```apache
<VirtualHost *:80>
    ServerName tu-dominio-o-ip
    DocumentRoot /var/www/almacen

    SetEnv APP_ENV production
    SetEnv APP_URL http://tu-dominio-o-ip
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

Si no usarás nombre de dominio todavía, coloca la IP del servidor en `ServerName` y `APP_URL`.

## 6. Sesiones y permisos de escritura

El proyecto ya soporta dos modos:

- `SESSION_DRIVER=database`: recomendado para producción
- sin `SESSION_DRIVER`: usa `storage/sessions`

Si usarás sesiones por archivos, crea y habilita escritura:

```bash
sudo mkdir -p /var/www/almacen/storage/sessions
sudo chown -R www-data:www-data /var/www/almacen/storage
sudo chmod -R 775 /var/www/almacen/storage
```

Si usarás `SESSION_DRIVER=database`, la tabla `app_sessions` se crea automáticamente.

## 7. Verificaciones después del despliegue

Abrir en navegador:

```text
http://tu-dominio-o-ip
```

Pruebas mínimas:

1. Login con administrador.
2. Abrir `Equipos` y `Accesorios`.
3. Abrir modal de `Importación`.
4. Exportar CSV y Excel.
5. Validar que un usuario normal no entre a módulos restringidos.
6. Editar `Mi Perfil`.

## 8. Credenciales iniciales

La base contiene el usuario administrador existente del sistema.

Acción obligatoria al primer ingreso:

1. Entrar con el administrador.
2. Cambiar contraseña.
3. Verificar correo y datos del perfil.

## 9. Logs útiles

Errores de Apache:

```bash
sudo tail -f /var/log/apache2/almacen_error.log
```

Estado de Apache:

```bash
sudo systemctl status apache2
```

Estado de PostgreSQL:

```bash
sudo systemctl status postgresql
```

## 10. Backup y recuperacion

Generar backup manual en Ubuntu:

```bash
pg_dump "postgresql://almacen_user:CAMBIAR_PASSWORD_SEGURA@localhost:5432/almacen_toi" \
  --clean --if-exists --no-owner --no-privileges \
  > /backups/almacen_toi_$(date +%Y%m%d_%H%M%S).sql
```

Restaurar un backup:

```bash
psql "postgresql://almacen_user:CAMBIAR_PASSWORD_SEGURA@localhost:5432/almacen_toi" \
  -f /backups/archivo_respaldo.sql
```

## 11. Seguridad recomendada

- No dejar `APP_ENV=development`
- Cambiar la contraseña del usuario `almacen_user`
- Cambiar la contraseña del administrador del sistema
- Restringir acceso al servidor con firewall
- Configurar HTTPS si el sistema será público o accesible fuera de la red interna

Firewall básico:

```bash
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

## 12. Checklist final

- Proyecto copiado en `/var/www/almacen`
- Base restaurada sin errores
- Apache activo
- PostgreSQL activo
- Login correcto
- Exportación correcta
- Importación correcta
- Permisos por rol correctos
- Perfil de usuario correcto

## 13. Archivos clave para la entrega

- [database/almacen_toi_neon_20260421.sql](C:/Users/raul1/.lmstudio/conversations/almacen/database/almacen_toi_neon_20260421.sql)
- [MANUAL_IMPLEMENTACION_UBUNTU_PRODUCCION_20260421.md](C:/Users/raul1/.lmstudio/conversations/almacen/MANUAL_IMPLEMENTACION_UBUNTU_PRODUCCION_20260421.md)
- [README.md](C:/Users/raul1/.lmstudio/conversations/almacen/README.md)
