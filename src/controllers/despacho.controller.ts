import { Request, Response } from 'express';
import { DespachoRepository, FiltrosDespacho } from '../repositories/despacho.repository';
import { EquipoRepository } from '../repositories/equipo.repository';
import { AccesorioRepository } from '../repositories/accesorio.repository';
import { MovimientoRepository } from '../repositories/movimiento.repository';
import { ConfiguracionRepository } from '../repositories/configuracion.repository';
import { AuditService } from '../services/audit.service';
import { PdfService } from '../services/pdf.service';
import { formatLocalDateTime, formatLocalDate } from '../utils/date.helper';
import { ItemCarritoDespacho, ComprobanteDespachoUnificado } from '../models/types';

const despachoRepo = new DespachoRepository();
const equipoRepo = new EquipoRepository();
const accesorioRepo = new AccesorioRepository();
const movimientoRepo = new MovimientoRepository();
const configRepo = new ConfiguracionRepository();

export class DespachoController {
  /**
   * Vista de Historial de Despachos (Anti-IDOR según rol)
   */
  static async index(req: Request, res: Response) {
    try {
      const userRole = req.session.userRole;
      const userId = req.session.userId;
      const esAdmin = userRole === 'admin';

      const filtros: FiltrosDespacho = {
        fechaInicio: req.query.fechaInicio as string || '',
        fechaFin: req.query.fechaFin as string || '',
        destino: req.query.destino as string || 'TODOS',
        buscar: req.query.buscar as string || ''
      };

      // Si no es admin, forzar estrictamente su propio userId en el backend
      const forzarUserId = esAdmin ? undefined : userId;

      const [ordenes, destinos] = await Promise.all([
        despachoRepo.getOrdenes(filtros, forzarUserId),
        movimientoRepo.getDestinosUnicos()
      ]);

      res.render('despachos/index', {
        ordenes,
        destinos,
        filtros,
        esAdmin,
        formatDateTime: formatLocalDateTime,
        formatDate: formatLocalDate,
        title: esAdmin ? 'Historial de Despachos y Salidas' : 'Mis Despachos Realizados',
        activeNav: 'despachos-historial'
      });
    } catch (err: any) {
      console.error('Error listando órdenes de despacho:', err);
      req.session.flash = { type: 'error', message: 'Error cargando el historial de despachos.' };
      res.redirect('/dashboard');
    }
  }

  /**
   * Vista para armar un Nuevo Despacho General (Carrito interactivo)
   */
  static async nuevoForm(req: Request, res: Response) {
    try {
      const [equiposDisponibles, accesoriosDisponibles, destinos] = await Promise.all([
        equipoRepo.getDisponibles(),
        accesorioRepo.getAll(), // Tomamos los accesorios y filtramos en memoria los con stock > 0
        movimientoRepo.getDestinosUnicos()
      ]);

      const materialesConStock = accesoriosDisponibles.filter((a: any) => a.cantidad > 0);

      res.render('despachos/nuevo', {
        equiposDisponibles,
        accesoriosDisponibles: materialesConStock,
        destinos,
        title: 'Nuevo Despacho General (Orden Unificada)',
        activeNav: 'despachos-nuevo'
      });
    } catch (err: any) {
      console.error('Error cargando formulario de nuevo despacho:', err);
      req.session.flash = { type: 'error', message: 'Error cargando datos para despacho.' };
      res.redirect('/despachos');
    }
  }

  /**
   * Confirmación y emisión atómica de la Orden de Despacho
   */
  static async confirmarSubmit(req: Request, res: Response) {
    try {
      const { destino, encargado_receptor, asunto, observaciones, items_payload } = req.body;

      if (!destino || !encargado_receptor || !asunto) {
        req.session.flash = { type: 'error', message: 'Destino, Encargado Receptor y Asunto son obligatorios.' };
        return res.redirect('/despachos/nuevo');
      }

      let items: ItemCarritoDespacho[] = [];
      if (typeof items_payload === 'string') {
        try {
          items = JSON.parse(items_payload);
        } catch (e) {
          req.session.flash = { type: 'error', message: 'Formato de ítems inválido en la solicitud.' };
          return res.redirect('/despachos/nuevo');
        }
      } else if (Array.isArray(req.body.items)) {
        items = req.body.items;
      }

      if (!items || items.length === 0) {
        req.session.flash = { type: 'error', message: 'Debe agregar al menos un equipo o material a la orden de despacho.' };
        return res.redirect('/despachos/nuevo');
      }

      const userId = req.session.userId;
      if (!userId) {
        req.session.flash = { type: 'error', message: 'Sesión expirada o no válida.' };
        return res.redirect('/auth/login');
      }

      const orden = await despachoRepo.crearDespachoTransaccional({
        destino: destino.trim(),
        encargado_receptor: encargado_receptor.trim(),
        asunto: asunto.trim(),
        observaciones: observaciones ? observaciones.trim() : undefined,
        created_by: userId,
        items
      });

      await AuditService.log(req, {
        modulo: 'DESPACHOS',
        accion: 'DESPACHO_CONFIRMADO',
        detalle: `Orden ${orden.numero_orden} emitida exitosamente para '${orden.destino}' con ${items.length} ítems.`
      });

      // Notificación con enlace de descarga directa del comprobante PDF
      req.session.flash = {
        type: 'success',
        message: `Orden de Despacho ${orden.numero_orden} registrada correctamente.`,
        link: {
          url: `/despachos/comprobante/${orden.id_orden}`,
          text: 'Descargar Comprobante PDF'
        }
      };

      res.redirect('/despachos');
    } catch (err: any) {
      console.error('Error emitiendo orden de despacho:', err);
      req.session.flash = { type: 'error', message: err.message || 'Error al emitir la orden de despacho.' };
      res.redirect('/despachos/nuevo');
    }
  }

  /**
   * API para previsualizar el detalle de una orden en modal flotante (Anti-IDOR)
   */
  static async apiDetalle(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      const userRole = req.session.userRole;
      const userId = req.session.userId;

      if (!id) {
        return res.status(400).json({ success: false, message: 'ID de orden inválido' });
      }

      const orden = await despachoRepo.getOrdenById(id);
      if (!orden) {
        return res.status(404).json({ success: false, message: 'Orden de despacho no encontrada' });
      }

      // Validación Anti-IDOR
      if (userRole !== 'admin' && orden.created_by !== userId) {
        await AuditService.log(req, {
          modulo: 'DESPACHOS',
          accion: 'ACCESO_DENEGADO',
          detalle: `Intento de acceso no autorizado (IDOR) a detalle de orden ${id} por usuario ${userId}`
        });
        return res.status(403).json({ success: false, message: 'No tienes permiso para ver esta orden.' });
      }

      const detalles = await despachoRepo.getDetallesByOrdenId(id);

      return res.json({
        success: true,
        orden: {
          ...orden,
          fecha_formateada: formatLocalDateTime(orden.fecha_despacho)
        },
        detalles
      });
    } catch (err: any) {
      console.error('Error obteniendo detalle de orden:', err);
      return res.status(500).json({ success: false, message: 'Error interno obteniendo detalles' });
    }
  }

  /**
   * Anulación transaccional de una orden de despacho (Solo Admin)
   */
  static async anular(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      if (!id) {
        return res.redirect('/despachos');
      }

      await despachoRepo.anularOrdenTransaccional(id);

      await AuditService.log(req, {
        modulo: 'DESPACHOS',
        accion: 'DESPACHO_ANULADO',
        detalle: `Orden de despacho ID ${id} anulada y stock revertido correctamente.`
      });

      req.session.flash = {
        type: 'success',
        message: 'Orden de despacho anulada exitosamente y stock restituido.'
      };

      res.redirect('/despachos');
    } catch (err: any) {
      console.error('Error anulando orden de despacho:', err);
      req.session.flash = { type: 'error', message: err.message || 'Error al anular orden de despacho.' };
      res.redirect('/despachos');
    }
  }

  /**
   * Genera y descarga el Comprobante de Despacho Unificado en PDF (Anti-IDOR)
   */
  static async descargarComprobante(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      const userRole = req.session.userRole;
      const userId = req.session.userId;

      if (!id) {
        return res.status(404).render('error', {
          title: 'Orden no encontrada (404)',
          status: 404,
          message: 'Identificador de orden inválido.',
          user: req.user
        });
      }

      const orden = await despachoRepo.getOrdenById(id);
      if (!orden) {
        return res.status(404).render('error', {
          title: 'Orden no encontrada (404)',
          status: 404,
          message: 'No existe registro de la orden de despacho solicitada.',
          user: req.user
        });
      }

      // Validación Anti-IDOR estricta
      if (userRole !== 'admin' && orden.created_by !== userId) {
        console.warn(`[SEGURIDAD] Intento IDOR en comprobante de despacho ${id} por usuario ${userId}`);
        await AuditService.log(req, {
          modulo: 'DESPACHOS',
          accion: 'ACCESO_DENEGADO',
          detalle: `Intento no autorizado de descarga de comprobante ${orden.numero_orden} por usuario ${userId}`
        });

        return res.status(403).render('error', {
          title: 'Acceso Denegado (403)',
          status: 403,
          message: 'No posees autorización para descargar comprobantes emitidos por otro operador.',
          user: req.user
        });
      }

      const [detalles, config] = await Promise.all([
        despachoRepo.getDetallesByOrdenId(id),
        configRepo.getConfiguracion()
      ]);

      const equipos = detalles
        .filter(d => d.tipo_item === 'EQUIPO')
        .map(e => ({
          numero_serie_mac: e.numero_serie_mac || 'S/N',
          descripcion: e.descripcion,
          codigo: e.codigo || undefined,
          marca: e.marca || undefined,
          modelo: e.modelo || undefined,
          cantidad: 1
        }));

      const accesorios = detalles
        .filter(d => d.tipo_item === 'ACCESORIO')
        .map(a => ({
          descripcion: a.descripcion,
          codigo: a.codigo || undefined,
          marca: a.marca || undefined,
          cantidad: Number(a.cantidad),
          unidad_medida: a.unidad_medida || 'UNIDAD'
        }));

      const pdfData: ComprobanteDespachoUnificado = {
        id_orden: orden.id_orden,
        numero_orden: orden.numero_orden,
        fecha_despacho: formatLocalDateTime(orden.fecha_despacho),
        destino: orden.destino,
        encargado_receptor: orden.encargado_receptor,
        asunto: orden.asunto,
        observaciones: orden.observaciones,
        operador_nombre: orden.operador_nombre || 'Almacén TOI',
        operador_carnet: orden.operador_carnet,
        empresa_nombre: config.empresa_nombre,
        empresa_nit: config.empresa_nit || undefined,
        empresa_direccion: config.empresa_direccion || undefined,
        empresa_telefono: config.empresa_telefono || undefined,
        almacen_pie_firma: config.almacen_pie_firma || undefined,
        equipos,
        accesorios
      };

      const pdfBuffer = await PdfService.generarComprobanteDespachoUnificado(pdfData);

      res.setHeader('Content-Type', 'application/pdf');
      res.setHeader('Content-Disposition', `inline; filename="Despacho-${orden.numero_orden}.pdf"`);
      res.setHeader('Content-Length', pdfBuffer.length);
      return res.end(pdfBuffer);
    } catch (err: any) {
      console.error('Error generando comprobante PDF unificado:', err);
      res.status(500).render('error', {
        title: 'Error de Generación de Comprobante (500)',
        status: 500,
        message: 'Ocurrió un inconveniente al generar el comprobante PDF.',
        user: req.user
      });
    }
  }
}
