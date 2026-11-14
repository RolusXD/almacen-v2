<!-- Formulario de Usuario -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-<?= $accion === 'crear' ? 'plus-circle' : 'pencil' ?>"></i>
        <?= $title ?>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?controller=usuarios&action=<?= $accion === 'crear' ? 'guardar' : 'actualizar' ?>">
            <?php if ($accion === 'editar' && $usuario): ?>
            <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre_completo" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required
                           value="<?= htmlspecialchars($usuario['nombre_completo'] ?? '') ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="usuario" class="form-label">Nombre de Usuario <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required
                           value="<?= htmlspecialchars($usuario['usuario'] ?? '') ?>">
                    <small class="text-muted">Será usado para iniciar sesión</small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="carnet" class="form-label">Carnet (ID Personal) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="carnet" name="carnet" required
                           value="<?= htmlspecialchars($usuario['carnet'] ?? '') ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required
                           value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required
                           value="<?= $usuario['fecha_nacimiento'] ?? '' ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="rol" class="form-label">Rol <span class="text-danger">*</span></label>
                    <select class="form-select" id="rol" name="rol" required>
                        <option value="usuario" <?= ($usuario['rol'] ?? '') === 'usuario' ? 'selected' : '' ?>>Usuario</option>
                        <option value="admin" <?= ($usuario['rol'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
            </div>
            
            <?php if ($accion === 'crear'): ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="8">
                    <small class="text-muted">Mínimo 8 caracteres, 1 mayúscula, 1 minúscula, 1 número</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="password_confirm" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="8">
                </div>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="activo" name="activo" checked>
                <label class="form-check-label" for="activo">Usuario Activo</label>
            </div>
            <?php else: ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nueva_password" class="form-label">Nueva Contraseña (opcional)</label>
                    <input type="password" class="form-control" id="nueva_password" name="nueva_password" minlength="8">
                    <small class="text-muted">Dejar en blanco para mantener la contraseña actual</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label d-block">Estado del Usuario</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="activo" id="activo_si" value="1" 
                               <?= ($usuario['activo'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="activo_si">Activo</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="activo" id="activo_no" value="0" 
                               <?= !($usuario['activo'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="activo_no">Inactivo</label>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> <?= $accion === 'crear' ? 'Guardar' : 'Actualizar' ?>
                </button>
                <a href="index.php?controller=usuarios" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Validar que las contraseñas coincidan al crear usuario
<?php if ($accion === 'crear'): ?>
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    
    if (password !== passwordConfirm) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
        return false;
    }
});
<?php endif; ?>
</script>
