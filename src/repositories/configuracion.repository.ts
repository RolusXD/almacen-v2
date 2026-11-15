import { pool } from '../db';
import { ConfiguracionGeneral } from '../models/types';

export class ConfiguracionRepository {
  /**
   * Obtiene la configuración general del sistema (registro único id=1)
   */
  async getConfiguracion(): Promise<ConfiguracionGeneral> {
    const res = await pool.query<ConfiguracionGeneral>(
      `SELECT id, zona_horaria, empresa_nombre, empresa_nit, empresa_direccion,
              empresa_telefono, empresa_email, almacen_pie_firma, stock_minimo_defecto,
              alertas_emails, updated_at
       FROM configuracion_general
       WHERE id = 1`
    );

    if (res.rows.length === 0) {
      // Si no existe, insertar fila por defecto y retornar
      const insertRes = await pool.query<ConfiguracionGeneral>(
        `INSERT INTO configuracion_general (id, zona_horaria, empresa_nombre, stock_minimo_defecto)
         VALUES (1, 'America/La_Paz', 'Telecomunicaciones Oportunas Inteligentes (TOI)', 10)
         RETURNING *`
      );
      return insertRes.rows[0];
    }

    return res.rows[0];
  }

  /**
   * Actualiza los parámetros de configuración institucional
   */
  async updateConfiguracion(data: {
    zona_horaria: string;
    empresa_nombre: string;
    empresa_nit?: string;
    empresa_direccion?: string;
    empresa_telefono?: string;
    empresa_email?: string;
    almacen_pie_firma?: string;
    stock_minimo_defecto: number;
    alertas_emails?: string;
  }): Promise<ConfiguracionGeneral> {
    const res = await pool.query<ConfiguracionGeneral>(
      `UPDATE configuracion_general
       SET zona_horaria = $1,
           empresa_nombre = $2,
           empresa_nit = $3,
           empresa_direccion = $4,
           empresa_telefono = $5,
           empresa_email = $6,
           almacen_pie_firma = $7,
           stock_minimo_defecto = $8,
           alertas_emails = $9,
           updated_at = CURRENT_TIMESTAMP
       WHERE id = 1
       RETURNING *`,
      [
        data.zona_horaria || 'America/La_Paz',
        data.empresa_nombre || 'Telecomunicaciones Oportunas Inteligentes (TOI)',
        data.empresa_nit || null,
        data.empresa_direccion || null,
        data.empresa_telefono || null,
        data.empresa_email || null,
        data.almacen_pie_firma || null,
        data.stock_minimo_defecto || 10,
        data.alertas_emails || null
      ]
    );

    return res.rows[0];
  }
}
