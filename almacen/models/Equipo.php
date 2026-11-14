<?php
/**
 * Modelo de Equipo
 */
class Equipo extends Model {
    protected $table = 'equipos';
    protected $primaryKey = 'id_equipo';

    /**
     * Buscar por número de serie/MAC
     */
    public function findByNumeroSerie($numeroSerie) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE numero_serie_mac = ?");
        $stmt->execute([$numeroSerie]);
        return $stmt->fetch();
    }

    /**
     * Buscar por código
     */
    public function findByCodigo($codigo) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE codigo = ?");
        $stmt->execute([$codigo]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener equipos por estado
     */
    public function porEstado($estado) {
        return $this->where('estado = ?', [$estado]);
    }

    /**
     * Obtener equipos disponibles
     */
    public function disponibles() {
        return $this->porEstado('DISPONIBLE');
    }

    /**
     * Buscar equipos con filtro avanzado
     */
    public function buscar($filtro = '', $estado = 'todos', $calidad = 'todas', $fecha_desde = '', $fecha_hasta = '', $numero_serie = '') {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if ($estado !== 'todos') {
            $sql .= " AND estado = ?";
            $params[] = $estado;
        }

        if ($calidad !== 'todas') {
            $sql .= " AND calidad = ?";
            $params[] = $calidad;
        }

        if (!empty($numero_serie)) {
            $sql .= " AND numero_serie_mac LIKE ?";
            $params[] = "%$numero_serie%";
        }

        if (!empty($fecha_desde)) {
            $sql .= " AND fecha_entrada >= ?";
            $params[] = $fecha_desde;
        }

        if (!empty($fecha_hasta)) {
            $sql .= " AND fecha_entrada <= ?";
            $params[] = $fecha_hasta;
        }

        if (!empty($filtro)) {
            $sql .= " AND (nombre LIKE ? OR numero_serie_mac LIKE ? OR modelo LIKE ? OR marca LIKE ? OR codigo LIKE ?)";
            $filtroParam = "%$filtro%";
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
        }

        $sql .= " ORDER BY nombre ASC, fecha_entrada DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Buscar específicamente por número de serie (para egresos)
     */
    public function buscarPorNumeroSerie($numeroSerie) {
        $sql = "SELECT e.*, c.descripcion as codigo_descripcion
                FROM {$this->table} e
                LEFT JOIN tipos_equipos c ON e.codigo = c.codigo
                WHERE e.numero_serie_mac LIKE ?
                ORDER BY e.fecha_entrada DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$numeroSerie%"]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener estadísticas por código
     */
    public function estadisticasPorCodigo() {
        $sql = "SELECT
                    e.codigo,
                    c.descripcion,
                    COUNT(*) as total,
                    SUM(CASE WHEN e.estado = 'DISPONIBLE' THEN 1 ELSE 0 END) as disponibles,
                    SUM(CASE WHEN e.estado = 'OCUPADO' THEN 1 ELSE 0 END) as ocupados,
                    AVG(e.precio) as precio_promedio
                FROM {$this->table} e
                LEFT JOIN tipos_equipos c ON e.codigo = c.codigo
                GROUP BY e.codigo, c.descripcion
                ORDER BY total DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Registrar nuevo equipo
     */
    public function registrar($data) {
        // Verificar si ya existe el número de serie
        $existente = $this->findByNumeroSerie($data['numero_serie_mac']);
        if ($existente) {
            return ['success' => false, 'message' => 'Ya existe un equipo con este número de serie/MAC'];
        }

        $data['estado'] = 'DISPONIBLE';
        $data['fecha_entrada'] = $data['fecha_entrada'] ?? date('Y-m-d');

        $this->insert($data);
        return ['success' => true, 'message' => 'Equipo registrado correctamente'];
    }

    /**
     * Actualizar equipo
     */
    public function actualizar($id, $data) {
        return $this->update($id, $data);
    }

    /**
     * Eliminar equipo (solo si no tiene movimientos)
     */
    public function eliminar($id) {
        $equipo = $this->find($id);
        if (!$equipo) {
            return ['success' => false, 'message' => 'Equipo no encontrado'];
        }

        // Verificar si tiene movimientos
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM movimientos_equipos WHERE numero_serie_mac = ?");
        $stmt->execute([$equipo['numero_serie_mac']]);
        $resultado = $stmt->fetch();

        if ($resultado['total'] > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar: el equipo tiene movimientos registrados'];
        }

        $this->delete($id);
        return ['success' => true, 'message' => 'Equipo eliminado correctamente'];
    }

    /**
     * Obtener estadísticas
     */
    public function estadisticas() {
        $sql = "SELECT
                    estado,
                    COUNT(*) as total,
                    SUM(CASE WHEN calidad = 'NUEVO' THEN 1 ELSE 0 END) as nuevos,
                    SUM(CASE WHEN calidad = 'BUENO' THEN 1 ELSE 0 END) as buenos,
                    SUM(CASE WHEN calidad = 'REGULAR' THEN 1 ELSE 0 END) as regulares,
                    SUM(CASE WHEN calidad = 'MALO' THEN 1 ELSE 0 END) as malos,
                    SUM(precio) as valor_total
                FROM {$this->table}
                GROUP BY estado";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtener resumen general
     */
    public function resumenGeneral() {
        $sql = "SELECT
                    COUNT(*) as total_equipos,
                    SUM(CASE WHEN estado = 'DISPONIBLE' THEN 1 ELSE 0 END) as disponibles,
                    SUM(CASE WHEN estado = 'OCUPADO' THEN 1 ELSE 0 END) as ocupados,
                    SUM(CASE WHEN estado = 'MANTENIMIENTO' THEN 1 ELSE 0 END) as mantenimiento,
                    SUM(precio) as valor_total_inventario,
                    AVG(precio) as precio_promedio
                FROM {$this->table}";

        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }

    /**
     * Obtener códigos únicos de equipos
     */
    public function obtenerCodigosUnicos() {
        $sql = "SELECT DISTINCT e.codigo, c.descripcion
                FROM {$this->table} e
                LEFT JOIN tipos_equipos c ON e.codigo = c.codigo
                WHERE e.codigo IS NOT NULL
                ORDER BY e.codigo";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerModelosReferencia() {
        $sql = "SELECT
                    modelo,
                    MAX(marca) as marca,
                    MAX(nombre) as nombre,
                    MAX(codigo) as codigo,
                    MAX(id_tipo_equipo) as id_tipo_equipo,
                    MAX(proveedor) as proveedor,
                    AVG(precio) as precio
                FROM {$this->table}
                WHERE modelo IS NOT NULL AND modelo <> ''
                GROUP BY modelo
                ORDER BY modelo";

        return $this->db->query($sql)->fetchAll();
    }

    public function referenciaPorModelo($modelo) {
        $sql = "SELECT
                    e.modelo,
                    e.marca,
                    e.nombre,
                    e.codigo,
                    e.id_tipo_equipo,
                    e.proveedor,
                    e.precio,
                    t.categoria as tipo_equipo,
                    t.descripcion as tipo_descripcion
                FROM {$this->table} e
                LEFT JOIN tipos_equipos t ON e.codigo = t.codigo
                WHERE LOWER(e.modelo) = LOWER(?)
                ORDER BY e.created_at DESC, e.id_equipo DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$modelo]);
        return $stmt->fetch();
    }
}
