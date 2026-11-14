<?php
/**
 * DIAGNÓSTICO Y SOLUCIÓN DE SESIONES PHP
 * Ejecute este archivo para diagnosticar problemas de sesiones
 */

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Diagnóstico de Sesiones - TOI</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { color: #2c5282; border-bottom: 3px solid #2c5282; padding-bottom: 10px; }
        h2 { color: #1e3a5f; margin-top: 30px; }
        .ok { color: #28a745; background: #d4edda; padding: 15px; border-radius: 6px; border-left: 4px solid #28a745; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 6px; border-left: 4px solid #dc3545; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 15px; border-radius: 6px; border-left: 4px solid #ffc107; margin: 10px 0; }
        .info { color: #004085; background: #d1ecf1; padding: 15px; border-radius: 6px; border-left: 4px solid #17a2b8; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; border: 1px solid #dee2e6; text-align: left; }
        th { background: #2c5282; color: white; }
        tr:nth-child(even) { background: #f8f9fa; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; color: #e83e8c; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 6px; overflow-x: auto; border: 1px solid #dee2e6; }
        .btn { display: inline-block; padding: 12px 24px; background: #2c5282; color: white; text-decoration: none; border-radius: 6px; margin: 10px 5px 10px 0; }
        .btn:hover { background: #1e3a5f; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .checklist { list-style: none; }
        .checklist li { padding: 8px 0; }
        .checklist li:before { content: '☐ '; color: #dc3545; font-weight: bold; }
        .checklist li.done:before { content: '✓ '; color: #28a745; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Diagnóstico de Sesiones PHP</h1>";

// ============================================
// 1. VERIFICAR CONFIGURACIÓN DE SESIONES
// ============================================
echo "<h2>1. Configuración de Sesiones en php.ini</h2>";
echo "<table>";
echo "<tr><th>Directiva</th><th>Valor Local</th><th>Valor Master</th><th>Estado</th></tr>";

$directivas = [
    'session.use_cookies',
    'session.use_only_cookies',
    'session.cookie_secure',
    'session.cookie_httponly',
    'session.use_strict_mode',
    'session.save_handler',
    'session.auto_start',
    'session.gc_maxlifetime'
];

foreach ($directivas as $directiva) {
    $local = ini_get($directiva);
    $master = ini_get($directiva, INI_MASTER);
    $estado = ($directiva === 'session.auto_start' && $local == '0') || 
              ($directiva !== 'session.auto_start' && $local) ? '✅ OK' : '⚠️ Revisar';
    echo "<tr>";
    echo "<td><code>$directiva</code></td>";
    echo "<td>" . ($local ? $local : '<em>no definido</em>') . "</td>";
    echo "<td>" . ($master ? $master : '<em>no definido</em>') . "</td>";
    echo "<td>$estado</td>";
    echo "</tr>";
}
echo "</table>";

// ============================================
// 2. VERIFICAR SESSION SAVE PATH
// ============================================
echo "<h2>2. Ruta de Guardado de Sesiones</h2>";
$savePath = ini_get('session.save_path');
echo "<p><strong>Ruta actual:</strong> <code>$savePath</code></p>";

if (!empty($savePath)) {
    // Limpiar ruta de prefijos (ej: "C:\xampp\tmp" o "154;/var/lib/php/sessions")
    $cleanPath = preg_replace('#^\d+;#', '', $savePath);
    
    if (is_dir($cleanPath)) {
        echo "<div class='ok'>✅ La carpeta existe y es accesible</div>";
        echo "<p><strong>Permisos:</strong> " . substr(sprintf('%o', fileperms($cleanPath)), -4) . "</p>";
        echo "<p><strong>Escribible:</strong> " . (is_writable($cleanPath) ? '✅ Sí' : '❌ No') . "</p>";
        
        // Contar archivos de sesión
        $archivos = glob($cleanPath . '/sess_*');
        echo "<p><strong>Archivos de sesión existentes:</strong> " . count($archivos) . "</p>";
    } else {
        echo "<div class='error'>❌ La carpeta NO existe o no es accesible</div>";
        echo "<div class='info'>";
        echo "<strong>Solución:</strong><br>";
        echo "1. Cree la carpeta: <code>mkdir $cleanPath</code><br>";
        echo "2. O cambie la ruta en <code>php.ini</code>: <code>session.save_path = \"C:/xampp/tmp\"</code><br>";
        echo "3. Reinicie Apache";
        echo "</div>";
    }
} else {
    echo "<div class='warning'>⚠️ session.save_path está vacío</div>";
    echo "<p>PHP usará la ruta por defecto del sistema</p>";
}

// ============================================
// 3. INTENTAR INICIAR SESIÓN
// ============================================
echo "<h2>3. Intentando Iniciar Sesión</h2>";

// Verificar si ya hay una sesión activa
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<div class='ok'>✅ Sesión ya estaba activa</div>";
    echo "<p><strong>ID de sesión:</strong> <code>" . session_id() . "</code></p>";
} else {
    // Intentar iniciar sesión
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    
    // Limpiar output buffer si es necesario
    if (ob_get_length()) {
        ob_clean();
    }
    
    $resultado = @session_start();
    
    if ($resultado) {
        echo "<div class='ok'>✅ Sesión iniciada correctamente</div>";
        echo "<p><strong>ID de sesión:</strong> <code>" . session_id() . "</code></p>";
        echo "<p><strong>Nombre de sesión:</strong> <code>" . session_name() . "</code></p>";
        
        // Probar guardar y leer variable de sesión
        $_SESSION['test_sesion'] = 'prueba_' . time();
        if (isset($_SESSION['test_sesion']) && $_SESSION['test_sesion'] === 'prueba_' . time()) {
            echo "<div class='ok'>✅ Lectura/escritura de variables de sesión funciona correctamente</div>";
        } else {
            echo "<div class='warning'>⚠️ Hay problemas con la lectura/escritura de variables de sesión</div>";
        }
    } else {
        echo "<div class='error'>❌ No se pudo iniciar la sesión</div>";
        echo "<p><strong>Error:</strong> " . error_get_last()['message'] . "</p>";
    }
}

// ============================================
// 4. VERIFICAR COOKIES
// ============================================
echo "<h2>4. Configuración de Cookies</h2>";
echo "<table>";
echo "<tr><th>Directiva</th><th>Valor</th><th>Descripción</th></tr>";
echo "<tr><td><code>session.cookie_lifetime</code></td><td>" . ini_get('session.cookie_lifetime') . "</td><td>Duración de la cookie (0 = hasta cerrar navegador)</td></tr>";
echo "<tr><td><code>session.cookie_path</code></td><td>" . ini_get('session.cookie_path') . "</td><td>Ruta donde la cookie es válida</td></tr>";
echo "<tr><td><code>session.cookie_domain</code></td><td>" . ini_get('session.cookie_domain') . "</td><td>Dominio donde la cookie es válida</td></tr>";
echo "<tr><td><code>session.cookie_secure</code></td><td>" . ini_get('session.cookie_secure') . "</td><td>1 = solo HTTPS, 0 = HTTP y HTTPS</td></tr>";
echo "<tr><td><code>session.cookie_httponly</code></td><td>" . ini_get('session.cookie_httponly') . "</td><td>1 = no accesible por JavaScript</td></tr>";
echo "</table>";

// ============================================
// 5. RESUMEN Y RECOMENDACIONES
// ============================================
echo "<h2>5. Resumen y Recomendaciones</h2>";

$problemas = [];
$soluciones = [];

// Verificar problemas comunes
if (ini_get('session.auto_start') == '1') {
    $problemas[] = "session.auto_start está en 1 (debería ser 0)";
}

if (ini_get('session.use_cookies') != '1') {
    $problemas[] = "session.use_cookies no está en 1";
}

$savePath = ini_get('session.save_path');
$cleanPath = preg_replace('#^\d+;#', '', $savePath);
if (!empty($savePath) && !is_dir($cleanPath)) {
    $problemas[] = "session.save_path apunta a una carpeta inexistente: $cleanPath";
}

if (empty($problemas)) {
    echo "<div class='ok'>";
    echo "<h3>✅ No se encontraron problemas críticos</h3>";
    echo "<p>Las sesiones deberían funcionar correctamente. Si aún tiene problemas, verifique:</p>";
    echo "<ul class='checklist'>";
    echo "<li class='done'>Apache está ejecutándose</li>";
    echo "<li class='done'>PHP está cargando correctamente</li>";
    echo "<li>No hay output antes de session_start() en sus scripts</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>⚠️ Se encontraron " . count($problemas) . " problema(s)</h3>";
    echo "<ul>";
    foreach ($problemas as $problema) {
        echo "<li>$problema</li>";
    }
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>🔧 Soluciones Recomendadas</h3>";
    echo "<ol>";
    echo "<li>Abra <code>C:\\xampp\\php\\php.ini</code></li>";
    echo "<li>Busque y modifique las siguientes líneas:</li>";
    echo "</ol>";
    echo "<pre>";
    echo "session.use_cookies = 1\n";
    echo "session.use_only_cookies = 1\n";
    echo "session.save_path = \"C:/xampp/tmp\"\n";
    echo "session.auto_start = 0\n";
    echo "session.cookie_httponly = 1\n";
    echo "</pre>";
    echo "<ol start='3'>";
    echo "<li>Guarde el archivo php.ini</li>";
    echo "<li>Reinicie Apache desde el panel de XAMPP</li>";
    echo "<li>Vuelva a ejecutar esta prueba</li>";
    echo "</ol>";
    echo "</div>";
}

// Botones de acción
echo "<hr>";
echo "<div style='margin-top: 30px;'>";
echo "<a href='test_carga.php' class='btn'>🔄 Volver a Test de Carga</a>";
echo "<a href='index.php?controller=auth&action=login' class='btn btn-success'>🚀 Ir al Login</a>";
echo "<a href='configurar.php' class='btn'>⚙️ Configurar Sistema</a>";
echo "</div>";

echo "</div></body></html>";
