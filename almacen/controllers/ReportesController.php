<?php
/**
 * Controlador de Reportes
 * Generación de reportes en PDF con TCPDF
 * Actualizado para Ubuntu 22.04
 */
class ReportesController extends Controller {

    /**
     * Página principal de reportes
     */
    public function index() {
        $this->render('reportes/index', [
            'title' => 'Reportes'
        ]);
    }

    /**
     * Generar reporte de equipos en PDF
     */
    public function equiposPdf() {
        if (!$this->loadTcpdf()) {
            return;
        }

        $equipoModel = new Equipo();
        $equipos = $equipoModel->all('nombre ASC');

        // Crear PDF con TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Configurar documento
        $pdf->SetCreator('Sistema de Almacén TOI');
        $pdf->SetAuthor('TOI - Telecomunicaciones Oportunas Inteligentes');
        $pdf->SetTitle('Reporte de Equipos');
        $pdf->SetSubject('Reporte de Equipos');
        
        // Configurar márgenes
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        // Agregar página
        $pdf->AddPage();
        
        // Encabezado
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 10, 'SISTEMA DE ALMACEN TOI', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Reporte de Equipos', 0, 1, 'C');
        $pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        $pdf->Ln(5);

        // Tabla
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(30, 58, 95);
        $pdf->SetTextColor(255);
        $pdf->Cell(30, 8, 'ID', 1, 0, 'C', true);
        $pdf->Cell(50, 8, 'Nombre', 1, 0, 'C', true);
        $pdf->Cell(45, 8, 'N. Serie/MAC', 1, 0, 'C', true);
        $pdf->Cell(35, 8, 'Modelo', 1, 0, 'C', true);
        $pdf->Cell(35, 8, 'Marca', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Estado', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Calidad', 1, 0, 'C', true);
        $pdf->Cell(35, 8, 'F. Entrada', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0);
        $pdf->SetFillColor(248, 249, 250);

        $fill = false;
        foreach ($equipos as $equipo) {
            $pdf->Cell(30, 6, $equipo['id_equipo'], 1, 0, 'C', $fill);
            $pdf->Cell(50, 6, $this->truncate($equipo['nombre'], 25), 1, 0, 'L', $fill);
            $pdf->Cell(45, 6, $this->truncate($equipo['numero_serie_mac'], 20), 1, 0, 'L', $fill);
            $pdf->Cell(35, 6, $this->truncate($equipo['modelo'] ?? '-', 15), 1, 0, 'L', $fill);
            $pdf->Cell(35, 6, $this->truncate($equipo['marca'] ?? '-', 15), 1, 0, 'L', $fill);
            $pdf->Cell(30, 6, $equipo['estado'], 1, 0, 'C', $fill);
            $pdf->Cell(30, 6, $equipo['calidad'], 1, 0, 'C', $fill);
            $pdf->Cell(35, 6, date('d/m/Y', strtotime($equipo['fecha_entrada'])), 1, 1, 'C', $fill);
            $fill = !$fill;
        }

        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Total de equipos: ' . count($equipos), 0, 1, 'R');

        // Footer con número de página
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(128);
        $pdf->Cell(0, 10, 'Página ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, 0, 'R');

        // Output
        if (ob_get_length()) {
            ob_clean();
        }
        $pdf->Output('Reporte_Equipos_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }

    /**
     * Generar reporte de accesorios en PDF
     */
    public function accesoriosPdf() {
        if (!$this->loadTcpdf()) {
            return;
        }

        $accesorioModel = new Accesorio();
        $accesorios = $accesorioModel->all('nombre ASC');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator('Sistema de Almacén TOI');
        $pdf->SetAuthor('TOI - Telecomunicaciones Oportunas Inteligentes');
        $pdf->SetTitle('Reporte de Accesorios');
        
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        $pdf->AddPage();
        
        // Encabezado
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 10, 'SISTEMA DE ALMACEN TOI', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Reporte de Accesorios', 0, 1, 'C');
        $pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        $pdf->Ln(5);

        // Tabla
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(30, 58, 95);
        $pdf->SetTextColor(255);
        $pdf->Cell(20, 8, 'ID', 1, 0, 'C', true);
        $pdf->Cell(60, 8, 'Nombre', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Cant.', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Modelo', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Marca', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Estado', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Calidad', 1, 0, 'C', true);
        $pdf->Cell(35, 8, 'F. Entrada', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0);
        $pdf->SetFillColor(248, 249, 250);

        $fill = false;
        foreach ($accesorios as $accesorio) {
            $pdf->Cell(20, 6, $accesorio['id_accesorio'], 1, 0, 'C', $fill);
            $pdf->Cell(60, 6, $this->truncate($accesorio['nombre'], 30), 1, 0, 'L', $fill);
            $pdf->Cell(25, 6, $accesorio['cantidad'], 1, 0, 'C', $fill);
            $pdf->Cell(40, 6, $this->truncate($accesorio['modelo'] ?? '-', 15), 1, 0, 'L', $fill);
            $pdf->Cell(40, 6, $this->truncate($accesorio['marca'] ?? '-', 15), 1, 0, 'L', $fill);
            $pdf->Cell(30, 6, $accesorio['estado'], 1, 0, 'C', $fill);
            $pdf->Cell(30, 6, $accesorio['calidad'], 1, 0, 'C', $fill);
            $pdf->Cell(35, 6, date('d/m/Y', strtotime($accesorio['fecha_entrada'])), 1, 1, 'C', $fill);
            $fill = !$fill;
        }

        $totalCantidad = array_sum(array_column($accesorios, 'cantidad'));
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Total de accesorios: ' . count($accesorios) . ' | Cantidad total: ' . $totalCantidad, 0, 1, 'R');

        // Footer
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(128);
        $pdf->Cell(0, 10, 'Página ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, 0, 'R');

        if (ob_get_length()) {
            ob_clean();
        }
        $pdf->Output('Reporte_Accesorios_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }

    /**
     * Generar reporte de movimientos en PDF
     */
    public function movimientosPdf() {
        if (!$this->loadTcpdf()) {
            return;
        }

        $movimientoModel = new Movimiento();
        $movimientos = $movimientoModel->obtenerMovimientosEquipos('', '', 100);

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator('Sistema de Almacén TOI');
        $pdf->SetAuthor('TOI - Telecomunicaciones Oportunas Inteligentes');
        $pdf->SetTitle('Reporte de Movimientos');
        
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        $pdf->AddPage();
        
        // Encabezado
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 10, 'SISTEMA DE ALMACEN TOI', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Reporte de Movimientos de Equipos', 0, 1, 'C');
        $pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        $pdf->Ln(5);

        // Tabla
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(30, 58, 95);
        $pdf->SetTextColor(255);
        $pdf->Cell(25, 8, 'Fecha', 1, 0, 'C', true);
        $pdf->Cell(45, 8, 'Equipo', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'N. Serie', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Tipo', 1, 0, 'C', true);
        $pdf->Cell(50, 8, 'Asunto', 1, 0, 'C', true);
        $pdf->Cell(50, 8, 'Encargado', 1, 0, 'C', true);
        $pdf->Cell(50, 8, 'Observaciones', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(0);
        $pdf->SetFillColor(248, 249, 250);

        $fill = false;
        foreach ($movimientos as $mov) {
            $pdf->Cell(25, 6, date('d/m/Y', strtotime($mov['fecha_movimiento'])), 1, 0, 'C', $fill);
            $pdf->Cell(45, 6, $this->truncate($mov['nombre_equipo'] ?? '-', 20), 1, 0, 'L', $fill);
            $pdf->Cell(40, 6, $this->truncate($mov['numero_serie_mac'], 18), 1, 0, 'L', $fill);
            $pdf->Cell(30, 6, $mov['tipo_movimiento'], 1, 0, 'C', $fill);
            $pdf->Cell(50, 6, $this->truncate($mov['asunto'], 25), 1, 0, 'L', $fill);
            $pdf->Cell(50, 6, $this->truncate($mov['encargado'], 25), 1, 0, 'L', $fill);
            $pdf->Cell(50, 6, $this->truncate($mov['observaciones'] ?? '-', 25), 1, 1, 'L', $fill);
            $fill = !$fill;
        }

        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Total de movimientos: ' . count($movimientos), 0, 1, 'R');

        // Footer
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(128);
        $pdf->Cell(0, 10, 'Página ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, 0, 'R');

        if (ob_get_length()) {
            ob_clean();
        }
        $pdf->Output('Reporte_Movimientos_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }

    /**
     * Generar reporte de usuarios en PDF (solo admin)
     */
    public function usuariosPdf() {
        if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
            $this->setFlash('error', 'No tiene permisos para ver este reporte');
            $this->redirect('index.php?controller=dashboard');
        }

        if (!$this->loadTcpdf()) {
            return;
        }

        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->obtenerTodos(100, 0);

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator('Sistema de Almacén TOI');
        $pdf->SetAuthor('TOI - Telecomunicaciones Oportunas Inteligentes');
        $pdf->SetTitle('Reporte de Usuarios');
        
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        $pdf->AddPage();
        
        // Encabezado
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 10, 'SISTEMA DE ALMACEN TOI', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Reporte de Usuarios', 0, 1, 'C');
        $pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        $pdf->Ln(5);

        // Tabla
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(30, 58, 95);
        $pdf->SetTextColor(255);
        $pdf->Cell(15, 8, 'ID', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Usuario', 1, 0, 'C', true);
        $pdf->Cell(70, 8, 'Nombre', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Carnet', 1, 0, 'C', true);
        $pdf->Cell(60, 8, 'Email', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Rol', 1, 0, 'C', true);
        $pdf->Cell(20, 8, 'Estado', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0);
        $pdf->SetFillColor(248, 249, 250);

        $fill = false;
        foreach ($usuarios as $user) {
            $pdf->Cell(15, 6, $user['id_usuario'], 1, 0, 'C', $fill);
            $pdf->Cell(40, 6, $user['usuario'], 1, 0, 'L', $fill);
            $pdf->Cell(70, 6, $this->truncate($user['nombre_completo'], 35), 1, 0, 'L', $fill);
            $pdf->Cell(30, 6, $user['carnet'], 1, 0, 'C', $fill);
            $pdf->Cell(60, 6, $this->truncate($user['email'], 30), 1, 0, 'L', $fill);
            $pdf->Cell(25, 6, strtoupper($user['rol']), 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, $user['activo'] ? 'ACTIVO' : 'INACTIVO', 1, 1, 'C', $fill);
            $fill = !$fill;
        }

        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Total de usuarios: ' . count($usuarios), 0, 1, 'R');

        // Footer
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(128);
        $pdf->Cell(0, 10, 'Página ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, 0, 'R');

        if (ob_get_length()) {
            ob_clean();
        }
        $pdf->Output('Reporte_Usuarios_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }

    private function loadTcpdf() {
        $autoload = __DIR__ . '/../vendor/autoload.php';

        if (file_exists($autoload)) {
            require_once $autoload;
        }

        if (class_exists('TCPDF')) {
            return true;
        }

        $this->setFlash('error', 'TCPDF no está instalado. Instale las dependencias antes de generar reportes PDF.');
        $this->redirect('index.php?controller=reportes');
        return false;
    }

    /**
     * Función auxiliar para truncar texto
     */
    private function truncate($text, $length = 50) {
        if (strlen($text) > $length) {
            return substr($text, 0, $length - 3) . '...';
        }
        return $text;
    }
}
