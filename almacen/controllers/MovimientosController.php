<?php
/**
 * Controlador de Movimientos
 */
class MovimientosController extends Controller {
    private function normalizarSeleccionConOtro($valor, $otro) {
        $valor = trim((string)$valor);
        $otro = trim((string)$otro);

        if ($valor === 'OTROS' && $otro !== '') {
            return mb_strtoupper($this->sanitize($otro), 'UTF-8');
        }

        return mb_strtoupper($this->sanitize($valor), 'UTF-8');
    }

    public function equipos() {
        $movimientoModel = new Movimiento();
        $equipoModel = new Equipo();
        $filtro = $this->sanitize($_GET['filtro'] ?? '');
        $destino = $this->sanitize($_GET['destino'] ?? '');
        $codigo = $this->sanitize($_GET['codigo'] ?? '');

        $movimientos = $movimientoModel->obtenerMovimientosEquipos($filtro, $destino, 100, $codigo);
        $equiposDisponibles = $equipoModel->disponibles();
        $destinosMasUsados = $movimientoModel->destinosMasUsados();
        $codigosEquipo = $equipoModel->obtenerCodigosUnicos();
        $encargadosFrecuentes = $movimientoModel->encargadosFrecuentes();
        $encargados = ['EQUIPO 1', 'EQUIPO 2'];

        foreach ($encargadosFrecuentes as $encargado) {
            $nombre = mb_strtoupper(trim((string)$encargado['encargado']), 'UTF-8');
            if ($nombre !== '' && !in_array($nombre, $encargados, true)) {
                $encargados[] = $nombre;
            }
        }

        // Registrar movimiento
        if ($this->isPost() && isset($_POST['accion']) && $_POST['accion'] === 'registrar_equipo') {
            $data = [
                'numero_serie_mac' => $this->sanitize($_POST['numero_serie_mac']),
                'tipo_movimiento' => $_POST['tipo_movimiento'],
                'asunto' => $this->normalizarSeleccionConOtro($_POST['asunto'] ?? '', $_POST['asunto_otro'] ?? ''),
                'encargado' => $this->normalizarSeleccionConOtro($_POST['encargado'] ?? '', $_POST['encargado_otro'] ?? ''),
                'destino' => $this->sanitize($_POST['destino']) ?? null,
                'observaciones' => $this->sanitize($_POST['observaciones']),
                'observaciones_detalladas' => $this->sanitize($_POST['observaciones_detalladas']) ?? null,
                'fecha_movimiento' => $_POST['fecha_movimiento']
            ];

            if (empty($data['numero_serie_mac']) || !$equipoModel->findByNumeroSerie($data['numero_serie_mac'])) {
                $this->setFlash('error', 'Debe seleccionar un equipo disponible mediante la búsqueda por serie.');
                $this->redirect('index.php?controller=movimientos&action=equipos');
            }

            $movimientoModel->registrarEquipo($data);
            $this->setFlash('success', 'Movimiento registrado correctamente');
            $this->redirect('index.php?controller=movimientos&action=equipos');
        }

        $this->render('movimientos/equipos', [
            'title' => 'Movimientos de Equipos',
            'movimientos' => $movimientos,
            'equiposDisponibles' => $equiposDisponibles,
            'filtro' => $filtro,
            'destino' => $destino,
            'codigo' => $codigo,
            'codigosEquipo' => $codigosEquipo,
            'destinosMasUsados' => $destinosMasUsados,
            'encargados' => $encargados
        ]);
    }

    public function accesorios() {
        $movimientoModel = new Movimiento();
        $accesorioModel = new Accesorio();
        $filtro = $this->sanitize($_GET['filtro'] ?? '');

        $movimientos = $movimientoModel->obtenerMovimientosAccesorios($filtro);
        $accesoriosConStock = $accesorioModel->conStock();
        $encargadosFrecuentes = $movimientoModel->encargadosFrecuentes();
        $encargados = ['EQUIPO 1', 'EQUIPO 2'];

        foreach ($encargadosFrecuentes as $encargado) {
            $nombre = mb_strtoupper(trim((string)$encargado['encargado']), 'UTF-8');
            if ($nombre !== '' && !in_array($nombre, $encargados, true)) {
                $encargados[] = $nombre;
            }
        }

        // Registrar movimiento
        if ($this->isPost() && isset($_POST['accion']) && $_POST['accion'] === 'registrar_accesorio') {
            $data = [
                'id_accesorio' => (int)$_POST['id_accesorio'],
                'tipo_movimiento' => $_POST['tipo_movimiento'],
                'cantidad' => (int)$_POST['cantidad'],
                'asunto' => $this->normalizarSeleccionConOtro($_POST['asunto'] ?? '', $_POST['asunto_otro'] ?? ''),
                'encargado' => $this->normalizarSeleccionConOtro($_POST['encargado'] ?? '', $_POST['encargado_otro'] ?? ''),
                'observaciones' => $this->sanitize($_POST['observaciones']),
                'observaciones_detalladas' => $this->sanitize($_POST['observaciones_detalladas']) ?? null,
                'fecha_movimiento' => $_POST['fecha_movimiento']
            ];

            $movimientoModel->registrarAccesorio($data);
            $this->setFlash('success', 'Movimiento registrado correctamente');
            $this->redirect('index.php?controller=movimientos&action=accesorios');
        }

        $this->render('movimientos/accesorios', [
            'title' => 'Movimientos de Accesorios',
            'movimientos' => $movimientos,
            'accesoriosConStock' => $accesoriosConStock,
            'filtro' => $filtro,
            'encargados' => $encargados
        ]);
    }

    public function eliminar_equipo() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->setFlash('error', 'ID de movimiento no válido');
            $this->redirect('index.php?controller=movimientos&action=equipos');
        }

        $movimientoModel = new Movimiento();
        $movimientoModel->eliminarMovimientoEquipo($id);

        $this->setFlash('success', 'Movimiento eliminado correctamente');
        $this->redirect('index.php?controller=movimientos&action=equipos');
    }

    public function eliminar_accesorio() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->setFlash('error', 'ID de movimiento no válido');
            $this->redirect('index.php?controller=movimientos&action=accesorios');
        }

        $movimientoModel = new Movimiento();
        $movimientoModel->eliminarMovimientoAccesorio($id);

        $this->setFlash('success', 'Movimiento eliminado correctamente');
        $this->redirect('index.php?controller=movimientos&action=accesorios');
    }

    /**
     * Exportar movimientos de equipos a Excel
     */
    public function exportar_equipos() {
        $movimientoModel = new Movimiento();
        $filtro = $this->sanitize($_GET['filtro'] ?? '');
        $destino = $this->sanitize($_GET['destino'] ?? '');
        $codigo = $this->sanitize($_GET['codigo'] ?? '');

        $movimientos = $movimientoModel->obtenerMovimientosEquipos($filtro, $destino, 1000, $codigo);

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="Movimientos_Equipos_' . date('Y-m-d') . '.xls"');

        echo utf8_decode("<html><head><meta charset='UTF-8'></head><body><table border='1'>");
        echo utf8_decode("<thead><tr>");
        echo utf8_decode("<th>ID</th><th>Fecha</th><th>Tipo</th><th>N° Serie</th><th>Equipo</th><th>Código</th><th>Asunto</th><th>Encargado</th><th>Destino</th><th>Observaciones</th>");
        echo utf8_decode("</tr></thead><tbody>");

        foreach ($movimientos as $mov) {
            echo utf8_decode("<tr>");
            echo utf8_decode("<td>" . $mov['id_movimiento'] . "</td>");
            echo utf8_decode("<td>" . $mov['fecha_movimiento'] . "</td>");
            echo utf8_decode("<td>" . $mov['tipo_movimiento'] . "</td>");
            echo utf8_decode("<td>" . $mov['numero_serie_mac'] . "</td>");
            echo utf8_decode("<td>" . ($mov['nombre_equipo'] ?? '') . "</td>");
            echo utf8_decode("<td>" . ($mov['codigo'] ?? '') . "</td>");
            echo utf8_decode("<td>" . $mov['asunto'] . "</td>");
            echo utf8_decode("<td>" . $mov['encargado'] . "</td>");
            echo utf8_decode("<td>" . ($mov['destino'] ?? '') . "</td>");
            echo utf8_decode("<td>" . ($mov['observaciones'] ?? '') . "</td>");
            echo utf8_decode("</tr>");
        }

        echo utf8_decode("</tbody></table></body></html>");
        exit;
    }

    /**
     * Exportar movimientos de accesorios a Excel
     */
    public function exportar_accesorios() {
        $movimientoModel = new Movimiento();
        $filtro = $this->sanitize($_GET['filtro'] ?? '');

        $movimientos = $movimientoModel->obtenerMovimientosAccesorios($filtro, 1000);

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="Movimientos_Accesorios_' . date('Y-m-d') . '.xls"');

        echo utf8_decode("<html><head><meta charset='UTF-8'></head><body><table border='1'>");
        echo utf8_decode("<thead><tr>");
        echo utf8_decode("<th>ID</th><th>Fecha</th><th>Tipo</th><th>Accesorio</th><th>Cantidad</th><th>Asunto</th><th>Encargado</th><th>Observaciones</th>");
        echo utf8_decode("</tr></thead><tbody>");

        foreach ($movimientos as $mov) {
            echo utf8_decode("<tr>");
            echo utf8_decode("<td>" . $mov['id_movimiento'] . "</td>");
            echo utf8_decode("<td>" . $mov['fecha_movimiento'] . "</td>");
            echo utf8_decode("<td>" . $mov['tipo_movimiento'] . "</td>");
            echo utf8_decode("<td>" . ($mov['nombre_accesorio'] ?? '') . "</td>");
            echo utf8_decode("<td>" . $mov['cantidad'] . "</td>");
            echo utf8_decode("<td>" . $mov['asunto'] . "</td>");
            echo utf8_decode("<td>" . $mov['encargado'] . "</td>");
            echo utf8_decode("<td>" . ($mov['observaciones'] ?? '') . "</td>");
            echo utf8_decode("</tr>");
        }

        echo utf8_decode("</tbody></table></body></html>");
        exit;
    }
}
