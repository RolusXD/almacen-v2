<!-- Mantenimientos de Equipos -->
<div class="card fade-in">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-gear"></i> Mantenimientos</span>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMantenimiento">
            <i class="bi bi-plus-circle"></i> Nuevo Mantenimiento
        </button>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form method="GET" class="row g-3 mb-4">
            <input type="hidden" name="controller" value="mantenimientos">
            <div class="col-md-4">
                <input type="text" class="form-control" name="filtro" placeholder="Buscar por equipo, técnico..." value="<?= htmlspecialchars($filtro ?? '') ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="estado">
                    <option value="todos" <?= ($estado ?? '') === 'todos' ? 'selected' : '' ?>>Todos los Estados</option>
                    <option value="EN_PROCESO" <?= ($estado ?? '') === 'EN_PROCESO' ? 'selected' : '' ?>>En Proceso</option>
                    <option value="COMPLETADO" <?= ($estado ?? '') === 'COMPLETADO' ? 'selected' : '' ?>>Completado</option>
                    <option value="CANCELADO" <?= ($estado ?? '') === 'CANCELADO' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Buscar</button>
            </div>
            <div class="col-md-2">
                <a href="index.php?controller=mantenimientos" class="btn btn-outline-secondary w-100"><i class="bi bi-x-circle"></i> Limpiar</a>
            </div>
        </form>
        
        <!-- Tabla -->
        <div class="table-responsive">
            <table class="table table-hover" id="tablaMantenimientos">
                <thead>
                    <tr>
                        <th>Fecha Ingreso</th>
                        <th>Equipo</th>
                        <th>N° Serie</th>
                        <th>Tipo</th>
                        <th>Técnico</th>
                        <th>Estado</th>
                        <th>Costo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mantenimientos ?? [])): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-2">No hay mantenimientos registrados</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($mantenimientos as $mant): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($mant['fecha_ingreso'])) ?></td>
                            <td><?= htmlspecialchars($mant['equipo_nombre'] ?? '-') ?></td>
                            <td><code><?= htmlspecialchars($mant['numero_serie_mac']) ?></code></td>
                            <td>
                                <span class="badge bg-<?= $mant['tipo_mantenimiento'] === 'PREVENTIVO' ? 'info' : ($mant['tipo_mantenimiento'] === 'CORRECTIVO' ? 'danger' : 'warning') ?>">
                                    <?= $mant['tipo_mantenimiento'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($mant['tecnico_responsable']) ?></td>
                            <td>
                                <span class="badge bg-<?= $mant['estado'] === 'EN_PROCESO' ? 'warning' : ($mant['estado'] === 'COMPLETADO' ? 'success' : 'secondary') ?>">
                                    <?= str_replace('_', ' ', $mant['estado']) ?>
                                </span>
                            </td>
                            <td><?= $mant['costo'] ? 'Bs ' . number_format($mant['costo'], 2) : '-' ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if ($mant['estado'] === 'EN_PROCESO'): ?>
                                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCompletar<?= $mant['id_mantenimiento'] ?>">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <?php endif; ?>
                                    <a href="javascript:void(0)" onclick="confirmarEliminacion('index.php?controller=mantenimientos&action=eliminar&id=<?= $mant['id_mantenimiento'] ?>')" class="btn btn-danger">
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

<!-- Modal Nuevo Mantenimiento -->
<div class="modal fade" id="modalMantenimiento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="index.php?controller=mantenimientos&action=registrar">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-gear"></i> Nuevo Mantenimiento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Equipo (N° Serie/MAC) *</label>
                            <select name="numero_serie_mac" class="form-select" required>
                                <option value="">Seleccione un equipo</option>
                                <?php foreach (($equipos ?? []) as $eq): ?>
                                <option value="<?= htmlspecialchars($eq['numero_serie_mac']) ?>"><?= htmlspecialchars($eq['nombre']) ?> - <?= htmlspecialchars($eq['numero_serie_mac']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Mantenimiento *</label>
                            <select name="tipo_mantenimiento" class="form-select" required>
                                <option value="PREVENTIVO">PREVENTIVO</option>
                                <option value="CORRECTIVO">CORRECTIVO</option>
                                <option value="EVOLUTIVO">EVOLUTIVO</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción *</label>
                            <textarea name="descripcion" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Técnico Responsable *</label>
                            <input type="text" name="tecnico_responsable" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de Ingreso *</label>
                            <input type="date" name="fecha_ingreso" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de Entrega Estimada</label>
                            <input type="date" name="fecha_entrega" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Costo (Bs)</label>
                            <input type="number" step="0.01" name="costo" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Garantía (días)</label>
                            <input type="number" name="garantia_dias" class="form-control" value="30">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Repuestos Cambiados</label>
                            <textarea name="repuestos_cambiados" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tablaMantenimientos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 10,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: true, targets: [0, 1, 2, 3, 4, 5, 6] },
            { orderable: false, targets: [7] }
        ]
    });
});
</script>
