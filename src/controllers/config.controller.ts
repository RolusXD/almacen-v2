import { Request, Response } from 'express';
import { SmtpRepository } from '../repositories/smtp.repository';
import { MailService } from '../services/mail.service';

const smtpRepo = new SmtpRepository();

export class ConfigController {
  static async smtpIndex(req: Request, res: Response) {
    try {
      const config = await smtpRepo.getConfig();
      res.render('configuracion/smtp', { config, activeNav: 'config-smtp' });
    } catch (err: any) {
      console.error('Error cargando configuración SMTP:', err);
      req.session.flash = { type: 'error', message: 'Error cargando la configuración SMTP.' };
      res.redirect('/dashboard');
    }
  }

  static async smtpSubmit(req: Request, res: Response) {
    try {
      const { host, puerto, usuario, password, secure, remitente_email, remitente_nombre, activo } = req.body;

      if (!host || !puerto || !remitente_email) {
        req.session.flash = { type: 'error', message: 'Host, Puerto y Correo Remitente son obligatorios.' };
        return res.redirect('/configuracion/smtp');
      }

      await smtpRepo.saveConfig({
        host: host.trim(),
        puerto: parseInt(puerto, 10) || 587,
        usuario: usuario ? usuario.trim() : '',
        password: password ? password : '',
        secure: secure === 'true' || secure === '1' || secure === 'on',
        remitente_email: remitente_email.trim(),
        remitente_nombre: remitente_nombre ? remitente_nombre.trim() : 'Almacén TOI',
        activo: activo === 'true' || activo === '1' || activo === 'on'
      });

      req.session.flash = { type: 'success', message: 'Configuración SMTP guardada exitosamente.' };
      res.redirect('/configuracion/smtp');
    } catch (err: any) {
      console.error('Error guardando configuración SMTP:', err);
      req.session.flash = { type: 'error', message: err.message || 'Error al guardar la configuración SMTP.' };
      res.redirect('/configuracion/smtp');
    }
  }

  static async smtpTest(req: Request, res: Response) {
    try {
      const { email_destino } = req.body;
      if (!email_destino || email_destino.trim() === '') {
        return res.status(400).json({ success: false, message: 'Debe ingresar un correo destinatario.' });
      }

      const result = await MailService.testConnection(email_destino.trim());
      if (result.success) {
        return res.json({ success: true, message: result.message });
      } else {
        return res.status(400).json({ success: false, message: result.message });
      }
    } catch (err: any) {
      console.error('Error en prueba SMTP:', err);
      return res.status(500).json({ success: false, message: err.message || 'Error al procesar la prueba.' });
    }
  }
}
