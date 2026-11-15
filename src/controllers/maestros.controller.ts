import { Request, Response } from 'express';
import { MaestroRepository } from '../repositories/maestro.repository';
import { AuditService } from '../services/audit.service';

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
      await AuditService.log(req, {
        modulo: 'MAESTROS',
        accion: 'CREAR',
        detalle: `Creación de tipo de equipo: ${descripcion} (${codigo || 'sin código'})`
      });
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
        await AuditService.log(req, {
          modulo: 'MAESTROS',
          accion: 'ELIMINAR',
          detalle: `Desactivación de tipo de equipo ID ${id}`
        });
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
      await AuditService.log(req, {
        modulo: 'MAESTROS',
        accion: 'CREAR',
        detalle: `Registro de proveedor: ${nombre_empresa} (NIT: ${nit || 'N/A'})`
      });
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
      await AuditService.log(req, {
        modulo: 'MAESTROS',
        accion: 'ELIMINAR',
        detalle: `Desactivación de proveedor ID ${id}`
      });
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
    } catch (err: any) {
      console.error('Error listando categorías:', err);
      req.session.flash = { type: 'error', message: 'Error listando categorías: ' + (err.message || '') };
      res.render('maestros/categorias', { categorias: [] });
    }
  }

  static async categoriasSubmit(req: Request, res: Response) {
    try {
      const { nombre, descripcion } = req.body;
      await maestroRepo.createCategoria(nombre, descripcion);
      await AuditService.log(req, {
        modulo: 'MAESTROS',
        accion: 'CREAR',
        detalle: `Creación de categoría: ${nombre}`
      });
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
      await AuditService.log(req, {
        modulo: 'MAESTROS',
        accion: 'ELIMINAR',
        detalle: `Eliminación de categoría ID ${id}`
      });
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
      await AuditService.log(req, {
        modulo: 'MAESTROS',
        accion: 'CREAR',
        detalle: `Creación de marca: ${nombre} (${pais_origen || 'sin país'})`
      });
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
      await AuditService.log(req, {
        modulo: 'MAESTROS',
        accion: 'ELIMINAR',
        detalle: `Eliminación de marca ID ${id}`
      });
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
    } catch (err: any) {
      console.error('Error en mantenimientosIndex:', err);
      req.session.flash = { type: 'error', message: 'Error cargando historial de mantenimientos: ' + err.message };
      res.render('maestros/mantenimientos', { mantenimientos: [] });
    }
  }

  static async mantenimientosSubmit(req: Request, res: Response) {
    try {
      const {
        numero_serie_mac,
        tipo_mantenimiento,
        tipo_falla,
        descripcion,
        diagnostico,
        tecnico_responsable,
        costo,
        costo_reparacion,
        fecha_ingreso,
        repuestos_cambiados,
        observaciones,
      } = req.body;

      await maestroRepo.createMantenimiento({
        numero_serie_mac: (numero_serie_mac || '').trim(),
        tipo_mantenimiento: tipo_mantenimiento || (tipo_falla === 'PREVENTIVO' ? 'PREVENTIVO' : 'CORRECTIVO'),
        descripcion: descripcion || diagnostico || tipo_falla || 'Mantenimiento registrado',
        tecnico_responsable: tecnico_responsable || 'Técnico de Laboratorio',
        costo: parseFloat(costo || costo_reparacion || '0') || 0,
        fecha_ingreso: fecha_ingreso || new Date().toISOString().split('T')[0],
        repuestos_cambiados,
        observaciones,
      });

      req.session.flash = { type: 'success', message: 'Mantenimiento registrado e ingresado al taller.' };
      res.redirect('/maestros/mantenimientos');
    } catch (err: any) {
      console.error('Error en mantenimientosSubmit:', err);
      req.session.flash = { type: 'error', message: 'Error al registrar mantenimiento: ' + err.message };
      res.redirect('/maestros/mantenimientos');
    }
  }

  static async mantenimientosCompletar(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      const { fecha_entrega, observaciones } = req.body;
      await maestroRepo.completeMantenimiento(id, fecha_entrega, observaciones);
      req.session.flash = { type: 'success', message: 'Mantenimiento completado y equipo devuelto a DISPONIBLE.' };
      res.redirect('/maestros/mantenimientos');
    } catch (err: any) {
      req.session.flash = { type: 'error', message: 'Error completando mantenimiento: ' + err.message };
      res.redirect('/maestros/mantenimientos');
    }
  }

  // PRESTAMOS
  static async prestamosIndex(req: Request, res: Response) {
    try {
      const prestamos = await maestroRepo.getPrestamos();
      res.render('maestros/prestamos', { prestamos });
    } catch (err: any) {
      console.error('Error en prestamosIndex:', err);
      req.session.flash = { type: 'error', message: 'Error cargando préstamos: ' + err.message };
      res.render('maestros/prestamos', { prestamos: [] });
    }
  }

  static async prestamosSubmit(req: Request, res: Response) {
    try {
      const {
        numero_serie_mac,
        nombre_solicita,
        solicitante,
        cargo_solicita,
        departamento,
        telefono_contacto,
        motivo_prestamo,
        motivo,
        fecha_prestamo,
        fecha_devolucion_esperada,
        observaciones_salida,
        observaciones,
      } = req.body;

      await maestroRepo.createPrestamo({
        numero_serie_mac: (numero_serie_mac || '').trim(),
        nombre_solicita: (nombre_solicita || solicitante || '').trim(),
        cargo_solicita,
        departamento: departamento || 'Operaciones',
        telefono_contacto,
        motivo_prestamo: motivo_prestamo || motivo || 'Préstamo operativo temporal',
        fecha_prestamo: fecha_prestamo || new Date().toISOString().split('T')[0],
        fecha_devolucion_esperada: fecha_devolucion_esperada || new Date(Date.now() + 7 * 86400000).toISOString().split('T')[0],
        observaciones_salida: observaciones_salida || observaciones,
      });

      req.session.flash = { type: 'success', message: 'Préstamo registrado y equipo marcado como OCUPADO.' };
      res.redirect('/maestros/prestamos');
    } catch (err: any) {
      console.error('Error en prestamosSubmit:', err);
      req.session.flash = { type: 'error', message: 'Error al registrar préstamo: ' + err.message };
      res.redirect('/maestros/prestamos');
    }
  }

  static async prestamosDevolver(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      const { fecha_devolucion_real, observaciones_devolucion } = req.body;
      await maestroRepo.returnPrestamo(id, fecha_devolucion_real, observaciones_devolucion);
      req.session.flash = { type: 'success', message: 'Préstamo devuelto con éxito y equipo marcado como DISPONIBLE.' };
      res.redirect('/maestros/prestamos');
    } catch (err: any) {
      req.session.flash = { type: 'error', message: 'Error devolviendo préstamo: ' + err.message };
      res.redirect('/maestros/prestamos');
    }
  }
}
