import { pool } from '../db';
import { OrdenDespacho, OrdenDespachoDetalle, ItemCarritoDespacho } from '../models/types';
import { MailService } from '../services/mail.service';

export interface FiltrosDespacho {
  fechaInicio?: string;
  fechaFin?: string;
  destino?: string;
  idUsuario?: number; // Para restricción Anti-IDOR
  buscar?: string;
}

export class DespachoRepository {
  /**
   * Ejecuta la creación atómica de una Orden de Despacho Unificada (Equipos + Accesorios)
   * Cumple con ACID: o se despacha todo con éxito o no se aplica ningún cambio.
   */
  async crearDespachoTransaccional(data: {
    destino: string;
    encargado_receptor: string;
    asunto: string;
    observaciones?: string;
    created_by: number;
    items: ItemCarritoDespacho[];
  }): Promise<OrdenDespacho> {
    if (!data.items || data.items.length === 0) {
      throw new Error('La orden de despacho debe contener al menos un equipo o accesorio.');
    }

    const client = await pool.connect();
    const stockAlerts: {
      nombre: string;
      codigo?: string;
      stockActual: number;
      stockMinimo: number;
      tipo: 'EQUIPO' | 'ACCESORIO';
      destino?: string;
      encargado?: string;
    }[] = [];

    try {
      await client.query('BEGIN');

      // 1. Generar número correlativo diario único: DSP-YYYYMMDD-XXXX
      const todayStr = new Date().toISOString().split('T')[0].replace(/-/g, '');
      const countRes = await client.query<{ count: string }>(
        `SELECT COUNT(*) as count FROM ordenes_despacho WHERE DATE(fecha_despacho) = CURRENT_DATE`
      );
      const nextSeq = parseInt(countRes.rows[0].count, 10) + 1;
      const numeroOrden = `DSP-${todayStr}-${String(nextSeq).padStart(4, '0')}`;

      // 2. Insertar cabecera de orden
      const ordenRes = await client.query<OrdenDespacho>(
        `INSERT INTO ordenes_despacho (numero_orden, fecha_despacho, destino, encargado_receptor, asunto, observaciones, estado, created_by)
         VALUES ($1, CURRENT_TIMESTAMP, $2, $3, $4, $5, 'COMPLETADO', $6)
         RETURNING *`,
        [
          numeroOrden,
          data.destino.trim(),
          data.encargado_receptor.trim(),
          data.asunto.trim(),
          data.observaciones ? data.observaciones.trim() : null,
          data.created_by
        ]
      );
      const orden = ordenRes.rows[0];

      // 3. Procesar cada ítem con bloqueos de fila FOR UPDATE para prevenir condiciones de carrera
      for (const item of data.items) {
        if (item.tipo_item === 'EQUIPO') {
          const serie = item.numero_serie_mac?.trim();
          if (!serie) {
            throw new Error('Todo equipo a despachar debe tener asignado su N° de Serie o MAC.');
          }

          // Bloqueo pesimista del equipo
          const eqRes = await client.query<any>(
            `SELECT id_equipo, nombre, codigo, marca, modelo, estado
             FROM equipos
             WHERE LOWER(TRIM(numero_serie_mac)) = LOWER($1)
             FOR UPDATE`,
            [serie]
          );

          if (eqRes.rows.length === 0) {
            throw new Error(`El equipo con serie/MAC '${serie}' no existe en el catálogo.`);
          }

          const eq = eqRes.rows[0];
          if (eq.estado !== 'DISPONIBLE') {
            throw new Error(`El equipo '${eq.nombre}' (Serie: ${serie}) no está disponible para despacho (Estado: ${eq.estado}).`);
          }

          // Registrar detalle
          await client.query(
            `INSERT INTO ordenes_despacho_detalles (id_orden, tipo_item, id_item, numero_serie_mac, descripcion, codigo, marca, modelo, cantidad, unidad_medida)
             VALUES ($1, 'EQUIPO', $2, $3, $4, $5, $6, $7, 1, 'UNIDAD')`,
            [
              orden.id_orden,
              eq.id_equipo,
              serie,
              eq.nombre,
              eq.codigo,
              eq.marca,
              eq.modelo
            ]
          );

          // Registrar movimiento en histórico
          await client.query(
            `INSERT INTO movimientos_equipos (id_orden, numero_orden, id_equipo, numero_serie_mac, tipo_movimiento, destino, encargado, asunto, observaciones, created_by, fecha_movimiento)
             VALUES ($1, $2, $3, $4, 'SALIDA', $5, $6, $7, $8, $9, CURRENT_TIMESTAMP)`,
            [
              orden.id_orden,
              orden.numero_orden,
              eq.id_equipo,
              serie,
              orden.destino,
              orden.encargado_receptor,
              orden.asunto,
              orden.observaciones,
              data.created_by
            ]
          );

          // Actualizar estado del equipo a OCUPADO
          await client.query(
            `UPDATE equipos SET estado = 'OCUPADO', updated_at = CURRENT_TIMESTAMP WHERE id_equipo = $1`,
            [eq.id_equipo]
          );

        } else if (item.tipo_item === 'ACCESORIO') {
          const idAccesorio = item.id_accesorio || item.id_item;
          const cantidad = parseFloat(String(item.cantidad));

          if (!idAccesorio || isNaN(cantidad) || cantidad <= 0) {
            throw new Error(`Cantidad inválida para el accesorio '${item.descripcion}'.`);
          }

          // Bloqueo pesimista del accesorio
          const accRes = await client.query<any>(
            `SELECT id_accesorio, nombre, codigo, marca, modelo, cantidad as stock, stock_minimo, unidad_medida
             FROM accesorios
             WHERE id_accesorio = $1
             FOR UPDATE`,
            [idAccesorio]
          );

          if (accRes.rows.length === 0) {
            throw new Error(`El accesorio ID ${idAccesorio} no existe.`);
          }

          const acc = accRes.rows[0];
          const stockActual = parseFloat(acc.stock) || 0;

          if (stockActual < cantidad) {
            throw new Error(`Stock insuficiente para '${acc.nombre}'. Solicitado: ${cantidad}, Disponible: ${stockActual}.`);
          }

          const nuevoStock = stockActual - cantidad;

          // Registrar detalle
          await client.query(
            `INSERT INTO ordenes_despacho_detalles (id_orden, tipo_item, id_item, numero_serie_mac, descripcion, codigo, marca, modelo, cantidad, unidad_medida)
             VALUES ($1, 'ACCESORIO', $2, NULL, $3, $4, $5, $6, $7, $8)`,
            [
              orden.id_orden,
              acc.id_accesorio,
              acc.nombre,
              acc.codigo,
              acc.marca,
              acc.modelo,
              cantidad,
              acc.unidad_medida || 'UNIDAD'
            ]
          );

          // Registrar movimiento en histórico
          await client.query(
            `INSERT INTO movimientos_accesorios (id_orden, numero_orden, id_accesorio, tipo_movimiento, cantidad, destino, encargado, asunto, observaciones, created_by, fecha_movimiento)
             VALUES ($1, $2, $3, 'SALIDA', $4, $5, $6, $7, $8, $9, CURRENT_TIMESTAMP)`,
            [
              orden.id_orden,
              orden.numero_orden,
              acc.id_accesorio,
              cantidad,
              orden.destino,
              orden.encargado_receptor,
              orden.asunto,
              orden.observaciones,
              data.created_by
            ]
          );

          // Actualizar stock del accesorio
          await client.query(
            `UPDATE accesorios SET cantidad = $1, updated_at = CURRENT_TIMESTAMP WHERE id_accesorio = $2`,
            [nuevoStock, acc.id_accesorio]
          );

          // Evaluar umbral de alerta de stock bajo
          const stockMinimo = acc.stock_minimo !== null && acc.stock_minimo !== undefined ? acc.stock_minimo : 10;
          if (nuevoStock <= stockMinimo) {
            stockAlerts.push({
              nombre: acc.nombre,
              codigo: acc.codigo,
              stockActual: nuevoStock,
              stockMinimo,
              tipo: 'ACCESORIO',
              destino: data.destino,
              encargado: data.encargado_receptor
            });
          }
        }
      }

      await client.query('COMMIT');

      // 4. Despacho asíncrono de alertas de stock bajo tras commit exitoso
      for (const alert of stockAlerts) {
        MailService.sendStockAlert(alert).catch(err => {
          console.error('[ALERTAS] Error enviando correo de stock bajo:', err.message);
        });
      }

      return orden;
    } catch (err) {
      await client.query('ROLLBACK');
      throw err;
    } finally {
      client.release();
    }
  }

  /**
   * Consulta las órdenes de despacho aplicando filtro Anti-IDOR si forzarUsuarioId está definido
   */
  async getOrdenes(filtros: FiltrosDespacho = {}, forzarUsuarioId?: number): Promise<OrdenDespacho[]> {
    const conditions: string[] = ['1=1'];
    const values: any[] = [];
    let idx = 1;

    const userConstraint = forzarUsuarioId || filtros.idUsuario;
    if (userConstraint) {
      conditions.push(`od.created_by = $${idx++}`);
      values.push(userConstraint);
    }

    if (filtros.fechaInicio) {
      conditions.push(`od.fecha_despacho >= $${idx++}`);
      values.push(`${filtros.fechaInicio} 00:00:00`);
    }

    if (filtros.fechaFin) {
      conditions.push(`od.fecha_despacho <= $${idx++}`);
      values.push(`${filtros.fechaFin} 23:59:59`);
    }

    if (filtros.destino && filtros.destino !== 'TODOS') {
      conditions.push(`LOWER(od.destino) = LOWER($${idx++})`);
      values.push(filtros.destino);
    }

    if (filtros.buscar) {
      conditions.push(`(
        LOWER(od.numero_orden) LIKE LOWER($${idx}) OR
        LOWER(od.destino) LIKE LOWER($${idx}) OR
        LOWER(od.encargado_receptor) LIKE LOWER($${idx}) OR
        LOWER(od.asunto) LIKE LOWER($${idx})
      )`);
      values.push(`%${filtros.buscar.trim()}%`);
      idx++;
    }

    const whereClause = conditions.join(' AND ');

    const res = await pool.query<OrdenDespacho>(
      `SELECT od.id_orden, od.numero_orden, od.fecha_despacho, od.destino,
              od.encargado_receptor, od.asunto, od.observaciones, od.estado,
              od.created_by, od.created_at, od.updated_at,
              u.nombre_completo as operador_nombre,
              COUNT(odd.id_detalle) as total_items,
              COUNT(CASE WHEN odd.tipo_item = 'EQUIPO' THEN 1 END) as total_equipos,
              COUNT(CASE WHEN odd.tipo_item = 'ACCESORIO' THEN 1 END) as total_accesorios
       FROM ordenes_despacho od
       LEFT JOIN usuarios u ON od.created_by = u.id_usuario
       LEFT JOIN ordenes_despacho_detalles odd ON od.id_orden = odd.id_orden
       WHERE ${whereClause}
       GROUP BY od.id_orden, u.nombre_completo
       ORDER BY od.fecha_despacho DESC`,
      values
    );

    return res.rows;
  }

  /**
   * Obtiene la cabecera de una orden por ID
   */
  async getOrdenById(idOrden: number): Promise<OrdenDespacho | null> {
    const res = await pool.query<OrdenDespacho>(
      `SELECT od.id_orden, od.numero_orden, od.fecha_despacho, od.destino,
              od.encargado_receptor, od.asunto, od.observaciones, od.estado,
              od.created_by, od.created_at, od.updated_at,
              u.nombre_completo as operador_nombre,
              u.carnet as operador_carnet
       FROM ordenes_despacho od
       LEFT JOIN usuarios u ON od.created_by = u.id_usuario
       WHERE od.id_orden = $1`,
      [idOrden]
    );

    return res.rows[0] || null;
  }

  /**
   * Obtiene los detalles desglosados de una orden
   */
  async getDetallesByOrdenId(idOrden: number): Promise<OrdenDespachoDetalle[]> {
    const res = await pool.query<OrdenDespachoDetalle>(
      `SELECT id_detalle, id_orden, tipo_item, id_item, numero_serie_mac,
              descripcion, codigo, marca, modelo, cantidad, unidad_medida, created_at
       FROM ordenes_despacho_detalles
       WHERE id_orden = $1
       ORDER BY tipo_item ASC, id_detalle ASC`,
      [idOrden]
    );

    return res.rows;
  }

  /**
   * Anula una orden de despacho y revierte atómicamente el inventario
   */
  async anularOrdenTransaccional(idOrden: number): Promise<void> {
    const client = await pool.connect();

    try {
      await client.query('BEGIN');

      const ordenRes = await client.query<OrdenDespacho>(
        `SELECT id_orden, numero_orden, estado FROM ordenes_despacho WHERE id_orden = $1 FOR UPDATE`,
        [idOrden]
      );

      if (ordenRes.rows.length === 0) {
        throw new Error('La orden de despacho no existe.');
      }

      const orden = ordenRes.rows[0];
      if (orden.estado === 'ANULADO') {
        throw new Error('La orden ya se encuentra anulada.');
      }

      const detalles = await client.query<OrdenDespachoDetalle>(
        `SELECT id_detalle, id_orden, tipo_item, id_item, numero_serie_mac, cantidad
         FROM ordenes_despacho_detalles
         WHERE id_orden = $1`,
        [idOrden]
      );

      // Revertir cada ítem
      for (const item of detalles.rows) {
        if (item.tipo_item === 'EQUIPO' && item.numero_serie_mac) {
          await client.query(
            `UPDATE equipos SET estado = 'DISPONIBLE', updated_at = CURRENT_TIMESTAMP WHERE numero_serie_mac = $1`,
            [item.numero_serie_mac]
          );
        } else if (item.tipo_item === 'ACCESORIO' && item.id_item) {
          const cantInt = Math.round(parseFloat(String(item.cantidad))) || 1;
          await client.query(
            `UPDATE accesorios SET cantidad = cantidad + $1, updated_at = CURRENT_TIMESTAMP WHERE id_accesorio = $2`,
            [cantInt, item.id_item]
          );
        }
      }

      await client.query(
        `UPDATE ordenes_despacho SET estado = 'ANULADO', updated_at = CURRENT_TIMESTAMP WHERE id_orden = $1`,
        [idOrden]
      );

      await client.query('COMMIT');
    } catch (err) {
      await client.query('ROLLBACK');
      throw err;
    } finally {
      client.release();
    }
  }
}
