import { app } from './app';
import { ENV } from './config/env';
import { pool } from './db';
import { ConfiguracionRepository } from './repositories/configuracion.repository';
import { setSystemTimeZone } from './utils/date.helper';

const configRepo = new ConfiguracionRepository();

const server = app.listen(ENV.PORT, async () => {
  console.log(`=======================================================`);
  console.log(`🚀 Sistema de Almacén TOI v4.0 (Node.js & TypeScript)`);
  console.log(`🌐 Servidor iniciado en: ${ENV.APP_URL}`);
  console.log(`📡 Puerto de escucha: ${ENV.PORT}`);
  console.log(`⚙️  Entorno: ${ENV.NODE_ENV}`);
  console.log(`=======================================================`);

  try {
    const res = await pool.query('SELECT current_database(), version()');
    console.log(`✅ Base de datos conectada: ${res.rows[0].current_database}`);

    // Cargar y aplicar zona horaria configurada
    const config = await configRepo.getConfiguracion();
    setSystemTimeZone(config.zona_horaria || 'America/La_Paz');
    console.log(`🕒 Zona horaria activa: ${config.zona_horaria || 'America/La_Paz'}`);
  } catch (err: any) {
    console.warn(`⚠️ Advertencia de conexión a BD: ${err.message}`);
    console.warn(`   (Normal durante la construcción de la imagen Docker en Coolify)`);
  }
});

// Manejo de señales de terminación para contenedores Docker en Coolify
process.on('SIGTERM', () => {
  console.log('Recibida señal SIGTERM, apagando servidor de forma segura...');
  server.close(() => {
    pool.end();
    console.log('Servidor y pool de conexiones cerrados.');
    process.exit(0);
  });
});

process.on('SIGINT', () => {
  console.log('Recibida señal SIGINT, saliendo...');
  server.close(() => {
    pool.end();
    process.exit(0);
  });
});
