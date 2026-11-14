<!-- Préstamos de Equipos -->
<div class="card fade-in">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-hand-index-thumb"></i> Préstamos de Equipos</span>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPrestamo">
            <i class="bi bi-plus-circle"></i> Nuevo Préstamo
        </button>
    </div>
    <div class="card-body">
        <!-- Alerta de vencidos -->
        <?php if ($vencidos > 0): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> 
            <strong>¡Atención!</strong> Hay <strong><?= $vencidos ?></strong> préstamo(s) vencido(s)
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Filtros -->
        <form method="GET" class="row g-3 mb-4">
            <input type="hidden" name="controller" value="prestamos">
            <div class="col-md-4">
                <input type="text" class="form-control" name="filtro" placeholder="Buscar por equipo, solicitante..." value="<?= htmlspecialchars($filtro) ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="estado">
                    <option value="todos" <?= $estado === 'todos' ? 'selected' : '' ?>>Todos los Estados</option>
                    <option value="PRESTADO" <?= $estado === 'PRESTADO' ? 'selected' : '' ?>>Prestado</option>
                    <option value="DEVUELTO" <?= $estado === 'DEVUELTO' ? 'selected' : '' ?>>Devuelto</option>
                    <option value="ATRASADO" <?= $estado === 'ATRASADO' ? 'selected' : '' ?>>Atrasado</option>
                    <option value="PERDIDO" <?= $estado === 'PERDIDO' ? 'selected' : '' ?>>Perdido</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Buscar</button>
            </div>
            <div class="col-md-2">
                <a href="index.php?controller=prestamos" class="btn btn-outline-secondary w-100"><i class="bi bi-x-circle"></i> Limpiar</a>
            </div>
        </form>
        
        <!-- Tabla -->
        <div class="table-responsive">
            <table class="table table-hover" id="tablaPrestamos">
                <thead>
                    <tr>
                        <th>Fecha Préstamo</th>
                        <th>Equipo</th>
                        <th>Solicitante</th>
                        <th>Departamento</th>
                        <th>Dev. Esperada</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($prestamos ?? [])): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-2">No hay préstamos registrados</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($prestamos as $pres): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($pres['fecha_prestamo'])) ?></td>
                            <td><?= htmlspecialchars($pres['equipo_nombre'] ?? $pres['numero_serie_mac']) ?></td>
                            <td><?= htmlspecialchars($pres['nombre_solicita']) ?><br><small class="text-muted"><?= htmlspecialchars($pres['cargo_solicita'] ?? '') ?></small></td>
                            <td><?= htmlspecialchars($pres['departamento'] ?? '-') ?></td>
                            <td><?= date('d/m/Y', strtotime($pres['fecha_devolucion_esperada'])) ?></td>
                            <td>
                                <span class="badge bg-<?= $pres['estado'] === 'PRESTADO' ? 'warning' : ($pres['estado'] === 'DEVUELTO' ? 'success' : ($pres['estado'] === 'ATRASADO' ? 'danger' : 'secondary')) ?>">
                                    <?= str_replace('_', ' ', $pres['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($pres['estado'] === 'PRESTADO' || $pres['estado'] === 'ATRASADO'): ?>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalDevolver<?= $pres['id_prestamo'] ?>"><i class="bi bi-arrow-return-left"></i> Devolver</button>
                                <?php endif; ?>
                                <a href="javascript:void(0)" onclick="confirmarEliminacion('index.php?controller=prestamos&action=eliminar&id=<?= $pres['id_prestamo'] ?>')" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        
                        <!-- Modal Devolver -->
                        <div class="modal fade" id="modalDevolver<?= $pres['id_prestamo'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="index.php?controller=prestamos&action=devolver">
                                        <input type="hidden" name="id_prestamo" value="<?= $pres['id_prestamo'] ?>">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title">Registrar Devolución</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Fecha de Devolución *</label>
                                                <input type="date" name="fecha_devolucion_real" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Observaciones</label>
                                                <textarea name="observaciones_devolucion" class="form-control" rows="3"></textarea>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="equipo_conforme" checked>
                                                <label class="form-check-label">Equipo recibido conforme</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Registrar Devolución</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nuevo Préstamo -->
<div class="modal fade" id="modalPrestamo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="index.php?controller=prestamos&action=registrar">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-hand-index-thumb"></i> Nuevo Préstamo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Equipo *</label>
                            <select name="numero_serie_mac" class="form-select" required>
                                <option value="">Seleccione un equipo disponible</option>
                                <?php foreach ($equipos as $eq): ?>
                                <option value="<?= htmlspecialchars($eq['numero_serie_mac']) ?>"><?= htmlspecialchars($eq['nombre']) ?> - <?= htmlspecialchars($eq['numero_serie_mac']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Usuario (opcional)</label>
                            <select name="id_usuario_solicita" class="form-select">
                                <option value="">Seleccione...</option>
                                <?php foreach ($usuarios as $usu): ?>
                                <option value="<?= $usu['id_usuario'] ?>"><?= htmlspecialchars($usu['nombre_completo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre del Solicitante *</label>
                            <input type="text" name="nombre_solicita" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cargo</label>
                            <input type="text" name="cargo_solicita" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Departamento</label>
                            <input type="text" name="departamento" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono de Contacto</label>
                            <input type="text" name="telefono_contacto" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email_contacto" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de Préstamo *</label>
                            <input type="date" name="fecha_prestamo" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha Dev. Esperada *</label>
                            <input type="date" name="fecha_devolucion_esperada" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Motivo del Préstamo *</label>
                            <textarea name="motivo_prestamo" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observaciones de Salida</label>
                            <textarea name="observaciones_salida" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Registrar Préstamo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tablaPrestamos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 10,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: true, targets: [0, 1, 2, 3, 4, 5] },
            { orderable: false, targets: [6] }
        ]
    });
});
</script>
