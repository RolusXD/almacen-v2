import { pool } from '../db';
import { ConfiguracionSmtp } from '../models/types';
import { CryptoService } from '../services/crypto.service';

export class SmtpRepository {
  async getConfig(): Promise<ConfiguracionSmtp | null> {
    const res = await pool.query(`
      SELECT id, host, puerto, usuario, password_cifrado, secure, remitente_email, remitente_nombre, activo, updated_at
      FROM configuracion_smtp
      ORDER BY id ASC
      LIMIT 1;
    `);

    if (res.rows.length === 0) {
      return null;
    }

    return res.rows[0];
  }

  async saveConfig(config: {
    host: string;
    puerto: number;
    usuario?: string;
    password?: string;
    secure: boolean;
    remitente_email: string;
    remitente_nombre: string;
    activo: boolean;
  }): Promise<ConfiguracionSmtp> {
    const existing = await this.getConfig();

    let passwordCifrado = existing ? existing.password_cifrado : null;
    if (config.password && config.password.trim().length > 0) {
      passwordCifrado = CryptoService.encrypt(config.password.trim());
    }

    if (existing && existing.id) {
      const res = await pool.query(
        `UPDATE configuracion_smtp
         SET host = $1, puerto = $2, usuario = $3, password_cifrado = $4, secure = $5,
             remitente_email = $6, remitente_nombre = $7, activo = $8, updated_at = CURRENT_TIMESTAMP
         WHERE id = $9
         RETURNING *;`,
        [
          config.host,
          config.puerto,
          config.usuario || null,
          passwordCifrado,
          config.secure,
          config.remitente_email,
          config.remitente_nombre,
          config.activo,
          existing.id,
        ]
      );
      return res.rows[0];
    } else {
      const res = await pool.query(
        `INSERT INTO configuracion_smtp
         (host, puerto, usuario, password_cifrado, secure, remitente_email, remitente_nombre, activo)
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8)
         RETURNING *;`,
        [
          config.host,
          config.puerto,
          config.usuario || null,
          passwordCifrado,
          config.secure,
          config.remitente_email,
          config.remitente_nombre,
          config.activo,
        ]
      );
      return res.rows[0];
    }
  }
}
