<!-- Perfil de Usuario - Diseño Sobrio 2026 -->
<div class="fade-in">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-person-circle"></i> Mi Perfil
                    </h5>
                </div>
                <div class="card-body p-4">
                    <!-- Información del Usuario -->
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            <div class="user-avatar-lg mb-3" 
                                 style="width: 120px; height: 120px; margin: 0 auto; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 3rem; border-radius: 50%;">
                                <?= strtoupper(substr($usuario['nombre_completo'] ?? 'U', 0, 1)) ?>
                            </div>
                            <h4 class="fw-semibold"><?= htmlspecialchars($usuario['nombre_completo']) ?></h4>
                            <span class="badge bg-<?= $usuario['rol'] === 'admin' ? 'danger' : 'info' ?>" style="font-size: 0.8rem;">
                                <?= strtoupper($usuario['rol']) ?>
                            </span>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Usuario</label>
                                    <div class="fw-semibold"><?= htmlspecialchars($usuario['usuario']) ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Carnet</label>
                                    <div class="fw-semibold"><?= htmlspecialchars($usuario['carnet']) ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Email</label>
                                    <div class="fw-semibold"><?= htmlspecialchars($usuario['email']) ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Fecha de Nacimiento</label>
                                    <div class="fw-semibold">
                                        <?php if (!empty($usuario['fecha_nacimiento'])): ?>
                                            <?= date('d/m/Y', strtotime($usuario['fecha_nacimiento'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">No registrado</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Estado</label>
                                    <div>
                                        <span class="badge bg-<?= $usuario['activo'] == 1 ? 'success' : 'secondary' ?>">
                                            <?= $usuario['activo'] == 1 ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Último Acceso</label>
                                    <div class="fw-semibold">
                                        <?php if (!empty($usuario['ultimo_acceso'])): ?>
                                            <?= date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Sin registro</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de Edición -->
                    <hr class="my-4">
                    
                    <h6 class="fw-semibold mb-3"><i class="bi bi-pencil"></i> Editar Información</h6>
                    
                    <form method="POST" action="index.php?controller=usuarios&action=actualizarPerfil" class="row g-3">
                        <?= csrf_field() ?>
                        <div class="col-md-6">
                            <label for="nombre_completo" class="form-label form-label-sm fw-semibold">Nombre Completo</label>
                            <input type="text" class="form-control form-control-sm" id="nombre_completo" name="nombre_completo"
                                   value="<?= htmlspecialchars($usuario['nombre_completo']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="carnet" class="form-label form-label-sm fw-semibold">Carnet (ID Personal)</label>
                            <input type="text" class="form-control form-control-sm" id="carnet" name="carnet"
                                   value="<?= htmlspecialchars($usuario['carnet']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label form-label-sm fw-semibold">Correo Electrónico</label>
                            <input type="email" class="form-control form-control-sm" id="email" name="email"
                                   value="<?= htmlspecialchars($usuario['email']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="fecha_nacimiento" class="form-label form-label-sm fw-semibold">Fecha de Nacimiento</label>
                            <input type="date" class="form-control form-control-sm" id="fecha_nacimiento" name="fecha_nacimiento"
                                   value="<?= $usuario['fecha_nacimiento'] ?>">
                        </div>

                        <div class="col-12">
                            <h6 class="fw-semibold mt-4"><i class="bi bi-lock"></i> Cambiar Contraseña</h6>
                            <p class="text-muted small">Deje en blanco si no desea cambiar la contraseña</p>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label form-label-sm fw-semibold">Nueva Contraseña</label>
                            <input type="password" class="form-control form-control-sm" id="password" name="password"
                                   placeholder="Mínimo 6 caracteres">
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirm" class="form-label form-label-sm fw-semibold">Confirmar Contraseña</label>
                            <input type="password" class="form-control form-control-sm" id="password_confirm" name="password_confirm"
                                   placeholder="Repita la contraseña">
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
                            <a href="index.php?controller=dashboard" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.user-avatar-lg {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 3rem;
    margin: 0 auto;
}
</style>
