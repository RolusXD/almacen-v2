import rateLimit from 'express-rate-limit';

// Limitador para autenticación y recuperación de credenciales
// Ajustado a 15 intentos cada 15 minutos para tolerar múltiples operadores compartiendo la misma IP de oficina
export const authLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutos
  max: 15, // Máximo 15 intentos por ventana
  standardHeaders: true,
  legacyHeaders: false,
  message: (req: any, res: any) => {
    res.status(429).render('error', {
      title: 'Demasiados Intentos (429)',
      status: 429,
      message: 'Has excedido el número máximo de intentos permitidos (15 intentos). Por seguridad, espera 15 minutos antes de volver a intentar.',
      user: null
    });
  }
});

// Limitador para peticiones de envío de correos (test SMTP, reseteo)
export const mailLimiter = rateLimit({
  windowMs: 60 * 1000, // 1 minuto
  max: 5, // Máximo 5 envíos por minuto
  standardHeaders: true,
  legacyHeaders: false,
  message: { error: 'Límite de envíos alcanzado. Por favor espera un minuto.' }
});
