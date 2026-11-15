import { Request, Response, NextFunction } from 'express';
import { UsuarioRepository } from '../repositories/usuario.repository';
import { Usuario } from '../models/types';

declare module 'express-session' {
  interface SessionData {
    userId?: number;
    userName?: string;
    userRole?: 'admin' | 'usuario';
    flash?: {
      type: 'success' | 'error';
      message: string;
      link?: { url: string; text: string };
    };
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
        req.session.userRole = user.rol;
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

export function requireRole(allowedRoles: Array<'admin' | 'usuario'>) {
  return (req: Request, res: Response, next: NextFunction) => {
    if (!req.session || !req.session.userId) {
      return res.redirect('/auth/login');
    }
    const role = (req.session.userRole || (req.user && req.user.rol)) as 'admin' | 'usuario';
    if (!role || !allowedRoles.includes(role)) {
      if (req.xhr || (req.headers.accept && req.headers.accept.includes('application/json'))) {
        return res.status(403).json({ error: 'Acceso denegado: permisos insuficientes.' });
      }
      req.session.flash = { 
        type: 'error', 
        message: 'Acceso denegado (403): Tu rol de usuario no tiene permisos para realizar esta acción.' 
      };
      return res.status(403).render('error', {
        title: 'Acceso Denegado (403)',
        status: 403,
        message: 'No posees privilegios suficientes para acceder al recurso solicitado.',
        user: req.user || null
      });
    }
    next();
  };
}

export function requireAdmin(req: Request, res: Response, next: NextFunction) {
  return requireRole(['admin'])(req, res, next);
}
