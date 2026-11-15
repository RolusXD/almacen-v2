import { Request, Response } from 'express';
import ExcelJS from 'exceljs';
import { SmtpRepository } from '../repositories/smtp.repository';
import { ConfiguracionRepository } from '../repositories/configuracion.repository';
import { UsuarioRepository } from '../repositories/usuario.repository';
import { MailService } from '../services/mail.service';
import { AuditService, FiltrosAuditoria } from '../services/audit.service';
import { setSystemTimeZone, formatLocalDateTime } from '../utils/date.helper';

const smtpRepo = new SmtpRepository();
const configRepo = new ConfiguracionRepository();
const userRepo = new UsuarioRepository();

export class ConfigController {
  // ==========================================
  // CONFIGURACIÓN GENERAL DEL SISTEMA
  // ==========================================
  static async generalIndex(req: Request, res: Response) {
    try {
      const config = await configRepo.getConfiguracion();
      res.render('administracion/configuracion', {
        config,
        activeNav: 'admin-config',
        title: 'Configuración General del Sistema'
      });
    } catch (err: any) {
      console.error('Error cargando configuración general:', err);
      req.session.flash = { type: 'error', message: 'Error cargando la configuración general.' };
      res.redirect('/dashboard');
    }
  }

  static async generalSubmit(req: Request, res: Response) {
    try {
      const {
        zona_horaria,
        empresa_nombre,
        empresa_nit,
        empresa_direccion,
        empresa_telefono,
        empresa_email,
        almacen_pie_firma,
        stock_minimo_defecto,
        alertas_emails
      } = req.body;

      if (!empresa_nombre || empresa_nombre.trim() === '') {
        req.session.flash = { type: 'error', message: 'El nombre de la empresa es obligatorio.' };
        return res.redirect('/administracion/configuracion');
      }

      const updated = await configRepo.updateConfiguracion({
        zona_horaria: zona_horaria || 'America/La_Paz',
        empresa_nombre: empresa_nombre.trim(),
        empresa_nit: empresa_nit ? empresa_nit.trim() : '',
        empresa_direccion: empresa_direccion ? empresa_direccion.trim() : '',
        empresa_telefono: empresa_telefono ? empresa_telefono.trim() : '',
        empresa_email: empresa_email ? empresa_email.trim() : '',
        almacen_pie_firma: almacen_pie_firma ? almacen_pie_firma.trim() : '',
        stock_minimo_defecto: parseInt(stock_minimo_defecto, 10) || 10,
        alertas_emails: alertas_emails ? alertas_emails.trim() : ''
      });

      // Aplicar zona horaria inmediatamente
      setSystemTimeZone(updated.zona_horaria);

      await AuditService.log(req, {
        modulo: 'CONFIG_GENERAL',
        accion: 'CONFIG_UPDATE',
        detalle: `Actualización de configuración general (Zona horaria: ${updated.zona_horaria}, Empresa: ${updated.empresa_nombre})`
      });

      req.session.flash = { type: 'success', message: 'Configuración general actualizada exitosamente.' };
      res.redirect('/administracion/configuracion');
    } catch (err: any) {
      console.error('Error guardando configuración general:', err);
      req.session.flash = { type: 'error', message: err.message || 'Error al guardar la configuración.' };
      res.redirect('/administracion/configuracion');
    }
  }

  // ==========================================
  // CONFIGURACIÓN SERVIDOR SMTP
  // ==========================================
  static async smtpIndex(req: Request, res: Response) {
    try {
      const config = await smtpRepo.getConfig();
      res.render('configuracion/smtp', { config, activeNav: 'config-smtp' });
    } catch (err: any) {
      console.error('Error cargando configuración SMTP:', err);
      req.session.flash = { type: 'error', message: 'Error cargando la configuración SMTP.' };
      res.redirect('/dashboard');
    }
  }

  static async smtpSubmit(req: Request, res: Response) {
    try {
      const { host, puerto, usuario, password, secure, remitente_email, remitente_nombre, activo } = req.body;

      if (!host || !puerto || !remitente_email) {
        req.session.flash = { type: 'error', message: 'Host, Puerto y Correo Remitente son obligatorios.' };
        return res.redirect('/configuracion/smtp');
      }

      await smtpRepo.saveConfig({
        host: host.trim(),
        puerto: parseInt(puerto, 10) || 587,
        usuario: usuario ? usuario.trim() : '',
        password: password ? password : '',
        secure: secure === 'true' || secure === '1' || secure === 'on',
        remitente_email: remitente_email.trim(),
        remitente_nombre: remitente_nombre ? remitente_nombre.trim() : 'Almacén TOI',
        activo: activo === 'true' || activo === '1' || activo === 'on'
      });

      await AuditService.log(req, {
        modulo: 'CONFIG_SMTP',
        accion: 'CONFIG_UPDATE',
        detalle: `Actualización de parámetros SMTP (Host: ${host}, Puerto: ${puerto}, Usuario: ${usuario || 'N/A'})`
      });

      req.session.flash = { type: 'success', message: 'Configuración SMTP guardada exitosamente.' };
      res.redirect('/configuracion/smtp');
    } catch (err: any) {
      console.error('Error guardando configuración SMTP:', err);
      req.session.flash = { type: 'error', message: err.message || 'Error al guardar la configuración SMTP.' };
      res.redirect('/configuracion/smtp');
    }
  }

  static async smtpTest(req: Request, res: Response) {
    try {
      const { email_destino } = req.body;
      if (!email_destino || email_destino.trim() === '') {
        return res.status(400).json({ success: false, message: 'Debe ingresar un correo destinatario.' });
      }

      const result = await MailService.testConnection(email_destino.trim());

      await AuditService.log(req, {
        modulo: 'CONFIG_SMTP',
        accion: 'TEST_SMTP',
        detalle: `Prueba de conexión SMTP enviada a '${email_destino.trim()}'. Resultado: ${result.success ? 'ÉXITO' : 'FALLIDO - ' + result.message}`
      });

      if (result.success) {
        return res.json({ success: true, message: result.message });
      } else {
        return res.status(400).json({ success: false, message: result.message });
      }
    } catch (err: any) {
      console.error('Error en prueba SMTP:', err);
      return res.status(500).json({ success: false, message: err.message || 'Error al procesar la prueba.' });
    }
  }

  // ==========================================
  // LOGS Y AUDITORÍA DEL SISTEMA
  // ==========================================
  static async logsIndex(req: Request, res: Response) {
    try {
      const filtros: FiltrosAuditoria = {
        fechaInicio: req.query.fechaInicio as string || '',
        fechaFin: req.query.fechaFin as string || '',
        idUsuario: req.query.idUsuario ? parseInt(req.query.idUsuario as string, 10) : undefined,
        modulo: req.query.modulo as string || 'TODOS',
        accion: req.query.accion as string || 'TODOS',
        page: req.query.page ? parseInt(req.query.page as string, 10) : 1,
        limit: 25
      };

      const [dataLogs, usuarios] = await Promise.all([
        AuditService.getLogs(filtros),
        userRepo.getAll()
      ]);

      res.render('administracion/logs', {
        logs: dataLogs.logs,
        total: dataLogs.total,
        page: dataLogs.page,
        totalPages: dataLogs.totalPages,
        filtros,
        usuarios,
        formatDate: formatLocalDateTime,
        activeNav: 'admin-logs',
        title: 'Bitácora y Logs de Auditoría'
      });
    } catch (err: any) {
      console.error('Error cargando logs de auditoría:', err);
      req.session.flash = { type: 'error', message: 'Error cargando la bitácora de auditoría.' };
      res.redirect('/dashboard');
    }
  }

  static async logsExportarExcel(req: Request, res: Response) {
    try {
      const filtros: FiltrosAuditoria = {
        fechaInicio: req.query.fechaInicio as string || '',
        fechaFin: req.query.fechaFin as string || '',
        idUsuario: req.query.idUsuario ? parseInt(req.query.idUsuario as string, 10) : undefined,
        modulo: req.query.modulo as string || 'TODOS',
        accion: req.query.accion as string || 'TODOS'
      };

      const logs = await AuditService.getAllFilteredLogs(filtros);

      const workbook = new ExcelJS.Workbook();
      workbook.creator = 'Almacén TOI v4.0';
      workbook.created = new Date();

      const worksheet = workbook.addWorksheet('Auditoría Logs');

      worksheet.columns = [
        { header: 'ID', key: 'id_log', width: 10 },
        { header: 'FECHA / HORA', key: 'fecha_hora', width: 22 },
        { header: 'USUARIO', key: 'nombre_usuario', width: 25 },
        { header: 'ROL', key: 'rol_usuario', width: 15 },
        { header: 'DIRECCIÓN IP', key: 'ip', width: 18 },
        { header: 'MÓDULO', key: 'modulo', width: 20 },
        { header: 'ACCIÓN', key: 'accion', width: 22 },
        { header: 'DETALLE / OBSERVACIÓN', key: 'detalle', width: 45 },
      ];

      // Formato cabecera
      const headerRow = worksheet.getRow(1);
      headerRow.font = { bold: true, color: { argb: 'FFFFFFFF' } };
      headerRow.fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FF1E293B' }
      };

      logs.forEach(log => {
        worksheet.addRow({
          id_log: log.id_log,
          fecha_hora: formatLocalDateTime(log.fecha_hora),
          nombre_usuario: log.nombre_usuario,
          rol_usuario: log.rol_usuario,
          ip: log.ip,
          modulo: log.modulo,
          accion: log.accion,
          detalle: log.detalle || '-'
        });
      });

      res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      res.setHeader('Content-Disposition', `attachment; filename="Auditoria-Logs-${new Date().toISOString().split('T')[0]}.xlsx"`);

      await workbook.xlsx.write(res);
      return res.end();
    } catch (err: any) {
      console.error('Error exportando logs a Excel:', err);
      res.status(500).send('Error generando archivo Excel');
    }
  }
}
