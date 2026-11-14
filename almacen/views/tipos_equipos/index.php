<!-- Gestión de Tipos de Equipos - Diseño Responsivo 2026 -->
<div class="fade-in">
    <!-- Encabezado -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <h5 class="mb-0 text-primary fw-semibold">
                        <i class="bi bi-tags"></i> Tipos de Equipos
                    </h5>
                    <small class="text-muted">Gestione los códigos y tipos de equipos de forma dinámica</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-3">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div>
                    <h6 class="text-muted text-uppercase mb-2 fw-semibold" style="font-size: 0.7rem;">Tipos Registrados</h6>
                    <h2 class="mb-0 fw-bold" style="font-size: 1.75rem;"><?= count($tipos) ?></h2>
                </div>
                <div class="stat-icon primary">
                    <i class="bi bi-tags"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card success">
                <div>
                    <h6 class="text-muted text-uppercase mb-2 fw-semibold" style="font-size: 0.7rem;">Equipos Asignados</h6>
                    <h2 class="mb-0 fw-bold" style="font-size: 1.75rem; color: #059669;"><?= array_sum(array_column($estadisticas ?? [], 'total_equipos')) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div>
                    <h6 class="text-muted text-uppercase mb-2 fw-semibold" style="font-size: 0.7rem;">Disponibles</h6>
                    <h2 class="mb-0 fw-bold" style="font-size: 1.75rem; color: #3182ce;"><?= array_sum(array_column($estadisticas ?? [], 'disponibles')) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card warning">
                <div>
                    <h6 class="text-muted text-uppercase mb-2 fw-semibold" style="font-size: 0.7rem;">Ocupados</h6>
                    <h2 class="mb-0 fw-bold" style="font-size: 1.75rem; color: #dd6b20;"><?= array_sum(array_column($estadisticas ?? [], 'ocupados')) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Acciones -->
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
                            <input type="hidden" name="controller" value="tipos_equipos">

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label form-label-sm fw-semibold">Buscar</label>
                                <input type="text" class="form-control form-control-sm" name="filtro"
                                       placeholder="Código o descripción..."
                                       value="<?= htmlspecialchars($filtro) ?>">
                            </div>

                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label form-label-sm fw-semibold">Categoría</label>
                                <select class="form-select form-select-sm" name="categoria">
                                    <option value="">Todas</option>
                                    <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['categoria']) ?>"
                                            <?= $categoria === $cat['categoria'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['categoria']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-2 col-lg-1 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search"></i>
                                </button>
                                <a href="index.php?controller=tipos_equipos&action=crear" class="btn btn-success btn-sm">
                                    <i class="bi bi-plus-circle"></i> Nuevo
                                </a>
                            </div>

                            <div class="col-12 mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> <?= count($tipos) ?> tipo(s) registrados
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Tipos - Diseño Responsivo -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0" id="tablaTipos" style="min-width: 100%;">
                            <thead>
                                <tr>
                                    <th class="fw-semibold" style="min-width: 100px;">CÓDIGO</th>
                                    <th class="fw-semibold" style="min-width: 200px;">DESCRIPCIÓN</th>
                                    <th class="fw-semibold" style="min-width: 100px;">CATEGORÍA</th>
                                    <th class="fw-semibold text-center" style="min-width: 80px;">PREFIJO</th>
                                    <th class="fw-semibold text-center" style="min-width: 80px;">TOTAL</th>
                                    <th class="fw-semibold text-center" style="min-width: 80px;">DISP</th>
                                    <th class="fw-semibold text-center" style="min-width: 80px;">OCUP</th>
                                    <th class="fw-semibold" style="min-width: 120px;">ESTADO</th>
                                    <th class="fw-semibold text-center" style="min-width: 150px;">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tipos)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-2">No hay tipos de equipos registrados</p>
                                        <a href="index.php?controller=tipos_equipos&action=crear" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-circle"></i> Crear primero
                                        </a>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($tipos as $tipo): ?>
                                    <?php 
                                    $estadistica = array_filter($estadisticas ?? [], fn($e) => $e['id_codigo'] == $tipo['id_codigo']);
                                    $estadistica = !empty($estadistica) ? reset($estadistica) : null;
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-info"><?= htmlspecialchars($tipo['codigo']) ?></span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($tipo['descripcion']) ?></strong>
                                            <?php if (!empty($tipo['observaciones'])): ?>
                                            <br><small class="text-muted"><i class="bi bi-chat-text"></i> <?= htmlspecialchars($tipo['observaciones']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($tipo['categoria']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <small class="text-muted"><?= htmlspecialchars($tipo['prefijo'] ?? '-') ?></small>
                                        </td>
                                        <td class="text-center fw-bold"><?= $estadistica['total_equipos'] ?? 0 ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-success"><?= $estadistica['disponibles'] ?? 0 ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning"><?= $estadistica['ocupados'] ?? 0 ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $tipo['activo'] ? 'success' : 'secondary' ?>">
                                                <?= $tipo['activo'] ? 'ACTIVO' : 'INACTIVO' ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="index.php?controller=tipos_equipos&action=editar&id=<?= $tipo['id_codigo'] ?>"
                                                   class="btn btn-outline-primary btn-sm" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="javascript:void(0)"
                                                   onclick="confirmarEliminacion('index.php?controller=tipos_equipos&action=eliminar&id=<?= $tipo['id_codigo'] ?>')"
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

<script>
$(document).ready(function() {
    $('#tablaTipos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 25,
        order: [[0, 'asc']],
        responsive: false,
        columnDefs: [
            { orderable: true, targets: [0, 1, 2, 3, 4, 5, 6] },
            { orderable: false, targets: [7, 8] }
        ]
    });
});
</script>
