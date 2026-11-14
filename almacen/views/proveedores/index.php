<!-- Listado de Proveedores - Diseño Sobrio 2026 -->
<div class="fade-in">
    <!-- Encabezado -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary fw-semibold">
                            <i class="bi bi-truck"></i> Proveedores
                        </h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalProveedor">
                            <i class="bi bi-plus-circle"></i> <span class="d-none d-md-inline">Nuevo Proveedor</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3 fw-semibold">
                        <i class="bi bi-funnel"></i> Filtros
                        <button class="btn btn-sm btn-link float-end text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </h6>
                    <div class="collapse show" id="filtrosCollapse">
                        <form method="GET" class="row g-2">
                            <input type="hidden" name="controller" value="proveedores">
                            
                            <div class="col-12 col-md-6">
                                <label class="form-label form-label-sm fw-semibold">Buscar</label>
                                <input type="text" class="form-control form-control-sm" name="filtro" 
                                       placeholder="Nombre, NIT, email..." 
                                       value="<?= htmlspecialchars($filtro) ?>">
                            </div>
                            
                            <div class="col-6 col-md-3">
                                <label class="form-label form-label-sm fw-semibold">Estado</label>
                                <select class="form-select form-select-sm" name="estado">
                                    <option value="activos" <?= $estado === 'activos' ? 'selected' : '' ?>>Activos</option>
                                    <option value="inactivos" <?= $estado === 'inactivos' ? 'selected' : '' ?>>Inactivos</option>
                                    <option value="todos" <?= $estado === 'todos' ? 'selected' : '' ?>>Todos</option>
                                </select>
                            </div>
                            
                            <div class="col-12 col-md-3 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                                <a href="index.php?controller=proveedores" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-circle"></i> Limpiar
                                </a>
                            </div>
                            
                            <div class="col-12 mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> <?= count($proveedores) ?> proveedor(es) encontrados
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0" id="tablaProveedores">
                            <thead>
                                <tr>
                                    <th class="fw-semibold">Empresa</th>
                                    <th class="fw-semibold">NIT</th>
                                    <th class="fw-semibold">Contacto</th>
                                    <th class="fw-semibold">Teléfono</th>
                                    <th class="fw-semibold">Email</th>
                                    <th class="fw-semibold">Ciudad</th>
                                    <th class="fw-semibold">Estado</th>
                                    <th class="fw-semibold text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($proveedores)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No se encontraron proveedores</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($proveedores as $prov): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($prov['nombre_empresa']) ?></strong></td>
                                        <td>
                                            <?php if (!empty($prov['nit'])): ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($prov['nit']) ?></span>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($prov['nombre_contacto'] ?? '-') ?>
                                            <?php if (!empty($prov['cargo_contacto'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($prov['cargo_contacto']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($prov['telefono'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($prov['email'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($prov['ciudad'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $prov['activo'] ? 'success' : 'secondary' ?>">
                                                <?= $prov['activo'] ? 'ACTIVO' : 'INACTIVO' ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="index.php?controller=proveedores&action=editar&id=<?= $prov['id_proveedor'] ?>"
                                                   class="btn btn-outline-primary btn-sm" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="javascript:void(0)"
                                                   onclick="confirmarEliminacion('index.php?controller=proveedores&action=eliminar&id=<?= $prov['id_proveedor'] ?>')"
                                                   class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </a>
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
        </div>
    </div>
</div>

<!-- Modal Nuevo Proveedor -->
<div class="modal fade" id="modalProveedor" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="index.php?controller=proveedores&action=guardar">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold"><i class="bi bi-truck"></i> Nuevo Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label form-label-sm fw-semibold">Nombre de Empresa *</label>
                            <input type="text" name="nombre_empresa" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm fw-semibold">NIT</label>
                            <input type="text" name="nit" class="form-control" placeholder="Número de Identificación Tributario">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm fw-semibold">Nombre de Contacto</label>
                            <input type="text" name="nombre_contacto" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm fw-semibold">Cargo</label>
                            <input type="text" name="cargo_contacto" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm fw-semibold">Teléfono</label>
                            <input type="text" name="telefono" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm fw-semibold">Celular</label>
                            <input type="text" name="celular" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm fw-semibold">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label form-label-sm fw-semibold">Dirección</label>
                            <input type="text" name="direccion" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label form-label-sm fw-semibold">Productos</label>
                            <textarea name="productos" class="form-control" rows="2" placeholder="Lista de productos que ofrece"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label form-label-sm fw-semibold">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="activo" checked value="1">
                                <label class="form-check-label">Proveedor Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tablaProveedores').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 10,
        order: [[0, 'asc']],
        responsive: false,
        dom: '<"row"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>'
    });
});
</script>
