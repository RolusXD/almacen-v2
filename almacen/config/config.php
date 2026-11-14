<?php
/**
 * Configuración general de la aplicación
 */

// Configuración de la base de datos
require_once __DIR__ . '/database.php';

class DatabaseSessionHandler implements SessionHandlerInterface {
    private ?PDO $pdo = null;

    public function open(string $path, string $name): bool {
        try {
            if (DB_HOST === '' || DB_NAME === '' || DB_USER === '') {
                return false;
            }

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

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS app_sessions (
                    id VARCHAR(128) PRIMARY KEY,
                    payload TEXT NOT NULL,
                    last_activity INTEGER NOT NULL
                )
            ");
            $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_app_sessions_last_activity ON app_sessions(last_activity)");

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function close(): bool {
        $this->pdo = null;
        return true;
    }

    public function read(string $id): string|false {
        if (!$this->pdo) {
            return '';
        }

        $stmt = $this->pdo->prepare("SELECT payload FROM app_sessions WHERE id = ?");
        $stmt->execute([$id]);
        $session = $stmt->fetch();

        return $session ? (string)$session['payload'] : '';
    }

    public function write(string $id, string $data): bool {
        if (!$this->pdo) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO app_sessions (id, payload, last_activity)
            VALUES (?, ?, ?)
            ON CONFLICT (id) DO UPDATE
            SET payload = EXCLUDED.payload,
                last_activity = EXCLUDED.last_activity
        ");

        return $stmt->execute([$id, $data, time()]);
    }

    public function destroy(string $id): bool {
        if (!$this->pdo) {
            return false;
        }

        $stmt = $this->pdo->prepare("DELETE FROM app_sessions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function gc(int $max_lifetime): int|false {
        if (!$this->pdo) {
            return false;
        }

        $stmt = $this->pdo->prepare("DELETE FROM app_sessions WHERE last_activity < ?");
        $stmt->execute([time() - $max_lifetime]);

        return $stmt->rowCount();
    }
}

// Configuración de la aplicación
define('APP_NAME', getenv('APP_NAME') ?: 'Sistema de Almacén TOI');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/almacen_moderno');
define('APP_ENV', getenv('APP_ENV') ?: 'production'); // development | production

// Configuración de sesión
define('SESSION_LIFETIME', 3600); // 1 hora

// Configuración de seguridad
define('HASH_COST', 10); // Para password_hash

// Zona horaria
date_default_timezone_set('America/La_Paz');

// Reporte de errores
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Endurecer la cookie de sesión antes de iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    $sessionDriver = getenv('SESSION_DRIVER') ?: '';

    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    if ($sessionDriver === 'database') {
        session_set_save_handler(new DatabaseSessionHandler(), true);
    } else {
        $sessionPath = __DIR__ . '/../storage/sessions';

        if (!is_dir($sessionPath)) {
            @mkdir($sessionPath, 0775, true);
        }

        if (is_dir($sessionPath) && is_writable($sessionPath)) {
            session_save_path($sessionPath);
        }
    }
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
