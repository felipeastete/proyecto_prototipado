<?php
// ============================================================
//   PANEL ADMINISTRADOR - DASHBOARD INDEPENDIENTE
//   Archivo: admin/dashboard.php
//
//   ✅ Protegido: requiere sesión activa (admin_id).
//   ✅ Redirige a login.php si no hay sesión.
//   ✅ Muestra info del usuario autenticado.
//   ✅ Estadísticas en tiempo real vía API.
//   ✅ Accesos rápidos a secciones.
// ============================================================
require_once __DIR__ . '/../includes/auth.php';

// Protección de sesión — redirige si no está autenticado
requireAuth();

$adminNombre = $_SESSION['admin_nombre'] ?? 'Administrador';
$adminId     = $_SESSION['admin_id'];

// Obtener estadísticas básicas directamente en PHP
$db = getDB();

// Total de productos
$totalProductos = (int) $db->query("SELECT COUNT(*) FROM productos")->fetchColumn();

// Productos agotados
$productosAgotados = (int) $db->query("SELECT COUNT(*) FROM productos WHERE stock = 0")->fetchColumn();

// Productos con stock crítico (≤5)
$stockCritico = (int) $db->query("SELECT COUNT(*) FROM productos WHERE stock > 0 AND stock <= 5")->fetchColumn();

// Total menús registrados
$totalMenus = (int) $db->query("SELECT COUNT(*) FROM menu_dia")->fetchColumn();

// Reportes pendientes
$reportesPend = (int) $db->query("SELECT COUNT(*) FROM reportes_precios WHERE estado = 'pendiente'")->fetchColumn();

// Último login del admin
$stmt = $db->prepare("SELECT ultimo_login FROM administradores WHERE id = :id");
$stmt->execute([':id' => $adminId]);
$adminInfo = $stmt->fetch();
$ultimoLogin = $adminInfo['ultimo_login'] ?? null;

// Hora actual formateada
$ahora = new DateTime('now', new DateTimeZone('America/Santiago'));
$horaActual = $ahora->format('H:i');
$fechaActual = $ahora->format('d \d\e F \d\e Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Panel Admin – <?= SITE_NAME ?></title>
    <meta name="description" content="Dashboard del panel administrativo del Casino Universitario.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --primary:       #a04000;
            --accent:        #d35400;
            --light:         #f39c12;
            --sidebar-bg:    #1a0900;
            --sidebar-hover: rgba(211,84,0,0.15);
            --sidebar-active:#d35400;
            --page-bg:       #f5f0eb;
            --card-bg:       #ffffff;
            --navbar-h:      68px;
            --sidebar-w:     264px;
            --text-main:     #1a0a00;
            --text-muted:    #888;
            --border:        rgba(211,84,0,0.1);
            --shadow-card:   0 4px 20px -4px rgba(160,64,0,0.1);
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--page-bg);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* ═══════════════ NAVBAR ═══════════════ */
        .navbar-admin {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--navbar-h);
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            z-index: 200;
            box-shadow: 0 4px 20px rgba(160,64,0,0.3);
        }
        .navbar-brand-admin {
            display: flex; align-items: center; gap: 0.7rem;
            text-decoration: none;
        }
        .navbar-brand-admin .brand-icon {
            width: 40px; height: 40px;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .navbar-brand-admin .brand-text {
            font-size: 1.1rem; font-weight: 800;
            color: #fff; letter-spacing: -0.3px;
        }
        .navbar-brand-admin .brand-sub {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.65);
            display: block;
            line-height: 1;
        }
        .navbar-right {
            display: flex; align-items: center; gap: 1rem;
        }
        .user-badge {
            display: flex; align-items: center; gap: 0.5rem;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 50px;
            padding: 0.4rem 0.9rem 0.4rem 0.5rem;
            cursor: default;
        }
        .user-avatar {
            width: 30px; height: 30px;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem; color: #fff; font-weight: 700;
        }
        .user-name {
            font-size: 0.88rem; font-weight: 600; color: #fff;
        }
        .btn-logout {
            display: flex; align-items: center; gap: 0.4rem;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 50px;
            padding: 0.4rem 1rem;
            color: #fff;
            font-size: 0.85rem; font-weight: 600;
            text-decoration: none;
            transition: background 0.2s, transform 0.2s;
            font-family: inherit;
            cursor: pointer;
        }
        .btn-logout:hover {
            background: rgba(255,255,255,0.22);
            color: #fff;
            transform: translateY(-1px);
        }

        /* ═══════════════ SIDEBAR ═══════════════ */
        .sidebar {
            position: fixed;
            top: var(--navbar-h);
            left: 0;
            bottom: 0;
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            overflow-y: auto;
            z-index: 100;
            padding: 1.5rem 0;
            border-right: 1px solid rgba(255,255,255,0.05);
            transition: transform 0.3s ease;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        .sidebar-section-title {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.3);
            padding: 1rem 1.5rem 0.4rem;
            margin-top: 0.5rem;
        }
        .sidebar-link {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            font-size: 0.9rem; font-weight: 600;
            border-left: 3px solid transparent;
            transition: all 0.2s;
            position: relative;
        }
        .sidebar-link i { font-size: 1.05rem; width: 20px; text-align: center; flex-shrink: 0; }
        .sidebar-link:hover {
            color: #fff;
            background: var(--sidebar-hover);
            border-left-color: rgba(211,84,0,0.5);
        }
        .sidebar-link.active {
            color: #fff;
            background: rgba(211,84,0,0.2);
            border-left-color: var(--sidebar-active);
        }
        .sidebar-link .badge-count {
            margin-left: auto;
            background: var(--accent);
            color: #fff;
            font-size: 0.7rem;
            padding: 0.15rem 0.5rem;
            border-radius: 50px;
            font-weight: 700;
        }
        .sidebar-divider {
            height: 1px;
            background: rgba(255,255,255,0.06);
            margin: 0.75rem 1.5rem;
        }

        /* ═══════════════ MAIN CONTENT ═══════════════ */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            padding-top: var(--navbar-h);
            min-height: 100vh;
        }
        .page-content {
            padding: 2rem;
        }

        /* ── Encabezado de página ── */
        .page-header {
            display: flex; align-items: flex-start;
            justify-content: space-between; flex-wrap: wrap; gap: 1rem;
            margin-bottom: 2rem;
        }
        .page-title { font-size: 1.6rem; font-weight: 800; margin-bottom: 0.2rem; letter-spacing: -0.5px; }
        .page-subtitle { font-size: 0.88rem; color: var(--text-muted); }
        .page-date-chip {
            display: flex; align-items: center; gap: 0.5rem;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 50px;
            padding: 0.45rem 1rem;
            font-size: 0.85rem; font-weight: 600;
            color: var(--accent);
            box-shadow: var(--shadow-card);
        }

        /* ═══════════════ STAT CARDS ═══════════════ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.2rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-card);
            transition: transform 0.25s, box-shadow 0.25s;
            position: relative;
            overflow: hidden;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            bottom: -20px; right: -20px;
            width: 80px; height: 80px;
            border-radius: 50%;
            opacity: 0.06;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 35px -8px rgba(160,64,0,0.18);
        }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }
        .stat-number {
            font-size: 2rem; font-weight: 800;
            line-height: 1; margin-bottom: 0.3rem;
        }
        .stat-label { font-size: 0.85rem; color: var(--text-muted); font-weight: 600; }
        .stat-trend {
            display: inline-flex; align-items: center; gap: 0.3rem;
            font-size: 0.78rem; font-weight: 700;
            margin-top: 0.5rem;
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
        }

        /* Colores de stat cards */
        .stat-card.orange .stat-icon { background: rgba(211,84,0,0.12); color: var(--accent); }
        .stat-card.orange .stat-number { color: var(--accent); }
        .stat-card.orange::after { background: var(--accent); }
        .stat-card.green .stat-icon { background: rgba(39,174,96,0.12); color: #27ae60; }
        .stat-card.green .stat-number { color: #27ae60; }
        .stat-card.green::after { background: #27ae60; }
        .stat-card.red .stat-icon { background: rgba(231,76,60,0.12); color: #e74c3c; }
        .stat-card.red .stat-number { color: #e74c3c; }
        .stat-card.red::after { background: #e74c3c; }
        .stat-card.blue .stat-icon { background: rgba(41,128,185,0.12); color: #2980b9; }
        .stat-card.blue .stat-number { color: #2980b9; }
        .stat-card.blue::after { background: #2980b9; }
        .stat-card.amber .stat-icon { background: rgba(243,156,18,0.12); color: #e67e22; }
        .stat-card.amber .stat-number { color: #e67e22; }

        /* ═══════════════ INFO CARDS (2 columnas) ═══════════════ */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        @media (max-width: 900px) { .info-grid { grid-template-columns: 1fr; } }

        .info-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-card);
        }
        .info-card-title {
            font-size: 0.9rem; font-weight: 700;
            color: var(--primary);
            display: flex; align-items: center; gap: 0.5rem;
            margin-bottom: 1.2rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid var(--border);
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        /* ─ Perfil admin ─ */
        .profile-avatar {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, var(--accent), var(--light));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; color: #fff; font-weight: 800;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 24px -8px rgba(211,84,0,0.4);
        }
        .profile-name { font-weight: 800; font-size: 1.1rem; text-align: center; }
        .profile-role {
            text-align: center; font-size: 0.82rem; color: #fff;
            background: var(--accent); display: inline-block;
            padding: 0.2rem 0.8rem; border-radius: 50px;
            margin: 0.3rem auto;
        }
        .profile-meta {
            display: flex; flex-direction: column; gap: 0.6rem;
            margin-top: 1.2rem;
        }
        .profile-meta-item {
            display: flex; align-items: center; gap: 0.6rem;
            font-size: 0.86rem; color: #555;
        }
        .profile-meta-item i { color: var(--accent); font-size: 0.95rem; }

        /* ─ Accesos rápidos ─ */
        .quick-links { display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; }
        .quick-link {
            display: flex; align-items: center; gap: 0.7rem;
            padding: 0.85rem 1rem;
            background: #faf5f0;
            border: 1px solid rgba(211,84,0,0.1);
            border-radius: 14px;
            text-decoration: none;
            color: var(--text-main);
            font-size: 0.88rem; font-weight: 600;
            transition: all 0.2s;
        }
        .quick-link i { font-size: 1.1rem; color: var(--accent); flex-shrink: 0; }
        .quick-link:hover {
            background: rgba(211,84,0,0.08);
            color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px -6px rgba(211,84,0,0.25);
        }

        /* ─ Alertas de sistema ─ */
        .system-alerts { display: flex; flex-direction: column; gap: 0.7rem; }
        .sys-alert {
            display: flex; align-items: flex-start; gap: 0.8rem;
            padding: 0.9rem 1rem;
            border-radius: 12px;
            font-size: 0.88rem; font-weight: 600;
        }
        .sys-alert i { font-size: 1rem; margin-top: 0.05rem; }
        .sys-alert.warning { background: rgba(243,156,18,0.1); color: #b7770d; border: 1px solid rgba(243,156,18,0.25); }
        .sys-alert.danger  { background: rgba(231,76,60,0.1);  color: #c0392b; border: 1px solid rgba(231,76,60,0.2); }
        .sys-alert.success { background: rgba(39,174,96,0.1);  color: #1e8449; border: 1px solid rgba(39,174,96,0.2); }
        .sys-alert.info    { background: rgba(41,128,185,0.1); color: #1a5276; border: 1px solid rgba(41,128,185,0.2); }
        .sys-alert .alert-text { flex: 1; }
        .sys-alert .alert-action {
            font-size: 0.8rem; font-weight: 700;
            text-decoration: underline; cursor: pointer;
            white-space: nowrap;
            background: none; border: none;
            color: inherit; font-family: inherit;
        }

        /* ─ Dashboard completo link ─ */
        .full-dashboard-btn {
            display: flex; align-items: center; justify-content: center; gap: 0.6rem;
            width: 100%; padding: 1rem;
            background: linear-gradient(135deg, var(--accent), var(--light));
            color: #fff; font-weight: 700; font-size: 0.95rem;
            border-radius: 14px; text-decoration: none;
            box-shadow: 0 8px 24px -8px rgba(211,84,0,0.4);
            transition: transform 0.2s, box-shadow 0.2s, filter 0.2s;
            margin-top: 1.5rem;
        }
        .full-dashboard-btn:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 14px 32px -8px rgba(211,84,0,0.5);
            filter: brightness(1.05);
        }

        /* ═══════════════ RESPONSIVE ═══════════════ */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .page-content { padding: 1.25rem; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .quick-links { grid-template-columns: 1fr; }
            .navbar-right .user-badge { display: none; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
        }

        /* ─ Hamburguesa ─ */
        .sidebar-toggle {
            display: none;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            width: 36px; height: 36px;
            align-items: center; justify-content: center;
            color: #fff; font-size: 1.1rem; cursor: pointer;
        }
        @media (max-width: 768px) { .sidebar-toggle { display: flex; } }
        .sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 99;
        }
        .sidebar-overlay.active { display: block; }

        /* ─ Animaciones de entrada ─ */
        .fade-in { animation: fadeIn 0.5s ease both; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .delay-1 { animation-delay: 0.05s; }
        .delay-2 { animation-delay: 0.1s; }
        .delay-3 { animation-delay: 0.15s; }
        .delay-4 { animation-delay: 0.2s; }
        .delay-5 { animation-delay: 0.25s; }
    </style>
</head>
<body>

<!-- ═══════════════ NAVBAR ═══════════════ -->
<nav class="navbar-admin" role="navigation" aria-label="Navegación principal">
    <div class="d-flex align-items-center gap-3">
        <!-- Hamburguesa (mobile) -->
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menú lateral">
            <i class="bi bi-list"></i>
        </button>

        <!-- Brand -->
        <a class="navbar-brand-admin" href="index.php">
            <div class="brand-icon"><i class="bi bi-shop"></i></div>
            <div>
                <span class="brand-text"><?= SITE_NAME ?></span>
                <span class="brand-sub">Panel Admin</span>
            </div>
        </a>
    </div>

    <div class="navbar-right">
        <!-- Badge usuario -->
        <div class="user-badge">
            <div class="user-avatar"><?= strtoupper(substr($adminNombre, 0, 1)) ?></div>
            <span class="user-name"><?= e($adminNombre) ?></span>
        </div>

        <!-- Botón cerrar sesión con confirmación -->
        <button class="btn-logout" id="btnLogout">
            <i class="bi bi-box-arrow-right"></i>
            <span class="d-none d-sm-inline">Cerrar Sesión</span>
        </button>
    </div>
</nav>

<!-- ═══════════════ SIDEBAR ═══════════════ -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="mainSidebar" role="complementary" aria-label="Menú lateral">

    <p class="sidebar-section-title">Principal</p>

    <a href="dashboard.php" class="sidebar-link active" id="link-dashboard">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </a>

    <a href="index.php#productos" class="sidebar-link" id="link-productos">
        <i class="bi bi-box-seam"></i>
        <span>Productos</span>
    </a>

    <a href="index.php#menu" class="sidebar-link" id="link-menu">
        <i class="bi bi-calendar-week"></i>
        <span>Menú del Día</span>
    </a>

    <a href="index.php#reportes" class="sidebar-link" id="link-reportes">
        <i class="bi bi-flag"></i>
        <span>Reportes de Precio</span>
        <?php if ($reportesPend > 0): ?>
        <span class="badge-count"><?= $reportesPend ?></span>
        <?php endif; ?>
    </a>

    <a href="index.php#usuarios" class="sidebar-link" id="link-usuarios">
        <i class="bi bi-people"></i>
        <span>Usuarios</span>
    </a>

    <a href="index.php#configuracion" class="sidebar-link" id="link-configuracion">
        <i class="bi bi-gear"></i>
        <span>Configuración</span>
    </a>

    <div class="sidebar-divider"></div>
    <p class="sidebar-section-title">Sistema</p>

    <a href="<?= SITE_URL ?>" target="_blank" class="sidebar-link" id="link-sitio">
        <i class="bi bi-eye"></i>
        <span>Ver Sitio Público</span>
    </a>

    <a href="logout.php" class="sidebar-link" id="link-logout">
        <i class="bi bi-door-open"></i>
        <span>Salir</span>
    </a>

</aside>

<!-- ═══════════════ CONTENIDO PRINCIPAL ═══════════════ -->
<div class="main-wrapper">
    <main class="page-content" role="main">

        <!-- Encabezado -->
        <div class="page-header fade-in">
            <div>
                <h1 class="page-title">¡Hola, <?= e($adminNombre) ?>! 👋</h1>
                <p class="page-subtitle">Resumen general del sistema · <?= $fechaActual ?></p>
            </div>
            <div class="page-date-chip">
                <i class="bi bi-clock"></i> <?= $horaActual ?> hrs
            </div>
        </div>

        <!-- ── TARJETAS DE ESTADÍSTICAS ── -->
        <div class="stats-grid">

            <div class="stat-card orange fade-in delay-1">
                <div class="stat-icon"><i class="bi bi-box-seam-fill"></i></div>
                <div class="stat-number"><?= $totalProductos ?></div>
                <div class="stat-label">Productos Totales</div>
                <div class="stat-trend" style="background:rgba(211,84,0,0.1); color:var(--accent);">
                    <i class="bi bi-database"></i> En catálogo
                </div>
            </div>

            <div class="stat-card green fade-in delay-2">
                <div class="stat-icon"><i class="bi bi-calendar-check-fill"></i></div>
                <div class="stat-number"><?= $totalMenus ?></div>
                <div class="stat-label">Menús Registrados</div>
                <div class="stat-trend" style="background:rgba(39,174,96,0.1); color:#27ae60;">
                    <i class="bi bi-calendar3"></i> Programados
                </div>
            </div>

            <div class="stat-card <?= $reportesPend > 0 ? 'red' : 'green' ?> fade-in delay-3">
                <div class="stat-icon"><i class="bi bi-flag-fill"></i></div>
                <div class="stat-number"><?= $reportesPend ?></div>
                <div class="stat-label">Reportes Pendientes</div>
                <?php if ($reportesPend > 0): ?>
                <div class="stat-trend" style="background:rgba(231,76,60,0.1); color:#e74c3c;">
                    <i class="bi bi-exclamation-circle"></i> Requieren atención
                </div>
                <?php else: ?>
                <div class="stat-trend" style="background:rgba(39,174,96,0.1); color:#27ae60;">
                    <i class="bi bi-check-circle"></i> Todo en orden
                </div>
                <?php endif; ?>
            </div>

            <div class="stat-card <?= $productosAgotados > 0 ? 'red' : ($stockCritico > 0 ? 'amber' : 'green') ?> fade-in delay-4">
                <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div class="stat-number"><?= $productosAgotados + $stockCritico ?></div>
                <div class="stat-label">Stock Crítico / Agotado</div>
                <?php if ($productosAgotados > 0 || $stockCritico > 0): ?>
                <div class="stat-trend" style="background:rgba(231,76,60,0.1); color:#e74c3c;">
                    <i class="bi bi-arrow-up-circle"></i> <?= $productosAgotados ?> agotados · <?= $stockCritico ?> críticos
                </div>
                <?php else: ?>
                <div class="stat-trend" style="background:rgba(39,174,96,0.1); color:#27ae60;">
                    <i class="bi bi-check2-circle"></i> Stock saludable
                </div>
                <?php endif; ?>
            </div>

        </div><!-- /.stats-grid -->

        <!-- ── GRID DE INFORMACIÓN ── -->
        <div class="info-grid">

            <!-- Perfil del administrador -->
            <div class="info-card fade-in delay-2">
                <div class="info-card-title"><i class="bi bi-person-badge"></i> Perfil del Administrador</div>
                <div class="text-center">
                    <div class="profile-avatar"><?= strtoupper(substr($adminNombre, 0, 1)) ?></div>
                    <div class="profile-name"><?= e($adminNombre) ?></div>
                    <div class="d-flex justify-content-center mt-1">
                        <span class="profile-role">Administrador</span>
                    </div>
                </div>
                <div class="profile-meta">
                    <div class="profile-meta-item">
                        <i class="bi bi-person-circle"></i>
                        <span>ID de sesión: <strong>#<?= $adminId ?></strong></span>
                    </div>
                    <div class="profile-meta-item">
                        <i class="bi bi-clock-history"></i>
                        <span>
                            Último acceso:
                            <strong>
                            <?php if ($ultimoLogin): ?>
                                <?= date('d/m/Y H:i', strtotime($ultimoLogin)) ?> hrs
                            <?php else: ?>
                                Este es tu primer acceso
                            <?php endif; ?>
                            </strong>
                        </span>
                    </div>
                    <div class="profile-meta-item">
                        <i class="bi bi-shield-check"></i>
                        <span>Sesión: <strong style="color:#27ae60;">Activa y segura</strong></span>
                    </div>
                </div>

                <!-- Ir al panel completo -->
                <a href="index.php" class="full-dashboard-btn">
                    <i class="bi bi-grid-1x2-fill"></i> Ir al Panel Completo
                </a>
            </div>

            <!-- Panel derecho: alertas + accesos rápidos -->
            <div style="display:flex; flex-direction:column; gap:1.5rem;">

                <!-- Alertas del sistema -->
                <div class="info-card fade-in delay-3">
                    <div class="info-card-title"><i class="bi bi-bell-fill"></i> Alertas del Sistema</div>
                    <div class="system-alerts">

                        <?php if ($productosAgotados > 0): ?>
                        <div class="sys-alert danger">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <div class="alert-text">
                                <strong><?= $productosAgotados ?> producto<?= $productosAgotados > 1 ? 's' : '' ?> agotado<?= $productosAgotados > 1 ? 's' : '' ?>.</strong>
                                Repón inventario cuanto antes.
                            </div>
                            <a href="index.php" class="alert-action">Ver →</a>
                        </div>
                        <?php endif; ?>

                        <?php if ($stockCritico > 0): ?>
                        <div class="sys-alert warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div class="alert-text">
                                <strong><?= $stockCritico ?> producto<?= $stockCritico > 1 ? 's' : '' ?> con stock ≤ 5 unidades.</strong>
                                Nivel crítico.
                            </div>
                            <a href="index.php" class="alert-action">Revisar →</a>
                        </div>
                        <?php endif; ?>

                        <?php if ($reportesPend > 0): ?>
                        <div class="sys-alert info">
                            <i class="bi bi-flag-fill"></i>
                            <div class="alert-text">
                                <strong><?= $reportesPend ?> reporte<?= $reportesPend > 1 ? 's' : '' ?> de precio pendiente<?= $reportesPend > 1 ? 's' : '' ?>.</strong>
                                Revisa los precios reportados.
                            </div>
                            <a href="index.php" class="alert-action">Ver →</a>
                        </div>
                        <?php endif; ?>

                        <?php if ($productosAgotados === 0 && $stockCritico === 0 && $reportesPend === 0): ?>
                        <div class="sys-alert success">
                            <i class="bi bi-check-circle-fill"></i>
                            <div class="alert-text">
                                <strong>¡Todo en orden!</strong>
                                No hay alertas pendientes en este momento.
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- Accesos rápidos -->
                <div class="info-card fade-in delay-4">
                    <div class="info-card-title"><i class="bi bi-lightning-charge-fill"></i> Accesos Rápidos</div>
                    <div class="quick-links">
                        <a href="index.php" class="quick-link">
                            <i class="bi bi-grid-1x2"></i> Panel Completo
                        </a>
                        <a href="index.php" class="quick-link">
                            <i class="bi bi-plus-circle"></i> Nuevo Producto
                        </a>
                        <a href="index.php" class="quick-link">
                            <i class="bi bi-calendar-plus"></i> Agregar Menú
                        </a>
                        <a href="index.php" class="quick-link">
                            <i class="bi bi-flag"></i> Ver Reportes
                        </a>
                        <a href="<?= SITE_URL ?>" target="_blank" class="quick-link">
                            <i class="bi bi-eye"></i> Ver Sitio
                        </a>
                        <a href="logout.php" class="quick-link" id="quickLogout">
                            <i class="bi bi-door-open"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>

            </div><!-- /right panel -->
        </div><!-- /.info-grid -->

    </main>
</div><!-- /.main-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ── Toggle Sidebar (mobile) ──────────────────────────────
const sidebar   = document.getElementById('mainSidebar');
const overlay   = document.getElementById('sidebarOverlay');
const toggleBtn = document.getElementById('sidebarToggle');

function openSidebar()  { sidebar.classList.add('open'); overlay.classList.add('active'); }
function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('active'); }

toggleBtn?.addEventListener('click', () => {
    sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
});
overlay?.addEventListener('click', closeSidebar);

// ── Confirmación de cierre de sesión ────────────────────
function confirmLogout(e) {
    e.preventDefault();
    const href = e.currentTarget.href || 'logout.php';
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: 'Se cerrará tu sesión de administrador.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-door-open me-1"></i> Sí, salir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d35400',
        cancelButtonColor: '#888',
        borderRadius: '18px',
    }).then(result => {
        if (result.isConfirmed) {
            window.location.href = href;
        }
    });
}

// Aplicar confirmación a todos los enlaces de logout
document.getElementById('btnLogout')?.addEventListener('click', function(e) {
    e.preventDefault();
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: 'Se cerrará tu sesión de administrador.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d35400',
        cancelButtonColor: '#888',
    }).then(result => {
        if (result.isConfirmed) window.location.href = 'logout.php';
    });
});

document.getElementById('link-logout')?.addEventListener('click', confirmLogout);
document.getElementById('quickLogout')?.addEventListener('click', confirmLogout);
</script>
</body>
</html>
