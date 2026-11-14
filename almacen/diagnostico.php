<?php
/**
 * SCRIPT DE DIAGNÓSTICO Y CONFIGURACIÓN
 * Ejecute este archivo en el navegador para:
 * 1. Verificar conexión a la base de datos
 * 2. Verificar si existe el usuario admin
 * 3. Generar el hash correcto para la contraseña
 */

// Configuración
define('DB_HOST', 'localhost');
define('DB_NAME', 'almacen_toi');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Diagnóstico - Sistema TOI</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c5282; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border-radius: 4px; margin: 10px 0; }
        .info { color: #004085; padding: 10px; background: #d1ecf1; border-radius: 4px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #2c5282; color: white; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        .btn { display: inline-block; padding: 10px 20px; background: #2c5282; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px 10px 0; }
        .btn:hover { background: #1e3a5f; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Diagnóstico del Sistema de Almacén TOI</h1>";

// Paso 1: Verificar conexión a la base de datos
echo "<h2>1. Verificando conexión a la base de datos</h2>";
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "<div class='success'>✅ Conexión exitosa a la base de datos: <code>" . DB_NAME . "</code></div>";
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='warning'>
        <strong>Posibles soluciones:</strong><br>
        1. Verifique que XAMPP esté ejecutándose (Apache y MySQL)<br>
        2. Verifique que la base de datos <code>almacen_toi</code> exista<br>
        3. Importe el archivo <code>database/schema.sql</code> en phpMyAdmin
    </div>";
    echo "</div></body></html>";
    exit;
}

// Paso 2: Verificar si existe la tabla usuarios
echo "<h2>2. Verificando tabla de usuarios</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✅ La tabla <code>usuarios</code> existe</div>";
    } else {
        echo "<div class='error'>❌ La tabla <code>usuarios</code> NO existe</div>";
        echo "<div class='warning'>
            <strong>Solución:</strong> Importe el archivo <code>database/schema.sql</code> en phpMyAdmin
        </div>";
    }
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Paso 3: Verificar estructura de la tabla usuarios
echo "<h2>3. Estructura de la tabla usuarios</h2>";
try {
    $stmt = $pdo->query("DESCRIBE usuarios");
    $columnas = $stmt->fetchAll();
    echo "<table>
            <tr><th>Columna</th><th>Tipo</th><th>Nulo</th><th>Key</th><th>Default</th></tr>";
    foreach ($columnas as $columna) {
        echo "<tr>
                <td>{$columna['Field']}</td>
                <td>{$columna['Type']}</td>
                <td>{$columna['Null']}</td>
                <td>{$columna['Key']}</td>
                <td>{$columna['Default']}</td>
              </tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Paso 4: Verificar si existe el usuario admin
echo "<h2>4. Verificando usuario administrador</h2>";
try {
    $stmt = $pdo->query("SELECT id_usuario, usuario, nombre_completo, carnet, email, rol, activo FROM usuarios WHERE usuario = 'admin'");
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<div class='success'>✅ El usuario <code>admin</code> existe</div>";
        echo "<table>
                <tr><th>ID</th><td>{$admin['id_usuario']}</td></tr>
                <tr><th>Usuario</th><td>{$admin['usuario']}</td></tr>
                <tr><th>Nombre</th><td>{$admin['nombre_completo']}</td></tr>
                <tr><th>Carnet</th><td>{$admin['carnet']}</td></tr>
                <tr><th>Email</th><td>{$admin['email']}</td></tr>
                <tr><th>Rol</th><td>{$admin['rol']}</td></tr>
                <tr><th>Activo</th><td>" . ($admin['activo'] ? 'Sí' : 'No') . "</td></tr>
              </table>";
    } else {
        echo "<div class='error'>❌ El usuario <code>admin</code> NO existe</div>";
    }
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Paso 5: Mostrar todos los usuarios
echo "<h2>5. Usuarios registrados</h2>";
try {
    $stmt = $pdo->query("SELECT id_usuario, usuario, nombre_completo, carnet, email, rol, activo FROM usuarios ORDER BY id_usuario");
    $usuarios = $stmt->fetchAll();
    
    if (count($usuarios) > 0) {
        echo "<table>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Carnet</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Activo</th>
                </tr>";
        foreach ($usuarios as $user) {
            echo "<tr>
                    <td>{$user['id_usuario']}</td>
                    <td><strong>{$user['usuario']}</strong></td>
                    <td>{$user['nombre_completo']}</td>
                    <td>{$user['carnet']}</td>
                    <td>{$user['email']}</td>
                    <td><span style='color: " . ($user['rol'] === 'admin' ? 'red' : 'blue') . "'>{$user['rol']}</span></td>
                    <td>" . ($user['activo'] ? '✅' : '❌') . "</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠️ No hay usuarios registrados en la base de datos</div>";
    }
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Paso 6: Generar hash de contraseña
echo "<h2>6. Generador de Hash de Contraseña</h2>";
$password = 'Admin123!';
$hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);
echo "<div class='info'>
    <strong>Contraseña:</strong> <code>{$password}</code><br>
    <strong>Hash generado:</strong><br>
    <code style='word-break: break-all; display: block; margin: 10px 0; padding: 10px; background: #f4f4f4;'>{$hash}</code>
</div>";

echo "<div class='warning'>
    <strong>⚠️ IMPORTANTE:</strong> Copie el hash generado arriba y ejecuta el siguiente SQL en phpMyAdmin:<br><br>
    <code style='display: block; margin: 10px 0; padding: 10px; background: #f4f4f4; word-break: break-all;'>
    UPDATE usuarios SET password_hash = '{$hash}' WHERE usuario = 'admin';
    </code>
</div>";

// Paso 7: Botón para crear/actualizar admin
echo "<h2>7. Acciones Rápidas</h2>";

// Formulario para actualizar contraseña del admin
echo "<form method='POST' style='border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin: 20px 0;'>
    <h3>Actualizar Contraseña del Administrador</h3>
    <div style='margin: 10px 0;'>
        <label><strong>Nueva contraseña:</strong></label><br>
        <input type='text' name='nueva_password' value='Admin123!' style='width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px;'>
        <small>Contraseña por defecto: Admin123!</small>
    </div>
    <button type='submit' name='actualizar_admin' class='btn' style='border: none; cursor: pointer;'>
        🔒 Actualizar Contraseña del Admin
    </button>
</form>";

if (isset($_POST['actualizar_admin'])) {
    try {
        $nueva_password = $_POST['nueva_password'] ?? 'Admin123!';
        $nuevo_hash = password_hash($nueva_password, PASSWORD_DEFAULT, ['cost' => 10]);
        
        // Verificar si existe admin
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE usuario = 'admin'");
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Actualizar contraseña
            $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE usuario = 'admin'");
            $stmt->execute([$nuevo_hash]);
            echo "<div class='success'>✅ Contraseña del administrador actualizada correctamente</div>";
            echo "<div class='info'>
                <strong>Nuevo hash:</strong><br>
                <code style='word-break: break-all; display: block; margin: 10px 0; padding: 10px; background: #f4f4f4;'>{$nuevo_hash}</code>
            </div>";
        } else {
            // Crear administrador
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, usuario, password_hash, carnet, email, fecha_nacimiento, rol, activo) 
                                   VALUES ('Administrador Principal TOI', 'admin', ?, '00000000', 'admin@toi.bo', '1990-01-01', 'admin', 1)");
            $stmt->execute([$nuevo_hash]);
            echo "<div class='success'>✅ Usuario administrador creado correctamente</div>";
        }
        
        echo "<div class='success'><strong>¡Ahora puede iniciar sesión con:</strong><br>
              Usuario: <code>admin</code><br>
              Contraseña: <code>" . htmlspecialchars($nueva_password) . "</code></div>";
        
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

echo "</div></body></html>";
?>
