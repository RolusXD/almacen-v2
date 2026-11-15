import { query, queryOne } from '../db';
import { ComprobanteDespacho } from '../models/types';
import { ReporteItemRow } from '../services/excel.service';

export interface FiltrosReporte {
  fechaInicio?: string;
  fechaFin?: string;
  tipoMovimiento?: string;
  tipoItem?: 'TODOS' | 'EQUIPOS' | 'ACCESORIOS';
  destino?: string;
  idUsuario?: number; // Para filtrar por operador
  buscar?: string;
}

export class ReportesRepository {
  /**
   * Obtiene la información completa de un movimiento de Equipo para emitir el Comprobante
   */
  async getComprobanteEquipo(idMovimiento: number): Promise<{ comprobante: ComprobanteDespacho; created_by: number | null } | null> {
    const res = await queryOne<any>(
      `SELECT me.id_movimiento, me.fecha_movimiento, me.tipo_movimiento, me.asunto,
              me.encargado, me.destino, me.observaciones, me.created_by,
              e.nombre as descripcion, e.codigo, e.marca, e.modelo, me.numero_serie_mac,
              u.nombre_completo as operador_nombre, u.carnet as operador_carnet
       FROM movimientos_equipos me
       LEFT JOIN equipos e ON me.numero_serie_mac = e.numero_serie_mac
       LEFT JOIN usuarios u ON me.created_by = u.id_usuario
       WHERE me.id_movimiento = $1`,
      [idMovimiento]
    );

    if (!res) return null;

    const fechaStr = res.fecha_movimiento ? new Date(res.fecha_movimiento).toISOString().split('T')[0] : '';
    const remision = `REM-EQ-${fechaStr.replace(/-/g, '')}-${String(res.id_movimiento).padStart(4, '0')}`;

    return {
      created_by: res.created_by,
      comprobante: {
        id_movimiento: res.id_movimiento,
        tipo_item: 'EQUIPO',
        numero_remision: remision,
        fecha_movimiento: fechaStr,
        tipo_movimiento: res.tipo_movimiento,
        asunto: res.asunto,
        encargado: res.encargado,
        destino: res.destino || 'Sin Destino',
        observaciones: res.observaciones,
        operador_nombre: res.operador_nombre || 'Almacén TOI',
        operador_carnet: res.operador_carnet,
        descripcion: res.descripcion || 'Equipo de Telecomunicaciones',
        codigo: res.codigo,
        marca: res.marca,
        modelo: res.modelo,
        numero_serie_mac: res.numero_serie_mac,
        cantidad: 1,
        unidad_medida: 'UNIDAD'
      }
    };
  }

  /**
   * Obtiene la información completa de un movimiento de Accesorio para emitir el Comprobante
   */
  async getComprobanteAccesorio(idMovimiento: number): Promise<{ comprobante: ComprobanteDespacho; created_by: number | null } | null> {
    const res = await queryOne<any>(
      `SELECT ma.id_movimiento, ma.fecha_movimiento, ma.tipo_movimiento, ma.asunto,
              ma.encargado, ma.destino, ma.observaciones, ma.cantidad, ma.created_by,
              a.nombre as descripcion, a.codigo, a.marca, a.modelo, a.unidad_medida,
              u.nombre_completo as operador_nombre, u.carnet as operador_carnet
       FROM movimientos_accesorios ma
       LEFT JOIN accesorios a ON ma.id_accesorio = a.id_accesorio
       LEFT JOIN usuarios u ON ma.created_by = u.id_usuario
       WHERE ma.id_movimiento = $1`,
      [idMovimiento]
    );

    if (!res) return null;

    const fechaStr = res.fecha_movimiento ? new Date(res.fecha_movimiento).toISOString().split('T')[0] : '';
    const remision = `REM-MAT-${fechaStr.replace(/-/g, '')}-${String(res.id_movimiento).padStart(4, '0')}`;

    return {
      created_by: res.created_by,
      comprobante: {
        id_movimiento: res.id_movimiento,
        tipo_item: 'ACCESORIO',
        numero_remision: remision,
        fecha_movimiento: fechaStr,
        tipo_movimiento: res.tipo_movimiento,
        asunto: res.asunto,
        encargado: res.encargado,
        destino: res.destino || 'Sin Destino',
        observaciones: res.observaciones,
        operador_nombre: res.operador_nombre || 'Almacén TOI',
        operador_carnet: res.operador_carnet,
        descripcion: res.descripcion || 'Material / Accesorio',
        codigo: res.codigo,
        marca: res.marca,
        modelo: res.modelo,
        numero_serie_mac: null,
        cantidad: res.cantidad || 1,
        unidad_medida: res.unidad_medida || 'UNIDADES'
      }
    };
  }

  /**
   * Consulta multi-criterio unificada para el Centro de Reportes
   * Si forzarUsuarioId está presente (rol usuario), inyecta estrictamente la condición en el backend (Anti-IDOR)
   */
  async getReportesUnificados(filtros: FiltrosReporte, forzarUsuarioId?: number): Promise<ReporteItemRow[]> {
    const paramsEq: any[] = [];
    const paramsAcc: any[] = [];
    let idxEq = 1;
    let idxAcc = 1;

    let whereEq = 'WHERE 1=1';
    let whereAcc = 'WHERE 1=1';

    // Restricción forzada de usuario (Anti-IDOR)
    const userIdFilter = forzarUsuarioId || filtros.idUsuario;
    if (userIdFilter) {
      whereEq += ` AND me.created_by = $${idxEq++}`;
      paramsEq.push(userIdFilter);

      whereAcc += ` AND ma.created_by = $${idxAcc++}`;
      paramsAcc.push(userIdFilter);
    }

    if (filtros.fechaInicio) {
      whereEq += ` AND me.fecha_movimiento >= $${idxEq++}`;
      paramsEq.push(filtros.fechaInicio);

      whereAcc += ` AND ma.fecha_movimiento >= $${idxAcc++}`;
      paramsAcc.push(filtros.fechaInicio);
    }

    if (filtros.fechaFin) {
      whereEq += ` AND me.fecha_movimiento <= $${idxEq++}`;
      paramsEq.push(filtros.fechaFin);

      whereAcc += ` AND ma.fecha_movimiento <= $${idxAcc++}`;
      paramsAcc.push(filtros.fechaFin);
    }

    if (filtros.tipoMovimiento && filtros.tipoMovimiento !== 'TODOS') {
      whereEq += ` AND me.tipo_movimiento = $${idxEq++}`;
      paramsEq.push(filtros.tipoMovimiento);

      whereAcc += ` AND ma.tipo_movimiento = $${idxAcc++}`;
      paramsAcc.push(filtros.tipoMovimiento);
    }

    if (filtros.destino && filtros.destino !== 'TODOS') {
      whereEq += ` AND me.destino = $${idxEq++}`;
      paramsEq.push(filtros.destino);

      whereAcc += ` AND ma.destino = $${idxAcc++}`;
      paramsAcc.push(filtros.destino);
    }

    if (filtros.buscar && filtros.buscar.trim() !== '') {
      const b = `%${filtros.buscar.trim()}%`;
      whereEq += ` AND (me.numero_serie_mac ILIKE $${idxEq} OR e.nombre ILIKE $${idxEq} OR me.encargado ILIKE $${idxEq} OR me.asunto ILIKE $${idxEq})`;
      paramsEq.push(b);
      idxEq++;

      whereAcc += ` AND (a.nombre ILIKE $${idxAcc} OR a.codigo ILIKE $${idxAcc} OR ma.encargado ILIKE $${idxAcc} OR ma.asunto ILIKE $${idxAcc})`;
      paramsAcc.push(b);
      idxAcc++;
    }

    const sqlEquipos = `
      SELECT me.fecha_movimiento, me.tipo_movimiento as tipo, e.codigo,
             COALESCE(e.nombre, 'Equipo') as descripcion, me.numero_serie_mac as serie_mac,
             1 as cantidad, 'UNIDAD' as unidad, COALESCE(me.destino, '-') as destino,
             me.encargado, me.asunto, COALESCE(u.nombre_completo, 'Almacén') as operador,
             COALESCE(me.observaciones, '') as observaciones,
             me.id_movimiento
      FROM movimientos_equipos me
      LEFT JOIN equipos e ON me.numero_serie_mac = e.numero_serie_mac
      LEFT JOIN usuarios u ON me.created_by = u.id_usuario
      ${whereEq}
    `;

    const sqlAccesorios = `
      SELECT ma.fecha_movimiento, ma.tipo_movimiento as tipo, a.codigo,
             COALESCE(a.nombre, 'Material') as descripcion, '' as serie_mac,
             ma.cantidad, COALESCE(a.unidad_medida, 'UNIDADES') as unidad, COALESCE(ma.destino, '-') as destino,
             ma.encargado, ma.asunto, COALESCE(u.nombre_completo, 'Almacén') as operador,
             COALESCE(ma.observaciones, '') as observaciones,
             ma.id_movimiento
      FROM movimientos_accesorios ma
      LEFT JOIN accesorios a ON ma.id_accesorio = a.id_accesorio
      LEFT JOIN usuarios u ON ma.created_by = u.id_usuario
      ${whereAcc}
    `;

    let finalRows: any[] = [];
    const tipoItem = filtros.tipoItem || 'TODOS';

    if (tipoItem === 'EQUIPOS') {
      finalRows = await query(sqlEquipos, paramsEq);
    } else if (tipoItem === 'ACCESORIOS') {
      finalRows = await query(sqlAccesorios, paramsAcc);
    } else {
      const [eqs, accs] = await Promise.all([
        query(sqlEquipos, paramsEq),
        query(sqlAccesorios, paramsAcc)
      ]);
      finalRows = [...eqs, ...accs];
    }

    // Ordenar cronológicamente descendente
    finalRows.sort((a, b) => {
      const dateA = new Date(a.fecha_movimiento).getTime();
      const dateB = new Date(b.fecha_movimiento).getTime();
      return dateB - dateA;
    });

    return finalRows.map(r => ({
      fecha: r.fecha_movimiento ? new Date(r.fecha_movimiento).toISOString().split('T')[0] : '-',
      tipo: r.tipo,
      codigo: r.codigo || '-',
      descripcion: r.descripcion,
      serie_mac: r.serie_mac || '-',
      cantidad: Number(r.cantidad) || 1,
      unidad: r.unidad || 'UNIDAD',
      destino: r.destino,
      encargado: r.encargado,
      asunto: r.asunto,
      operador: r.operador,
      observaciones: r.observaciones
    }));
  }
}
