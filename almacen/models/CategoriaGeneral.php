<?php
/**
 * Modelo de Categoría General
 * Gestiona categorías para equipos y accesorios
 */
class CategoriaGeneral extends Model {
    protected $table = 'categorias_general';
    protected $primaryKey = 'id_categoria';

    /**
     * Obtener categorías activas
     */
    public function obtenerActivas($tipo = '') {
        if (!empty($tipo)) {
            return $this->where('activo = ? AND (tipo = ? OR tipo = ?)', [1, $tipo, 'AMBOS']);
        }
        return $this->where('activo = ?', [1]);
    }

    /**
     * Buscar categorías con filtro
     */
    public function buscar($filtro = '', $tipo = '') {
        $sql = "SELECT * FROM {$this->table} WHERE activo = 1";
        $params = [];

        if (!empty($tipo)) {
            $sql .= " AND (tipo = ? OR tipo = ?)";
            $params[] = $tipo;
            $params[] = 'AMBOS';
        }

        if (!empty($filtro)) {
            $sql .= " AND (nombre LIKE ? OR descripcion LIKE ? OR codigo_base LIKE ?)";
            $filtroParam = "%$filtro%";
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
        }

        $sql .= " ORDER BY tipo ASC, nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Registrar nueva categoría
     */
    public function registrar($data) {
        // Verificar si ya existe el nombre
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE nombre = ?");
        $stmt->execute([$data['nombre']]);
        $existente = $stmt->fetch();

        if ($existente) {
            return ['success' => false, 'message' => 'Ya existe una categoría con este nombre'];
        }

        $data['activo'] = 1;
        $this->insert($data);
        return ['success' => true, 'message' => 'Categoría registrada correctamente'];
    }

    /**
     * Actualizar categoría
     */
    public function actualizar($id, $data) {
        return $this->update($id, $data);
    }

    /**
     * Activar/desactivar categoría
     */
    public function toggleActivo($id) {
        $categoria = $this->find($id);
        if (!$categoria) {
            return false;
        }

        $nuevoEstado = $categoria['activo'] == 1 ? 0 : 1;
        return $this->update($id, ['activo' => $nuevoEstado]);
    }

    /**
     * Obtener estadísticas de uso
     */
    public function estadisticasUso() {
        $sql = "SELECT 
                    c.id_categoria,
                    c.nombre,
                    c.tipo,
                    c.codigo_base,
                    COUNT(DISTINCT e.id_equipo) as total_equipos,
                    COUNT(DISTINCT a.id_accesorio) as total_accesorios
                FROM {$this->table} c
                LEFT JOIN equipos e ON c.codigo_base = e.codigo
                LEFT JOIN accesorios a ON c.id_categoria = a.id_categoria
                WHERE c.activo = 1
                GROUP BY c.id_categoria, c.nombre, c.tipo, c.codigo_base
                ORDER BY c.tipo, c.nombre";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Eliminar categoría
     */
    public function eliminar($id) {
        $categoria = $this->find($id);
        if (!$categoria) {
            return ['success' => false, 'message' => 'Categoría no encontrada'];
        }

        // Verificar si tiene items asociados
        $total = 0;
        
        if (in_array($categoria['tipo'], ['EQUIPO', 'AMBOS'])) {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM equipos WHERE codigo = ?");
            $stmt->execute([$categoria['codigo_base']]);
            $resultado = $stmt->fetch();
            $total += $resultado['total'];
        }

        if (in_array($categoria['tipo'], ['ACCESORIO', 'AMBOS'])) {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM accesorios WHERE id_categoria = ?");
            $stmt->execute([$id]);
            $resultado = $stmt->fetch();
            $total += $resultado['total'];
        }

        if ($total > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar: hay ' . $total . ' item(s) asociados'];
        }

        $this->delete($id);
        return ['success' => true, 'message' => 'Categoría eliminada correctamente'];
    }
}
