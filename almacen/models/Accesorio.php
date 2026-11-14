<?php
/**
 * Modelo de Accesorio
 */
class Accesorio extends Model {
    protected $table = 'accesorios';
    protected $primaryKey = 'id_accesorio';

    /**
     * Obtener accesorios con stock
     */
    public function conStock() {
        return $this->where('cantidad > 0 AND estado = ?', ['DISPONIBLE']);
    }

    /**
     * Buscar accesorios con filtro avanzado
     */
    public function buscar($filtro = '', $codigo = '', $unidad_medida = '', $estado = '') {
        $sql = "SELECT a.*, p.nombre_empresa AS proveedor_nombre
                FROM {$this->table} a
                LEFT JOIN proveedores p ON a.id_proveedor = p.id_proveedor
                WHERE 1=1";
        $params = [];

        if (!empty($codigo)) {
            $sql .= " AND a.codigo LIKE ?";
            $params[] = "%$codigo%";
        }

        if (!empty($unidad_medida)) {
            $sql .= " AND a.unidad_medida = ?";
            $params[] = $unidad_medida;
        }

        if (!empty($estado)) {
            $sql .= " AND a.estado = ?";
            $params[] = $estado;
        }

        if (!empty($filtro)) {
            $sql .= " AND (a.nombre LIKE ? OR a.codigo LIKE ? OR a.modelo LIKE ? OR a.marca LIKE ? OR p.nombre_empresa LIKE ?)";
            $filtroParam = "%$filtro%";
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
        }

        $sql .= " ORDER BY a.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Buscar por código específico
     */
    public function findByCodigo($codigo) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE codigo = ?");
        $stmt->execute([$codigo]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener resumen de accesorios por código
     */
    public function resumenPorCodigo() {
        $sql = "SELECT 
                    codigo,
                    nombre,
                    unidad_medida,
                    SUM(cantidad) as total_cantidad,
                    SUM(CASE WHEN estado = 'DISPONIBLE' THEN cantidad ELSE 0 END) as disponibles,
                    SUM(CASE WHEN cantidad < 10 THEN 1 ELSE 0 END) as stock_bajo
                FROM {$this->table}
                GROUP BY codigo, nombre, unidad_medida
                ORDER BY nombre";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Registrar nuevo accesorio
     */
    public function registrar($data) {
        $data['estado'] = 'DISPONIBLE';
        $data['fecha_entrada'] = $data['fecha_entrada'] ?? date('Y-m-d');

        // Verificar si ya existe el mismo accesorio con mismo código
        if (!empty($data['codigo'])) {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE codigo = ? AND unidad_medida = ?");
            $stmt->execute([$data['codigo'], $data['unidad_medida']]);
            $existente = $stmt->fetch();

            if ($existente) {
                // Actualizar cantidad
                $nuevaCantidad = $existente['cantidad'] + $data['cantidad'];
                return $this->update($existente['id_accesorio'], ['cantidad' => $nuevaCantidad]);
            }
        }

        return $this->insert($data);
    }

    /**
     * Actualizar cantidad
     */
    public function actualizarCantidad($id, $cantidad, $operacion = 'sumar') {
        $accesorio = $this->find($id);
        if (!$accesorio) {
            return false;
        }

        $nuevaCantidad = $operacion === 'sumar'
            ? $accesorio['cantidad'] + $cantidad
            : $accesorio['cantidad'] - $cantidad;

        if ($nuevaCantidad < 0) {
            return false;
        }

        return $this->update($id, ['cantidad' => $nuevaCantidad]);
    }

    /**
     * Obtener estadísticas
     */
    public function estadisticas() {
        $sql = "SELECT
                    COUNT(*) as total_accesorios,
                    SUM(cantidad) as total_cantidad,
                    AVG(cantidad) as promedio_cantidad,
                    SUM(CASE WHEN cantidad < 10 THEN 1 ELSE 0 END) as stock_bajo,
                    SUM(CASE WHEN estado = 'DISPONIBLE' THEN cantidad ELSE 0 END) as total_disponibles
                FROM {$this->table}";

        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }

    /**
     * Obtener unidades de medida únicas
     */
    public function obtenerUnidadesMedida() {
        $sql = "SELECT DISTINCT unidad_medida FROM {$this->table} WHERE unidad_medida IS NOT NULL ORDER BY unidad_medida";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtener códigos únicos
     */
    public function obtenerCodigosUnicos() {
        $sql = "SELECT DISTINCT codigo FROM {$this->table} WHERE codigo IS NOT NULL ORDER BY codigo";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function referenciaPorCodigo($codigo) {
        new TipoAccesorio();

        $sql = "SELECT
                    a.codigo,
                    a.nombre,
                    a.unidad_medida,
                    a.modelo,
                    a.marca,
                    a.calidad,
                    a.observaciones,
                    t.descripcion as tipo_descripcion,
                    t.categoria as tipo_categoria,
                    t.observaciones as tipo_observaciones
                FROM {$this->table} a
                LEFT JOIN tipos_accesorios t ON a.codigo = t.codigo
                WHERE a.codigo = ?
                ORDER BY a.created_at DESC, a.id_accesorio DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$codigo]);
        $referencia = $stmt->fetch();

        if ($referencia) {
            return $referencia;
        }

        $tipoModel = new TipoAccesorio();
        $tipo = $tipoModel->findByCodigo($codigo);

        if (!$tipo) {
            return null;
        }

        return [
            'codigo' => $tipo['codigo'],
            'nombre' => $tipo['descripcion'],
            'unidad_medida' => null,
            'modelo' => null,
            'marca' => null,
            'calidad' => 'NUEVO',
            'costo' => null,
            'id_proveedor' => null,
            'observaciones' => $tipo['observaciones'],
            'tipo_descripcion' => $tipo['descripcion'],
            'tipo_categoria' => $tipo['categoria'],
            'tipo_observaciones' => $tipo['observaciones'],
        ];
    }
}
