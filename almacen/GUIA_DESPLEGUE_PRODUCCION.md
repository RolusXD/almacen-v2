# 🚀 GUÍA DE DESPLIEGUE - PRODUCCIÓN
## Sistema de Almacén TOI

**Versión:** 3.1  
**Fecha:** Marzo 2026  
**Estado:** Lista para Producción

---

## 📋 TABLA DE CONTENIDOS

1. [Requisitos Previos](#requisitos-previos)
2. [Configuración del Servidor](#configuración-del-servidor)
3. [Configuración de la Aplicación](#configuración-de-la-aplicación)
4. [Base de Datos](#base-de-datos)
5. [Seguridad](#seguridad)
6. [Checklist Pre-Producción](#checklist-pre-producción)
7. [Pruebas de Aceptación](#pruebas-de-aceptación)
8. [Plan de Rollback](#plan-de-rollback)
9. [Mantenimiento](#mantenimiento)

---

## 🎯 REQUISITOS PREVIOS

### Servidor
- [ ] Apache 2.4+ con mod_rewrite habilitado
- [ ] PHP 8.0+ (recomendado 8.1 o superior)
- [ ] MariaDB 10.4+ o MySQL 5.7+
- [ ] Extensión PDO habilitada en PHP
- [ ] Extensión mbstring habilitada
- [ ] Mínimo 512MB RAM disponible
- [ ] Mínimo 1GB de espacio en disco

### Permisos de Archivos
```bash
# Directorios
chmod 755 /var/www/html/almacen_moderno
chmod 755 /var/www/html/almacen_moderno/config
chmod 755 /var/www/html/almacen_moderno/database

# Archivos PHP
chmod 644 /var/www/html/almacen_moderno/*.php
chmod 644 /var/www/html/almacen_moderno/config/*.php

# Archivos de configuración
chmod 600 /var/www/html/almacen_moderno/config/database.php
```

---

## ⚙️ CONFIGURACIÓN DEL SERVIDOR

### 1. Habilitar mod_rewrite (Apache)

**Ubuntu/Debian:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**Windows (XAMPP):**
```
1. Abrir httpd.conf
2. Buscar: #LoadModule rewrite_module modules/mod_rewrite.so
3. Quitar el # al inicio
4. Reiniciar Apache
```

### 2. Configurar VirtualHost (Recomendado)

```apache
<VirtualHost *:80>
    ServerName almacen.toi.bo
    DocumentRoot /var/www/html/almacen_moderno
    
    <Directory /var/www/html/almacen_moderno>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/almacen_toi_error.log
    CustomLog ${APACHE_LOG_DIR}/almacen_toi_access.log combined
</VirtualHost>
```

### 3. Habilitar HTTPS (Recomendado)

**Con Let's Encrypt:**
```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d almacen.toi.bo
```

**Forzar HTTPS en .htaccess:**
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 🔧 CONFIGURACIÓN DE LA APLICACIÓN

### 1. Archivo config/config.php

**CAMBIOS REQUERIDOS:**

```php
<?php
/**
 * Configuración general de la aplicación
 * VERSIÓN DE PRODUCCIÓN
 */

require_once __DIR__ . '/database.php';

// ============================================
// CONFIGURACIÓN DE LA APLICACIÓN
// ============================================
define('APP_NAME', 'Sistema de Almacén TOI');

// OPCIÓN 1: URL dinámica (recomendado)
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/almacen_moderno');

// OPCIÓN 2: URL fija (producción con dominio)
// define('APP_URL', 'https://almacen.toi.bo');

// IMPORTANTE: Cambiar a production en producción real
define('APP_ENV', 'production'); // development | production

// ============================================
// CONFIGURACIÓN DE SESIÓN
// ============================================
define('SESSION_LIFETIME', 3600); // 1 hora
define('SESSION_NAME', 'ALMACEN_TOI_SESSION');

// ============================================
// CONFIGURACIÓN DE SEGURIDAD
// ============================================
define('HASH_COST', 12); // Más seguro que 10 (producción)

// ============================================
// ZONA HORARIA
// ============================================
date_default_timezone_set('America/La_Paz');

// ============================================
// CONFIGURACIÓN DE ERRORES
// ============================================
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Producción: NO mostrar errores
    error_reporting(0);
    ini_set('display_errors', 0);
    
    // Logs de errores (RECOMENDADO)
    ini_set('log_errors', 1);
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    ini_set('error_log', $logDir . '/error.log');
}

// ============================================
// INICIAR SESIÓN
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    // Configuración de sesión segura
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Cambiar a 1 si usa HTTPS
    ini_set('session.cookie_samesite', 'Strict');
    
    session_start();
}
```

### 2. Archivo config/database.php

**CAMBIOS REQUERIDOS:**

```php
<?php
/**
 * Configuración de la base de datos
 * VERSIÓN DE PRODUCCIÓN
 */

// ============================================
// CONFIGURACIÓN DE PRODUCCIÓN
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'almacen_toi');

// IMPORTANTE: NO USAR ROOT en producción
define('DB_USER', 'almacen_user');     // Usuario específico
define('DB_PASS', 'TuContraseñaSegura2026!'); // Contraseña fuerte

define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

// ============================================
// CONFIGURACIÓN DE CONEXIÓN (PDO)
// ============================================
define('DB_DSN', sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    DB_HOST,
    DB_PORT,
    DB_NAME,
    DB_CHARSET
));

// ============================================
// CONFIGURACIÓN DE CONEXIÓN (OPCIONAL)
// ============================================
// Para servidores remotos
// define('DB_HOST', '192.168.1.100');
// define('DB_PORT', 3306);
```

### 3. Crear Usuario de Base de Datos

**Script SQL:**
```sql
-- ============================================
-- CREAR USUARIO ESPECÍFICO PARA LA APLICACIÓN
-- ============================================
-- NO usar root en producción

-- Crear usuario
CREATE USER IF NOT EXISTS 'almacen_user'@'localhost' 
IDENTIFIED BY 'TuContraseñaSegura2026!';

-- Otorgar permisos específicos
GRANT SELECT, INSERT, UPDATE, DELETE ON almacen_toi.* 
TO 'almacen_user'@'localhost';

-- Permisos adicionales si es necesario
GRANT CREATE, ALTER, INDEX ON almacen_toi.* 
TO 'almacen_user'@'localhost';

-- Aplicar cambios
FLUSH PRIVILEGES;

-- Verificar
SHOW GRANTS FOR 'almacen_user'@'localhost';
```

---

## 🗄️ BASE DE DATOS

### 1. Verificar Estructura

**Script de verificación:**
```sql
-- Verificar que todas las tablas existen
USE almacen_toi;

SHOW TABLES;

-- Debería mostrar:
-- usuarios
-- equipos
-- accesorios
-- movimientos_equipos
-- movimientos_accesorios
-- equipos_retirados
-- accesorios_retirados
-- actividades
-- proveedores
-- categorias
-- marcas
-- mantenimientos
-- prestamos
-- tipos_equipos
```

### 2. Ejecutar Migraciones Pendientes

**Orden de ejecución:**

```bash
# 1. Cambio RUC → NIT (Bolivia)
mysql -u root -p almacen_toi < database/nit_cambio.sql

# 2. Migración de equipos (si es necesario)
mysql -u root -p almacen_toi < database/migracion_equipos.sql

# 3. Verificación de tipos_equipos
mysql -u root -p almacen_toi < database/verificar_tipos_equipos.sql
```

### 3. Cambiar Contraseña de Admin

**IMPORTANTE:** Cambiar la contraseña por defecto antes de producción.

**Opción 1: Desde la aplicación**
```
1. Iniciar sesión como admin
2. Ir a Usuarios
3. Editar usuario admin
4. Cambiar contraseña
```

**Opción 2: Desde SQL**
```sql
-- Generar nuevo hash (PHP)
-- password_hash('NuevaContraseña2026!', PASSWORD_BCRYPT, ['cost' => 12])

-- Actualizar en BD
UPDATE usuarios 
SET password = '$2y$12$...'  -- Reemplazar con hash generado
WHERE usuario = 'admin';
```

### 4. Crear Backup Inicial

```bash
# Backup completo de la base de datos
mysqldump -u root -p \
  --single-transaction \
  --routines \
  --triggers \
  almacen_toi > backup_almacen_toi_$(date +%Y%m%d_%H%M%S).sql

# Verificar backup
ls -lh backup_almacen_toi_*.sql
```

---

## 🔒 SEGURIDAD

### 1. Archivos a Eliminar/Proteger

**ELIMINAR en producción:**
```bash
# Archivos de test
rm diagnostico.php
rm test_carga.php
rm test_sesiones.php

# Scripts SQL (mover fuera del root)
mkdir /var/www/html/_sql_backups
mv database/*.sql /var/www/html/_sql_backups/
```

**PROTEGER con .htaccess adicional:**
```apache
# database/.htaccess
Order deny,allow
Deny from all
```

### 2. Documentación

**Mover documentación fuera del root:**
```bash
mkdir /var/www/html/_docs
mv *.md /var/www/html/_docs/
mv Documentacion /var/www/html/_docs/
```

### 3. Configurar Logs

**Crear directorio de logs:**
```bash
mkdir /var/www/html/almacen_moderno/logs
chmod 755 /var/www/html/almacen_moderno/logs
chown www-data:www-data /var/www/html/almacen_moderno/logs
```

**Configurar en config.php:**
```php
// Ya incluido en la configuración de producción
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');
```

### 4. Cabeceras de Seguridad (.htaccess)

**Verificar que estén activas:**
```apache
# Ya incluidas en el .htaccess actual
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

### 5. Permisos de Archivos Sensibles

```bash
# Archivos de configuración
chmod 600 config/database.php
chmod 600 config/config.php

# Directorio de logs
chmod 755 logs/

# Resto de archivos
find . -name "*.php" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
```

---

## ✅ CHECKLIST PRE-PRODUCCIÓN

### Configuración
- [ ] Cambiar `APP_URL` en config/config.php
- [ ] Cambiar `APP_ENV` a `'production'`
- [ ] Crear usuario específico de BD (no root)
- [ ] Actualizar credenciales en config/database.php
- [ ] Cambiar `HASH_COST` a 12
- [ ] Configurar logs de errores
- [ ] Eliminar archivos de test (diagnostico.php, test_*.php)
- [ ] Mover documentación fuera del root
- [ ] Mover scripts SQL fuera del root

### Base de Datos
- [ ] Ejecutar nit_cambio.sql (RUC → NIT)
- [ ] Ejecutar migracion_equipos.sql (si corresponde)
- [ ] Verificar todas las tablas existen
- [ ] Cambiar contraseña de admin por defecto
- [ ] Crear backup inicial
- [ ] Configurar usuario específico de BD

### Seguridad
- [ ] Configurar HTTPS (recomendado)
- [ ] Verificar .htaccess activo
- [ ] Configurar cabeceras de seguridad
- [ ] Proteger directorio database/
- [ ] Configurar permisos correctos
- [ ] Habilitar logs de errores
- [ ] Configurar sesión segura

### Servidor
- [ ] Apache con mod_rewrite habilitado
- [ ] PHP 8.0+ instalado
- [ ] Extensiones PDO y mbstring habilitadas
- [ ] VirtualHost configurado (opcional)
- [ ] Logs de Apache configurados

### Pruebas
- [ ] Login funciona
- [ ] Menú Gestión se despliega
- [ ] CRUD de Equipos funciona
- [ ] CRUD de Accesorios funciona
- [ ] Exportación a Excel funciona
- [ ] Exportación a PDF funciona
- [ ] No hay errores en consola del navegador
- [ ] No hay errores en logs de Apache

---

## 🧪 PRUEBAS DE ACEPTACIÓN

### Script de Pruebas

**1. Login y Autenticación**
```
□ Ingresar con usuario: admin
□ Verificar que redirige al dashboard
□ Verificar que el menú lateral se muestra
□ Colapsar sidebar (<<) y verificar tooltips
□ Expandir sidebar (>>)
□ Cerrar sesión
```

**2. Menú Gestión**
```
□ Click en "Gestión" con sidebar expandido → Despliega submenú
□ Verificar submenú: Proveedores, Tipos de Equipos, Categorías, Marcas
□ Click en "Gestión" nuevamente → Cierra submenú
□ Colapsar sidebar
□ Click en "Gestión" con sidebar colapsado → NO hace nada
□ Verificar tooltip "Gestión" al pasar mouse
```

**3. Equipos**
```
□ Listar equipos
□ Crear nuevo equipo
□ Editar equipo existente
□ Buscar equipos por filtro
□ Exportar a Excel
□ Exportar a CSV
```

**4. Accesorios**
```
□ Listar accesorios
□ Crear nuevo accesorio
□ Editar accesorio existente
□ Verificar alertas de stock bajo
□ Exportar a Excel
```

**5. Proveedores**
```
□ Listar proveedores
□ Crear nuevo proveedor
□ Verificar campo "NIT" en lugar de "RUC"
□ Editar proveedor
```

**6. Tipos de Equipos**
```
□ Listar tipos de equipos
□ Verificar diseño con fuentes consistentes
□ Crear nuevo tipo
□ Editar tipo existente
```

**7. Dashboard**
```
□ Verificar estadísticas
□ Verificar gráficos
□ Verificar últimos movimientos
```

### Formulario de Feedback

**Para el personal del almacén:**

```
FEEDBACK DE PRUEBA - SISTEMA DE ALMACÉN TOI
Fecha: ___________
Usuario: ___________

1. ¿Qué módulos utilizó?
□ Dashboard    □ Equipos    □ Accesorios    □ Movimientos
□ Proveedores  □ Reportes   □ Gestión       □ Otros: _______

2. Califique la facilidad de uso (1-5):
   □ 1  □ 2  □ 3  □ 4  □ 5

3. ¿Encontró algún error?
   Descripción: ___________________________________________
   ¿En qué módulo? _______________________________________

4. ¿Qué funcionó bien?
   _______________________________________________________

5. ¿Qué sugiere mejorar?
   _______________________________________________________

6. ¿El sistema es más rápido que el anterior?
   □ Sí  □ No  □ No estoy seguro

7. ¿Recomendaría el sistema?
   □ Sí  □ No  □ Tal vez

Firma: ___________________
```

---

## 🔄 PLAN DE ROLLBACK

### Si Algo Sale Mal

**1. Restaurar Base de Datos**
```bash
# Listar backups disponibles
ls -lh backup_almacen_toi_*.sql

# Restaurar backup
mysql -u root -p almacen_toi < backup_almacen_toi_YYYYMMDD_HHMMSS.sql
```

**2. Restaurar Versión Anterior**
```bash
# Si hay versión anterior del código
cd /var/www/html
mv almacen_moderno almacen_moderno_nuevo
mv almacen_moderno_backup almacen_moderno

# Reiniciar Apache
sudo systemctl restart apache2
```

**3. Verificar Estado**
```bash
# Verificar logs de Apache
tail -f /var/log/apache2/almacen_toi_error.log

# Verificar logs de la aplicación
tail -f /var/www/html/almacen_moderno/logs/error.log

# Verificar estado de MySQL
mysql -u root -p -e "SHOW DATABASES;"
```

### Contactos de Emergencia

```
Desarrollador: _________________
Teléfono: _____________________
Email: _______________________

Soporte Técnico: ______________
Teléfono: _____________________
```

---

## 🛠️ MANTENIMIENTO

### Diario
- [ ] Verificar logs de errores
- [ ] Verificar espacio en disco
- [ ] Verificar que el sistema está accesible

### Semanal
- [ ] Revisar backups automáticos
- [ ] Verificar rendimiento de consultas
- [ ] Revisar logs de acceso

### Mensual
- [ ] Actualizar dependencias (CDN)
- [ ] Revisar usuarios activos
- [ ] Limpieza de logs antiguos
- [ ] Backup completo y prueba de restauración

### Script de Backup Automático

**backup_diario.sh:**
```bash
#!/bin/bash

# Configuración
DB_USER="root"
DB_PASS="tu_contraseña"
DB_NAME="almacen_toi"
BACKUP_DIR="/var/backups/almacen_toi"
DATE=$(date +%Y%m%d_%H%M%S)

# Crear directorio si no existe
mkdir -p $BACKUP_DIR

# Crear backup
mysqldump -u $DB_USER -p$DB_PASS \
  --single-transaction \
  --routines \
  --triggers \
  $DB_NAME > $BACKUP_DIR/backup_$DATE.sql

# Comprimir
gzip $BACKUP_DIR/backup_$DATE.sql

# Eliminar backups antiguos (más de 30 días)
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +30 -delete

# Log
echo "Backup completado: $DATE" >> $BACKUP_DIR/backup.log
```

**Agendar en crontab:**
```bash
# Ejecutar diariamente a las 2 AM
0 2 * * * /var/www/html/almacen_moderno/backup_diario.sh
```

---

## 📊 ESTADO DEL SISTEMA

| Componente | Versión | Estado |
|------------|---------|--------|
| Sistema | 3.1 | ✅ Listo |
| Base de Datos | MariaDB/MySQL | ✅ Compatible |
| PHP | 8.0+ | ✅ Requerido |
| Apache | 2.4+ | ✅ Requerido |

---

## 📞 SOPORTE

### Documentación Disponible
- `README.md` - Guía principal
- `ESTADO_DEL_PROYECTO.md` - Estado actual
- `USUARIOS.md` - Guía de usuarios
- `CAMBIOS_REALIZADOS_2026.md` - Historial de cambios

### Enlaces Útiles
- Bootstrap 5: https://getbootstrap.com/docs/5.3
- DataTables: https://datatables.net/manual/
- PHP PDO: https://www.php.net/manual/es/book.pdo.php

---

**Última actualización:** Marzo 2026  
**Desarrollado para:** TOI - Telecomunicaciones Oportunas Inteligentes  
**Versión:** 3.1 - Lista para Producción
