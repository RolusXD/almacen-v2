# 🔧 CORRECCIÓN DEFINITIVA - id_equipo
## Error #1075 - PRIMARY KEY requerida

**Error:** `#1075 - Definición incorrecta de tabla; sólo puede haber una columna automática y ésta debe de ser definida como una clave`

---

## ❌ CAUSA DEL PROBLEMA

MySQL requiere que **TODA columna AUTO_INCREMENT sea PRIMARY KEY**.

El script anterior intentó agregar AUTO_INCREMENT sin verificar si `id_equipo` era PRIMARY KEY.

---

## ✅ SOLUCIÓN DEFINITIVA

### Script Creado: `database/corregir_estructura_equipos_v4.sql`

**Qué hace:**
1. ✅ Verifica estructura actual
2. ✅ Agrega PRIMARY KEY si no existe
3. ✅ Agrega AUTO_INCREMENT
4. ✅ Prueba con INSERT

---

## 🚀 EJECUCIÓN EN UBUNTU

### Método 1: Con sudo mysql (Recomendado)

```bash
# Conectarse
sudo mysql -u root

# Usar base de datos
USE almacen_toi;

# Ejecutar script
SOURCE /var/www/html/almacen_moderno/database/corregir_estructura_equipos_v4.sql;

# Salir
EXIT;
```

### Método 2: Redirección

```bash
sudo mysql -u root almacen_toi < /var/www/html/almacen_moderno/database/corregir_estructura_equipos_v4.sql
```

### Método 3: Manual Paso a Paso

```bash
sudo mysql -u root
```

```sql
USE almacen_toi;

-- Paso 1: Verificar estructura actual
SHOW COLUMNS FROM equipos;
SHOW INDEX FROM equipos;

-- Paso 2: Asegurar NOT NULL
ALTER TABLE `equipos` MODIFY COLUMN `id_equipo` INT(11) NOT NULL;

-- Paso 3: Agregar PRIMARY KEY (si no existe)
ALTER TABLE `equipos` ADD PRIMARY KEY (`id_equipo`);

-- Paso 4: Agregar AUTO_INCREMENT
ALTER TABLE `equipos` MODIFY COLUMN `id_equipo` INT(11) NOT NULL AUTO_INCREMENT;

-- Paso 5: Verificar
SHOW COLUMNS FROM equipos WHERE Field = 'id_equipo';

-- Paso 6: Corregir fecha_entrada
ALTER TABLE `equipos` MODIFY COLUMN `fecha_entrada` DATE DEFAULT NULL;

-- Paso 7: Probar INSERT
INSERT INTO equipos (nombre, numero_serie_mac, estado, calidad, fecha_entrada)
VALUES ('TEST', '123', 'DISPONIBLE', 'NUEVO', CURDATE());

-- Verificar
SELECT * FROM equipos WHERE numero_serie_mac = '123';

-- Limpiar
DELETE FROM equipos WHERE numero_serie_mac = '123';

EXIT;
```

---

## 📋 ORDEN COMPLETO DE EJECUCIÓN

```bash
# 1. Auditoría general
sudo mysql -u root almacen_toi < database/auditoria_completa.sql

# 2. Corregir estructura de equipos (USAR v4)
sudo mysql -u root almacen_toi < database/corregir_estructura_equipos_v4.sql

# 3. Corregir foreign keys
sudo mysql -u root almacen_toi < database/corregir_foreign_key.sql

# 4. Verificar
sudo mysql -u root -e "USE almacen_toi; SHOW TABLES;"
sudo mysql -u root -e "USE almacen_toi; DESCRIBE equipos;"
```

---

## 🔍 VERIFICACIÓN POST-CORRECCIÓN

```sql
-- En MySQL:
USE almacen_toi;

-- Verificar id_equipo
DESCRIBE equipos;

-- Debe mostrar:
-- Field: id_equipo
-- Type: int(11)
-- Null: NO
-- Key: PRI  ← IMPORTANTE
-- Extra: auto_increment  ← IMPORTANTE

-- Verificar índices
SHOW INDEX FROM equipos;

-- Debe mostrar PRIMARY KEY en id_equipo
```

---

## 🐛 SI EL SCRIPT FALLA

### Error: "Multiple primary key defined"

**Causa:** Ya existe otra PRIMARY KEY

**Solución:**
```sql
-- Ver qué columna es PRIMARY KEY
SHOW INDEX FROM equipos WHERE Key_name = 'PRIMARY';

-- Si hay otra columna como PRIMARY KEY, removerla
ALTER TABLE equipos DROP PRIMARY KEY;

-- Luego agregar id_equipo como PRIMARY KEY
ALTER TABLE equipos ADD PRIMARY KEY (id_equipo);
ALTER TABLE equipos MODIFY COLUMN id_equipo INT(11) NOT NULL AUTO_INCREMENT;
```

### Error: "Can't DROP PRIMARY KEY"

**Causa:** Necesitás saber qué columna es la PRIMARY KEY actual

**Solución:**
```sql
-- Ver estructura completa
SHOW CREATE TABLE equipos;

-- Copiar la definición y modificar manualmente
```

### Error: "Duplicate entry for key 'PRIMARY'"

**Causa:** Hay valores duplicados en id_equipo

**Solución:**
```sql
-- Ver duplicados
SELECT id_equipo, COUNT(*) as count
FROM equipos
GROUP BY id_equipo
HAVING count > 1;

-- Si hay duplicados, necesitarás eliminarlos o reasignar IDs
-- Esto es más complejo, contactame si pasa esto
```

---

## ✅ CHECKLIST DE VERIFICACIÓN

Después de ejecutar el script:

- [ ] `id_equipo` es `INT(11)`
- [ ] `id_equipo` es `NOT NULL`
- [ ] `id_equipo` es `PRIMARY KEY` (Key: PRI)
- [ ] `id_equipo` tiene `auto_increment` (Extra: auto_increment)
- [ ] `fecha_entrada` tiene `DEFAULT NULL`
- [ ] INSERT funciona sin especificar id_equipo
- [ ] No hay errores en logs

---

## 📊 ESTRUCTURA ESPERADA

```sql
+------------------+--------------+------+-----+-------------------+
| Field            | Type         | Null | Key | Extra             |
+------------------+--------------+------+-----+-------------------+
| id_equipo        | int(11)      | NO   | PRI | auto_increment    | ← CORRECTO
| nombre           | varchar(100) | YES  |     |                   |
| numero_serie_mac | varchar(100) | YES  |     |                   |
| codigo           | varchar(20)  | YES  | MUL |                   |
| modelo           | varchar(50)  | YES  |     |                   |
| marca            | varchar(50)  | YES  |     |                   |
| fecha_entrada    | date         | YES  |     | NULL              | ← CORRECTO
| estado           | varchar(20)  | YES  |     |                   |
| calidad          | varchar(20)  | YES  |     |                   |
| observaciones    | text         | YES  |     |                   |
| precio           | decimal(10,2)| YES  |     |                   |
| proveedor        | varchar(100) | YES  |     |                   |
+------------------+--------------+------+-----+-------------------+
```

---

## 🎯 RESUMEN

**Problema:** id_equipo sin PRIMARY KEY  
**Error:** #1075  
**Solución:** Script v4 que agrega PRIMARY KEY antes de AUTO_INCREMENT  
**Tiempo:** 2-3 minutos  
**Riesgo:** Bajo  

**Archivo:** `database/corregir_estructura_equipos_v4.sql`

---

**Estado:** ✅ Solución robusta creada  
**Próximo Paso:** Ejecutar en Ubuntu con `sudo mysql`
