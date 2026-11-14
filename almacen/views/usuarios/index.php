<!-- Gestión de Usuarios -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people"></i> Gestión de Usuarios</span>
        <a href="index.php?controller=usuarios&action=crear" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Nuevo Usuario
        </a>
    </div>
    <div class="card-body">
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-2 col-6">
                <div class="text-center p-3 bg-light rounded">
                    <h4 class="text-primary mb-0"><?= $estadisticas['total_usuarios'] ?></h4>
                    <small class="text-muted">Total</small>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="text-center p-3 bg-light rounded">
                    <h4 class="text-info mb-0"><?= $estadisticas['administradores'] ?></h4>
                    <small class="text-muted">Admins</small>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="text-center p-3 bg-light rounded">
                    <h4 class="text-success mb-0"><?= $estadisticas['activos'] ?></h4>
                    <small class="text-muted">Activos</small>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="text-center p-3 bg-light rounded">
                    <h4 class="text-danger mb-0"><?= $estadisticas['inactivos'] ?></h4>
                    <small class="text-muted">Inactivos</small>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="text-center p-3 bg-light rounded">
                    <h4 class="text-secondary mb-0"><?= $estadisticas['usuarios_normales'] ?></h4>
                    <small class="text-muted">Usuarios</small>
                </div>
            </div>
        </div>
        
        <!-- Filtros -->
        <form method="GET" class="row g-3 mb-4">
            <input type="hidden" name="controller" value="usuarios">
            <div class="col-md-4">
                <input type="text" class="form-control" name="filtro" 
                       placeholder="Buscar por nombre, usuario, email o carnet" 
                       value="<?= htmlspecialchars($filtro) ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="rol">
                    <option value="todos" <?= $rol === 'todos' ? 'selected' : '' ?>>Todos los Roles</option>
                    <option value="admin" <?= $rol === 'admin' ? 'selected' : '' ?>>Administradores</option>
                    <option value="usuario" <?= $rol === 'usuario' ? 'selected' : '' ?>>Usuarios</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="estado">
                    <option value="activos" <?= $estado === 'activos' ? 'selected' : '' ?>>Activos</option>
                    <option value="inactivos" <?= $estado === 'inactivos' ? 'selected' : '' ?>>Inactivos</option>
                    <option value="todos" <?= $estado === 'todos' ? 'selected' : '' ?>>Todos</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
            <div class="col-md-2">
                <a href="index.php?controller=usuarios" class="btn btn-secondary w-100">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>
        
        <!-- Tabla -->
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Carnet</th>
                        <th>Email</th>
                        <th>F. Nacimiento</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-2">No se encontraron usuarios</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $user): ?>
                        <tr>
                            <td><?= $user['id_usuario'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($user['usuario']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($user['nombre_completo']) ?></td>
                            <td><?= htmlspecialchars($user['carnet']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= date('d/m/Y', strtotime($user['fecha_nacimiento'])) ?></td>
                            <td>
                                <?php if ($user['rol'] === 'admin'): ?>
                                    <span class="badge bg-danger"><i class="bi bi-shield-check"></i> Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-info"><i class="bi bi-person"></i> Usuario</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['ultimo_acceso']): ?>
                                    <small><?= date('d/m/Y H:i', strtotime($user['ultimo_acceso'])) ?></small>
                                <?php else: ?>
                                    <small class="text-muted">Nunca</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="index.php?controller=usuarios&action=editar&id=<?= $user['id_usuario'] ?>" 
                                       class="btn btn-info" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($user['usuario'] !== 'admin'): ?>
                                        <?php if ($user['activo']): ?>
                                        <a href="index.php?controller=usuarios&action=cambiarEstado&id=<?= $user['id_usuario'] ?>&activo=0&csrf_token=<?= urlencode(csrf_token()) ?>"
                                           class="btn btn-warning" title="Desactivar">
                                            <i class="bi bi-pause-circle"></i>
                                        </a>
                                        <?php else: ?>
                                        <a href="index.php?controller=usuarios&action=cambiarEstado&id=<?= $user['id_usuario'] ?>&activo=1&csrf_token=<?= urlencode(csrf_token()) ?>"
                                           class="btn btn-success" title="Activar">
                                            <i class="bi bi-play-circle"></i>
                                        </a>
                                        <?php endif; ?>
                                        <a href="javascript:void(0)" 
                                           onclick="confirmarEliminacion('index.php?controller=usuarios&action=eliminar&id=<?= $user['id_usuario'] ?>')" 
                                           class="btn btn-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
