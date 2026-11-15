import { Request, Response } from 'express';
import { ReportesRepository, FiltrosReporte } from '../repositories/reportes.repository';
import { MovimientoRepository } from '../repositories/movimiento.repository';
import { UsuarioRepository } from '../repositories/usuario.repository';
import { PdfService } from '../services/pdf.service';
import { ExcelService } from '../services/excel.service';
import { MailService } from '../services/mail.service';

const reportesRepo = new ReportesRepository();
const movimientoRepo = new MovimientoRepository();
const userRepo = new UsuarioRepository();

export class ReportesController {
  /**
   * Generación y descarga del Comprobante de Entrega / Remisión en PDF (con protección Anti-IDOR)
   */
  static async descargarComprobante(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      const tipo = (req.query.tipo as string || 'EQUIPO').toUpperCase();
      const user = req.user;
      const userId = req.session.userId;
      const userRole = req.session.userRole;

      if (!id) {
        req.session.flash = { type: 'error', message: 'Identificador de comprobante inválido.' };
        return res.redirect('/movimientos/equipos');
      }

      let result: any = null;
      if (tipo === 'ACCESORIO') {
        result = await reportesRepo.getComprobanteAccesorio(id);
      } else {
        result = await reportesRepo.getComprobanteEquipo(id);
      }

      if (!result || !result.comprobante) {
        return res.status(404).render('error', {
          title: 'Comprobante no encontrado (404)',
          status: 404,
          message: 'No existe registro del movimiento solicitado o no generó remisión.',
          user
        });
      }

      // ==========================================
      // VALIDACIÓN ANTI-IDOR ESTRICTA
      // ==========================================
      // Si el rol es 'usuario', solo puede acceder a comprobantes creados por su propia cuenta
      if (userRole !== 'admin' && result.created_by !== userId) {
        console.warn(`[SEGURIDAD] Intento de acceso no autorizado (IDOR) a comprobante ${id} por usuario ${userId}`);
        return res.status(403).render('error', {
          title: 'Acceso Denegado (403)',
          status: 403,
          message: 'No posees autorización para visualizar o descargar comprobantes generados por otro operador.',
          user
        });
      }

      const pdfBuffer = await PdfService.generarComprobanteDespacho(result.comprobante);

      res.setHeader('Content-Type', 'application/pdf');
      res.setHeader('Content-Disposition', `inline; filename="Comprobante-${result.comprobante.numero_remision}.pdf"`);
      res.setHeader('Content-Length', pdfBuffer.length);
      return res.end(pdfBuffer);
    } catch (err: any) {
      console.error('Error generando comprobante PDF:', err);
      return res.status(500).render('error', {
        title: 'Error de Generación de Comprobante (500)',
        status: 500,
        message: 'Ocurrió un inconveniente al generar el documento PDF.',
        user: req.user
      });
    }
  }

  /**
   * Vista principal del Centro de Reportes (Multi-criterio para Admin / "Mis Movimientos" para Usuario)
   */
  static async index(req: Request, res: Response) {
    try {
      const userRole = req.session.userRole;
      const userId = req.session.userId;
      const esAdmin = userRole === 'admin';

      const filtros: FiltrosReporte = {
        fechaInicio: req.query.fechaInicio as string || '',
        fechaFin: req.query.fechaFin as string || '',
        tipoMovimiento: req.query.tipoMovimiento as string || 'TODOS',
        tipoItem: (req.query.tipoItem as any) || 'TODOS',
        destino: req.query.destino as string || 'TODOS',
        idUsuario: esAdmin && req.query.idUsuario ? parseInt(req.query.idUsuario as string, 10) : undefined,
        buscar: req.query.buscar as string || ''
      };

      // Si no es admin, forzar estrictamente su propio userId en el backend
      const forzarUserId = esAdmin ? undefined : userId;

      const items = await reportesRepo.getReportesUnificados(filtros, forzarUserId);
      const destinos = await movimientoRepo.getDestinosUnicos();
      const usuarios = esAdmin ? await userRepo.getAll() : [];

      res.render('reportes/index', {
        items,
        destinos,
        usuarios,
        filtros,
        esAdmin,
        title: esAdmin ? 'Centro de Reportes Multi-criterio' : 'Historial de Mis Movimientos',
        activeNav: 'reportes'
      });
    } catch (err: any) {
      console.error('Error cargando centro de reportes:', err);
      res.status(500).render('error', {
        title: 'Error de Consulta de Reportes (500)',
        status: 500,
        message: 'No fue posible consultar el centro de reportes.',
        user: req.user
      });
    }
  }

  /**
   * Exportación a Excel (.xlsx) nativo formateado profesionalmente
   */
  static async exportarExcel(req: Request, res: Response) {
    try {
      const userRole = req.session.userRole;
      const userId = req.session.userId;
      const esAdmin = userRole === 'admin';

      const filtros: FiltrosReporte = {
        fechaInicio: req.query.fechaInicio as string || '',
        fechaFin: req.query.fechaFin as string || '',
        tipoMovimiento: req.query.tipoMovimiento as string || 'TODOS',
        tipoItem: (req.query.tipoItem as any) || 'TODOS',
        destino: req.query.destino as string || 'TODOS',
        idUsuario: esAdmin && req.query.idUsuario ? parseInt(req.query.idUsuario as string, 10) : undefined,
        buscar: req.query.buscar as string || ''
      };

      const forzarUserId = esAdmin ? undefined : userId;
      const items = await reportesRepo.getReportesUnificados(filtros, forzarUserId);

      const titulo = esAdmin ? 'Movimientos Globales de Almacén' : `Movimientos Operador - ${req.session.userName || 'Usuario'}`;
      const excelBuffer = await ExcelService.generarReporteMovimientos(items, titulo);

      res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      res.setHeader('Content-Disposition', `attachment; filename="Reporte-Almacen-${new Date().toISOString().split('T')[0]}.xlsx"`);
      res.setHeader('Content-Length', excelBuffer.length);
      return res.end(excelBuffer);
    } catch (err: any) {
      console.error('Error exportando Excel:', err);
      req.session.flash = { type: 'error', message: 'Error al generar el archivo Excel.' };
      res.redirect('/reportes');
    }
  }

  /**
   * Despacho opcional de reporte por correo electrónico con archivo adjunto
   */
  static async enviarCorreoReporte(req: Request, res: Response) {
    try {
      const { email_destino, formato } = req.body;
      const userRole = req.session.userRole;
      const userId = req.session.userId;
      const esAdmin = userRole === 'admin';

      if (!email_destino || email_destino.trim() === '') {
        return res.status(400).json({ success: false, message: 'Ingrese una dirección de correo válida.' });
      }

      const forzarUserId = esAdmin ? undefined : userId;
      const items = await reportesRepo.getReportesUnificados({}, forzarUserId);
      const titulo = esAdmin ? 'Movimientos Globales de Almacén' : 'Mis Movimientos Registrados';

      const excelBuffer = await ExcelService.generarReporteMovimientos(items, titulo);
      const filename = `Reporte-Almacen-${new Date().toISOString().split('T')[0]}.xlsx`;

      const result = await MailService.sendReportWithAttachment({
        destinatario: email_destino.trim(),
        asunto: `Reporte de Movimientos - Almacén TOI (${new Date().toLocaleDateString('es-BO')})`,
        mensaje: `Se adjunta el reporte consolidado de operaciones generado por ${req.session.userName || 'Almacén TOI'}.`,
        archivo: {
          nombre: filename,
          contenido: excelBuffer,
          contentType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        }
      });

      return res.json(result);
    } catch (err: any) {
      console.error('Error enviando reporte por correo:', err);
      return res.status(500).json({ success: false, message: err.message || 'Error al enviar reporte.' });
    }
  }

  // Rutas legacy de exportación simple preservadas para compatibilidad
  static async exportarEquipos(req: Request, res: Response) {
    res.redirect('/reportes/exportar/excel?tipoItem=EQUIPOS');
  }

  static async exportarAccesorios(req: Request, res: Response) {
    res.redirect('/reportes/exportar/excel?tipoItem=ACCESORIOS');
  }

  static async exportarMovimientosEquipos(req: Request, res: Response) {
    res.redirect('/reportes/exportar/excel?tipoItem=EQUIPOS');
  }

  static async exportarMovimientosAccesorios(req: Request, res: Response) {
    res.redirect('/reportes/exportar/excel?tipoItem=ACCESORIOS');
  }
}
