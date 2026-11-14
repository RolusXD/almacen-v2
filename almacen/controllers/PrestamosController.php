<?php
/**
 * Controlador de Préstamos de Equipos
 */
class PrestamosController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
    }
    
    public function index() {
        $filtro = $this->sanitize($_GET['filtro'] ?? '');
        $estado = $this->sanitize($_GET['estado'] ?? 'todos');
        
        $sql = "SELECT p.*, e.nombre as equipo_nombre, u.nombre_completo as usuario_nombre
                FROM prestamos p 
                LEFT JOIN equipos e ON p.numero_serie_mac = e.numero_serie_mac
                LEFT JOIN usuarios u ON p.id_usuario_solicita = u.id_usuario
                WHERE 1=1";
        $params = [];
        
        if ($estado !== 'todos') {
            $sql .= " AND p.estado = ?";
            $params[] = $estado;
        }
        
        if (!empty($filtro)) {
            $sql .= " AND (p.numero_serie_mac LIKE ? OR p.nombre_solicita LIKE ? OR p.departamento LIKE ?)";
            $filtroParam = "%$filtro%";
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
        }
        
        $sql .= " ORDER BY p.fecha_prestamo DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $prestamos = $stmt->fetchAll();
        
        // Obtener equipos disponibles
        $equiposStmt = $this->db->query("SELECT numero_serie_mac, nombre FROM equipos WHERE estado = 'DISPONIBLE' ORDER BY nombre");
        $equipos = $equiposStmt->fetchAll();
        
        // Obtener usuarios
        $usuariosStmt = $this->db->query("SELECT id_usuario, nombre_completo FROM usuarios WHERE activo = 1 ORDER BY nombre_completo");
        $usuarios = $usuariosStmt->fetchAll();
        
        // Contar préstamos vencidos
        $vencidosStmt = $this->db->query("SELECT COUNT(*) as total FROM prestamos WHERE estado = 'PRESTADO' AND fecha_devolucion_esperada < CURRENT_DATE");
        $vencidos = $vencidosStmt->fetch()['total'];
        
        $this->render('prestamos/index', [
            'title' => 'Préstamos de Equipos',
            'prestamos' => $prestamos,
            'equipos' => $equipos,
            'usuarios' => $usuarios,
            'filtro' => $filtro,
            'estado' => $estado,
            'vencidos' => $vencidos
        ]);
    }
    
    public function registrar() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=prestamos');
        }
        
        $data = [
            'numero_serie_mac' => $this->sanitize($_POST['numero_serie_mac']),
            'id_usuario_solicita' => $_POST['id_usuario_solicita'] ?: null,
            'nombre_solicita' => $this->sanitize($_POST['nombre_solicita']),
            'cargo_solicita' => $this->sanitize($_POST['cargo_solicita']),
            'departamento' => $this->sanitize($_POST['departamento']),
            'telefono_contacto' => $this->sanitize($_POST['telefono_contacto']),
            'email_contacto' => $this->sanitize($_POST['email_contacto']),
            'fecha_prestamo' => $_POST['fecha_prestamo'],
            'fecha_devolucion_esperada' => $_POST['fecha_devolucion_esperada'],
            'motivo_prestamo' => $this->sanitize($_POST['motivo_prestamo']),
            'observaciones_salida' => $this->sanitize($_POST['observaciones_salida']),
            'created_by' => $_SESSION['user_id'] ?? null
        ];
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO prestamos 
                (numero_serie_mac, id_usuario_solicita, nombre_solicita, cargo_solicita, departamento,
                 telefono_contacto, email_contacto, fecha_prestamo, fecha_devolucion_esperada,
                 motivo_prestamo, observaciones_salida, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['numero_serie_mac'], $data['id_usuario_solicita'], $data['nombre_solicita'],
                $data['cargo_solicita'], $data['departamento'], $data['telefono_contacto'],
                $data['email_contacto'], $data['fecha_prestamo'], $data['fecha_devolucion_esperada'],
                $data['motivo_prestamo'], $data['observaciones_salida'], $data['created_by']
            ]);
            
            $this->setFlash('success', 'Préstamo registrado correctamente. El equipo fue marcado como OCUPADO.');
        } catch (PDOException $e) {
            $mensaje = APP_ENV === 'development' ? 'Error al registrar: ' . $e->getMessage() : 'Error al registrar';
            $this->setFlash('error', $mensaje);
        }
        
        $this->redirect('index.php?controller=prestamos');
    }
    
    public function devolver() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=prestamos');
        }
        
        $id = $_POST['id_prestamo'];
        $fecha_devolucion_real = $_POST['fecha_devolucion_real'];
        $observaciones = $this->sanitize($_POST['observaciones_devolucion']);
        $equipo_conforme = isset($_POST['equipo_conforme']) ? 1 : 0;
        
        try {
            $stmt = $this->db->prepare("
                UPDATE prestamos 
                SET estado = 'DEVUELTO', 
                    fecha_devolucion_real = ?,
                    observaciones_devolucion = ?,
                    equipo_recibido_conforme = ?,
                    id_usuario_recibe = ?,
                    updated_at = NOW()
                WHERE id_prestamo = ?
            ");
            $stmt->execute([
                $fecha_devolucion_real, 
                $observaciones, 
                $equipo_conforme,
                $_SESSION['user_id'] ?? null,
                $id
            ]);
            
            $this->setFlash('success', 'Devolución registrada correctamente. El equipo fue marcado como DISPONIBLE.');
        } catch (PDOException $e) {
            $this->setFlash('error', 'Error al registrar devolución');
        }
        
        $this->redirect('index.php?controller=prestamos');
    }
    
    public function eliminar() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->setFlash('error', 'ID no válido');
            $this->redirect('index.php?controller=prestamos');
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM prestamos WHERE id_prestamo = ?");
            $stmt->execute([$id]);
            $this->setFlash('success', 'Préstamo eliminado correctamente');
        } catch (PDOException $e) {
            $this->setFlash('error', 'Error al eliminar');
        }
        
        $this->redirect('index.php?controller=prestamos');
    }
}
