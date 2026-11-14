import { Request, Response, NextFunction } from 'express';
import { UsuarioRepository } from '../repositories/usuario.repository';
import { Usuario } from '../models/types';

declare module 'express-session' {
  interface SessionData {
    userId?: number;
    userName?: string;
    userRole?: 'admin' | 'usuario';
    flash?: { type: 'success' | 'error'; message: string };
  }
}

declare global {
  namespace Express {
    interface Request {
      user?: Usuario;
    }
  }
}

const userRepo = new UsuarioRepository();

export async function attachUserMiddleware(req: Request, res: Response, next: NextFunction) {
  res.locals.flash = req.session.flash || null;
  delete req.session.flash;

  if (req.session && req.session.userId) {
    try {
      const user = await userRepo.findById(req.session.userId);
      if (user && user.activo === 1) {
        req.user = user;
        res.locals.user = user;
        return next();
      }
    } catch (err) {
      console.error('Error al resolver usuario de sesión:', err);
    }
    // Si el usuario no existe o está inactivo, limpiar sesión
    req.session.destroy(() => {});
  }

  res.locals.user = null;
  next();
}

export function requireAuth(req: Request, res: Response, next: NextFunction) {
  if (!req.session || !req.session.userId) {
    return res.redirect('/auth/login');
  }
  next();
}

export function requireAdmin(req: Request, res: Response, next: NextFunction) {
  if (!req.session || !req.session.userId || req.session.userRole !== 'admin') {
    if (req.session) {
      req.session.flash = { type: 'error', message: 'Acceso restringido a administradores.' };
    }
    return res.redirect('/dashboard');
  }
  next();
}
