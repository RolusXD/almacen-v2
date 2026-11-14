# 🔧 SOLUCIÓN ERROR #1005 - FOREIGN KEY
## Sistema de Almacén TOI

**Error:** `#1005 - Can't create table (errno: 150 "Foreign key constraint is incorrectly formed")`

---

## ❌ CAUSAS POSIBLES

Este error ocurre cuando:

1. **Valores huérfanos:** Hay códigos en `equipos.codigo` que NO existen en `tipos_equipos.codigo`
2. **Tipos de dato diferentes:** Las columnas tienen tipos diferentes
3. **Engines diferentes:** Una tabla es InnoDB y otra no
4. **Collation diferente:** Las columnas tienen collation diferente
5. **Columna no existe:** La columna referenciada no existe

---

## ✅ DIAGNÓSTICO

### Ejecutar Script de Diagnóstico:

```bash
# En Ubuntu
sudo mysql -u root almacen_toi < database/diagnostico_foreign_key.sql
```

**O manualmente:**

```sql
USE almacen_toi;

-- 1. Verificar valores huérfanos
SELECT 
    e.codigo,
    COUNT(*) as cantidad
FROM equipos e
LEFT JOIN tipos_equipos c ON e.codigo = c.codigo
WHERE c.codigo IS NULL
AND e.codigo IS NOT NULL
GROUP BY e.codigo;

-- 2. Verificar tipos de dato
SELECT 
    'equipos.codigo' as Tabla,
    DATA_TYPE as Tipo,
    CHARACTER_MAXIMUM_LENGTH as Longitud,
    COLLATION_NAME as Collation
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'almacen_toi'
AND TABLE_NAME = 'equipos'
AND COLUMN_NAME = 'codigo'
UNION ALL
SELECT 
    'tipos_equipos.codigo' as Tabla,
    DATA_TYPE as Tipo,
    CHARACTER_MAXIMUM_LENGTH as Longitud,
    COLLATION_NAME as Collation
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'almacen_toi'
AND TABLE_NAME = 'tipos_equipos'
AND COLUMN_NAME = 'codigo';

-- 3. Verificar engines
SELECT TABLE_NAME, ENGINE
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'almacen_toi'
AND TABLE_NAME IN ('equipos', 'tipos_equipos');
```

---

## 🔧 SOLUCIONES

### Solución 1: Limpiar Valores Huérfanos (Más Común)

```bash
# Ejecutar script de corrección
sudo mysql -u root almacen_toi < database/corregir_foreign_key_v2.sql
```

**O manualmente:**

```sql
USE almacen_toi;

-- Poner en NULL los códigos inválidos
UPDATE equipos e
LEFT JOIN tipos_equipos c ON e.codigo = c.codigo
SET e.codigo = NULL
WHERE c.codigo IS NULL
AND e.codigo IS NOT NULL;

-- Agregar foreign key
ALTER TABLE `equipos` 
ADD CONSTRAINT `fk_equipo_tipos` 
FOREIGN KEY (`codigo`) REFERENCES `tipos_equipos`(`codigo`) 
ON DELETE SET NULL ON UPDATE CASCADE;
```

---

### Solución 2: Agregar Tipos Faltantes

Si hay códigos válidos en `equipos` que deberían existir en `tipos_equipos`:

```sql
USE almacen_toi;

-- Insertar tipos faltantes automáticamente
INSERT INTO tipos_equipos (codigo, descripcion, categoria, activo)
SELECT DISTINCT 
    e.codigo,
    e.nombre as descripcion,
    'OTRO' as categoria,
    1 as activo
FROM equipos e
WHERE e.codigo IS NOT NULL
AND e.codigo NOT IN (SELECT codigo FROM tipos_equipos);

-- Luego agregar la FK
ALTER TABLE `equipos` 
ADD CONSTRAINT `fk_equipo_tipos` 
FOREIGN KEY (`codigo`) REFERENCES `tipos_equipos`(`codigo`) 
ON DELETE SET NULL ON UPDATE CASCADE;
```

---

### Solución 3: Corregir Tipos de Dato

Si los tipos de dato son diferentes:

```sql
USE almacen_toi;

-- Verificar tipos actuales
DESCRIBE equipos;
DESCRIBE tipos_equipos;

-- Si son diferentes, hacerlos iguales
-- Ejemplo: Si equipos.codigo es VARCHAR(50) y tipos_equipos.codigo es VARCHAR(20)

ALTER TABLE equipos 
MODIFY COLUMN codigo VARCHAR(20) DEFAULT NULL;

-- O al revés
ALTER TABLE tipos_equipos 
MODIFY COLUMN codigo VARCHAR(50) NOT NULL;

-- Luego agregar FK
ALTER TABLE `equipos` 
ADD CONSTRAINT `fk_equipo_tipos` 
FOREIGN KEY (`codigo`) REFERENCES `tipos_equipos`(`codigo`) 
ON DELETE SET NULL ON UPDATE CASCADE;
```

---

### Solución 4: Corregir Collation

Si el collation es diferente:

```sql
USE almacen_toi;

-- Verificar collation actual
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    COLLATION_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'almacen_toi'
AND TABLE_NAME IN ('equipos', 'tipos_equipos')
AND COLUMN_NAME = 'codigo';

-- Hacerlos iguales
ALTER TABLE equipos 
MODIFY COLUMN codigo VARCHAR(20) 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci 
DEFAULT NULL;

ALTER TABLE tipos_equipos 
MODIFY COLUMN codigo VARCHAR(20) 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci 
NOT NULL;

-- Luego agregar FK
ALTER TABLE `equipos` 
ADD CONSTRAINT `fk_equipo_tipos` 
FOREIGN KEY (`codigo`) REFERENCES `tipos_equipos`(`codigo`) 
ON DELETE SET NULL ON UPDATE CASCADE;
```

---

### Solución 5: Corregir Engine

Si los engines son diferentes:

```sql
USE almacen_toi;

-- Verificar engines
SELECT TABLE_NAME, ENGINE
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'almacen_toi'
AND TABLE_NAME IN ('equipos', 'tipos_equipos');

-- Si uno no es InnoDB, convertirlo
ALTER TABLE equipos ENGINE = InnoDB;
ALTER TABLE tipos_equipos ENGINE = InnoDB;

-- Luego agregar FK
ALTER TABLE `equipos` 
ADD CONSTRAINT `fk_equipo_tipos` 
FOREIGN KEY (`codigo`) REFERENCES `tipos_equipos`(`codigo`) 
ON DELETE SET NULL ON UPDATE CASCADE;
```

---

## 🚀 ORDEN DE EJECUCIÓN RECOMENDADO

### En Ubuntu:

```bash
# 1. Diagnóstico
sudo mysql -u root almacen_toi < database/diagnostico_foreign_key.sql

# 2. Corregir (usar v2)
sudo mysql -u root almacen_toi < database/corregir_foreign_key_v2.sql

# 3. Verificar
sudo mysql -u root -e "
USE almacen_toi;
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'almacen_toi'
AND TABLE_NAME = 'equipos'
AND CONSTRAINT_NAME = 'fk_equipo_tipos';
"
```

---

## ✅ CHECKLIST DE VERIFICACIÓN

Después de corregir:

- [ ] No hay valores huérfanos en `equipos.codigo`
- [ ] Tipos de dato son iguales
- [ ] Collation es igual
- [ ] Engines son InnoDB en ambas tablas
- [ ] Foreign key fue creada exitosamente
- [ ] INSERT funciona correctamente

---

## 📊 EJEMPLO DE VERIFICACIÓN

```sql
USE almacen_toi;

-- 1. Verificar FK creada
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'almacen_toi'
AND CONSTRAINT_NAME = 'fk_equipo_tipos';

-- Resultado esperado:
-- CONSTRAINT_NAME: fk_equipo_tipos
-- TABLE_NAME: equipos
-- COLUMN_NAME: codigo
-- REFERENCED_TABLE_NAME: tipos_equipos
-- REFERENCED_COLUMN_NAME: codigo

-- 2. Probar FK
-- Este INSERT debería funcionar
INSERT INTO equipos (nombre, codigo, estado)
VALUES ('Test', 'ROT2.4', 'DISPONIBLE');

-- Este INSERT debería fallar (código no existe)
INSERT INTO equipos (nombre, codigo, estado)
VALUES ('Test', 'CODIGO_INVALIDO', 'DISPONIBLE');

-- Limpiar prueba
DELETE FROM equipos WHERE nombre = 'Test';
```

---

## 🐛 ERRORES COMUNES

### Error: "#1005 - Can't create table (errno: 121)"

**Causa:** El nombre de la FK ya existe

**Solución:**
```sql
-- Usar nombre diferente
ALTER TABLE `equipos` 
ADD CONSTRAINT `fk_equipos_tipos_01` 
FOREIGN KEY (`codigo`) REFERENCES `tipos_equipos`(`codigo`);
```

### Error: "#1826 - Foreign key constraint is incorrectly formed"

**Causa:** Múltiples problemas posibles

**Solución:**
```sql
-- Verificar errores específicos
SHOW ENGINE INNODB STATUS;

-- Buscar la sección "LATEST FOREIGN KEY ERROR"
```

### Error: "#1025 - Error on rename"

**Causa:** Tablas bloqueadas

**Solución:**
```sql
-- Matar procesos bloqueados
SHOW PROCESSLIST;
KILL [process_id];

-- Reintentar
```

---

## 📞 RESUMEN RÁPIDO

**Error:** #1005 - Foreign key constraint incorrectly formed  
**Causa más común:** Valores huérfanos en `equipos.codigo`  
**Solución:** Ejecutar `corregir_foreign_key_v2.sql`  
**Tiempo:** 2-3 minutos  

**Comando rápido:**
```bash
sudo mysql -u root almacen_toi < database/corregir_foreign_key_v2.sql
```

---

**Estado:** ✅ Soluciones disponibles  
**Archivos:** 
- `diagnostico_foreign_key.sql`
- `corregir_foreign_key_v2.sql`
