<?php
/**
 * Controlador de Autenticación
 * Gestión de login y logout con nombre de usuario
 */
class AuthController extends Controller {

    public function login() {
        // Si ya está autenticado, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('index.php?controller=dashboard');
        }

        // Renderizar login sin layout (sin sidebar)
        $title = 'Iniciar Sesión';
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function authenticate() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=auth&action=login');
        }
        
        $usuario = $this->sanitize($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? ''; // No sanitizar para password_verify
        
        if (empty($usuario) || empty($password)) {
            $this->setFlash('error', 'Por favor ingrese usuario y contraseña');
            $this->redirect('index.php?controller=auth&action=login');
        }
        
        $usuarioModel = new Usuario();
        $user = $usuarioModel->verificarCredenciales($usuario, $password);
        
        if ($user) {
            session_regenerate_id(true);
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            // Iniciar sesión
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_nombre'] = $user['nombre_completo'];
            $_SESSION['user_usuario'] = $user['usuario'];
            $_SESSION['user_rol'] = $user['rol'];
            $_SESSION['user_carnet'] = $user['carnet'];
            $_SESSION['user_email'] = $user['email'];
            
            $this->setFlash('success', 'Bienvenido, ' . $user['nombre_completo']);
            $this->redirect('index.php?controller=dashboard');
        } else {
            $this->setFlash('error', 'Usuario o contraseña incorrectos');
            $this->redirect('index.php?controller=auth&action=login');
        }
    }
    
    public function logout() {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        session_start();

        $this->setFlash('success', 'Sesión cerrada correctamente');
        $this->redirect('index.php?controller=auth&action=login');
    }
}
