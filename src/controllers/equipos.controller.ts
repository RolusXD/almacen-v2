import { Request, Response } from 'express';
import { EquipoRepository } from '../repositories/equipo.repository';
import { MaestroRepository } from '../repositories/maestro.repository';

const equipoRepo = new EquipoRepository();
const maestroRepo = new MaestroRepository();

export class EquiposController {
  static async index(req: Request, res: Response) {
    try {
      const filtro = req.query.filtro as string || '';
      const estadoFiltro = req.query.estado as string || 'Todos';
      const codigoFiltro = req.query.codigo as string || 'Todos';

      const equipos = await equipoRepo.getAll(filtro, estadoFiltro, codigoFiltro);
      const tipos = await maestroRepo.getTiposEquipos();

      res.render('equipos/index', {
        equipos,
        tipos,
        filtro,
        estadoFiltro,
        codigoFiltro,
      });
    } catch (err) {
      console.error('Error listando equipos:', err);
      res.status(500).send('Error interno listando equipos');
    }
  }

  static async nuevoForm(req: Request, res: Response) {
    try {
      const tipos = await maestroRepo.getTiposEquipos();
      const modelosRef = await equipoRepo.getModelosReferencia();

      res.render('equipos/form', {
        equipo: null,
        tipos,
        modelosRef,
      });
    } catch (err) {
      console.error('Error cargando formulario nuevo equipo:', err);
      res.status(500).send('Error interno');
    }
  }

  static async nuevoSubmit(req: Request, res: Response) {
    try {
      const { nombre, numero_serie_mac, codigo, marca, modelo, precio, proveedor, fecha_entrada, observaciones } = req.body;

      if (!nombre || !numero_serie_mac) {
        req.session.flash = { type: 'error', message: 'El nombre y número de serie son obligatorios.' };
        return res.redirect('/equipos/nuevo');
      }

      // Validar si la serie ya existe
      const existente = await equipoRepo.findBySerieMac(numero_serie_mac);
      if (existente) {
        req.session.flash = { type: 'error', message: `El número de serie o MAC '${numero_serie_mac}' ya está registrado.` };
        return res.redirect('/equipos/nuevo');
      }

      await equipoRepo.create({
        nombre,
        numero_serie_mac,
        codigo,
        marca,
        modelo,
        precio: parseFloat(precio || '0'),
        proveedor,
        fecha_entrada,
        observaciones,
      });

      req.session.flash = { type: 'success', message: 'Equipo registrado exitosamente.' };
      res.redirect('/equipos');
    } catch (err: any) {
      console.error('Error guardando equipo:', err);
      req.session.flash = { type: 'error', message: 'Error al registrar equipo: ' + (err.message || 'error interno') };
      res.redirect('/equipos/nuevo');
    }
  }

  static async editarForm(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      const equipo = await equipoRepo.findById(id);
      if (!equipo) {
        req.session.flash = { type: 'error', message: 'Equipo no encontrado.' };
        return res.redirect('/equipos');
      }

      const tipos = await maestroRepo.getTiposEquipos();
      const modelosRef = await equipoRepo.getModelosReferencia();

      res.render('equipos/form', {
        equipo,
        tipos,
        modelosRef,
      });
    } catch (err) {
      console.error('Error cargando formulario edición equipo:', err);
      res.status(500).send('Error interno');
    }
  }

  static async editarSubmit(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      const { nombre, codigo, marca, modelo, precio, proveedor, fecha_entrada, observaciones, estado } = req.body;

      await equipoRepo.update(id, {
        nombre,
        codigo,
        marca,
        modelo,
        precio: parseFloat(precio || '0'),
        proveedor,
        fecha_entrada,
        observaciones,
        estado,
      });

      req.session.flash = { type: 'success', message: 'Equipo actualizado correctamente.' };
      res.redirect('/equipos');
    } catch (err: any) {
      console.error('Error actualizando equipo:', err);
      req.session.flash = { type: 'error', message: 'Error al actualizar: ' + err.message };
      res.redirect('/equipos/editar/' + req.params.id);
    }
  }

  static async eliminar(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      const ok = await equipoRepo.delete(id);
      if (!ok) {
        req.session.flash = { type: 'error', message: 'No se puede eliminar el equipo porque cuenta con historial de movimientos.' };
      } else {
        req.session.flash = { type: 'success', message: 'Equipo eliminado con éxito.' };
      }
      res.redirect('/equipos');
    } catch (err: any) {
      console.error('Error eliminando equipo:', err);
      req.session.flash = { type: 'error', message: 'Error al eliminar equipo.' };
      res.redirect('/equipos');
    }
  }

  static async apiBuscar(req: Request, res: Response) {
    try {
      const serie = (req.query.serie as string || '').trim();
      const equipo = await equipoRepo.findBySerieMac(serie);
      if (!equipo) {
        return res.json({ found: false });
      }
      return res.json({
        found: true,
        equipo: {
          id: equipo.id_equipo,
          serie: equipo.numero_serie_mac,
          nombre: equipo.nombre,
          codigo: equipo.codigo,
          modelo: equipo.modelo,
          marca: equipo.marca,
          estado: equipo.estado,
        },
      });
    } catch (err) {
      res.status(500).json({ found: false, error: 'Error de servidor' });
    }
  }
}
