const fs = require('node:fs/promises');
const path = require('node:path');
const { Client } = require('pg');

async function main() {
  const connectionString = process.env.DATABASE_URL_UNPOOLED || process.env.DATABASE_URL;

  if (!connectionString) {
    throw new Error('Defina DATABASE_URL_UNPOOLED o DATABASE_URL antes de ejecutar la migracion.');
  }

  const sqlPath = path.join(__dirname, '..', 'database', 'almacen_toi_postgresql.sql');
  const sql = await fs.readFile(sqlPath, 'utf8');
  const client = new Client({
    connectionString,
    ssl: { rejectUnauthorized: false },
  });

  await client.connect();

  try {
    await client.query(sql);
    console.log('Migracion PostgreSQL aplicada correctamente.');
  } finally {
    await client.end();
  }
}

main().catch((error) => {
  console.error(error.message);
  process.exit(1);
});
