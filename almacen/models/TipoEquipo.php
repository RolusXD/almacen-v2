<?php
/**
 * Modelo de Tipo de Equipo
 * Gestiona los tipos/categorías de equipos de forma dinámica
 */
class TipoEquipo extends Model {
    protected $table = 'tipos_equipos';
    protected $primaryKey = 'id_codigo';

    /**
     * Obtener todos los tipos activos
     */
    public function obtenerActivos() {
        return $this->where('activo = ?', [1]);
    }

    /**
     * Buscar por código
     */
    public function findByCodigo($codigo) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE codigo = ?");
        $stmt->execute([$codigo]);
        return $stmt->fetch();
    }

    /**
     * Obtener tipos por categoría
     */
    public function porCategoria($categoria) {
        return $this->where('categoria = ? AND activo = ?', [$categoria, 1]);
    }

    /**
     * Buscar tipos con filtro
     */
    public function buscar($filtro = '', $categoria = '') {
        $sql = "SELECT * FROM {$this->table} WHERE activo = 1";
        $params = [];

        if (!empty($categoria)) {
            $sql .= " AND categoria = ?";
            $params[] = $categoria;
        }

        if (!empty($filtro)) {
            $sql .= " AND (codigo LIKE ? OR descripcion LIKE ?)";
            $filtroParam = "%$filtro%";
            $params[] = $filtroParam;
            $params[] = $filtroParam;
        }

        $sql .= " ORDER BY categoria ASC, codigo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Registrar nuevo tipo
     */
    public function registrar($data) {
        // Verificar si ya existe el código
        $existente = $this->findByCodigo($data['codigo']);
        if ($existente) {
            return ['success' => false, 'message' => 'Ya existe un tipo con este código'];
        }

        $data['activo'] = 1;
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->insert($data);
        return ['success' => true, 'message' => 'Tipo de equipo registrado correctamente'];
    }

    /**
     * Actualizar tipo
     */
    public function actualizar($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    /**
     * Activar/desactivar tipo
     */
    public function toggleActivo($id) {
        $tipo = $this->find($id);
        if (!$tipo) {
            return false;
        }

        $nuevoEstado = $tipo['activo'] == 1 ? 0 : 1;
        return $this->update($id, ['activo' => $nuevoEstado]);
    }

    /**
     * Obtener todas las categorías únicas
     */
    public function obtenerCategorias() {
        $sql = "SELECT DISTINCT categoria FROM {$this->table} WHERE activo = 1 ORDER BY categoria";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtener estadísticas de uso
     */
    public function estadisticasUso() {
        $sql = "SELECT 
                    t.id_codigo,
                    t.codigo,
                    t.descripcion,
                    t.categoria,
                    COUNT(e.id_equipo) as total_equipos,
                    SUM(CASE WHEN e.estado = 'DISPONIBLE' THEN 1 ELSE 0 END) as disponibles,
                    SUM(CASE WHEN e.estado = 'OCUPADO' THEN 1 ELSE 0 END) as ocupados
                FROM {$this->table} t
                LEFT JOIN equipos e ON t.codigo = e.codigo
                WHERE t.activo = 1
                GROUP BY t.id_codigo, t.codigo, t.descripcion, t.categoria
                ORDER BY total_equipos DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Eliminar tipo (solo si no tiene equipos asociados)
     */
    public function eliminar($id) {
        $tipo = $this->find($id);
        if (!$tipo) {
            return ['success' => false, 'message' => 'Tipo no encontrado'];
        }

        // Verificar si tiene equipos asociados
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM equipos WHERE codigo = ?");
        $stmt->execute([$tipo['codigo']]);
        $resultado = $stmt->fetch();

        if ($resultado['total'] > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar: hay ' . $resultado['total'] . ' equipo(s) con este tipo'];
        }

        $this->delete($id);
        return ['success' => true, 'message' => 'Tipo eliminado correctamente'];
    }
}
