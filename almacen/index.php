<?php
/**
 * Punto de entrada principal de la aplicación
 * Enrutador básico MVC
 */

// ============================================
// 1. CONFIGURACIÓN GENERAL (INCLUYE BD Y SESIONES)
// ============================================
require_once __DIR__ . '/config/config.php';

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_is_valid($token) {
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_forbidden() {
    http_response_code(403);
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'La solicitud expiró o no es válida. Intente nuevamente.'
    ];
    header('Location: index.php?controller=dashboard');
    exit;
}

// ============================================
// 3. CARGAR CLASES BASE (EN ORDEN)
// ============================================
require_once __DIR__ . '/config/databasepdo.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/Controller.php';

// ============================================
// 4. AUTOLOAD PARA MODELOS Y CONTROLADORES
// ============================================
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/models/',
        __DIR__ . '/controllers/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});

// ============================================
// 5. ENRUTADOR
// ============================================

// Obtener controller y action de la URL
$controller = isset($_GET['controller']) ? trim($_GET['controller']) : '';
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

// Si no hay controller, mostrar login por defecto
if (empty($controller)) {
    $controller = 'auth';
    $action = 'login';
}

// Mapeo de controladores
$controllerMap = [
    'auth' => 'AuthController',
    'dashboard' => 'DashboardController',
    'equipos' => 'EquiposController',
    'accesorios' => 'AccesoriosController',
    'movimientos' => 'MovimientosController',
    'usuarios' => 'UsuariosController',
    'reportes' => 'ReportesController',
    'proveedores' => 'ProveedoresController',
    'categorias' => 'CategoriasController',
    'marcas' => 'MarcasController',
    'mantenimientos' => 'MantenimientosController',
    'prestamos' => 'PrestamosController',
    'tipos_equipos' => 'TiposEquiposController'
];

// Verificar autenticación SOLO después de determinar el controller
$publicActions = ['login', 'authenticate', 'logout'];
$isPublic = ($controller === 'auth' && in_array($action, $publicActions));

// Si NO es público y NO hay sesión, forzar login
if (!$isPublic && !isset($_SESSION['user_id'])) {
    // Redirigir explícitamente al login
    header('Location: index.php?controller=auth&action=login');
    exit;
}

// Verificar si el controller existe en el mapa
if (!array_key_exists($controller, $controllerMap)) {
    http_response_code(404);
    echo "<h1>Error 404 - Controlador no encontrado</h1>";
    echo "<p>El controlador <strong>'$controller'</strong> no está registrado</p>";
    echo "<p>Controladores disponibles: " . implode(', ', array_keys($controllerMap)) . "</p>";
    echo "<a href='index.php?controller=auth&action=login'>Ir al Login</a>";
    exit;
}

// Obtener nombre de la clase del controller
$controllerName = $controllerMap[$controller];

// Verificar si la clase existe
if (!class_exists($controllerName)) {
    http_response_code(404);
    echo "<h1>Error 404 - Controlador no encontrado</h1>";
    echo "<p>El controlador <strong>'$controllerName'</strong> no existe</p>";
    echo "<p>Verifique que el archivo exista en: <code>controllers/$controllerName.php</code></p>";
    exit;
}

// Crear instancia del controller
$controllerObject = new $controllerName();

// Si no hay acción, usar 'index' como default (excepto para auth)
if (empty($action)) {
    $action = ($controller === 'auth') ? 'login' : 'index';
}

// Proteger operaciones que cambian estado.
$stateChangingGetActions = [
    'eliminar',
    'eliminar_equipo',
    'eliminar_accesorio',
    'cambiarEstado',
    'toggle_activo',
    'logout'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? '')) {
        csrf_forbidden();
    }
} elseif (in_array($action, $stateChangingGetActions, true)) {
    if (!csrf_is_valid($_GET['csrf_token'] ?? '')) {
        csrf_forbidden();
    }
}

// Verificar si la acción existe en el controller
if (!method_exists($controllerObject, $action)) {
    http_response_code(404);
    echo "<h1>Error 404 - Acción no encontrada</h1>";
    echo "<p>La acción <strong>'$action'</strong> no existe en el controlador <strong>'$controllerName'</strong></p>";
    echo "<hr>";
    echo "<h3>Métodos disponibles en $controllerName:</h3>";
    echo "<ul>";
    foreach (get_class_methods($controllerName) as $method) {
        if (substr($method, 0, 2) !== '__') {
            echo "<li><a href='index.php?controller=$controller&action=$method'>$method</a></li>";
        }
    }
    echo "</ul>";
    echo "<a href='index.php?controller=auth&action=login'>Ir al Login</a>";
    exit;
}

// Ejecutar la acción
try {
    $controllerObject->$action();
} catch (Exception $e) {
    http_response_code(500);
    echo "<h1>Error Interno</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    if (APP_ENV === 'development') {
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
}
