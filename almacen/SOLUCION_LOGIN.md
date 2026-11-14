# 🔧 SOLUCIÓN - Problema de Inicio de Sesión

## Problema Reportado
Las credenciales iniciales no funcionan aunque hay conexión a la base de datos.

## Causa Probable
El hash de contraseña almacenado en la base de datos puede no ser compatible con su versión de PHP, o la tabla no se ha creado correctamente.

---

## ✅ SOLUCIÓN PASO A PASO

### Opción 1: Usar el Script de Diagnóstico (RECOMENDADO)

1. **Abra su navegador y vaya a:**
   ```
   http://localhost/almacen_moderno/diagnostico.php
   ```

2. **El script verificará:**
   - ✅ Conexión a la base de datos
   - ✅ Existencia de la tabla `usuarios`
   - ✅ Estructura de la tabla
   - ✅ Existencia del usuario `admin`

3. **En la sección "Acciones Rápidas":**
   - Click en el botón **"🔒 Actualizar Contraseña del Admin"**
   - Esto generará un hash compatible con su versión de PHP

4. **Ahora intente iniciar sesión:**
   ```
   Usuario: admin
   Contraseña: Admin123!
   ```

---

### Opción 2: Ejecutar SQL Manualmente

1. **Abra phpMyAdmin:**
   ```
   http://localhost/phpmyadmin
   ```

2. **Seleccione la base de datos:**
   ```
   almacen_toi
   ```

3. **Vaya a la pestaña "SQL" y ejecute:**
   ```sql
   USE almacen_toi;
   
   -- Eliminar admin si existe
   DELETE FROM usuarios WHERE usuario = 'admin';
   
   -- Crear usuario admin con contraseña temporal
   INSERT INTO usuarios (nombre_completo, usuario, password_hash, carnet, email, fecha_nacimiento, rol, activo) 
   VALUES (
       'Administrador Principal TOI',
       'admin',
       '$2y$10$e0MYZfJKLzQb8qXJx5vWuO.9KqGhN5RjLpX4dYwH8VqT2nF6mR3sC',
       '00000000',
       'admin@toi.bo',
       '1990-01-01',
       'admin',
       1
   );
   ```

4. **Intente iniciar sesión**

---

### Opción 3: Generar Hash Manualmente

Si las opciones anteriores no funcionan, genere un hash manualmente:

1. **Cree un archivo PHP temporal** (`temp_hash.php`):
   ```php
   <?php
   // Ejecute este archivo en el navegador
   $password = 'Admin123!';
   $hash = password_hash($password, PASSWORD_DEFAULT);
   echo "Hash generado: " . $hash;
   ?>
   ```

2. **Abra en el navegador:**
   ```
   http://localhost/almacen_moderno/temp_hash.php
   ```

3. **Copie el hash generado**

4. **Ejecute en phpMyAdmin:**
   ```sql
   UPDATE usuarios 
   SET password_hash = 'PEGUE_EL_HASH_GENERADO_AQUI' 
   WHERE usuario = 'admin';
   ```

5. **Elimine el archivo temporal**

---

## 🔍 Verificación de Problemas Comunes

### Problema 1: La tabla `usuarios` no existe
**Solución:**
```sql
-- En phpMyAdmin, ejecute:
Source database/schema.sql
```

### Problema 2: El campo `usuario` no existe en la tabla
**Solución:**
La tabla puede ser de una versión anterior. Ejecute:
```sql
-- Verificar columnas
DESCRIBE usuarios;

-- Si falta la columna 'usuario', necesita recrear la tabla
-- Importe database/schema.sql nuevamente
```

### Problema 3: La contraseña tiene espacios en blanco
**Solución:**
Asegúrese de escribir exactamente:
```
Usuario: admin (sin espacios)
Contraseña: Admin123! (exactamente así, sensible a mayúsculas)
```

### Problema 4: El usuario está inactivo
**Solución:**
```sql
UPDATE usuarios SET activo = 1 WHERE usuario = 'admin';
```

---

## 📋 Credenciales Correctas

| Campo | Valor |
|-------|-------|
| **Usuario** | `admin` |
| **Contraseña** | `Admin123!` |
| **Carnet** | `00000000` |
| **Email** | `admin@toi.bo` |

**Nota:** El sistema usa el campo `usuario` para login, NO el carnet.

---

## 🧪 Probar Conexión desde PHP

Cree un archivo de prueba (`test_db.php`):

```php
<?php
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=almacen_toi;charset=utf8mb4",
        "root",
        ""
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar usuario admin
    $stmt = $pdo->query("SELECT usuario, password_hash FROM usuarios WHERE usuario = 'admin'");
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ Usuario admin encontrado\n";
        echo "Hash almacenado: " . $user['password_hash'] . "\n";
        
        // Probar verificación
        $password = 'Admin123!';
        if (password_verify($password, $user['password_hash'])) {
            echo "✅ La contraseña es VÁLIDA\n";
        } else {
            echo "❌ La contraseña NO coincide con el hash\n";
            echo "Generando nuevo hash...\n";
            $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
            echo "Nuevo hash: " . $nuevoHash . "\n";
        }
    } else {
        echo "❌ Usuario admin NO encontrado\n";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
```

---

## 📞 Si Nada Funciona

1. **Ejecute el diagnóstico:**
   ```
   http://localhost/almacen_moderno/diagnostico.php
   ```

2. **Tome captura de pantalla del resultado**

3. **Verifique los logs de error de PHP:**
   ```
   C:\xampp\apache\logs\error.log
   ```

4. **Asegúrese de que:**
   - XAMPP esté ejecutándose (Apache y MySQL)
   - La base de datos `almacen_toi` exista
   - Los archivos del sistema estén en `C:\xampp\htdocs\almacen_moderno\`

---

## ✅ Checklist de Verificación

- [ ] XAMPP ejecutándose (Apache + MySQL)
- [ ] Base de datos `almacen_toi` creada
- [ ] Tabla `usuarios` existe con columnas correctas
- [ ] Usuario `admin` existe en la tabla
- [ ] Campo `activo` = 1 para el usuario admin
- [ ] Hash de contraseña generado con la versión actual de PHP
- [ ] Archivo `config/database.php` con credenciales correctas
- [ ] Archivo `config/config.php` cargando correctamente
- [ ] Sesiones de PHP habilitadas

---

**Última actualización:** Marzo 2026  
**Versión del sistema:** 2.0
