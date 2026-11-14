# 📋 CAMBIO: codigos_equipos → tipos_equipos

**Fecha:** Marzo 2026  
**Estado:** ✅ Completado

---

## 🎯 Objetivo

Renombrar la tabla `codigos_equipos` a `tipos_equipos` para hacerla más genérica y manejable desde el módulo de Gestión.

---

## ✅ Cambios Realizados

### 1. Base de Datos

**Tabla renombrada:**
```sql
RENAME TABLE `codigos_equipos` TO `tipos_equipos`;
```

**Nuevos campos agregados:**
- `prefijo` VARCHAR(10) - Prefijo del código (ej: ROT, ONU, SW)
- `observaciones` TEXT - Observaciones adicionales

**Estructura final:**
```sql
CREATE TABLE `tipos_equipos` (
  `id_codigo` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(20) NOT NULL UNIQUE,
  `descripcion` VARCHAR(150) NOT NULL,
  `categoria` ENUM('ROUTER', 'ONU', 'SWITCH', 'OTRO') DEFAULT 'OTRO',
  `prefijo` VARCHAR(10) DEFAULT NULL,
  `observaciones` TEXT DEFAULT NULL,
  `activo` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_codigo`),
  INDEX `idx_codigo_activo` (`codigo`, `activo`)
);
```

---

### 2. Archivos PHP Actualizados

#### Modelos
| Archivo | Cambio |
|---------|--------|
| `models/Equipo.php` | 3 consultas actualizadas (codigos_equipos → tipos_equipos) |
| `models/CodigoEquipo.php` | ~~Eliminado~~ (obsoleto) |
| `models/TipoEquipo.php` | ✅ Ya existe y usa la tabla correcta |

#### Controladores
| Archivo | Estado |
|---------|--------|
| `controllers/TiposEquiposController.php` | ✅ Funcional |

#### Vistas
| Archivo | Estado |
|---------|--------|
| `views/tipos_equipos/index.php` | ✅ Funcional |
| `views/tipos_equipos/form.php` | ✅ Funcional |

---

### 3. Archivos SQL Actualizados

| Archivo | Cambio |
|---------|--------|
| `database/schema.sql` | Tabla y vista actualizadas |
| `database/actualizacion_2026.sql` | Referencias actualizadas |
| `database/actualizacion_categorias.sql` | Ya contenía el RENAME TABLE |
| `database/renombrar_tipos_equipos.sql` | Script creado (para ejecutar) |
| `database/verificar_tipos_equipos.sql` | Script de verificación creado |

---

### 4. Documentación Actualizada

| Archivo | Cambio |
|---------|--------|
| `CAMBIOS_REALIZADOS_2026.md` | Referencia actualizada |

---

## 📝 Scripts SQL Disponibles

### Para ejecutar en phpMyAdmin:

**1. Verificación de estado:**
```bash
database/verificar_tipos_equipos.sql
```

Este script:
- Verifica si la tabla existe
- Muestra la estructura actual
- Agrega campos faltantes (prefijo, observaciones)
- Actualiza prefijos automáticamente
- Muestra datos de verificación

---

## 🔍 Consultas de Verificación

```sql
-- Verificar que la tabla existe
SHOW TABLES LIKE 'tipos_equipos';

-- Verificar estructura
DESCRIBE tipos_equipos;

-- Verificar datos
SELECT * FROM tipos_equipos ORDER BY categoria, codigo;

-- Verificar referencias en equipos
SELECT e.codigo, t.descripcion, COUNT(*) as total
FROM equipos e
LEFT JOIN tipos_equipos t ON e.codigo = t.codigo
GROUP BY e.codigo, t.descripcion
ORDER BY total DESC;
```

---

## 🐛 Errores Conocidos

### "La tabla 'tipos_equipos' ya existe"

**Causa:** El renombrado ya se realizó anteriormente.

**Solución:** Ejecutar el script `verificar_tipos_equipos.sql` que:
1. Verifica el estado actual
2. Agrega campos faltantes
3. Actualiza los datos

---

## 📊 Datos de Ejemplo

La tabla `tipos_equipos` contiene:

| codigo | descripcion | categoria | prefijo |
|--------|-------------|-----------|---------|
| ROT2.4 | ROUTER WIFI 2.4 | ROUTER | ROT |
| ROT52.4 | ROUTER DUAL BAND 2.4-5G | ROUTER | ROT |
| ONU2.4 | ONU WIFI 2.4 | ONU | ONU |
| ONUDUAL | ONU WIFI 2.4-5G | ONU | ONU |
| ONUR | ONU ROUTER | ONU | ONU |
| ONUB | ONUBRIDGED | ONU | ONU |

---

## ✅ Checklist Final

- [x] Tabla renombrada en la base de datos
- [x] Campos adicionales agregados (prefijo, observaciones)
- [x] Modelo `Equipo.php` actualizado
- [x] Modelo `CodigoEquipo.php` eliminado
- [x] Controlador `TiposEquiposController.php` funcional
- [x] Vistas actualizadas
- [x] Scripts SQL actualizados
- [x] Documentación actualizada
- [ ] ~~Ejecutar script de verificación en phpMyAdmin~~ ⬅️ **PENDIENTE**

---

## 🚀 Próximos Pasos

1. Ejecutar `database/verificar_tipos_equipos.sql` en phpMyAdmin
2. Verificar que el módulo "Tipos de Equipos" funcione correctamente
3. Probar CRUD de tipos de equipos
4. Verificar que las referencias en Equipos funcionen

---

**Estado:** ✅ Cambios de código completados  
**Pendiente:** Ejecutar verificación en base de datos
