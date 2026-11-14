<?php
/**
 * Controlador de Proveedores
 * CRUD completo para gestión de proveedores
 */
class ProveedoresController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
    }
    
    public function index() {
        $filtro = $this->sanitize($_GET['filtro'] ?? '');
        $estado = $this->sanitize($_GET['estado'] ?? 'activos');
        
        $sql = "SELECT * FROM proveedores WHERE 1=1";
        $params = [];
        
        if ($estado === 'activos') {
            $sql .= " AND activo = 1";
        } elseif ($estado === 'inactivos') {
            $sql .= " AND activo = 0";
        }
        
        if (!empty($filtro)) {
            $sql .= " AND (nombre_empresa LIKE ? OR nit LIKE ? OR nombre_contacto LIKE ? OR email LIKE ?)";
            $filtroParam = "%$filtro%";
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
        }
        
        $sql .= " ORDER BY nombre_empresa ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $proveedores = $stmt->fetchAll();
        
        $this->render('proveedores/index', [
            'title' => 'Proveedores',
            'proveedores' => $proveedores,
            'filtro' => $filtro,
            'estado' => $estado
        ]);
    }
    
    public function crear() {
        $this->render('proveedores/form', [
            'title' => 'Nuevo Proveedor',
            'accion' => 'crear',
            'proveedor' => null
        ]);
    }
    
    public function guardar() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=proveedores');
        }
        
        $data = [
            'nombre_empresa' => $this->sanitize($_POST['nombre_empresa']),
            'nit' => $this->sanitize($_POST['nit']),
            'nombre_contacto' => $this->sanitize($_POST['nombre_contacto']),
            'cargo_contacto' => $this->sanitize($_POST['cargo_contacto']),
            'telefono' => $this->sanitize($_POST['telefono']),
            'celular' => $this->sanitize($_POST['celular']),
            'email' => $this->sanitize($_POST['email']),
            'direccion' => $this->sanitize($_POST['direccion']),
            'ciudad' => $this->sanitize($_POST['ciudad']),
            'pais' => $this->sanitize($_POST['pais']),
            'pagina_web' => $this->sanitize($_POST['pagina_web']),
            'productos' => $this->sanitize($_POST['productos']),
            'observaciones' => $this->sanitize($_POST['observaciones']),
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];

        if (empty($data['nombre_empresa'])) {
            $this->setFlash('error', 'El nombre de la empresa es obligatorio');
            $this->redirect('index.php?controller=proveedores&action=crear');
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO proveedores
                (nombre_empresa, nit, nombre_contacto, cargo_contacto, telefono, celular, email,
                 direccion, ciudad, pais, pagina_web, productos, observaciones, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute(array_values($data));
            
            $this->setFlash('success', 'Proveedor registrado correctamente');
            $this->redirect('index.php?controller=proveedores');
        } catch (PDOException $e) {
            $mensaje = APP_ENV === 'development' ? 'Error al registrar el proveedor: ' . $e->getMessage() : 'Error al registrar el proveedor';
            $this->setFlash('error', $mensaje);
            $this->redirect('index.php?controller=proveedores&action=crear');
        }
    }
    
    public function editar() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->setFlash('error', 'ID de proveedor no válido');
            $this->redirect('index.php?controller=proveedores');
        }
        
        $stmt = $this->db->prepare("SELECT * FROM proveedores WHERE id_proveedor = ?");
        $stmt->execute([$id]);
        $proveedor = $stmt->fetch();
        
        if (!$proveedor) {
            $this->setFlash('error', 'Proveedor no encontrado');
            $this->redirect('index.php?controller=proveedores');
        }
        
        $this->render('proveedores/form', [
            'title' => 'Editar Proveedor',
            'accion' => 'editar',
            'proveedor' => $proveedor
        ]);
    }
    
    public function actualizar() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=proveedores');
        }
        
        $id = $_POST['id_proveedor'] ?? null;
        
        if (!$id) {
            $this->setFlash('error', 'ID de proveedor no válido');
            $this->redirect('index.php?controller=proveedores');
        }
        
        $data = [
            'id_proveedor' => $id,
            'nombre_empresa' => $this->sanitize($_POST['nombre_empresa']),
            'nit' => $this->sanitize($_POST['nit']),
            'nombre_contacto' => $this->sanitize($_POST['nombre_contacto']),
            'cargo_contacto' => $this->sanitize($_POST['cargo_contacto']),
            'telefono' => $this->sanitize($_POST['telefono']),
            'celular' => $this->sanitize($_POST['celular']),
            'email' => $this->sanitize($_POST['email']),
            'direccion' => $this->sanitize($_POST['direccion']),
            'ciudad' => $this->sanitize($_POST['ciudad']),
            'pais' => $this->sanitize($_POST['pais']),
            'pagina_web' => $this->sanitize($_POST['pagina_web']),
            'productos' => $this->sanitize($_POST['productos']),
            'observaciones' => $this->sanitize($_POST['observaciones']),
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];

        try {
            $stmt = $this->db->prepare("
                UPDATE proveedores SET
                nombre_empresa = ?, nit = ?, nombre_contacto = ?, cargo_contacto = ?,
                telefono = ?, celular = ?, email = ?, direccion = ?, ciudad = ?, pais = ?,
                pagina_web = ?, productos = ?, observaciones = ?, activo = ?
                WHERE id_proveedor = ?
            ");

            $params = [
                $data['nombre_empresa'], $data['nit'], $data['nombre_contacto'], $data['cargo_contacto'],
                $data['telefono'], $data['celular'], $data['email'], $data['direccion'],
                $data['ciudad'], $data['pais'], $data['pagina_web'], $data['productos'],
                $data['observaciones'], $data['activo'], $data['id_proveedor']
            ];

            $stmt->execute($params);
            
            $this->setFlash('success', 'Proveedor actualizado correctamente');
            $this->redirect('index.php?controller=proveedores');
        } catch (PDOException $e) {
            $this->setFlash('error', 'Error al actualizar el proveedor');
            $this->redirect('index.php?controller=proveedores&action=editar&id=' . $id);
        }
    }
    
    public function eliminar() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->setFlash('error', 'ID de proveedor no válido');
            $this->redirect('index.php?controller=proveedores');
        }
        
        try {
            // Verificar si tiene equipos asociados
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM equipos WHERE id_proveedor = ?");
            $stmt->execute([$id]);
            $resultado = $stmt->fetch();
            
            if ($resultado['total'] > 0) {
                $this->setFlash('warning', 'No se puede eliminar: el proveedor tiene equipos asociados');
                $this->redirect('index.php?controller=proveedores');
                return;
            }
            
            // Desactivar en lugar de eliminar
            $stmt = $this->db->prepare("UPDATE proveedores SET activo = 0 WHERE id_proveedor = ?");
            $stmt->execute([$id]);
            
            $this->setFlash('success', 'Proveedor desactivado correctamente');
        } catch (PDOException $e) {
            $this->setFlash('error', 'Error al eliminar el proveedor');
        }
        
        $this->redirect('index.php?controller=proveedores');
    }
}
