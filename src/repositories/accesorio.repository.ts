import { query, queryOne } from '../db';
import { Accesorio } from '../models/types';

export class AccesorioRepository {
  async getAll(filtro?: string, unidadMedida?: string, stockBajo?: boolean): Promise<Accesorio[]> {
    let sql = `
      SELECT a.*, p.nombre_empresa as nombre_proveedor
      FROM accesorios a
      LEFT JOIN proveedores p ON a.id_proveedor = p.id_proveedor
      WHERE 1=1
    `;
    const params: any[] = [];
    let idx = 1;

    if (unidadMedida && unidadMedida !== 'Todas') {
      sql += ` AND a.unidad_medida = $${idx++}`;
      params.push(unidadMedida);
    }

    if (stockBajo) {
      sql += ` AND a.cantidad < 10`;
    }

    if (filtro && filtro.trim() !== '') {
      sql += ` AND (a.nombre ILIKE $${idx} OR a.codigo ILIKE $${idx} OR a.observaciones ILIKE $${idx})`;
      params.push(`%${filtro.trim()}%`);
      idx++;
    }

    sql += ' ORDER BY a.id_accesorio DESC';
    return query<Accesorio>(sql, params);
  }

  async findById(id: number): Promise<Accesorio | null> {
    return queryOne<Accesorio>(
      `SELECT a.*, p.nombre_empresa as nombre_proveedor
       FROM accesorios a
       LEFT JOIN proveedores p ON a.id_proveedor = p.id_proveedor
       WHERE a.id_accesorio = $1`,
      [id]
    );
  }

  async getConStock(): Promise<Accesorio[]> {
    return query<Accesorio>(
      `SELECT id_accesorio, codigo, nombre, cantidad, unidad_medida, costo
       FROM accesorios
       WHERE cantidad > 0
       ORDER BY nombre ASC`
    );
  }

  async create(data: {
    nombre: string;
    codigo?: string;
    cantidad: number;
    unidad_medida: string;
    costo?: number;
    id_proveedor?: number;
    id_categoria?: number;
    fecha_entrada?: string;
    observaciones?: string;
  }): Promise<Accesorio> {
    const fecha = data.fecha_entrada && data.fecha_entrada.trim() !== ''
      ? data.fecha_entrada.trim()
      : new Date().toISOString().split('T')[0];

    const res = await query<Accesorio>(
      `INSERT INTO accesorios (
        nombre, codigo, cantidad, unidad_medida, costo,
        id_proveedor, id_categoria, fecha_entrada, estado, calidad, observaciones
      ) VALUES (
        $1, $2, $3, $4, $5, $6, $7, $8, 'DISPONIBLE', 'NUEVO', $9
      ) RETURNING *`,
      [
        data.nombre.trim(),
        data.codigo ? data.codigo.trim() : null,
        data.cantidad || 0,
        data.unidad_medida || 'UNIDADES',
        data.costo || 0,
        data.id_proveedor || null,
        data.id_categoria || null,
        fecha,
        data.observaciones || null,
      ]
    );
    return res[0];
  }

  async update(id: number, data: Partial<Accesorio>): Promise<void> {
    const fields: string[] = [];
    const values: any[] = [];
    let idx = 1;

    for (const [key, val] of Object.entries(data)) {
      if (['nombre', 'codigo', 'cantidad', 'unidad_medida', 'costo', 'id_proveedor', 'observaciones', 'estado'].includes(key)) {
        fields.push(`${key} = $${idx++}`);
        values.push(val);
      }
    }

    if (fields.length === 0) return;
    values.push(id);
    await query(`UPDATE accesorios SET ${fields.join(', ')} WHERE id_accesorio = $${idx}`, values);
  }

  async delete(id: number): Promise<boolean> {
    const movs = await queryOne<{ count: string }>(
      'SELECT COUNT(*) as count FROM movimientos_accesorios WHERE id_accesorio = $1',
      [id]
    );
    if (movs && parseInt(movs.count, 10) > 0) {
      return false;
    }
    await query('DELETE FROM accesorios WHERE id_accesorio = $1', [id]);
    return true;
  }

  async getEstadisticas() {
    const totalItems = await queryOne<{ count: string }>('SELECT COUNT(*) as count FROM accesorios');
    const totalUnidades = await queryOne<{ sum: string }>('SELECT SUM(COALESCE(cantidad, 0)) as sum FROM accesorios');
    const stockBajo = await queryOne<{ count: string }>('SELECT COUNT(*) as count FROM accesorios WHERE COALESCE(cantidad, 0) <= COALESCE(stock_minimo, 10)');
    const valorTotal = await queryOne<{ sum: string }>('SELECT SUM(COALESCE(cantidad, 0) * COALESCE(costo, 0)) as sum FROM accesorios');

    return {
      totalItems: parseInt(totalItems?.count || '0', 10),
      totalUnidades: parseInt(totalUnidades?.sum || '0', 10),
      stockBajo: parseInt(stockBajo?.count || '0', 10),
      valorTotal: parseFloat(valorTotal?.sum || '0'),
    };
  }
}
