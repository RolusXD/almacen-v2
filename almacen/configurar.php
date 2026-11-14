<?php
/**
 * SCRIPT DE CONFIGURACIÓN INICIAL
 * Este script:
 * 1. Verifica la conexión a la BD
 * 2. Crea/actualiza el usuario admin con hash correcto
 * 3. Verifica que todo esté configurado correctamente
 */

// Configuración
define('DB_HOST', 'localhost');
define('DB_NAME', 'almacen_toi');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Iniciar buffer de salida para mejor presentación
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Sistema TOI</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden; }
        .header { background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .content { padding: 30px; }
        .step { margin-bottom: 25px; padding: 20px; border-radius: 8px; border-left: 4px solid #ddd; }
        .step.success { background: #d4edda; border-left-color: #28a745; }
        .step.error { background: #f8d7da; border-left-color: #dc3545; }
        .step.warning { background: #fff3cd; border-left-color: #ffc107; }
        .step.info { background: #d1ecf1; border-left-color: #17a2b8; }
        .step h3 { margin-bottom: 10px; display: flex; align-items: center; gap: 10px; }
        .step-content { margin-top: 10px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 6px; overflow-x: auto; margin: 10px 0; }
        .btn { display: inline-block; padding: 12px 24px; background: #2c5282; color: white; text-decoration: none; border-radius: 6px; margin: 10px 5px 10px 0; border: none; cursor: pointer; font-size: 14px; }
        .btn:hover { background: #1e3a5f; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .credentials { background: #f8f9fa; padding: 20px; border-radius: 8px; border: 2px solid #28a745; margin: 20px 0; }
        .credentials h4 { color: #28a745; margin-bottom: 15px; }
        .cred-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #dee2e6; }
        .cred-item:last-child { border-bottom: none; }
        .cred-label { font-weight: bold; color: #495057; }
        .cred-value { font-family: 'Courier New', monospace; color: #2c5282; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; border: 1px solid #dee2e6; text-align: left; }
        th { background: #2c5282; color: white; }
        tr:nth-child(even) { background: #f8f9fa; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-info { background: #17a2b8; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Configuración del Sistema de Almacén TOI</h1>
            <p>Verificación y configuración inicial</p>
        </div>
        
        <div class="content">
<?php

// Paso 1: Verificar conexión
echo '<div class="step info">';
echo '<h3>📡 Paso 1: Verificando conexión a la base de datos</h3>';
echo '<div class="step-content">';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo '<span class="badge badge-success">✅ EXITO</span> Conexión establecida a: <code>' . DB_NAME . '</code>';
    echo '</div></div>';
    
    // Paso 2: Verificar tabla usuarios
    echo '<div class="step info">';
    echo '<h3>📋 Paso 2: Verificando tabla de usuarios</h3>';
    echo '<div class="step-content">';
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() > 0) {
        echo '<span class="badge badge-success">✅ EXITO</span> La tabla <code>usuarios</code> existe';
        
        // Verificar estructura
        $stmt = $pdo->query("DESCRIBE usuarios");
        $columnas = $stmt->fetchAll();
        echo '<table>';
        echo '<tr><th>Columna</th><th>Tipo</th><th>Nulo</th><th>Key</th></tr>';
        foreach ($columnas as $columna) {
            echo '<tr>';
            echo '<td>' . $columna['Field'] . '</td>';
            echo '<td>' . $columna['Type'] . '</td>';
            echo '<td>' . $columna['Null'] . '</td>';
            echo '<td>' . ($columna['Key'] ? '🔑' : '') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<span class="badge badge-danger">❌ ERROR</span> La tabla <code>usuarios</code> NO existe';
        echo '<p><strong>Solución:</strong> Importe el archivo <code>database/schema.sql</code> en phpMyAdmin</p>';
        echo '<a href="http://localhost/phpmyadmin" class="btn" target="_blank">Abrir phpMyAdmin</a>';
    }
    echo '</div></div>';
    
    // Paso 3: Generar hash y actualizar/crear admin
    echo '<div class="step warning">';
    echo '<h3>🔐 Paso 3: Configurando usuario administrador</h3>';
    echo '<div class="step-content">';
    
    $password = 'Admin123!';
    $hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);
    
    echo '<p>Generando hash de contraseña con PHP ' . PHP_VERSION . '...</p>';
    echo '<pre>Contraseña: ' . $password . "\nHash generado: " . $hash . '</pre>';
    
    // Verificar si existe admin
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE usuario = 'admin'");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Actualizar contraseña
        $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE usuario = 'admin'");
        $stmt->execute([$hash]);
        echo '<p><span class="badge badge-success">✅ EXITO</span> Contraseña del administrador actualizada</p>';
    } else {
        // Crear administrador
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre_completo, usuario, password_hash, carnet, email, fecha_nacimiento, rol, activo) 
            VALUES ('Administrador Principal TOI', 'admin', ?, '00000000', 'admin@toi.bo', '1990-01-01', 'admin', 1)
        ");
        $stmt->execute([$hash]);
        echo '<p><span class="badge badge-success">✅ EXITO</span> Usuario administrador creado</p>';
    }
    
    echo '</div></div>';
    
    // Paso 4: Verificar usuario admin
    echo '<div class="step success">';
    echo '<h3>✅ Paso 4: Verificando usuario administrador</h3>';
    echo '<div class="step-content">';
    
    $stmt = $pdo->query("SELECT id_usuario, usuario, nombre_completo, carnet, email, rol, activo, created_at FROM usuarios WHERE usuario = 'admin'");
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo '<div class="credentials">';
        echo '<h4>🎉 ¡Configuración Completada!</h4>';
        echo '<div class="cred-item"><span class="cred-label">Usuario:</span><span class="cred-value">' . htmlspecialchars($admin['usuario']) . '</span></div>';
        echo '<div class="cred-item"><span class="cred-label">Contraseña:</span><span class="cred-value">Admin123!</span></div>';
        echo '<div class="cred-item"><span class="cred-label">Nombre:</span><span class="cred-value">' . htmlspecialchars($admin['nombre_completo']) . '</span></div>';
        echo '<div class="cred-item"><span class="cred-label">Email:</span><span class="cred-value">' . htmlspecialchars($admin['email']) . '</span></div>';
        echo '<div class="cred-item"><span class="cred-label">Rol:</span><span class="cred-value"><span class="badge badge-danger">' . strtoupper($admin['rol']) . '</span></span></div>';
        echo '<div class="cred-item"><span class="cred-label">Estado:</span><span class="cred-value">' . ($admin['activo'] ? '<span class="badge badge-success">ACTIVO</span>' : '<span class="badge badge-danger">INACTIVO</span>') . '</span></div>';
        echo '</div>';
        
        // Probar verificación de contraseña
        echo '<p>Probando verificación de contraseña...</p>';
        if (password_verify('Admin123!', $hash)) {
            echo '<p><span class="badge badge-success">✅ EXITO</span> La verificación de contraseña funciona correctamente</p>';
        } else {
            echo '<p><span class="badge badge-danger">❌ ERROR</span> La verificación de contraseña falló</p>';
        }
        
        echo '<div style="margin-top: 20px;">';
        echo '<a href="index.php?controller=auth&action=login" class="btn btn-success">🚀 Ir al Login</a>';
        echo '<a href="diagnostico.php" class="btn">📊 Ver Diagnóstico Completo</a>';
        echo '</div>';
    } else {
        echo '<p><span class="badge badge-danger">❌ ERROR</span> No se pudo crear/verificar el usuario administrador</p>';
    }
    
    echo '</div></div>';
    
    // Paso 5: Todos los usuarios
    echo '<div class="step info">';
    echo '<h3>👥 Usuarios Registrados</h3>';
    echo '<div class="step-content">';
    
    $stmt = $pdo->query("SELECT id_usuario, usuario, nombre_completo, carnet, email, rol, activo FROM usuarios ORDER BY id_usuario");
    $usuarios = $stmt->fetchAll();
    
    if (count($usuarios) > 0) {
        echo '<table>';
        echo '<tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Carnet</th><th>Email</th><th>Rol</th><th>Estado</th></tr>';
        foreach ($usuarios as $user) {
            echo '<tr>';
            echo '<td>' . $user['id_usuario'] . '</td>';
            echo '<td><strong>' . htmlspecialchars($user['usuario']) . '</strong></td>';
            echo '<td>' . htmlspecialchars($user['nombre_completo']) . '</td>';
            echo '<td>' . htmlspecialchars($user['carnet']) . '</td>';
            echo '<td>' . htmlspecialchars($user['email']) . '</td>';
            echo '<td><span class="badge ' . ($user['rol'] === 'admin' ? 'badge-danger' : 'badge-info') . '">' . strtoupper($user['rol']) . '</span></td>';
            echo '<td>' . ($user['activo'] ? '<span class="badge badge-success">ACTIVO</span>' : '<span class="badge badge-danger">INACTIVO</span>') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>No hay usuarios registrados además del administrador.</p>';
    }
    
    echo '</div></div>';

} catch (PDOException $e) {
    echo '<span class="badge badge-danger">❌ ERROR</span> ' . htmlspecialchars($e->getMessage());
    echo '<div class="step error">';
    echo '<h3>Errores Encontrados</h3>';
    echo '<ul>';
    echo '<li>Verifique que XAMPP esté ejecutándose</li>';
    echo '<li>Verifique que la base de datos <code>almacen_toi</code> exista</li>';
    echo '<li>Verifique las credenciales en <code>config/database.php</code></li>';
    echo '</ul>';
    echo '<a href="http://localhost/phpmyadmin" class="btn" target="_blank">Abrir phpMyAdmin</a>';
    echo '</div>';
}

?>
        </div>
    </div>
    
    <?php
    $content = ob_get_clean();
    echo $content;
    ?>
</body>
</html>
