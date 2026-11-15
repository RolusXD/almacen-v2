import { query, queryOne, pool } from '../db';
import { MovimientoEquipo, MovimientoAccesorio } from '../models/types';

export class MovimientoRepository {
  // ==========================================
  // MOVIMIENTOS DE EQUIPOS
  // ==========================================
  async getMovimientosEquipos(filtro?: string, destino?: string, codigo?: string, limit: number = 200): Promise<MovimientoEquipo[]> {
    let sql = `
      SELECT me.*, e.nombre as nombre_equipo, e.codigo as codigo_equipo,
             e.marca as marca_equipo, e.modelo as modelo_equipo, u.nombre_completo as usuario_registro
      FROM movimientos_equipos me
      LEFT JOIN equipos e ON me.numero_serie_mac = e.numero_serie_mac
      LEFT JOIN usuarios u ON me.created_by = u.id_usuario
      WHERE 1=1
    `;
    const params: any[] = [];
    let idx = 1;

    if (destino && destino.trim() !== '') {
      sql += ` AND me.destino = $${idx++}`;
      params.push(destino);
    }

    if (codigo && codigo.trim() !== '') {
      sql += ` AND e.codigo = $${idx++}`;
      params.push(codigo);
    }

    if (filtro && filtro.trim() !== '') {
      sql += ` AND (me.numero_serie_mac ILIKE $${idx} OR me.asunto ILIKE $${idx} OR me.encargado ILIKE $${idx} OR me.destino ILIKE $${idx} OR e.nombre ILIKE $${idx} OR e.modelo ILIKE $${idx})`;
      params.push(`%${filtro.trim()}%`);
      idx++;
    }

    sql += ` ORDER BY me.fecha_movimiento DESC, me.id_movimiento DESC LIMIT $${idx}`;
    params.push(limit);

    return query<MovimientoEquipo>(sql, params);
  }

  async registrarMovimientoEquipo(data: {
    numero_serie_mac: string;
    tipo_movimiento: 'SALIDA' | 'ENTRADA' | 'MANTENIMIENTO' | 'REVISION';
    asunto: string;
    encargado: string;
    destino?: string;
    observaciones?: string;
    observaciones_detalladas?: string;
    fecha_movimiento: string;
    created_by?: number;
  }): Promise<MovimientoEquipo> {
    const client = await pool.connect();
    try {
      await client.query('BEGIN');

      // 1. Insertar movimiento
      const insertRes = await client.query<MovimientoEquipo>(
        `INSERT INTO movimientos_equipos (
          numero_serie_mac, tipo_movimiento, asunto, encargado, destino,
          observaciones, observaciones_detalladas, fecha_movimiento, created_by
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
        RETURNING *`,
        [
          data.numero_serie_mac.trim(),
          data.tipo_movimiento,
          data.asunto,
          data.encargado,
          data.destino || null,
          data.observaciones || null,
          data.observaciones_detalladas || null,
          data.fecha_movimiento,
          data.created_by || null,
        ]
      );

      // 2. Actualizar estado del equipo
      let nuevoEstado = 'DISPONIBLE';
      if (data.tipo_movimiento === 'SALIDA') nuevoEstado = 'OCUPADO';
      else if (data.tipo_movimiento === 'MANTENIMIENTO') nuevoEstado = 'MANTENIMIENTO';
      else if (data.tipo_movimiento === 'REVISION') nuevoEstado = 'MANTENIMIENTO';

      await client.query(
        'UPDATE equipos SET estado = $1, updated_at = CURRENT_TIMESTAMP WHERE TRIM(LOWER(numero_serie_mac)) = TRIM(LOWER($2))',
        [nuevoEstado, data.numero_serie_mac.trim()]
      );

      await client.query('COMMIT');
      return insertRes.rows[0];
    } catch (err) {
      await client.query('ROLLBACK');
      throw err;
    } finally {
      client.release();
    }
  }

  // ==========================================
  // MOVIMIENTOS DE ACCESORIOS
  // ==========================================
  async getMovimientosAccesorios(filtro?: string, destino?: string, limit: number = 200): Promise<MovimientoAccesorio[]> {
    let sql = `
      SELECT ma.*, a.nombre as nombre_accesorio, a.codigo as codigo_accesorio,
             a.unidad_medida, u.nombre_completo as usuario_registro
      FROM movimientos_accesorios ma
      LEFT JOIN accesorios a ON ma.id_accesorio = a.id_accesorio
      LEFT JOIN usuarios u ON ma.created_by = u.id_usuario
      WHERE 1=1
    `;
    const params: any[] = [];
    let idx = 1;

    if (destino && destino.trim() !== '') {
      sql += ` AND ma.destino = $${idx++}`;
      params.push(destino);
    }

    if (filtro && filtro.trim() !== '') {
      sql += ` AND (a.nombre ILIKE $${idx} OR a.codigo ILIKE $${idx} OR ma.asunto ILIKE $${idx} OR ma.encargado ILIKE $${idx} OR ma.destino ILIKE $${idx})`;
      params.push(`%${filtro.trim()}%`);
      idx++;
    }

    sql += ` ORDER BY ma.fecha_movimiento DESC, ma.id_movimiento DESC LIMIT $${idx}`;
    params.push(limit);

    return query<MovimientoAccesorio>(sql, params);
  }

  async registrarMovimientoAccesorio(data: {
    id_accesorio: number;
    tipo_movimiento: 'SALIDA' | 'ENTRADA' | 'MANTENIMIENTO' | 'REVISION';
    cantidad: number;
    asunto: string;
    encargado: string;
    destino?: string;
    observaciones?: string;
    observaciones_detalladas?: string;
    fecha_movimiento: string;
    created_by?: number;
  }): Promise<MovimientoAccesorio> {
    const client = await pool.connect();
    try {
      await client.query('BEGIN');

      // 1. Verificar stock actual
      const accRes = await client.query<{ cantidad: number }>(
        'SELECT cantidad FROM accesorios WHERE id_accesorio = $1 FOR UPDATE',
        [data.id_accesorio]
      );
      if (accRes.rows.length === 0) {
        throw new Error('El accesorio seleccionado no existe');
      }

      const stockActual = accRes.rows[0].cantidad;
      if (data.tipo_movimiento === 'SALIDA' && data.cantidad > stockActual) {
        throw new Error(`Stock insuficiente. Disponible: ${stockActual}, solicitado: ${data.cantidad}`);
      }

      // 2. Insertar movimiento con DESTINO garantizado
      const insertRes = await client.query<MovimientoAccesorio>(
        `INSERT INTO movimientos_accesorios (
          id_accesorio, tipo_movimiento, cantidad, asunto, encargado, destino,
          observaciones, observaciones_detalladas, fecha_movimiento, created_by
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)
        RETURNING *`,
        [
          data.id_accesorio,
          data.tipo_movimiento,
          data.cantidad,
          data.asunto,
          data.encargado,
          data.destino || null,
          data.observaciones || null,
          data.observaciones_detalladas || null,
          data.fecha_movimiento,
          data.created_by || null,
        ]
      );

      // 3. Ajustar existencias (si la BD no tiene trigger activo o para asegurar consistencia)
      let nuevaCantidad = stockActual;
      if (data.tipo_movimiento === 'SALIDA') {
        nuevaCantidad = Math.max(0, stockActual - data.cantidad);
      } else if (data.tipo_movimiento === 'ENTRADA') {
        nuevaCantidad = stockActual + data.cantidad;
      }

      await client.query(
        'UPDATE accesorios SET cantidad = $1, updated_at = CURRENT_TIMESTAMP WHERE id_accesorio = $2',
        [nuevaCantidad, data.id_accesorio]
      );

      await client.query('COMMIT');
      return insertRes.rows[0];
    } catch (err) {
      await client.query('ROLLBACK');
      throw err;
    } finally {
      client.release();
    }
  }

  async getDestinosUnicos(): Promise<string[]> {
    const res = await query<{ destino: string }>(`
      SELECT DISTINCT destino FROM (
        SELECT nombre as destino FROM destinos WHERE activo = 1
        UNION
        SELECT destino FROM movimientos_equipos WHERE destino IS NOT NULL AND TRIM(destino) <> ''
        UNION
        SELECT destino FROM movimientos_accesorios WHERE destino IS NOT NULL AND TRIM(destino) <> ''
        UNION
        SELECT destino FROM ordenes_despacho WHERE destino IS NOT NULL AND TRIM(destino) <> ''
      ) as d WHERE destino IS NOT NULL AND TRIM(destino) <> '' ORDER BY destino ASC
    `);
    return res.map(r => r.destino);
  }
}
