<!-- Categorías y Marcas -->
<div class="row fade-in">
    <!-- Categorías -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-folder"></i> Categorías</span>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCategoria">
                    <i class="bi bi-plus-circle"></i> Nueva
                </button>
            </div>
            <div class="card-body">
                <?php if (isset($categorias) && is_array($categorias)): ?>
                <form method="GET" class="mb-3">
                    <input type="hidden" name="controller" value="categorias">
                    <div class="input-group">
                        <input type="text" class="form-control" name="filtro" placeholder="Buscar categoría..." value="<?= htmlspecialchars($filtro ?? '') ?>">
                        <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr><th>Nombre</th><th>Descripción</th><th>Equipos</th><th>Acces.</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categorias)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No hay categorías registradas</td></tr>
                            <?php else: ?>
                                <?php foreach ($categorias as $cat): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($cat['nombre']) ?></strong></td>
                                    <td><small><?= htmlspecialchars($cat['descripcion'] ?? '-') ?></small></td>
                                    <td><span class="badge bg-info"><?= $cat['total_equipos'] ?? 0 ?></span></td>
                                    <td><span class="badge bg-success"><?= $cat['total_accesorios'] ?? 0 ?></span></td>
                                    <td>
                                        <a href="javascript:void(0)" onclick="confirmarEliminacion('index.php?controller=categorias&action=eliminar&id=<?= $cat['id_categoria'] ?>')" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">Cargando categorías...</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Marcas -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-tag"></i> Marcas</span>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalMarca">
                    <i class="bi bi-plus-circle"></i> Nueva
                </button>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <input type="hidden" name="controller" value="marcas">
                    <div class="input-group">
                        <input type="text" class="form-control" name="filtro" placeholder="Buscar marca...">
                        <button class="btn btn-outline-success" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr><th>Nombre</th><th>País</th><th>Web</th><th>Equipos</th><th>Acces.</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($marcas as $marca): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($marca['nombre']) ?></strong></td>
                                <td><?= htmlspecialchars($marca['pais_origen'] ?? '-') ?></td>
                                <td><small><?= htmlspecialchars($marca['pagina_web'] ?? '-') ?></small></td>
                                <td><span class="badge bg-info"><?= $marca['total_equipos'] ?></span></td>
                                <td><span class="badge bg-success"><?= $marca['total_accesorios'] ?></span></td>
                                <td>
                                    <a href="javascript:void(0)" onclick="confirmarEliminacion('index.php?controller=marcas&action=eliminar&id=<?= $marca['id_marca'] ?>')" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Categoria -->
<div class="modal fade" id="modalCategoria" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?controller=categorias&action=guardar">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-folder"></i> Nueva Categoría</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activo" checked>
                        <label class="form-check-label">Categoría Activa</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Marca -->
<div class="modal fade" id="modalMarca" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?controller=marcas&action=guardar">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-tag"></i> Nueva Marca</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">País de Origen</label>
                        <input type="text" name="pais_origen" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Página Web</label>
                        <input type="url" name="pagina_web" class="form-control">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activo" checked>
                        <label class="form-check-label">Marca Activa</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
