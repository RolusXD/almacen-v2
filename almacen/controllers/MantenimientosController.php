<?php
/**
 * Controlador de Mantenimientos
 */
class MantenimientosController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
    }
    
    public function index() {
        $filtro = $this->sanitize($_GET['filtro'] ?? '');
        $estado = $this->sanitize($_GET['estado'] ?? 'todos');
        
        $sql = "SELECT m.*, e.nombre as equipo_nombre 
                FROM mantenimientos m 
                LEFT JOIN equipos e ON m.numero_serie_mac = e.numero_serie_mac
                WHERE 1=1";
        $params = [];
        
        if ($estado !== 'todos') {
            $sql .= " AND m.estado = ?";
            $params[] = $estado;
        }
        
        if (!empty($filtro)) {
            $sql .= " AND (m.numero_serie_mac LIKE ? OR m.tecnico_responsable LIKE ? OR m.descripcion LIKE ?)";
            $filtroParam = "%$filtro%";
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
        }
        
        $sql .= " ORDER BY m.fecha_ingreso DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $mantenimientos = $stmt->fetchAll();
        
        // Obtener equipos disponibles para el formulario
        $equiposStmt = $this->db->query("SELECT numero_serie_mac, nombre FROM equipos ORDER BY nombre");
        $equipos = $equiposStmt->fetchAll();
        
        $this->render('mantenimientos/index', [
            'title' => 'Mantenimientos',
            'mantenimientos' => $mantenimientos,
            'equipos' => $equipos,
            'filtro' => $filtro,
            'estado' => $estado
        ]);
    }
    
    public function registrar() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=mantenimientos');
        }
        
        $data = [
            'numero_serie_mac' => $this->sanitize($_POST['numero_serie_mac']),
            'tipo_mantenimiento' => $_POST['tipo_mantenimiento'],
            'descripcion' => $this->sanitize($_POST['descripcion']),
            'tecnico_responsable' => $this->sanitize($_POST['tecnico_responsable']),
            'fecha_ingreso' => $_POST['fecha_ingreso'],
            'fecha_entrega' => $_POST['fecha_entrega'] ?: null,
            'costo' => $_POST['costo'] ?: null,
            'repuestos_cambiados' => $this->sanitize($_POST['repuestos_cambiados']),
            'observaciones' => $this->sanitize($_POST['observaciones']),
            'garantia_dias' => (int)($_POST['garantia_dias'] ?? 0),
            'created_by' => $_SESSION['user_id'] ?? null
        ];
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO mantenimientos 
                (numero_serie_mac, tipo_mantenimiento, descripcion, tecnico_responsable, 
                 fecha_ingreso, fecha_entrega, costo, repuestos_cambiados, observaciones, 
                 garantia_dias, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['numero_serie_mac'], $data['tipo_mantenimiento'], $data['descripcion'],
                $data['tecnico_responsable'], $data['fecha_ingreso'], $data['fecha_entrega'],
                $data['costo'], $data['repuestos_cambiados'], $data['observaciones'],
                $data['garantia_dias'], $data['created_by']
            ]);
            
            // Actualizar estado del equipo
            $this->db->prepare("UPDATE equipos SET estado = 'MANTENIMIENTO' WHERE numero_serie_mac = ?")
                ->execute([$data['numero_serie_mac']]);
            
            $this->setFlash('success', 'Mantenimiento registrado correctamente');
        } catch (PDOException $e) {
            $mensaje = APP_ENV === 'development' ? 'Error al registrar: ' . $e->getMessage() : 'Error al registrar';
            $this->setFlash('error', $mensaje);
        }
        
        $this->redirect('index.php?controller=mantenimientos');
    }
    
    public function actualizar_estado() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=mantenimientos');
        }
        
        $id = $_POST['id_mantenimiento'];
        $estado = $_POST['estado'];
        $fecha_entrega = $_POST['fecha_entrega'] ?? null;
        $observaciones = $this->sanitize($_POST['observaciones_devolucion']);
        
        try {
            $stmt = $this->db->prepare("
                UPDATE mantenimientos 
                SET estado = ?,
                    fecha_entrega = ?,
                    observaciones = COALESCE(observaciones, '') || ?,
                    updated_at = NOW()
                WHERE id_mantenimiento = ?
            ");
            $stmt->execute([$estado, $fecha_entrega, "\n[" . date('Y-m-d H:i') . "] " . $observaciones, $id]);
            
            // Si está completado, actualizar equipo
            if ($estado === 'COMPLETADO') {
                $stmt = $this->db->prepare("SELECT numero_serie_mac FROM mantenimientos WHERE id_mantenimiento = ?");
                $stmt->execute([$id]);
                $mant = $stmt->fetch();
                
                if ($mant) {
                    $this->db->prepare("UPDATE equipos SET estado = 'DISPONIBLE' WHERE numero_serie_mac = ?")
                        ->execute([$mant['numero_serie_mac']]);
                }
            }
            
            $this->setFlash('success', 'Estado actualizado correctamente');
        } catch (PDOException $e) {
            $this->setFlash('error', 'Error al actualizar');
        }
        
        $this->redirect('index.php?controller=mantenimientos');
    }
    
    public function eliminar() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->setFlash('error', 'ID no válido');
            $this->redirect('index.php?controller=mantenimientos');
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM mantenimientos WHERE id_mantenimiento = ?");
            $stmt->execute([$id]);
            $this->setFlash('success', 'Mantenimiento eliminado correctamente');
        } catch (PDOException $e) {
            $this->setFlash('error', 'Error al eliminar');
        }
        
        $this->redirect('index.php?controller=mantenimientos');
    }
}
