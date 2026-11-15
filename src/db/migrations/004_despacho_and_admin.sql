-- ==========================================================
-- MIGRACIÓN 004: Despacho General Unificado, Transaccionalidad,
-- Configuración General y Auditoría de Seguridad
-- ==========================================================

-- 1. Tabla de Órdenes de Despacho General Unificadas
CREATE TABLE IF NOT EXISTS ordenes_despacho (
    id_orden SERIAL PRIMARY KEY,
    numero_orden VARCHAR(50) NOT NULL UNIQUE,
    fecha_despacho TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    destino VARCHAR(150) NOT NULL,
    encargado_receptor VARCHAR(150) NOT NULL,
    asunto VARCHAR(200) NOT NULL,
    observaciones TEXT,
    estado VARCHAR(30) NOT NULL DEFAULT 'COMPLETADO', -- 'COMPLETADO', 'ANULADO'
    created_by INTEGER REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_ordenes_despacho_fecha ON ordenes_despacho(fecha_despacho DESC);
CREATE INDEX IF NOT EXISTS idx_ordenes_despacho_created_by ON ordenes_despacho(created_by);
CREATE INDEX IF NOT EXISTS idx_ordenes_despacho_numero ON ordenes_despacho(numero_orden);

-- 2. Tabla de Detalles de Orden de Despacho (Equipos + Accesorios)
CREATE TABLE IF NOT EXISTS ordenes_despacho_detalles (
    id_detalle SERIAL PRIMARY KEY,
    id_orden INTEGER NOT NULL REFERENCES ordenes_despacho(id_orden) ON DELETE CASCADE,
    tipo_item VARCHAR(20) NOT NULL CHECK (tipo_item IN ('EQUIPO', 'ACCESORIO')),
    id_item INTEGER,
    numero_serie_mac VARCHAR(100),
    descripcion VARCHAR(255) NOT NULL,
    codigo VARCHAR(100),
    marca VARCHAR(100),
    modelo VARCHAR(100),
    cantidad NUMERIC(10, 2) NOT NULL DEFAULT 1,
    unidad_medida VARCHAR(50) DEFAULT 'UNIDAD',
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_ordenes_detalles_orden ON ordenes_despacho_detalles(id_orden);
CREATE INDEX IF NOT EXISTS idx_ordenes_detalles_serie ON ordenes_despacho_detalles(numero_serie_mac);

-- 3. Vinculación con movimientos existentes para compatibilidad total
ALTER TABLE movimientos_equipos 
    ADD COLUMN IF NOT EXISTS id_orden INTEGER REFERENCES ordenes_despacho(id_orden) ON DELETE SET NULL,
    ADD COLUMN IF NOT EXISTS numero_orden VARCHAR(50);

ALTER TABLE movimientos_accesorios 
    ADD COLUMN IF NOT EXISTS id_orden INTEGER REFERENCES ordenes_despacho(id_orden) ON DELETE SET NULL,
    ADD COLUMN IF NOT EXISTS numero_orden VARCHAR(50);

CREATE INDEX IF NOT EXISTS idx_mov_eq_id_orden ON movimientos_equipos(id_orden);
CREATE INDEX IF NOT EXISTS idx_mov_acc_id_orden ON movimientos_accesorios(id_orden);

-- 4. Tabla de Configuración General del Sistema
CREATE TABLE IF NOT EXISTS configuracion_general (
    id SERIAL PRIMARY KEY,
    zona_horaria VARCHAR(50) NOT NULL DEFAULT 'America/La_Paz',
    empresa_nombre VARCHAR(150) NOT NULL DEFAULT 'Telecomunicaciones Oportunas Inteligentes (TOI)',
    empresa_nit VARCHAR(50) DEFAULT '1029384021',
    empresa_direccion TEXT DEFAULT 'Av. Principal #123, Zona Sur, La Paz - Bolivia',
    empresa_telefono VARCHAR(50) DEFAULT '+591 2 2456789',
    empresa_email VARCHAR(100) DEFAULT 'contacto@toi.bo',
    almacen_pie_firma TEXT DEFAULT 'Documento oficial de entrega y custodia de materiales de red y telecomunicaciones.',
    stock_minimo_defecto INTEGER NOT NULL DEFAULT 10,
    alertas_emails TEXT DEFAULT 'almacen@toi.bo, supervisores@toi.bo',
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Insertar configuración inicial por defecto si está vacía
INSERT INTO configuracion_general (id, zona_horaria, empresa_nombre, stock_minimo_defecto)
VALUES (1, 'America/La_Paz', 'Telecomunicaciones Oportunas Inteligentes (TOI)', 10)
ON CONFLICT (id) DO NOTHING;

-- 5. Tabla de Auditoría y Bitácora de Seguridad (Audit Logs)
CREATE TABLE IF NOT EXISTS auditoria_logs (
    id_log BIGSERIAL PRIMARY KEY,
    fecha_hora TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_usuario INTEGER REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    nombre_usuario VARCHAR(100) NOT NULL DEFAULT 'SISTEMA',
    rol_usuario VARCHAR(30) NOT NULL DEFAULT 'SISTEMA',
    ip VARCHAR(50) NOT NULL DEFAULT '127.0.0.1',
    modulo VARCHAR(50) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    detalle TEXT,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_auditoria_fecha ON auditoria_logs(fecha_hora DESC);
CREATE INDEX IF NOT EXISTS idx_auditoria_usuario ON auditoria_logs(id_usuario);
CREATE INDEX IF NOT EXISTS idx_auditoria_modulo ON auditoria_logs(modulo);
CREATE INDEX IF NOT EXISTS idx_auditoria_accion ON auditoria_logs(accion);
