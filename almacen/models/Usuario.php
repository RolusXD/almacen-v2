<?php
/**
 * Modelo de Usuario
 * Gestión completa de usuarios con autenticación segura
 */
class Usuario extends Model {
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    
    /**
     * Buscar usuario por nombre de usuario
     */
    public function findByUsuario($usuario) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE usuario = ?");
        $stmt->execute([$usuario]);
        return $stmt->fetch();
    }
    
    /**
     * Buscar usuario por carnet
     */
    public function findByCarnet($carnet) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE carnet = ?");
        $stmt->execute([$carnet]);
        return $stmt->fetch();
    }
    
    /**
     * Buscar usuario por email
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Verificar credenciales (usuario y contraseña)
     */
    public function verificarCredenciales($usuario, $password) {
        $user = $this->findByUsuario($usuario);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['activo']) {
                // Actualizar último acceso
                $this->actualizarUltimoAcceso($user['id_usuario']);
                return $user;
            }
        }
        
        return null;
    }
    
    /**
     * Actualizar último acceso del usuario
     */
    public function actualizarUltimoAcceso($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET ultimo_acceso = NOW() WHERE id_usuario = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Registrar nuevo usuario con validaciones
     */
    public function registrar($data) {
        // Validar que el usuario no exista
        if ($this->findByUsuario($data['usuario'])) {
            return ['success' => false, 'message' => 'El nombre de usuario ya está en uso'];
        }
        
        // Validar que el carnet no exista
        if ($this->findByCarnet($data['carnet'])) {
            return ['success' => false, 'message' => 'El carnet ya está registrado'];
        }
        
        // Validar que el email no exista
        if ($this->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'El correo electrónico ya está registrado'];
        }
        
        // Validar fortaleza de contraseña
        if (!$this->validarPassword($data['password'])) {
            return [
                'success' => false, 
                'message' => 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número'
            ];
        }
        
        // Hashear contraseña con bcrypt
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        unset($data['password']);
        
        // Establecer valores por defecto
        $data['activo'] = $data['activo'] ?? 1;
        $data['rol'] = $data['rol'] ?? 'usuario';
        
        try {
            $this->insert($data);
            return ['success' => true, 'message' => 'Usuario registrado correctamente'];
        } catch (PDOException $e) {
            $message = APP_ENV === 'development'
                ? 'Error al registrar el usuario: ' . $e->getMessage()
                : 'Error al registrar el usuario';
            return ['success' => false, 'message' => $message];
        }
    }
    
    /**
     * Actualizar usuario (sin contraseña)
     */
    public function actualizar($id, $data) {
        // Verificar si el usuario existe
        $usuario = $this->find($id);
        if (!$usuario) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        
        // Si se intenta cambiar el usuario, verificar que no exista
        if (isset($data['usuario']) && $data['usuario'] !== $usuario['usuario']) {
            if ($this->findByUsuario($data['usuario'])) {
                return ['success' => false, 'message' => 'El nombre de usuario ya está en uso'];
            }
        }
        
        // Si se intenta cambiar el carnet, verificar que no exista
        if (isset($data['carnet']) && $data['carnet'] !== $usuario['carnet']) {
            if ($this->findByCarnet($data['carnet'])) {
                return ['success' => false, 'message' => 'El carnet ya está registrado'];
            }
        }
        
        // Si se intenta cambiar el email, verificar que no exista
        if (isset($data['email']) && $data['email'] !== $usuario['email']) {
            if ($this->findByEmail($data['email'])) {
                return ['success' => false, 'message' => 'El correo electrónico ya está registrado'];
            }
        }
        
        try {
            $this->update($id, $data);
            return ['success' => true, 'message' => 'Usuario actualizado correctamente'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al actualizar el usuario'];
        }
    }
    
    /**
     * Actualizar contraseña
     */
    public function actualizarPassword($id, $nuevoPassword) {
        if (!$this->validarPassword($nuevoPassword)) {
            return ['success' => false, 'message' => 'La contraseña no cumple con los requisitos de seguridad'];
        }
        
        $passwordHash = password_hash($nuevoPassword, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        return $this->update($id, ['password_hash' => $passwordHash]);
    }
    
    /**
     * Cambiar estado del usuario (activo/inactivo)
     */
    public function cambiarEstado($id, $activo) {
        return $this->update($id, ['activo' => $activo ? 1 : 0]);
    }
    
    /**
     * Eliminar usuario (solo si no tiene registros relacionados)
     */
    public function eliminar($id) {
        $usuario = $this->find($id);
        if (!$usuario) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        
        // No permitir eliminar el usuario administrador principal
        if ($usuario['usuario'] === 'admin') {
            return ['success' => false, 'message' => 'No se puede eliminar el usuario administrador principal'];
        }
        
        // Verificar si tiene movimientos registrados
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM movimientos_equipos WHERE created_by = ?");
        $stmt->execute([$id]);
        $resultado = $stmt->fetch();
        
        if ($resultado['total'] > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar: el usuario tiene movimientos registrados'];
        }
        
        // En lugar de eliminar físicamente, desactivamos el usuario
        return $this->cambiarEstado($id, 0) 
            ? ['success' => true, 'message' => 'Usuario desactivado correctamente']
            : ['success' => false, 'message' => 'Error al desactivar el usuario'];
    }
    
    /**
     * Obtener todos los usuarios con paginación
     */
    public function obtenerTodos($limit = 50, $offset = 0) {
        $limit = max(1, min(200, (int)$limit));
        $offset = max(0, (int)$offset);

        $stmt = $this->db->query("
            SELECT * FROM {$this->table} 
            ORDER BY created_at DESC 
            LIMIT {$limit} OFFSET {$offset}
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar usuarios con filtro
     */
    public function buscar($filtro = '', $rol = 'todos', $estado = 'todos') {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($rol !== 'todos') {
            $sql .= " AND rol = ?";
            $params[] = $rol;
        }
        
        if ($estado !== 'todos') {
            $sql .= " AND activo = ?";
            $params[] = $estado === 'activos' ? 1 : 0;
        }
        
        if (!empty($filtro)) {
            $sql .= " AND (nombre_completo LIKE ? OR usuario LIKE ? OR email LIKE ? OR carnet LIKE ?)";
            $filtroParam = "%$filtro%";
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
            $params[] = $filtroParam;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener estadísticas de usuarios
     */
    public function estadisticas() {
        $sql = "SELECT 
                    COUNT(*) as total_usuarios,
                    SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as administradores,
                    SUM(CASE WHEN rol = 'usuario' THEN 1 ELSE 0 END) as usuarios_normales,
                    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as inactivos
                FROM {$this->table}";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
    
    /**
     * Validar fortaleza de contraseña
     * Requisitos: mín 8 caracteres, 1 mayúscula, 1 minúscula, 1 número
     */
    private function validarPassword($password) {
        if (strlen($password) < 8) {
            return false;
        }
        
        // Al menos una mayúscula
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // Al menos una minúscula
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // Al menos un número
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Generar token de recuperación de contraseña
     */
    public function generarTokenRecuperacion($id) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET reset_token = ?, reset_token_expires = ? 
            WHERE id_usuario = ?
        ");
        $stmt->execute([$token, $expires, $id]);
        
        return $token;
    }
}
