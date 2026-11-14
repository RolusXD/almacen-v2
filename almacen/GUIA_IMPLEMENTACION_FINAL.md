# 🚀 GUÍA FINAL DE IMPLEMENTACIÓN
## Sistema de Almacén TOI - Ubuntu 22.04
### Versión 3.2 - Lista para Producción

**Fecha:** Marzo 2026  
**Estado:** ✅ Base de datos corregida y lista  
**Servidor:** srv-sis-toi1

---

## 📋 ÍNDICE

1. [Resumen de Correcciones](#1-resumen-de-correcciones)
2. [Scripts de Base de Datos](#2-scripts-de-base-de-datos)
3. [Implementación en Ubuntu](#3-implementación-en-ubuntu)
4. [Verificación y Pruebas](#4-verificación-y-pruebas)
5. [Puesta en Producción](#5-puesta-en-producción)
6. [Solución de Problemas](#6-solución-de-problemas)

---

## 1. RESUMEN DE CORRECCIONES

### Problemas Solucionados ✅

| # | Problema | Error | Solución |
|---|----------|-------|----------|
| 1 | Vistas corruptas | #1064, #1356 | Script `auditoria_completa.sql` |
| 2 | `ruc` en proveedores | - | Cambiado a `nit` |
| 3 | `codigos_equipos` | - | Renombrada a `tipos_equipos` |
| 4 | `id_equipo` sin AUTO_INCREMENT | #1364 | Script `corregir_estructura_equipos_v4.sql` |
| 5 | Foreign Key fallaba | #1005, #1452 | Script `diagnostico_avanzado_fk.sql` |
| 6 | Índice faltante | - | Índice agregado en `tipos_equipos.codigo` |

### Estado Actual de la Base de Datos

```
✅ 15 tablas creadas
✅ 3 vistas funcionando
✅ Foreign Keys verificadas
✅ Engines InnoDB
✅ Collation: utf8mb4_unicode_ci
✅ id_equipo: PRIMARY KEY AUTO_INCREMENT
✅ fk_equipo_tipos: Creada exitosamente
```

---

## 2. SCRIPTS DE BASE DE DATOS

### Ubicación de Archivos

Todos los scripts están en:
```
/var/www/html/almacen_moderno/database/
```

### Orden de Ejecución (YA COMPLETADO ✅)

```bash
# 1. Auditoría general (COMPLETADO)
sudo mysql -u root -p almacen_toi < auditoria_completa.sql

# 2. Corregir estructura de equipos (COMPLETADO)
sudo mysql -u root -p almacen_toi < corregir_estructura_equipos_v4.sql

# 3. Verificar y corregir FK (COMPLETADO)
sudo mysql -u root -p almacen_toi < verificar_y_corregir_fk.sql

# 4. Diagnóstico avanzado FK (COMPLETADO) ✅
sudo mysql -u root -p almacen_toi < diagnostico_avanzado_fk.sql
```

### Verificación de Estado

```bash
# Verificar tablas
sudo mysql -u root -p -e "USE almacen_toi; SHOW TABLES;"

# Verificar vistas
sudo mysql -u root -p -e "USE almacen_toi; SELECT * FROM vista_resumen_equipos_por_codigo LIMIT 3;"

# Verificar foreign keys
sudo mysql -u root -p -e "
USE almacen_toi;
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'almacen_toi'
AND TABLE_NAME = 'equipos'
AND CONSTRAINT_NAME = 'fk_equipo_tipos';
"
```

---

## 3. IMPLEMENTACIÓN EN UBUNTU

### 3.1 Prerrequisitos

Verificar que el servidor cumple con los requisitos:

```bash
# Verificar PHP (debe ser 8.0+)
php -v

# Verificar MySQL/MariaDB
mysql --version

# Verificar Apache
sudo systemctl status apache2

# Verificar Composer (instalar si no existe)
composer --version
```

### 3.2 Instalar Composer (si no existe)

```bash
cd /tmp
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### 3.3 Instalar TCPDF para Reportes PDF

```bash
cd /var/www/html/almacen_moderno
composer require tecnickcom/tcpdf
```

**Verificar instalación:**
```bash
ls -la vendor/tecnickcom/tcpdf/tcpdf.php
```

### 3.4 Configurar Base de Datos

#### Opción A: Usar base de datos actual (YA CORREGIDA ✅)

La base de datos ya está corregida en el servidor. Solo verificar:

```bash
sudo mysql -u root -p -e "USE almacen_toi; SHOW TABLES;"
```

#### Opción B: Importar desde backup

```bash
# Crear base de datos
sudo mysql -u root -p -e "CREATE DATABASE almacen_toi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importar backup
sudo mysql -u root -p almacen_toi < /ruta/al/backup/almacen_toi_produccion.sql
```

### 3.5 Crear Usuario de Base de Datos

```bash
sudo mysql -u root -p << EOF
-- Crear usuario específico para la aplicación
CREATE USER IF NOT EXISTS 'almacen_user'@'localhost' 
IDENTIFIED BY 'ContraseñaSegura2026!';

-- Otorgar privilegios solo en almacen_toi
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX 
ON almacen_toi.* TO 'almacen_user'@'localhost';

-- Aplicar cambios
FLUSH PRIVILEGES;

-- Verificar
SHOW GRANTS FOR 'almacen_user'@'localhost';
EOF
```

### 3.6 Actualizar Configuración

#### Editar `config/database.php`:

```bash
sudo nano /var/www/html/almacen_moderno/config/database.php
```

**Contenido:**
```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'almacen_toi');
define('DB_USER', 'almacen_user');
define('DB_PASS', 'ContraseñaSegura2026!');
define('DB_CHARSET', 'utf8mb4');
```

#### Editar `config/config.php`:

```bash
sudo nano /var/www/html/almacen_moderno/config/config.php
```

**Contenido:**
```php
<?php
define('APP_NAME', 'Sistema de Almacén TOI');
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/almacen_moderno');
define('APP_ENV', 'production'); // IMPORTANTE: production
define('SESSION_LIFETIME', 3600);
define('HASH_COST', 12);
date_default_timezone_set('America/La_Paz');

// Ocultar errores en producción
error_reporting(0);
ini_set('display_errors', 0);

// Logs
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

### 3.7 Establecer Permisos

```bash
# Directorio del proyecto
sudo chown -R www-data:www-data /var/www/html/almacen_moderno
sudo chmod -R 755 /var/www/html/almacen_moderno

# Archivos PHP
sudo find /var/www/html/almacen_moderno -name "*.php" -exec chmod 644 {} \;

# Configuración (más restrictivo)
sudo chmod 600 /var/www/html/almacen_moderno/config/database.php
sudo chmod 600 /var/www/html/almacen_moderno/config/config.php

# Directorio de logs
sudo mkdir -p /var/www/html/almacen_moderno/logs
sudo chown www-data:www-data /var/www/html/almacen_moderno/logs
sudo chmod 755 /var/www/html/almacen_moderno/logs

# Directorio temporal para TCPDF
sudo mkdir -p /var/www/html/almacen_moderno/tmp
sudo chown www-data:www-data /var/www/html/almacen_moderno/tmp
sudo chmod 777 /var/www/html/almacen_moderno/tmp
```

### 3.8 Reiniciar Apache

```bash
sudo systemctl restart apache2
sudo systemctl status apache2
```

---

## 4. VERIFICACIÓN Y PRUEBAS

### 4.1 Acceso Web

Abrir navegador e ir a:
```
http://srv-sis-toi1/almacen_moderno
```

**Credenciales de acceso:**
- Usuario: `admin`
- Contraseña: `Admin123!`

### 4.2 Checklist de Pruebas

#### Login y Autenticación
- [ ] Página de login carga correctamente
- [ ] Login con admin/Admin123! funciona
- [ ] Redirige al dashboard después de login
- [ ] Cerrar sesión funciona

#### Dashboard
- [ ] Estadísticas se muestran
- [ ] Gráficos cargan
- [ ] Últimos movimientos se muestran

#### Menú Lateral
- [ ] Sidebar se muestra correctamente
- [ ] Botón `<<` colapsa sidebar
- [ ] Tooltips aparecen al hacer hover en sidebar colapsado
- [ ] Menú "Gestión" se despliega correctamente
- [ ] Submenús de Gestión funcionan

#### Equipos
- [ ] Listar equipos funciona
- [ ] Crear nuevo equipo funciona
- [ ] Editar equipo funciona
- [ ] Búsqueda/filtros funcionan
- [ ] Exportar a Excel funciona
- [ ] Exportar a PDF funciona

#### Accesorios
- [ ] Listar accesorios funciona
- [ ] Fuentes consistentes
- [ ] Stock bajo se muestra
- [ ] CRUD completo funciona

#### Proveedores
- [ ] Listar proveedores funciona
- [ ] Campo "NIT" se muestra (no RUC)
- [ ] Crear proveedor con NIT funciona

#### Tipos de Equipos
- [ ] Listar tipos funciona
- [ ] Fuentes estandarizadas
- [ ] CRUD funciona

#### Reportes PDF
- [ ] Equipos PDF genera y descarga
- [ ] Accesorios PDF genera y descarga
- [ ] Movimientos PDF genera y descarga
- [ ] Usuarios PDF genera y descarga (solo admin)
- [ ] Caracteres UTF-8 (ñ, tildes) se muestran correctamente

#### Usuarios
- [ ] Listar usuarios funciona (solo admin)
- [ ] Mi Perfil funciona
- [ ] Editar perfil funciona
- [ ] Cambiar contraseña funciona

### 4.3 Pruebas de Base de Datos

```bash
# Probar INSERT con AUTO_INCREMENT
sudo mysql -u root -p -e "
USE almacen_toi;
INSERT INTO equipos (nombre, numero_serie_mac, estado, calidad, fecha_entrada)
VALUES ('EQUIPO TEST', 'TEST123', 'DISPONIBLE', 'NUEVO', CURDATE());
SELECT id_equipo, nombre, numero_serie_mac FROM equipos WHERE numero_serie_mac = 'TEST123';
DELETE FROM equipos WHERE numero_serie_mac = 'TEST123';
"

# Probar Foreign Key
sudo mysql -u root -p -e "
USE almacen_toi;
-- Este INSERT debería funcionar (código existe)
INSERT INTO equipos (nombre, codigo, estado) VALUES ('Test', 'ROT2.4', 'DISPONIBLE');
SELECT * FROM equipos WHERE nombre = 'Test';
DELETE FROM equipos WHERE nombre = 'Test';
"

# Probar vista
sudo mysql -u root -p -e "
USE almacen_toi;
SELECT * FROM vista_resumen_equipos_por_codigo LIMIT 3;
"
```

### 4.4 Verificar Logs

```bash
# Logs de Apache
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/log/apache2/access.log

# Logs de la aplicación
sudo tail -f /var/www/html/almacen_moderno/logs/error.log
```

---

## 5. PUESTA EN PRODUCCIÓN

### 5.1 Cambiar Contraseña de Admin

**Desde la aplicación:**
1. Iniciar sesión como admin
2. Ir a Usuarios
3. Editar usuario admin
4. Cambiar contraseña

**O desde SQL:**
```sql
-- Generar hash nuevo (usar PHP)
-- password_hash('NuevaContraseña2026!', PASSWORD_BCRYPT, ['cost' => 12])

-- Actualizar en BD
UPDATE usuarios 
SET password = '$2y$12$...'  -- Reemplazar con hash generado
WHERE usuario = 'admin';
```

### 5.2 Configurar HTTPS (Recomendado)

```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-apache

# Obtener certificado
sudo certbot --apache -d tu-dominio.com

# Forzar HTTPS
sudo nano /etc/apache2/sites-available/almacen_moderno.conf
```

**Agregar:**
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 5.3 Configurar Backup Automático

**Crear script:**
```bash
sudo nano /var/www/html/almacen_moderno/backup_diario.sh
```

**Contenido:**
```bash
#!/bin/bash
DB_USER="almacen_user"
DB_PASS="ContraseñaSegura2026!"
DB_NAME="almacen_toi"
BACKUP_DIR="/var/backups/almacen_toi"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

mysqldump -u $DB_USER -p$DB_PASS \
  --single-transaction \
  --routines \
  --triggers \
  $DB_NAME > $BACKUP_DIR/backup_$DATE.sql

gzip $BACKUP_DIR/backup_$DATE.sql

# Eliminar backups antiguos (+30 días)
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +30 -delete

echo "Backup completado: $DATE" >> $BACKUP_DIR/backup.log
```

**Agendar en crontab:**
```bash
sudo crontab -e
```

**Agregar:**
```
0 2 * * * /var/www/html/almacen_moderno/backup_diario.sh
```

### 5.4 Monitoreo

**Crear script de salud del sistema:**
```bash
sudo nano /var/www/html/almacen_moderno/health_check.sh
```

**Contenido:**
```bash
#!/bin/bash
echo "=== Salud del Sistema ==="
echo "Fecha: $(date)"
echo ""
echo "Apache:"
sudo systemctl is-active apache2
echo ""
echo "MySQL:"
sudo systemctl is-active mysql
echo ""
echo "Espacio en disco:"
df -h /var/www/html
echo ""
echo "Últimos errores Apache:"
sudo tail -5 /var/log/apache2/error.log
```

---

## 6. SOLUCIÓN DE PROBLEMAS

### Error: "Class 'TCPDF' not found"

```bash
cd /var/www/html/almacen_moderno
composer dump-autoload
```

### Error: "Permission denied"

```bash
sudo chown -R www-data:www-data /var/www/html/almacen_moderno
sudo chmod 777 /var/www/html/almacen_moderno/tmp
```

### Error: "Cannot modify header information"

Verificar en `ReportesController.php`:
```php
ob_clean();
$pdf->Output('archivo.pdf', 'D');
exit; // IMPORTANTE
```

### Error: MySQL acceso denegado

```bash
# Verificar usuario
sudo mysql -u root -p -e "SHOW GRANTS FOR 'almacen_user'@'localhost';"

# Si no tiene permisos
sudo mysql -u root -p -e "
GRANT ALL ON almacen_toi.* TO 'almacen_user'@'localhost';
FLUSH PRIVILEGES;
"
```

### Error: 403 Forbidden

```bash
# Verificar permisos
sudo chmod 755 /var/www/html/almacen_moderno
sudo chmod 644 /var/www/html/almacen_moderno/*.php

# Verificar .htaccess
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Error: Sidebar no colapsa

Limpiar caché del navegador:
```
Ctrl + Shift + Supr (Chrome/Edge)
Ctrl + F5 (Recarga forzada)
```

### Error: Menú Gestión no se despliega

Verificar JavaScript en `footer.php`:
```javascript
$('#btnGestion').on('click', function(e) {
    e.preventDefault();
    if ($('#sidebar').hasClass('collapsed')) {
        return false;
    }
    $('#menuGestion').collapse('toggle');
});
```

### Error: Reportes PDF no generan

1. Verificar TCPDF instalado:
```bash
ls -la vendor/tecnickcom/tcpdf/tcpdf.php
```

2. Reinstalar TCPDF:
```bash
composer require tecnickcom/tcpdf
```

---

## 📊 ARCHIVOS DE REFERENCIA

| Archivo | Propósito |
|---------|-----------|
| `database/auditoria_completa.sql` | Auditoría general de BD |
| `database/corregir_estructura_equipos_v4.sql` | AUTO_INCREMENT id_equipo |
| `database/diagnostico_avanzado_fk.sql` | Foreign Key (✅ YA EJECUTADO) |
| `database/verificar_y_corregir_fk.sql` | Verificación FK |
| `controllers/ReportesController.php` | Reportes PDF con TCPDF |
| `controllers/ProveedoresController.php` | Proveedores con NIT |
| `controllers/UsuariosController.php` | Método perfil() agregado |
| `views/layouts/header.php` | Header con usuario y notificaciones |
| `views/layouts/footer.php` | JavaScript sidebar colapsable |
| `config/database.php` | Configuración de BD |
| `config/config.php` | Configuración general |

---

## ✅ CHECKLIST FINAL

### Base de Datos
- [x] `auditoria_completa.sql` ejecutado
- [x] `corregir_estructura_equipos_v4.sql` ejecutado
- [x] `diagnostico_avanzado_fk.sql` ejecutado
- [x] Foreign Key `fk_equipo_tipos` creada
- [x] Vistas funcionan
- [x] INSERT con AUTO_INCREMENT funciona

### Servidor
- [ ] PHP 8.0+ instalado
- [ ] Apache configurado
- [ ] MySQL/MariaDB corriendo
- [ ] Composer instalado
- [ ] TCPDF instalado
- [ ] Permisos establecidos
- [ ] Logs configurados

### Configuración
- [ ] `config/database.php` actualizado
- [ ] `config/config.php` en production
- [ ] Usuario de BD creado
- [ ] Contraseña de admin cambiada
- [ ] HTTPS configurado (opcional)
- [ ] Backup automático configurado

### Pruebas
- [ ] Login funciona
- [ ] Dashboard carga
- [ ] Sidebar colapsa
- [ ] Menú Gestión funciona
- [ ] CRUD Equipos funciona
- [ ] CRUD Accesorios funciona
- [ ] CRUD Proveedores (NIT) funciona
- [ ] Reportes PDF generan
- [ ] Caracteres UTF-8 correctos
- [ ] Mi Perfil funciona
- [ ] Sin errores en logs

---

## 📞 CONTACTOS DE EMERGENCIA

```
Desarrollador: _________________
Teléfono: _____________________
Email: _______________________

Soporte Técnico: ______________
Teléfono: _____________________
```

---

## 🎯 PRÓXIMOS PASOS

1. **Completar checklist de pruebas** (Sección 4.2)
2. **Cambiar contraseña de admin** (Sección 5.1)
3. **Configurar HTTPS** (Sección 5.2 - Opcional)
4. **Configurar backup automático** (Sección 5.3)
5. **Capacitar al personal** en el uso del sistema
6. **Recopilar feedback** después de la primera semana
7. **Corregir errores** reportados por usuarios

---

**Estado:** ✅ Base de datos lista - Pendiente configuración final  
**Tiempo estimado:** 30-45 minutos para completar configuración  
**Dificultad:** Media

---

## 📝 NOTAS IMPORTANTES

1. **La base de datos YA está corregida** - No es necesario ejecutar los scripts nuevamente
2. **TCPDF es obligatorio** para los reportes PDF
3. **Los permisos deben ser exactos** - www-data como propietario
4. **Los logs son cruciales** - Revisar diariamente la primera semana
5. **Backup automático** - Configurar antes de poner en producción real

---

**¡Éxito en la implementación!** 🚀

**Última actualización:** Marzo 2026  
**Versión del documento:** 1.0 Final
