import { query, queryOne } from '../db';
import { Equipo } from '../models/types';

export class EquipoRepository {
  async getAll(filtro?: string, estado?: string, codigo?: string): Promise<Equipo[]> {
    let sql = `
      SELECT e.*, t.descripcion as tipo_descripcion, t.categoria as tipo_categoria
      FROM equipos e
      LEFT JOIN tipos_equipos t ON e.codigo = t.codigo
      WHERE 1=1
    `;
    const params: any[] = [];
    let idx = 1;

    if (estado && estado !== 'Todos') {
      sql += ` AND e.estado = $${idx++}`;
      params.push(estado);
    }

    if (codigo && codigo !== 'Todos') {
      sql += ` AND e.codigo = $${idx++}`;
      params.push(codigo);
    }

    if (filtro && filtro.trim() !== '') {
      sql += ` AND (e.nombre ILIKE $${idx} OR e.numero_serie_mac ILIKE $${idx} OR e.modelo ILIKE $${idx} OR e.marca ILIKE $${idx})`;
      params.push(`%${filtro.trim()}%`);
      idx++;
    }

    sql += ' ORDER BY e.id_equipo DESC';
    return query<Equipo>(sql, params);
  }

  async findById(id: number): Promise<Equipo | null> {
    return queryOne<Equipo>(
      `SELECT e.*, t.descripcion as tipo_descripcion
       FROM equipos e
       LEFT JOIN tipos_equipos t ON e.codigo = t.codigo
       WHERE e.id_equipo = $1`,
      [id]
    );
  }

  async findBySerieMac(serieMac: string): Promise<Equipo | null> {
    return queryOne<Equipo>(
      `SELECT e.*, t.descripcion as tipo_descripcion
       FROM equipos e
       LEFT JOIN tipos_equipos t ON e.codigo = t.codigo
       WHERE TRIM(LOWER(e.numero_serie_mac)) = TRIM(LOWER($1))`,
      [serieMac.trim()]
    );
  }

  async searchDisponiblesParaMovimiento(term?: string): Promise<Equipo[]> {
    let sql = `
      SELECT e.id_equipo, e.numero_serie_mac, e.codigo, e.nombre, e.modelo, e.marca, e.estado
      FROM equipos e
      WHERE 1=1
    `;
    const params: any[] = [];
    if (term && term.trim() !== '') {
      sql += ` AND (e.numero_serie_mac ILIKE $1 OR e.nombre ILIKE $1 OR e.modelo ILIKE $1 OR e.codigo ILIKE $1)`;
      params.push(`%${term.trim()}%`);
    }
    sql += ' ORDER BY e.estado = \'DISPONIBLE\' DESC, e.numero_serie_mac ASC LIMIT 50';
    return query<Equipo>(sql, params);
  }

  async create(data: {
    nombre: string;
    numero_serie_mac: string;
    codigo?: string;
    marca?: string;
    modelo?: string;
    precio?: number;
    proveedor?: string;
    fecha_entrada?: string;
    observaciones?: string;
  }): Promise<Equipo> {
    const res = await query<Equipo>(
      `INSERT INTO equipos (
        nombre, numero_serie_mac, codigo, marca, modelo, precio,
        proveedor, fecha_entrada, estado, calidad, observaciones
      ) VALUES (
        $1, $2, $3, $4, $5, $6, $7, $8, 'DISPONIBLE', 'NUEVO', $9
      ) RETURNING *`,
      [
        data.nombre,
        data.numero_serie_mac.trim(),
        data.codigo || null,
        data.marca || null,
        data.modelo || null,
        data.precio || 0,
        data.proveedor || null,
        data.fecha_entrada || new Date().toISOString().split('T')[0],
        data.observaciones || null,
      ]
    );
    return res[0];
  }

  async update(id: number, data: Partial<Equipo>): Promise<void> {
    const fields: string[] = [];
    const values: any[] = [];
    let idx = 1;

    for (const [key, val] of Object.entries(data)) {
      if (['nombre', 'codigo', 'marca', 'modelo', 'precio', 'proveedor', 'fecha_entrada', 'observaciones', 'estado'].includes(key)) {
        fields.push(`${key} = $${idx++}`);
        values.push(val);
      }
    }

    if (fields.length === 0) return;
    values.push(id);
    await query(`UPDATE equipos SET ${fields.join(', ')} WHERE id_equipo = $${idx}`, values);
  }

  async delete(id: number): Promise<boolean> {
    // Validar si tiene movimientos asociados
    const movs = await queryOne<{ count: string }>(
      `SELECT COUNT(*) as count FROM movimientos_equipos me
       JOIN equipos e ON me.numero_serie_mac = e.numero_serie_mac
       WHERE e.id_equipo = $1`,
      [id]
    );
    if (movs && parseInt(movs.count, 10) > 0) {
      return false; // No se puede borrar si tiene historial
    }
    await query('DELETE FROM equipos WHERE id_equipo = $1', [id]);
    return true;
  }

  async getModelosReferencia(): Promise<Array<{ modelo: string; marca: string; codigo: string; nombre: string; proveedor: string; precio: number }>> {
    return query(`
      SELECT DISTINCT modelo, marca, codigo, nombre, proveedor, precio
      FROM equipos
      WHERE modelo IS NOT NULL AND TRIM(modelo) <> ''
      ORDER BY modelo ASC
    `);
  }

  async getEstadisticas() {
    const total = await queryOne<{ count: string }>('SELECT COUNT(*) as count FROM equipos');
    const disponibles = await queryOne<{ count: string }>('SELECT COUNT(*) as count FROM equipos WHERE estado = \'DISPONIBLE\'');
    const asignados = await queryOne<{ count: string }>('SELECT COUNT(*) as count FROM equipos WHERE estado IN (\'ASIGNADO\', \'NO DISPONIBLE\', \'SALIDA\')');
    const mantenimiento = await queryOne<{ count: string }>('SELECT COUNT(*) as count FROM equipos WHERE estado IN (\'MANTENIMIENTO\', \'REVISION\')');

    return {
      total: parseInt(total?.count || '0', 10),
      disponibles: parseInt(disponibles?.count || '0', 10),
      asignados: parseInt(asignados?.count || '0', 10),
      mantenimiento: parseInt(mantenimiento?.count || '0', 10),
    };
  }
}
