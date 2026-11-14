<!-- Movimientos de Accesorios -->
<div class="row">
    <!-- Formulario de Registro -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle"></i> Registrar Movimiento
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="accion" value="registrar_accesorio">

                    <div class="mb-3">
                        <label for="id_accesorio" class="form-label">Accesorio</label>
                        <select class="form-select" name="id_accesorio" required>
                            <option value="">Seleccione un accesorio</option>
                            <?php foreach ($accesoriosConStock as $accesorio): ?>
                            <option value="<?= $accesorio['id_accesorio'] ?>">
                                <?= htmlspecialchars($accesorio['nombre']) ?> (Stock: <?= $accesorio['cantidad'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tipo_movimiento" class="form-label">Tipo de Movimiento</label>
                        <select class="form-select" name="tipo_movimiento" required>
                            <option value="SALIDA">SALIDA</option>
                            <option value="ENTRADA">ENTRADA</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="cantidad" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" name="cantidad" required min="1" value="1">
                    </div>

                    <div class="mb-3">
                        <label for="asunto" class="form-label">Asunto</label>
                        <select class="form-select" name="asunto" required onchange="toggleOtro('asunto_otro', this.value)">
                            <option value="RCA">RCA</option>
                            <option value="INSTALACIÓN">INSTALACIÓN</option>
                            <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                            <option value="OTROS">OTROS</option>
                        </select>
                        <input type="text" class="form-control mt-2 d-none" id="asunto_otro" name="asunto_otro"
                               placeholder="Especifique el asunto">
                    </div>

                    <div class="mb-3">
                        <label for="encargado" class="form-label">Encargado</label>
                        <select class="form-select" name="encargado" required onchange="toggleOtro('encargado_otro', this.value)">
                            <option value="">Seleccione encargado...</option>
                            <?php foreach (($encargados ?? []) as $encargado): ?>
                            <option value="<?= htmlspecialchars($encargado) ?>"><?= htmlspecialchars($encargado) ?></option>
                            <?php endforeach; ?>
                            <option value="OTROS">OTROS</option>
                        </select>
                        <input type="text" class="form-control mt-2 d-none" id="encargado_otro" name="encargado_otro"
                               placeholder="Especifique el encargado">
                    </div>

                    <div class="mb-3">
                        <label for="fecha_movimiento" class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="fecha_movimiento" required
                               value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-save"></i> Registrar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Listado de Movimientos -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul"></i> Historial de Movimientos</span>
                <form method="GET" class="d-flex">
                    <input type="hidden" name="controller" value="movimientos">
                    <input type="hidden" name="action" value="accesorios">
                    <input type="text" class="form-control form-control-sm me-2" name="filtro"
                           placeholder="Buscar..." value="<?= htmlspecialchars($filtro) ?>">
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tablaMovimientosAccesorios">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Accesorio</th>
                                <th>Cant.</th>
                                <th>Tipo</th>
                                <th>Asunto</th>
                                <th>Encargado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($movimientos)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mt-2">No hay movimientos registrados</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($movimientos as $mov): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($mov['fecha_movimiento'])) ?></td>
                                    <td><?= htmlspecialchars($mov['nombre_accesorio']) ?></td>
                                    <td><?= $mov['cantidad'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $mov['tipo_movimiento'] === 'SALIDA' ? 'warning' : 'success' ?>">
                                            <?= $mov['tipo_movimiento'] ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($mov['asunto']) ?></td>
                                    <td><?= htmlspecialchars($mov['encargado']) ?></td>
                                    <td>
                                        <a href="javascript:void(0)"
                                           onclick="confirmarEliminacion('index.php?controller=movimientos&action=eliminar_accesorio&id=<?= $mov['id_movimiento'] ?>')"
                                           class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
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

<script>
function toggleOtro(inputId, value) {
    const input = document.getElementById(inputId);
    if (value === 'OTROS') {
        input.classList.remove('d-none');
        input.required = true;
    } else {
        input.classList.add('d-none');
        input.required = false;
        input.value = '';
    }
}

// Inicializar DataTable después de que el DOM esté listo
$(document).ready(function() {
    $('#tablaMovimientosAccesorios').DataTable({
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
