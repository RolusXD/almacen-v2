import { Request, Response } from 'express';
import { MaestroRepository } from '../repositories/maestro.repository';

const maestroRepo = new MaestroRepository();

export class MaestrosController {
  // TIPOS DE EQUIPOS
  static async tiposIndex(req: Request, res: Response) {
    try {
      const tipos = await maestroRepo.getTiposEquipos();
      res.render('maestros/tipos', { tipos });
    } catch (err) {
      res.status(500).send('Error listando tipos');
    }
  }

  static async tiposSubmit(req: Request, res: Response) {
    try {
      const { codigo, descripcion, categoria, prefijo, observaciones } = req.body;
      await maestroRepo.createTipoEquipo({ codigo, descripcion, categoria, prefijo, observaciones });
      req.session.flash = { type: 'success', message: 'Tipo de equipo registrado correctamente.' };
      res.redirect('/maestros/tipos');
    } catch (err: any) {
      req.session.flash = { type: 'error', message: err.message || 'Error al guardar tipo de equipo' };
      res.redirect('/maestros/tipos');
    }
  }

  static async tiposEliminar(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      const ok = await maestroRepo.deleteTipoEquipo(id);
      if (!ok) {
        req.session.flash = { type: 'error', message: 'No se puede desactivar el tipo porque tiene equipos vinculados.' };
      } else {
        req.session.flash = { type: 'success', message: 'Tipo de equipo desactivado.' };
      }
      res.redirect('/maestros/tipos');
    } catch (err) {
      res.redirect('/maestros/tipos');
    }
  }

  // PROVEEDORES
  static async proveedoresIndex(req: Request, res: Response) {
    try {
      const proveedores = await maestroRepo.getProveedores();
      res.render('maestros/proveedores', { proveedores });
    } catch (err) {
      res.status(500).send('Error listando proveedores');
    }
  }

  static async proveedoresSubmit(req: Request, res: Response) {
    try {
      const { nombre_empresa, contacto, telefono, email, direccion, nit, pagina_web } = req.body;
      await maestroRepo.createProveedor({ nombre_empresa, contacto, telefono, email, direccion, nit, pagina_web });
      req.session.flash = { type: 'success', message: 'Proveedor registrado correctamente.' };
      res.redirect('/maestros/proveedores');
    } catch (err: any) {
      req.session.flash = { type: 'error', message: err.message };
      res.redirect('/maestros/proveedores');
    }
  }

  static async proveedoresEliminar(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      await maestroRepo.deleteProveedor(id);
      req.session.flash = { type: 'success', message: 'Proveedor desactivado.' };
      res.redirect('/maestros/proveedores');
    } catch (err) {
      res.redirect('/maestros/proveedores');
    }
  }

  // CATEGORIAS
  static async categoriasIndex(req: Request, res: Response) {
    try {
      const categorias = await maestroRepo.getCategorias();
      res.render('maestros/categorias', { categorias });
    } catch (err) {
      res.status(500).send('Error listando categorías');
    }
  }

  static async categoriasSubmit(req: Request, res: Response) {
    try {
      const { nombre, descripcion } = req.body;
      await maestroRepo.createCategoria(nombre, descripcion);
      req.session.flash = { type: 'success', message: 'Categoría guardada.' };
      res.redirect('/maestros/categorias');
    } catch (err: any) {
      req.session.flash = { type: 'error', message: err.message };
      res.redirect('/maestros/categorias');
    }
  }

  static async categoriasEliminar(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      await maestroRepo.deleteCategoria(id);
      req.session.flash = { type: 'success', message: 'Categoría eliminada.' };
      res.redirect('/maestros/categorias');
    } catch (err) {
      res.redirect('/maestros/categorias');
    }
  }

  // MARCAS
  static async marcasIndex(req: Request, res: Response) {
    try {
      const marcas = await maestroRepo.getMarcas();
      res.render('maestros/marcas', { marcas });
    } catch (err) {
      res.status(500).send('Error listando marcas');
    }
  }

  static async marcasSubmit(req: Request, res: Response) {
    try {
      const { nombre, pais_origen, pagina_web } = req.body;
      await maestroRepo.createMarca(nombre, pais_origen, pagina_web);
      req.session.flash = { type: 'success', message: 'Marca guardada exitosamente.' };
      res.redirect('/maestros/marcas');
    } catch (err: any) {
      req.session.flash = { type: 'error', message: err.message };
      res.redirect('/maestros/marcas');
    }
  }

  static async marcasEliminar(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      await maestroRepo.deleteMarca(id);
      req.session.flash = { type: 'success', message: 'Marca eliminada.' };
      res.redirect('/maestros/marcas');
    } catch (err) {
      res.redirect('/maestros/marcas');
    }
  }

  // MANTENIMIENTOS
  static async mantenimientosIndex(req: Request, res: Response) {
    try {
      const mantenimientos = await maestroRepo.getMantenimientos();
      res.render('maestros/mantenimientos', { mantenimientos });
    } catch (err) {
      res.status(500).send('Error listando mantenimientos');
    }
  }

  static async mantenimientosSubmit(req: Request, res: Response) {
    try {
      const { numero_serie_mac, tipo_falla, diagnostico, tecnico_responsable, costo_reparacion, fecha_ingreso } = req.body;
      await maestroRepo.createMantenimiento({
        numero_serie_mac,
        tipo_falla,
        diagnostico,
        tecnico_responsable,
        costo_reparacion: parseFloat(costo_reparacion || '0'),
        fecha_ingreso,
      });
      req.session.flash = { type: 'success', message: 'Mantenimiento registrado.' };
      res.redirect('/maestros/mantenimientos');
    } catch (err: any) {
      req.session.flash = { type: 'error', message: err.message };
      res.redirect('/maestros/mantenimientos');
    }
  }

  // PRESTAMOS
  static async prestamosIndex(req: Request, res: Response) {
    try {
      const prestamos = await maestroRepo.getPrestamos();
      res.render('maestros/prestamos', { prestamos });
    } catch (err) {
      res.status(500).send('Error listando préstamos');
    }
  }

  static async prestamosSubmit(req: Request, res: Response) {
    try {
      const { numero_serie_mac, solicitante, departamento, motivo, fecha_prestamo, fecha_devolucion_esperada } = req.body;
      await maestroRepo.createPrestamo({
        numero_serie_mac,
        solicitante,
        departamento,
        motivo,
        fecha_prestamo,
        fecha_devolucion_esperada,
      });
      req.session.flash = { type: 'success', message: 'Préstamo registrado.' };
      res.redirect('/maestros/prestamos');
    } catch (err: any) {
      req.session.flash = { type: 'error', message: err.message };
      res.redirect('/maestros/prestamos');
    }
  }
}
