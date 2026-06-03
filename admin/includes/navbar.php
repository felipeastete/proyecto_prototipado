<?php
/**
 * admin/includes/navbar.php
 * Barra de navegación superior del panel admin.
 *
 * Variables esperadas del scope padre:
 *   @var string $adminNombre  Nombre del administrador activo
 */
?>
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

        <!-- Botón cerrar sesión -->
        <button class="btn-logout" id="btnLogout">
            <i class="bi bi-box-arrow-right"></i>
            <span class="d-none d-sm-inline">Cerrar Sesión</span>
        </button>
    </div>
</nav>
