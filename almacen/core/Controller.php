<?php
/**
 * Controlador base
 * Proporciona métodos comunes para todos los controladores
 */
class Controller {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Renderizar una vista
     */
    protected function render($view, $data = []) {
        $data = array_merge($this->getLayoutData(), $data);
        extract($data);
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/' . $view . '.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    /**
     * Renderizar una vista sin layout (para AJAX)
     */
    protected function renderJson($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    
    /**
     * Redireccionar a una URL
     */
    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
    
    /**
     * Verificar si es una petición POST
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Sanitizar entrada
     */
    protected function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
    
    /**
     * Mostrar mensaje flash
     */
    protected function setFlash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Obtener y limpiar mensaje flash
     */
    protected function getFlash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    /**
     * Verificar que exista una sesión activa
     */
    protected function requireAuth($redirect = 'index.php?controller=auth&action=login') {
        if (!isset($_SESSION['user_id'])) {
            $this->setFlash('error', 'Debe iniciar sesión para acceder');
            $this->redirect($redirect);
        }
    }

    /**
     * Verificar permisos de administrador
     */
    protected function requireAdmin($redirect = 'index.php?controller=dashboard') {
        $this->requireAuth();

        if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
            $this->setFlash('error', 'No tiene permisos para realizar esta acción');
            $this->redirect($redirect);
        }
    }

    /**
     * Datos compartidos del layout principal.
     */
    protected function getLayoutData() {
        $layoutNotifications = [];
        $layoutNotificationCount = 0;

        try {
            $stockBajo = (int)($this->db->query("
                SELECT COUNT(*) AS total
                FROM accesorios
                WHERE estado = 'DISPONIBLE' AND cantidad < 10
            ")->fetch()['total'] ?? 0);

            $prestamosVencidos = (int)($this->db->query("
                SELECT COUNT(*) AS total
                FROM prestamos
                WHERE estado = 'PRESTADO'
                  AND fecha_devolucion_esperada < CURRENT_DATE
            ")->fetch()['total'] ?? 0);

            $mantenimientosActivos = (int)($this->db->query("
                SELECT COUNT(*) AS total
                FROM mantenimientos
                WHERE estado = 'EN_PROCESO'
            ")->fetch()['total'] ?? 0);

            if ($stockBajo > 0) {
                $layoutNotifications[] = [
                    'type' => 'warning',
                    'icon' => 'exclamation-triangle',
                    'title' => 'Stock bajo',
                    'message' => $stockBajo . ' accesorio(s) requieren reposición.',
                    'meta' => 'Inventario actual',
                    'link' => 'index.php?controller=accesorios'
                ];
            }

            if ($prestamosVencidos > 0) {
                $layoutNotifications[] = [
                    'type' => 'danger',
                    'icon' => 'hand-index-thumb',
                    'title' => 'Préstamos vencidos',
                    'message' => $prestamosVencidos . ' préstamo(s) superaron la fecha de devolución.',
                    'meta' => 'Seguimiento requerido',
                    'link' => 'index.php?controller=prestamos'
                ];
            }

            if ($mantenimientosActivos > 0) {
                $layoutNotifications[] = [
                    'type' => 'info',
                    'icon' => 'gear',
                    'title' => 'Mantenimientos activos',
                    'message' => $mantenimientosActivos . ' equipo(s) siguen en proceso técnico.',
                    'meta' => 'Estado de taller',
                    'link' => 'index.php?controller=mantenimientos'
                ];
            }
        } catch (Throwable $e) {
            // Si faltan tablas durante despliegues o migraciones, el layout sigue funcionando.
        }

        $layoutNotificationCount = count($layoutNotifications);

        if ($layoutNotificationCount === 0) {
            $layoutNotifications[] = [
                'type' => 'success',
                'icon' => 'check-circle',
                'title' => 'Operación estable',
                'message' => 'No hay alertas críticas registradas en este momento.',
                'meta' => 'Panel actualizado',
                'link' => 'index.php?controller=dashboard'
            ];
        }

        return [
            'layoutNotifications' => $layoutNotifications,
            'layoutNotificationCount' => $layoutNotificationCount
        ];
    }
}
