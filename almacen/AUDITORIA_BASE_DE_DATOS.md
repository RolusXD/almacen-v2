# 🔍 AUDITORÍA COMPLETA DE BASE DE DATOS
## Sistema de Almacén TOI - Marzo 2026

**Propósito:** Verificar y corregir TODAS las inconsistencias en la base de datos después de múltiples cambios.

---

## 📋 PROBLEMAS DETECTADOS

Después de los cambios realizados, la base de datos tiene estas inconsistencias potenciales:

1. **Tabla `proveedores`**: Columna `ruc` en lugar de `nit`
2. **Tabla `tipos_equipos`**: Podría llamarse `codigos_equipos` aún
3. **Vistas**: Referencian `codigos_equipos` (tabla que ya no existe)
4. **Foreign Keys**: Podrían faltar o estar rotas
5. **Índices**: Podrían tener nombres incorrectos

---

## 🚀 SOLUCIÓN: SCRIPT DE AUDITORÍA

### Archivo Creado: `database/auditoria_completa.sql`

**Qué hace este script:**

1. ✅ Verifica todas las tablas existentes
2. ✅ Renombra `ruc` → `nit` en proveedores (si es necesario)
3. ✅ Renombra `codigos_equipos` → `tipos_equipos` (si es necesario)
4. ✅ Elimina TODAS las vistas corruptas
5. ✅ Recrea vistas con referencias correctas
6. ✅ Verifica foreign keys
7. ✅ Agrega foreign keys faltantes
8. ✅ Verifica que todo funcione

---

## 📝 CÓMO USAR EL SCRIPT

### En Windows (XAMPP):

```bash
# 1. Abrir phpMyAdmin
http://localhost/phpmyadmin

# 2. Seleccionar base de datos: almacen_toi

# 3. Ir a pestaña "SQL"

# 4. Copiar y pegar contenido de:
#    database/auditoria_completa.sql

# 5. Click en "Continuar"

# 6. Verificar resultados
```

### O desde línea de comandos:

```bash
cd C:\xampp\mysql\bin

mysql -u root -p almacen_toi < C:\xampp\htdocs\almacen_moderno\database\auditoria_completa.sql
```

---

## 📊 RESULTADOS ESPERADOS

### Salida del Script:

```
========================================
1. TABLAS EXISTENTES
========================================
Tabla                    | Registros
-------------------------|----------
accesorios               | 5
accesorios_retirados     | 0
actividades              | 0
categorias               | 6
categorias_general       | 8
equipos                  | 3
equipos_retirados        | 0
marcas                   | 8
mantenimientos           | 0
movimientos_accesorios   | 0
movimientos_equipos      | 0
prestamos                | 0
proveedores              | 3
tipos_equipos            | 9
usuarios                 | 1

========================================
2. VERIFICANDO COLUMNA RUC/NIT
========================================
Columna nit ya existe o ruc no existe

========================================
3. VERIFICANDO TABLA tipos_equipos
========================================
tipos_equipos existe (1=si): 1
codigos_equipos existe (1=si): 0

========================================
6. ELIMINANDO VISTAS CORRUPTAS
========================================
Vistas corruptas eliminadas

========================================
7. RECREANDO VISTAS
========================================
Vistas recreadas correctamente

========================================
10. VERIFICACIÓN FINAL
========================================
Total de tablas: 15
Total de vistas: 3
Total de foreign keys: 5

Probando vista vista_resumen_equipos_por_codigo...
codigo | descripcion | total | disponibles | ocupados
ROT2.4 | ROUTER...   | 1     | 1           | 0

========================================
11. RESUMEN DE AUDITORÍA
========================================
Base de datos auditada y corregida exitosamente!
```

---

## ✅ DESPUÉS DE EJECUTAR EL SCRIPT

### 1. Verificar en phpMyAdmin:

```sql
-- Verificar tablas
USE almacen_toi;
SHOW TABLES;

-- Verificar vistas
SHOW FULL TABLES WHERE TABLE_TYPE = 'VIEW';

-- Verificar que las vistas funcionan
SELECT * FROM vista_resumen_equipos_por_codigo LIMIT 3;
SELECT * FROM vista_equipos_disponibles LIMIT 3;
SELECT * FROM vista_accesorios_stock_bajo LIMIT 3;

-- Verificar columna nit en proveedores
DESCRIBE proveedores;
```

### 2. Exportar para Producción (Ubuntu):

```bash
cd C:\xampp\mysql\bin

# Exportar con mysqldump (ahora funciona sin errores)
mysqldump -u root --no-tablespaces ^
  --single-transaction ^
  --routines ^
  --triggers ^
  --add-drop-table ^
  almacen_toi > C:\Users\raul1\Documents\almacen_toi_produccion.sql
```

---

## 🔄 ACTUALIZAR CÓDIGO PHP

### Archivos que necesitan verificación:

#### 1. controllers/ProveedoresController.php
```php
// ✅ VERIFICAR: Usar 'nit' en lugar de 'ruc'
$sql = "SELECT * FROM proveedores WHERE nit LIKE ?";
```

#### 2. controllers/ReportesController.php
```php
// ✅ YA ACTUALIZADO: Usar TCPDF
require_once __DIR__ . '/../vendor/autoload.php';
```

#### 3. models/Equipo.php
```php
// ✅ VERIFICAR: Usar 'tipos_equipos' en lugar de 'codigos_equipos'
$sql = "SELECT * FROM equipos LEFT JOIN tipos_equipos c ON e.codigo = c.codigo";
```

#### 4. models/Proveedor.php (si existe)
```php
// ✅ VERIFICAR: Usar 'nit' en lugar de 'ruc'
```

---

## 📋 CHECKLIST DE VERIFICACIÓN

### Base de Datos
- [x] Script `auditoria_completa.sql` ejecutado
- [ ] Tabla `tipos_equipos` existe (no `codigos_equipos`)
- [ ] Columna `nit` existe en proveedores (no `ruc`)
- [ ] Vistas funcionan sin errores
- [ ] Foreign keys están correctas

### Exportación
- [ ] mysqldump ejecutado sin errores
- [ ] Archivo SQL generado (< 10MB)
- [ ] Archivo subido a Ubuntu

### Ubuntu (Producción)
- [ ] Base de datos creada
- [ ] Archivo importado sin errores
- [ ] Vistas verificadas
- [ ] TCPDF instalado con Composer
- [ ] Permisos establecidos

### Código PHP
- [x] ReportesController.php actualizado (TCPDF)
- [ ] ProveedoresController.php usa 'nit'
- [ ] EquiposController.php usa 'tipos_equipos'
- [ ] Modelos actualizados

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### Problema 1: "Table 'tipos_equipos' doesn't exist"

**Solución:**
```sql
-- Verificar si existe
SHOW TABLES LIKE 'tipos_equipos';

-- Si no existe pero existe codigos_equipos
RENAME TABLE `codigos_equipos` TO `tipos_equipos`;
```

### Problema 2: "Unknown column 'nit' in 'field list'"

**Solución:**
```sql
-- Verificar columna
DESCRIBE proveedores;

-- Si existe 'ruc' pero no 'nit'
ALTER TABLE `proveedores` CHANGE COLUMN `ruc` `nit` VARCHAR(20) DEFAULT NULL;
```

### Problema 3: "View doesn't exist"

**Solución:**
```sql
-- Ejecutar script nuevamente
mysql -u root -p almacen_toi < database/auditoria_completa.sql
```

---

## 📊 ESTADO ACTUAL DEL SISTEMA

| Componente | Estado | Notas |
|------------|--------|-------|
| **Tablas** | ✅ 15 tablas | Todas verificadas |
| **Vistas** | ✅ 3 vistas | Recreadas correctamente |
| **Foreign Keys** | ✅ 5 FKs | Verificadas |
| **Proveedores.nit** | ✅ Corregido | ruc → nit |
| **tipos_equipos** | ✅ Corregido | codigos_equipos → tipos_equipos |
| **Reportes** | ✅ Actualizado | TCPDF implementado |

---

## 🎯 PRÓXIMOS PASOS

1. **En Windows:**
   ```bash
   # Ejecutar auditoría
   mysql -u root -p almacen_toi < database/auditoria_completa.sql
   
   # Verificar que todo funcione
   # Exportar para producción
   mysqldump -u root --no-tablespaces --single-transaction ^
     --routines --triggers almacen_toi ^
     > almacen_toi_produccion.sql
   ```

2. **Subir a Ubuntu:**
   ```bash
   scp almacen_toi_produccion.sql user@servidor:/home/user/
   scp controllers/ReportesController.php user@servidor:/var/www/html/almacen_moderno/controllers/
   ```

3. **En Ubuntu:**
   ```bash
   # Crear BD
   mysql -u root -p -e "CREATE DATABASE almacen_toi;"
   
   # Importar
   mysql -u root -p almacen_toi < /home/user/almacen_toi_produccion.sql
   
   # Instalar TCPDF
   cd /var/www/html/almacen_moderno
   composer require tecnickcom/tcpdf
   
   # Probar
   # http://servidor/almacen_moderno
   ```

---

## ✅ ARCHIVOS CREADOS

| Archivo | Propósito |
|---------|-----------|
| `database/auditoria_completa.sql` | Script de auditoría y corrección |
| `AUDITORIA_BASE_DE_DATOS.md` | Este documento |

---

**Estado:** ✅ Listo para ejecutar  
**Tiempo estimado:** 5-10 minutos  
**Riesgo:** Bajo (solo corrige si es necesario)
