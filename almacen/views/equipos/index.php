<!-- Listado de Equipos - Diseño Sobrio y Compacto 2026 -->
<div class="fade-in">
    <!-- Encabezado con Título y Acciones -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0 text-primary fw-semibold">
                            <i class="bi bi-pc-display"></i> Equipos
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
                                    <li><a class="dropdown-item" href="index.php?controller=equipos&action=exportar&formato=excel&filtro=<?= urlencode($filtro) ?>&estado=<?= urlencode($estado) ?>&calidad=<?= urlencode($calidad) ?>&fecha_desde=<?= urlencode($fecha_desde) ?>&fecha_hasta=<?= urlencode($fecha_hasta) ?>&numero_serie=<?= urlencode($numero_serie) ?>"><i class="bi bi-file-earmark-excel"></i> Excel</a></li>
                                    <li><a class="dropdown-item" href="index.php?controller=equipos&action=exportar&formato=csv&filtro=<?= urlencode($filtro) ?>&estado=<?= urlencode($estado) ?>&calidad=<?= urlencode($calidad) ?>&fecha_desde=<?= urlencode($fecha_desde) ?>&fecha_hasta=<?= urlencode($fecha_hasta) ?>&numero_serie=<?= urlencode($numero_serie) ?>"><i class="bi bi-file-text"></i> CSV</a></li>
                                </ul>
                            </div>
                            <a href="index.php?controller=equipos&action=crear" class="btn btn-primary btn-sm">
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
                            <input type="hidden" name="controller" value="equipos">

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label form-label-sm fw-semibold">Buscar</label>
                                <input type="text" class="form-control form-control-sm" name="filtro"
                                       placeholder="Nombre, serie, modelo..."
                                       value="<?= htmlspecialchars($filtro) ?>">
                            </div>

                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label form-label-sm fw-semibold">N° Serie</label>
                                <input type="text" class="form-control form-control-sm" name="numero_serie"
                                       placeholder="Ej: 214B769004691"
                                       value="<?= htmlspecialchars($numero_serie) ?>">
                            </div>

                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label form-label-sm fw-semibold">Estado</label>
                                <select class="form-select form-select-sm" name="estado">
                                    <option value="todos" <?= $estado === 'todos' ? 'selected' : '' ?>>Todos</option>
                                    <option value="DISPONIBLE" <?= $estado === 'DISPONIBLE' ? 'selected' : '' ?>>Disponibles</option>
                                    <option value="OCUPADO" <?= $estado === 'OCUPADO' ? 'selected' : '' ?>>Ocupados</option>
                                    <option value="MANTENIMIENTO" <?= $estado === 'MANTENIMIENTO' ? 'selected' : '' ?>>Mantenimiento</option>
                                    <option value="RETIRADO" <?= $estado === 'RETIRADO' ? 'selected' : '' ?>>Retirados</option>
                                </select>
                            </div>

                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label form-label-sm fw-semibold">Calidad</label>
                                <select class="form-select form-select-sm" name="calidad">
                                    <option value="todas" <?= $calidad === 'todas' ? 'selected' : '' ?>>Todas</option>
                                    <option value="NUEVO" <?= $calidad === 'NUEVO' ? 'selected' : '' ?>>NUEVO</option>
                                    <option value="BUENO" <?= $calidad === 'BUENO' ? 'selected' : '' ?>>BUENO</option>
                                    <option value="REGULAR" <?= $calidad === 'REGULAR' ? 'selected' : '' ?>>REGULAR</option>
                                    <option value="MALO" <?= $calidad === 'MALO' ? 'selected' : '' ?>>MALO</option>
                                </select>
                            </div>

                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label form-label-sm fw-semibold">Desde</label>
                                <input type="date" class="form-control form-control-sm" name="fecha_desde" value="<?= htmlspecialchars($fecha_desde) ?>">
                            </div>

                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label form-label-sm fw-semibold">Hasta</label>
                                <input type="date" class="form-control form-control-sm" name="fecha_hasta" value="<?= htmlspecialchars($fecha_hasta) ?>">
                            </div>

                            <div class="col-12 col-md-2 col-lg-1 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search"></i>
                                </button>
                                <a href="index.php?controller=equipos" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-circle"></i> Limpiar
                                </a>
                            </div>

                            <div class="col-12 mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> <?= count($equipos) ?> equipo(s) encontrados
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Equipos -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0 resizable-table" id="tablaEquipos" style="min-width: 100%;">
                            <thead>
                                <tr>
                                    <th class="fw-semibold" style="min-width: 60px;">ID</th>
                                    <th class="fw-semibold" style="min-width: 130px;">N° SERIE</th>
                                    <th class="fw-semibold" style="min-width: 90px;">CÓDIGO</th>
                                    <th class="fw-semibold" style="min-width: 180px;">DESCRIPCIÓN</th>
                                    <th class="fw-semibold" style="min-width: 120px;">MARCA</th>
                                    <th class="fw-semibold" style="min-width: 120px;">MODELO</th>
                                    <th class="fw-semibold text-center" style="min-width: 110px;">FECHA <i class="bi bi-arrow-down-up text-muted"></i></th>
                                    <th class="fw-semibold text-end" style="min-width: 100px;">PRECIO</th>
                                    <th class="fw-semibold" style="min-width: 130px;">PROVEEDOR</th>
                                    <th class="fw-semibold" style="min-width: 110px;">ESTADO</th>
                                    <th class="fw-semibold" style="min-width: 90px;">CALIDAD</th>
                                    <th class="fw-semibold text-center" style="min-width: 120px;">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($equipos)): ?>
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No se encontraron equipos</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($equipos as $equipo): ?>
                                    <tr>
                                        <td class="fw-bold">#<?= $equipo['id_equipo'] ?></td>
                                        <td><code class="text-primary"><?= htmlspecialchars($equipo['numero_serie_mac']) ?></code></td>
                                        <td>
                                            <?php if (!empty($equipo['codigo'])): ?>
                                            <span class="badge bg-info"><?= htmlspecialchars($equipo['codigo']) ?></span>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($equipo['nombre']) ?></td>
                                        <td><?= htmlspecialchars($equipo['marca'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($equipo['modelo'] ?? '-') ?></td>
                                        <td data-order="<?= htmlspecialchars($equipo['fecha_entrada']) ?>"><?= date('d/m/Y', strtotime($equipo['fecha_entrada'])) ?></td>
                                        <td class="text-end">
                                            <?php if (!empty($equipo['precio'])): ?>
                                            <strong>Bs. <?= number_format($equipo['precio'], 2) ?></strong>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($equipo['proveedor'] ?? '-') ?></td>
                                        <td>
                                            <?php
                                            $estadoClass = match($equipo['estado']) {
                                                'DISPONIBLE' => 'bg-success',
                                                'OCUPADO' => 'bg-warning',
                                                'MANTENIMIENTO' => 'bg-info',
                                                'RETIRADO' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $estadoClass ?>">
                                                <?= $equipo['estado'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $calidadClass = match($equipo['calidad']) {
                                                'NUEVO' => 'bg-success',
                                                'BUENO' => 'bg-primary',
                                                'REGULAR' => 'bg-warning',
                                                'MALO' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $calidadClass ?>"><?= $equipo['calidad'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="index.php?controller=equipos&action=editar&id=<?= $equipo['id_equipo'] ?>"
                                                   class="btn btn-outline-primary btn-sm" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="javascript:void(0)"
                                                   onclick="confirmarEliminacion('index.php?controller=equipos&action=eliminar&id=<?= $equipo['id_equipo'] ?>')"
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
                                    <td colspan="7" class="text-end fw-bold">Total:</td>
                                    <td colspan="5" class="text-end fw-bold">
                                        <?php
                                        $total = array_sum(array_column($equipos, 'precio'));
                                        echo 'Bs. ' . number_format($total, 2);
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
            <form method="POST" action="index.php?controller=equipos&action=importar" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold"><i class="bi bi-upload"></i> Importar Equipos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="archivo" class="form-label">Archivo CSV</label>
                        <input type="file" class="form-control" id="archivo" name="archivo" accept=".csv" required>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <strong>Columnas:</strong> Nombre, N° Serie, Código, Modelo, Marca, Fecha, Calidad, Observaciones, Precio, Proveedor<br>
                        <strong>Separador obligatorio:</strong> comas (,)<br>
                        <strong>Fecha:</strong> usar formato AAAA-MM-DD<br>
                        <strong>Código:</strong> debe existir en el catálogo de tipos de equipos<br>
                        <strong>Nota:</strong> la exportación del sistema no es la misma plantilla de importación.<br>
                        <a href="assets/templates/plantilla_importacion_equipos.csv" class="alert-link" download>Descargar plantilla vacía</a> |
                        <a href="assets/templates/ejemplo_importacion_equipos.csv" class="alert-link" download>Descargar ejemplo</a>
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
    $('#tablaEquipos').DataTable({
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
