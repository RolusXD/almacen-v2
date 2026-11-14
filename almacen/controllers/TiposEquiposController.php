<?php
/**
 * Controlador de Tipos de Equipos
 * Gestiona CRUD de tipos/categorías de equipos
 */
class TiposEquiposController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
    }

    public function index() {
        $tipoModel = new TipoEquipo();
        $filtro = $this->sanitize($_GET['filtro'] ?? '');
        $categoria = $this->sanitize($_GET['categoria'] ?? '');

        $tipos = $tipoModel->buscar($filtro, $categoria);
        $categorias = $tipoModel->obtenerCategorias();
        $estadisticas = $tipoModel->estadisticasUso();

        $this->render('tipos_equipos/index', [
            'title' => 'Tipos de Equipos',
            'tipos' => $tipos,
            'categorias' => $categorias,
            'filtro' => $filtro,
            'categoria' => $categoria,
            'estadisticas' => $estadisticas
        ]);
    }

    public function crear() {
        $this->render('tipos_equipos/form', [
            'title' => 'Nuevo Tipo de Equipo',
            'accion' => 'crear',
            'tipo' => null
        ]);
    }

    public function guardar() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=tipos_equipos');
        }

        $tipoModel = new TipoEquipo();

        $data = [
            'codigo' => strtoupper($this->sanitize($_POST['codigo'])),
            'descripcion' => $this->sanitize($_POST['descripcion']),
            'categoria' => $_POST['categoria'],
            'prefijo' => strtoupper($this->sanitize($_POST['prefijo'])),
            'observaciones' => $this->sanitize($_POST['observaciones'])
        ];

        // Validaciones
        if (empty($data['codigo']) || empty($data['descripcion'])) {
            $this->setFlash('error', 'El código y la descripción son obligatorios');
            $this->redirect('index.php?controller=tipos_equipos&action=crear');
        }

        $resultado = $tipoModel->registrar($data);

        if ($resultado['success']) {
            $this->setFlash('success', $resultado['message']);
            $this->redirect('index.php?controller=tipos_equipos');
        } else {
            $this->setFlash('error', $resultado['message']);
            $this->redirect('index.php?controller=tipos_equipos&action=crear');
        }
    }

    public function editar() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->setFlash('error', 'ID de tipo no válido');
            $this->redirect('index.php?controller=tipos_equipos');
        }

        $tipoModel = new TipoEquipo();
        $tipo = $tipoModel->find($id);

        if (!$tipo) {
            $this->setFlash('error', 'Tipo de equipo no encontrado');
            $this->redirect('index.php?controller=tipos_equipos');
        }

        $this->render('tipos_equipos/form', [
            'title' => 'Editar Tipo de Equipo',
            'accion' => 'editar',
            'tipo' => $tipo
        ]);
    }

    public function actualizar() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=tipos_equipos');
        }

        $id = $_POST['id_codigo'] ?? null;

        if (!$id) {
            $this->setFlash('error', 'ID de tipo no válido');
            $this->redirect('index.php?controller=tipos_equipos');
        }

        $tipoModel = new TipoEquipo();

        $data = [
            'codigo' => strtoupper($this->sanitize($_POST['codigo'])),
            'descripcion' => $this->sanitize($_POST['descripcion']),
            'categoria' => $_POST['categoria'],
            'prefijo' => strtoupper($this->sanitize($_POST['prefijo'])),
            'observaciones' => $this->sanitize($_POST['observaciones'])
        ];

        $resultado = $tipoModel->actualizar($id, $data);

        if ($resultado) {
            $this->setFlash('success', 'Tipo de equipo actualizado correctamente');
        } else {
            $this->setFlash('error', 'Error al actualizar el tipo de equipo');
        }

        $this->redirect('index.php?controller=tipos_equipos');
    }

    public function eliminar() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->setFlash('error', 'ID de tipo no válido');
            $this->redirect('index.php?controller=tipos_equipos');
        }

        $tipoModel = new TipoEquipo();
        $resultado = $tipoModel->eliminar($id);

        if ($resultado['success']) {
            $this->setFlash('success', $resultado['message']);
        } else {
            $this->setFlash('error', $resultado['message']);
        }

        $this->redirect('index.php?controller=tipos_equipos');
    }

    public function toggle_activo() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->setFlash('error', 'ID de tipo no válido');
            $this->redirect('index.php?controller=tipos_equipos');
        }

        $tipoModel = new TipoEquipo();
        $tipoModel->toggleActivo($id);

        $this->redirect('index.php?controller=tipos_equipos');
    }
}
