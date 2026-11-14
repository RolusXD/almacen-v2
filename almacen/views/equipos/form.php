<!-- Formulario de Equipo - Diseño Responsivo 2026 -->
<div class="fade-in">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12">
            <!-- Encabezado -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center py-4">
                    <i class="bi bi-<?= $accion === 'crear' ? 'plus-circle' : 'pencil-square' ?> text-primary" style="font-size: 3rem;"></i>
                    <h3 class="mt-3 mb-0"><?= $title ?></h3>
                    <small class="text-muted">Complete la información del equipo</small>
                </div>
            </div>

            <!-- Formulario -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="index.php?controller=equipos&action=<?= $accion === 'crear' ? 'guardar' : 'actualizar' ?>">
                        <?php if ($accion === 'editar' && $equipo): ?>
                        <input type="hidden" name="id_equipo" value="<?= $equipo['id_equipo'] ?>">
                        <?php endif; ?>

                        <!-- Sección 1: Información Principal -->
                        <h6 class="mb-3 text-primary border-bottom pb-2">
                            <i class="bi bi-upc-scan"></i> Información Principal
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="numero_serie_mac" class="form-label">
                                    N° DE SERIE <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="numero_serie_mac" name="numero_serie_mac" required
                                       value="<?= htmlspecialchars($equipo['numero_serie_mac'] ?? '') ?>"
                                       <?= $accion === 'editar' ? 'readonly' : '' ?>
                                       placeholder="Ej: 214B769004691"
                                       style="text-transform: uppercase;">
                                <?php if ($accion === 'editar'): ?>
                                <small class="text-muted">No editable</small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4">
                                <label for="id_tipo_equipo" class="form-label">
                                    TIPO DE EQUIPO <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="id_tipo_equipo" name="id_tipo_equipo" required onchange="actualizarDescripcion()">
                                    <option value="">Seleccionar tipo...</option>
                                    <?php foreach ($codigos as $tipo): ?>
                                    <option value="<?= $tipo['id_codigo'] ?>" 
                                            data-codigo="<?= htmlspecialchars($tipo['codigo']) ?>"
                                            data-descripcion="<?= htmlspecialchars($tipo['descripcion']) ?>"
                                            <?= ($equipo['id_tipo_equipo'] ?? $equipo['codigo'] ?? '') == $tipo['id_codigo'] ? 'selected' : '' ?>
                                            <?= ($equipo['codigo'] ?? '') === $tipo['codigo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipo['codigo']) ?> - <?= htmlspecialchars($tipo['descripcion']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted"><a href="index.php?controller=tipos_equipos&action=crear" target="_blank">+ Nuevo tipo</a></small>
                            </div>

                            <div class="col-md-4">
                                <label for="codigo_text" class="form-label">CÓDIGO</label>
                                <input type="text" class="form-control" id="codigo_text" readonly
                                       placeholder="Se auto-completa">
                            </div>

                            <div class="col-md-8">
                                <label for="nombre" class="form-label">
                                    DESCRIPCIÓN <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required
                                       value="<?= htmlspecialchars($equipo['nombre'] ?? '') ?>"
                                       placeholder="Ej: ROUTER WIFI 2.4 GHz">
                            </div>

                            <div class="col-md-4">
                                <label for="nombre_corto" class="form-label">NOMBRE CORTO</label>
                                <input type="text" class="form-control" id="nombre_corto" name="nombre_corto"
                                       value="<?= htmlspecialchars($equipo['nombre_corto'] ?? '') ?>"
                                       placeholder="Opcional">
                            </div>
                        </div>

                        <!-- Sección 2: Características Técnicas -->
                        <h6 class="mb-3 text-primary border-bottom pb-2 mt-4">
                            <i class="bi bi-cpu"></i> Características Técnicas
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="marca" class="form-label">MARCA</label>
                                <input type="text" class="form-control" id="marca" name="marca"
                                       value="<?= htmlspecialchars($equipo['marca'] ?? '') ?>"
                                       placeholder="Ej: TPLINK, VSOL, HUAWEI">
                            </div>

                            <div class="col-md-6">
                                <label for="modelo" class="form-label">MODELO</label>
                                <input type="text" class="form-control" id="modelo" name="modelo"
                                       list="modelos_referencia"
                                       value="<?= htmlspecialchars($equipo['modelo'] ?? '') ?>"
                                       placeholder="Ej: WR940N, V2802GW">
                                <datalist id="modelos_referencia">
                                    <?php foreach (($modelosReferencia ?? []) as $referencia): ?>
                                    <option value="<?= htmlspecialchars($referencia['modelo']) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                                <small class="text-muted">Al elegir un modelo registrado se completan marca, descripción, código, proveedor y precio.</small>
                            </div>
                        </div>

                        <!-- Sección 3: Información Económica -->
                        <h6 class="mb-3 text-primary border-bottom pb-2 mt-4">
                            <i class="bi bi-currency-dollar"></i> Información Económica
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="precio" class="form-label">PRECIO (Bs.)</label>
                                <input type="number" step="0.01" class="form-control" id="precio" name="precio"
                                       value="<?= htmlspecialchars($equipo['precio'] ?? '') ?>"
                                       placeholder="0.00">
                            </div>

                            <div class="col-md-4">
                                <label for="proveedor" class="form-label">PROVEEDOR</label>
                                <input type="text" class="form-control" id="proveedor" name="proveedor"
                                       value="<?= htmlspecialchars($equipo['proveedor'] ?? '') ?>"
                                       placeholder="Ej: DIGICORP, COMRADE">
                            </div>

                            <div class="col-md-4">
                                <label for="fecha_entrada" class="form-label">FECHA DE INGRESO</label>
                                <input type="date" class="form-control" id="fecha_entrada" name="fecha_entrada"
                                       value="<?= $equipo['fecha_entrada'] ?? date('Y-m-d') ?>">
                            </div>
                        </div>

                        <!-- Sección 4: Estado y Calidad -->
                        <h6 class="mb-3 text-primary border-bottom pb-2 mt-4">
                            <i class="bi bi-sliders"></i> Estado y Calidad
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="calidad" class="form-label">CALIDAD</label>
                                <select class="form-select" id="calidad" name="calidad">
                                    <option value="NUEVO" <?= ($equipo['calidad'] ?? '') === 'NUEVO' ? 'selected' : '' ?>>NUEVO</option>
                                    <option value="BUENO" <?= ($equipo['calidad'] ?? '') === 'BUENO' ? 'selected' : '' ?>>BUENO</option>
                                    <option value="REGULAR" <?= ($equipo['calidad'] ?? '') === 'REGULAR' ? 'selected' : '' ?>>REGULAR</option>
                                    <option value="MALO" <?= ($equipo['calidad'] ?? '') === 'MALO' ? 'selected' : '' ?>>MALO</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="estado" class="form-label">ESTADO ACTUAL</label>
                                <input type="text" class="form-control" id="estado" 
                                       value="<?= htmlspecialchars($equipo['estado'] ?? 'DISPONIBLE') ?>" disabled>
                                <small class="text-muted">Se actualiza con los movimientos</small>
                            </div>
                        </div>

                        <!-- Sección 5: Observaciones -->
                        <h6 class="mb-3 text-primary border-bottom pb-2 mt-4">
                            <i class="bi bi-chat-text"></i> Observaciones
                        </h6>
                        
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">OBSERVACIONES</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                                      placeholder="Observaciones adicionales..."><?= htmlspecialchars($equipo['observaciones'] ?? '') ?></textarea>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="d-flex gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                                <i class="bi bi-save"></i> <?= $accion === 'crear' ? 'Guardar' : 'Actualizar' ?>
                            </button>
                            <a href="index.php?controller=equipos" class="btn btn-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Actualizar descripción y código cuando se selecciona un tipo
function actualizarDescripcion() {
    const select = document.getElementById('id_tipo_equipo');
    const option = select.options[select.selectedIndex];
    
    if (select.value) {
        const codigo = option.getAttribute('data-codigo');
        const descripcion = option.getAttribute('data-descripcion');
        
        document.getElementById('codigo_text').value = codigo || '';
        
        // Si la descripción está vacía, la completamos
        const nombreInput = document.getElementById('nombre');
        if (!nombreInput.value && descripcion) {
            nombreInput.value = descripcion;
        }
    } else {
        document.getElementById('codigo_text').value = '';
    }
}

// Auto-mayúsculas para campos de texto
['numero_serie_mac', 'marca', 'modelo'].forEach(id => {
    const input = document.getElementById(id);
    if (input) {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
});

async function completarPorModelo() {
    const modeloInput = document.getElementById('modelo');
    const modelo = modeloInput.value.trim();

    if (!modelo) return;

    try {
        const response = await fetch(`index.php?controller=equipos&action=referenciaModelo&modelo=${encodeURIComponent(modelo)}`);
        const result = await response.json();

        if (!result.success || !result.data) return;

        const data = result.data;
        const tipoSelect = document.getElementById('id_tipo_equipo');

        if (data.id_tipo_equipo) {
            tipoSelect.value = data.id_tipo_equipo;
        }

        document.getElementById('codigo_text').value = data.codigo || '';
        document.getElementById('nombre').value = data.nombre || '';
        document.getElementById('marca').value = data.marca || '';
        document.getElementById('proveedor').value = data.proveedor || '';
        document.getElementById('precio').value = data.precio ? Number(data.precio).toFixed(2) : '';

        actualizarDescripcion();
    } catch (error) {
        console.warn('No se pudo cargar la referencia del modelo', error);
    }
}

// Ejecutar al cargar para editar
document.addEventListener('DOMContentLoaded', function() {
    actualizarDescripcion();
    const modeloInput = document.getElementById('modelo');
    modeloInput.addEventListener('change', completarPorModelo);
});
</script>
