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
        (SELECT COUNT(*) FROM equipos WHERE id_categoria = c.id_categoria) as total_equipos,
        (SELECT COUNT(*) FROM accesorios WHERE id_categoria = c.id_categoria) as total_accesorios
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
        (SELECT COUNT(*) FROM equipos WHERE id_marca = m.id_marca OR marca ILIKE m.nombre) as total_equipos,
        (SELECT COUNT(*) FROM accesorios WHERE id_marca = m.id_marca OR marca ILIKE m.nombre) as total_accesorios
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
      SELECT m.*, e.nombre as nombre_equipo, e.codigo as codigo_equipo, e.marca as marca_equipo, e.modelo as modelo_equipo
      FROM mantenimientos m
      LEFT JOIN equipos e ON m.numero_serie_mac = e.numero_serie_mac
      ORDER BY m.fecha_ingreso DESC, m.id_mantenimiento DESC LIMIT 200
    `);
  }

  async createMantenimiento(data: {
    numero_serie_mac: string;
    tipo_mantenimiento: 'PREVENTIVO' | 'CORRECTIVO' | 'EVOLUTIVO';
    descripcion: string;
    tecnico_responsable: string;
    costo?: number;
    fecha_ingreso: string;
    repuestos_cambiados?: string;
    observaciones?: string;
  }): Promise<Mantenimiento> {
    // Buscar id_equipo si existe
    const eq = await queryOne<{ id_equipo: number }>(
      'SELECT id_equipo FROM equipos WHERE numero_serie_mac = $1',
      [data.numero_serie_mac.trim()]
    );

    const tipoValido = ['PREVENTIVO', 'CORRECTIVO', 'EVOLUTIVO'].includes(data.tipo_mantenimiento)
      ? data.tipo_mantenimiento
      : 'CORRECTIVO';

    const res = await query<Mantenimiento>(
      `INSERT INTO mantenimientos (
        id_equipo, numero_serie_mac, tipo_mantenimiento, descripcion,
        tecnico_responsable, costo, estado, fecha_ingreso,
        repuestos_cambiados, observaciones
      ) VALUES ($1, $2, $3, $4, $5, $6, 'EN_PROCESO', $7, $8, $9)
      RETURNING *`,
      [
        eq?.id_equipo || null,
        data.numero_serie_mac.trim(),
        tipoValido,
        data.descripcion || 'Mantenimiento técnico de equipo',
        data.tecnico_responsable || 'Técnico de Laboratorio',
        data.costo || 0,
        data.fecha_ingreso || new Date().toISOString().split('T')[0],
        data.repuestos_cambiados || null,
        data.observaciones || null,
      ]
    );

    // Actualizar estado del equipo a MANTENIMIENTO
    await query("UPDATE equipos SET estado = 'MANTENIMIENTO' WHERE numero_serie_mac = $1", [
      data.numero_serie_mac.trim(),
    ]);

    return res[0];
  }

  async completeMantenimiento(
    id_mantenimiento: number,
    fecha_entrega?: string,
    observaciones?: string
  ): Promise<void> {
    const mant = await queryOne<Mantenimiento>(
      'SELECT * FROM mantenimientos WHERE id_mantenimiento = $1',
      [id_mantenimiento]
    );
    if (!mant) return;

    await query(
      `UPDATE mantenimientos 
       SET estado = 'COMPLETADO', fecha_entrega = $1, observaciones = COALESCE($2, observaciones)
       WHERE id_mantenimiento = $3`,
      [fecha_entrega || new Date().toISOString().split('T')[0], observaciones || null, id_mantenimiento]
    );

    // Restaurar estado del equipo a DISPONIBLE
    await query("UPDATE equipos SET estado = 'DISPONIBLE' WHERE numero_serie_mac = $1", [
      mant.numero_serie_mac,
    ]);
  }

  // ==========================================
  // PRESTAMOS
  // ==========================================
  async getPrestamos(): Promise<Prestamo[]> {
    return query<Prestamo>(`
      SELECT p.*, e.nombre as nombre_equipo, e.codigo as codigo_equipo, e.marca as marca_equipo, e.modelo as modelo_equipo
      FROM prestamos p
      LEFT JOIN equipos e ON p.numero_serie_mac = e.numero_serie_mac
      ORDER BY p.fecha_prestamo DESC, p.id_prestamo DESC LIMIT 200
    `);
  }

  async createPrestamo(data: {
    numero_serie_mac: string;
    nombre_solicita: string;
    cargo_solicita?: string;
    departamento?: string;
    telefono_contacto?: string;
    motivo_prestamo: string;
    fecha_prestamo: string;
    fecha_devolucion_esperada: string;
    observaciones_salida?: string;
  }): Promise<Prestamo> {
    // Buscar id_equipo si existe
    const eq = await queryOne<{ id_equipo: number }>(
      'SELECT id_equipo FROM equipos WHERE numero_serie_mac = $1',
      [data.numero_serie_mac.trim()]
    );

    const res = await query<Prestamo>(
      `INSERT INTO prestamos (
        id_equipo, numero_serie_mac, nombre_solicita, cargo_solicita,
        departamento, telefono_contacto, fecha_prestamo, fecha_devolucion_esperada,
        estado, motivo_prestamo, observaciones_salida, equipo_entregado_conforme, equipo_recibido_conforme
      ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, 'PRESTADO', $9, $10, 1, 1)
      RETURNING *`,
      [
        eq?.id_equipo || null,
        data.numero_serie_mac.trim(),
        data.nombre_solicita,
        data.cargo_solicita || null,
        data.departamento || null,
        data.telefono_contacto || null,
        data.fecha_prestamo || new Date().toISOString().split('T')[0],
        data.fecha_devolucion_esperada || new Date(Date.now() + 7 * 86400000).toISOString().split('T')[0],
        data.motivo_prestamo,
        data.observaciones_salida || null,
      ]
    );

    // Actualizar estado del equipo a OCUPADO
    await query("UPDATE equipos SET estado = 'OCUPADO' WHERE numero_serie_mac = $1", [
      data.numero_serie_mac.trim(),
    ]);

    return res[0];
  }

  async returnPrestamo(
    id_prestamo: number,
    fecha_devolucion_real?: string,
    observaciones_devolucion?: string
  ): Promise<void> {
    const prest = await queryOne<Prestamo>(
      'SELECT * FROM prestamos WHERE id_prestamo = $1',
      [id_prestamo]
    );
    if (!prest) return;

    await query(
      `UPDATE prestamos 
       SET estado = 'DEVUELTO', fecha_devolucion_real = $1, observaciones_devolucion = $2
       WHERE id_prestamo = $3`,
      [
        fecha_devolucion_real || new Date().toISOString().split('T')[0],
        observaciones_devolucion || 'Devuelto conforme a almacén',
        id_prestamo,
      ]
    );

    // Restaurar equipo a DISPONIBLE
    await query("UPDATE equipos SET estado = 'DISPONIBLE' WHERE numero_serie_mac = $1", [
      prest.numero_serie_mac,
    ]);
  }
}
