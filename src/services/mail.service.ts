import nodemailer from 'nodemailer';
import { SmtpRepository } from '../repositories/smtp.repository';
import { CryptoService } from './crypto.service';
import { UsuarioRepository } from '../repositories/usuario.repository';

const smtpRepo = new SmtpRepository();
const userRepo = new UsuarioRepository();

export class MailService {
  private static async createTransporter() {
    const config = await smtpRepo.getConfig();
    if (!config || !config.activo) {
      throw new Error('El servicio de correo SMTP no está configurado o se encuentra desactivado.');
    }

    const passwordPlano = config.password_cifrado ? CryptoService.decrypt(config.password_cifrado) : '';

    const transporter = nodemailer.createTransport({
      host: config.host,
      port: config.puerto,
      secure: config.secure, // true para puerto 465, false para otros (587, 25)
      auth: config.usuario ? {
        user: config.usuario,
        pass: passwordPlano
      } : undefined,
      tls: {
        rejectUnauthorized: false // Permite certificados autofirmados si fuera necesario
      }
    });

    return { transporter, config };
  }

  /**
   * Valida la conexión y envía un correo de prueba en vivo
   */
  static async testConnection(destinatario: string): Promise<{ success: boolean; message: string }> {
    try {
      const { transporter, config } = await this.createTransporter();

      // Verificar transporte
      await transporter.verify();

      // Enviar correo de prueba
      await transporter.sendMail({
        from: `"${config.remitente_nombre}" <${config.remitente_email}>`,
        to: destinatario,
        subject: 'Prueba de Conexión Exitosa - Sistema de Almacén TOI',
        html: `
          <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
            <div style="background-color: #0d6efd; color: white; padding: 20px; text-align: center;">
              <h2 style="margin: 0;">Almacén TOI</h2>
              <p style="margin: 5px 0 0 0; opacity: 0.85;">Telecomunicaciones Oportunas Inteligentes</p>
            </div>
            <div style="padding: 24px; color: #334155;">
              <h3 style="color: #198754; margin-top: 0;">¡Enlace SMTP Operativo!</h3>
              <p>Este correo confirma que el servidor de correo configurado responde adecuadamente y las credenciales han sido verificadas.</p>
              <div style="background-color: #f8fafc; border-left: 4px solid #0d6efd; padding: 12px; margin: 16px 0; font-size: 13px;">
                <strong>Servidor:</strong> ${config.host}:${config.puerto}<br>
                <strong>Remitente:</strong> ${config.remitente_email}<br>
                <strong>Fecha:</strong> ${new Date().toLocaleString('es-BO')}
              </div>
              <p style="font-size: 12px; color: #64748b; margin-top: 24px;">Mensaje automático del Sistema de Gestión de Almacén.</p>
            </div>
          </div>
        `
      });

      return { success: true, message: `Correo de prueba enviado con éxito a ${destinatario}.` };
    } catch (err: any) {
      console.error('Error al probar conexión SMTP:', err);
      return { success: false, message: `Error en servidor SMTP: ${err.message || 'Fallo de conexión o autenticación'}` };
    }
  }

  /**
   * Envía enlace seguro de recuperación de contraseña (Token de 15 minutos)
   */
  static async sendPasswordReset(destinatario: string, nombreCompleto: string, resetUrl: string): Promise<boolean> {
    try {
      const { transporter, config } = await this.createTransporter();

      await transporter.sendMail({
        from: `"${config.remitente_nombre}" <${config.remitente_email}>`,
        to: destinatario,
        subject: 'Recuperación de Contraseña - Almacén TOI',
        html: `
          <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
            <div style="background-color: #1e293b; color: white; padding: 20px; text-align: center;">
              <h2 style="margin: 0;">Almacén TOI</h2>
              <p style="margin: 5px 0 0 0; color: #94a3b8;">Restablecimiento de Credenciales de Acceso</p>
            </div>
            <div style="padding: 24px; color: #334155;">
              <p>Hola <strong>${nombreCompleto}</strong>,</p>
              <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta de acceso al Sistema de Almacén TOI.</p>
              <div style="text-align: center; margin: 30px 0;">
                <a href="${resetUrl}" style="background-color: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
                  Restablecer Contraseña
                </a>
              </div>
              <p style="font-size: 13px; color: #64748b;">
                Este enlace expirará automáticamente en <strong>15 minutos</strong> y solo puede ser utilizado una vez.
              </p>
              <p style="font-size: 12px; color: #94a3b8; margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 12px;">
                Si tú no solicitaste este cambio, puedes ignorar este mensaje; tu contraseña actual continuará siendo segura.
              </p>
            </div>
          </div>
        `
      });

      return true;
    } catch (err: any) {
      console.error('Error al enviar correo de recuperación:', err.message);
      return false;
    }
  }

  /**
   * Despacha alerta automática por stock crítico cuando un artículo cae por debajo del mínimo
   */
  static async sendStockAlert(itemData: {
    nombre: string;
    codigo?: string;
    stockActual: number;
    stockMinimo: number;
    tipo: 'EQUIPO' | 'ACCESORIO';
    destino?: string;
    encargado?: string;
  }): Promise<boolean> {
    try {
      const { transporter, config } = await this.createTransporter();

      // Obtener correos de administradores
      const admins = await userRepo.getAll();
      const adminEmails = admins.filter(u => u.rol === 'admin' && u.email && u.activo === 1).map(u => u.email);

      if (adminEmails.length === 0) {
        return false;
      }

      await transporter.sendMail({
        from: `"${config.remitente_nombre}" <${config.remitente_email}>`,
        to: adminEmails.join(', '),
        subject: `⚠️ ALERTA DE STOCK CRÍTICO: ${itemData.nombre} (${itemData.stockActual} unidades)`,
        html: `
          <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #fecaca; border-radius: 8px; overflow: hidden;">
            <div style="background-color: #dc2626; color: white; padding: 18px; text-align: center;">
              <h2 style="margin: 0; font-size: 1.25rem;">⚠️ Alerta Automática de Inventario</h2>
              <p style="margin: 4px 0 0 0; font-size: 13px; opacity: 0.9;">Existencias por debajo del umbral mínimo</p>
            </div>
            <div style="padding: 20px; color: #334155;">
              <h3 style="color: #991b1b; margin-top: 0;">Artículo en Nivel Crítico</h3>
              <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 16px;">
                <tr>
                  <td style="padding: 6px 0; color: #64748b;"><strong>Ítem:</strong></td>
                  <td style="padding: 6px 0;"><strong>${itemData.nombre}</strong> ${itemData.codigo ? `(${itemData.codigo})` : ''}</td>
                </tr>
                <tr>
                  <td style="padding: 6px 0; color: #64748b;"><strong>Tipo:</strong></td>
                  <td style="padding: 6px 0;">${itemData.tipo}</td>
                </tr>
                <tr>
                  <td style="padding: 6px 0; color: #64748b;"><strong>Stock Actual:</strong></td>
                  <td style="padding: 6px 0; color: #dc2626; font-weight: bold; font-size: 16px;">${itemData.stockActual}</td>
                </tr>
                <tr>
                  <td style="padding: 6px 0; color: #64748b;"><strong>Umbral Mínimo:</strong></td>
                  <td style="padding: 6px 0;">${itemData.stockMinimo}</td>
                </tr>
                ${itemData.destino ? `
                <tr>
                  <td style="padding: 6px 0; color: #64748b;"><strong>Último Destino:</strong></td>
                  <td style="padding: 6px 0;">${itemData.destino}</td>
                </tr>` : ''}
                ${itemData.encargado ? `
                <tr>
                  <td style="padding: 6px 0; color: #64748b;"><strong>Retirado por:</strong></td>
                  <td style="padding: 6px 0;">${itemData.encargado}</td>
                </tr>` : ''}
              </table>
              <p style="font-size: 12px; color: #64748b; margin-top: 20px;">
                Se recomienda emitir una solicitud de reposición al proveedor o realizar una transferencia interna.
              </p>
            </div>
          </div>
        `
      });

      return true;
    } catch (err: any) {
      console.warn('Aviso: No se pudo despachar la alerta de stock por correo:', err.message);
      return false;
    }
  }

  /**
   * Envía reporte o comprobante con archivo adjunto (PDF / Excel)
   */
  static async sendReportWithAttachment(options: {
    destinatario: string;
    asunto: string;
    mensaje: string;
    archivo: {
      nombre: string;
      contenido: Buffer;
      contentType: string;
    };
  }): Promise<{ success: boolean; message: string }> {
    try {
      const { transporter, config } = await this.createTransporter();

      await transporter.sendMail({
        from: `"${config.remitente_nombre}" <${config.remitente_email}>`,
        to: options.destinatario,
        subject: options.asunto,
        text: options.mensaje,
        html: `
          <div style="font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;">
            <h3 style="color: #0d6efd; margin-top: 0;">Almacén TOI - Documento Adjunto</h3>
            <p>${options.mensaje}</p>
            <p style="font-size: 13px; color: #64748b;">Se adjunta el archivo solicitado: <strong>${options.archivo.nombre}</strong></p>
          </div>
        `,
        attachments: [
          {
            filename: options.archivo.nombre,
            content: options.archivo.contenido,
            contentType: options.archivo.contentType
          }
        ]
      });

      return { success: true, message: `Reporte enviado exitosamente a ${options.destinatario}` };
    } catch (err: any) {
      console.error('Error al enviar reporte adjunto por correo:', err);
      return { success: false, message: `Fallo al enviar correo: ${err.message}` };
    }
  }
}
