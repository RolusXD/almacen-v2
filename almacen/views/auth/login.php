<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Iniciar Sesión' ?> - TOI</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #1e3a5f;
            --secondary-color: #2c5282;
            --accent-color: #4299e1;
            --bg-gradient-start: #1a365d;
            --bg-gradient-end: #2d5a87;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--primary-color) 50%, var(--bg-gradient-end) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            margin: 0;
        }

        .login-container {
            display: flex;
            max-width: 900px;
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
        }

        .login-logo {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.95;
        }

        .login-left h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
        }

        .login-left p {
            font-size: 0.95rem;
            opacity: 0.85;
            margin: 0;
        }

        .login-features {
            margin-top: 2rem;
            text-align: left;
        }

        .login-features li {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .login-right {
            flex: 1;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            margin-bottom: 2rem;
        }

        .login-header h2 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0 0 0.5rem 0;
        }

        .login-header p {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(30,58,95,0.1);
            outline: none;
        }

        .input-group-text {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-right: none;
            border-radius: 8px 0 0 8px;
            color: #6b7280;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 8px 8px 0;
        }

        .input-group .form-control:focus {
            border-left: none;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            padding: 0.875rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: white;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #1a365d 0%, #1c4e8a 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30,58,95,0.4);
        }

        .login-info {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f0f7ff;
            border-radius: 8px;
            border-left: 4px solid var(--accent-color);
        }

        .login-info small {
            color: #1e3a5f;
            font-size: 0.85rem;
        }

        .login-info strong {
            font-weight: 600;
        }

        .alert {
            border-radius: 8px;
            padding: 0.875rem 1rem;
            font-size: 0.9rem;
            border: none;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .login-left {
                padding: 2rem;
            }

            .login-right {
                padding: 2rem;
            }

            .login-logo {
                font-size: 3rem;
            }

            .login-left h1 {
                font-size: 1.5rem;
            }
        }
    </style>
    <link href="assets/css/future-interface.css?v=20260417" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-left">
            <div class="login-logo">
                <i class="bi bi-box-seam"></i>
            </div>
            <h1>TOI Nexus</h1>
            <p>Control operativo de inventario</p>
            
            <ul class="login-features list-unstyled">
                <li>
                    <i class="bi bi-check-circle-fill" style="color: #48bb78;"></i>
                    Inventario centralizado
                </li>
                <li>
                    <i class="bi bi-check-circle-fill" style="color: #48bb78;"></i>
                    Movimientos trazables
                </li>
                <li>
                    <i class="bi bi-check-circle-fill" style="color: #48bb78;"></i>
                    Lectura rápida de stock
                </li>
                <li>
                    <i class="bi bi-check-circle-fill" style="color: #48bb78;"></i>
                    Sesión protegida
                </li>
            </ul>
        </div>

        <div class="login-right">
            <div class="login-header">
                <h2>Acceso Operativo</h2>
                <p>Ingrese sus credenciales para continuar</p>
            </div>

            <?php
            $flash = null;
            if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['flash'])) {
                $flash = $_SESSION['flash'];
                unset($_SESSION['flash']);
            }
            if ($flash):
            ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?= $flash['type'] === 'error' ? 'exclamation-triangle' : 'check-circle' ?>"></i>
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form action="index.php?controller=auth&action=authenticate" method="POST">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" class="form-control" id="usuario" name="usuario"
                               placeholder="Ingresa tu usuario" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Ingresa tu contraseña" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Ingresar
                </button>
            </form>

            <?php if (APP_ENV === 'development'): ?>
            <div class="login-info">
                <small>
                    <i class="bi bi-info-circle"></i>
                    <strong>Credenciales por defecto:</strong><br>
                    Usuario: <code>admin</code> | Contraseña: <code>Admin123!</code>
                </small>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
