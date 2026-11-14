import { query, queryOne } from '../db';
import { TipoEquipo, Proveedor, Categoria, Marca, Mantenimiento, Prestamo } from '../models/types';

export class MaestroRepository {
  // ==========================================
  // TIPOS DE EQUIPOS
  // ==========================================
  async getTiposEquipos(): Promise<TipoEquipo[]> {
    return query<TipoEquipo>(
      'SELECT * FROM tipos_equipos WHERE activo = 1 ORDER BY categoria ASC, codigo ASC'
    );
  }

  async createTipoEquipo(data: {
    codigo: string;
    descripcion: string;
    categoria: 'ROUTER' | 'ONU' | 'SWITCH' | 'OTRO';
    prefijo?: string;
    observaciones?: string;
  }): Promise<TipoEquipo> {
    // Verificar si ya existe el código
    const exist = await queryOne('SELECT id_codigo FROM tipos_equipos WHERE codigo = $1', [data.codigo.trim()]);
    if (exist) {
      throw new Error(`El código '${data.codigo}' ya se encuentra registrado`);
    }

    const res = await query<TipoEquipo>(
      `INSERT INTO tipos_equipos (codigo, prefijo, descripcion, categoria, observaciones, activo)
       VALUES ($1, $2, $3, $4, $5, 1)
       RETURNING *`,
      [
        data.codigo.trim().toUpperCase(),
        data.prefijo || null,
        data.descripcion,
        data.categoria || 'OTRO',
        data.observaciones || null,
      ]
    );
    return res[0];
  }

  async deleteTipoEquipo(id: number): Promise<boolean> {
    const uso = await queryOne<{ count: string }>(
      'SELECT COUNT(*) as count FROM equipos WHERE id_tipo_equipo = $1',
      [id]
    );
    if (uso && parseInt(uso.count, 10) > 0) {
      return false;
    }
    await query('UPDATE tipos_equipos SET activo = 0 WHERE id_codigo = $1', [id]);
    return true;
  }

  // ==========================================
  // PROVEEDORES (CON NIT DE BOLIVIA)
  // ==========================================
  async getProveedores(): Promise<Proveedor[]> {
    return query<Proveedor>(
      'SELECT * FROM proveedores WHERE activo = 1 ORDER BY nombre_empresa ASC'
    );
  }

  async createProveedor(data: {
    nombre_empresa: string;
    contacto?: string;
    telefono?: string;
    email?: string;
    direccion?: string;
    nit?: string;
    pagina_web?: string;
  }): Promise<Proveedor> {
    const res = await query<Proveedor>(
      `INSERT INTO proveedores (nombre_empresa, contacto, telefono, email, direccion, nit, pagina_web, activo)
       VALUES ($1, $2, $3, $4, $5, $6, $7, 1)
       RETURNING *`,
      [
        data.nombre_empresa,
        data.contacto || null,
        data.telefono || null,
        data.email || null,
        data.direccion || null,
        data.nit || null,
        data.pagina_web || null,
      ]
    );
    return res[0];
  }

  async deleteProveedor(id: number): Promise<boolean> {
    await query('UPDATE proveedores SET activo = 0 WHERE id_proveedor = $1', [id]);
    return true;
  }

  // ==========================================
  // CATEGORIAS
  // ==========================================
  async getCategorias(): Promise<Categoria[]> {
    return query<Categoria>(`
      SELECT c.*,
        (SELECT COUNT(*) FROM equipos WHERE codigo IN (SELECT codigo FROM tipos_equipos WHERE categoria = c.nombre)) as total_equipos,
        (SELECT COUNT(*) FROM accesorios WHERE id_tipo_accesorio = c.id_categoria) as total_accesorios
      FROM categorias c
      WHERE c.activo = 1
      ORDER BY c.nombre ASC
    `);
  }

  async createCategoria(nombre: string, descripcion?: string): Promise<Categoria> {
    const res = await query<Categoria>(
      `INSERT INTO categorias (nombre, descripcion, activo)
       VALUES ($1, $2, 1) RETURNING *`,
      [nombre.trim(), descripcion || null]
    );
    return res[0];
  }

  async deleteCategoria(id: number): Promise<void> {
    await query('UPDATE categorias SET activo = 0 WHERE id_categoria = $1', [id]);
  }

  // ==========================================
  // MARCAS
  // ==========================================
  async getMarcas(): Promise<Marca[]> {
    return query<Marca>(`
      SELECT m.*,
        (SELECT COUNT(*) FROM equipos WHERE marca ILIKE m.nombre) as total_equipos,
        0 as total_accesorios
      FROM marcas m
      WHERE m.activo = 1
      ORDER BY m.nombre ASC
    `);
  }

  async createMarca(nombre: string, pais?: string, web?: string): Promise<Marca> {
    const res = await query<Marca>(
      `INSERT INTO marcas (nombre, pais_origen, pagina_web, activo)
       VALUES ($1, $2, $3, 1) RETURNING *`,
      [nombre.trim(), pais || null, web || null]
    );
    return res[0];
  }

  async deleteMarca(id: number): Promise<void> {
    await query('UPDATE marcas SET activo = 0 WHERE id_marca = $1', [id]);
  }

  // ==========================================
  // MANTENIMIENTOS
  // ==========================================
  async getMantenimientos(): Promise<Mantenimiento[]> {
    return query<Mantenimiento>(`
      SELECT m.*, e.nombre as nombre_equipo
      FROM mantenimientos m
      LEFT JOIN equipos e ON m.numero_serie_mac = e.numero_serie_mac
      ORDER BY m.fecha_ingreso DESC LIMIT 150
    `);
  }

  async createMantenimiento(data: {
    numero_serie_mac: string;
    tipo_falla: string;
    diagnostico?: string;
    tecnico_responsable: string;
    costo_reparacion?: number;
    fecha_ingreso: string;
  }): Promise<Mantenimiento> {
    const res = await query<Mantenimiento>(
      `INSERT INTO mantenimientos (
        numero_serie_mac, tipo_falla, diagnostico, tecnico_responsable,
        costo_reparacion, estado, fecha_ingreso
      ) VALUES ($1, $2, $3, $4, $5, 'EN_PROCESO', $6)
      RETURNING *`,
      [
        data.numero_serie_mac.trim(),
        data.tipo_falla,
        data.diagnostico || null,
        data.tecnico_responsable,
        data.costo_reparacion || 0,
        data.fecha_ingreso || new Date().toISOString().split('T')[0],
      ]
    );
    return res[0];
  }

  // ==========================================
  // PRESTAMOS
  // ==========================================
  async getPrestamos(): Promise<Prestamo[]> {
    return query<Prestamo>(`
      SELECT p.*, e.nombre as nombre_equipo
      FROM prestamos p
      LEFT JOIN equipos e ON p.numero_serie_mac = e.numero_serie_mac
      ORDER BY p.fecha_prestamo DESC LIMIT 150
    `);
  }

  async createPrestamo(data: {
    numero_serie_mac: string;
    solicitante: string;
    departamento: string;
    motivo: string;
    fecha_prestamo: string;
    fecha_devolucion_esperada?: string;
    observaciones?: string;
  }): Promise<Prestamo> {
    const res = await query<Prestamo>(
      `INSERT INTO prestamos (
        numero_serie_mac, solicitante, departamento, motivo,
        fecha_prestamo, fecha_devolucion_esperada, estado, observaciones
      ) VALUES ($1, $2, $3, $4, $5, $6, 'ACTIVO', $7)
      RETURNING *`,
      [
        data.numero_serie_mac.trim(),
        data.solicitante,
        data.departamento,
        data.motivo,
        data.fecha_prestamo,
        data.fecha_devolucion_esperada || null,
        data.observaciones || null,
      ]
    );
    return res[0];
  }
}
