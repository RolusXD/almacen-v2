# 🔧 SOLUCIÓN ERROR #1452 - FOREIGN KEY
## Sistema de Almacén TOI

**Error:** `#1452 - Cannot add or update a child row: a foreign key constraint fails`

---

## ❌ PROBLEMA

### Causa del Error:

La tabla `equipos` tiene valores en la columna `codigo` que **NO existen** en la tabla `tipos_equipos`.

**Ejemplo:**
```
equipos.codigo          tipos_equipos.codigo
----------------        --------------------
ROT2.4                  ROT2.4
ONU2.4                  ONU2.4
SWITCH                  ❌ NO EXISTE  ← ESTE ES EL PROBLEMA
ROT52.4                 ROT52.4
```

Cuando intentas agregar la foreign key:
```sql
ALTER TABLE equipos 
ADD CONSTRAINT fk_equipo_tipos 
FOREIGN KEY (codigo) REFERENCES tipos_equipos(codigo);
```

MySQL verifica que TODOS los valores en `equipos.codigo` existan en `tipos_equipos.codigo`. Si hay algún valor que no existe (como "SWITCH"), falla con el error #1452.

---

## ✅ SOLUCIÓN

### Script Creado: `database/corregir_foreign_key.sql`

**Qué hace:**
1. ✅ Detecta valores huérfanos en `equipos.codigo`
2. ✅ Los pone en `NULL` (ya que la FK permite NULL)
3. ✅ Agrega la foreign key correctamente

---

## 🚀 CÓMO USAR

### Opción 1: Ejecutar Script de Corrección

```bash
# En phpMyAdmin o MySQL
mysql -u root -p almacen_toi < database/corregir_foreign_key.sql
```

### Opción 2: Manual Paso a Paso

#### Paso 1: Ver Valores Huérfanos
```sql
USE almacen_toi;

-- Ver qué códigos en equipos NO existen en tipos_equipos
SELECT 
    e.codigo,
    COUNT(*) as cantidad
FROM equipos e
LEFT JOIN tipos_equipos c ON e.codigo = c.codigo
WHERE c.codigo IS NULL
AND e.codigo IS NOT NULL
GROUP BY e.codigo;
```

#### Paso 2: Corregir Valores Huérfanos
```sql
-- Poner a NULL los códigos que no existen
UPDATE equipos e
LEFT JOIN tipos_equipos c ON e.codigo = c.codigo
SET e.codigo = NULL
WHERE c.codigo IS NULL
AND e.codigo IS NOT NULL;
```

#### Paso 3: Verificar Corrección
```sql
-- Debería mostrar 0 resultados
SELECT 
    e.codigo,
    COUNT(*) as cantidad
FROM equipos e
LEFT JOIN tipos_equipos c ON e.codigo = c.codigo
WHERE c.codigo IS NULL
AND e.codigo IS NOT NULL
GROUP BY e.codigo;
```

#### Paso 4: Agregar Foreign Key
```sql
-- Ahora debería funcionar
ALTER TABLE `equipos` 
ADD CONSTRAINT `fk_equipo_tipos` 
FOREIGN KEY (`codigo`) REFERENCES `tipos_equipos`(`codigo`) 
ON DELETE SET NULL ON UPDATE CASCADE;
```

---

## 📊 EJEMPLO PRÁCTICO

### Antes de Corregir:

```sql
-- Tabla equipos
id_equipo | codigo  | nombre
----------|---------|--------
1         | ROT2.4  | Router
2         | ONU2.4  | ONT
3         | SWITCH  | Switch  ← ESTE NO EXISTE EN tipos_equipos
```

```sql
-- Tabla tipos_equipos
id_codigo | codigo
----------|--------
1         | ROT2.4
2         | ONU2.4
```

### Después de Corregir:

```sql
-- Tabla equipos (actualizada)
id_equipo | codigo  | nombre
----------|---------|--------
1         | ROT2.4  | Router
2         | ONU2.4  | ONT
3         | NULL    | Switch  ← AHORA ES NULL
```

```sql
-- Foreign Key agregada correctamente ✅
ALTER TABLE equipos ADD CONSTRAINT fk_equipo_tipos ...
```

---

## 🎯 DOS OPCIONES DE SOLUCIÓN

### Opción A: Poner en NULL (Recomendado)

**Ventajas:**
- ✅ No se pierden datos
- ✅ Se puede actualizar después
- ✅ Rápido

**Comando:**
```sql
UPDATE equipos e
LEFT JOIN tipos_equipos c ON e.codigo = c.codigo
SET e.codigo = NULL
WHERE c.codigo IS NULL;
```

### Opción B: Eliminar Equipos Huérfanos

**Solo si son datos de prueba o inválidos:**

```sql
-- Eliminar equipos con códigos inválidos
DELETE e
FROM equipos e
LEFT JOIN tipos_equipos c ON e.codigo = c.codigo
WHERE c.codigo IS NULL
AND e.codigo IS NOT NULL;
```

---

## 🔍 PREVENIR EN EL FUTURO

### 1. Agregar Tipos Válidos Antes

```sql
-- Si vas a usar "SWITCH", agrégalo primero
INSERT INTO tipos_equipos (codigo, descripcion, categoria)
VALUES ('SWITCH', 'Switch de Red', 'SWITCH');
```

### 2. Validar en el Código PHP

```php
// Antes de guardar equipo, verificar que el código existe
$tipoModel = new TipoEquipo();
$tipo = $tipoModel->findByCodigo($data['codigo']);

if (!$tipo) {
    return ['success' => false, 'message' => 'Código de equipo no válido'];
}
```

### 3. Usar Select en Lugar de Input

```html
<!-- En forms de equipos -->
<select name="codigo" required>
    <option value="">Seleccionar tipo</option>
    <?php foreach ($tipos as $tipo): ?>
    <option value="<?= $tipo['codigo'] ?>"><?= $tipo['descripcion'] ?></option>
    <?php endforeach; ?>
</select>
```

---

## ✅ CHECKLIST DE CORRECCIÓN

- [ ] Script `corregir_foreign_key.sql` ejecutado
- [ ] Valores huérfanos identificados
- [ ] Valores corregidos (NULL o eliminados)
- [ ] Foreign Key agregada correctamente
- [ ] Verificación sin errores
- [ ] Equipos con código válido: X
- [ ] Equipos sin código (NULL): X

---

## 📊 VERIFICACIÓN FINAL

```sql
USE almacen_toi;

-- 1. Verificar foreign keys
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'almacen_toi'
AND CONSTRAINT_NAME = 'fk_equipo_tipos';

-- 2. Verificar equipos con código
SELECT 
    'Con código' as Estado,
    COUNT(*) as Cantidad
FROM equipos
WHERE codigo IS NOT NULL
UNION ALL
SELECT 
    'Sin código (NULL)' as Estado,
    COUNT(*) as Cantidad
FROM equipos
WHERE codigo IS NULL;

-- 3. Probar que la FK funciona
-- Este INSERT debería fallar (código no existe)
INSERT INTO equipos (nombre, codigo) 
VALUES ('Test', 'CODIGO_INVALIDO');

-- Este INSERT debería funcionar (código existe)
INSERT INTO equipos (nombre, codigo) 
VALUES ('Test Router', 'ROT2.4');
```

---

## 🐛 POSIBLES PROBLEMAS ADICIONALES

### Problema 1: "Cannot DROP INDEX"

**Si intentas eliminar la FK y da error:**

```sql
-- Verificar nombre exacto de la FK
SELECT CONSTRAINT_NAME 
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'almacen_toi'
AND TABLE_NAME = 'equipos';

-- Eliminar con nombre correcto
ALTER TABLE equipos DROP FOREIGN KEY fk_equipo_tipos;
```

### Problema 2: "Table doesn't exist"

**Si `tipos_equipos` no existe:**

```sql
-- Verificar si existe
SHOW TABLES LIKE 'tipos_equipos';

-- Si no existe pero existe codigos_equipos
RENAME TABLE `codigos_equipos` TO `tipos_equipos`;
```

### Problema 3: Muchos valores huérfanos

**Si hay muchos equipos con códigos inválidos:**

```sql
-- Crear tipos_equipos faltantes automáticamente
INSERT INTO tipos_equipos (codigo, descripcion, categoria, activo)
SELECT DISTINCT 
    e.codigo,
    e.nombre as descripcion,
    'OTRO' as categoria,
    1 as activo
FROM equipos e
LEFT JOIN tipos_equipos c ON e.codigo = c.codigo
WHERE c.codigo IS NULL
AND e.codigo IS NOT NULL;
```

---

## 📞 RESUMEN RÁPIDO

**Problema:** Códigos en `equipos` que no existen en `tipos_equipos`  
**Solución:** Ejecutar `database/corregir_foreign_key.sql`  
**Tiempo:** 1-2 minutos  
**Riesgo:** Bajo (solo pone en NULL valores inválidos)

---

**Estado:** ✅ Solución lista  
**Archivo:** `database/corregir_foreign_key.sql`
