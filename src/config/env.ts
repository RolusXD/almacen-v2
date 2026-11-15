import dotenv from 'dotenv';
dotenv.config();

export const ENV = {
  NODE_ENV: process.env.NODE_ENV || 'development',
  PORT: parseInt(process.env.PORT || '3000', 10),
  APP_NAME: process.env.APP_NAME || 'Sistema de Almacén TOI',
  APP_URL: process.env.APP_URL || 'http://localhost:3000',
  SESSION_SECRET: process.env.SESSION_SECRET || 'toi_super_secret_session_key_2026_xyz',
  ENCRYPTION_KEY: process.env.ENCRYPTION_KEY || '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef', // 32 bytes (256-bit hex)
  
  // Base de datos (acepta DATABASE_URL completa o variables individuales)
  DATABASE_URL: process.env.DATABASE_URL || '',
  DB_HOST: process.env.DB_HOST || 'localhost',
  DB_PORT: parseInt(process.env.DB_PORT || '5432', 10),
  DB_NAME: process.env.DB_NAME || 'almacen_toi',
  DB_USER: process.env.DB_USER || 'postgres',
  DB_PASS: process.env.DB_PASS || '',
  DB_SSL: process.env.DB_SSL === 'true' || process.env.DB_SSLMODE === 'require',
};
