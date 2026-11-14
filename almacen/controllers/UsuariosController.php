<?php
/**
 * Controlador de Usuarios
 * CRUD completo para gestión de usuarios
 */
class UsuariosController extends Controller {
    
    /**
     * Listar todos los usuarios
     */
    public function index() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
            $this->setFlash('error', 'No tiene permisos para acceder a esta sección');
            $this->redirect('index.php?controller=dashboard');
        }
        
        $usuarioModel = new Usuario();
        $filtro = $this->sanitize($_GET['filtro'] ?? '');
        $rol = $this->sanitize($_GET['rol'] ?? 'todos');
        $estado = $this->sanitize($_GET['estado'] ?? 'activos');
        
        $usuarios = $usuarioModel->buscar($filtro, $rol, $estado);
        $estadisticas = $usuarioModel->estadisticas();
        
        $this->render('usuarios/index', [
            'title' => 'Gestión de Usuarios',
            'usuarios' => $usuarios,
            'filtro' => $filtro,
            'rol' => $rol,
            'estado' => $estado,
            'estadisticas' => $estadisticas
        ]);
    }
    
    /**
     * Mostrar formulario de creación
     */
    public function crear() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
            $this->setFlash('error', 'No tiene permisos para realizar esta acción');
            $this->redirect('index.php?controller=dashboard');
        }
        
        $this->render('usuarios/form', [
            'title' => 'Nuevo Usuario',
            'accion' => 'crear',
            'usuario' => null
        ]);
    }
    
    /**
     * Guardar nuevo usuario
     */
    public function guardar() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
            $this->setFlash('error', 'No tiene permisos para realizar esta acción');
            $this->redirect('index.php?controller=usuarios');
        }
        
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=usuarios');
        }
        
        $usuarioModel = new Usuario();
        
        $data = [
            'nombre_completo' => $this->sanitize($_POST['nombre_completo']),
            'usuario' => $this->sanitize($_POST['usuario']),
            'password' => $_POST['password'], // No sanitizar para hash
            'carnet' => $this->sanitize($_POST['carnet']),
            'email' => $this->sanitize($_POST['email']),
            'fecha_nacimiento' => $_POST['fecha_nacimiento'],
            'rol' => $_POST['rol'],
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        // Validaciones
        if (empty($data['nombre_completo']) || empty($data['usuario']) || 
            empty($data['password']) || empty($data['carnet']) || 
            empty($data['email']) || empty($data['fecha_nacimiento'])) {
            $this->setFlash('error', 'Todos los campos marcados con * son obligatorios');
            $this->redirect('index.php?controller=usuarios&action=crear');
        }
        
        // Validar email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'El correo electrónico no es válido');
            $this->redirect('index.php?controller=usuarios&action=crear');
        }
        
        // Validar que la fecha de nacimiento no sea futura
        if ($data['fecha_nacimiento'] > date('Y-m-d')) {
            $this->setFlash('error', 'La fecha de nacimiento no puede ser futura');
            $this->redirect('index.php?controller=usuarios&action=crear');
        }
        
        $resultado = $usuarioModel->registrar($data);
        
        if ($resultado['success']) {
            $this->setFlash('success', $resultado['message']);
            $this->redirect('index.php?controller=usuarios');
        } else {
            $this->setFlash('error', $resultado['message']);
            $this->redirect('index.php?controller=usuarios&action=crear');
        }
    }
    
    /**
     * Mostrar formulario de edición
     */
    public function editar() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
            $this->setFlash('error', 'No tiene permisos para realizar esta acción');
            $this->redirect('index.php?controller=dashboard');
        }
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->setFlash('error', 'ID de usuario no válido');
            $this->redirect('index.php?controller=usuarios');
        }
        
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->find($id);
        
        if (!$usuario) {
            $this->setFlash('error', 'Usuario no encontrado');
            $this->redirect('index.php?controller=usuarios');
        }
        
        $this->render('usuarios/form', [
            'title' => 'Editar Usuario',
            'accion' => 'editar',
            'usuario' => $usuario
        ]);
    }
    
    /**
     * Actualizar usuario
     */
    public function actualizar() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
            $this->setFlash('error', 'No tiene permisos para realizar esta acción');
            $this->redirect('index.php?controller=usuarios');
        }
        
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=usuarios');
        }
        
        $id = $_POST['id_usuario'] ?? null;
        
        if (!$id) {
            $this->setFlash('error', 'ID de usuario no válido');
            $this->redirect('index.php?controller=usuarios');
        }
        
        $usuarioModel = new Usuario();
        
        $data = [
            'nombre_completo' => $this->sanitize($_POST['nombre_completo']),
            'usuario' => $this->sanitize($_POST['usuario']),
            'carnet' => $this->sanitize($_POST['carnet']),
            'email' => $this->sanitize($_POST['email']),
            'fecha_nacimiento' => $_POST['fecha_nacimiento'],
            'rol' => $_POST['rol'],
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        // Validaciones
        if (empty($data['nombre_completo']) || empty($data['usuario']) || 
            empty($data['carnet']) || empty($data['email']) || 
            empty($data['fecha_nacimiento'])) {
            $this->setFlash('error', 'Todos los campos marcados con * son obligatorios');
            $this->redirect('index.php?controller=usuarios&action=editar&id=' . $id);
        }
        
        // Validar email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'El correo electrónico no es válido');
            $this->redirect('index.php?controller=usuarios&action=editar&id=' . $id);
        }
        
        $resultado = $usuarioModel->actualizar($id, $data);
        
        if ($resultado['success']) {
            $this->setFlash('success', $resultado['message']);
            $this->redirect('index.php?controller=usuarios');
        } else {
            $this->setFlash('error', $resultado['message']);
            $this->redirect('index.php?controller=usuarios&action=editar&id=' . $id);
        }
    }
    
    /**
     * Cambiar contraseña de usuario
     */
    public function cambiarPassword() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
            $this->setFlash('error', 'No tiene permisos para realizar esta acción');
            $this->redirect('index.php?controller=dashboard');
        }
        
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=usuarios');
        }
        
        $id = $_POST['id_usuario'] ?? null;
        $nuevaPassword = $_POST['nueva_password'] ?? '';
        
        if (!$id || empty($nuevaPassword)) {
            $this->setFlash('error', 'Datos inválidos');
            $this->redirect('index.php?controller=usuarios');
        }
        
        $usuarioModel = new Usuario();
        $resultado = $usuarioModel->actualizarPassword($id, $nuevaPassword);
        
        if (is_array($resultado) && !empty($resultado['success'])) {
            $this->setFlash('success', 'Contraseña actualizada correctamente');
        } else {
            $mensaje = is_array($resultado) ? ($resultado['message'] ?? 'Error al actualizar la contraseña') : 'Error al actualizar la contraseña';
            $this->setFlash('error', $mensaje);
        }
        
        $this->redirect('index.php?controller=usuarios');
    }
    
    /**
     * Cambiar estado del usuario (activo/inactivo)
     */
    public function cambiarEstado() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
            $this->setFlash('error', 'No tiene permisos para realizar esta acción');
            $this->redirect('index.php?controller=dashboard');
        }
        
        $id = $_GET['id'] ?? null;
        $activo = $_GET['activo'] ?? 1;
        
        if (!$id) {
            $this->setFlash('error', 'ID de usuario no válido');
            $this->redirect('index.php?controller=usuarios');
        }
        
        $usuarioModel = new Usuario();
        $usuarioModel->cambiarEstado($id, $activo);
        
        $this->setFlash('success', 'Estado del usuario actualizado correctamente');
        $this->redirect('index.php?controller=usuarios');
    }
    
    /**
     * Eliminar usuario
     */
    public function eliminar() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
            $this->setFlash('error', 'No tiene permisos para realizar esta acción');
            $this->redirect('index.php?controller=dashboard');
        }
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->setFlash('error', 'ID de usuario no válido');
            $this->redirect('index.php?controller=usuarios');
        }
        
        $usuarioModel = new Usuario();
        $resultado = $usuarioModel->eliminar($id);
        
        if ($resultado['success']) {
            $this->setFlash('success', $resultado['message']);
        } else {
            $this->setFlash('error', $resultado['message']);
        }
        
        $this->redirect('index.php?controller=usuarios');
    }

    /**
     * Mostrar perfil del usuario actual
     */
    public function perfil() {
        $this->requireAuth();

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->find($_SESSION['user_id']);

        if (!$usuario) {
            $this->setFlash('error', 'Usuario no encontrado');
            $this->redirect('index.php?controller=dashboard');
        }

        $this->render('usuarios/perfil', [
            'title' => 'Mi Perfil',
            'usuario' => $usuario
        ]);
    }

    /**
     * Actualizar perfil del usuario actual
     */
    public function actualizarPerfil() {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('index.php?controller=usuarios&action=perfil');
        }

        $id = $_SESSION['user_id'];
        $usuarioModel = new Usuario();

        $data = [
            'nombre_completo' => $this->sanitize($_POST['nombre_completo']),
            'email' => $this->sanitize($_POST['email']),
            'carnet' => $this->sanitize($_POST['carnet']),
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null
        ];

        // Validar email
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Email no válido');
            $this->redirect('index.php?controller=usuarios&action=perfil');
        }

        // Verificar si hay nueva contraseña
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if (!empty($password)) {
            if ($password !== $password_confirm) {
                $this->setFlash('error', 'Las contraseñas no coinciden');
                $this->redirect('index.php?controller=usuarios&action=perfil');
            }

            if (strlen($password) < 6) {
                $this->setFlash('error', 'La contraseña debe tener al menos 6 caracteres');
                $this->redirect('index.php?controller=usuarios&action=perfil');
            }

            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        }

        $resultado = $usuarioModel->actualizar($id, $data);

        if (is_array($resultado) && !empty($resultado['success'])) {
            // Actualizar nombre en sesión
            $_SESSION['user_nombre'] = $data['nombre_completo'];
            $_SESSION['user_email'] = $data['email'];
            $_SESSION['user_carnet'] = $data['carnet'];

            $this->setFlash('success', 'Perfil actualizado correctamente');
        } else {
            $mensaje = is_array($resultado) ? ($resultado['message'] ?? 'Error al actualizar el perfil') : 'Error al actualizar el perfil';
            $this->setFlash('error', $mensaje);
        }

        $this->redirect('index.php?controller=usuarios&action=perfil');
    }
}
