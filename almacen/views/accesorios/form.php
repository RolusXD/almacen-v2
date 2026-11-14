<!-- Formulario de Accesorio - Actualizado 2026 -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-<?= $accion === 'crear' ? 'plus-circle' : 'pencil' ?>"></i>
        <?= $title ?>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?controller=accesorios&action=<?= $accion === 'crear' ? 'guardar' : 'actualizar' ?>">
            <?php if ($accion === 'editar' && $accesorio): ?>
            <input type="hidden" name="id_accesorio" value="<?= $accesorio['id_accesorio'] ?>">
            <?php endif; ?>

            <h6 class="mb-3 text-primary"><i class="bi bi-box-seam"></i> Información Principal</h6>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="codigo" class="form-label">CÓDIGO</label>
                    <select class="form-select" id="codigo" name="codigo">
                        <option value="">Seleccionar código...</option>
                        <?php foreach (($tiposAccesorios ?? []) as $tipo): ?>
                        <option value="<?= htmlspecialchars($tipo['codigo']) ?>"
                                data-descripcion="<?= htmlspecialchars($tipo['descripcion']) ?>"
                                data-categoria="<?= htmlspecialchars($tipo['categoria'] ?? '') ?>"
                                data-observaciones="<?= htmlspecialchars($tipo['observaciones'] ?? '') ?>"
                                <?= ($accesorio['codigo'] ?? '') === $tipo['codigo'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo['codigo']) ?> - <?= htmlspecialchars($tipo['descripcion']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Seleccione un código registrado para autocompletar la información.</small>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="nombre" class="form-label">DESCRIPCIÓN <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required
                           value="<?= htmlspecialchars($accesorio['nombre'] ?? '') ?>"
                           placeholder="Ej: FIBRA DROP 1H">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="cantidad" class="form-label">CANTIDAD <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="cantidad" name="cantidad" required min="1"
                           value="<?= $accesorio['cantidad'] ?? 1 ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="unidad_medida" class="form-label">UNIDAD DE MEDIDA</label>
                    <select class="form-select" id="unidad_medida" name="unidad_medida">
                        <option value="">Seleccionar...</option>
                        <option value="METROS" <?= ($accesorio['unidad_medida'] ?? '') === 'METROS' ? 'selected' : '' ?>>METROS (m)</option>
                        <option value="UNIDADES" <?= ($accesorio['unidad_medida'] ?? '') === 'UNIDADES' ? 'selected' : '' ?>>UNIDADES (ud)</option>
                        <option value="CAJAS" <?= ($accesorio['unidad_medida'] ?? '') === 'CAJAS' ? 'selected' : '' ?>>CAJAS</option>
                        <option value="KILOS" <?= ($accesorio['unidad_medida'] ?? '') === 'KILOS' ? 'selected' : '' ?>>KILOS (kg)</option>
                        <option value="LITROS" <?= ($accesorio['unidad_medida'] ?? '') === 'LITROS' ? 'selected' : '' ?>>LITROS (l)</option>
                        <option value="PARES" <?= ($accesorio['unidad_medida'] ?? '') === 'PARES' ? 'selected' : '' ?>>PARES</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="marca" class="form-label">MARCA</label>
                    <input type="text" class="form-control" id="marca" name="marca"
                           value="<?= htmlspecialchars($accesorio['marca'] ?? '') ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="modelo" class="form-label">MODELO</label>
                    <input type="text" class="form-control" id="modelo" name="modelo"
                           value="<?= htmlspecialchars($accesorio['modelo'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="numero_serie_mac" class="form-label">N° Serie/MAC</label>
                    <input type="text" class="form-control" id="numero_serie_mac" name="numero_serie_mac"
                           value="<?= htmlspecialchars($accesorio['numero_serie_mac'] ?? '') ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="fecha_entrada" class="form-label">FECHA DE INGRESO</label>
                    <input type="date" class="form-control" id="fecha_entrada" name="fecha_entrada"
                           value="<?= $accesorio['fecha_entrada'] ?? date('Y-m-d') ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="calidad" class="form-label">CALIDAD</label>
                    <select class="form-select" id="calidad" name="calidad">
                        <option value="NUEVO" <?= ($accesorio['calidad'] ?? '') === 'NUEVO' ? 'selected' : '' ?>>NUEVO</option>
                        <option value="BUENO" <?= ($accesorio['calidad'] ?? '') === 'BUENO' ? 'selected' : '' ?>>BUENO</option>
                        <option value="REGULAR" <?= ($accesorio['calidad'] ?? '') === 'REGULAR' ? 'selected' : '' ?>>REGULAR</option>
                        <option value="MALO" <?= ($accesorio['calidad'] ?? '') === 'MALO' ? 'selected' : '' ?>>MALO</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="costo" class="form-label">PRECIO POR UNIDAD (Bs.)</label>
                    <input type="number" class="form-control" id="costo" name="costo" min="0" step="0.01"
                           value="<?= htmlspecialchars($accesorio['costo'] ?? '') ?>"
                           placeholder="0.00">
                </div>

                <div class="col-md-8 mb-3">
                    <label for="id_proveedor" class="form-label">PROVEEDOR</label>
                    <select class="form-select" id="id_proveedor" name="id_proveedor">
                        <option value="">Seleccionar proveedor...</option>
                        <?php foreach (($proveedores ?? []) as $proveedor): ?>
                        <option value="<?= (int)$proveedor['id_proveedor'] ?>"
                                <?= (string)($accesorio['id_proveedor'] ?? '') === (string)$proveedor['id_proveedor'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($proveedor['nombre_empresa']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="observaciones" class="form-label">OBSERVACIONES</label>
                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?= htmlspecialchars($accesorio['observaciones'] ?? '') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> <?= $accion === 'crear' ? 'Guardar' : 'Actualizar' ?>
                </button>
                <a href="index.php?controller=accesorios" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php if ($accion === 'crear'): ?>
<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-tags"></i> Registrar nuevo tipo de accesorio
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?controller=accesorios&action=guardarTipo" class="row g-3">
            <div class="col-md-3">
                <label for="tipo_codigo" class="form-label">Código <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="tipo_codigo" name="tipo_codigo" required placeholder="Ej: FD1H">
            </div>
            <div class="col-md-4">
                <label for="tipo_descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="tipo_descripcion" name="tipo_descripcion" required placeholder="Ej: Fibra drop 1 hilo">
            </div>
            <div class="col-md-3">
                <label for="tipo_categoria" class="form-label">Categoría</label>
                <input type="text" class="form-control" id="tipo_categoria" name="tipo_categoria" placeholder="Ej: Fibra, Conector">
            </div>
            <div class="col-md-12">
                <label for="tipo_observaciones" class="form-label">Observaciones</label>
                <textarea class="form-control" id="tipo_observaciones" name="tipo_observaciones" rows="2"></textarea>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-plus-circle"></i> Guardar tipo de accesorio
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
async function autocompletarAccesorioPorCodigo() {
    const codigoSelect = document.getElementById('codigo');
    const codigo = codigoSelect.value;

    if (!codigo) return;

    const selected = codigoSelect.options[codigoSelect.selectedIndex];
    const fallbackDescripcion = selected.getAttribute('data-descripcion') || '';
    const fallbackObservaciones = selected.getAttribute('data-observaciones') || '';

    try {
        const response = await fetch(`index.php?controller=accesorios&action=referenciaCodigo&codigo=${encodeURIComponent(codigo)}`);
        const result = await response.json();
        const data = result.success ? result.data : null;

        document.getElementById('nombre').value = data?.nombre || data?.tipo_descripcion || fallbackDescripcion;
        document.getElementById('unidad_medida').value = data?.unidad_medida || selected.getAttribute('data-categoria') || '';
        document.getElementById('modelo').value = data?.modelo || '';
        document.getElementById('marca').value = data?.marca || '';
        document.getElementById('costo').value = data?.costo ? Number(data.costo).toFixed(2) : '';

        if (data?.id_proveedor) {
            document.getElementById('id_proveedor').value = data.id_proveedor;
        }

        if (data?.calidad) {
            document.getElementById('calidad').value = data.calidad;
        }

        const observaciones = data?.observaciones || data?.tipo_observaciones || fallbackObservaciones;
        if (observaciones) {
            document.getElementById('observaciones').value = observaciones;
        }
    } catch (error) {
        document.getElementById('nombre').value = fallbackDescripcion;
        if (fallbackObservaciones) {
            document.getElementById('observaciones').value = fallbackObservaciones;
        }
    }
}

['tipo_codigo', 'codigo', 'marca', 'modelo', 'numero_serie_mac'].forEach(id => {
    const input = document.getElementById(id);
    if (input) {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const codigoSelect = document.getElementById('codigo');
    codigoSelect.addEventListener('change', autocompletarAccesorioPorCodigo);
});
</script>
