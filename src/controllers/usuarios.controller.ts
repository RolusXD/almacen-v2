import { Request, Response } from 'express';
import bcrypt from 'bcryptjs';
import { UsuarioRepository } from '../repositories/usuario.repository';
import { AuditService } from '../services/audit.service';

const userRepo = new UsuarioRepository();

export class UsuariosController {
  static async index(req: Request, res: Response) {
    try {
      const usuarios = await userRepo.getAll();
      res.render('usuarios/index', { usuarios });
    } catch (err) {
      console.error('Error listando usuarios:', err);
      res.status(500).render('error', {
        title: 'Error de Servidor (500)',
        status: 500,
        message: 'No fue posible obtener la lista de usuarios.',
        user: req.user
      });
    }
  }

  static async nuevoSubmit(req: Request, res: Response) {
    try {
      const { nombre_completo, usuario, password, carnet, email, rol, fecha_nacimiento } = req.body;

      if (!nombre_completo || !usuario || !password || !carnet || !email) {
        req.session.flash = { type: 'error', message: 'Todos los campos obligatorios deben ser completados.' };
        return res.redirect('/usuarios');
      }

      const existing = await userRepo.findByUsuario(usuario);
      if (existing) {
        req.session.flash = { type: 'error', message: `El nombre de usuario '${usuario}' ya se encuentra registrado.` };
        return res.redirect('/usuarios');
      }

      const password_hash = await bcrypt.hash(password, 10);
      await userRepo.create({
        nombre_completo,
        usuario,
        password_hash,
        carnet,
        email,
        fecha_nacimiento: fecha_nacimiento || '1995-01-01',
        rol: rol === 'admin' ? 'admin' : 'usuario',
      });

      await AuditService.log(req, {
        modulo: 'USUARIOS',
        accion: 'CREAR',
        detalle: `Creación de usuario '${usuario}' con rol '${rol || 'usuario'}'`
      });

      req.session.flash = { type: 'success', message: `Usuario '${usuario}' creado exitosamente.` };
      res.redirect('/usuarios');
    } catch (err: any) {
      console.error('Error creando usuario:', err);
      req.session.flash = { type: 'error', message: err.message || 'Error al crear usuario' };
      res.redirect('/usuarios');
    }
  }

  static async editarSubmit(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      const { nombre_completo, carnet, email, rol, fecha_nacimiento } = req.body;

      if (!id || !nombre_completo || !carnet || !email) {
        req.session.flash = { type: 'error', message: 'Faltan datos requeridos para actualizar el usuario.' };
        return res.redirect('/usuarios');
      }

      await userRepo.update(id, {
        nombre_completo,
        carnet,
        email,
        rol: rol === 'admin' ? 'admin' : 'usuario',
        fecha_nacimiento: fecha_nacimiento || '1995-01-01'
      });

      await AuditService.log(req, {
        modulo: 'USUARIOS',
        accion: 'EDITAR',
        detalle: `Actualización de perfil para usuario ID ${id} (${nombre_completo})`
      });

      req.session.flash = { type: 'success', message: 'Datos del usuario actualizados correctamente.' };
      res.redirect('/usuarios');
    } catch (err: any) {
      console.error('Error actualizando usuario:', err);
      req.session.flash = { type: 'error', message: err.message || 'Error al actualizar usuario.' };
      res.redirect('/usuarios');
    }
  }

  static async cambiarPasswordSubmit(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      const { nueva_password, confirmar_password } = req.body;

      if (!id || !nueva_password || nueva_password.length < 6) {
        req.session.flash = { type: 'error', message: 'La nueva contraseña debe tener al menos 6 caracteres.' };
        return res.redirect('/usuarios');
      }

      if (nueva_password !== confirmar_password) {
        req.session.flash = { type: 'error', message: 'Las contraseñas ingresadas no coinciden.' };
        return res.redirect('/usuarios');
      }

      const password_hash = await bcrypt.hash(nueva_password, 10);
      await userRepo.updatePassword(id, password_hash);

      await AuditService.log(req, {
        modulo: 'USUARIOS',
        accion: 'PASSWORD_RESET',
        detalle: `Cambio de contraseña para usuario ID ${id}`
      });

      req.session.flash = { type: 'success', message: 'Contraseña actualizada con éxito.' };
      res.redirect('/usuarios');
    } catch (err: any) {
      console.error('Error cambiando contraseña:', err);
      req.session.flash = { type: 'error', message: err.message || 'Error al cambiar contraseña.' };
      res.redirect('/usuarios');
    }
  }

  static async toggleActivo(req: Request, res: Response) {
    try {
      const id = parseInt(req.params.id, 10);
      if (!id) {
        return res.redirect('/usuarios');
      }

      // Evitar que el admin principal se desactive a sí mismo
      if (req.session.userId === id) {
        req.session.flash = { type: 'error', message: 'No puedes desactivar tu propia cuenta en sesión.' };
        return res.redirect('/usuarios');
      }

      const nuevoEstado = await userRepo.toggleActivo(id);

      await AuditService.log(req, {
        modulo: 'USUARIOS',
        accion: 'TOGGLE_ESTADO',
        detalle: `Estado de cuenta para usuario ID ${id} conmutado a: ${nuevoEstado === 1 ? 'ACTIVO' : 'INACTIVO'}`
      });

      req.session.flash = {
        type: 'success',
        message: nuevoEstado === 1 ? 'Usuario reactivado con éxito.' : 'Usuario deshabilitado exitosamente.'
      };
      res.redirect('/usuarios');
    } catch (err: any) {
      console.error('Error alternando estado de usuario:', err);
      req.session.flash = { type: 'error', message: 'Error al cambiar estado del usuario.' };
      res.redirect('/usuarios');
    }
  }
}
