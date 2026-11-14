<!-- Formulario de Tipo de Equipo - Diseño Responsivo 2026 -->
<div class="fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <!-- Encabezado -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center py-4">
                    <i class="bi bi-<?= $accion === 'crear' ? 'plus-circle' : 'pencil-square' ?> text-primary" style="font-size: 3rem;"></i>
                    <h3 class="mt-3 mb-0"><?= $title ?></h3>
                    <small class="text-muted">Complete la información del tipo de equipo</small>
                </div>
            </div>

            <!-- Formulario -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="index.php?controller=tipos_equipos&action=<?= $accion === 'crear' ? 'guardar' : 'actualizar' ?>">
                        <?php if ($accion === 'editar' && $tipo): ?>
                        <input type="hidden" name="id_codigo" value="<?= $tipo['id_codigo'] ?>">
                        <?php endif; ?>

                        <!-- Sección: Información Principal -->
                        <h6 class="mb-3 text-primary">
                            <i class="bi bi-info-circle"></i> Información Principal
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="codigo" class="form-label">
                                    CÓDIGO <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg" 
                                       id="codigo" name="codigo" required
                                       value="<?= htmlspecialchars($tipo['codigo'] ?? '') ?>"
                                       placeholder="Ej: ROT2.4" 
                                       style="text-transform: uppercase;">
                                <small class="text-muted">Código único identificador</small>
                            </div>

                            <div class="col-md-6">
                                <label for="prefijo" class="form-label">
                                    PREFIJO
                                </label>
                                <input type="text" class="form-control form-control-lg" 
                                       id="prefijo" name="prefijo"
                                       value="<?= htmlspecialchars($tipo['prefijo'] ?? '') ?>"
                                       placeholder="Ej: ROT"
                                       style="text-transform: uppercase;">
                                <small class="text-muted">Prefijo para búsqueda</small>
                            </div>

                            <div class="col-12">
                                <label for="descripcion" class="form-label">
                                    DESCRIPCIÓN <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg" 
                                       id="descripcion" name="descripcion" required
                                       value="<?= htmlspecialchars($tipo['descripcion'] ?? '') ?>"
                                       placeholder="Ej: ROUTER WIFI 2.4 GHz">
                                <small class="text-muted">Descripción completa del tipo de equipo</small>
                            </div>

                            <div class="col-md-6">
                                <label for="categoria" class="form-label">
                                    CATEGORÍA <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="categoria" name="categoria" required>
                                    <option value="">Seleccionar categoría...</option>
                                    <option value="ROUTER" <?= ($tipo['categoria'] ?? '') === 'ROUTER' ? 'selected' : '' ?>>ROUTER</option>
                                    <option value="ONU" <?= ($tipo['categoria'] ?? '') === 'ONU' ? 'selected' : '' ?>>ONU</option>
                                    <option value="SWITCH" <?= ($tipo['categoria'] ?? '') === 'SWITCH' ? 'selected' : '' ?>>SWITCH</option>
                                    <option value="OTRO" <?= ($tipo['categoria'] ?? '') === 'OTRO' ? 'selected' : '' ?>>OTRO</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="estado" class="form-label">
                                    ESTADO
                                </label>
                                <input type="text" class="form-control form-control-lg" 
                                       id="estado" 
                                       value="<?= ($tipo['activo'] ?? 1) ? 'ACTIVO' : 'INACTIVO' ?>" 
                                       disabled
                                       class="<?= ($tipo['activo'] ?? 1) ? 'text-success' : 'text-danger' ?>">
                                <small class="text-muted">Se puede cambiar después</small>
                            </div>

                            <div class="col-12">
                                <label for="observaciones" class="form-label">
                                    OBSERVACIONES
                                </label>
                                <textarea class="form-control" id="observaciones" name="observaciones" 
                                          rows="3" 
                                          placeholder="Observaciones adicionales..."><?= htmlspecialchars($tipo['observaciones'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="d-flex gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                                <i class="bi bi-save"></i> <?= $accion === 'crear' ? 'Guardar' : 'Actualizar' ?>
                            </button>
                            <a href="index.php?controller=tipos_equipos" class="btn btn-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Ayuda -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h6><i class="bi bi-lightbulb"></i> Consejos</h6>
                    <ul class="mb-0">
                        <li>El <strong>CÓDIGO</strong> debe ser único y preferiblemente en formato corto (ej: ROT2.4, ONUDUAL)</li>
                        <li>El <strong>PREFIJO</strong> ayuda a agrupar tipos similares (ej: ROT para todos los routers)</li>
                        <li>La <strong>DESCRIPCIÓN</strong> debe ser clara y descriptiva para facilitar la búsqueda</li>
                        <li>Los tipos se pueden activar/desactivar sin eliminarlos</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-generar prefijo desde el código
document.getElementById('codigo').addEventListener('input', function() {
    const codigo = this.value.toUpperCase();
    const prefijoInput = document.getElementById('prefijo');
    
    // Si el prefijo está vacío, auto-generar desde las primeras 3 letras del código
    if (!prefijoInput.value && codigo.length >= 3) {
        prefijoInput.value = codigo.substring(0, 3).toUpperCase();
    }
});

// Auto-mayúsculas para todos los campos
['codigo', 'prefijo'].forEach(id => {
    document.getElementById(id).addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
});
</script>
