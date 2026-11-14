# 🔧 SOLUCIÓN DE ERRORES - IMPORTACIÓN DE BASE DE DATOS
## Ubuntu 22.04 / MariaDB

**Problema:** Error #1064 al importar backup con VISTAS

---

## ❌ ERROR

```sql
-- Estructura Stand-in para la vista `vista_resumen_equipos_por_codigo`
CREATE TABLE `vista_resumen_equipos_por_codigo` ( );
```

**Mensaje de Error:**
```
#1064 - Existe un error en su sintaxis SQL
A symbol name was expected! (near ")" at position 227)
```

**Causa:** phpMyAdmin exporta las VISTAS como `CREATE TABLE ()` vacío, lo cual es SQL inválido.

---

## ✅ SOLUCIÓN 1: EDITAR ARCHIVO SQL (Rápido)

### Paso 1: Abrir archivo SQL en Ubuntu

```bash
cd /home/tu_usuario/
nano backup_almacen_toi.sql
# O usar editor gráfico:
gedit backup_almacen_toi.sql
```

### Paso 2: Buscar y Eliminar

**Buscar estas líneas:**
```sql
-- --------------------------------------------------------
-- -- Estructura Stand-in para la vista `vista_resumen_equipos_por_codigo`
-- -- (Véase abajo para la vista actual)
-- CREATE TABLE `vista_resumen_equipos_por_codigo` ( );
```

**Eliminar TODAS las líneas que digan:**
- `CREATE TABLE ...vista... ( );`
- `Estructura Stand-in para la vista`

### Paso 3: Guardar y Re-importar

```bash
# En MySQL/MariaDB:
mysql -u root -p almacen_toi < backup_almacen_toi_editado.sql
```

---

## ✅ SOLUCIÓN 2: SCRIPT DE LIMPIEZA (Automático)

### Crear script de limpieza

```bash
cd /home/tu_usuario/
nano limpiar_backup.sh
```

**Contenido del script:**
```bash
#!/bin/bash

# Script para limpiar CREATE TABLE inválidos de vistas

INPUT=$1
OUTPUT=$2

if [ -z "$INPUT" ] || [ -z "$OUTPUT" ]; then
    echo "Uso: $0 archivo_entrada.sql archivo_salida.sql"
    exit 1
fi

# Eliminar CREATE TABLE de vistas (líneas inválidas)
grep -v "Estructura Stand-in para la vista" "$INPUT" | \
grep -v "CREATE TABLE.*vista.*();" | \
grep -v "CREATE TABLE.*vista.*( );" | \
grep -v "CREATE TABLE \`vista" > "$OUTPUT"

echo "Limpieza completada!"
echo "Archivo limpio: $OUTPUT"
```

**Ejecutar:**
```bash
chmod +x limpiar_backup.sh
./limpiar_backup.sh backup_almacen_toi.sql backup_limpio.sql
```

---

## ✅ SOLUCIÓN 3: EXPORTAR CORRECTAMENTE (Preventivo)

### En phpMyAdmin (Windows/XAMPP):

1. **Seleccionar base de datos** `almacen_toi`
2. **Click en "Exportar"**
3. **Método de exportación:** Personalizado
4. **Marcar opciones:**
   - ✅ Agregar DROP TABLE / VIEW / PROCEDURE
   - ✅ Agregar CREATE TABLE / VIEW / PROCEDURE
   - ❌ NO marcar "Crear vista como tabla"
   - ✅ Incluir datos
5. **Formato:** SQL
6. **Click en "Continuar"**

### Opción Recomendada: Usar mysqldump

**En Windows (XAMPP):**
```bash
cd C:\xampp\mysql\bin

# Exportar con mysqldump (mejor que phpMyAdmin)
mysqldump -u root --no-tablespaces --skip-triggers ^
  --routines --events ^
  almacen_toi > C:\Users\raul1\Documents\almacen_toi_backup.sql
```

**O con mysqldump completo:**
```bash
mysqldump -u root --no-tablespaces ^
  --single-transaction ^
  --routines ^
  --triggers ^
  --events ^
  --hex-blob ^
  almacen_toi > C:\Users\raul1\Documents\almacen_toi_completo.sql
```

---

## ✅ SOLUCIÓN 4: IMPORTAR SIN VISTAS

### Si las vistas no son críticas:

```bash
# 1. Crear base de datos
mysql -u root -p -e "CREATE DATABASE almacen_toi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Importar sin vistas
mysql -u root -p almacen_toi < backup_sin_vistas.sql

# 3. Crear vistas manualmente después
mysql -u root -p almacen_toi < crear_vistas.sql
```

---

## 📋 CREAR VISTAS MANUALMENTE

**Archivo: `crear_vistas.sql`**

```sql
USE almacen_toi;

-- Vista: Resumen de equipos por código
CREATE OR REPLACE VIEW `vista_resumen_equipos_por_codigo` AS
SELECT
    e.codigo,
    c.descripcion,
    COUNT(*) as total,
    SUM(CASE WHEN e.estado = 'DISPONIBLE' THEN 1 ELSE 0 END) as disponibles,
    SUM(CASE WHEN e.estado = 'OCUPADO' THEN 1 ELSE 0 END) as ocupados,
    SUM(CASE WHEN e.estado = 'MANTENIMIENTO' THEN 1 ELSE 0 END) as mantenimiento,
    SUM(CASE WHEN e.calidad = 'NUEVO' THEN 1 ELSE 0 END) as nuevos,
    AVG(e.precio) as precio_promedio
FROM equipos e
LEFT JOIN tipos_equipos c ON e.codigo = c.codigo
GROUP BY e.codigo, c.descripcion
ORDER BY total DESC;

-- Vista: Equipos disponibles
CREATE OR REPLACE VIEW `vista_equipos_disponibles` AS
SELECT
    e.id_equipo,
    e.nombre,
    e.codigo,
    e.numero_serie_mac,
    e.modelo,
    e.marca,
    e.calidad,
    e.precio,
    e.fecha_entrada,
    DATEDIFF(CURDATE(), e.fecha_entrada) as dias_almacenado
FROM equipos e
WHERE e.estado = 'DISPONIBLE'
ORDER BY e.nombre, e.fecha_entrada;

-- Vista: Accesorios con stock bajo
CREATE OR REPLACE VIEW `vista_accesorios_stock_bajo` AS
SELECT
    id_accesorio,
    nombre,
    cantidad,
    unidad_medida,
    estado
FROM accesorios
WHERE cantidad < 10
ORDER BY cantidad ASC;
```

**Ejecutar:**
```bash
mysql -u root -p almacen_toi < crear_vistas.sql
```

---

## 🚀 PROCESO COMPLETO RECOMENDADO

### En Windows (antes de migrar):

```bash
cd C:\xampp\mysql\bin

# Exportar con mysqldump (MEJOR OPCIÓN)
mysqldump -u root --no-tablespaces ^
  --single-transaction ^
  --routines ^
  --triggers ^
  --hex-blob ^
  --add-drop-table ^
  almacen_toi > C:\Users\raul1\Documents\almacen_toi_produccion.sql
```

### En Ubuntu (después de subir):

```bash
# 1. Crear base de datos
mysql -u root -p -e "CREATE DATABASE almacen_toi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Importar
mysql -u root -p almacen_toi < /home/tu_usuario/almacen_toi_produccion.sql

# 3. Verificar tablas
mysql -u root -p -e "USE almacen_toi; SHOW TABLES;"

# 4. Verificar vistas
mysql -u root -p -e "USE almacen_toi; SHOW FULL TABLES WHERE TABLE_TYPE = 'VIEW';"
```

---

## 🐛 VERIFICAR ERRORES DESPUÉS DE IMPORTAR

```bash
# Verificar si hay errores
mysql -u root -p -e "
USE almacen_toi;
SELECT TABLE_NAME, TABLE_TYPE 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'almacen_toi'
ORDER BY TABLE_TYPE, TABLE_NAME;
"

# Verificar conteo de registros
mysql -u root -p -e "
USE almacen_toi;
SELECT 
    'usuarios' as tabla, COUNT(*) as registros FROM usuarios UNION ALL
SELECT 'equipos', COUNT(*) FROM equipos UNION ALL
SELECT 'accesorios', COUNT(*) FROM accesorios UNION ALL
SELECT 'proveedores', COUNT(*) FROM proveedores;
"
```

---

## ✅ CHECKLIST DE MIGRACIÓN

### Windows (Exportar)
- [ ] Usar mysqldump en lugar de phpMyAdmin
- [ ] Incluir --single-transaction
- [ ] Incluir --routines --triggers
- [ ] Incluir --add-drop-table
- [ ] Verificar archivo SQL generado
- [ ] Subir archivo a Ubuntu

### Ubuntu (Importar)
- [ ] Crear base de datos `almacen_toi`
- [ ] Importar archivo SQL
- [ ] Verificar sin errores
- [ ] Verificar tablas creadas
- [ ] Verificar vistas creadas
- [ ] Verificar registros

### Post-Importación
- [ ] Ejecutar `nit_cambio.sql` (si no está incluido)
- [ ] Ejecutar `migracion_equipos.sql` (si es necesario)
- [ ] Cambiar contraseña de admin
- [ ] Crear usuario de BD específico
- [ ] Probar login en aplicación

---

## 📊 COMPARATIVA DE MÉTODOS

| Método | phpMyAdmin | mysqldump |
|--------|------------|-----------|
| **Vistas** | ❌ Problemas | ✅ Correcto |
| **Triggers** | ⚠️ A veces | ✅ Siempre |
| **Routines** | ⚠️ A veces | ✅ Siempre |
| **Velocidad** | 🐢 Lento | ⚡ Rápido |
| **Archivo** | 📄 Grande | 📄 Optimizado |
| **Recomendado** | ❌ No | ✅ Sí |

---

## 🎯 RECOMENDACIÓN FINAL

**NO usar exportación de phpMyAdmin para migrar a producción.**

**USAR mysqldump:**
```bash
# Windows (XAMPP)
C:\xampp\mysql\bin\mysqldump -u root --no-tablespaces ^
  --single-transaction --routines --triggers ^
  --add-drop-table almacen_toi ^
  > C:\Users\raul1\Documents\almacen_toi_produccion.sql

# Subir archivo a Ubuntu
scp C:\Users\raul1\Documents\almacen_toi_produccion.sql user@servidor:/home/user/

# Ubuntu - Importar
mysql -u root -p -e "CREATE DATABASE almacen_toi CHARACTER SET utf8mb4;"
mysql -u root -p almacen_toi < /home/user/almacen_toi_produccion.sql
```

---

**Estado:** ✅ Soluciones disponibles  
**Tiempo estimado:** 10-15 minutos  
**Dificultad:** Baja
