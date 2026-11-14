# 🔧 SOLUCIÓN ERROR #1044 - PERMISOS MySQL
## Sistema de Almacén TOI

**Error:** `#1044 - Acceso denegado para usuario: 'root'@'localhost' a la base de datos 'information_schema'`

---

## ❌ CAUSA DEL PROBLEMA

En **Ubuntu**, MySQL usa **autenticación por socket** para el usuario root. Esto significa:

- ✅ `sudo mysql` funciona (sin contraseña)
- ❌ `mysql -u root -p` puede fallar (con contraseña)
- ❌ phpMyAdmin puede tener problemas de permisos

---

## ✅ SOLUCIONES

### SOLUCIÓN 1: Usar sudo mysql (Recomendado)

**En Ubuntu:**
```bash
# Conectarse como root con sudo
sudo mysql -u root

# O sin password
sudo mysql
```

**Luego ejecutar los scripts:**
```sql
USE almacen_toi;
SOURCE /var/www/html/almacen_moderno/database/corregir_estructura_equipos_v2.sql;
```

---

### SOLUCIÓN 2: Habilitar Password para root

**Si necesitás usar contraseña:**

```bash
# Conectarse con sudo
sudo mysql -u root

# En MySQL, ejecutar:
USE mysql;

-- Cambiar método de autenticación a mysql_native_password
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'TuContraseña2026!';
FLUSH PRIVILEGES;

-- Verificar cambio
SELECT User, Host, plugin FROM user WHERE User = 'root';
```

**Ahora podés usar:**
```bash
mysql -u root -p
# Contraseña: TuContraseña2026!
```

---

### SOLUCIÓN 3: Crear Usuario Administrador

**Si no querés modificar root:**

```bash
# Conectarse con sudo
sudo mysql -u root
```

```sql
-- Crear usuario administrador
CREATE USER 'admin_toi'@'localhost' IDENTIFIED BY 'Admin2026!';

-- Dar todos los privilegios en almacen_toi
GRANT ALL PRIVILEGES ON almacen_toi.* TO 'admin_toi'@'localhost';

-- Dar privilegios adicionales necesarios
GRANT ALTER, CREATE, DROP, INDEX, INSERT, UPDATE, DELETE, SELECT 
ON almacen_toi.* TO 'admin_toi'@'localhost';

FLUSH PRIVILEGES;

-- Verificar
SHOW GRANTS FOR 'admin_toi'@'localhost';
```

**Usar en phpMyAdmin:**
```
Usuario: admin_toi
Contraseña: Admin2026!
```

---

### SOLUCIÓN 4: Configurar phpMyAdmin

**Si el error es en phpMyAdmin:**

```bash
# Editar configuración de phpMyAdmin
sudo nano /etc/phpmyadmin/config-db.php
```

**Cambiar:**
```php
// De:
$dbuser = 'root';
$dbpass = '';

// A:
$dbuser = 'admin_toi';
$dbpass = 'Admin2026!';
```

**O crear usuario pma:**
```sql
sudo mysql -u root

CREATE USER 'pma'@'localhost' IDENTIFIED BY 'pma2026';
GRANT ALL PRIVILEGES ON almacen_toi.* TO 'pma'@'localhost';
FLUSH PRIVILEGES;
```

---

## 🚀 EJECUCIÓN DE SCRIPTS

### Método 1: Línea de Comandos (Recomendado)

```bash
# Conectarse con sudo
sudo mysql -u root

# Usar base de datos
USE almacen_toi;

# Ejecutar script
SOURCE /var/www/html/almacen_moderno/database/corregir_estructura_equipos_v2.sql;

# Salir
EXIT;
```

### Método 2: Redirección

```bash
# Con sudo
sudo mysql -u root almacen_toi < /var/www/html/almacen_moderno/database/corregir_estructura_equipos_v2.sql

# O con usuario administrador
mysql -u admin_toi -p almacen_toi < /var/www/html/almacen_moderno/database/corregir_estructura_equipos_v2.sql
```

### Método 3: phpMyAdmin

1. **Iniciar sesión** con usuario administrador
2. **Seleccionar** `almacen_toi`
3. **Pestaña SQL**
4. **Copiar contenido** de `corregir_estructura_equipos_v2.sql`
5. **Click en "Continuar"**

---

## 📋 ORDEN DE EJECUCIÓN

### En Ubuntu:

```bash
# 1. Conectarse con sudo
sudo mysql -u root

# 2. Ejecutar scripts en orden
USE almacen_toi;
SOURCE /var/www/html/almacen_moderno/database/auditoria_completa.sql;
SOURCE /var/www/html/almacen_moderno/database/corregir_estructura_equipos_v2.sql;
SOURCE /var/www/html/almacen_moderno/database/corregir_foreign_key.sql;

# 3. Verificar
SHOW TABLES;
SELECT * FROM vista_resumen_equipos_por_codigo LIMIT 3;

# 4. Salir
EXIT;
```

---

## 🔍 VERIFICACIÓN DE PERMISOS

### Verificar Usuario Actual:
```sql
SELECT CURRENT_USER();
```

### Verificar Privilegios:
```sql
SHOW GRANTS FOR CURRENT_USER();
```

### Verificar si puede hacer ALTER:
```sql
USE almacen_toi;
SHOW GRANTS FOR 'root'@'localhost';
-- Debe mostrar: ALTER, ALTER ROUTINE, CREATE, etc.
```

---

## 🐛 PROBLEMAS COMUNES

### Problema 1: "Access denied for user 'root'@'localhost'"

**Solución:**
```bash
# Usar sudo
sudo mysql -u root

# O editar plugin de autenticación
sudo mysql
USE mysql;
UPDATE user SET plugin='mysql_native_password' WHERE User='root';
FLUSH PRIVILEGES;
```

### Problema 2: "The requested server connection failed"

**Solución:**
```bash
# Verificar si MySQL está corriendo
sudo systemctl status mysql

# Reiniciar si es necesario
sudo systemctl restart mysql
```

### Problema 3: phpMyAdmin no puede conectarse

**Solución:**
```bash
# Reconfigurar phpMyAdmin
sudo dpkg-reconfigure phpmyadmin

# O crear usuario específico
sudo mysql -u root
CREATE USER 'phpmyadmin'@'localhost' IDENTIFIED BY 'pma2026';
GRANT ALL PRIVILEGES ON almacen_toi.* TO 'phpmyadmin'@'localhost';
FLUSH PRIVILEGES;
```

---

## ✅ CHECKLIST

- [ ] Conectado con `sudo mysql -u root`
- [ ] Script `auditoria_completa.sql` ejecutado
- [ ] Script `corregir_estructura_equipos_v2.sql` ejecutado
- [ ] Script `corregir_foreign_key.sql` ejecutado
- [ ] Tablas verificadas
- [ ] Vistas funcionan
- [ ] INSERT de prueba funciona
- [ ] phpMyAdmin puede conectarse

---

## 📞 COMANDOS RÁPIDOS

```bash
# Conectarse como root
sudo mysql -u root

# Conectarse con usuario
mysql -u admin_toi -p

# Ejecutar script
sudo mysql -u root almacen_toi < database/corregir_estructura_equipos_v2.sql

# Verificar estructura
mysql -u root -e "USE almacen_toi; DESCRIBE equipos;"

# Verificar vistas
mysql -u root -e "USE almacen_toi; SELECT * FROM vista_resumen_equipos_por_codigo LIMIT 3;"
```

---

## 🎯 RESUMEN

**Problema:** Permisos insuficientes en MySQL  
**Causa:** Autenticación por socket en Ubuntu  
**Solución:** Usar `sudo mysql -u root`  
**Tiempo:** 2-3 minutos  

**Script Actualizado:** `corregir_estructura_equipos_v2.sql` (sin information_schema)

---

**Estado:** ✅ Solución lista  
**Próximo Paso:** Ejecutar con `sudo mysql`
