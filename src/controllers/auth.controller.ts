import { Request, Response } from 'express';
import { UsuarioRepository } from '../repositories/usuario.repository';

const userRepo = new UsuarioRepository();

export class AuthController {
  static showLogin(req: Request, res: Response) {
    if (req.session && req.session.userId) {
      return res.redirect('/dashboard');
    }
    res.render('auth/login', { error: null });
  }

  static async handleLogin(req: Request, res: Response) {
    const { usuario, password } = req.body;

    if (!usuario || !password) {
      return res.render('auth/login', { error: 'Por favor complete usuario y contraseña.' });
    }

    try {
      const user = await userRepo.verifyCredentials(usuario, password);
      if (!user) {
        return res.render('auth/login', { error: 'Usuario o contraseña incorrectos.' });
      }

      req.session.userId = user.id_usuario;
      req.session.userName = user.nombre_completo;
      req.session.userRole = user.rol;
      req.session.flash = { type: 'success', message: `Bienvenido al sistema, ${user.nombre_completo}` };

      req.session.save((err) => {
        if (err) {
          console.error('Error guardando sesión:', err);
        }
        res.redirect('/dashboard');
      });
    } catch (err: any) {
      console.error('Error en autenticación:', err);
      res.render('auth/login', { error: 'Error interno al procesar el inicio de sesión.' });
    }
  }

  static handleLogout(req: Request, res: Response) {
    req.session.destroy(() => {
      res.redirect('/auth/login');
    });
  }
}
