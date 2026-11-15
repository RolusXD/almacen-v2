-- ==========================================================
-- MIGRACIÓN 003: Servidor SMTP, Auditoría y Recuperación
-- ==========================================================

-- 1. Asegurar columnas de recuperación de contraseña en usuarios y stock mínimo en accesorios
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255), ADD COLUMN IF NOT EXISTS reset_token_expires TIMESTAMP;
ALTER TABLE accesorios ADD COLUMN IF NOT EXISTS stock_minimo INTEGER DEFAULT 10;

-- 2. Tabla de configuración persistente de SMTP
CREATE TABLE IF NOT EXISTS configuracion_smtp (
  id SERIAL PRIMARY KEY,
  host VARCHAR(255) NOT NULL,
  puerto INTEGER NOT NULL DEFAULT 587,
  usuario VARCHAR(255),
  password_cifrado TEXT,
  secure BOOLEAN NOT NULL DEFAULT FALSE,
  remitente_email VARCHAR(255) NOT NULL,
  remitente_nombre VARCHAR(255) NOT NULL DEFAULT 'Almacén TOI',
  activo BOOLEAN NOT NULL DEFAULT TRUE,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 3. Índices de auditoría, Anti-IDOR y recuperación
CREATE INDEX IF NOT EXISTS idx_usuarios_reset_token ON usuarios(reset_token);
CREATE INDEX IF NOT EXISTS idx_mov_equipos_created_by ON movimientos_equipos(created_by);
CREATE INDEX IF NOT EXISTS idx_mov_accesorios_created_by ON movimientos_accesorios(created_by);
