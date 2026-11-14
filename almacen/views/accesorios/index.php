<!-- Listado de Accesorios - Diseño Sobrio 2026 -->
<div class="fade-in">
    <!-- Encabezado con Título y Acciones -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0 text-primary fw-semibold">
                            <i class="bi bi-tools"></i> Accesorios
                        </h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalImportar">
                                <i class="bi bi-upload"></i> <span class="d-none d-md-inline">Importación</span>
                            </button>
                            <div class="btn-group">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-download"></i> <span class="d-none d-md-inline">Exportación</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="index.php?controller=accesorios&action=exportar&formato=excel&filtro=<?= urlencode($filtro) ?>&codigo=<?= urlencode($codigo) ?>&estado=<?= urlencode($estado ?? '') ?>"><i class="bi bi-file-earmark-excel"></i> Excel</a></li>
                                    <li><a class="dropdown-item" href="index.php?controller=accesorios&action=exportar&formato=csv&filtro=<?= urlencode($filtro) ?>&codigo=<?= urlencode($codigo) ?>&estado=<?= urlencode($estado ?? '') ?>"><i class="bi bi-file-text"></i> CSV</a></li>
                                </ul>
                            </div>
                            <a href="index.php?controller=accesorios&action=crear" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> <span class="d-none d-md-inline">Nuevo</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Avanzados -->
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
                            <input type="hidden" name="controller" value="accesorios">

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label form-label-sm fw-semibold">Buscar</label>
                                <input type="text" class="form-control form-control-sm" name="filtro"
                                       placeholder="Nombre, código, modelo..."
                                       value="<?= htmlspecialchars($filtro) ?>">
                            </div>

                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label form-label-sm fw-semibold">Código</label>
                                <input type="text" class="form-control form-control-sm" name="codigo"
                                       placeholder="Ej: FD1H, FADD"
                                       value="<?= htmlspecialchars($codigo) ?>">
                            </div>

                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label form-label-sm fw-semibold">Unidad</label>
                                <select class="form-select form-select-sm" name="unidad_medida">
                                    <option value="">Todas</option>
                                    <?php foreach ($unidadesMedida as $unidad): ?>
                                    <option value="<?= htmlspecialchars($unidad['unidad_medida']) ?>"
                                            <?= $unidad_medida === $unidad['unidad_medida'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($unidad['unidad_medida']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label form-label-sm fw-semibold">Estado</label>
                                <select class="form-select form-select-sm" name="estado">
                                    <option value="">Todos</option>
                                    <option value="DISPONIBLE" <?= ($estado ?? '') === 'DISPONIBLE' ? 'selected' : '' ?>>Disponibles</option>
                                    <option value="OCUPADO" <?= ($estado ?? '') === 'OCUPADO' ? 'selected' : '' ?>>Ocupados</option>
                                    <option value="MANTENIMIENTO" <?= ($estado ?? '') === 'MANTENIMIENTO' ? 'selected' : '' ?>>Mantenimiento</option>
                                    <option value="RETIRADO" <?= ($estado ?? '') === 'RETIRADO' ? 'selected' : '' ?>>Retirados</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-2 col-lg-1 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search"></i>
                                </button>
                                <a href="index.php?controller=accesorios" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-circle"></i> Limpiar
                                </a>
                            </div>

                            <div class="col-12 mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> <?= count($accesorios) ?> accesorio(s) encontrados
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Accesorios -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0 resizable-table" id="tablaAccesorios" style="min-width: 100%;">
                            <thead>
                                <tr>
                                    <th class="fw-semibold" style="min-width: 60px;">ID</th>
                                    <th class="fw-semibold" style="min-width: 100px;">CÓDIGO</th>
                                    <th class="fw-semibold" style="min-width: 200px;">DESCRIPCIÓN</th>
                                    <th class="fw-semibold text-center" style="min-width: 100px;">CANTIDAD</th>
                                    <th class="fw-semibold" style="min-width: 100px;">UNIDAD</th>
                                    <th class="fw-semibold" style="min-width: 120px;">MARCA</th>
                                    <th class="fw-semibold" style="min-width: 120px;">MODELO</th>
                                    <th class="fw-semibold text-center" style="min-width: 110px;">FECHA <i class="bi bi-arrow-down-up text-muted"></i></th>
                                    <th class="fw-semibold" style="min-width: 110px;">ESTADO</th>
                                    <th class="fw-semibold" style="min-width: 90px;">CALIDAD</th>
                                    <th class="fw-semibold text-center" style="min-width: 120px;">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($accesorios)): ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No se encontraron accesorios</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($accesorios as $accesorio): ?>
                                    <tr>
                                        <td class="fw-bold">#<?= $accesorio['id_accesorio'] ?></td>
                                        <td>
                                            <?php if (!empty($accesorio['codigo'])): ?>
                                            <span class="badge bg-info"><?= htmlspecialchars($accesorio['codigo']) ?></span>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($accesorio['nombre']) ?></strong></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $accesorio['cantidad'] < 10 ? 'danger' : 'success' ?>">
                                                <i class="bi bi-box"></i> <?= $accesorio['cantidad'] ?>
                                            </span>
                                            <?php if ($accesorio['cantidad'] < 10): ?>
                                            <br><small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Stock bajo</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($accesorio['unidad_medida'])): ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($accesorio['unidad_medida']) ?></span>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($accesorio['marca'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($accesorio['modelo'] ?? '-') ?></td>
                                        <td class="text-center" data-order="<?= htmlspecialchars($accesorio['fecha_entrada']) ?>"><?= date('d/m/Y', strtotime($accesorio['fecha_entrada'])) ?></td>
                                        <td>
                                            <?php
                                            $estadoClass = match($accesorio['estado']) {
                                                'DISPONIBLE' => 'bg-success',
                                                'OCUPADO' => 'bg-warning',
                                                'MANTENIMIENTO' => 'bg-info',
                                                'RETIRADO' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $estadoClass ?> text-truncate" style="max-width: 100px;" title="<?= $accesorio['estado'] ?>">
                                                <?= $accesorio['estado'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $calidadClass = match($accesorio['calidad']) {
                                                'NUEVO' => 'bg-success',
                                                'BUENO' => 'bg-primary',
                                                'REGULAR' => 'bg-warning',
                                                'MALO' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $calidadClass ?>"><?= $accesorio['calidad'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="index.php?controller=accesorios&action=editar&id=<?= $accesorio['id_accesorio'] ?>"
                                                   class="btn btn-outline-primary btn-sm" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="javascript:void(0)"
                                                   onclick="confirmarEliminacion('index.php?controller=accesorios&action=eliminar&id=<?= $accesorio['id_accesorio'] ?>')"
                                                   class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total general:</td>
                                    <td colspan="8" class="text-end fw-bold">
                                        <?php
                                        $total = array_sum(array_column($accesorios, 'cantidad'));
                                        echo $total . ' unidad(es)';
                                        ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Importar -->
<div class="modal fade" id="modalImportar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="index.php?controller=accesorios&action=importar" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold"><i class="bi bi-upload"></i> Importar Accesorios</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="archivo" class="form-label">Archivo CSV</label>
                        <input type="file" class="form-control" id="archivo" name="archivo" accept=".csv" required>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <strong>Columnas:</strong> Código, Descripción, Cantidad, Unidad de medida, Marca, Modelo, Serie/MAC, Fecha de ingreso, Calidad, Precio por unidad, Proveedor, Observaciones<br>
                        <strong>Separador obligatorio:</strong> comas (,)<br>
                        <strong>Fecha:</strong> usar formato AAAA-MM-DD<br>
                        <strong>Proveedor:</strong> debe coincidir con uno existente<br>
                        <strong>Importante:</strong> si se repite Código + Unidad, el sistema suma la cantidad.<br>
                        <strong>Nota:</strong> la exportación del sistema no es la misma plantilla de importación.<br>
                        <a href="assets/templates/plantilla_importacion_accesorios.csv" class="alert-link" download>Descargar plantilla vacía</a> |
                        <a href="assets/templates/ejemplo_importacion_accesorios.csv" class="alert-link" download>Descargar ejemplo</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Importar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTables
    $('#tablaAccesorios').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 25,
        order: [[0, 'desc']],
        responsive: false,
        scrollX: true,
        autoWidth: false,
        dom: '<"row"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
        drawCallback: function() {
            $('.paginate_button').addClass('btn btn-sm btn-outline-primary');
        }
    });

    // Las columnas se pueden redimensionar desde el potenciador global de tablas.
});

// Función para hacer las columnas redimensionables
function makeTableResizable() {
    const table = document.querySelector('.resizable-table');
    if (!table) return;

    const headers = table.querySelectorAll('thead th');
    
    headers.forEach((th, index) => {
        // Ignorar última columna (acciones)
        if (index === headers.length - 1) return;

        const resizer = document.createElement('div');
        resizer.className = 'resizer';
        resizer.style.cssText = `
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            cursor: col-resize;
            user-select: none;
            z-index: 10;
        `;
        
        th.style.position = 'relative';
        th.appendChild(resizer);

        let x = 0;
        let w = 0;

        const mouseDownHandler = function(e) {
            x = e.clientX;
            const styles = window.getComputedStyle(th);
            w = parseInt(styles.width, 10);

            document.addEventListener('mousemove', mouseMoveHandler);
            document.addEventListener('mouseup', mouseUpHandler);
            resizer.style.cursor = 'col-resize';
        };

        const mouseMoveHandler = function(e) {
            const dx = e.clientX - x;
            th.style.width = `${w + dx}px`;
        };

        const mouseUpHandler = function() {
            document.removeEventListener('mousemove', mouseMoveHandler);
            document.removeEventListener('mouseup', mouseUpHandler);
        };

        resizer.addEventListener('mousedown', mouseDownHandler);
    });
}
</script>

<style>
/* Estilos para redimensionar columnas */
.resizable-table thead th {
    position: relative;
    overflow: visible;
}

.resizable-table .resizer:hover {
    background: var(--primary-color);
}

/* Badges con texto truncado */
.badge.text-truncate {
    display: inline-block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Ajustar padding de celdas para mejor visibilidad */
.resizable-table tbody td {
    vertical-align: middle;
    line-height: 1.4;
}
</style>
