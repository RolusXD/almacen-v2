<?php
/**
 * Controlador de Accesorios
 */
class AccesoriosController extends Controller {
    private function obtenerProveedoresActivos() {
        try {
            $stmt = $this->db->query("
                SELECT id_proveedor, nombre_empresa
                FROM proveedores
                WHERE activo = 1
                ORDER BY nombre_empresa ASC
            ");
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    public function index() {
        $accesorioModel = new Accesorio();
        $filtro = $this->sanitize($_GET['filtro'] ?? '');
        $codigo = $this->sanitize($_GET['codigo'] ?? '');
        $unidad_medida = $this->sanitize($_GET['unidad_medida'] ?? '');
        $estado = $this->sanitize($_GET['estado'] ?? '');

        $accesorios = $accesorioModel->buscar($filtro, $codigo, $unidad_medida, $estado);
        $unidadesMedida = $accesorioModel->obtenerUnidadesMedida();
        $codigosUnicos = $accesorioModel->obtenerCodigosUnicos();

        $this->render('accesorios/index', [
            'title' => 'Accesorios',
            'accesorios' => $accesorios,
            'filtro' => $filtro,
            'codigo' => $codigo,
            'unidad_medida' => $unidad_medida,
            'estado' => $estado,
            'unidadesMedida' => $unidadesMedida,
            'codigosUnicos' => $codigosUnicos
        ]);
    }

    public function crear() {
        $tipoAccesorioModel = new TipoAccesorio();
        $unidades = ['METROS', 'UNIDADES', 'CAJAS', 'KILOS', 'LITROS', 'PARES'];
        $tiposAccesorios = $tipoAccesorioModel->obtenerActivos();
        $proveedores = $this->obtenerProveedoresActivos();

        $this->render('accesorios/form', [
            'title' => 'Nuevo Accesorio',
            'accion' => 'crear',
            'accesorio' => null,
            'unidades' => $unidades,
            'tiposAccesorios' => $tiposAccesorios,
            'proveedores' => $proveedores
        ]);
    }

    public function guardar() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=accesorios');
        }

        $accesorioModel = new Accesorio();

        $data = [
            'nombre' => $this->sanitize($_POST['nombre']),
            'codigo' => $this->sanitize($_POST['codigo']) ?? null,
            'cantidad' => (int)$_POST['cantidad'],
            'unidad_medida' => $_POST['unidad_medida'] ?? null,
            'modelo' => $this->sanitize($_POST['modelo']),
            'marca' => $this->sanitize($_POST['marca']),
            'numero_serie_mac' => $this->sanitize($_POST['numero_serie_mac']),
            'fecha_entrada' => $_POST['fecha_entrada'],
            'calidad' => $_POST['calidad'],
            'costo' => isset($_POST['costo']) && $_POST['costo'] !== '' ? (float)$_POST['costo'] : null,
            'id_proveedor' => !empty($_POST['id_proveedor']) ? (int)$_POST['id_proveedor'] : null,
            'observaciones' => $this->sanitize($_POST['observaciones'])
        ];

        // Validaciones
        if (empty($data['nombre']) || $data['cantidad'] <= 0) {
            $this->setFlash('error', 'El nombre y cantidad son obligatorios');
            $this->redirect('index.php?controller=accesorios&action=crear');
        }

        $accesorioModel->registrar($data);

        $this->setFlash('success', 'Accesorio registrado correctamente');
        $this->redirect('index.php?controller=accesorios');
    }

    public function editar() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->setFlash('error', 'ID de accesorio no válido');
            $this->redirect('index.php?controller=accesorios');
        }

        $accesorioModel = new Accesorio();
        $accesorio = $accesorioModel->find($id);
        $tipoAccesorioModel = new TipoAccesorio();
        $unidades = ['METROS', 'UNIDADES', 'CAJAS', 'KILOS', 'LITROS', 'PARES'];
        $tiposAccesorios = $tipoAccesorioModel->obtenerActivos();
        $proveedores = $this->obtenerProveedoresActivos();

        if (!$accesorio) {
            $this->setFlash('error', 'Accesorio no encontrado');
            $this->redirect('index.php?controller=accesorios');
        }

        $this->render('accesorios/form', [
            'title' => 'Editar Accesorio',
            'accion' => 'editar',
            'accesorio' => $accesorio,
            'unidades' => $unidades,
            'tiposAccesorios' => $tiposAccesorios,
            'proveedores' => $proveedores
        ]);
    }

    public function actualizar() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=accesorios');
        }

        $id = $_POST['id_accesorio'] ?? null;

        if (!$id) {
            $this->setFlash('error', 'ID de accesorio no válido');
            $this->redirect('index.php?controller=accesorios');
        }

        $accesorioModel = new Accesorio();

        $data = [
            'nombre' => $this->sanitize($_POST['nombre']),
            'codigo' => $this->sanitize($_POST['codigo']) ?? null,
            'cantidad' => (int)$_POST['cantidad'],
            'unidad_medida' => $_POST['unidad_medida'] ?? null,
            'modelo' => $this->sanitize($_POST['modelo']),
            'marca' => $this->sanitize($_POST['marca']),
            'numero_serie_mac' => $this->sanitize($_POST['numero_serie_mac']),
            'calidad' => $_POST['calidad'],
            'costo' => isset($_POST['costo']) && $_POST['costo'] !== '' ? (float)$_POST['costo'] : null,
            'id_proveedor' => !empty($_POST['id_proveedor']) ? (int)$_POST['id_proveedor'] : null,
            'observaciones' => $this->sanitize($_POST['observaciones'])
        ];

        $accesorioModel->update($id, $data);

        $this->setFlash('success', 'Accesorio actualizado correctamente');
        $this->redirect('index.php?controller=accesorios');
    }

    public function eliminar() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->setFlash('error', 'ID de accesorio no válido');
            $this->redirect('index.php?controller=accesorios');
        }

        $accesorioModel = new Accesorio();
        $accesorioModel->delete($id);

        $this->setFlash('success', 'Accesorio eliminado correctamente');
        $this->redirect('index.php?controller=accesorios');
    }

    /**
     * Exportar a Excel/CSV
     */
    public function exportar() {
        $accesorioModel = new Accesorio();
        $filtro = $this->sanitize($_GET['filtro'] ?? '');
        $codigo = $this->sanitize($_GET['codigo'] ?? '');
        $estado = $this->sanitize($_GET['estado'] ?? '');

        $accesorios = $accesorioModel->buscar($filtro, $codigo, '', $estado);

        $formato = $_GET['formato'] ?? 'excel';

        if ($formato === 'csv') {
            $this->exportarCSV($accesorios);
        } else {
            $this->exportarExcel($accesorios);
        }
    }

    /**
     * Exportar a Excel (formato .xls simple)
     */
    private function exportarExcel($data) {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="Accesorios_' . date('Y-m-d') . '.xls"');

        echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'>";
        echo "<head><meta charset='UTF-8'></head><body><table border='1'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th><th>Código</th><th>Nombre</th><th>Cantidad</th><th>Unidad</th><th>Modelo</th><th>Marca</th><th>Fecha Entrada</th><th>Estado</th><th>Calidad</th><th>Precio Unitario</th><th>Proveedor</th><th>Observaciones</th>";
        echo "</tr>";
        echo "</thead><tbody>";

        foreach ($data as $accesorio) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars((string)$accesorio['id_accesorio'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)($accesorio['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)$accesorio['nombre'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)$accesorio['cantidad'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)($accesorio['unidad_medida'] ?? ''), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)($accesorio['modelo'] ?? ''), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)($accesorio['marca'] ?? ''), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)$accesorio['fecha_entrada'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)$accesorio['estado'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)$accesorio['calidad'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)($accesorio['costo'] ?? ''), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)($accesorio['proveedor_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars((string)($accesorio['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8') . "</td>";
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
        header('Content-Disposition: attachment; filename="Accesorios_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // BOM para Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Encabezados
        fputcsv($output, ['ID', 'Código', 'Nombre', 'Cantidad', 'Unidad', 'Modelo', 'Marca', 'Fecha Entrada', 'Estado', 'Calidad', 'Precio Unitario', 'Proveedor', 'Observaciones']);

        // Datos
        foreach ($data as $accesorio) {
            fputcsv($output, [
                $accesorio['id_accesorio'],
                $accesorio['codigo'] ?? '',
                $accesorio['nombre'],
                $accesorio['cantidad'],
                $accesorio['unidad_medida'] ?? '',
                $accesorio['modelo'] ?? '',
                $accesorio['marca'] ?? '',
                $accesorio['fecha_entrada'],
                $accesorio['estado'],
                $accesorio['calidad'],
                $accesorio['costo'] ?? '',
                $accesorio['proveedor_nombre'] ?? '',
                $accesorio['observaciones'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Importar desde CSV
     */
    public function importar() {
        if (!$this->isPost() || !isset($_FILES['archivo'])) {
            $this->setFlash('error', 'No se ha seleccionado ningún archivo');
            $this->redirect('index.php?controller=accesorios');
        }

        $archivo = $_FILES['archivo']['tmp_name'];
        $extension = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);

        if (strtolower($extension) !== 'csv') {
            $this->setFlash('error', 'El archivo debe ser CSV');
            $this->redirect('index.php?controller=accesorios');
        }

        $accesoriosModel = new Accesorio();
        $contador = 0;
        $errores = [];

        $proveedores = $this->obtenerProveedoresActivos();
        $proveedoresPorNombre = [];
        foreach ($proveedores as $proveedor) {
            $proveedoresPorNombre[mb_strtoupper(trim($proveedor['nombre_empresa']), 'UTF-8')] = (int)$proveedor['id_proveedor'];
        }

        if (($handle = fopen($archivo, 'r')) !== FALSE) {
            // Saltar encabezado
            fgetcsv($handle);
            $numeroFila = 1;

            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $numeroFila++;
                if (count($data) >= 3) {
                    $nombreProveedor = trim((string)($data[10] ?? ''));
                    try {
                        $resultado = $accesoriosModel->registrar([
                            'codigo' => $data[0] ?? null,
                            'nombre' => $data[1] ?? '',
                            'cantidad' => intval($data[2] ?? 0),
                            'unidad_medida' => $data[3] ?? null,
                            'marca' => $data[4] ?? null,
                            'modelo' => $data[5] ?? null,
                            'numero_serie_mac' => $data[6] ?? null,
                            'fecha_entrada' => $data[7] ?? date('Y-m-d'),
                            'calidad' => $data[8] ?? 'BUENO',
                            'costo' => isset($data[9]) && $data[9] !== '' ? (float)$data[9] : null,
                            'id_proveedor' => $nombreProveedor !== '' ? ($proveedoresPorNombre[mb_strtoupper($nombreProveedor, 'UTF-8')] ?? null) : null,
                            'observaciones' => $data[11] ?? null
                        ]);
                    } catch (Throwable $e) {
                        $resultado = APP_ENV === 'development' ? $e->getMessage() : false;
                    }

                    $registroExitoso = is_array($resultado)
                        ? (bool)($resultado['success'] ?? false)
                        : (bool)$resultado;

                    if ($registroExitoso) {
                        $contador++;
                    } else {
                        $detalle = is_string($resultado) && $resultado !== ''
                            ? $resultado
                            : 'Error al registrar';
                        $errores[] = 'Fila ' . $numeroFila . ': ' . $detalle;
                    }
                }
            }
            fclose($handle);
        }

        if ($contador > 0) {
            $this->setFlash('success', "Se importaron {$contador} accesorio(s) correctamente");
        }

        if (!empty($errores)) {
            $this->setFlash('warning', 'Errores: ' . implode(', ', array_slice($errores, 0, 5)));
        }

        $this->redirect('index.php?controller=accesorios');
    }

    public function guardarTipo() {
        if (!$this->isPost()) {
            $this->redirect('index.php?controller=accesorios&action=crear');
        }

        $tipoModel = new TipoAccesorio();
        $data = [
            'codigo' => strtoupper($this->sanitize($_POST['tipo_codigo'] ?? '')),
            'descripcion' => $this->sanitize($_POST['tipo_descripcion'] ?? ''),
            'categoria' => $this->sanitize($_POST['tipo_categoria'] ?? ''),
            'observaciones' => $this->sanitize($_POST['tipo_observaciones'] ?? ''),
        ];

        if ($data['codigo'] === '' || $data['descripcion'] === '') {
            $this->setFlash('error', 'El código y la descripción del tipo son obligatorios');
            $this->redirect('index.php?controller=accesorios&action=crear');
        }

        $resultado = $tipoModel->registrar($data);
        $this->setFlash($resultado['success'] ? 'success' : 'error', $resultado['message']);
        $this->redirect('index.php?controller=accesorios&action=crear');
    }

    public function referenciaCodigo() {
        $codigo = strtoupper($this->sanitize($_GET['codigo'] ?? ''));

        if ($codigo === '') {
            $this->renderJson(['success' => false, 'message' => 'Código requerido']);
            return;
        }

        $accesorioModel = new Accesorio();
        $referencia = $accesorioModel->referenciaPorCodigo($codigo);

        $this->renderJson([
            'success' => (bool)$referencia,
            'data' => $referencia ?: null
        ]);
    }
}
