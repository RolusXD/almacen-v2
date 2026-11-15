import { Request } from 'express';
import { pool } from '../db';
import { AuditoriaLog } from '../models/types';

export interface FiltrosAuditoria {
  fechaInicio?: string;
  fechaFin?: string;
  idUsuario?: number;
  modulo?: string;
  accion?: string;
  page?: number;
  limit?: number;
}

export class AuditService {
  /**
   * Registra un evento en la bitácora de auditoría inmutable
   */
  static async log(
    req: Request | null,
    event: {
      modulo: string;
      accion: string;
      detalle?: string;
      id_usuario?: number;
      nombre_usuario?: string;
      rol_usuario?: string;
      ip?: string;
    }
  ): Promise<void> {
    try {
      let ip = event.ip || '127.0.0.1';
      let idUsuario = event.id_usuario || null;
      let nombreUsuario = event.nombre_usuario || 'SISTEMA';
      let rolUsuario = event.rol_usuario || 'SISTEMA';

      if (req) {
        const forwarded = req.headers['x-forwarded-for'];
        if (typeof forwarded === 'string') {
          ip = forwarded.split(',')[0].trim();
        } else if (req.socket?.remoteAddress) {
          ip = req.socket.remoteAddress;
        }

        if (req.session) {
          if (!idUsuario && req.session.userId) idUsuario = req.session.userId;
          if (nombreUsuario === 'SISTEMA' && req.session.userName) nombreUsuario = req.session.userName;
          if (rolUsuario === 'SISTEMA' && req.session.userRole) rolUsuario = req.session.userRole;
        }
      }

      await pool.query(
        `INSERT INTO auditoria_logs (fecha_hora, id_usuario, nombre_usuario, rol_usuario, ip, modulo, accion, detalle)
         VALUES (CURRENT_TIMESTAMP, $1, $2, $3, $4, $5, $6, $7)`,
        [
          idUsuario,
          nombreUsuario,
          rolUsuario,
          ip,
          event.modulo,
          event.accion,
          event.detalle || null
        ]
      );
    } catch (err: any) {
      console.error('[AUDITORIA] Error registrando log en base de datos:', err.message);
      // No bloqueamos la ejecución principal por fallo en logging
    }
  }

  /**
   * Consulta registros paginados y filtrados de la bitácora
   */
  static async getLogs(filtros: FiltrosAuditoria = {}): Promise<{
    logs: AuditoriaLog[];
    total: number;
    page: number;
    limit: number;
    totalPages: number;
  }> {
    const page = Math.max(1, filtros.page || 1);
    const limit = Math.min(100, Math.max(10, filtros.limit || 25));
    const offset = (page - 1) * limit;

    const conditions: string[] = ['1=1'];
    const values: any[] = [];
    let idx = 1;

    if (filtros.fechaInicio) {
      conditions.push(`fecha_hora >= $${idx++}`);
      values.push(`${filtros.fechaInicio} 00:00:00`);
    }

    if (filtros.fechaFin) {
      conditions.push(`fecha_hora <= $${idx++}`);
      values.push(`${filtros.fechaFin} 23:59:59`);
    }

    if (filtros.idUsuario) {
      conditions.push(`id_usuario = $${idx++}`);
      values.push(filtros.idUsuario);
    }

    if (filtros.modulo && filtros.modulo !== 'TODOS') {
      conditions.push(`modulo = $${idx++}`);
      values.push(filtros.modulo);
    }

    if (filtros.accion && filtros.accion !== 'TODOS') {
      conditions.push(`accion = $${idx++}`);
      values.push(filtros.accion);
    }

    const whereClause = conditions.join(' AND ');

    // Conteo total
    const countRes = await pool.query<{ count: string }>(
      `SELECT COUNT(*) as count FROM auditoria_logs WHERE ${whereClause}`,
      values
    );
    const total = parseInt(countRes.rows[0].count, 10);
    const totalPages = Math.ceil(total / limit) || 1;

    // Consulta de registros
    const logsRes = await pool.query<AuditoriaLog>(
      `SELECT id_log, fecha_hora, id_usuario, nombre_usuario, rol_usuario, ip, modulo, accion, detalle
       FROM auditoria_logs
       WHERE ${whereClause}
       ORDER BY fecha_hora DESC
       LIMIT $${idx++} OFFSET $${idx++}`,
      [...values, limit, offset]
    );

    return {
      logs: logsRes.rows,
      total,
      page,
      limit,
      totalPages
    };
  }

  /**
   * Obtiene todos los logs filtrados sin paginación para exportación a Excel
   */
  static async getAllFilteredLogs(filtros: FiltrosAuditoria = {}): Promise<AuditoriaLog[]> {
    const conditions: string[] = ['1=1'];
    const values: any[] = [];
    let idx = 1;

    if (filtros.fechaInicio) {
      conditions.push(`fecha_hora >= $${idx++}`);
      values.push(`${filtros.fechaInicio} 00:00:00`);
    }

    if (filtros.fechaFin) {
      conditions.push(`fecha_hora <= $${idx++}`);
      values.push(`${filtros.fechaFin} 23:59:59`);
    }

    if (filtros.idUsuario) {
      conditions.push(`id_usuario = $${idx++}`);
      values.push(filtros.idUsuario);
    }

    if (filtros.modulo && filtros.modulo !== 'TODOS') {
      conditions.push(`modulo = $${idx++}`);
      values.push(filtros.modulo);
    }

    if (filtros.accion && filtros.accion !== 'TODOS') {
      conditions.push(`accion = $${idx++}`);
      values.push(filtros.accion);
    }

    const whereClause = conditions.join(' AND ');

    const res = await pool.query<AuditoriaLog>(
      `SELECT id_log, fecha_hora, id_usuario, nombre_usuario, rol_usuario, ip, modulo, accion, detalle
       FROM auditoria_logs
       WHERE ${whereClause}
       ORDER BY fecha_hora DESC
       LIMIT 1000`,
      values
    );

    return res.rows;
  }
}
