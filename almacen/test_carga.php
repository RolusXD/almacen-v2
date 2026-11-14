<?php
/**
 * PRUEBA DE CARGA DEL SISTEMA
 * Ejecute este archivo para verificar que todo cargue correctamente
 */

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Prueba de Carga</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5}";
echo ".ok{color:green}.error{color:red}.info{color:blue}</style></head><body>";
echo "<h1>🧪 Prueba de Carga del Sistema</h1>";

// Paso 1: Cargar database.php
echo "<h2>Paso 1: Cargando database.php...</h2>";
if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
    echo "<p class='ok'>✅ database.php cargado</p>";
    echo "<p class='info'>DB_HOST: " . DB_HOST . "</p>";
    echo "<p class='info'>DB_NAME: " . DB_NAME . "</p>";
    echo "<p class='info'>DB_USER: " . DB_USER . "</p>";
} else {
    echo "<p class='error'>❌ database.php no encontrado</p>";
    exit;
}

// Paso 2: Cargar databasepdo.php
echo "<h2>Paso 2: Cargando databasepdo.php...</h2>";
if (file_exists(__DIR__ . '/config/databasepdo.php')) {
    require_once __DIR__ . '/config/databasepdo.php';
    echo "<p class='ok'>✅ databasepdo.php cargado</p>";
    echo "<p class='info'>Clase Database existe: " . (class_exists('Database') ? 'Sí' : 'No') . "</p>";
} else {
    echo "<p class='error'>❌ databasepdo.php no encontrado</p>";
    exit;
}

// Paso 3: Cargar Model.php
echo "<h2>Paso 3: Cargando core/Model.php...</h2>";
if (file_exists(__DIR__ . '/core/Model.php')) {
    require_once __DIR__ . '/core/Model.php';
    echo "<p class='ok'>✅ Model.php cargado</p>";
    echo "<p class='info'>Clase Model existe: " . (class_exists('Model') ? 'Sí' : 'No') . "</p>";
} else {
    echo "<p class='error'>❌ core/Model.php no encontrado</p>";
    exit;
}

// Paso 4: Cargar Controller.php
echo "<h2>Paso 4: Cargando core/Controller.php...</h2>";
if (file_exists(__DIR__ . '/core/Controller.php')) {
    require_once __DIR__ . '/core/Controller.php';
    echo "<p class='ok'>✅ Controller.php cargado</p>";
    echo "<p class='info'>Clase Controller existe: " . (class_exists('Controller') ? 'Sí' : 'No') . "</p>";
} else {
    echo "<p class='error'>❌ core/Controller.php no encontrado</p>";
    exit;
}

// Paso 5: Cargar AuthController
echo "<h2>Paso 5: Cargando controllers/AuthController.php...</h2>";
if (file_exists(__DIR__ . '/controllers/AuthController.php')) {
    require_once __DIR__ . '/controllers/AuthController.php';
    echo "<p class='ok'>✅ AuthController.php cargado</p>";
    echo "<p class='info'>Clase AuthController existe: " . (class_exists('AuthController') ? 'Sí' : 'No') . "</p>";
    echo "<p class='info'>Método login existe: " . (method_exists('AuthController', 'login') ? 'Sí' : 'No') . "</p>";
    echo "<p class='info'>Método authenticate existe: " . (method_exists('AuthController', 'authenticate') ? 'Sí' : 'No') . "</p>";
    echo "<p class='info'>Método logout existe: " . (method_exists('AuthController', 'logout') ? 'Sí' : 'No') . "</p>";
} else {
    echo "<p class='error'>❌ controllers/AuthController.php no encontrado</p>";
    exit;
}

// Paso 6: Probar conexión a BD
echo "<h2>Paso 6: Probando conexión a la base de datos...</h2>";
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "<p class='ok'>✅ Conexión a BD exitosa</p>";
    
    // Verificar tabla usuarios
    $stmt = $conn->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='ok'>✅ Tabla 'usuarios' existe</p>";
        
        // Verificar usuario admin
        $stmt = $conn->query("SELECT usuario, activo FROM usuarios WHERE usuario = 'admin'");
        if ($stmt->rowCount() > 0) {
            $admin = $stmt->fetch();
            echo "<p class='ok'>✅ Usuario 'admin' existe (activo: " . ($admin['activo'] ? 'Sí' : 'No') . ")</p>";
        } else {
            echo "<p class='error'>❌ Usuario 'admin' NO existe en la BD</p>";
            echo "<p class='info'>Ejecute: database/insertar_admin.sql</p>";
        }
    } else {
        echo "<p class='error'>❌ Tabla 'usuarios' NO existe</p>";
        echo "<p class='info'>Importe: database/schema.sql</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Paso 7: Verificar sesiones
echo "<h2>Paso 7: Verificando sesiones...</h2>";

// Intentar iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Configurar sesión
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p class='ok'>✅ Sesiones de PHP están activas</p>";
    echo "<p class='info'>ID de sesión: " . session_id() . "</p>";
} else {
    echo "<p class='error'>❌ Sesiones de PHP NO están activas</p>";
    echo "<div class='info'>";
    echo "<h4>Posibles causas:</h4>";
    echo "<ul>";
    echo "<li>Sesiones deshabilitadas en php.ini</li>";
    echo "<li>Permisos incorrectos en la carpeta de sesiones</li>";
    echo "<li>Output enviado antes de session_start()</li>";
    echo "</ul>";
    echo "<h4>Solución:</h4>";
    echo "<ol>";
    echo "<li>Abra <code>C:\\xampp\\php\\php.ini</code></li>";
    echo "<li>Busque la línea: <code>session.save_path</code></li>";
    echo "<li>Asegúrese de que apunte a una carpeta existente (ej: <code>C:\\xampp\\tmp</code>)</li>";
    echo "<li>Busque: <code>session.use_cookies</code> y asegúrese que esté en <code>1</code></li>";
    echo "<li>Reinicie Apache en XAMPP</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<hr>";
echo "<h2>✅ Prueba Completada</h2>";
echo "<p>Si todos los pasos fueron exitosos, el sistema debería funcionar correctamente.</p>";
echo "<p><a href='index.php?controller=auth&action=login' class='button'>Ir al Login</a></p>";

echo "</body></html>";
