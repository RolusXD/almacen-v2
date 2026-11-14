<?php
/**
 * Controlador del Dashboard - Actualizado 2026
 */
class DashboardController extends Controller {

    public function index() {
        $equipoModel = new Equipo();
        $accesorioModel = new Accesorio();
        $movimientoModel = new Movimiento();
        $usuarioModel = new Usuario();

        // Estadísticas generales de equipos
        $resumenEquipos = $equipoModel->resumenGeneral();
        $equiposPorCodigo = $equipoModel->estadisticasPorCodigo();
        
        // Estadísticas de accesorios
        $estadisticasAccesorios = $accesorioModel->estadisticas();
        $accesoriosPorCodigo = $accesorioModel->resumenPorCodigo();
        
        // Estadísticas de usuarios
        $estadisticasUsuarios = $usuarioModel->estadisticas();

        // Últimos movimientos
        $ultimosMovimientosEquipos = $movimientoModel->obtenerMovimientosEquipos('', '', 10);
        $ultimosMovimientosAccesorios = $movimientoModel->obtenerMovimientosAccesorios('', 10);

        // Equipos por estado
        $equiposPorEstado = $equipoModel->estadisticas();
        
        // Destinos más usados
        $destinosMasUsados = $movimientoModel->destinosMasUsados(5);

        // Accesorios con stock bajo
        $stockBajoAccesorios = [];
        if (!empty($accesoriosPorCodigo)) {
            foreach ($accesoriosPorCodigo as $acc) {
                if ($acc['stock_bajo'] > 0) {
                    $stockBajoAccesorios[] = $acc;
                }
            }
        }

        $this->render('dashboard/index', [
            'title' => 'Dashboard - Ver Stock',
            // Equipos
            'totalEquipos' => $resumenEquipos['total_equipos'] ?? 0,
            'equiposDisponibles' => $resumenEquipos['disponibles'] ?? 0,
            'equiposOcupados' => $resumenEquipos['ocupados'] ?? 0,
            'equiposMantenimiento' => $resumenEquipos['mantenimiento'] ?? 0,
            'valorTotalInventario' => $resumenEquipos['valor_total_inventario'] ?? 0,
            'equiposPorCodigo' => $equiposPorCodigo,
            
            // Accesorios
            'totalAccesorios' => $estadisticasAccesorios['total_accesorios'] ?? 0,
            'totalCantidadAccesorios' => $estadisticasAccesorios['total_cantidad'] ?? 0,
            'stockBajo' => $estadisticasAccesorios['stock_bajo'] ?? 0,
            'accesoriosPorCodigo' => $accesoriosPorCodigo,
            'stockBajoAccesorios' => $stockBajoAccesorios,
            
            // Usuarios
            'totalUsuarios' => $estadisticasUsuarios['total_usuarios'] ?? 0,
            'usuariosActivos' => $estadisticasUsuarios['activos'] ?? 0,
            
            // Movimientos
            'ultimosMovimientosEquipos' => $ultimosMovimientosEquipos,
            'ultimosMovimientosAccesorios' => $ultimosMovimientosAccesorios,
            'equiposPorEstado' => $equiposPorEstado,
            'destinosMasUsados' => $destinosMasUsados
        ]);
    }
}
