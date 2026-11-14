import { Request, Response } from 'express';
import { EquipoRepository } from '../repositories/equipo.repository';
import { AccesorioRepository } from '../repositories/accesorio.repository';
import { MovimientoRepository } from '../repositories/movimiento.repository';

const equipoRepo = new EquipoRepository();
const accesorioRepo = new AccesorioRepository();
const movimientoRepo = new MovimientoRepository();

export class DashboardController {
  static async index(req: Request, res: Response) {
    try {
      const statsEquipos = await equipoRepo.getEstadisticas();
      const statsAccesorios = await accesorioRepo.getEstadisticas();
      const ultimosMovimientos = await movimientoRepo.getMovimientosEquipos(undefined, undefined, undefined, 8);

      res.render('dashboard/index', {
        statsEquipos,
        statsAccesorios,
        ultimosMovimientos,
      });
    } catch (err) {
      console.error('Error al cargar dashboard:', err);
      res.status(500).send('Error interno cargando dashboard');
    }
  }
}
