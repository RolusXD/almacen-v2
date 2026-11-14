<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistema de Almacén TOI' ?></title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons -->
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- jQuery y DataTables antes de las vistas, para que los scripts de cada página puedan usarlos -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script>
        $.extend(true, $.fn.dataTable.defaults, {
            scrollX: true,
            autoWidth: false,
            responsive: false
        });
    </script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --primary-color: #1e3a5f;
            --secondary-color: #2c5282;
            --accent-color: #4299e1;
            --success-color: #38a169;
            --warning-color: #dd6b20;
            --danger-color: #e53e3e;
            --sidebar-width: 260px;
            --bg-sidebar: #1a202c;
            --bg-sidebar-hover: #2d3748;
        }

        body {
            background: #f1f5f9;
            min-height: 100vh;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            font-size: 14px;
        }

        /* Sidebar compacto y sobrio */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
            transition: all 0.3s ease;
            padding-bottom: 70px;
            /* Scrollbar personalizada para que no se superponga */
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.2) transparent;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Body sin scrollbar cuando sidebar está colapsado */
        body.sidebar-collapsed {
            overflow-x: hidden;
        }

        body.sidebar-collapsed .main-content {
            margin-right: 0 !important;
        }
        
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 1rem 1.25rem;
            color: white;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            background: rgba(0,0,0,0.2);
        }

        .sidebar-brand i {
            font-size: 1.75rem;
            color: var(--accent-color);
        }

        .sidebar-brand-text h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        .sidebar-brand-text small {
            font-size: 0.7rem;
            opacity: 0.6;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 0.65rem 1rem;
            margin: 0.15rem 0.5rem;
            border-radius: 6px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            font-size: 0.875rem;
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar .nav-link i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar .nav-link span {
            flex-shrink: 1;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: var(--bg-sidebar-hover);
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 0;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Navbar superior - Header principal */
        .top-navbar {
            background: white;
            border-radius: 0;
            padding: 0.75rem 1.5rem;
            margin: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .navbar-title h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }

        .navbar-title small {
            color: #718096;
            font-size: 0.75rem;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .ops-glance {
            display: none;
            align-items: center;
            gap: 0.5rem;
            margin-right: 0.25rem;
        }

        .ops-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #334155;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
        }

        .ops-pill.warning { border-color: #f6ad55; background: #fffaf0; color: #9a3412; }
        .ops-pill.danger { border-color: #fca5a5; background: #fff5f5; color: #991b1b; }
        .ops-pill.info { border-color: #93c5fd; background: #eff6ff; color: #1d4ed8; }

        /* Notificaciones */
        .notification-dropdown {
            position: relative;
        }

        .notification-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .notification-btn:hover {
            border-color: var(--accent-color);
            background: #f7fafc;
        }

        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: var(--danger-color);
            color: white;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: bold;
        }

        .notification-menu {
            position: absolute;
            top: 100%;
            right: 0;
            width: 350px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border: 1px solid #e2e8f0;
            margin-top: 0.5rem;
            z-index: 1000;
            display: none;
        }

        .notification-menu.show {
            display: block;
        }

        .notification-header {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header h6 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .notification-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            gap: 0.75rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .notification-item:hover {
            background: #f8fafc;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notification-icon.success { background: #c6f6d5; color: #22543d; }
        .notification-icon.warning { background: #feebc8; color: #7c2d12; }
        .notification-icon.info { background: #bee3f8; color: #2c5282; }
        .notification-icon.danger { background: #fed7d7; color: #742a2a; }

        .notification-content {
            flex: 1;
        }

        .notification-content p {
            margin: 0;
            font-size: 0.85rem;
            color: #2d3748;
        }

        .notification-content small {
            color: #718096;
            font-size: 0.75rem;
        }

        .notification-footer {
            padding: 0.75rem 1rem;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 0 0 12px 12px;
        }

        .notification-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .notification-empty {
            padding: 1.5rem 1rem;
            text-align: center;
            color: #64748b;
        }

        /* Usuario Dropdown */
        .user-dropdown {
            position: relative;
        }

        .user-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .user-btn:hover {
            border-color: var(--accent-color);
            background: #f7fafc;
        }

        .user-avatar-sm {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .user-menu {
            position: absolute;
            top: 100%;
            right: 0;
            width: 220px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border: 1px solid #e2e8f0;
            margin-top: 0.5rem;
            z-index: 1000;
            display: none;
        }

        .user-menu.show {
            display: block;
        }

        .user-menu-header {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
        }

        .user-menu-header .user-avatar-lg {
            width: 60px;
            height: 60px;
            margin: 0 auto 0.5rem;
        }

        .user-menu-header h6 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .user-menu-header small {
            color: #718096;
            font-size: 0.75rem;
        }

        .user-menu-items {
            padding: 0.5rem 0;
        }

        .user-menu-items a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 1rem;
            color: #2d3748;
            text-decoration: none;
            font-size: 0.875rem;
            transition: background 0.2s;
        }

        .user-menu-items a:hover {
            background: #f7fafc;
        }

        .user-menu-items a i {
            width: 18px;
            color: var(--primary-color);
        }

        .user-menu-divider {
            border-top: 1px solid #e2e8f0;
            margin: 0.5rem 0;
        }

        .user-menu-items .logout {
            color: var(--danger-color);
        }

        .user-menu-items .logout i {
            color: var(--danger-color);
        }

        /* Cards compactos */
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.875rem 1rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--primary-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header i {
            margin-right: 0.5rem;
            color: var(--accent-color);
        }

        .card-body {
            padding: 1rem;
        }

        /* Stat Cards compactas */
        .stat-card {
            border-radius: 8px;
            padding: 1rem;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border-left: 4px solid var(--accent-color);
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }

        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.danger { border-left-color: var(--danger-color); }

        .stat-card .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-card .stat-label {
            font-size: 0.8rem;
            color: #718096;
        }
        
        /* Tablas compactas y sobrias */
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .table {
            margin-bottom: 0;
            font-size: 0.875rem;
        }

        .table thead th {
            background: #f8fafc;
            color: #4a5568;
            border: none;
            padding: 0.75rem 1rem;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 0.625rem 1rem;
            vertical-align: middle;
            border-color: #edf2f7;
            color: #2d3748;
        }

        .table tbody tr {
            border-bottom: 1px solid #edf2f7;
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.5rem 0.75rem;
        }

        /* Botones compactos */
        .btn {
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
        }

        .btn-group .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        /* Badges compactos */
        .badge {
            padding: 0.35rem 0.65rem;
            font-weight: 500;
            border-radius: 6px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Badges de estado sobrios */
        .badge.bg-success {
            background: #c6f6d5 !important;
            color: #22543d !important;
        }

        .badge.bg-warning {
            background: #feebc8 !important;
            color: #7c2d12 !important;
        }

        .badge.bg-info {
            background: #bee3f8 !important;
            color: #2c5282 !important;
        }

        .badge.bg-danger {
            background: #fed7d7 !important;
            color: #742a2a !important;
        }

        .badge.bg-primary {
            background: #c3dafe !important;
            color: #2c5282 !important;
        }

        .badge.bg-secondary {
            background: #e2e8f0 !important;
            color: #4a5568 !important;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stat-card {
                margin-bottom: 1rem;
            }
        }

        @media (min-width: 1200px) {
            .ops-glance {
                display: flex;
            }
        }
        
        /* Animaciones */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease;
        }
        
        /* Loading spinner */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .spinner-custom {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .modal {
            z-index: 2000;
        }

        .modal-backdrop {
            z-index: 1990;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Sidebar colapsable */
        .sidebar {
            transition: width 0.3s ease;
            overflow: visible !important; /* Permitir tooltips fuera del sidebar */
        }

        .sidebar.collapsed {
            width: 80px;
        }

        .sidebar.collapsed .sidebar-brand-text,
        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .user-details {
            display: none !important;
        }

        .sidebar.collapsed .sidebar-brand {
            justify-content: center;
            padding: 1rem;
        }

        .sidebar.collapsed .sidebar-brand i {
            margin: 0;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 0.875rem;
            position: relative;
        }

        .sidebar.collapsed .nav-link i {
            margin: 0;
            font-size: 1.25rem;
        }

        /* Tooltip para sidebar colapsado */
        .sidebar.collapsed .nav-link[data-title]:hover::after {
            content: attr(data-title);
            position: fixed; /* Fixed para que no se corte */
            left: 90px;
            top: 50%;
            transform: translateY(-50%);
            background: #1a202c;
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            white-space: nowrap;
            opacity: 1;
            pointer-events: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 9999; /* Muy alto para estar sobre todo */
            margin-left: 10px;
        }

        /* Flechita del tooltip */
        .sidebar.collapsed .nav-link[data-title]:hover::before {
            content: '';
            position: fixed; /* Fixed para que no se corte */
            left: 78px;
            top: 50%;
            transform: translateY(-50%);
            border: 6px solid transparent;
            border-right-color: #1a202c;
            opacity: 1;
            z-index: 9999;
        }

        .sidebar.collapsed #menuGestion {
            display: none !important;
        }

        .sidebar.collapsed .nav-link .bi-chevron-down {
            display: none;
        }

        /* Deshabilitar pointer events en elementos colapsables cuando sidebar está colapsado */
        .sidebar.collapsed .nav-link[data-bs-toggle="collapse"],
        .sidebar.collapsed .nav-link#btnGestion {
            pointer-events: none; /* Prevenir clicks */
            cursor: default;
            opacity: 0.7;
        }

        /* Pero permitir pointer events en los enlaces normales dentro del sidebar colapsado */
        .sidebar.collapsed .nav-link:not([data-bs-toggle="collapse"]):not(#btnGestion) {
            pointer-events: auto;
            cursor: pointer;
        }

        /* Prevenir comportamiento de hover en enlaces deshabilitados */
        .sidebar.collapsed .nav-link[data-bs-toggle="collapse"]:hover,
        .sidebar.collapsed .nav-link#btnGestion:hover {
            background: transparent !important;
            color: rgba(255,255,255,0.7) !important;
            transform: none !important;
        }

        .btn-toggle-sidebar {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--primary-color);
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-toggle-sidebar:hover {
            background: var(--bg-table-head);
            border-color: var(--primary-color);
            transform: scale(1.05);
        }

        .main-content.expanded {
            margin-left: 70px;
        }
    </style>
    <link href="assets/css/future-interface.css?v=20260417" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <a href="index.php?controller=dashboard" class="sidebar-brand">
            <i class="bi bi-box-seam"></i>
            <div class="sidebar-brand-text">
                <h3>TOI Nexus</h3>
                <small>Control de inventario</small>
            </div>
        </a>

        <ul class="nav flex-column mt-2">
            <li class="nav-item">
                <a class="nav-link <?= ($title ?? '') === 'Dashboard' ? 'active' : '' ?>"
                   href="index.php?controller=dashboard" data-title="Dashboard">
                    <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($title ?? '') === 'Equipos' ? 'active' : '' ?>"
                   href="index.php?controller=equipos" data-title="Equipos">
                    <i class="bi bi-pc-display"></i> <span>Equipos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($title ?? '') === 'Accesorios' ? 'active' : '' ?>"
                   href="index.php?controller=accesorios" data-title="Accesorios">
                    <i class="bi bi-tools"></i> <span>Accesorios</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" id="btnMovimientos" data-title="Movimientos">
                    <i class="bi bi-arrow-left-right"></i> <span>Movimientos</span>
                    <i class="bi bi-chevron-down ms-auto" style="font-size: 0.7rem;"></i>
                </a>
                <div class="collapse <?= in_array(($title ?? ''), ['Movimientos de Equipos', 'Movimientos de Accesorios'], true) ? 'show' : '' ?>" id="menuMovimientos">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?= ($title ?? '') === 'Movimientos de Equipos' ? 'active' : '' ?>"
                               href="index.php?controller=movimientos&action=equipos" data-title="Mover equipos">
                                <i class="bi bi-pc-display"></i> <span>Mover equipos</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($title ?? '') === 'Movimientos de Accesorios' ? 'active' : '' ?>"
                               href="index.php?controller=movimientos&action=accesorios" data-title="Mover accesorios">
                                <i class="bi bi-tools"></i> <span>Mover accesorios</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <!-- Menú desplegable para módulos adicionales (solo admin) -->
            <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="#" id="btnGestion" data-title="Gestión">
                    <i class="bi bi-briefcase"></i> <span>Gestión</span>
                    <i class="bi bi-chevron-down ms-auto" style="font-size: 0.7rem;"></i>
                </a>
                <div class="collapse" id="menuGestion">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?controller=proveedores" data-title="Proveedores">
                                <i class="bi bi-truck"></i> <span>Proveedores</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?controller=tipos_equipos" data-title="Tipos de Equipos">
                                <i class="bi bi-tags"></i> <span>Tipos de Equipos</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?controller=categorias" data-title="Categorías">
                                <i class="bi bi-folder"></i> <span>Categorías</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?controller=marcas" data-title="Marcas">
                                <i class="bi bi-tag"></i> <span>Marcas</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="index.php?controller=mantenimientos" data-title="Mantenimientos">
                    <i class="bi bi-gear"></i> <span>Mantenimientos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?controller=prestamos" data-title="Préstamos">
                    <i class="bi bi-hand-index-thumb"></i> <span>Préstamos</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link" href="index.php?controller=reportes" data-title="Reportes">
                    <i class="bi bi-file-earmark-bar-graph"></i> <span>Reportes</span>
                </a>
            </li>
            <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?= ($title ?? '') === 'Gestión de Usuarios' ? 'active' : '' ?>"
                   href="index.php?controller=usuarios" data-title="Usuarios">
                    <i class="bi bi-people"></i> <span>Usuarios</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar - Header con Usuario y Notificaciones -->
        <div class="top-navbar">
            <div class="navbar-left">
                <!-- Botón colapsar sidebar -->
                <button class="btn-toggle-sidebar" id="btnToggleSidebar" title="Colapsar menú">
                    <i class="bi bi-chevron-double-left"></i>
                </button>
                <div class="navbar-title">
                    <h2><?= $title ?? 'Sistema de Almacén' ?></h2>
                    <small><i class="bi bi-calendar"></i> <?= date('d \d\e F \d\e\l Y') ?> · Inventario sincronizado</small>
                </div>
            </div>
            
            <div class="navbar-actions">
                <?php if (!empty($layoutNotifications)): ?>
                <div class="ops-glance">
                    <?php foreach (array_slice($layoutNotifications, 0, 3) as $opsNote): ?>
                    <?php if ($opsNote['type'] !== 'success'): ?>
                    <span class="ops-pill <?= htmlspecialchars($opsNote['type']) ?>">
                        <i class="bi bi-<?= htmlspecialchars($opsNote['icon']) ?>"></i>
                        <?= htmlspecialchars($opsNote['title']) ?>
                    </span>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Notificaciones Dropdown -->
                <div class="notification-dropdown">
                    <button class="notification-btn" id="btnNotificaciones" title="Notificaciones">
                        <i class="bi bi-bell"></i>
                        <?php if (($layoutNotificationCount ?? 0) > 0): ?>
                        <span class="notification-badge" id="notificacionesCount"><?= (int)$layoutNotificationCount ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="notification-menu" id="notificationMenu">
                        <div class="notification-header">
                            <h6>Notificaciones</h6>
                            <span class="text-muted small"><?= ($layoutNotificationCount ?? 0) > 0 ? 'Alertas activas' : 'Sin pendientes' ?></span>
                        </div>
                        <div class="notification-list">
                            <?php foreach (($layoutNotifications ?? []) as $notification): ?>
                            <a class="notification-item text-decoration-none" href="<?= htmlspecialchars($notification['link']) ?>">
                                <div class="notification-icon <?= htmlspecialchars($notification['type']) ?>">
                                    <i class="bi bi-<?= htmlspecialchars($notification['icon']) ?>"></i>
                                </div>
                                <div class="notification-content">
                                    <p><strong><?= htmlspecialchars($notification['title']) ?></strong> - <?= htmlspecialchars($notification['message']) ?></p>
                                    <small><?= htmlspecialchars($notification['meta']) ?></small>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <div class="notification-footer">
                            <a href="index.php?controller=dashboard"><i class="bi bi-activity"></i> Ir al panel operativo</a>
                        </div>
                    </div>
                </div>

                <!-- Usuario Dropdown -->
                <div class="user-dropdown">
                    <button class="user-btn" id="btnUsuario">
                        <div class="user-avatar-sm">
                            <?= strtoupper(substr($_SESSION['user_nombre'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="text-start d-none d-md-block">
                            <div style="font-size: 0.85rem; font-weight: 600; color: var(--primary-color);">
                                <?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario') ?>
                            </div>
                            <div style="font-size: 0.7rem; color: #718096;">
                                <?= strtoupper($_SESSION['user_rol'] ?? 'USUARIO') ?>
                            </div>
                        </div>
                        <i class="bi bi-chevron-down" style="font-size: 0.7rem; color: #718096;"></i>
                    </button>
                    <div class="user-menu" id="userMenu">
                        <div class="user-menu-header">
                            <div class="user-avatar-lg" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.5rem;">
                                <?= strtoupper(substr($_SESSION['user_nombre'] ?? 'U', 0, 1)) ?>
                            </div>
                            <h6><?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario') ?></h6>
                            <small><?= strtoupper($_SESSION['user_rol'] ?? 'USUARIO') ?></small>
                        </div>
                        <div class="user-menu-items">
                            <a href="index.php?controller=dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>
                            <a href="index.php?controller=usuarios&action=perfil"><i class="bi bi-person"></i> Mi Perfil</a>
                            <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'): ?>
                            <a href="index.php?controller=usuarios"><i class="bi bi-people"></i> Usuarios</a>
                            <?php endif; ?>
                            <a href="index.php?controller=reportes"><i class="bi bi-file-earmark-bar-graph"></i> Reportes</a>
                        </div>
                        <div class="user-menu-divider"></div>
                        <div class="user-menu-items">
                            <a href="index.php?controller=auth&action=logout&csrf_token=<?= urlencode(csrf_token()) ?>" class="logout" onclick="return confirm('¿Cerrar sesión?')">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido de la página -->
        <div class="page-content" style="padding: 1.25rem;">
        <!-- Flash Messages -->
        <?php
        $flash = null;
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }
        if ($flash):
        ?>
        <?php
        $flashClass = match ($flash['type']) {
            'error' => 'danger',
            'warning' => 'warning',
            'info' => 'info',
            default => 'success'
        };
        $flashIcon = match ($flash['type']) {
            'error' => 'exclamation-triangle',
            'warning' => 'exclamation-circle',
            'info' => 'info-circle',
            default => 'check-circle-fill'
        };
        $flashTitle = match ($flash['type']) {
            'error' => '¡Error!',
            'warning' => 'Atención',
            'info' => 'Información',
            default => '¡Éxito!'
        };
        ?>
        <div class="alert alert-<?= $flashClass ?> alert-dismissible fade show fade-in" role="alert">
            <i class="bi bi-<?= $flashIcon ?> me-2"></i>
            <strong><?= $flashTitle ?></strong>
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
