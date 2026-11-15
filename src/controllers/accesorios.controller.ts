import { Request, Response } from 'express';
import { AccesorioRepository } from '../repositories/accesorio.repository';
import { MaestroRepository } from '../repositories/maestro.repository';

const accesorioRepo = new AccesorioRepository();
const maestroRepo = new MaestroRepository();

export class AccesoriosController {
  static async index(req: Request, res: Response) {
    try {
      const filtro = req.query.filtro as string || '';
      const unidadFiltro = req.query.unidad as string || 'Todas';
      const stockBajoFiltro = req.query.stockBajo === '1' || req.query.stock_bajo === 'true';

      const accesorios = await accesorioRepo.getAll(filtro, unidadFiltro, stockBajoFiltro);

      res.render('accesorios/index', {
        accesorios,
        filtro,
        unidadFiltro,
        stockBajoFiltro,
      });
    } catch (err) {
      console.error('Error listando accesorios:', err);
      res.status(500).send('Error interno listando accesorios');
    }
  }

  static async nuevoForm(req: Request, res: Response) {
    try {
      const proveedores = await maestroRepo.getProveedores();
      const categorias = await maestroRepo.getCategorias();
      res.render('accesorios/form', {
        accesorio: null,
        proveedores,
        categorias,
      });
    } catch (err) {
      console.error('Error cargando formulario accesorio:', err);
      res.status(500).send('Error interno');
    }
  }

  static async nuevoSubmit(req: Request, res: Response) {
    try {
      const { nombre, codigo, cantidad, unidad_medida, costo, id_proveedor, id_categoria, fecha_entrada, observaciones } = req.body;

      if (!nombre) {
        req.session.flash = { type: 'error', message: 'El nombre del material es obligatorio.' };
        return res.redirect('/accesorios/nuevo');
      }

      await accesorioRepo.create({
        nombre,
        codigo,
        cantidad: parseInt(cantidad || '0', 10),
        unidad_medida,
        costo: parseFloat(costo || '0'),
        id_proveedor: id_proveedor ? parseInt(id_proveedor, 10) : undefined,
        id_categoria: id_categoria ? parseInt(id_categoria, 10) : undefined,
        fecha_entrada: fecha_entrada || new Date().toISOString().split('T')[0],
        observaciones,
      });

      req.session.flash = { type: 'success', message: 'Accesorio registrado correctamente.' };
      res.redirect('/accesorios');
    } catch (err: any) {
      console.error('Error guardando accesorio:', err);
      req.session.flash = { type: 'error', message: 'Error al registrar accesorio: ' + err.message };
      res.redirect('/accesorios/nuevo');
    }
  }

  static async editarForm(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      const accesorio = await accesorioRepo.findById(id);
      if (!accesorio) {
        req.session.flash = { type: 'error', message: 'Accesorio no encontrado.' };
        return res.redirect('/accesorios');
      }

      const proveedores = await maestroRepo.getProveedores();
      res.render('accesorios/form', {
        accesorio,
        proveedores,
      });
    } catch (err) {
      console.error('Error cargando edición accesorio:', err);
      res.status(500).send('Error interno');
    }
  }

  static async editarSubmit(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      const { nombre, codigo, cantidad, unidad_medida, costo, id_proveedor, observaciones } = req.body;

      await accesorioRepo.update(id, {
        nombre,
        codigo,
        cantidad: parseInt(cantidad || '0', 10),
        unidad_medida,
        costo: parseFloat(costo || '0'),
        id_proveedor: id_proveedor ? parseInt(id_proveedor, 10) : undefined,
        observaciones,
      });

      req.session.flash = { type: 'success', message: 'Accesorio actualizado correctamente.' };
      res.redirect('/accesorios');
    } catch (err: any) {
      console.error('Error actualizando accesorio:', err);
      req.session.flash = { type: 'error', message: 'Error al actualizar: ' + err.message };
      res.redirect('/accesorios/editar/' + req.params.id);
    }
  }

  static async eliminar(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      const ok = await accesorioRepo.delete(id);
      if (!ok) {
        req.session.flash = { type: 'error', message: 'No se puede eliminar el accesorio porque tiene movimientos registrados.' };
      } else {
        req.session.flash = { type: 'success', message: 'Accesorio eliminado correctamente.' };
      }
      res.redirect('/accesorios');
    } catch (err: any) {
      console.error('Error eliminando accesorio:', err);
      req.session.flash = { type: 'error', message: 'Error al eliminar accesorio.' };
      res.redirect('/accesorios');
    }
  }
}
