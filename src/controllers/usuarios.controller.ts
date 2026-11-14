import { Request, Response } from 'express';
import bcrypt from 'bcryptjs';
import { UsuarioRepository } from '../repositories/usuario.repository';

const userRepo = new UsuarioRepository();

export class UsuariosController {
  static async index(req: Request, res: Response) {
    try {
      const usuarios = await userRepo.getAll();
      res.render('usuarios/index', { usuarios });
    } catch (err) {
      console.error('Error listando usuarios:', err);
      res.status(500).send('Error interno');
    }
  }

  static async nuevoSubmit(req: Request, res: Response) {
    try {
      const { nombre_completo, usuario, password, carnet, email, rol } = req.body;

      if (!nombre_completo || !usuario || !password || !carnet || !email) {
        req.session.flash = { type: 'error', message: 'Todos los campos son obligatorios.' };
        return res.redirect('/usuarios');
      }

      const existing = await userRepo.findByUsuario(usuario);
      if (existing) {
        req.session.flash = { type: 'error', message: `El nombre de usuario '${usuario}' ya está en uso.` };
        return res.redirect('/usuarios');
      }

      const password_hash = await bcrypt.hash(password, 10);
      await userRepo.create({
        nombre_completo,
        usuario,
        password_hash,
        carnet,
        email,
        rol: rol || 'usuario',
      });

      req.session.flash = { type: 'success', message: 'Usuario creado exitosamente.' };
      res.redirect('/usuarios');
    } catch (err: any) {
      console.error('Error creando usuario:', err);
      req.session.flash = { type: 'error', message: err.message || 'Error al crear usuario' };
      res.redirect('/usuarios');
    }
  }
}
