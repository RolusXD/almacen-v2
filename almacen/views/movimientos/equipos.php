<!-- Movimientos de Equipos - Actualizado 2026 -->
<div class="row">
    <!-- Formulario de Registro -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-plus-circle"></i> Registrar Movimiento de Equipo
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="accion" value="registrar_equipo">

                    <div class="mb-3">
                        <label for="buscar_numero_serie" class="form-label">
                            <i class="bi bi-upc-scan"></i> N° DE SERIE <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="buscar_numero_serie" name="numero_serie_mac" required
                               list="equipos_disponibles_list"
                               placeholder="Escriba o seleccione una serie disponible">
                        <datalist id="equipos_disponibles_list">
                            <?php foreach ($equiposDisponibles as $equipo): ?>
                            <option value="<?= htmlspecialchars($equipo['numero_serie_mac']) ?>">
                                <?= htmlspecialchars(trim(($equipo['codigo'] ?? '') . ' ' . ($equipo['nombre'] ?? '') . ' ' . ($equipo['modelo'] ?? ''))) ?>
                            </option>
                            <?php endforeach; ?>
                        </datalist>
                        <small class="text-muted">Busque por serie y el sistema mostrará la referencia del equipo.</small>
                    </div>

                    <div class="mb-3">
                        <div class="border rounded p-3 bg-light-subtle" id="panel_equipo_seleccionado">
                            <div class="small text-muted mb-1">Equipo seleccionado</div>
                            <div class="fw-semibold" id="equipo_descripcion">Sin selección</div>
                            <div class="small text-muted" id="equipo_modelo">Modelo: -</div>
                            <div class="small text-muted" id="equipo_codigo">Código: -</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tipo_movimiento" class="form-label">
                            <i class="bi bi-arrow-left-right"></i> Tipo de Movimiento <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" name="tipo_movimiento" required id="tipo_movimiento">
                            <option value="">Seleccione...</option>
                            <option value="SALIDA">SALIDA (Egreso de almacén)</option>
                            <option value="ENTRADA">ENTRADA (Retorno a almacén)</option>
                            <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                            <option value="REVISION">REVISIÓN</option>
                        </select>
                    </div>

                    <div class="mb-3" id="div_destino">
                        <label for="destino" class="form-label">
                            <i class="bi bi-geo-alt"></i> DESTINO <span class="text-danger" id="req_destino">*</span>
                        </label>
                        <select class="form-select" name="destino" id="destino">
                            <option value="">Seleccione destino...</option>
                            <option value="INSTALACIONES">INSTALACIONES</option>
                            <option value="INFRAESTRUCTURA">INFRAESTRUCTURA</option>
                            <option value="MANTENIMIENTO VEHICULOS">MANTENIMIENTO VEHICULOS</option>
                            <option value="MANTENIMIENTO RED">MANTENIMIENTO RED</option>
                            <option value="ADMINISTRACION">ADMINISTRACION</option>
                            <option value="SOPORTE TECNICO">SOPORTE TECNICO</option>
                            <option value="OTROS">OTROS</option>
                        </select>
                        <small class="text-muted">Lugar donde se envía el equipo</small>
                    </div>

                    <div class="mb-3">
                        <label for="asunto" class="form-label">
                            <i class="bi bi-file-text"></i> Asunto <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" name="asunto" required onchange="toggleOtro('asunto_otro', this.value)">
                            <option value="">Seleccione...</option>
                            <option value="RCA">RCA (Requerimiento de Cambio/Alta)</option>
                            <option value="INSTALACIÓN">INSTALACIÓN</option>
                            <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                            <option value="AMPLIACION">AMPLIACIÓN DE RED</option>
                            <option value="REEMPLAZO">REEMPLAZO DE EQUIPO</option>
                            <option value="OTROS">OTROS</option>
                        </select>
                        <input type="text" class="form-control mt-2 d-none" id="asunto_otro" name="asunto_otro"
                               placeholder="Especifique el asunto">
                    </div>

                    <div class="mb-3">
                        <label for="encargado" class="form-label">
                            <i class="bi bi-person"></i> Encargado <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" name="encargado" id="encargado" required onchange="toggleOtro('encargado_otro', this.value)">
                            <option value="">Seleccione encargado...</option>
                            <?php foreach (($encargados ?? []) as $encargado): ?>
                            <option value="<?= htmlspecialchars($encargado) ?>"><?= htmlspecialchars($encargado) ?></option>
                            <?php endforeach; ?>
                            <option value="OTROS">OTROS</option>
                        </select>
                        <input type="text" class="form-control mt-2 d-none" id="encargado_otro" name="encargado_otro"
                               placeholder="Especifique el encargado">
                        <small class="text-muted">Seleccione un encargado de la lista definida.</small>
                    </div>

                    <div class="mb-3">
                        <label for="fecha_movimiento" class="form-label">
                            <i class="bi bi-calendar"></i> Fecha <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" name="fecha_movimiento" id="fecha_movimiento" required
                               value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="observaciones" class="form-label">
                            <i class="bi bi-chat-text"></i> Observaciones
                        </label>
                        <textarea class="form-control" name="observaciones" id="observaciones" rows="3"
                                  placeholder="Observaciones generales..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones_detalladas" class="form-label">
                            <i class="bi bi-journal-text"></i> Observaciones Detalladas
                        </label>
                        <textarea class="form-control" name="observaciones_detalladas" id="observaciones_detalladas" rows="3"
                                  placeholder="Detalles adicionales del movimiento..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> Registrar Movimiento
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Listado de Movimientos -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header movimientos-toolbar">
                <span class="movimientos-title"><i class="bi bi-list-ul"></i> Historial de Movimientos de Equipos</span>
                <div class="movimientos-actions">
                    <a href="index.php?controller=movimientos&action=exportar_equipos&destino=<?= urlencode($destino) ?>&codigo=<?= urlencode($codigo ?? '') ?>&filtro=<?= urlencode($filtro) ?>" 
                       class="btn btn-sm btn-success movimientos-action-btn">
                        <i class="bi bi-file-earmark-excel"></i> <span>Exportar</span>
                    </a>
                    <form method="GET" class="movimientos-search">
                        <input type="hidden" name="controller" value="movimientos">
                        <input type="hidden" name="action" value="equipos">
                        <input type="hidden" name="destino" value="<?= htmlspecialchars($destino) ?>">
                        <input type="hidden" name="codigo" value="<?= htmlspecialchars($codigo ?? '') ?>">
                        <input type="text" class="form-control form-control-sm" name="filtro"
                               placeholder="Buscar serie, código, equipo..." value="<?= htmlspecialchars($filtro) ?>">
                        <button type="submit" class="btn btn-sm btn-primary movimientos-action-btn" title="Buscar movimientos">
                            <i class="bi bi-search"></i> <span>Buscar</span>
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <!-- Filtro por destino -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <form method="GET" class="d-flex gap-2 flex-wrap">
                            <input type="hidden" name="controller" value="movimientos">
                            <input type="hidden" name="action" value="equipos">
                            <input type="hidden" name="filtro" value="<?= htmlspecialchars($filtro) ?>">
                            <select class="form-select form-select-sm" name="destino" onchange="this.form.submit()">
                                <option value="">Todos los destinos</option>
                                <option value="INSTALACIONES" <?= $destino === 'INSTALACIONES' ? 'selected' : '' ?>>INSTALACIONES</option>
                                <option value="INFRAESTRUCTURA" <?= $destino === 'INFRAESTRUCTURA' ? 'selected' : '' ?>>INFRAESTRUCTURA</option>
                                <option value="MANTENIMIENTO VEHICULOS" <?= $destino === 'MANTENIMIENTO VEHICULOS' ? 'selected' : '' ?>>MANTENIMIENTO VEHICULOS</option>
                                <option value="MANTENIMIENTO RED" <?= $destino === 'MANTENIMIENTO RED' ? 'selected' : '' ?>>MANTENIMIENTO RED</option>
                                <option value="ADMINISTRACION" <?= $destino === 'ADMINISTRACION' ? 'selected' : '' ?>>ADMINISTRACION</option>
                                <option value="SOPORTE TECNICO" <?= $destino === 'SOPORTE TECNICO' ? 'selected' : '' ?>>SOPORTE TECNICO</option>
                            </select>
                            <select class="form-select form-select-sm" name="codigo" onchange="this.form.submit()">
                                <option value="">Todos los códigos</option>
                                <?php foreach (($codigosEquipo ?? []) as $codigoEquipo): ?>
                                <option value="<?= htmlspecialchars($codigoEquipo['codigo']) ?>"
                                        <?= ($codigo ?? '') === $codigoEquipo['codigo'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($codigoEquipo['codigo']) ?><?= !empty($codigoEquipo['descripcion']) ? ' - ' . htmlspecialchars($codigoEquipo['descripcion']) : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($destino) || !empty($codigo) || !empty($filtro)): ?>
                            <a href="index.php?controller=movimientos&action=equipos" class="btn btn-sm btn-outline-secondary">
                                Limpiar
                            </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Total: <?= count($movimientos) ?> movimiento(s)
                        </small>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="tablaMovimientosEquipos">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>N° Serie</th>
                                <th>Equipo</th>
                                <th>Código</th>
                                <th>Tipo</th>
                                <th>Destino</th>
                                <th>Asunto</th>
                                <th>Encargado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($movimientos)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mt-2">No hay movimientos registrados</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($movimientos as $mov): ?>
                                <tr>
                                    <td data-order="<?= htmlspecialchars($mov['fecha_movimiento']) ?>"><?= date('d/m/Y', strtotime($mov['fecha_movimiento'])) ?></td>
                                    <td><code><?= htmlspecialchars($mov['numero_serie_mac']) ?></code></td>
                                    <td><?= htmlspecialchars($mov['nombre_equipo'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($mov['codigo'])): ?>
                                        <span class="badge bg-info"><?= htmlspecialchars($mov['codigo']) ?></span>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $mov['tipo_movimiento'] === 'SALIDA' ? 'warning' : ($mov['tipo_movimiento'] === 'ENTRADA' ? 'success' : 'info') ?>">
                                            <?= $mov['tipo_movimiento'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($mov['destino'])): ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($mov['destino']) ?></span>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($mov['asunto']) ?></td>
                                    <td><?= htmlspecialchars($mov['encargado']) ?></td>
                                    <td>
                                        <a href="javascript:void(0)"
                                           onclick="confirmarEliminacion('index.php?controller=movimientos&action=eliminar_equipo&id=<?= $mov['id_movimiento'] ?>')"
                                           class="btn btn-sm btn-danger" title="Eliminar">
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

const equiposDisponiblesMap = {
    <?php foreach ($equiposDisponibles as $equipo): ?>
    <?= json_encode($equipo['numero_serie_mac']) ?>: {
        nombre: <?= json_encode($equipo['nombre'] ?? '') ?>,
        modelo: <?= json_encode($equipo['modelo'] ?? '') ?>,
        codigo: <?= json_encode($equipo['codigo'] ?? '') ?>
    },
    <?php endforeach; ?>
};

function actualizarFichaEquipo() {
    const serieVisible = document.getElementById('buscar_numero_serie').value.trim();
    const descripcion = document.getElementById('equipo_descripcion');
    const modelo = document.getElementById('equipo_modelo');
    const codigo = document.getElementById('equipo_codigo');
    const equipo = equiposDisponiblesMap[serieVisible];

    if (!equipo) {
        descripcion.textContent = 'Sin selección';
        modelo.textContent = 'Modelo: -';
        codigo.textContent = 'Código: -';
        return;
    }

    descripcion.textContent = equipo.nombre || serieVisible;
    modelo.textContent = 'Modelo: ' + (equipo.modelo || '-');
    codigo.textContent = 'Código: ' + (equipo.codigo || '-');
}

// Mostrar/ocultar campo destino según tipo de movimiento
document.getElementById('tipo_movimiento').addEventListener('change', function() {
    const destinoDiv = document.getElementById('div_destino');
    const destinoReq = document.getElementById('req_destino');
    
    if (this.value === 'SALIDA') {
        destinoDiv.style.display = 'block';
        destinoReq.style.display = 'inline';
    } else {
        destinoDiv.style.display = 'none';
        destinoReq.style.display = 'none';
        document.getElementById('destino').value = '';
    }
});

// Inicializar DataTable
$(document).ready(function() {
    document.getElementById('buscar_numero_serie').addEventListener('input', actualizarFichaEquipo);
    document.getElementById('buscar_numero_serie').addEventListener('change', actualizarFichaEquipo);

    $('#tablaMovimientosEquipos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: true, targets: [0, 1, 2, 3, 4, 5, 6, 7] },
            { orderable: false, targets: [8] }
        ]
    });
});
</script>

<style>
.movimientos-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.movimientos-title {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.movimientos-actions,
.movimientos-search,
.movimientos-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
}

.movimientos-actions {
    flex-wrap: wrap;
}

.movimientos-search {
    min-width: min(100%, 440px);
}

.movimientos-search .form-control {
    min-width: 260px;
}

.movimientos-action-btn {
    min-width: 112px;
    white-space: nowrap;
}

@media (max-width: 768px) {
    .movimientos-actions,
    .movimientos-search {
        width: 100%;
    }

    .movimientos-search .form-control {
        min-width: 0;
        flex: 1;
    }
}
</style>
