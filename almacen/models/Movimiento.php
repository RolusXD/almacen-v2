<?php
/**
 * Modelo de Movimiento
 */
class Movimiento extends Model {

    /**
     * Registrar movimiento de equipo
     */
    public function registrarEquipo($data) {
        $data['fecha_movimiento'] = $data['fecha_movimiento'] ?? date('Y-m-d');
        $data['created_by'] = $_SESSION['user_id'] ?? null;

        return $this->db->prepare("INSERT INTO movimientos_equipos
            (numero_serie_mac, tipo_movimiento, asunto, encargado, destino, observaciones, observaciones_detalladas, fecha_movimiento, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([
                $data['numero_serie_mac'],
                $data['tipo_movimiento'],
                $data['asunto'],
                $data['encargado'],
                $data['destino'] ?? null,
                $data['observaciones'] ?? null,
                $data['observaciones_detalladas'] ?? null,
                $data['fecha_movimiento'],
                $data['created_by']
            ]);
    }

    /**
     * Registrar movimiento de accesorio
     */
    public function registrarAccesorio($data) {
        $data['fecha_movimiento'] = $data['fecha_movimiento'] ?? date('Y-m-d');
        $data['created_by'] = $_SESSION['user_id'] ?? null;

        return $this->db->prepare("INSERT INTO movimientos_accesorios
            (id_accesorio, tipo_movimiento, cantidad, asunto, encargado, observaciones, observaciones_detalladas, fecha_movimiento, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([
                $data['id_accesorio'],
                $data['tipo_movimiento'],
                $data['cantidad'],
                $data['asunto'],
                $data['encargado'],
                $data['observaciones'] ?? null,
                $data['observaciones_detalladas'] ?? null,
                $data['fecha_movimiento'],
                $data['created_by']
            ]);
    }

    /**
     * Obtener movimientos de equipos con filtro por destino
     */
    public function obtenerMovimientosEquipos($filtro = '', $destino = '', $limit = 100, $codigo = '') {
        $limit = max(1, min(500, (int)$limit));
        $sql = "SELECT me.*, e.nombre as nombre_equipo, e.codigo, e.marca, e.modelo
                FROM movimientos_equipos me
                LEFT JOIN equipos e ON me.numero_serie_mac = e.numero_serie_mac
                WHERE 1=1";

        $params = [];

        if (!empty($destino)) {
            $sql .= " AND me.destino = ?";
            $params[] = $destino;
        }

        if (!empty($codigo)) {
            $sql .= " AND e.codigo = ?";
            $params[] = $codigo;
        }

        if (!empty($filtro)) {
            $sql .= " AND (me.asunto LIKE ? OR me.encargado LIKE ? OR me.numero_serie_mac LIKE ? OR me.destino LIKE ? OR e.codigo LIKE ? OR e.nombre LIKE ?)";
            $filtroParam = "%$filtro%";
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
        }

        $sql .= " ORDER BY me.fecha_movimiento DESC, me.created_at DESC LIMIT {$limit}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtener movimientos de accesorios
     */
    public function obtenerMovimientosAccesorios($filtro = '', $limit = 100) {
        $limit = max(1, min(500, (int)$limit));
        $sql = "SELECT ma.*, a.nombre as nombre_accesorio, a.codigo, a.unidad_medida
                FROM movimientos_accesorios ma
                LEFT JOIN accesorios a ON ma.id_accesorio = a.id_accesorio
                WHERE 1=1";

        $params = [];

        if (!empty($filtro)) {
            $sql .= " AND (ma.asunto LIKE ? OR ma.encargado LIKE ? OR a.nombre LIKE ? OR a.codigo LIKE ?)";
            $filtroParam = "%$filtro%";
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
        }

        $sql .= " ORDER BY ma.fecha_movimiento DESC, ma.created_at DESC LIMIT {$limit}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtener movimientos por número de serie
     */
    public function obtenerPorNumeroSerie($numeroSerie) {
        $sql = "SELECT me.*, e.nombre as nombre_equipo, e.codigo, e.marca, e.modelo
                FROM movimientos_equipos me
                LEFT JOIN equipos e ON me.numero_serie_mac = e.numero_serie_mac
                WHERE me.numero_serie_mac = ?
                ORDER BY me.fecha_movimiento DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numeroSerie]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener historial completo de un equipo
     */
    public function historialEquipo($numeroSerie) {
        return $this->obtenerPorNumeroSerie($numeroSerie);
    }

    /**
     * Eliminar movimiento de equipo
     */
    public function eliminarMovimientoEquipo($id) {
        return $this->db->prepare("DELETE FROM movimientos_equipos WHERE id_movimiento = ?")
            ->execute([$id]);
    }

    /**
     * Eliminar movimiento de accesorio
     */
    public function eliminarMovimientoAccesorio($id) {
        return $this->db->prepare("DELETE FROM movimientos_accesorios WHERE id_movimiento = ?")
            ->execute([$id]);
    }

    /**
     * Obtener estadísticas de movimientos
     */
    public function estadisticas() {
        $sql = "SELECT 
                    tipo_movimiento,
                    COUNT(*) as total,
                    TO_CHAR(fecha_movimiento, 'YYYY-MM') as mes
                FROM movimientos_equipos
                GROUP BY tipo_movimiento, TO_CHAR(fecha_movimiento, 'YYYY-MM')
                ORDER BY mes DESC, tipo_movimiento";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtener destinos más usados
     */
    public function destinosMasUsados($limit = 10) {
        $limit = max(1, min(100, (int)$limit));
        $sql = "SELECT destino, COUNT(*) as total
                FROM movimientos_equipos
                WHERE destino IS NOT NULL AND destino != ''
                GROUP BY destino
                ORDER BY total DESC
                LIMIT {$limit}";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function encargadosFrecuentes($limit = 20) {
        $limit = max(1, min(100, (int)$limit));
        $sql = "SELECT encargado, COUNT(*) AS total
                FROM (
                    SELECT encargado FROM movimientos_equipos
                    UNION ALL
                    SELECT encargado FROM movimientos_accesorios
                ) movimientos
                WHERE encargado IS NOT NULL AND encargado <> ''
                GROUP BY encargado
                ORDER BY total DESC, encargado ASC
                LIMIT {$limit}";

        return $this->db->query($sql)->fetchAll();
    }
}
