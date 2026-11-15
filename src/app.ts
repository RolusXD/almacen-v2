import express from 'express';
import path from 'path';
import fs from 'fs';
import session from 'express-session';
import connectPgSimple from 'connect-pg-simple';
import helmet from 'helmet';
import cookieParser from 'cookie-parser';
import { ENV } from './config/env';
import { pool } from './db';
import { attachUserMiddleware, requireAuth, requireAdmin, requireRole } from './middlewares/auth.middleware';
import { authLimiter, mailLimiter } from './middlewares/rate-limit.middleware';

// Controladores
import { AuthController } from './controllers/auth.controller';
import { DashboardController } from './controllers/dashboard.controller';
import { EquiposController } from './controllers/equipos.controller';
import { AccesoriosController } from './controllers/accesorios.controller';
import { MovimientosController } from './controllers/movimientos.controller';
import { MaestrosController } from './controllers/maestros.controller';
import { UsuariosController } from './controllers/usuarios.controller';
import { ReportesController } from './controllers/reportes.controller';
import { ConfigController } from './controllers/config.controller';
import { DespachoController } from './controllers/despacho.controller';

export const app = express();

// Habilitar 'trust proxy' para despliegues detrás de reverse proxies (Coolify, Traefik, Nginx)
// Permite leer X-Forwarded-Proto para HTTPS, cookies seguras, express-rate-limit e IP real de auditoría
app.set('trust proxy', 1);

// Configuración de vistas EJS y archivos estáticos
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));
app.use(express.static(path.join(__dirname, 'public')));

// Servir librerías oficiales de AdminLTE 4, Bootstrap 5 y Bootstrap Icons
const nodeModulesPath = fs.existsSync(path.resolve(__dirname, '..', 'node_modules'))
  ? path.resolve(__dirname, '..', 'node_modules')
  : path.resolve(process.cwd(), 'node_modules');
app.use('/adminlte', express.static(path.join(nodeModulesPath, 'admin-lte', 'dist')));
app.use('/bootstrap', express.static(path.join(nodeModulesPath, 'bootstrap', 'dist')));
app.use('/bootstrap-icons', express.static(path.join(nodeModulesPath, 'bootstrap-icons', 'font')));

// Seguridad HTTP con Helmet (permitiendo CDNs para Bootstrap e iconos)
app.use(
  helmet({
    contentSecurityPolicy: false, // Permite CDNs y estilos en línea de forma limpia
  })
);

// Parsers
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(cookieParser());

// Manejador de sesiones con almacenamiento persistente en PostgreSQL
const PgSession = connectPgSimple(session);

app.use(
  session({
    store: new PgSession({
      pool: pool,
      tableName: 'user_sessions',
      createTableIfMissing: true,
    }),
    secret: ENV.SESSION_SECRET,
    resave: false,
    saveUninitialized: false,
    cookie: {
      maxAge: 30 * 24 * 60 * 60 * 1000, // 30 días
      httpOnly: true,
      sameSite: 'lax',
      secure: ENV.NODE_ENV === 'production' && ENV.APP_URL.startsWith('https'),
    },
  })
);

// Middleware de usuario en sesión
app.use(attachUserMiddleware);

// ==========================================
// RUTAS PÚBLICAS Y DE AUTENTICACIÓN
// ==========================================
app.get('/health', async (req, res) => {
  try {
    await pool.query('SELECT 1');
    res.json({ status: 'ok', time: new Date().toISOString() });
  } catch (err: any) {
    res.status(500).json({ status: 'error', error: err.message });
  }
});

app.get('/auth/login', AuthController.showLogin);
app.post('/auth/login', authLimiter, AuthController.handleLogin);
app.get('/auth/logout', AuthController.handleLogout);

// Recuperación de Contraseña (Flujo Seguro con Rate Limiting)
app.get('/auth/forgot-password', AuthController.showForgotPassword);
app.post('/auth/forgot-password', authLimiter, AuthController.handleForgotPassword);
app.get('/auth/reset-password', AuthController.showResetPassword);
app.post('/auth/reset-password', authLimiter, AuthController.handleResetPassword);

// Redirección raíz
app.get('/', (req, res) => {
  res.redirect('/dashboard');
});

// ==========================================
// RUTAS PROTEGIDAS (REQUIEREN LOGIN)
// ==========================================
app.use(requireAuth);

// Dashboard
app.get('/dashboard', DashboardController.index);

// Equipos
app.get('/equipos', EquiposController.index);
app.get('/equipos/nuevo', EquiposController.nuevoForm);
app.post('/equipos/nuevo', EquiposController.nuevoSubmit);
app.get('/equipos/editar/:id', EquiposController.editarForm);
app.post('/equipos/editar/:id', EquiposController.editarSubmit);
app.get('/equipos/eliminar/:id', requireAdmin, EquiposController.eliminar);
app.get('/api/equipos/buscar', EquiposController.apiBuscar);

// Accesorios
app.get('/accesorios', AccesoriosController.index);
app.get('/accesorios/nuevo', AccesoriosController.nuevoForm);
app.post('/accesorios/nuevo', AccesoriosController.nuevoSubmit);
app.get('/accesorios/editar/:id', AccesoriosController.editarForm);
app.post('/accesorios/editar/:id', AccesoriosController.editarSubmit);
app.get('/accesorios/eliminar/:id', requireAdmin, AccesoriosController.eliminar);

// Movimientos
app.get('/movimientos/equipos', MovimientosController.equiposIndex);
app.post('/movimientos/equipos', MovimientosController.equiposSubmit);
app.get('/movimientos/accesorios', MovimientosController.accesoriosIndex);
app.post('/movimientos/accesorios', MovimientosController.accesoriosSubmit);
app.get('/movimientos/comprobante/:id', ReportesController.descargarComprobante);

// Despacho General y Operaciones Unificadas
app.get('/despachos', DespachoController.index);
app.get('/despachos/nuevo', DespachoController.nuevoForm);
app.post('/despachos/confirmar', DespachoController.confirmarSubmit);
app.get('/despachos/comprobante/:id', DespachoController.descargarComprobante);
app.get('/api/despachos/:id/detalle', DespachoController.apiDetalle);
app.post('/despachos/anular/:id', requireAdmin, DespachoController.anular);

// Maestros: Tipos de Equipos
app.get('/maestros/tipos', MaestrosController.tiposIndex);
app.post('/maestros/tipos', MaestrosController.tiposSubmit);
app.get('/maestros/tipos/eliminar/:id', requireAdmin, MaestrosController.tiposEliminar);

// Maestros: Proveedores
app.get('/maestros/proveedores', MaestrosController.proveedoresIndex);
app.post('/maestros/proveedores', MaestrosController.proveedoresSubmit);
app.get('/maestros/proveedores/eliminar/:id', requireAdmin, MaestrosController.proveedoresEliminar);

// Maestros: Categorías
app.get('/maestros/categorias', MaestrosController.categoriasIndex);
app.post('/maestros/categorias', MaestrosController.categoriasSubmit);
app.get('/maestros/categorias/eliminar/:id', requireAdmin, MaestrosController.categoriasEliminar);

// Maestros: Marcas
app.get('/maestros/marcas', MaestrosController.marcasIndex);
app.post('/maestros/marcas', MaestrosController.marcasSubmit);
app.get('/maestros/marcas/eliminar/:id', requireAdmin, MaestrosController.marcasEliminar);

// Maestros: Mantenimientos y Préstamos
app.get('/maestros/mantenimientos', MaestrosController.mantenimientosIndex);
app.post('/maestros/mantenimientos', MaestrosController.mantenimientosSubmit);
app.post('/maestros/mantenimientos/completar/:id', MaestrosController.mantenimientosCompletar);
app.get('/maestros/prestamos', MaestrosController.prestamosIndex);
app.post('/maestros/prestamos', MaestrosController.prestamosSubmit);
app.post('/maestros/prestamos/devolver/:id', MaestrosController.prestamosDevolver);

// Centro de Reportes y Exportación
app.get('/reportes', ReportesController.index);
app.get('/reportes/exportar/excel', ReportesController.exportarExcel);
app.post('/reportes/enviar-correo', mailLimiter, ReportesController.enviarCorreoReporte);
app.get('/reportes/exportar/equipos', ReportesController.exportarEquipos);
app.get('/reportes/exportar/accesorios', ReportesController.exportarAccesorios);
app.get('/reportes/exportar/movimientos-equipos', ReportesController.exportarMovimientosEquipos);
app.get('/reportes/exportar/movimientos-accesorios', ReportesController.exportarMovimientosAccesorios);

// Usuarios (Solo Administradores)
app.get('/usuarios', requireAdmin, UsuariosController.index);
app.post('/usuarios/nuevo', requireAdmin, UsuariosController.nuevoSubmit);
app.post('/usuarios/editar/:id', requireAdmin, UsuariosController.editarSubmit);
app.post('/usuarios/password/:id', requireAdmin, UsuariosController.cambiarPasswordSubmit);
app.post('/usuarios/toggle/:id', requireAdmin, UsuariosController.toggleActivo);

// Configuración SMTP (Solo Administradores)
app.get('/configuracion/smtp', requireAdmin, ConfigController.smtpIndex);
app.post('/configuracion/smtp', requireAdmin, ConfigController.smtpSubmit);
app.post('/configuracion/smtp/test', requireAdmin, mailLimiter, ConfigController.smtpTest);

// Administración General y Auditoría (Solo Administradores)
app.get('/administracion/configuracion', requireAdmin, ConfigController.generalIndex);
app.post('/administracion/configuracion', requireAdmin, ConfigController.generalSubmit);
app.get('/administracion/logs', requireAdmin, ConfigController.logsIndex);
app.get('/administracion/logs/exportar/excel', requireAdmin, ConfigController.logsExportarExcel);

// Manejo 404
app.use((req, res) => {
  res.status(404).render('error', {
    title: 'Recurso no encontrado (404)',
    status: 404,
    message: `La ruta '${req.originalUrl}' no existe o fue movida en la reestructuración del sistema.`,
    user: (req as any).user || null,
  });
});

// Middleware Centralizado de Manejo de Errores (500)
app.use((err: any, req: express.Request, res: express.Response, next: express.NextFunction) => {
  console.error('Error no controlado en la aplicación:', err);
  const status = err.status || 500;
  res.status(status).render('error', {
    title: `Error del Servidor (${status})`,
    status,
    message: ENV.NODE_ENV === 'production' 
      ? 'Ocurrió un inconveniente temporal procesando los datos. Por favor reintente o contacte a soporte.'
      : (err.message || 'Error interno del servidor'),
    user: (req as any).user || null,
  });
});
