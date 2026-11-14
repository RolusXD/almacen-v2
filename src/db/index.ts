import { Pool, PoolConfig } from 'pg';
import { ENV } from '../config/env';

let poolConfig: PoolConfig;

if (ENV.DATABASE_URL) {
  poolConfig = {
    connectionString: ENV.DATABASE_URL,
    ssl: ENV.DB_SSL ? { rejectUnauthorized: false } : false,
  };
} else {
  poolConfig = {
    host: ENV.DB_HOST,
    port: ENV.DB_PORT,
    database: ENV.DB_NAME,
    user: ENV.DB_USER,
    password: ENV.DB_PASS,
    ssl: ENV.DB_SSL ? { rejectUnauthorized: false } : false,
    max: 20,
    idleTimeoutMillis: 30000,
    connectionTimeoutMillis: 5000,
  };
}

export const pool = new Pool(poolConfig);

pool.on('error', (err) => {
  console.error('Error imprevisto en el pool de PostgreSQL:', err);
});

export async function query<T = any>(text: string, params?: any[]): Promise<T[]> {
  const start = Date.now();
  const res = await pool.query(text, params);
  const duration = Date.now() - start;
  if (ENV.NODE_ENV === 'development') {
    console.log(`[SQL] ${text.replace(/\s+/g, ' ').substring(0, 80)} (${duration}ms - ${res.rowCount} rows)`);
  }
  return res.rows as T[];
}

export async function queryOne<T = any>(text: string, params?: any[]): Promise<T | null> {
  const rows = await query<T>(text, params);
  return rows.length > 0 ? rows[0] : null;
}
