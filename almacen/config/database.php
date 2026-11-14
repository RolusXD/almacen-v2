<?php
/**
 * Configuración de la base de datos.
 *
 * En local mantiene defaults para PostgreSQL. En Vercel exige variables
 * reales para evitar intentar conectar a localhost, que no existe en serverless.
 */
$databaseUrl = getenv('DATABASE_URL')
    ?: getenv('POSTGRES_URL')
    ?: getenv('POSTGRES_PRISMA_URL')
    ?: getenv('POSTGRES_URL_NON_POOLING')
    ?: getenv('DATABASE_URL_UNPOOLED')
    ?: '';
$databaseParts = $databaseUrl ? parse_url($databaseUrl) : [];
$databasePath = $databaseParts['path'] ?? '';
$databaseNameFromUrl = $databasePath ? ltrim($databasePath, '/') : '';
$databaseQuery = [];
parse_str($databaseParts['query'] ?? '', $databaseQuery);
$isVercel = (getenv('VERCEL') ?: '') === '1';

$dbHost = getenv('DB_HOST') ?: getenv('POSTGRES_HOST') ?: getenv('PGHOST') ?: ($databaseParts['host'] ?? null);
$dbName = getenv('DB_NAME') ?: getenv('POSTGRES_DATABASE') ?: getenv('PGDATABASE') ?: ($databaseNameFromUrl ?: null);
$dbUser = getenv('DB_USER') ?: getenv('POSTGRES_USER') ?: getenv('PGUSER') ?: ($databaseParts['user'] ?? null);
$dbPass = getenv('DB_PASS') ?: getenv('POSTGRES_PASSWORD') ?: getenv('PGPASSWORD') ?: ($databaseParts['pass'] ?? null);
$dbPort = getenv('DB_PORT') ?: getenv('POSTGRES_PORT') ?: getenv('PGPORT') ?: ($databaseParts['port'] ?? null);
$dbSslMode = getenv('DB_SSLMODE') ?: ($databaseQuery['sslmode'] ?? null);
$dbPgOptions = getenv('DB_PG_OPTIONS') ?: getenv('PGOPTIONS') ?: ($databaseQuery['options'] ?? null);

if (!$dbPgOptions && !empty($dbHost) && str_contains($dbHost, '.neon.tech')) {
    $dbPgOptions = 'endpoint=' . explode('.', $dbHost)[0];
}

if (!$isVercel) {
    $dbHost = $dbHost ?: 'localhost';
    $dbName = $dbName ?: 'almacen_toi';
    $dbUser = $dbUser ?: 'postgres';
    $dbPass = $dbPass ?? '';
}

define('DB_DRIVER', getenv('DB_DRIVER') ?: 'pgsql');
define('DB_HOST', $dbHost ?: '');
define('DB_NAME', $dbName ?: '');
define('DB_USER', $dbUser ?: '');
define('DB_PASS', $dbPass ?? '');
define('DB_PORT', $dbPort ?: '5432');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8');
define('DB_SSLMODE', $dbSslMode ?: (getenv('VERCEL') ? 'require' : 'prefer'));
define('DB_PG_OPTIONS', $dbPgOptions ?: '');
