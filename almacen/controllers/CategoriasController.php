<?php
/**
 * Controlador de Categorías
 */
class CategoriasController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
    }
    
    public function index() {
        $filtro = $this->sanitize($_GET['filtro'] ?? '');
        
        try {
            $sql = "SELECT c.*, 
                    (SELECT COUNT(*) FROM equipos WHERE id_categoria = c.id_categoria) as total_equipos,
                    (SELECT COUNT(*) FROM accesorios WHERE id_categoria = c.id_categoria) as total_accesorios
                    FROM categorias c WHERE 1=1";
            $params = [];
            
            if (!empty($filtro)) {
                $sql .= " AND (c.nombre LIKE ? OR c.descripcion LIKE ?)";
                $filtroParam = "%$filtro%";
                $params[] = $filtroParam;
                $params[] = $filtroParam;
            }
            
            $sql .= " ORDER BY c.nombre ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $categorias = $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->setFlash('error', 'Error: La tabla de categorías no existe. Ejecute el script database/nuevas_tablas.sql');
            $categorias = [];
        }

        try {
            $marcasStmt = $this->db->query("
                SELECT m.*,
                    (SELECT COUNT(*) FROM equipos WHERE id_marca = m.id_marca) as total_equipos,
                    (SELECT COUNT(*) FROM accesorios WHERE id_marca = m.id_marca) as total_accesorios
                FROM marcas m
                ORDER BY m.nombre ASC
            ");
            $marcas = $marcasStmt->fetchAll();
        } catch (PDOException $e) {
            $marcas = [];
        }

        $this->render('categorias/index', [
            'title' => 'Categorías',
            'categorias' => $categorias,
            'marcas' => $marcas,
            'filtro' => $filtro
        ]);
    }
    
    public function guardar() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=categorias');
        }
        
        $nombre = $this->sanitize($_POST['nombre']);
        $descripcion = $this->sanitize($_POST['descripcion']);
        $activo = isset($_POST['activo']) ? 1 : 0;
        
        if (empty($nombre)) {
            $this->setFlash('error', 'El nombre es obligatorio');
            $this->redirect('index.php?controller=categorias');
        }
        
        try {
            $stmt = $this->db->prepare("INSERT INTO categorias (nombre, descripcion, activo) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $descripcion, $activo]);
            $this->setFlash('success', 'Categoría registrada correctamente');
        } catch (PDOException $e) {
            $this->setFlash('error', 'Error: El nombre ya existe');
        }
        
        $this->redirect('index.php?controller=categorias');
    }
    
    public function eliminar() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->setFlash('error', 'ID no válido');
            $this->redirect('index.php?controller=categorias');
        }
        
        try {
            $stmt = $this->db->prepare("UPDATE categorias SET activo = 0 WHERE id_categoria = ?");
            $stmt->execute([$id]);
            $this->setFlash('success', 'Categoría desactivada correctamente');
        } catch (PDOException $e) {
            $this->setFlash('error', 'Error al desactivar');
        }
        
        $this->redirect('index.php?controller=categorias');
    }
}
