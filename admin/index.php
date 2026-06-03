<?php
// ============================================================
//   PANEL ADMINISTRADOR - INDEX (DASHBOARD PROFESIONAL)
//   Archivo: admin/index.php
//   VERSIÓN CORREGIDA v1.2.0
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
requireAuth();
$adminNombre = $_SESSION['admin_nombre'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin | <?= e(SITE_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        :root {
            --admin-primary: #a04000;
            --admin-accent:  #d35400;
            --admin-light:   #f39c12;
            --admin-bg:      #fcfaf7;
            --admin-sidebar-bg: #1a0900;
            --admin-card-bg: #ffffff;
            --navbar-h:      68px;
            --sidebar-w:     260px;
            --border:        rgba(211,84,0,0.08);
            --shadow:        0 10px 25px -5px rgba(211,84,0,0.06);
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--admin-bg);
            padding-top: var(--navbar-h);
            margin: 0;
            color: #1a0a00;
        }

        /* ── Navbar ── */
        .navbar-admin {
            position: fixed; top: 0; left: 0; right: 0;
            height: var(--navbar-h);
            background: linear-gradient(90deg, var(--admin-primary) 0%, var(--admin-accent) 100%);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.5rem; z-index: 200;
            box-shadow: 0 4px 20px rgba(160,64,0,0.3);
        }
        .navbar-brand-admin {
            display: flex; align-items: center; gap: 0.7rem;
            text-decoration: none; color: #fff;
            font-weight: 800; font-size: 1.1rem;
        }
        .navbar-brand-admin .brand-icon {
            width: 38px; height: 38px;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .navbar-right { display: flex; align-items: center; gap: 0.75rem; }
        .user-badge {
            display: flex; align-items: center; gap: 0.5rem;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 50px;
            padding: 0.35rem 0.85rem 0.35rem 0.45rem;
        }
        .user-avatar {
            width: 28px; height: 28px;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; color: #fff; font-weight: 700;
        }
        .user-name { font-size: 0.85rem; font-weight: 600; color: #fff; }
        .btn-logout-nav {
            display: flex; align-items: center; gap: 0.4rem;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 50px; padding: 0.4rem 1rem;
            color: #fff; font-size: 0.85rem; font-weight: 600;
            text-decoration: none; cursor: pointer;
            font-family: inherit; transition: background 0.2s;
        }
        .btn-logout-nav:hover { background: rgba(255,255,255,0.22); color: #fff; }

        /* ── Sidebar ── (estilos completos en assets/css/dashboard.css) */
        /* Solo se mantiene aquí la estructura posicional base del sidebar */
        .sidebar {
            position: fixed;
            top: var(--navbar-h); left: 0; bottom: 0;
            width: var(--sidebar-w);
            overflow-y: auto; z-index: 100;
            padding: 1.25rem 0;
            transition: transform 0.3s ease;
        }

        /* ── Main content ── */
        .main-content {
            margin-left: var(--sidebar-w);
            padding: 2rem;
            min-height: calc(100vh - var(--navbar-h));
        }

        /* ── Cards ── */
        .card-stats {
            border: 1px solid var(--border) !important;
            border-radius: 20px !important;
            box-shadow: var(--shadow);
            background: var(--admin-card-bg);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card-stats:hover { transform: translateY(-3px); box-shadow: 0 16px 32px -8px rgba(211,84,0,0.1); }
        .btn-action { border-radius: 100px !important; font-weight: 700; padding: 0.65rem 1.8rem !important; transition: all 0.2s; }
        .btn-action:hover { transform: translateY(-1px); }

        .table-custom {
            background: var(--admin-card-bg);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border) !important;
        }
        .table-custom th {
            background: rgba(211,84,0,0.05);
            font-weight: 700; color: var(--admin-primary);
            padding: 1rem 1rem;
            border-bottom: 2px solid rgba(211,84,0,0.1);
        }
        .table-custom td { padding: 1rem; vertical-align: middle; }

        .modal-content { border-radius: 22px !important; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.18); }
        .modal-header { border-bottom: 1px solid var(--border); }
        .modal-footer { border-top: 1px solid var(--border); }

        .chart-container {
            background: var(--admin-card-bg);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            height: 340px;
            position: relative;
        }
        .restock-btn { transition: all 0.2s; }
        .restock-btn:hover { transform: scale(1.05); }

        /* ── Toggle & Overlay (responsive) — sidebar media queries en dashboard.css ── */
        .sidebar-toggle {
            display: none;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            width: 36px; height: 36px;
            align-items: center; justify-content: center;
            color: #fff; font-size: 1.1rem; cursor: pointer;
        }
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 99;
        }
        .sidebar-overlay.active { display: block; }
        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1.25rem 1rem; }
            .sidebar-toggle { display: flex; }
            .d-none-mobile { display: none !important; }
        }
    </style>
</head>
<body>

<!-- ══ NAVBAR ══ -->
<nav class="navbar-admin" role="navigation" aria-label="Navegación principal">
    <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menú lateral">
            <i class="bi bi-list"></i>
        </button>
        <a class="navbar-brand-admin" href="#">
            <div class="brand-icon"><i class="bi bi-shop"></i></div>
            <div>
                <span><?= e(SITE_NAME) ?></span>
                <small class="d-block" style="opacity:.65;font-weight:500;font-size:.7rem;">Panel Admin</small>
            </div>
        </a>
    </div>
    <div class="navbar-right">
        <div class="user-badge d-none-mobile">
            <div class="user-avatar"><?= strtoupper(substr($adminNombre, 0, 1)) ?></div>
            <span class="user-name"><?= e($adminNombre) ?></span>
        </div>
        <button class="btn-logout-nav" id="btnLogout">
            <i class="bi bi-box-arrow-right"></i>
            <span class="d-none d-sm-inline">Salir</span>
        </button>
    </div>
</nav>

<!-- ══ OVERLAY MOBILE ══ -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar" id="mainSidebar" role="complementary">
    <p class="sidebar-section-label">Principal</p>

    <a class="nav-link active" href="#" data-section="dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a class="nav-link" href="#" data-section="productos"><i class="bi bi-box-seam"></i> Productos</a>
    <a class="nav-link" href="#" data-section="menu"><i class="bi bi-calendar-week"></i> Menú del Día</a>
    <a class="nav-link" href="#" data-section="reportes"><i class="bi bi-flag"></i> Reportes de precio</a>
    <a class="nav-link" href="#" data-section="usuarios"><i class="bi bi-people"></i> Usuarios</a>
    <a class="nav-link" href="#" data-section="configuracion"><i class="bi bi-gear"></i> Configuración</a>

    <div class="sidebar-divider"></div>
    <p class="sidebar-section-label">Sistema</p>
    <a class="nav-link" href="<?= SITE_URL ?>" target="_blank"><i class="bi bi-eye"></i> Ver sitio público</a>
    <a class="nav-link" href="#" id="sidebarLogout"><i class="bi bi-door-open"></i> Cerrar sesión</a>
</aside>

<!-- ══ CONTENIDO PRINCIPAL ══ -->
<div class="main-content" id="mainContent">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3 class="fw-bold mb-0" id="sectionTitle">Dashboard</h3>
        <button class="btn btn-primary btn-action" id="actionBtn" style="display:none;"><i class="bi bi-plus-lg"></i> Nuevo</button>
    </div>
    <div id="dynamicView">
        <div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-3 text-muted">Cargando...</p></div>
    </div>
</div>

<!-- ══════════ MODALES ══════════ -->

<!-- Modal Producto -->
<div class="modal fade" id="productoModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--admin-primary)">
                <h5 class="modal-title fw-bold text-white"><i class="bi bi-box me-1"></i> <span id="productoModalTitle">Nuevo Producto</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="productoForm" novalidate>
                    <input type="hidden" id="prod_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Categoría <span class="text-danger">*</span></label>
                            <select id="prod_categoria" class="form-select" required></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" id="prod_nombre" class="form-control" placeholder="Ej: Barros Luco" required maxlength="120">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea id="prod_descripcion" class="form-control" rows="2" placeholder="Detalle del producto..."></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Precio ($) <span class="text-danger">*</span></label>
                            <input type="number" id="prod_precio" class="form-control" placeholder="Ej: 3500" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Stock</label>
                            <input type="number" id="prod_stock" class="form-control" value="50" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">URL Imagen</label>
                            <input type="text" id="prod_imagen" class="form-control" placeholder="URL (opcional)">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Subir imagen</label>
                            <input type="file" id="prod_imagen_file" class="form-control" accept="image/*">
                            <img id="prod_preview" src="" style="max-height:100px;display:none;border-radius:8px;" class="mt-2">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Advertencias <small class="text-muted fw-normal">(separar con comas)</small></label>
                            <input type="text" id="prod_advertencias" class="form-control" placeholder="Ej: Alto en calorías, Alto en sodio">
                        </div>
                        <div class="col-md-6 d-flex align-items-center gap-3">
                            <div class="form-check">
                                <input type="checkbox" id="prod_disponible" class="form-check-input" checked>
                                <label class="form-check-label fw-semibold" for="prod_disponible">Disponible</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="prod_destacado" class="form-check-input">
                                <label class="form-check-label fw-semibold" for="prod_destacado">Destacado</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-action" id="btnGuardarProducto">
                    <i class="bi bi-save me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Menú -->
<div class="modal fade" id="menuModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-calendar me-1"></i> <span id="menuModalTitle">Nuevo Menú</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="menuForm" novalidate>
                    <input type="hidden" id="menu_id">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Fecha <span class="text-danger">*</span></label>
                            <input type="date" id="menu_fecha" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Plato principal <span class="text-danger">*</span></label>
                            <input type="text" id="menu_plato_nombre" class="form-control" placeholder="Ej: Pastel de Choclo" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea id="menu_plato_desc" class="form-control" rows="2" placeholder="Detalle del plato..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Acompañamiento</label>
                            <input type="text" id="menu_acompanamiento" class="form-control" placeholder="Ej: Arroz, Papas fritas">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ensalada</label>
                            <input type="text" id="menu_ensalada" class="form-control" placeholder="Ej: Tomate, Repollo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Jugo</label>
                            <input type="text" id="menu_jugo" class="form-control" placeholder="Ej: Jugo de piña">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Postre</label>
                            <input type="text" id="menu_postre" class="form-control" placeholder="Ej: Flan casero">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Fruta</label>
                            <input type="text" id="menu_fruta" class="form-control" placeholder="Ej: Manzana">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Precio ($) <span class="text-danger">*</span></label>
                            <input type="number" id="menu_precio" class="form-control" placeholder="Ej: 4200" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Disponible hasta</label>
                            <input type="time" id="menu_disponible_hasta" class="form-control" value="15:00">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">URL Imagen</label>
                            <input type="text" id="menu_imagen" class="form-control" placeholder="URL existente (opcional)">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Subir imagen</label>
                            <input type="file" id="menu_imagen_file" class="form-control" accept="image/*">
                            <img id="menu_preview" src="" style="max-height:100px;display:none;border-radius:8px;" class="mt-2">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-action" id="btnGuardarMenu">
                    <i class="bi bi-save me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Usuario -->
<div class="modal fade" id="usuarioModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person me-1"></i> <span id="usuarioModalTitle">Nuevo Administrador</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="usuarioForm" novalidate>
                    <input type="hidden" id="us_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" id="us_nombre" class="form-control" placeholder="Ej: Juan Pérez" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Usuario <span class="text-danger">*</span></label>
                        <input type="text" id="us_username" class="form-control" placeholder="Ej: jperez" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                        <input type="email" id="us_email" class="form-control" placeholder="Ej: juan@casino.cl" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Contraseña <span class="text-danger" id="us_pass_required">*</span></label>
                        <input type="password" id="us_password" class="form-control" placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                        <small class="text-muted" id="us_pass_help" style="display:none;">Deja en blanco para conservar la contraseña actual.</small>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="us_activo" class="form-check-input" checked>
                        <label class="form-check-label fw-semibold" for="us_activo">Activo</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark btn-action" id="btnGuardarUsuario">
                    <i class="bi bi-save me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Reporte -->
<div class="modal fade" id="reporteModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-flag me-1"></i> Editar Reporte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reporteForm" novalidate>
                    <input type="hidden" id="rep_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Producto <span class="text-danger">*</span></label>
                        <input type="text" id="rep_producto_nombre" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Precio Sitio ($) <span class="text-danger">*</span></label>
                            <input type="number" id="rep_precio_sitio" class="form-control" required min="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Precio Real ($) <span class="text-danger">*</span></label>
                            <input type="number" id="rep_precio_real" class="form-control" required min="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Comentarios</label>
                        <textarea id="rep_comentarios" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado</label>
                        <select id="rep_estado" class="form-select">
                            <option value="pendiente">Pendiente</option>
                            <option value="revisado">Revisado</option>
                            <option value="resuelto">Resuelto</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger btn-action" id="btnGuardarReporte">
                    <i class="bi bi-save me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambio de Precio Manual -->
<div class="modal fade" id="historialPrecioModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-currency-dollar me-1"></i> Registrar Cambio de Precio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="historialPrecioForm" novalidate>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Producto <span class="text-danger">*</span></label>
                        <select id="hp_producto_id" class="form-select" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nuevo Precio ($) <span class="text-danger">*</span></label>
                        <input type="number" id="hp_precio_nuevo" class="form-control" placeholder="Ej: 3800" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivo <span class="text-danger">*</span></label>
                        <input type="text" id="hp_motivo" class="form-control" placeholder="Ej: Alza de costos de insumos" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning text-dark fw-bold btn-action" id="btnGuardarHistorialPrecio">
                    <i class="bi bi-save me-1"></i> Actualizar Precio
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ════════════════════════════════════════════
//   CASINO UNIVERSITARIO - ADMIN PANEL JS
//   Archivo: admin/assets/js/admin.js (inline)
// ════════════════════════════════════════════

const API_BASE = '../api/api.php';
let currentSection = 'dashboard';
let categoriasList  = [];
// Guarda instancias de Chart.js para destruirlas antes de redibujar
let chartInstances  = {};

// ── Helpers ────────────────────────────────

function escHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function formatPrecio(valor) {
    return '$' + parseInt(valor || 0).toLocaleString('es-CL');
}

async function callApi(action, method = 'GET', body = null) {
    const url = `${API_BASE}?action=${action}`;
    const opts = { method };
    if (body) {
        if (body instanceof FormData) {
            opts.body = body;
        } else {
            opts.headers = { 'Content-Type': 'application/json' };
            opts.body = JSON.stringify(body);
        }
    }
    const res = await fetch(url, opts);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
}

function showSpinner(container) {
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-3 text-muted">Cargando...</p></div>';
}

function destroyCharts() {
    Object.values(chartInstances).forEach(c => { try { c.destroy(); } catch(e){} });
    chartInstances = {};
}

// ── Sidebar Toggle (mobile) ─────────────────
const sidebar   = document.getElementById('mainSidebar');
const overlay   = document.getElementById('sidebarOverlay');
const toggleBtn = document.getElementById('sidebarToggle');

function openSidebar()  { sidebar.classList.add('open'); overlay.classList.add('active'); }
function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('active'); }

toggleBtn?.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
overlay?.addEventListener('click', closeSidebar);

// ── Logout confirm ──────────────────────────
function confirmarLogout(e) {
    e && e.preventDefault();
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: 'Se cerrará la sesión de administrador.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d35400',
        cancelButtonColor: '#888'
    }).then(r => { if (r.isConfirmed) window.location.href = 'logout.php'; });
}

document.getElementById('btnLogout')?.addEventListener('click', confirmarLogout);
document.getElementById('sidebarLogout')?.addEventListener('click', confirmarLogout);

// ── Sidebar navigation ──────────────────────
document.querySelectorAll('.sidebar .nav-link[data-section]').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        closeSidebar();
        document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
        link.classList.add('active');
        currentSection = link.dataset.section;
        document.getElementById('sectionTitle').textContent = link.textContent.trim();
        updateActionBtn(currentSection);
        loadSection(currentSection);
    });
});

function updateActionBtn(section) {
    const btn = document.getElementById('actionBtn');
    if (section === 'productos') {
        btn.style.display = 'block';
        btn.className = 'btn btn-primary btn-action';
        btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i> Nuevo Producto';
        btn.onclick = () => abrirProductoModal();
    } else if (section === 'menu') {
        btn.style.display = 'block';
        btn.className = 'btn btn-success btn-action';
        btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i> Nuevo Menú';
        btn.onclick = () => abrirMenuModal();
    } else if (section === 'usuarios') {
        btn.style.display = 'block';
        btn.className = 'btn btn-dark btn-action';
        btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i> Nuevo Usuario';
        btn.onclick = () => abrirUsuarioModal();
    } else {
        btn.style.display = 'none';
    }
}

async function loadSection(section) {
    const container = document.getElementById('dynamicView');
    destroyCharts();
    showSpinner(container);
    try {
        if      (section === 'dashboard')    await loadDashboard(container);
        else if (section === 'productos')    await loadProductos(container);
        else if (section === 'menu')         await loadMenu(container);
        else if (section === 'reportes')     await loadReportes(container);
        else if (section === 'usuarios')     await loadUsuarios(container);
        else if (section === 'configuracion') await loadConfiguracion(container);
    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Error al cargar sección: ${escHtml(err.message)}</div>`;
    }
}

// ── Cargar categorías al inicio ─────────────
async function loadCategorias() {
    try {
        const res = await callApi('categorias');
        if (res.ok) {
            categoriasList = res.data;
            // Poblar el select del modal de productos
            const sel = document.getElementById('prod_categoria');
            if (sel) {
                sel.innerHTML = categoriasList.map(c =>
                    `<option value="${c.id}">${escHtml(c.nombre)}</option>`
                ).join('');
            }
        }
    } catch(e) { console.error('Error cargando categorías:', e); }
}

// ════════════════════
//   DASHBOARD
// ════════════════════
async function loadDashboard(container) {
    const [prodRes, menuRes, reportRes] = await Promise.all([
        callApi('admin_listar_productos'),
        callApi('admin_listar_menu'),
        callApi('admin_listar_reportes')
    ]);

    const totalProd  = prodRes.ok  ? prodRes.data.length  : 0;
    const totalMenu  = menuRes.ok  ? menuRes.data.length  : 0;
    const reportPend = reportRes.ok ? reportRes.data.filter(r => r.estado === 'pendiente').length : 0;

    const outOfStock   = prodRes.ok ? prodRes.data.filter(p => parseInt(p.stock) === 0) : [];
    const lowStock     = prodRes.ok ? prodRes.data.filter(p => parseInt(p.stock) > 0 && parseInt(p.stock) <= 5) : [];
    const criticalProds = [...outOfStock, ...lowStock];

    // Datos para gráficos
    const catCounts = {};
    const catStock  = {};
    if (prodRes.ok) {
        prodRes.data.forEach(p => {
            const cat = p.categoria_nombre || 'Sin categoría';
            catCounts[cat] = (catCounts[cat] || 0) + 1;
            catStock[cat]  = (catStock[cat]  || 0) + parseInt(p.stock);
        });
    }

    // Reposición rápida HTML
    let restockHtml = '';
    if (criticalProds.length > 0) {
        const rows = criticalProds.map(p => `
            <tr>
                <td><strong>${escHtml(p.nombre)}</strong><br>
                    <small class="text-muted">${escHtml(p.categoria_nombre)}</small></td>
                <td>
                    <span class="badge ${parseInt(p.stock) === 0 ? 'bg-danger' : 'bg-warning text-dark'} px-2 py-1">
                        ${parseInt(p.stock) === 0 ? 'Agotado' : 'Solo ' + p.stock + ' uds'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-success restock-btn rounded-pill px-3 me-1"
                        onclick="restockRapido(${p.id}, 10)"><i class="bi bi-plus-lg"></i> +10</button>
                    <button class="btn btn-sm btn-outline-success restock-btn rounded-pill px-3"
                        onclick="restockRapido(${p.id}, 50)"><i class="bi bi-plus-lg"></i> +50</button>
                </td>
            </tr>`).join('');
        restockHtml = `
        <div class="card border-0 shadow-sm p-4 rounded-4 mt-4">
            <h5 class="fw-bold mb-1 text-danger d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i> Reposición Rápida
            </h5>
            <p class="small text-muted mb-3">Productos con quiebre o stock crítico (&le;5 unidades).</p>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr class="text-muted small">
                        <th>Producto</th><th>Stock</th><th>Añadir unidades</th>
                    </tr></thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        </div>`;
    } else {
        restockHtml = `
        <div class="card border-0 shadow-sm p-4 rounded-4 mt-4 text-center text-success py-5">
            <i class="bi bi-check-circle-fill fs-1 mb-2"></i>
            <h5 class="fw-bold mb-1">¡Todo en Orden!</h5>
            <p class="small mb-0 text-muted">Todos los productos cuentan con stock suficiente.</p>
        </div>`;
    }

    container.innerHTML = `
    <!-- Tarjetas -->
    <div class="row g-4 mb-4">
        <div class="col-sm-4">
            <div class="card card-stats border-0 p-3">
                <div class="d-flex align-items-center">
                    <div class="p-3 rounded-4 me-3" style="background:rgba(211,84,0,.1);color:#d35400">
                        <i class="bi bi-box-seam fs-2"></i></div>
                    <div><h6 class="text-muted mb-1 fw-bold">Productos</h6>
                        <h3 class="mb-0 fw-bold">${totalProd}</h3></div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card card-stats border-0 p-3">
                <div class="d-flex align-items-center">
                    <div class="p-3 rounded-4 me-3" style="background:rgba(39,174,96,.1);color:#27ae60">
                        <i class="bi bi-calendar-week fs-2"></i></div>
                    <div><h6 class="text-muted mb-1 fw-bold">Menús Registrados</h6>
                        <h3 class="mb-0 fw-bold">${totalMenu}</h3></div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card card-stats border-0 p-3">
                <div class="d-flex align-items-center">
                    <div class="p-3 rounded-4 me-3" style="background:rgba(231,76,60,.1);color:#e74c3c">
                        <i class="bi bi-flag fs-2"></i></div>
                    <div><h6 class="text-muted mb-1 fw-bold">Reportes Pendientes</h6>
                        <h3 class="mb-0 fw-bold">${reportPend}</h3></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row g-4 mb-2">
        <div class="col-lg-6">
            <div class="chart-container">
                <h6 class="fw-bold text-muted mb-2"><i class="bi bi-pie-chart-fill me-1"></i>Productos por Categoría</h6>
                <canvas id="chartCategorias"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-container">
                <h6 class="fw-bold text-muted mb-2"><i class="bi bi-bar-chart-line-fill me-1"></i>Stock por Categoría</h6>
                <canvas id="chartStock"></canvas>
            </div>
        </div>
    </div>
    ${restockHtml}`;

    // CORRECCIÓN BUG: Renderizar gráficos correctamente con cierre de llaves
    setTimeout(() => {
        const ctxCat = document.getElementById('chartCategorias')?.getContext('2d');
        if (ctxCat && Object.keys(catCounts).length > 0) {
            chartInstances['cat'] = new Chart(ctxCat, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(catCounts),
                    datasets: [{
                        data: Object.values(catCounts),
                        backgroundColor: ['#d35400','#f39c12','#27ae60','#2980b9','#8e44ad','#2c3e50','#16a085','#c0392b','#7f8c8d'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 12, font: { family: 'Plus Jakarta Sans', size: 10 } }
                        }
                    }
                }
            });
        }

        const ctxStock = document.getElementById('chartStock')?.getContext('2d');
        if (ctxStock && Object.keys(catStock).length > 0) {
            chartInstances['stock'] = new Chart(ctxStock, {
                type: 'bar',
                data: {
                    labels: Object.keys(catStock),
                    datasets: [{
                        label: 'Stock Total',
                        data: Object.values(catStock),
                        backgroundColor: 'rgba(211,84,0,0.72)',
                        borderColor: '#d35400',
                        borderWidth: 1.5,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    }, 80);
}

// Reposición rápida
async function restockRapido(id, cantidad) {
    try {
        const res = await callApi('admin_restock_producto', 'POST', { id, cantidad });
        if (res.ok) {
            Swal.fire({ icon: 'success', title: 'Inventario Actualizado', text: res.message, timer: 2200, showConfirmButton: false });
            loadSection(currentSection);
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.message });
        }
    } catch(err) {
        Swal.fire({ icon: 'error', title: 'Error de Red', text: 'No se pudo registrar la reposición.' });
    }
}

// ════════════════════
//   PRODUCTOS
// ════════════════════
async function loadProductos(container) {
    const res = await callApi('admin_listar_productos');
    if (!res.ok) {
        container.innerHTML = `<div class="alert alert-danger">Error cargando productos: ${escHtml(res.message)}</div>`;
        return;
    }

    const catsOptions = categoriasList.map(c =>
        `<option value="${escHtml(c.nombre)}">${escHtml(c.nombre)}</option>`
    ).join('');

    const rows = res.data.map(p => `
        <tr>
            <td>${p.id}</td>
            <td>
                <strong>${escHtml(p.nombre)}</strong>
                ${p.descripcion ? `<br><small class="text-muted">${escHtml(p.descripcion.substring(0,60))}${p.descripcion.length>60?'…':''}</small>` : ''}
            </td>
            <td>${escHtml(p.categoria_nombre)}</td>
            <td><strong>${formatPrecio(p.precio)}</strong></td>
            <td>
                <span class="badge px-2 py-1 ${parseInt(p.stock)===0 ? 'bg-danger' : parseInt(p.stock)<=5 ? 'bg-warning text-dark' : 'bg-success'}">
                    ${p.stock} uds
                </span>
            </td>
            <td>${p.disponible
                ? '<span class="badge bg-light text-success border border-success border-opacity-25 px-2 py-1">Disponible</span>'
                : '<span class="badge bg-light text-secondary border px-2 py-1">No disponible</span>'}</td>
            <td>
                <button class="btn btn-sm btn-outline-info me-1 rounded-circle" onclick="verDetallesProducto(${p.id})" title="Ver"><i class="bi bi-eye"></i></button>
                <button class="btn btn-sm btn-outline-primary me-1 rounded-circle" onclick="editarProducto(${p.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger rounded-circle" onclick="eliminarProducto(${p.id}, '${escHtml(p.nombre)}')" title="Eliminar"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`).join('');

    container.innerHTML = `
    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" id="busquedaProducto" class="form-control" placeholder="🔍 Buscar por nombre...">
        </div>
        <div class="col-md-4">
            <select id="filtroCategoria" class="form-select">
                <option value="">Todas las categorías</option>${catsOptions}
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0" id="tablaProductos">
            <thead><tr>
                <th>ID</th><th>Nombre</th><th>Categoría</th>
                <th>Precio</th><th>Stock</th><th>Estado</th>
                <th style="width:140px">Acciones</th>
            </tr></thead>
            <tbody>${rows}</tbody>
        </table>
    </div>`;

    document.getElementById('busquedaProducto').addEventListener('input', filtrarTablaProductos);
    document.getElementById('filtroCategoria').addEventListener('change', filtrarTablaProductos);
}

function filtrarTablaProductos() {
    const term = (document.getElementById('busquedaProducto')?.value || '').toLowerCase();
    const cat  = document.getElementById('filtroCategoria')?.value || '';
    document.querySelectorAll('#tablaProductos tbody tr').forEach(r => {
        const name   = r.cells[1]?.textContent.toLowerCase() || '';
        const rowCat = r.cells[2]?.textContent.trim() || '';
        r.style.display = (name.includes(term) && (!cat || rowCat === cat)) ? '' : 'none';
    });
}

async function verDetallesProducto(id) {
    try {
        const res = await callApi('admin_listar_productos');
        if (!res.ok) return;
        const p = res.data.find(x => x.id == id);
        if (!p) return;
        const imgUrl = p.imagen ? (p.imagen.startsWith('http') ? p.imagen : `../assets/img/${p.imagen}`) : '';
        const advs = (p.advertencias_arr || []).map(a => `<span class="badge bg-danger me-1">${escHtml(a)}</span>`).join('') || '<span class="text-muted">Ninguna</span>';
        Swal.fire({
            title: `<strong>${escHtml(p.nombre)}</strong>`,
            html: `<div class="text-start">
                ${imgUrl ? `<div class="text-center mb-3"><img src="${escHtml(imgUrl)}" style="max-height:160px;border-radius:10px;object-fit:cover"
                    onerror="this.style.display='none'"></div>` : ''}
                <p><strong>ID:</strong> #${p.id}</p>
                <p><strong>Categoría:</strong> ${escHtml(p.categoria_nombre)}</p>
                <p><strong>Precio:</strong> <span class="text-success fw-bold">${formatPrecio(p.precio)}</span></p>
                <p><strong>Stock:</strong> <span class="${parseInt(p.stock)===0?'text-danger fw-bold':parseInt(p.stock)<=5?'text-warning fw-bold':'text-success'}">${p.stock} unidades</span></p>
                <p><strong>Descripción:</strong> ${p.descripcion ? escHtml(p.descripcion) : '<span class="text-muted">Sin descripción</span>'}</p>
                <p><strong>Advertencias:</strong> ${advs}</p>
                <p><strong>Estado:</strong> ${p.disponible ? '<span class="badge bg-success">Disponible</span>' : '<span class="badge bg-secondary">No disponible</span>'}
                    ${p.destacado ? '<span class="badge bg-warning text-dark ms-1">Destacado</span>' : ''}</p>
            </div>`,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#a04000'
        });
    } catch(e) {
        Swal.fire('Error', 'No se pudieron cargar los detalles.', 'error');
    }
}

function abrirProductoModal(id = null) {
    document.getElementById('productoForm').reset();
    document.getElementById('prod_id').value = '';
    document.getElementById('prod_preview').style.display = 'none';
    document.getElementById('prod_preview').src = '';
    document.getElementById('productoModalTitle').textContent = id ? 'Editar Producto' : 'Nuevo Producto';

    // Repoblar categorías por si la lista se cargó después
    const sel = document.getElementById('prod_categoria');
    sel.innerHTML = categoriasList.map(c => `<option value="${c.id}">${escHtml(c.nombre)}</option>`).join('');

    if (id) cargarProductoParaEditar(id);
    new bootstrap.Modal(document.getElementById('productoModal')).show();
}

async function cargarProductoParaEditar(id) {
    try {
        const res = await callApi('admin_listar_productos');
        if (!res.ok) return;
        const p = res.data.find(x => x.id == id);
        if (!p) return;
        document.getElementById('prod_id').value           = p.id;
        document.getElementById('prod_categoria').value   = p.categoria_id;
        document.getElementById('prod_nombre').value      = p.nombre;
        document.getElementById('prod_descripcion').value = p.descripcion || '';
        document.getElementById('prod_precio').value      = p.precio;
        document.getElementById('prod_stock').value       = p.stock;
        document.getElementById('prod_imagen').value      = p.imagen || '';
        document.getElementById('prod_advertencias').value = (p.advertencias_arr || []).join(', ');
        document.getElementById('prod_disponible').checked = p.disponible == 1;
        document.getElementById('prod_destacado').checked  = p.destacado == 1;
        document.getElementById('prod_imagen_file').value  = '';

        const preview = document.getElementById('prod_preview');
        if (p.imagen_url) {
            preview.src = p.imagen_url;
            preview.style.display = 'block';
        }
    } catch(e) {
        Swal.fire('Error', 'No se pudieron cargar los datos del producto.', 'error');
    }
}

function editarProducto(id) { abrirProductoModal(id); }

async function guardarProducto() {
    const id          = document.getElementById('prod_id').value;
    const nombre      = document.getElementById('prod_nombre').value.trim();
    const precio      = parseInt(document.getElementById('prod_precio').value);
    const categoriaId = document.getElementById('prod_categoria').value;

    if (!nombre) {
        Swal.fire('Campo requerido', 'El nombre del producto es obligatorio.', 'warning'); return;
    }
    if (!categoriaId) {
        Swal.fire('Campo requerido', 'Selecciona una categoría.', 'warning'); return;
    }
    if (isNaN(precio) || precio < 0) {
        Swal.fire('Precio inválido', 'Ingresa un precio válido (mayor o igual a 0).', 'warning'); return;
    }

    const data = new FormData();
    data.append('categoria_id',  categoriaId);
    data.append('nombre',        nombre);
    data.append('descripcion',   document.getElementById('prod_descripcion').value.trim());
    data.append('precio',        precio);
    data.append('stock',         parseInt(document.getElementById('prod_stock').value) || 0);
    data.append('imagen',        document.getElementById('prod_imagen').value.trim());
    data.append('advertencias',  JSON.stringify(
        document.getElementById('prod_advertencias').value
            .split(',').map(s => s.trim()).filter(Boolean)
    ));
    data.append('disponible',    document.getElementById('prod_disponible').checked ? 1 : 0);
    data.append('destacado',     document.getElementById('prod_destacado').checked  ? 1 : 0);

    const fileInput = document.getElementById('prod_imagen_file');
    if (fileInput.files.length > 0) data.append('imagen_file', fileInput.files[0]);

    const action = id ? 'admin_actualizar_producto' : 'admin_crear_producto';
    if (id) data.append('id', id);

    // Deshabilitar botón durante el envío
    const btn = document.getElementById('btnGuardarProducto');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    try {
        const res = await callApi(action, 'POST', data);
        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('productoModal')).hide();
            loadSection('productos');
            Swal.fire({ icon: 'success', title: '¡Éxito!', text: res.message, timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.message || 'No se pudo guardar el producto.', 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'Error de red al guardar el producto.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i> Guardar';
    }
}

async function eliminarProducto(id, nombre) {
    const result = await Swal.fire({
        title: '¿Eliminar producto?',
        html: `¿Estás seguro de eliminar <strong>"${escHtml(nombre)}"</strong> permanentemente?<br><small class="text-muted">Esta acción no se puede deshacer.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#7f8c8d',
        confirmButtonText: '<i class="bi bi-trash me-1"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });
    if (!result.isConfirmed) return;
    try {
        const res = await callApi('admin_eliminar_producto', 'POST', { id });
        if (res.ok) {
            loadSection('productos');
            Swal.fire({ icon: 'success', title: 'Eliminado', text: res.message, timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'No se pudo eliminar el producto.', 'error');
    }
}

// Vista previa de imagen al seleccionar archivo
document.addEventListener('change', e => {
    let previewId = null;
    if (e.target.id === 'prod_imagen_file')  previewId = 'prod_preview';
    if (e.target.id === 'menu_imagen_file')  previewId = 'menu_preview';
    if (!previewId) return;
    const preview = document.getElementById(previewId);
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = ev => { preview.src = ev.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(e.target.files[0]);
    } else {
        preview.style.display = 'none';
    }
});

// Vista previa de imagen al escribir URL
document.addEventListener('input', e => {
    if (e.target.id === 'prod_imagen') {
        const preview = document.getElementById('prod_preview');
        const v = e.target.value.trim();
        preview.src = v; preview.style.display = v ? 'block' : 'none';
    }
    if (e.target.id === 'menu_imagen') {
        const preview = document.getElementById('menu_preview');
        const v = e.target.value.trim();
        preview.src = v; preview.style.display = v ? 'block' : 'none';
    }
});

// ════════════════════
//   MENÚ DEL DÍA
// ════════════════════
async function loadMenu(container) {
    const res = await callApi('admin_listar_menu');
    if (!res.ok) {
        container.innerHTML = `<div class="alert alert-danger">Error cargando menús: ${escHtml(res.message)}</div>`;
        return;
    }

    if (res.data.length === 0) {
        container.innerHTML = `
        <div class="text-center py-5">
            <i class="bi bi-calendar-x fs-1 text-muted"></i>
            <p class="mt-3 text-muted">No hay menús registrados aún.</p>
            <button class="btn btn-success btn-action mt-2" onclick="abrirMenuModal()">
                <i class="bi bi-plus-lg me-1"></i> Crear primer menú
            </button>
        </div>`;
        return;
    }

    const rows = res.data.map(m => {
        const comps = [
            m.acompanamiento ? `🍚 ${m.acompanamiento}` : '',
            m.ensalada       ? `🥗 ${m.ensalada}`       : '',
            m.jugo           ? `🧃 ${m.jugo}`           : '',
            m.postre         ? `🍮 ${m.postre}`         : '',
            m.fruta          ? `🍎 ${m.fruta}`          : ''
        ].filter(Boolean).join(' | ');
        return `
        <tr>
            <td><strong>${escHtml(m.fecha)}</strong></td>
            <td>
                <strong>${escHtml(m.plato_nombre)}</strong>
                ${m.plato_desc ? `<br><small class="text-muted">${escHtml(m.plato_desc)}</small>` : ''}
            </td>
            <td><strong>${formatPrecio(m.precio)}</strong></td>
            <td><small class="text-secondary">${comps || '—'}</small></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1 rounded-circle" onclick="editarMenu(${m.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger rounded-circle" onclick="eliminarMenu(${m.id}, '${escHtml(m.plato_nombre)}')" title="Eliminar"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    }).join('');

    container.innerHTML = `
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead><tr>
                <th>Fecha</th><th>Plato Principal</th><th>Precio</th>
                <th>Componentes</th><th style="width:110px">Acciones</th>
            </tr></thead>
            <tbody>${rows}</tbody>
        </table>
    </div>`;
}

function abrirMenuModal(id = null) {
    document.getElementById('menuForm').reset();
    document.getElementById('menu_id').value = '';
    document.getElementById('menu_preview').style.display = 'none';
    document.getElementById('menu_preview').src = '';
    // Poner fecha de hoy por defecto en nuevo menú
    if (!id) {
        document.getElementById('menu_fecha').value = new Date().toISOString().split('T')[0];
    }
    document.getElementById('menuModalTitle').textContent = id ? 'Editar Menú del Día' : 'Nuevo Menú del Día';
    if (id) cargarMenuParaEditar(id);
    new bootstrap.Modal(document.getElementById('menuModal')).show();
}

async function cargarMenuParaEditar(id) {
    try {
        const res = await callApi('admin_listar_menu');
        if (!res.ok) return;
        const m = res.data.find(x => x.id == id);
        if (!m) return;
        document.getElementById('menu_id').value              = m.id;
        document.getElementById('menu_fecha').value           = m.fecha;
        document.getElementById('menu_plato_nombre').value    = m.plato_nombre;
        document.getElementById('menu_plato_desc').value      = m.plato_desc || '';
        document.getElementById('menu_acompanamiento').value  = m.acompanamiento || '';
        document.getElementById('menu_ensalada').value        = m.ensalada || '';
        document.getElementById('menu_jugo').value            = m.jugo || '';
        document.getElementById('menu_postre').value          = m.postre || '';
        document.getElementById('menu_fruta').value           = m.fruta || '';
        document.getElementById('menu_precio').value          = m.precio;
        document.getElementById('menu_disponible_hasta').value = (m.disponible_hasta || '15:00').substring(0,5);
        document.getElementById('menu_imagen').value          = m.imagen || '';
        document.getElementById('menu_imagen_file').value     = '';

        const preview = document.getElementById('menu_preview');
        if (m.imagen_url) { preview.src = m.imagen_url; preview.style.display = 'block'; }
    } catch(e) {
        Swal.fire('Error', 'No se pudieron cargar los datos del menú.', 'error');
    }
}

function editarMenu(id) { abrirMenuModal(id); }

async function guardarMenu() {
    const id          = document.getElementById('menu_id').value;
    const fecha       = document.getElementById('menu_fecha').value;
    const platoNombre = document.getElementById('menu_plato_nombre').value.trim();
    const precio      = parseInt(document.getElementById('menu_precio').value);

    if (!fecha) {
        Swal.fire('Campo requerido', 'La fecha es obligatoria.', 'warning'); return;
    }
    if (!platoNombre) {
        Swal.fire('Campo requerido', 'El nombre del plato es obligatorio.', 'warning'); return;
    }
    if (isNaN(precio) || precio <= 0) {
        Swal.fire('Precio inválido', 'Ingresa un precio válido (mayor a 0).', 'warning'); return;
    }

    const data = new FormData();
    data.append('fecha',             fecha);
    data.append('plato_nombre',      platoNombre);
    data.append('plato_desc',        document.getElementById('menu_plato_desc').value.trim());
    data.append('acompanamiento',    document.getElementById('menu_acompanamiento').value.trim());
    data.append('ensalada',          document.getElementById('menu_ensalada').value.trim());
    data.append('jugo',              document.getElementById('menu_jugo').value.trim());
    data.append('postre',            document.getElementById('menu_postre').value.trim());
    data.append('fruta',             document.getElementById('menu_fruta').value.trim());
    data.append('precio',            precio);
    data.append('disponible_hasta',  document.getElementById('menu_disponible_hasta').value || '15:00');
    data.append('imagen',            document.getElementById('menu_imagen').value.trim());

    const fileInput = document.getElementById('menu_imagen_file');
    if (fileInput.files.length > 0) data.append('imagen_file', fileInput.files[0]);

    const action = id ? 'admin_actualizar_menu' : 'admin_crear_menu';
    if (id) data.append('id', id);

    const btn = document.getElementById('btnGuardarMenu');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    try {
        const res = await callApi(action, 'POST', data);
        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('menuModal')).hide();
            loadSection('menu');
            Swal.fire({ icon: 'success', title: '¡Éxito!', text: res.message, timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.message || 'No se pudo guardar el menú.', 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'Error de red al guardar el menú.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i> Guardar';
    }
}

async function eliminarMenu(id, nombre) {
    const result = await Swal.fire({
        title: '¿Eliminar menú?',
        html: `¿Estás seguro de eliminar el menú <strong>"${escHtml(nombre)}"</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#7f8c8d',
        confirmButtonText: '<i class="bi bi-trash me-1"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });
    if (!result.isConfirmed) return;
    try {
        const res = await callApi('admin_eliminar_menu', 'POST', { id });
        if (res.ok) {
            loadSection('menu');
            Swal.fire({ icon: 'success', title: 'Eliminado', text: res.message, timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'No se pudo eliminar el menú.', 'error');
    }
}

// Botones de guardar de cada modal (event delegation en modal-footer)
document.getElementById('btnGuardarProducto')?.addEventListener('click', guardarProducto);
document.getElementById('btnGuardarMenu')?.addEventListener('click', guardarMenu);
document.getElementById('btnGuardarUsuario')?.addEventListener('click', guardarUsuario);
document.getElementById('btnGuardarReporte')?.addEventListener('click', guardarReporte);
document.getElementById('btnGuardarHistorialPrecio')?.addEventListener('click', guardarHistorialPrecio);

// ════════════════════
//   REPORTES
// ════════════════════
async function loadReportes(container) {
    const res = await callApi('admin_listar_reportes');
    if (!res.ok) {
        container.innerHTML = `<div class="alert alert-danger">Error cargando reportes: ${escHtml(res.message)}</div>`;
        return;
    }

    const rows = res.data.map(r => `
        <tr data-date="${r.created_at.substring(0,10)}">
            <td>${r.id}</td>
            <td><strong>${escHtml(r.producto_nombre)}</strong></td>
            <td class="text-danger">${formatPrecio(r.precio_sitio)}</td>
            <td class="text-success fw-bold">${formatPrecio(r.precio_real)}</td>
            <td><small>${escHtml(r.comentarios || '—')}</small></td>
            <td><span class="badge px-2 py-1 ${r.estado==='pendiente'?'bg-warning text-dark':r.estado==='revisado'?'bg-info text-dark':'bg-success'}">${r.estado}</span></td>
            <td><small>${new Date(r.created_at).toLocaleString('es-CL')}</small></td>
            <td class="no-export">
                ${r.estado==='pendiente' ? `<button class="btn btn-sm btn-outline-success me-1 rounded-circle" onclick="resolverReporte(${r.id},${r.precio_real},'${escHtml(r.producto_nombre)}')" title="Resolver"><i class="bi bi-check-lg"></i></button>` : ''}
                <button class="btn btn-sm btn-outline-primary me-1 rounded-circle" onclick="editarReporte(${r.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger rounded-circle" onclick="eliminarReporte(${r.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`).join('');

    container.innerHTML = `
    <ul class="nav nav-pills mb-4" id="reportesTabs">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#user-reports" type="button">Reportes de Usuarios</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#price-history" type="button" id="tabHistorialBtn">Historial de Precios</button>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="user-reports">
            <div class="row g-2 align-items-center mb-3">
                <div class="col-md-3">
                    <select id="filtroRangoReportes" class="form-select">
                        <option value="todo">Ver todos</option>
                        <option value="dia">Hoy</option>
                        <option value="semana">Esta semana</option>
                        <option value="mes" selected>Este mes</option>
                        <option value="anio">Este año</option>
                    </select>
                </div>
                <div class="col-md-9 d-flex gap-2 justify-content-md-end flex-wrap">
                    <button class="btn btn-outline-danger btn-sm" onclick="exportarReportesPDF()"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</button>
                    <button class="btn btn-outline-success btn-sm" onclick="exportarReportesExcel()"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel</button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="imprimirReportesTable()"><i class="bi bi-printer me-1"></i>Imprimir</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0" id="tablaReportesUsuarios">
                    <thead><tr>
                        <th>ID</th><th>Producto</th><th>Precio Sitio</th><th>Precio Real</th>
                        <th>Comentarios</th><th>Estado</th><th>Fecha</th>
                        <th class="no-export" style="width:130px">Acciones</th>
                    </tr></thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="price-history">
            <div id="priceHistoryContainer">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>`;

    document.getElementById('filtroRangoReportes').addEventListener('change', filtrarRangoReportes);
    setTimeout(filtrarRangoReportes, 20);

    // Cargar historial cuando se hace clic en esa pestaña
    document.getElementById('tabHistorialBtn')?.addEventListener('click', loadHistorialPreciosTab);
}

function filtrarRangoReportes() {
    const range = document.getElementById('filtroRangoReportes')?.value;
    if (!range) return;
    const hoy = new Date();
    document.querySelectorAll('#tablaReportesUsuarios tbody tr').forEach(row => {
        const d = new Date(row.dataset.date);
        let match = false;
        if (range === 'todo') match = true;
        else if (range === 'dia') match = d.toDateString() === hoy.toDateString();
        else if (range === 'semana') {
            const mon = new Date(hoy); mon.setDate(hoy.getDate() - ((hoy.getDay()+6)%7)); mon.setHours(0,0,0,0);
            match = d >= mon && d <= hoy;
        }
        else if (range === 'mes')  match = d.getMonth()    === hoy.getMonth()    && d.getFullYear() === hoy.getFullYear();
        else if (range === 'anio') match = d.getFullYear() === hoy.getFullYear();
        row.style.display = match ? '' : 'none';
    });
}

function exportarReportesPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.text('Casino Universitario - Reportes de Precios', 14, 15);
    const table   = document.getElementById('tablaReportesUsuarios');
    const headers = [];
    table.querySelectorAll('thead th').forEach((th, i) => { if (!th.classList.contains('no-export')) headers.push(th.innerText); });
    const data = [];
    table.querySelectorAll('tbody tr').forEach(tr => {
        if (tr.style.display === 'none') return;
        const row = [];
        tr.querySelectorAll('td').forEach((td, i) => { if (!td.classList.contains('no-export')) row.push(td.innerText); });
        data.push(row);
    });
    doc.autoTable({ head: [headers], body: data, startY: 20, theme: 'striped', headStyles: { fillColor: [160,64,0] } });
    doc.save('reportes_precios.pdf');
}

function exportarReportesExcel() {
    const table = document.getElementById('tablaReportesUsuarios');
    const rows  = [];
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => { if (!th.classList.contains('no-export')) headers.push(th.innerText); });
    rows.push(headers);
    table.querySelectorAll('tbody tr').forEach(tr => {
        if (tr.style.display === 'none') return;
        const row = [];
        tr.querySelectorAll('td').forEach(td => { if (!td.classList.contains('no-export')) row.push(td.innerText); });
        rows.push(row);
    });
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(rows), 'Reportes');
    XLSX.writeFile(wb, 'reportes_precios.xlsx');
}

function imprimirReportesTable() {
    const el   = document.getElementById('tablaReportesUsuarios');
    const win  = window.open('', '', 'height=700,width=900');
    const clone = el.cloneNode(true);
    clone.querySelectorAll('.no-export').forEach(e => e.remove());
    win.document.write(`<html><head><title>Reportes</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>body{padding:20px}</style></head>
        <body><h3 class="mb-4">Reportes de Precios de Usuarios</h3>${clone.outerHTML}</body></html>`);
    win.document.close();
    setTimeout(() => { win.print(); win.close(); }, 500);
}

async function editarReporte(id) {
    try {
        const res = await callApi('admin_listar_reportes');
        if (!res.ok) return;
        const rep = res.data.find(r => r.id == id);
        if (!rep) return;
        document.getElementById('rep_id').value              = rep.id;
        document.getElementById('rep_producto_nombre').value = rep.producto_nombre;
        document.getElementById('rep_precio_sitio').value    = rep.precio_sitio;
        document.getElementById('rep_precio_real').value     = rep.precio_real;
        document.getElementById('rep_comentarios').value     = rep.comentarios || '';
        document.getElementById('rep_estado').value          = rep.estado;
        new bootstrap.Modal(document.getElementById('reporteModal')).show();
    } catch(e) {
        Swal.fire('Error', 'No se pudieron cargar los datos del reporte.', 'error');
    }
}

async function guardarReporte() {
    const id          = document.getElementById('rep_id').value;
    const prodNombre  = document.getElementById('rep_producto_nombre').value.trim();
    const precioSitio = parseInt(document.getElementById('rep_precio_sitio').value);
    const precioReal  = parseInt(document.getElementById('rep_precio_real').value);

    if (!prodNombre || isNaN(precioSitio) || isNaN(precioReal)) {
        Swal.fire('Formulario incompleto', 'El nombre y ambos precios son obligatorios.', 'warning'); return;
    }

    const data = {
        id:              parseInt(id),
        producto_nombre: prodNombre,
        precio_sitio:    precioSitio,
        precio_real:     precioReal,
        comentarios:     document.getElementById('rep_comentarios').value.trim(),
        estado:          document.getElementById('rep_estado').value
    };

    try {
        const res = await callApi('admin_actualizar_reporte_completo', 'POST', data);
        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('reporteModal')).hide();
            loadSection('reportes');
            Swal.fire({ icon: 'success', title: '¡Éxito!', text: res.message, timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'No se pudo guardar el reporte.', 'error');
    }
}

async function eliminarReporte(id) {
    const c = await Swal.fire({
        title: '¿Eliminar reporte?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#e74c3c', confirmButtonText: 'Sí, eliminar'
    });
    if (!c.isConfirmed) return;
    try {
        const res = await callApi('admin_eliminar_reporte', 'POST', { id });
        if (res.ok) {
            loadSection('reportes');
            Swal.fire({ icon: 'success', title: 'Eliminado', text: res.message, timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'No se pudo eliminar el reporte.', 'error');
    }
}

async function resolverReporte(id, precioReal, prodNombre) {
    const c = await Swal.fire({
        title: 'Resolver Reporte',
        html: `¿Deseas resolver este reporte y actualizar el precio de <strong>${escHtml(prodNombre)}</strong> a ${formatPrecio(precioReal)}?`,
        icon: 'question', showCancelButton: true, showDenyButton: true,
        confirmButtonText: 'Resolver y actualizar precio',
        denyButtonText: 'Solo resolver', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#27ae60', denyButtonColor: '#2980b9'
    });
    if (c.isDismissed) return;
    try {
        if (c.isConfirmed) {
            const prodRes = await callApi('admin_listar_productos');
            if (prodRes.ok) {
                const prod = prodRes.data.find(p => p.nombre.toLowerCase().trim() === prodNombre.toLowerCase().trim());
                if (prod) {
                    await callApi('admin_crear_historial_precio', 'POST', {
                        producto_id: prod.id, precio_nuevo: precioReal,
                        motivo: `Resolución de reporte #${id}`
                    });
                }
            }
        }
        const res = await callApi('admin_marcar_reporte', 'POST', { id, estado: 'resuelto' });
        if (res.ok) {
            loadSection('reportes');
            Swal.fire({ icon: 'success', title: '¡Resuelto!', text: 'El reporte fue resuelto correctamente.', timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'No se pudo resolver el reporte.', 'error');
    }
}

async function loadHistorialPreciosTab() {
    const container = document.getElementById('priceHistoryContainer');
    if (!container) return;
    container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    try {
        const res = await callApi('admin_listar_historial_precios');
        if (!res.ok) {
            container.innerHTML = `<div class="alert alert-danger">Error: ${escHtml(res.message)}</div>`; return;
        }
        const rows = res.data.map(h => `
            <tr>
                <td>${h.id}</td>
                <td><strong>${escHtml(h.producto_nombre)}</strong></td>
                <td class="text-secondary">${formatPrecio(h.precio_anterior)}</td>
                <td class="text-success fw-bold">${formatPrecio(h.precio_nuevo)}</td>
                <td><small>${escHtml(h.admin_nombre || 'Sistema')}</small></td>
                <td><small>${escHtml(h.motivo || '—')}</small></td>
                <td><small>${new Date(h.created_at).toLocaleString('es-CL')}</small></td>
                <td><button class="btn btn-sm btn-outline-danger rounded-circle" onclick="eliminarHistorialPrecio(${h.id})"><i class="bi bi-trash"></i></button></td>
            </tr>`).join('');
        container.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-muted"><i class="bi bi-clock-history me-1"></i> Historial de Cambios</h6>
            <button class="btn btn-warning btn-sm text-dark fw-bold" onclick="abrirHistorialPrecioModal()">
                <i class="bi bi-plus-lg me-1"></i> Registrar cambio manual
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0" id="tablaHistorialPrecios">
                <thead><tr>
                    <th>ID</th><th>Producto</th><th>Precio Anterior</th><th>Precio Nuevo</th>
                    <th>Admin</th><th>Motivo</th><th>Fecha</th><th>Acción</th>
                </tr></thead>
                <tbody>${rows || '<tr><td colspan="8" class="text-center text-muted py-4">Sin registros de historial.</td></tr>'}</tbody>
            </table>
        </div>`;
    } catch(e) {
        container.innerHTML = `<div class="alert alert-danger">Error cargando historial: ${escHtml(e.message)}</div>`;
    }
}

async function abrirHistorialPrecioModal() {
    document.getElementById('historialPrecioForm').reset();
    try {
        const res = await callApi('admin_listar_productos');
        const sel = document.getElementById('hp_producto_id');
        if (res.ok && sel) {
            sel.innerHTML = res.data.map(p =>
                `<option value="${p.id}">${escHtml(p.nombre)} (${formatPrecio(p.precio)})</option>`
            ).join('');
        }
    } catch(e) {}
    new bootstrap.Modal(document.getElementById('historialPrecioModal')).show();
}

async function guardarHistorialPrecio() {
    const productoId = parseInt(document.getElementById('hp_producto_id').value);
    const precioNuevo = parseInt(document.getElementById('hp_precio_nuevo').value);
    const motivo = document.getElementById('hp_motivo').value.trim();

    if (isNaN(productoId) || isNaN(precioNuevo) || precioNuevo <= 0 || !motivo) {
        Swal.fire('Campos inválidos', 'Todos los campos son obligatorios.', 'warning'); return;
    }
    try {
        const res = await callApi('admin_crear_historial_precio', 'POST', {
            producto_id: productoId, precio_nuevo: precioNuevo, motivo
        });
        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('historialPrecioModal')).hide();
            loadHistorialPreciosTab();
            Swal.fire({ icon: 'success', title: '¡Éxito!', text: res.message, timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'No se pudo registrar la variación.', 'error');
    }
}

async function eliminarHistorialPrecio(id) {
    const c = await Swal.fire({
        title: '¿Eliminar registro?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#e74c3c', confirmButtonText: 'Sí, eliminar'
    });
    if (!c.isConfirmed) return;
    try {
        const res = await callApi('admin_eliminar_historial_precio', 'POST', { id });
        if (res.ok) {
            loadHistorialPreciosTab();
            Swal.fire({ icon: 'success', title: 'Eliminado', text: res.message, timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
    }
}

// ════════════════════
//   USUARIOS
// ════════════════════
async function loadUsuarios(container) {
    const res = await callApi('admin_listar_usuarios');
    if (!res.ok) {
        container.innerHTML = `<div class="alert alert-danger">Error cargando usuarios: ${escHtml(res.message)}</div>`; return;
    }
    const rows = res.data.map(u => `
        <tr>
            <td>${u.id}</td>
            <td><strong>${escHtml(u.nombre)}</strong></td>
            <td><code>${escHtml(u.username)}</code></td>
            <td>${escHtml(u.email || '—')}</td>
            <td>${u.activo
                ? '<span class="badge bg-light text-success border border-success border-opacity-25 px-2 py-1">Activo</span>'
                : '<span class="badge bg-light text-secondary border px-2 py-1">Inactivo</span>'}</td>
            <td><small>${u.ultimo_login ? new Date(u.ultimo_login).toLocaleString('es-CL') : 'Nunca'}</small></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1 rounded-circle" onclick="editarUsuario(${u.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger rounded-circle" onclick="eliminarUsuario(${u.id}, '${escHtml(u.nombre)}')" title="Eliminar"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`).join('');
    container.innerHTML = `
    <div class="mb-3 col-md-4">
        <input type="text" id="busquedaUsuario" class="form-control" placeholder="🔍 Buscar por nombre o usuario...">
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0" id="tablaUsuarios">
            <thead><tr>
                <th>ID</th><th>Nombre</th><th>Usuario</th><th>Email</th>
                <th>Estado</th><th>Último Acceso</th><th style="width:100px">Acciones</th>
            </tr></thead>
            <tbody>${rows}</tbody>
        </table>
    </div>`;
    document.getElementById('busquedaUsuario').addEventListener('input', () => {
        const term = document.getElementById('busquedaUsuario').value.toLowerCase();
        document.querySelectorAll('#tablaUsuarios tbody tr').forEach(r => {
            const n = (r.cells[1]?.textContent + r.cells[2]?.textContent).toLowerCase();
            r.style.display = n.includes(term) ? '' : 'none';
        });
    });
}

function abrirUsuarioModal(id = null) {
    document.getElementById('usuarioForm').reset();
    document.getElementById('us_id').value = '';
    document.getElementById('usuarioModalTitle').textContent = id ? 'Editar Administrador' : 'Nuevo Administrador';
    document.getElementById('us_pass_required').style.display = id ? 'none' : 'inline';
    document.getElementById('us_pass_help').style.display     = id ? 'block' : 'none';
    if (id) cargarUsuarioParaEditar(id);
    new bootstrap.Modal(document.getElementById('usuarioModal')).show();
}

async function cargarUsuarioParaEditar(id) {
    try {
        const res = await callApi('admin_listar_usuarios');
        if (!res.ok) return;
        const u = res.data.find(x => x.id == id);
        if (!u) return;
        document.getElementById('us_id').value       = u.id;
        document.getElementById('us_nombre').value   = u.nombre;
        document.getElementById('us_username').value = u.username;
        document.getElementById('us_email').value    = u.email || '';
        document.getElementById('us_activo').checked = u.activo == 1;
    } catch(e) {}
}

function editarUsuario(id) { abrirUsuarioModal(id); }

async function guardarUsuario() {
    const id       = document.getElementById('us_id').value;
    const nombre   = document.getElementById('us_nombre').value.trim();
    const username = document.getElementById('us_username').value.trim();
    const email    = document.getElementById('us_email').value.trim();
    const password = document.getElementById('us_password').value;

    if (!nombre || !username || !email) {
        Swal.fire('Campos obligatorios', 'Nombre, usuario y email son requeridos.', 'warning'); return;
    }
    if (!id && !password) {
        Swal.fire('Contraseña requerida', 'Debes ingresar una contraseña para el nuevo usuario.', 'warning'); return;
    }
    if (password && password.length < 6) {
        Swal.fire('Contraseña corta', 'La contraseña debe tener al menos 6 caracteres.', 'warning'); return;
    }

    const data = { nombre, username, email, password, activo: document.getElementById('us_activo').checked ? 1 : 0 };
    const action = id ? 'admin_actualizar_usuario' : 'admin_crear_usuario';
    if (id) data.id = parseInt(id);

    try {
        const res = await callApi(action, 'POST', data);
        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('usuarioModal')).hide();
            loadSection('usuarios');
            Swal.fire({ icon: 'success', title: '¡Éxito!', text: res.message, timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'No se pudo guardar el usuario.', 'error');
    }
}

async function eliminarUsuario(id, nombre) {
    const c = await Swal.fire({
        title: '¿Eliminar administrador?',
        html: `¿Estás seguro de eliminar a <strong>${escHtml(nombre)}</strong>?`,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#e74c3c', confirmButtonText: 'Sí, eliminar'
    });
    if (!c.isConfirmed) return;
    try {
        const res = await callApi('admin_eliminar_usuario', 'POST', { id });
        if (res.ok) {
            loadSection('usuarios');
            Swal.fire({ icon: 'success', title: 'Eliminado', text: res.message, timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'No se pudo eliminar el usuario.', 'error');
    }
}

// ════════════════════
//   CONFIGURACIÓN
// ════════════════════
async function loadConfiguracion(container) {
    try {
        const res = await callApi('admin_obtener_config');
        if (!res.ok) {
            container.innerHTML = `<div class="alert alert-danger">Error: ${escHtml(res.message)}</div>`; return;
        }
        const c = res.data;
        container.innerHTML = `
        <div class="card border-0 shadow-sm p-4 rounded-4 col-lg-8 mx-auto">
            <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-gear-fill me-1"></i> Configuración del Sitio</h5>
            <form id="configForm">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre del Casino</label>
                    <input type="text" id="conf_site_name" class="form-control" value="${escHtml(c.site_name)}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">URL del Sitio</label>
                    <input type="url" id="conf_site_url" class="form-control" value="${escHtml(c.site_url)}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email de Contacto</label>
                    <input type="email" id="conf_contact_email" class="form-control" value="${escHtml(c.contact_email)}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Horario de Atención</label>
                    <input type="text" id="conf_open_hours" class="form-control" value="${escHtml(c.open_hours)}" required>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="conf_maintenance_mode" ${c.maintenance_mode ? 'checked' : ''}>
                    <label class="form-check-label fw-bold" for="conf_maintenance_mode">Modo Mantenimiento</label>
                </div>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" id="conf_enable_reports" ${c.enable_reports ? 'checked' : ''}>
                    <label class="form-check-label fw-bold" for="conf_enable_reports">Permitir Reportes de Precios de Usuarios</label>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-primary btn-action px-5" id="btnGuardarConfig">
                        <i class="bi bi-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>`;
        document.getElementById('btnGuardarConfig').addEventListener('click', guardarConfiguracion);
    } catch(e) {
        container.innerHTML = `<div class="alert alert-danger">Error cargando configuración.</div>`;
    }
}

async function guardarConfiguracion() {
    const data = {
        site_name:        document.getElementById('conf_site_name').value.trim(),
        site_url:         document.getElementById('conf_site_url').value.trim(),
        contact_email:    document.getElementById('conf_contact_email').value.trim(),
        open_hours:       document.getElementById('conf_open_hours').value.trim(),
        maintenance_mode: document.getElementById('conf_maintenance_mode').checked,
        enable_reports:   document.getElementById('conf_enable_reports').checked
    };
    if (!data.site_name || !data.site_url) {
        Swal.fire('Campos requeridos', 'El nombre y URL del sitio son obligatorios.', 'warning'); return;
    }
    try {
        const res = await callApi('admin_guardar_config', 'POST', data);
        if (res.ok) {
            Swal.fire({ icon: 'success', title: 'Configuración Guardada', text: res.message, timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'No se pudo guardar la configuración.', 'error');
    }
}

// ════════════════════
//   ARRANQUE
// ════════════════════
loadCategorias();

// Soporte para deep-link vía hash
let startupSection = 'dashboard';
if (location.hash) {
    const hash = location.hash.replace('#', '');
    if (['dashboard','productos','menu','reportes','usuarios','configuracion'].includes(hash)) {
        startupSection = hash;
        document.querySelectorAll('.sidebar .nav-link').forEach(l => {
            l.classList.toggle('active', l.dataset.section === hash);
        });
        document.getElementById('sectionTitle').textContent = hash.charAt(0).toUpperCase() + hash.slice(1);
        updateActionBtn(hash);
    }
}
loadSection(startupSection);
</script>
</body>
</html>
