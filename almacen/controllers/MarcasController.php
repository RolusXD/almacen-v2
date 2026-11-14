<?php
/**
 * Controlador de Marcas
 */
class MarcasController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
    }
    
    public function index() {
        $filtro = $this->sanitize($_GET['filtro'] ?? '');
        
        try {
            $sql = "SELECT m.*, 
                    (SELECT COUNT(*) FROM equipos WHERE id_marca = m.id_marca) as total_equipos,
                    (SELECT COUNT(*) FROM accesorios WHERE id_marca = m.id_marca) as total_accesorios
                    FROM marcas m WHERE 1=1";
            $params = [];
            
            if (!empty($filtro)) {
                $sql .= " AND (m.nombre LIKE ? OR m.pais_origen LIKE ?)";
                $filtroParam = "%$filtro%";
                $params[] = $filtroParam;
                $params[] = $filtroParam;
            }
            
            $sql .= " ORDER BY m.nombre ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $marcas = $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->setFlash('error', 'Error: La tabla de marcas no existe. Ejecute el script database/nuevas_tablas.sql');
            $marcas = [];
        }

        $this->render('marcas/index', [
            'title' => 'Marcas',
            'marcas' => $marcas,
            'filtro' => $filtro
        ]);
    }
    
    public function guardar() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=marcas');
        }
        
        $nombre = $this->sanitize($_POST['nombre']);
        $pais = $this->sanitize($_POST['pais_origen']);
        $web = $this->sanitize($_POST['pagina_web']);
        $activo = isset($_POST['activo']) ? 1 : 0;
        
        if (empty($nombre)) {
            $this->setFlash('error', 'El nombre es obligatorio');
            $this->redirect('index.php?controller=marcas');
        }
        
        try {
            $stmt = $this->db->prepare("INSERT INTO marcas (nombre, pais_origen, pagina_web, activo) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $pais, $web, $activo]);
            $this->setFlash('success', 'Marca registrada correctamente');
        } catch (PDOException $e) {
            $this->setFlash('error', 'Error: El nombre ya existe');
        }
        
        $this->redirect('index.php?controller=marcas');
    }
    
    public function eliminar() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->setFlash('error', 'ID no válido');
            $this->redirect('index.php?controller=marcas');
        }
        
        try {
            $stmt = $this->db->prepare("UPDATE marcas SET activo = 0 WHERE id_marca = ?");
            $stmt->execute([$id]);
            $this->setFlash('success', 'Marca desactivada correctamente');
        } catch (PDOException $e) {
            $this->setFlash('error', 'Error al desactivar');
        }
        
        $this->redirect('index.php?controller=marcas');
    }
}
