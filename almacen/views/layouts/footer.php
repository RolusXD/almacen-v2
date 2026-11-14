                <!-- Footer -->
                <footer class="mt-auto pt-4 border-top">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                &copy; <?= date('Y') ?> Sistema de Almacén TOI. Todos los derechos reservados.
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="bi bi-building"></i> Telecomunicaciones Oportunas Inteligentes
                            </small>
                        </div>
                    </div>
                </footer>
            </main>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        window.CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;

        // Toggle Sidebar - Colapsar/Expandir menú lateral
        $(document).ready(function() {
            $('form[method="POST"], form[method="post"]').each(function() {
                if ($(this).find('input[name="csrf_token"]').length === 0) {
                    $('<input>', {
                        type: 'hidden',
                        name: 'csrf_token',
                        value: window.CSRF_TOKEN
                    }).appendTo(this);
                }
            });

            // Verificar estado guardado en localStorage
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed) {
                $('body').addClass('sidebar-collapsed');
                $('#sidebar').addClass('collapsed');
                $('.main-content').addClass('expanded');
                $('#btnToggleSidebar i').removeClass('bi-chevron-double-left').addClass('bi-chevron-double-right');
            }

            // Evento click en botón toggle
            $('#btnToggleSidebar').on('click', function() {
                toggleSidebar();
            });

            // Prevenir TODOS los clicks en el sidebar cuando está colapsado
            $('#sidebar').on('click', 'a.nav-link', function(e) {
                if ($('#sidebar').hasClass('collapsed')) {
                    // Permitir solo si es el botón de gestión (que no debe hacer nada)
                    if ($(this).attr('id') === 'btnGestion') {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    // Para todos los demás enlaces, verificar si tiene href válido
                    var href = $(this).attr('href');
                    if (href && href !== '#' && !href.startsWith('javascript')) {
                        // Permitir navegación normal
                        return true;
                    }
                    // Prevenir cualquier otro comportamiento
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });

            // Prevenir que Bootstrap abra los collapses cuando sidebar está colapsado
            $('.collapse').on('show.bs.collapse', function(e) {
                if ($('#sidebar').hasClass('collapsed')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });

            // Manejar menús colapsables del sidebar
            $('#btnGestion, #btnMovimientos').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // No hacer nada si sidebar está colapsado
                if ($('#sidebar').hasClass('collapsed')) {
                    return false;
                }

                const menuId = this.id === 'btnGestion' ? '#menuGestion' : '#menuMovimientos';
                $(menuId).collapse('toggle');
            });

            // Rotar flechita cuando menú se abre/cierra
            $('#menuGestion, #menuMovimientos').on('shown.bs.collapse', function() {
                const buttonId = this.id === 'menuGestion' ? '#btnGestion' : '#btnMovimientos';
                $(`${buttonId} .bi-chevron-down`)
                    .removeClass('bi-chevron-down')
                    .addClass('bi-chevron-up');
            });

            $('#menuGestion, #menuMovimientos').on('hidden.bs.collapse', function() {
                const buttonId = this.id === 'menuGestion' ? '#btnGestion' : '#btnMovimientos';
                $(`${buttonId} .bi-chevron-up`)
                    .removeClass('bi-chevron-up')
                    .addClass('bi-chevron-down');
            });

            // Función para colapsar sidebar
            function collapseSidebar() {
                $('#sidebar').addClass('collapsed');
                $('.main-content').addClass('expanded');
                $('#btnToggleSidebar i').removeClass('bi-chevron-double-left').addClass('bi-chevron-double-right');
                $('body').addClass('sidebar-collapsed');
                
                // Cerrar menús colapsables y resetear flechitas
                $('#menuGestion').collapse('hide');
                $('#menuMovimientos').collapse('hide');
                $('#btnGestion .bi-chevron-up').removeClass('bi-chevron-up').addClass('bi-chevron-down');
                $('#btnMovimientos .bi-chevron-up').removeClass('bi-chevron-up').addClass('bi-chevron-down');
                
                localStorage.setItem('sidebarCollapsed', 'true');
            }

            // Función para expandir sidebar
            function expandSidebar() {
                $('#sidebar').removeClass('collapsed');
                $('.main-content').removeClass('expanded');
                $('#btnToggleSidebar i').removeClass('bi-chevron-double-right').addClass('bi-chevron-double-left');
                $('body').removeClass('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', 'false');
            }

            // Función toggle
            function toggleSidebar() {
                if ($('#sidebar').hasClass('collapsed')) {
                    expandSidebar();
                } else {
                    collapseSidebar();
                }
            }

            // Toggle Dropdown Notificaciones
            $('#btnNotificaciones').on('click', function(e) {
                e.stopPropagation();
                $('#notificationMenu').toggleClass('show');
                $('#userMenu').removeClass('show');
            });

            // Toggle Dropdown Usuario
            $('#btnUsuario').on('click', function(e) {
                e.stopPropagation();
                $('#userMenu').toggleClass('show');
                $('#notificationMenu').removeClass('show');
            });

            // Cerrar dropdowns al hacer click fuera
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.notification-dropdown').length) {
                    $('#notificationMenu').removeClass('show');
                }
                if (!$(e.target).closest('.user-dropdown').length) {
                    $('#userMenu').removeClass('show');
                }
            });

            // Inicializar tablas con clase 'datatable'
            $('.datatable').each(function() {
                const tableId = $(this).attr('id') || 'tabla_' + Math.random().toString(36).substr(2, 9);
                $(this).attr('id', tableId);
                
                new DataTable(`#${tableId}`, {
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    },
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
                    order: [[0, 'desc']],
                    responsive: false,
                    dom: '<"row"<"col-md-6"B><"col-md-6"f>>rtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                            className: 'btn btn-success btn-sm',
                            title: 'Reporte_' + new Date().toISOString().slice(0,10),
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '<i class="bi bi-file-earmark-text"></i> CSV',
                            className: 'btn btn-info btn-sm text-white',
                            title: 'Reporte_' + new Date().toISOString().slice(0,10)
                        },
                        {
                            extend: 'print',
                            text: '<i class="bi bi-printer"></i> Imprimir',
                            className: 'btn btn-secondary btn-sm',
                            title: 'Reporte de Almacén TOI',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'colvis',
                            text: '<i class="bi bi-eye"></i> Columnas',
                            className: 'btn btn-primary btn-sm'
                        }
                    ]
                });
            });

            // Evitar conflictos de stacking context en modales dentro de contenedores animados.
            $(document).on('show.bs.modal', '.modal', function() {
                const modal = this;

                $('#notificationMenu, #userMenu').removeClass('show');
                $('.dropdown-menu.show').removeClass('show');

                if (modal.parentNode !== document.body) {
                    document.body.appendChild(modal);
                }
            });

            $(document).on('hidden.bs.modal', '.modal', function() {
                if ($('.modal.show').length === 0) {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css({
                        overflow: '',
                        paddingRight: ''
                    });
                }
            });

            enhanceTablesForOperations();
            setTimeout(enhanceTablesForOperations, 250);
            
            // Auto-ocultar solo alertas de éxito e información después de 20 segundos.
            setTimeout(function() {
                $('.alert-success, .alert-info').fadeOut('slow');
            }, 20000);
        });

        function enhanceTablesForOperations() {
            $('table').each(function() {
                const table = this;
                const $table = $(table);

                if ($table.closest('.dataTables_scrollHead, .dataTables_scrollFoot').length) {
                    return;
                }

                if ($table.data('ops-enhanced')) {
                    return;
                }

                if (!$table.closest('.table-responsive, .dataTables_scrollBody').length) {
                    $table.wrap('<div class="table-responsive table-scroll-shell"></div>');
                }

                $table.addClass('ops-data-table');
                $table.data('ops-enhanced', true);

                addColumnResize(table);
                addColumnVisibilityControl(table);
            });
        }

        function addColumnResize(table) {
            const headers = table.querySelectorAll('thead th');

            headers.forEach((th) => {
                if (th.querySelector('.ops-col-resizer')) {
                    return;
                }

                th.style.position = 'relative';
                const resizer = document.createElement('span');
                resizer.className = 'ops-col-resizer';
                resizer.setAttribute('aria-hidden', 'true');
                th.appendChild(resizer);

                let startX = 0;
                let startWidth = 0;

                const onMove = (event) => {
                    const nextWidth = Math.max(72, startWidth + event.clientX - startX);
                    th.style.width = `${nextWidth}px`;
                    th.style.minWidth = `${nextWidth}px`;
                };

                const onUp = () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                    document.body.classList.remove('is-resizing-table');
                };

                resizer.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    startX = event.clientX;
                    startWidth = th.offsetWidth;
                    document.body.classList.add('is-resizing-table');
                    document.addEventListener('mousemove', onMove);
                    document.addEventListener('mouseup', onUp);
                });
            });
        }

        function addColumnVisibilityControl(table) {
            const $table = $(table);
            const headers = Array.from(table.querySelectorAll('thead th'));

            if (headers.length < 2 || $table.closest('.dataTables_wrapper').prev('.ops-table-tools').length) {
                return;
            }

            const tableId = table.id || `tabla_${Math.random().toString(36).slice(2, 9)}`;
            table.id = tableId;
            const isDataTable = $.fn.DataTable && $.fn.DataTable.isDataTable(table);
            const api = isDataTable ? $table.DataTable() : null;
            const wrapper = $table.closest('.dataTables_wrapper');

            if (isDataTable && wrapper.find('.buttons-colvis').length) {
                return;
            }

            const tools = document.createElement('div');
            tools.className = 'ops-table-tools';
            tools.innerHTML = `
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <i class="bi bi-layout-three-columns"></i> Columnas
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-2 ops-column-menu"></div>
                </div>
            `;

            const menu = tools.querySelector('.ops-column-menu');

            headers.forEach((th, index) => {
                const label = th.textContent.replace(/\s+/g, ' ').trim() || `Columna ${index + 1}`;
                const item = document.createElement('label');
                item.className = 'dropdown-item d-flex align-items-center gap-2 small';
                item.innerHTML = `<input class="form-check-input m-0" type="checkbox" checked> <span>${label}</span>`;
                const input = item.querySelector('input');

                input.addEventListener('change', () => {
                    if (api) {
                        api.column(index).visible(input.checked);
                    } else {
                        table.querySelectorAll('tr').forEach((row) => {
                            const cell = row.children[index];
                            if (cell) {
                                cell.style.display = input.checked ? '' : 'none';
                            }
                        });
                    }
                });

                menu.appendChild(item);
            });

            if (wrapper.length) {
                wrapper.before(tools);
            } else {
                $table.closest('.table-responsive, .card-body').first().before(tools);
            }
        }
        
        // Confirmar eliminaciones con SweetAlert2
        function confirmarEliminacion(url) {
            Swal.fire({
                title: '¿Está seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const separator = url.includes('?') ? '&' : '?';
                    if (!url.includes('csrf_token=')) {
                        url = url + separator + 'csrf_token=' + encodeURIComponent(window.CSRF_TOKEN);
                    }
                    window.location.href = url;
                }
            });
        }
        
        // Mostrar notificaciones con SweetAlert2
        function mostrarNotificacion(tipo, mensaje) {
            const iconos = {
                'success': 'success',
                'error': 'error',
                'warning': 'warning',
                'info': 'info'
            };
            
            Swal.fire({
                icon: iconos[tipo] || 'info',
                title: tipo === 'success' ? '¡Éxito!' : tipo === 'error' ? '¡Error!' : 'Información',
                text: mensaje,
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    </script>
    
    <!-- Cerrar page-content y main -->
    </div>
    </main>
</body>
</html>
