import { Request, Response } from 'express';
import { MovimientoRepository } from '../repositories/movimiento.repository';
import { EquipoRepository } from '../repositories/equipo.repository';
import { AccesorioRepository } from '../repositories/accesorio.repository';

const movimientoRepo = new MovimientoRepository();
const equipoRepo = new EquipoRepository();
const accesorioRepo = new AccesorioRepository();

export class MovimientosController {
  // ==========================================
  // MOVIMIENTOS DE EQUIPOS
  // ==========================================
  static async equiposIndex(req: Request, res: Response) {
    try {
      const filtro = req.query.filtro as string || '';
      const destinoFiltro = req.query.destino as string || '';

      const movimientos = await movimientoRepo.getMovimientosEquipos(filtro, destinoFiltro);
      const equiposDisponibles = await equipoRepo.searchDisponiblesParaMovimiento();
      const destinos = await movimientoRepo.getDestinosUnicos();

      res.render('movimientos/equipos', {
        movimientos,
        equiposDisponibles,
        destinos,
        filtro,
        destinoFiltro,
      });
    } catch (err) {
      console.error('Error cargando movimientos de equipos:', err);
      res.status(500).send('Error interno');
    }
  }

  static async equiposSubmit(req: Request, res: Response) {
    try {
      const { numero_serie_mac, tipo_movimiento, asunto, encargado, destino, observaciones, fecha_movimiento } = req.body;

      if (!numero_serie_mac || !tipo_movimiento || !asunto || !encargado) {
        req.session.flash = { type: 'error', message: 'Complete todos los campos requeridos.' };
        return res.redirect('/movimientos/equipos');
      }

      if (tipo_movimiento === 'SALIDA' && (!destino || destino.trim() === '')) {
        req.session.flash = { type: 'error', message: 'El campo Destino es obligatorio en egresos/salidas de almacén.' };
        return res.redirect('/movimientos/equipos');
      }

      await movimientoRepo.registrarMovimientoEquipo({
        numero_serie_mac: numero_serie_mac.trim(),
        tipo_movimiento,
        asunto,
        encargado,
        destino: destino || undefined,
        observaciones,
        fecha_movimiento: fecha_movimiento || new Date().toISOString().split('T')[0],
        created_by: req.session.userId,
      });

      req.session.flash = { type: 'success', message: `Movimiento de equipo (${tipo_movimiento}) registrado exitosamente.` };
      res.redirect('/movimientos/equipos');
    } catch (err: any) {
      console.error('Error registrando movimiento de equipo:', err);
      req.session.flash = { type: 'error', message: 'Error: ' + (err.message || 'Error al procesar movimiento') };
      res.redirect('/movimientos/equipos');
    }
  }

  // ==========================================
  // MOVIMIENTOS DE ACCESORIOS
  // ==========================================
  static async accesoriosIndex(req: Request, res: Response) {
    try {
      const filtro = req.query.filtro as string || '';
      const destinoFiltro = req.query.destino as string || '';
      const selectedId = req.query.id_accesorio as string || '';

      const movimientos = await movimientoRepo.getMovimientosAccesorios(filtro, destinoFiltro);
      const accesoriosDisponibles = await accesorioRepo.getAll();
      const destinos = await movimientoRepo.getDestinosUnicos();

      res.render('movimientos/accesorios', {
        movimientos,
        accesoriosDisponibles,
        destinos,
        filtro,
        destinoFiltro,
        selectedId,
      });
    } catch (err) {
      console.error('Error cargando movimientos de accesorios:', err);
      res.status(500).send('Error interno');
    }
  }

  static async accesoriosSubmit(req: Request, res: Response) {
    try {
      const { id_accesorio, tipo_movimiento, cantidad, asunto, encargado, destino, observaciones, fecha_movimiento } = req.body;

      if (!id_accesorio || !tipo_movimiento || !cantidad || !asunto || !encargado) {
        req.session.flash = { type: 'error', message: 'Complete todos los campos requeridos.' };
        return res.redirect('/movimientos/accesorios');
      }

      const cantNum = parseInt(cantidad, 10);
      if (isNaN(cantNum) || cantNum <= 0) {
        req.session.flash = { type: 'error', message: 'La cantidad debe ser mayor a cero.' };
        return res.redirect('/movimientos/accesorios');
      }

      if (tipo_movimiento === 'SALIDA' && (!destino || destino.trim() === '')) {
        req.session.flash = { type: 'error', message: 'El campo Destino es obligatorio al asignar accesorios.' };
        return res.redirect('/movimientos/accesorios');
      }

      await movimientoRepo.registrarMovimientoAccesorio({
        id_accesorio: parseInt(id_accesorio, 10),
        tipo_movimiento,
        cantidad: cantNum,
        asunto,
        encargado,
        destino: destino || undefined,
        observaciones,
        fecha_movimiento: fecha_movimiento || new Date().toISOString().split('T')[0],
        created_by: req.session.userId,
      });

      req.session.flash = { type: 'success', message: `Movimiento de accesorio (${tipo_movimiento}) registrado con éxito.` };
      res.redirect('/movimientos/accesorios');
    } catch (err: any) {
      console.error('Error registrando movimiento accesorio:', err);
      req.session.flash = { type: 'error', message: 'Error: ' + (err.message || 'Error al procesar movimiento') };
      res.redirect('/movimientos/accesorios');
    }
  }
}
