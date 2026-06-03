<?php
/**
 * admin/includes/sidebar.php
 * Menú lateral del panel admin.
 *
 * Variables esperadas del scope padre:
 *   @var int    $reportesPend  Cantidad de reportes pendientes (para badge)
 *   @var string $currentPage   Identificador de la página activa:
 *                              'dashboard' | 'index' | (vacío para páginas sin sidebar activo)
 *
 * En admin/index.php el sidebar usa links con data-section (SPA).
 * En admin/dashboard.php el sidebar usa href con links reales.
 */

// Valor por defecto seguro
$reportesPend = $reportesPend ?? 0;
$currentPage  = $currentPage  ?? '';
?>

<!-- Overlay para mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="mainSidebar" role="complementary" aria-label="Menú lateral">

    <p class="sidebar-section-title">Principal</p>

    <a href="dashboard.php"
       class="sidebar-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>"
       id="link-dashboard">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </a>

    <a href="index.php#productos"
       class="sidebar-link"
       id="link-productos">
        <i class="bi bi-box-seam"></i>
        <span>Productos</span>
    </a>

    <a href="index.php#menu"
       class="sidebar-link"
       id="link-menu">
        <i class="bi bi-calendar-week"></i>
        <span>Menú del Día</span>
    </a>

    <a href="index.php#reportes"
       class="sidebar-link"
       id="link-reportes">
        <i class="bi bi-flag"></i>
        <span>Reportes de Precio</span>
        <?php if ($reportesPend > 0): ?>
        <span class="badge-count"><?= $reportesPend ?></span>
        <?php endif; ?>
    </a>

    <a href="index.php#usuarios"
       class="sidebar-link"
       id="link-usuarios">
        <i class="bi bi-people"></i>
        <span>Usuarios</span>
    </a>

    <a href="index.php#configuracion"
       class="sidebar-link"
       id="link-configuracion">
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
