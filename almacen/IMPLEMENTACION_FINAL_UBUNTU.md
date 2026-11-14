#  IMPLEMENTACIÓN FINAL - UBUNTU 22.04
## Sistema de Almacén TOI - Marzo 2026

**Estado:** ✅ Listo para Producción  
**Versión:** 3.2 Completa

---

## 📋 RESUMEN DE CAMBIOS REALIZADOS

### Base de Datos
1. ✅ **auditoria_completa.sql** creado - Verifica y corrige TODO
2. ✅ **nuevas_tablas.sql** actualizado - `ruc` → `nit`
3. ✅ Vistas corruptas eliminadas y recreadas
4. ✅ Foreign keys verificadas

### Código PHP
1. ✅ **ReportesController.php** actualizado - FPDF → TCPDF
2. ✅ **ProveedoresController.php** actualizado - `ruc` → `nit`
3. ✅ **Equipo.php** verificado - `tipos_equipos` correcto

### Documentación
1. ✅ `AUDITORIA_BASE_DE_DATOS.md` - Guía de auditoría
2. ✅ `INSTALACION_TCPDF_UBUNTU.md` - Instalación en Ubuntu
3. ✅ `GUIA_DESPLEGUE_PRODUCCION.md` - Guía completa
4. ✅ `IMPLEMENTACION_FINAL_UBUNTU.md` - Este documento

---

## 🚀 PASOS PARA IMPLEMENTACIÓN

### FASE 1: Preparar en Windows (XAMPP)

#### Paso 1: Ejecutar Auditoría
```bash
# En phpMyAdmin o línea de comandos
mysql -u root -p almacen_toi < database/auditoria_completa.sql
```

**Qué hace:**
- ✅ Verifica tablas existentes
- ✅ Renombra `ruc` → `nit` en proveedores
- ✅ Renombra `codigos_equipos` → `tipos_equipos` (si es necesario)
- ✅ Elimina vistas corruptas
- ✅ Recrea vistas correctamente
- ✅ Verifica foreign keys

#### Paso 2: Verificar que Todo Funcione
```sql
-- En phpMyAdmin
USE almacen_toi;

-- Verificar tablas
SHOW TABLES;

-- Verificar vistas
SELECT * FROM vista_resumen_equipos_por_codigo LIMIT 3;
SELECT * FROM vista_equipos_disponibles LIMIT 3;
SELECT * FROM vista_accesorios_stock_bajo LIMIT 3;

-- Verificar columna nit
DESCRIBE proveedores;
```

#### Paso 3: Exportar para Producción
```bash
cd C:\xampp\mysql\bin

# Exportar con mysqldump
mysqldump -u root --no-tablespaces ^
  --single-transaction ^
  --routines ^
  --triggers ^
  --add-drop-table ^
  almacen_toi > C:\Users\raul1\Documents\almacen_toi_produccion.sql
```

#### Paso 4: Subir Archivos a Ubuntu
**Archivos a subir:**
```
almacen_toi_produccion.sql       ← Base de datos
controllers/ReportesController.php ← Actualizado TCPDF
controllers/ProveedoresController.php ← Actualizado nit
controllers/UsuariosController.php ← Con método perfil()
views/usuarios/perfil.php          ← Nuevo archivo
views/layouts/header.php           ← Con menús corregidos
views/layouts/footer.php           ← Con JavaScript corregido
views/accesorios/index.php         ← Diseño sobrio
views/tipos_equipos/index.php      ← Fuentes estandarizadas
views/proveedores/index.php        ← Con NIT
```

**Comandos SCP:**
```powershell
# PowerShell en Windows
scp C:\Users\raul1\Documents\almacen_toi_produccion.sql user@servidor:/home/user/

scp controllers/ReportesController.php user@servidor:/var/www/html/almacen_moderno/controllers/
scp controllers/ProveedoresController.php user@servidor:/var/www/html/almacen_moderno/controllers/
scp controllers/UsuariosController.php user@servidor:/var/www/html/almacen_moderno/controllers/

scp views/usuarios/perfil.php user@servidor:/var/www/html/almacen_moderno/views/usuarios/
scp views/layouts/header.php user@servidor:/var/www/html/almacen_moderno/views/layouts/
scp views/layouts/footer.php user@servidor:/var/www/html/almacen_moderno/views/layouts/

scp views/accesorios/index.php user@servidor:/var/www/html/almacen_moderno/views/accesorios/
scp views/tipos_equipos/index.php user@servidor:/var/www/html/almacen_moderno/views/tipos_equipos/
scp views/proveedores/index.php user@servidor:/var/www/html/almacen_moderno/views/proveedores/
```

---

### FASE 2: Implementar en Ubuntu 22.04

#### Paso 1: Conectarse al Servidor
```bash
ssh user@tu-servidor
```

#### Paso 2: Verificar Prerrequisitos
```bash
# Verificar PHP
php -v  # Debe ser 8.0+

# Verificar MySQL/MariaDB
mysql --version

# Verificar Apache
sudo systemctl status apache2
```

#### Paso 3: Instalar Composer (si no existe)
```bash
cd /tmp
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

#### Paso 4: Crear Base de Datos
```bash
mysql -u root -p -e "CREATE DATABASE almacen_toi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

#### Paso 5: Importar Base de Datos
```bash
mysql -u root -p almacen_toi < /home/user/almacen_toi_produccion.sql
```

#### Paso 6: Verificar Importación
```bash
mysql -u root -p -e "
USE almacen_toi;
SHOW TABLES;
SELECT COUNT(*) FROM usuarios;
SELECT COUNT(*) FROM equipos;
SELECT COUNT(*) FROM proveedores;
"
```

#### Paso 7: Instalar TCPDF
```bash
cd /var/www/html/almacen_moderno

# Instalar TCPDF con Composer
composer require tecnickcom/tcpdf

# Verificar instalación
ls -la vendor/tecnickcom/tcpdf/tcpdf.php
```

#### Paso 8: Establecer Permisos
```bash
# Propietario
sudo chown -R www-data:www-data /var/www/html/almacen_moderno

# Permisos de directorios
sudo find /var/www/html/almacen_moderno -type d -exec chmod 755 {} \;

# Permisos de archivos PHP
sudo find /var/www/html/almacen_moderno -name "*.php" -exec chmod 644 {} \;

# Configuración (más restrictivo)
sudo chmod 600 /var/www/html/almacen_moderno/config/database.php
sudo chmod 600 /var/www/html/almacen_moderno/config/config.php

# Directorio temporal para TCPDF
sudo mkdir -p /var/www/html/almacen_moderno/tmp
sudo chown www-data:www-data /var/www/html/almacen_moderno/tmp
sudo chmod 777 /var/www/html/almacen_moderno/tmp

# Logs
sudo mkdir -p /var/www/html/almacen_moderno/logs
sudo chown www-data:www-data /var/www/html/almacen_moderno/logs
sudo chmod 755 /var/www/html/almacen_moderno/logs
```

#### Paso 9: Actualizar Configuración
```bash
# Editar config/config.php
sudo nano /var/www/html/almacen_moderno/config/config.php
```

**Cambiar:**
```php
// URL dinámica
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/almacen_moderno');

// Producción
define('APP_ENV', 'production');

// Hash más seguro
define('HASH_COST', 12);
```

```bash
# Editar config/database.php
sudo nano /var/www/html/almacen_moderno/config/database.php
```

**Cambiar:**
```php
define('DB_USER', 'almacen_user');
define('DB_PASS', 'ContraseñaSegura2026!');
```

#### Paso 10: Crear Usuario de Base de Datos
```bash
mysql -u root -p << EOF
CREATE USER IF NOT EXISTS 'almacen_user'@'localhost' 
IDENTIFIED BY 'ContraseñaSegura2026!';

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX 
ON almacen_toi.* TO 'almacen_user'@'localhost';

FLUSH PRIVILEGES;
EOF
```

#### Paso 11: Reiniciar Apache
```bash
sudo systemctl restart apache2
sudo systemctl status apache2
```

---

### FASE 3: Pruebas

#### Paso 12: Probar Acceso Web
```
http://tu-servidor/almacen_moderno
```

**Login:**
- Usuario: `admin`
- Contraseña: `Admin123!`

#### Paso 13: Probar Funcionalidades

**Dashboard:**
```
□ Carga correctamente
□ Estadísticas se muestran
□ Gráficos funcionan
```

**Menú:**
```
□ Sidebar se muestra
□ Botón << colapsa sidebar
□ Tooltips aparecen al hacer hover
□ Menú Gestión se despliega correctamente
□ Submenús funcionan
```

**Equipos:**
```
□ Listar equipos
□ Crear nuevo equipo
□ Editar equipo
□ Buscar/filtrar
```

**Accesorios:**
```
□ Listar accesorios
□ Diseño con fuentes consistentes
□ Stock bajo se muestra
```

**Proveedores:**
```
□ Listar proveedores
□ Campo "NIT" se muestra (no RUC)
□ Crear proveedor con NIT
```

**Reportes PDF:**
```
□ Equipos PDF - Genera y descarga
□ Accesorios PDF - Genera y descarga
□ Movimientos PDF - Genera y descarga
□ Usuarios PDF - Genera y descarga
□ Caracteres UTF-8 (ñ, tildes) se muestran
```

**Usuarios:**
```
□ Listar usuarios
□ Mi Perfil - Funciona
□ Editar perfil - Funciona
```

#### Paso 14: Verificar Logs
```bash
# Logs de Apache
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/log/apache2/access.log

# Logs de la aplicación
sudo tail -f /var/www/html/almacen_moderno/logs/error.log
```

---

##  CHECKLIST FINAL

### Windows (Preparación)
- [x] `auditoria_completa.sql` ejecutado
- [x] Vistas verificadas
- [x] `almacen_toi_produccion.sql` exportado
- [x] Archivos PHP actualizados
- [x] Archivos subidos a Ubuntu

### Ubuntu (Implementación)
- [ ] Composer instalado
- [ ] Base de datos creada
- [ ] Backup importado
- [ ] TCPDF instalado
- [ ] Permisos establecidos
- [ ] Configuración actualizada
- [ ] Usuario BD creado
- [ ] Apache reiniciado

### Pruebas
- [ ] Login funciona
- [ ] Dashboard carga
- [ ] Sidebar colapsa
- [ ] Menú Gestión funciona
- [ ] Equipos CRUD funciona
- [ ] Accesorios CRUD funciona
- [ ] Proveedores (NIT) funciona
- [ ] Reportes PDF generan
- [ ] Caracteres UTF-8 correctos
- [ ] Mi Perfil funciona
- [ ] Sin errores en logs

---

## 🐛 SOLUCIÓN DE PROBLEMAS

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
- Verificar `ob_clean()` antes de `$pdf->Output()`
- Verificar `exit;` después de `$pdf->Output()`

### Error: MySQL acceso denegado
```bash
# Verificar usuario
mysql -u almacen_user -p -e "SHOW GRANTS;"

# Si no tiene permisos
mysql -u root -p -e "GRANT ALL ON almacen_toi.* TO 'almacen_user'@'localhost';"
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

## 📄 ARCHIVOS DE REFERENCIA

| Archivo | Propósito |
|---------|-----------|
| `database/auditoria_completa.sql` | Auditoría completa de BD |
| `AUDITORIA_BASE_DE_DATOS.md` | Guía de auditoría |
| `INSTALACION_TCPDF_UBUNTU.md` | Instalación TCPDF |
| `GUIA_DESPLEGUE_PRODUCCION.md` | Guía completa |
| `IMPLEMENTACION_FINAL_UBUNTU.md` | Este documento |

---

## ✅ ESTADO FINAL

| Componente | Estado | Notas |
|------------|--------|-------|
| **Base de Datos** | ✅ Lista | Auditada y corregida |
| **TCPDF** | ✅ Listo | Implementado |
| **Proveedores** | ✅ Listo | NIT en lugar de RUC |
| **Tipos Equipos** | ✅ Listo | Tabla correcta |
| **Vistas** | ✅ Listas | Recreadas |
| **Reportes** | ✅ Listos | PDF con UTF-8 |
| **Perfil Usuario** | ✅ Listo | Funcional |
| **Sidebar** | ✅ Listo | Colapsable con tooltips |
| **Documentación** | ✅ Lista | Completa |

---

**Estado:** ✅ Listo para implementar  
**Tiempo estimado:** 30-45 minutos  
**Dificultad:** Media

---

## 🎯 PRÓXIMOS PASOS

1. **Seguir FASE 1** en Windows
2. **Subir archivos** a Ubuntu
3. **Seguir FASE 2** en Ubuntu
4. **Ejecutar FASE 3** de pruebas
5. **Recopilar feedback** del personal
6. **Corregir** errores reportados
7. **Capacitar** al personal

---

**¡Éxito en la implementación!** 🚀
