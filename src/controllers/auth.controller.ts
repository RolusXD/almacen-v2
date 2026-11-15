import { Request, Response } from 'express';
import crypto from 'crypto';
import bcrypt from 'bcryptjs';
import { UsuarioRepository } from '../repositories/usuario.repository';
import { MailService } from '../services/mail.service';
import { AuditService } from '../services/audit.service';
import { ENV } from '../config/env';

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
        await AuditService.log(req, {
          modulo: 'AUTENTICACION',
          accion: 'LOGIN_FAILED',
          detalle: `Credenciales inválidas para usuario '${usuario}'`
        });
        return res.render('auth/login', { error: 'Usuario o contraseña incorrectos.' });
      }

      if (user.activo !== 1) {
        await AuditService.log(req, {
          modulo: 'AUTENTICACION',
          accion: 'LOGIN_FAILED',
          detalle: `Intento de acceso con cuenta desactivada '${usuario}'`,
          id_usuario: user.id_usuario,
          nombre_usuario: user.nombre_completo,
          rol_usuario: user.rol
        });
        return res.render('auth/login', { error: 'Esta cuenta ha sido desactivada. Comuníquese con el administrador.' });
      }

      await AuditService.log(req, {
        modulo: 'AUTENTICACION',
        accion: 'LOGIN_SUCCESS',
        detalle: `Inicio de sesión exitoso`,
        id_usuario: user.id_usuario,
        nombre_usuario: user.nombre_completo,
        rol_usuario: user.rol
      });

      // Regenerar ID de sesión para prevenir Session Fixation (OWASP)
      req.session.regenerate((err) => {
        if (err) {
          console.error('Error regenerando sesión:', err);
        }
        req.session.userId = user.id_usuario;
        req.session.userName = user.nombre_completo;
        req.session.userRole = user.rol;
        req.session.flash = { type: 'success', message: `Bienvenido al sistema, ${user.nombre_completo}` };

        req.session.save((saveErr) => {
          if (saveErr) {
            console.error('Error guardando sesión:', saveErr);
          }
          res.redirect('/dashboard');
        });
      });
    } catch (err: any) {
      console.error('Error en autenticación:', err);
      res.render('auth/login', { error: 'Error interno al procesar el inicio de sesión.' });
    }
  }

  static async handleLogout(req: Request, res: Response) {
    if (req.session?.userId) {
      await AuditService.log(req, {
        modulo: 'AUTENTICACION',
        accion: 'LOGOUT',
        detalle: 'Cierre de sesión de usuario'
      });
    }
    req.session.destroy(() => {
      res.redirect('/auth/login');
    });
  }

  // ==========================================
  // RECUPERACIÓN DE CONTRASEÑA
  // ==========================================
  static showForgotPassword(req: Request, res: Response) {
    res.render('auth/forgot-password', { error: null, success: null });
  }

  static async handleForgotPassword(req: Request, res: Response) {
    const { identificador } = req.body;
    if (!identificador || identificador.trim() === '') {
      return res.render('auth/forgot-password', {
        error: 'Por favor ingresa tu nombre de usuario o correo electrónico.',
        success: null
      });
    }

    try {
      const user = await userRepo.findByUsuarioOrEmail(identificador.trim());
      
      // Si el usuario existe, generar token seguro
      if (user && user.email) {
        const rawToken = crypto.randomBytes(32).toString('hex');
        const hashedToken = crypto.createHash('sha256').update(rawToken).digest('hex');
        const expires = new Date(Date.now() + 15 * 60 * 1000); // 15 minutos exactos

        await userRepo.setResetToken(user.id_usuario, hashedToken, expires);

        const resetUrl = `${ENV.APP_URL}/auth/reset-password?token=${rawToken}`;
        await MailService.sendPasswordReset(user.email, user.nombre_completo, resetUrl);
      }

      // Respuesta neutral para prevenir enumeración de usuarios (OWASP)
      res.render('auth/forgot-password', {
        error: null,
        success: 'Si los datos corresponden a un usuario registrado, se ha enviado un enlace de recuperación a su correo electrónico. El enlace expira en 15 minutos.'
      });
    } catch (err: any) {
      console.error('Error en recuperación de contraseña:', err);
      res.render('auth/forgot-password', {
        error: 'Ocurrió un error al procesar la solicitud. Por favor intente más tarde.',
        success: null
      });
    }
  }

  static async showResetPassword(req: Request, res: Response) {
    const { token } = req.query;
    if (!token || typeof token !== 'string') {
      req.session.flash = { type: 'error', message: 'Enlace de recuperación inválido o inexistente.' };
      return res.redirect('/auth/login');
    }

    const hashedToken = crypto.createHash('sha256').update(token).digest('hex');
    const user = await userRepo.findByResetToken(hashedToken);

    if (!user) {
      req.session.flash = { type: 'error', message: 'El enlace de recuperación es inválido o ha expirado (15 minutos).' };
      return res.redirect('/auth/login');
    }

    res.render('auth/reset-password', { token, error: null, usuario: user.usuario });
  }

  static async handleResetPassword(req: Request, res: Response) {
    const { token, password, confirmar_password } = req.body;

    if (!token || !password || !confirmar_password) {
      return res.render('auth/reset-password', {
        token,
        error: 'Todos los campos son obligatorios.',
        usuario: ''
      });
    }

    if (password.length < 6) {
      return res.render('auth/reset-password', {
        token,
        error: 'La nueva contraseña debe tener al menos 6 caracteres.',
        usuario: ''
      });
    }

    if (password !== confirmar_password) {
      return res.render('auth/reset-password', {
        token,
        error: 'Las contraseñas no coinciden.',
        usuario: ''
      });
    }

    try {
      const hashedToken = crypto.createHash('sha256').update(token).digest('hex');
      const user = await userRepo.findByResetToken(hashedToken);

      if (!user) {
        req.session.flash = { type: 'error', message: 'El enlace de recuperación ha expirado o ya fue utilizado.' };
        return res.redirect('/auth/login');
      }

      const password_hash = await bcrypt.hash(password, 10);
      await userRepo.updatePassword(user.id_usuario, password_hash);

      req.session.flash = { type: 'success', message: 'Tu contraseña ha sido restablecida exitosamente. Ya puedes iniciar sesión.' };
      res.redirect('/auth/login');
    } catch (err: any) {
      console.error('Error al restablecer contraseña:', err);
      res.render('auth/reset-password', {
        token,
        error: 'Error al actualizar la contraseña.',
        usuario: ''
      });
    }
  }
}
