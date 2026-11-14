<!-- Dashboard con VER STOCK - Diseño Sobrio 2026 -->
<div class="fade-in">
    <!-- Título -->
    <div class="ops-hero mb-3">
        <div class="row align-items-center g-3">
            <div class="col-lg-7">
                <div class="ops-code mb-2">Centro de control · <?= date('Y-m-d') ?></div>
                <h4 class="fw-semibold mb-2"><i class="bi bi-speedometer2"></i> Inventario TOI en tiempo real</h4>
                <p class="mb-0">Equipos, accesorios y movimientos reunidos en una vista operativa.</p>
            </div>
            <div class="col-lg-5">
                <div class="ops-actions justify-content-lg-end">
                    <a href="index.php?controller=equipos&action=crear" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> Equipo
                    </a>
                    <a href="index.php?controller=accesorios&action=crear" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-plus-circle"></i> Accesorio
                    </a>
                    <a href="index.php?controller=movimientos&action=equipos" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-arrow-left-right"></i> Movimiento
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Fila de Estadísticas Principales -->
    <div class="row mb-3">
        <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
            <div class="stat-card">
                <div>
                    <h6 class="text-muted text-uppercase mb-2 fw-semibold" style="font-size: 0.7rem;">Total Equipos</h6>
                    <h2 class="mb-0 fw-bold" style="font-size: 1.75rem;"><?= $totalEquipos ?></h2>
                    <small class="text-muted"><i class="bi bi-pc-display"></i> En inventario</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
            <div class="stat-card success">
                <div>
                    <h6 class="text-muted text-uppercase mb-2 fw-semibold" style="font-size: 0.7rem;">Disponibles</h6>
                    <h2 class="mb-0 fw-bold" style="font-size: 1.75rem; color: #059669;"><?= $equiposDisponibles ?></h2>
                    <small class="text-success"><i class="bi bi-check-circle"></i> Listos para usar</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
            <div class="stat-card warning">
                <div>
                    <h6 class="text-muted text-uppercase mb-2 fw-semibold" style="font-size: 0.7rem;">Ocupados</h6>
                    <h2 class="mb-0 fw-bold" style="font-size: 1.75rem; color: #d97706;"><?= $equiposOcupados ?></h2>
                    <small class="text-warning"><i class="bi bi-exclamation-circle"></i> En uso</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
            <div class="stat-card">
                <div>
                    <h6 class="text-muted text-uppercase mb-2 fw-semibold" style="font-size: 0.7rem;">Valor Inventario</h6>
                    <h2 class="mb-0 fw-bold" style="font-size: 1.75rem; color: #0f7b72;">Bs. <?= number_format($valorTotalInventario ?? 0, 0) ?></h2>
                    <small class="text-muted"><i class="bi bi-currency-dollar"></i> Valor total</small>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN: VER STOCK DE EQUIPOS POR CÓDIGO -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-table"></i> STOCK DE EQUIPOS POR CÓDIGO</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0" id="tablaStockEquipos">
                            <thead>
                                <tr>
                                    <th class="fw-semibold">CÓDIGO</th>
                                    <th class="fw-semibold">DESCRIPCIÓN</th>
                                    <th class="fw-semibold text-center">TOTAL</th>
                                    <th class="fw-semibold text-center">DISPONIBLES</th>
                                    <th class="fw-semibold text-center">OCUPADOS</th>
                                    <th class="fw-semibold text-center">MANTENIMIENTO</th>
                                    <th class="fw-semibold">% DISPONIBILIDAD</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($equiposPorCodigo)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No hay equipos registrados</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($equiposPorCodigo as $eq): ?>
                                    <?php
                                    $porcentaje = $eq['total'] > 0 ? round(($eq['disponibles'] / $eq['total']) * 100) : 0;
                                    $barClass = $porcentaje >= 50 ? 'bg-success' : ($porcentaje >= 25 ? 'bg-warning' : 'bg-danger');
                                    ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($eq['codigo'] ?? 'S/C') ?></strong></td>
                                        <td><?= htmlspecialchars($eq['descripcion'] ?? 'Sin descripción') ?></td>
                                        <td class="text-center fw-bold"><?= $eq['total'] ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-success"><?= $eq['disponibles'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning"><?= $eq['ocupados'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info"><?= $eq['mantenimiento'] ?? 0 ?></span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 18px;">
                                                <div class="progress-bar <?= $barClass ?>" role="progressbar"
                                                     style="width: <?= $porcentaje ?>%;"
                                                     aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100">
                                                    <small><?= $porcentaje ?>%</small>
                                                </div>
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

    <!-- SECCIÓN: VER STOCK DE ACCESORIOS POR CÓDIGO -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-tools"></i> STOCK DE ACCESORIOS</h5>
                </div>
                <div class="card-body p-0">
                    <?php if ($stockBajo > 0): ?>
                    <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>¡Atención!</strong> Hay <strong><?= $stockBajo ?></strong> accesorio(s) con stock bajo (&lt; 10 unidades)
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0" id="tablaStockAccesorios">
                            <thead>
                                <tr>
                                    <th class="fw-semibold">CÓDIGO</th>
                                    <th class="fw-semibold">DESCRIPCIÓN</th>
                                    <th class="fw-semibold text-center">UNIDAD</th>
                                    <th class="fw-semibold text-center">TOTAL</th>
                                    <th class="fw-semibold text-center">DISPONIBLES</th>
                                    <th class="fw-semibold text-center">STOCK BAJO</th>
                                    <th class="fw-semibold text-center">ESTADO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($accesoriosPorCodigo)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No hay accesorios registrados</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($accesoriosPorCodigo as $acc): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($acc['codigo'] ?? 'S/C') ?></strong></td>
                                        <td><?= htmlspecialchars($acc['nombre'] ?? 'Sin nombre') ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary"><?= htmlspecialchars($acc['unidad_medida'] ?? '-') ?></span>
                                        </td>
                                        <td class="text-center fw-bold"><?= $acc['total_cantidad'] ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-success"><?= $acc['disponibles'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($acc['stock_bajo'] > 0): ?>
                                            <span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> <?= $acc['stock_bajo'] ?></span>
                                            <?php else: ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> OK</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($acc['total_cantidad'] >= 100): ?>
                                            <span class="badge bg-success">Óptimo</span>
                                            <?php elseif ($acc['total_cantidad'] >= 50): ?>
                                            <span class="badge bg-warning">Regular</span>
                                            <?php else: ?>
                                            <span class="badge bg-danger">Bajo</span>
                                            <?php endif; ?>
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

    <!-- Gráficos y Acciones -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <span><i class="bi bi-graph-up"></i> Equipos por Estado</span>
                </div>
                <div class="card-body">
                    <canvas id="equiposChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <span><i class="bi bi-lightning-charge"></i> Acciones Rápidas</span>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="index.php?controller=equipos&action=crear" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle"></i> Nuevo Equipo
                        </a>
                        <a href="index.php?controller=accesorios&action=crear" class="btn btn-success btn-lg">
                            <i class="bi bi-plus-circle"></i> Nuevo Accesorio
                        </a>
                        <a href="index.php?controller=movimientos&action=equipos" class="btn btn-info btn-lg text-white">
                            <i class="bi bi-arrow-left-right"></i> Movimiento de Equipo
                        </a>
                        <a href="index.php?controller=movimientos&action=accesorios" class="btn btn-warning btn-lg text-white">
                            <i class="bi bi-arrow-left-right"></i> Movimiento de Accesorio
                        </a>
                        <a href="index.php?controller=equipos" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-table"></i> Ver Todos los Equipos
                        </a>
                        <a href="index.php?controller=accesorios" class="btn btn-outline-success btn-lg">
                            <i class="bi bi-tools"></i> Ver Todos los Accesorios
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimos Movimientos -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <span><i class="bi bi-pc-display"></i> Últimos Movimientos de Equipos</span>
                    <a href="index.php?controller=movimientos&action=equipos" class="btn btn-sm btn-outline-primary">
                        Ver todos <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($ultimosMovimientosEquipos)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
                        <p class="mt-3">No hay movimientos registrados</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Equipo</th>
                                    <th>Tipo</th>
                                    <th>Destino</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimosMovimientosEquipos as $mov): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($mov['fecha_movimiento'])) ?></td>
                                    <td><?= htmlspecialchars($mov['nombre_equipo'] ?? $mov['numero_serie_mac']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $mov['tipo_movimiento'] === 'SALIDA' ? 'warning' : 'success' ?>">
                                            <?= $mov['tipo_movimiento'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($mov['destino'])): ?>
                                        <small><?= htmlspecialchars($mov['destino']) ?></small>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <span><i class="bi bi-tools"></i> Últimos Movimientos de Accesorios</span>
                    <a href="index.php?controller=movimientos&action=accesorios" class="btn btn-sm btn-outline-success">
                        Ver todos <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($ultimosMovimientosAccesorios)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
                        <p class="mt-3">No hay movimientos registrados</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Accesorio</th>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimosMovimientosAccesorios as $mov): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($mov['fecha_movimiento'])) ?></td>
                                    <td><?= htmlspecialchars($mov['nombre_accesorio'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $mov['tipo_movimiento'] === 'SALIDA' ? 'warning' : 'success' ?>">
                                            <?= $mov['tipo_movimiento'] ?>
                                        </span>
                                    </td>
                                    <td><strong><?= $mov['cantidad'] ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para Gráficos -->
<script>
$(document).ready(function() {
    // Inicializar DataTables para stock
    $('#tablaStockEquipos').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        pageLength: 10,
        order: [[2, 'desc']]
    });

    $('#tablaStockAccesorios').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        pageLength: 15,
        order: [[3, 'desc']]
    });

    // Gráfico de barras - Equipos por estado
    const ctxEquipos = document.getElementById('equiposChart').getContext('2d');
    new Chart(ctxEquipos, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($equiposPorEstado ?? [], 'estado')) ?>,
            datasets: [{
                label: 'Cantidad de Equipos',
                data: <?= json_encode(array_column($equiposPorEstado ?? [], 'total')) ?>,
                backgroundColor: [
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderColor: [
                    'rgb(16, 185, 129)',
                    'rgb(245, 158, 11)',
                    'rgb(59, 130, 246)',
                    'rgb(239, 68, 68)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>
