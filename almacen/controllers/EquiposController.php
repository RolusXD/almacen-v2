<?php
/**
 * Controlador de Equipos
 */
class EquiposController extends Controller {
    private function leerArchivoCsv($ruta) {
        $filas = [];

        if (($handle = fopen($ruta, 'r')) === false) {
            return $filas;
        }

        while (($fila = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($fila) === 1 && $fila[0] === null) {
                continue;
            }

            $filas[] = array_map(static function ($valor) {
                return is_string($valor) ? trim($valor) : $valor;
            }, $fila);
        }

        fclose($handle);
        return $filas;
    }

    private function esFilaCabecera(array $fila) {
        $texto = mb_strtolower(implode(' ', $fila), 'UTF-8');
        return str_contains($texto, 'serie') || str_contains($texto, 'codigo') || str_contains($texto, 'nombre');
    }

    public function index() {
        $equipoModel = new Equipo();
        $filtro = $this->sanitize($_GET['filtro'] ?? '');
        $estado = $this->sanitize($_GET['estado'] ?? 'todos');
        $calidad = $this->sanitize($_GET['calidad'] ?? 'todas');
        $fecha_desde = $_GET['fecha_desde'] ?? '';
        $fecha_hasta = $_GET['fecha_hasta'] ?? '';
        $numero_serie = $this->sanitize($_GET['numero_serie'] ?? '');

        $equipos = $equipoModel->buscar($filtro, $estado, $calidad, $fecha_desde, $fecha_hasta, $numero_serie);

        $this->render('equipos/index', [
            'title' => 'Equipos',
            'equipos' => $equipos,
            'filtro' => $filtro,
            'estado' => $estado,
            'calidad' => $calidad,
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta,
            'numero_serie' => $numero_serie
        ]);
    }

    public function crear() {
        $tipoModel = new TipoEquipo();
        $equipoModel = new Equipo();
        $tipos = $tipoModel->obtenerActivos();
        $modelosReferencia = $equipoModel->obtenerModelosReferencia();

        $this->render('equipos/form', [
            'title' => 'Nuevo Equipo',
            'accion' => 'crear',
            'equipo' => null,
            'codigos' => $tipos,
            'modelosReferencia' => $modelosReferencia
        ]);
    }

    public function guardar() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=equipos');
        }

        $equipoModel = new Equipo();
        $tipoModel = new TipoEquipo();

        // Obtener el código del tipo seleccionado
        $idTipo = $_POST['id_tipo_equipo'] ?? null;
        $tipo = $idTipo ? $tipoModel->find($idTipo) : null;

        $data = [
            'nombre' => $this->sanitize($_POST['nombre']),
            'numero_serie_mac' => $this->sanitize($_POST['numero_serie_mac']),
            'id_tipo_equipo' => $idTipo,
            'codigo' => $tipo ? $tipo['codigo'] : $this->sanitize($_POST['codigo']),
            'modelo' => $this->sanitize($_POST['modelo']),
            'marca' => $this->sanitize($_POST['marca']),
            'fecha_entrada' => $_POST['fecha_entrada'],
            'calidad' => $_POST['calidad'],
            'observaciones' => $this->sanitize($_POST['observaciones']),
            'precio' => !empty($_POST['precio']) ? floatval($_POST['precio']) : null,
            'proveedor' => $this->sanitize($_POST['proveedor']) ?? null
        ];

        // Validaciones
        if (empty($data['nombre']) || empty($data['numero_serie_mac'])) {
            $this->setFlash('error', 'El nombre y número de serie son obligatorios');
            $this->redirect('index.php?controller=equipos&action=crear');
        }

        $resultado = $equipoModel->registrar($data);

        if ($resultado['success']) {
            $this->setFlash('success', $resultado['message']);
            $this->redirect('index.php?controller=equipos');
        } else {
            $this->setFlash('error', $resultado['message']);
            $this->redirect('index.php?controller=equipos&action=crear');
        }
    }

    public function editar() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->setFlash('error', 'ID de equipo no válido');
            $this->redirect('index.php?controller=equipos');
        }

        $equipoModel = new Equipo();
        $tipoModel = new TipoEquipo();
        $equipo = $equipoModel->find($id);
        $tipos = $tipoModel->obtenerActivos();
        $modelosReferencia = $equipoModel->obtenerModelosReferencia();

        if (!$equipo) {
            $this->setFlash('error', 'Equipo no encontrado');
            $this->redirect('index.php?controller=equipos');
        }

        $this->render('equipos/form', [
            'title' => 'Editar Equipo',
            'accion' => 'editar',
            'equipo' => $equipo,
            'codigos' => $tipos,
            'modelosReferencia' => $modelosReferencia
        ]);
    }

    public function actualizar() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=equipos');
        }

        $id = $_POST['id_equipo'] ?? null;

        if (!$id) {
            $this->setFlash('error', 'ID de equipo no válido');
            $this->redirect('index.php?controller=equipos');
        }

        $equipoModel = new Equipo();
        $tipoModel = new TipoEquipo();

        // Obtener el código del tipo seleccionado
        $idTipo = $_POST['id_tipo_equipo'] ?? null;
        $tipo = $idTipo ? $tipoModel->find($idTipo) : null;

        $data = [
            'nombre' => $this->sanitize($_POST['nombre']),
            'id_tipo_equipo' => $idTipo,
            'codigo' => $tipo ? $tipo['codigo'] : $this->sanitize($_POST['codigo']),
            'modelo' => $this->sanitize($_POST['modelo']),
            'marca' => $this->sanitize($_POST['marca']),
            'fecha_entrada' => $_POST['fecha_entrada'],
            'calidad' => $_POST['calidad'],
            'observaciones' => $this->sanitize($_POST['observaciones']),
            'precio' => !empty($_POST['precio']) ? floatval($_POST['precio']) : null,
            'proveedor' => $this->sanitize($_POST['proveedor']) ?? null
        ];

        $equipoModel->actualizar($id, $data);

        $this->setFlash('success', 'Equipo actualizado correctamente');
        $this->redirect('index.php?controller=equipos');
    }

    public function eliminar() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->setFlash('error', 'ID de equipo no válido');
            $this->redirect('index.php?controller=equipos');
        }

        $equipoModel = new Equipo();
        $resultado = $equipoModel->eliminar($id);

        if ($resultado['success']) {
            $this->setFlash('success', $resultado['message']);
        } else {
            $this->setFlash('error', $resultado['message']);
        }

        $this->redirect('index.php?controller=equipos');
    }

    /**
     * Exportar a Excel/CSV
     */
    public function exportar() {
        $equipoModel = new Equipo();
        $filtro = $this->sanitize($_GET['filtro'] ?? '');
        $estado = $this->sanitize($_GET['estado'] ?? 'todos');
        $calidad = $this->sanitize($_GET['calidad'] ?? 'todas');
        $fechaDesde = $_GET['fecha_desde'] ?? '';
        $fechaHasta = $_GET['fecha_hasta'] ?? '';
        $numeroSerie = $this->sanitize($_GET['numero_serie'] ?? '');

        $equipos = $equipoModel->buscar($filtro, $estado, $calidad, $fechaDesde, $fechaHasta, $numeroSerie);

        $formato = $_GET['formato'] ?? 'excel';

        if ($formato === 'csv') {
            $this->exportarCSV($equipos);
        } else {
            $this->exportarExcel($equipos);
        }
    }

    /**
     * Exportar a Excel (formato .xls simple)
     */
    private function exportarExcel($data) {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="Equipos_' . date('Y-m-d') . '.xls"');

        echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'>";
        echo "<head><meta charset='UTF-8'></head><body><table border='1'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th><th>Nombre</th><th>N° Serie/MAC</th><th>Código</th><th>Modelo</th><th>Marca</th><th>Fecha Entrada</th><th>Estado</th><th>Calidad</th><th>Precio</th><th>Proveedor</th><th>Observaciones</th>";
        echo "</tr>";
        echo "</thead><tbody>";

        foreach ($data as $equipo) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars((string)$equipo['id_equipo'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)$equipo['nombre'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)$equipo['numero_serie_mac'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)($equipo['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)($equipo['modelo'] ?? ''), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)($equipo['marca'] ?? ''), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)$equipo['fecha_entrada'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)$equipo['estado'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)$equipo['calidad'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)($equipo['precio'] ?? ''), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)($equipo['proveedor'] ?? ''), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)($equipo['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "</tr>";
        }

        echo "</tbody></table></body></html>";
        exit;
    }

    /**
     * Exportar a CSV
     */
    private function exportarCSV($data) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Equipos_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // BOM para Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Encabezados
        fputcsv($output, ['ID', 'Nombre', 'N° Serie/MAC', 'Código', 'Modelo', 'Marca', 'Fecha Entrada', 'Estado', 'Calidad', 'Precio', 'Proveedor', 'Observaciones']);

        // Datos
        foreach ($data as $equipo) {
            fputcsv($output, [
                $equipo['id_equipo'],
                $equipo['nombre'],
                $equipo['numero_serie_mac'],
                $equipo['codigo'] ?? '',
                $equipo['modelo'] ?? '',
                $equipo['marca'] ?? '',
                $equipo['fecha_entrada'],
                $equipo['estado'],
                $equipo['calidad'],
                $equipo['precio'] ?? '',
                $equipo['proveedor'] ?? '',
                $equipo['observaciones'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Importar desde Excel/CSV
     */
    public function importar() {
        if (!$this->isPost() || !isset($_FILES['archivo'])) {
            $this->setFlash('error', 'No se ha seleccionado ningún archivo');
            $this->redirect('index.php?controller=equipos');
        }

        $archivo = $_FILES['archivo']['tmp_name'];
        $extension = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);

        if (strtolower($extension) !== 'csv') {
            $this->setFlash('error', 'La importación solo acepta archivos CSV con comas. Descargue la plantilla oficial y vuelva a intentarlo.');
            $this->redirect('index.php?controller=equipos');
        }

        $equiposModel = new Equipo();
        $contador = 0;
        $errores = [];

        $filas = $this->leerArchivoCsv($archivo);

        if (!empty($filas) && $this->esFilaCabecera($filas[0])) {
            array_shift($filas);
        }

        foreach ($filas as $indice => $data) {
            if (count(array_filter($data, static fn ($valor) => $valor !== null && $valor !== '')) === 0) {
                continue;
            }

            if (count($data) < 3) {
                $errores[] = 'Fila ' . ($indice + 2) . ': no tiene suficientes columnas.';
                continue;
            }

            try {
                $resultado = $equiposModel->registrar([
                    'nombre' => $data[0] ?? '',
                    'numero_serie_mac' => $data[1] ?? '',
                    'codigo' => $data[2] ?? null,
                    'modelo' => $data[3] ?? null,
                    'marca' => $data[4] ?? null,
                    'fecha_entrada' => $data[5] ?? date('Y-m-d'),
                    'calidad' => $data[6] ?? 'BUENO',
                    'observaciones' => $data[7] ?? null,
                    'precio' => isset($data[8]) && $data[8] !== '' ? floatval(str_replace(',', '.', $data[8])) : null,
                    'proveedor' => $data[9] ?? null
                ]);
            } catch (Throwable $e) {
                $resultado = [
                    'success' => false,
                    'message' => APP_ENV === 'development'
                        ? $e->getMessage()
                        : 'No se pudo registrar el equipo'
                ];
            }

            if ($resultado['success']) {
                $contador++;
            } else {
                $errores[] = 'Fila ' . ($indice + 2) . ': ' . $resultado['message'];
            }
        }

        if ($contador > 0) {
            $this->setFlash('success', "Se importaron {$contador} equipo(s) correctamente");
        }

        if (!empty($errores)) {
            $this->setFlash('warning', 'Errores: ' . implode(', ', array_slice($errores, 0, 5)));
        }

        $this->redirect('index.php?controller=equipos');
    }

    /**
     * Buscar por número de serie (AJAX)
     */
    public function buscarPorSerie() {
        if (!$this->isPost()) {
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $numeroSerie = $this->sanitize($_POST['numero_serie'] ?? '');
        $equipoModel = new Equipo();

        if (empty($numeroSerie)) {
            echo json_encode(['error' => 'Número de serie requerido']);
            return;
        }

        $equipos = $equipoModel->buscarPorNumeroSerie($numeroSerie);

        echo json_encode([
            'success' => true,
            'data' => $equipos
        ]);
    }

    public function referenciaModelo() {
        $modelo = $this->sanitize($_GET['modelo'] ?? '');

        if ($modelo === '') {
            $this->renderJson(['success' => false, 'message' => 'Modelo requerido']);
            return;
        }

        $equipoModel = new Equipo();
        $referencia = $equipoModel->referenciaPorModelo($modelo);

        $this->renderJson([
            'success' => (bool)$referencia,
            'data' => $referencia ?: null
        ]);
    }
}
