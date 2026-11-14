<?php
/**
 * Clase de conexión a la base de datos usando PDO
 * Previene SQL Injection mediante prepared statements
 */

// Definir APP_ENV si no existe (para cuando se carga directamente)
if (!defined('APP_ENV')) {
    define('APP_ENV', 'production');
}

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $this->validateConfig();

            $dsn = DB_DRIVER . ":host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;

            if (DB_DRIVER === 'pgsql' && DB_SSLMODE !== '') {
                $dsn .= ";sslmode=" . DB_SSLMODE;
            }

            if (DB_DRIVER === 'pgsql' && DB_PG_OPTIONS !== '') {
                $dsn .= ";options=" . DB_PG_OPTIONS;
            }

            if (DB_DRIVER !== 'pgsql') {
                $dsn .= ";charset=" . DB_CHARSET;
            }

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $this->renderConnectionError($e);
        } catch (RuntimeException $e) {
            $this->renderConfigurationError($e->getMessage());
        }
    }

    private function validateConfig() {
        $missing = [];

        foreach (['DB_HOST', 'DB_NAME', 'DB_USER'] as $constant) {
            if (!defined($constant) || constant($constant) === '') {
                $missing[] = $constant;
            }
        }

        if (!empty($missing)) {
            throw new RuntimeException('Faltan variables de entorno: ' . implode(', ', $missing));
        }

        if (!in_array(DB_DRIVER, PDO::getAvailableDrivers(), true)) {
            throw new RuntimeException('El driver PDO "' . DB_DRIVER . '" no está habilitado en PHP.');
        }
    }

    private function renderConfigurationError($message) {
        http_response_code(500);

        if (APP_ENV === 'development' || (getenv('DB_DEBUG') ?: '') === 'true') {
            die($this->formatErrorPage('Configuración de base de datos incompleta', $message));
        }

        die($this->formatErrorPage(
            'Configuración de base de datos incompleta',
            'Revise DATABASE_URL o las variables DB_HOST, DB_NAME, DB_USER y DB_PASS en Vercel.'
        ));
    }

    private function renderConnectionError(PDOException $e) {
        http_response_code(500);

        $detail = 'No se pudo conectar al servidor PostgreSQL configurado.';

        if (APP_ENV === 'development' || (getenv('DB_DEBUG') ?: '') === 'true') {
            $detail = $e->getMessage();
        }

        die($this->formatErrorPage('Error de conexión a la base de datos', $detail));
    }

    private function formatErrorPage($title, $detail) {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeDetail = htmlspecialchars($detail, ENT_QUOTES, 'UTF-8');
        $safeHost = htmlspecialchars(DB_HOST ?: 'sin configurar', ENT_QUOTES, 'UTF-8');
        $safeName = htmlspecialchars(DB_NAME ?: 'sin configurar', ENT_QUOTES, 'UTF-8');
        $safeUser = htmlspecialchars(DB_USER ?: 'sin configurar', ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$safeTitle}</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #eef4ef; color: #17201c; font-family: system-ui, -apple-system, Segoe UI, sans-serif; }
        main { width: min(720px, calc(100% - 32px)); background: #fff; border: 1px solid #d9e2dc; border-radius: 8px; box-shadow: 0 18px 48px rgba(17,24,21,.14); padding: 28px; }
        h1 { margin: 0 0 10px; font-size: 24px; }
        p { margin: 0 0 16px; color: #66746d; }
        code { background: #f4f7f4; border: 1px solid #d9e2dc; border-radius: 6px; padding: 2px 6px; }
        ul { margin: 14px 0 0; padding-left: 20px; }
        li { margin: 8px 0; }
    </style>
</head>
<body>
    <main>
        <h1>{$safeTitle}</h1>
        <p>{$safeDetail}</p>
        <ul>
            <li>Host configurado: <code>{$safeHost}</code></li>
            <li>Base configurada: <code>{$safeName}</code></li>
            <li>Usuario configurado: <code>{$safeUser}</code></li>
            <li>En Vercel configure <code>DATABASE_URL</code> o las variables de PostgreSQL. No use <code>localhost</code>.</li>
        </ul>
    </main>
</body>
</html>
HTML;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Prevenir clonación
    private function __clone() {}

    // Prevenir deserialización
    public function __wakeup() {
        throw new Exception("No se puede deserializar la clase");
    }
}
