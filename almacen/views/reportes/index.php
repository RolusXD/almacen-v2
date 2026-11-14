<!-- Página de Reportes -->
<div class="fade-in">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <span><i class="bi bi-file-earmark-bar-graph"></i> Centro de Reportes</span>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        <i class="bi bi-info-circle"></i> 
                        Seleccione el tipo de reporte que desea generar. Puede exportar en PDF, Excel o CSV.
                    </p>
                    
                    <div class="row">
                        <!-- Reporte de Equipos -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div style="width: 80px; height: 80px; margin: 0 auto 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-pc-display" style="font-size: 2.5rem; color: white;"></i>
                                    </div>
                                    <h5 class="card-title">Equipos</h5>
                                    <p class="card-text text-muted">Listado completo de todos los equipos en el almacén</p>
                                    <div class="d-grid gap-2">
                                        <a href="index.php?controller=reportes&action=equiposPdf" class="btn btn-danger">
                                            <i class="bi bi-file-earmark-pdf"></i> PDF
                                        </a>
                                        <a href="index.php?controller=equipos" class="btn btn-outline-primary">
                                            <i class="bi bi-table"></i> Ver en Tabla
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Reporte de Accesorios -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div style="width: 80px; height: 80px; margin: 0 auto 1rem; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-tools" style="font-size: 2.5rem; color: white;"></i>
                                    </div>
                                    <h5 class="card-title">Accesorios</h5>
                                    <p class="card-text text-muted">Listado de accesorios con cantidades y stock</p>
                                    <div class="d-grid gap-2">
                                        <a href="index.php?controller=reportes&action=accesoriosPdf" class="btn btn-danger">
                                            <i class="bi bi-file-earmark-pdf"></i> PDF
                                        </a>
                                        <a href="index.php?controller=accesorios" class="btn btn-outline-success">
                                            <i class="bi bi-table"></i> Ver en Tabla
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Reporte de Movimientos -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div style="width: 80px; height: 80px; margin: 0 auto 1rem; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-arrow-left-right" style="font-size: 2.5rem; color: white;"></i>
                                    </div>
                                    <h5 class="card-title">Movimientos</h5>
                                    <p class="card-text text-muted">Historial de movimientos de equipos</p>
                                    <div class="d-grid gap-2">
                                        <a href="index.php?controller=reportes&action=movimientosPdf" class="btn btn-danger">
                                            <i class="bi bi-file-earmark-pdf"></i> PDF
                                        </a>
                                        <a href="index.php?controller=movimientos&action=equipos" class="btn btn-outline-info">
                                            <i class="bi bi-table"></i> Ver en Tabla
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Reporte de Usuarios (Solo Admin) -->
                        <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div style="width: 80px; height: 80px; margin: 0 auto 1rem; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-people" style="font-size: 2.5rem; color: white;"></i>
                                    </div>
                                    <h5 class="card-title">Usuarios</h5>
                                    <p class="card-text text-muted">Listado de usuarios del sistema</p>
                                    <div class="d-grid gap-2">
                                        <a href="index.php?controller=reportes&action=usuariosPdf" class="btn btn-danger">
                                            <i class="bi bi-file-earmark-pdf"></i> PDF
                                        </a>
                                        <a href="index.php?controller=usuarios" class="btn btn-outline-warning">
                                            <i class="bi bi-table"></i> Ver en Tabla
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Reporte de Equipos Disponibles -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div style="width: 80px; height: 80px; margin: 0 auto 1rem; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-check-circle" style="font-size: 2.5rem; color: white;"></i>
                                    </div>
                                    <h5 class="card-title">Equipos Disponibles</h5>
                                    <p class="card-text text-muted">Equipos listos para asignar</p>
                                    <div class="d-grid gap-2">
                                        <a href="index.php?controller=equipos&estado=DISPONIBLE" class="btn btn-outline-success">
                                            <i class="bi bi-eye"></i> Ver Disponibles
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Reporte de Stock Bajo -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div style="width: 80px; height: 80px; margin: 0 auto 1rem; background: linear-gradient(135deg, #ff0844 0%, #ffb199 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-exclamation-triangle" style="font-size: 2.5rem; color: white;"></i>
                                    </div>
                                    <h5 class="card-title">Stock Bajo</h5>
                                    <p class="card-text text-muted">Accesorios con menos de 10 unidades</p>
                                    <div class="d-grid gap-2">
                                        <a href="index.php?controller=accesorios" class="btn btn-outline-warning">
                                            <i class="bi bi-eye"></i> Ver Stock
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Instrucciones -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <span><i class="bi bi-question-circle"></i> ¿Cómo usar los reportes?</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex align-items-start mb-3">
                                <div class="me-3">
                                    <span class="badge bg-primary rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">1</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">PDF</h6>
                                    <p class="text-muted mb-0">Genera un documento PDF listo para imprimir o compartir. Ideal para reportes formales.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start mb-3">
                                <div class="me-3">
                                    <span class="badge bg-success rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">2</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Excel</h6>
                                    <p class="text-muted mb-0">Exporta a formato Excel (.xlsx) para editar datos y crear gráficos personalizados.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start mb-3">
                                <div class="me-3">
                                    <span class="badge bg-info rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">3</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">CSV</h6>
                                    <p class="text-muted mb-0">Formato universal compatible con cualquier hoja de cálculo. Perfecto para importar a otros sistemas.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
