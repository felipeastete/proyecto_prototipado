<?php
// ============================================================
//   PANEL ADMINISTRADOR - INDEX (DASHBOARD PROFESIONAL)
//   Archivo: admin/index.php
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
    <title>Panel Admin | Casino Universitario</title>
    <link href="../style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        :root {
            --admin-primary: #a04000;
            --admin-accent: #d35400;
            --admin-bg: #fcfaf7;
            --admin-sidebar-bg: #2c3e50;
            --admin-card-bg: #ffffff;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--admin-bg); padding-top: 86px; }
        .navbar-admin {
            background: var(--admin-primary) !important;
            box-shadow: 0 4px 12px rgba(44,62,80,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 0.85rem 1.5rem;
        }
        .sidebar {
            position: fixed;
            top: 72px;
            bottom: 0;
            left: 0;
            width: 260px;
            background: var(--admin-sidebar-bg);
            padding: 2rem 0;
            overflow-y: auto;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(0,0,0,0.05);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            font-weight: 600;
            padding: 0.85rem 1.8rem;
            border-radius: 0;
            transition: all 0.2s;
            border-left: 4px solid transparent;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
        }
        .sidebar .nav-link:hover {
            color: #ffffff;
            background: rgba(255,255,255,0.05);
        }
        .sidebar .nav-link.active {
            background: rgba(211,84,0,0.15);
            color: #ffffff;
            border-left: 4px solid var(--admin-accent);
            font-weight: 700;
        }
        .main-content { margin-left: 260px; padding: 2rem; }
        .card-stats {
            border: 1px solid rgba(211,84,0,0.08) !important;
            border-radius: 20px !important;
            box-shadow: 0 10px 25px -5px rgba(211,84,0,0.04);
            background: var(--admin-card-bg);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card-stats:hover { transform: translateY(-4px); box-shadow: 0 15px 30px -5px rgba(211,84,0,0.08); }
        .btn-action { border-radius: 100px !important; font-weight: 700; padding: 0.65rem 1.8rem !important; transition: all 0.2s; }
        .btn-action:hover { transform: translateY(-1px); }
        
        .table-custom {
            background: var(--admin-card-bg);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(211,84,0,0.03);
            border: 1px solid rgba(211,84,0,0.05) !important;
        }
        .table-custom th { background: rgba(211, 84, 0, 0.05); font-weight: 700; color: var(--admin-primary); padding: 1.15rem 1rem; border-bottom: 2px solid rgba(211,84,0,0.1); }
        .table-custom td { padding: 1.15rem 1rem; }
        
        .modal-content { border-radius: 24px !important; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.15); }
        .modal-header { border-bottom: 1px solid rgba(211,84,0,0.08); }
        .modal-footer { border-top: 1px solid rgba(211,84,0,0.08); }
        
        .chart-container {
            position: relative;
            background: var(--admin-card-bg);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid rgba(211,84,0,0.06);
            box-shadow: 0 10px 25px -5px rgba(211,84,0,0.03);
            margin-bottom: 1.5rem;
            height: 340px;
        }
        
        .restock-btn {
            transition: all 0.2s;
        }
        .restock-btn:hover {
            transform: scale(1.05);
        }
        
        @media (max-width: 768px) {
            .sidebar { width: 100%; position: relative; top: 0; height: auto; padding: 1rem 0; box-shadow: none; }
            .main-content { margin-left: 0; padding: 1.5rem 1rem; }
            body { padding-top: 72px; }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-admin navbar-dark fixed-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#"><i class="bi bi-fork-knife"></i> Casino Admin</a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-white-50 small d-none d-sm-inline"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($adminNombre) ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
        </div>
    </div>
</nav>

<div class="sidebar">
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link active" href="#" data-section="dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-section="productos"><i class="bi bi-box-seam me-2"></i>Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-section="menu"><i class="bi bi-calendar-week me-2"></i>Menú del Día</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-section="reportes"><i class="bi bi-flag me-2"></i>Reportes de precio</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-section="usuarios"><i class="bi bi-people me-2"></i>Usuarios</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-section="configuracion"><i class="bi bi-gear me-2"></i>Configuración</a></li>
        <li class="nav-item"><a class="nav-link" href="../" target="_blank"><i class="bi bi-eye me-2"></i>Ver sitio</a></li>
    </ul>
</div>

<div class="main-content" id="mainContent">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3 class="fw-bold mb-0" id="sectionTitle">Dashboard</h3>
        <button class="btn btn-primary btn-action" id="actionBtn" style="display:none;"><i class="bi bi-plus-lg"></i> Nuevo</button>
    </div>
    <div id="dynamicView">
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div> Cargando...
        </div>
    </div>
</div>

<!-- Modal Producto -->
<div class="modal fade" id="productoModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-box me-1"></i> <span id="productoModalTitle">Nuevo Producto</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="productoForm">
                    <input type="hidden" id="prod_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Categoría *</label>
                            <select id="prod_categoria" class="form-select" required></select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre *</label>
                            <input type="text" id="prod_nombre" class="form-control" placeholder="Ej: Barros Luco" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea id="prod_descripcion" class="form-control" rows="2" placeholder="Detalle del producto..."></textarea>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Precio ($) *</label>
                            <input type="number" id="prod_precio" class="form-control" placeholder="Ej: 3500" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Stock</label>
                            <input type="number" id="prod_stock" class="form-control" value="50" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">URL Imagen / Subir</label>
                            <input type="text" id="prod_imagen" class="form-control mb-2" placeholder="URL existente (opcional)">
                            <input type="file" id="prod_imagen_file" class="form-control" accept="image/*" onchange="previewImage(this, 'prod_preview')">
                            <img id="prod_preview" src="" style="max-height: 100px; display: none;" class="mt-2 rounded">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Advertencias (separar por comas)</label>
                            <input type="text" id="prod_advertencias" class="form-control" placeholder="Ej: Alto en calorías, Alto en sodio">
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input type="checkbox" id="prod_disponible" class="form-check-input" checked>
                                <label class="form-check-label fw-semibold">Disponible</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input type="checkbox" id="prod_destacado" class="form-check-input">
                                <label class="form-check-label fw-semibold">Destacado</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarProducto()">Guardar</button>
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
                <form id="menuForm">
                    <input type="hidden" id="menu_id">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Fecha *</label>
                            <input type="date" id="menu_fecha" class="form-control" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Plato principal *</label>
                            <input type="text" id="menu_plato_nombre" class="form-control" placeholder="Ej: Pastel de Choclo" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea id="menu_plato_desc" class="form-control" rows="2" placeholder="Detalle del plato..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Acompañamiento</label>
                            <input type="text" id="menu_acompanamiento" class="form-control" placeholder="Ej: Arroz, Papas fritas">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ensalada</label>
                            <input type="text" id="menu_ensalada" class="form-control" placeholder="Ej: Tomate, Repollo">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Jugo</label>
                            <input type="text" id="menu_jugo" class="form-control" placeholder="Ej: Jugo de piña">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Postre</label>
                            <input type="text" id="menu_postre" class="form-control" placeholder="Ej: Flan casero">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Fruta</label>
                            <input type="text" id="menu_fruta" class="form-control" placeholder="Ej: Manzana, Plátano">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Precio ($) *</label>
                            <input type="number" id="menu_precio" class="form-control" placeholder="Ej: 4200" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Disponible hasta</label>
                            <input type="time" id="menu_disponible_hasta" class="form-control" value="15:00">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">URL Imagen / Subir</label>
                            <input type="text" id="menu_imagen" class="form-control mb-2" placeholder="URL existente (opcional)">
                            <input type="file" id="menu_imagen_file" class="form-control" accept="image/*" onchange="previewImage(this, 'menu_preview')">
                            <img id="menu_preview" src="" style="max-height: 100px; display: none;" class="mt-2 rounded">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarMenu()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Usuario -->
<div class="modal fade" id="usuarioModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person me-1"></i> <span id="usuarioModalTitle">Nuevo Usuario</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="usuarioForm">
                    <input type="hidden" id="us_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre Completo *</label>
                        <input type="text" id="us_nombre" class="form-control" placeholder="Ej: Juan Pérez" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Usuario *</label>
                        <input type="text" id="us_username" class="form-control" placeholder="Ej: jperez" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email *</label>
                        <input type="email" id="us_email" class="form-control" placeholder="Ej: juan.perez@casino.cl" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Contraseña <span id="us_pass_required" class="text-danger">*</span></label>
                        <input type="password" id="us_password" class="form-control" placeholder="Mínimo 6 caracteres">
                        <small class="text-muted d-block mt-1" id="us_pass_help" style="display:none;">Deja en blanco para conservar la contraseña actual.</small>
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" id="us_activo" class="form-check-input" checked>
                        <label class="form-check-label fw-semibold">Activo</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark" onclick="guardarUsuario()">Guardar</button>
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
                <form id="reporteForm">
                    <input type="hidden" id="rep_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Producto *</label>
                        <input type="text" id="rep_producto_nombre" class="form-control" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Precio Sitio *</label>
                            <input type="number" id="rep_precio_sitio" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Precio Real *</label>
                            <input type="number" id="rep_precio_real" class="form-control" required>
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
                <button type="button" class="btn btn-danger" onclick="guardarReporte()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Historial Precio (Manual) -->
<div class="modal fade" id="historialPrecioModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-currency-dollar me-1"></i> Registrar Cambio de Precio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="historialPrecioForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Producto *</label>
                        <select id="hp_producto_id" class="form-select" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nuevo Precio ($) *</label>
                        <input type="number" id="hp_precio_nuevo" class="form-control" placeholder="Ej: 3800" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivo / Razón *</label>
                        <input type="text" id="hp_motivo" class="form-control" placeholder="Ej: Alza de costos de insumos" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning text-dark fw-bold" onclick="guardarHistorialPrecio()">Actualizar Precio</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const API_BASE = '../api/api.php';
let currentSection = 'dashboard';
let categoriasList = [];

async function callApi(action, method = 'GET', body = null) {
    const url = `${API_BASE}?action=${action}`;
    let opts = { method };
    if (body) {
        if (body instanceof FormData) {
            opts.body = body;
        } else {
            opts.headers = { 'Content-Type': 'application/json' };
            opts.body = JSON.stringify(body);
        }
    }
    const res = await fetch(url, opts);
    return res.json();
}

async function loadCategorias() {
    try {
        const res = await callApi('categorias');
        if (res.ok) categoriasList = res.data;
        const sel = document.getElementById('prod_categoria');
        if (sel) sel.innerHTML = categoriasList.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
    } catch (e) {
        console.error('Error cargando categorías', e);
    }
}

document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
        link.classList.add('active');
        currentSection = link.dataset.section;
        document.getElementById('sectionTitle').innerText = link.innerText.trim();
        const actionBtn = document.getElementById('actionBtn');
        
        if (currentSection === 'productos') { 
            actionBtn.style.display = 'block'; 
            actionBtn.onclick = () => abrirProductoModal(); 
            actionBtn.innerHTML = '<i class="bi bi-plus-lg"></i> Nuevo Producto'; 
            actionBtn.className = 'btn btn-primary btn-action';
        }
        else if (currentSection === 'menu') { 
            actionBtn.style.display = 'block'; 
            actionBtn.onclick = () => abrirMenuModal(); 
            actionBtn.innerHTML = '<i class="bi bi-plus-lg"></i> Nuevo Menú'; 
            actionBtn.className = 'btn btn-success btn-action';
        }
        else if (currentSection === 'usuarios') { 
            actionBtn.style.display = 'block'; 
            actionBtn.onclick = () => abrirUsuarioModal(); 
            actionBtn.innerHTML = '<i class="bi bi-plus-lg"></i> Nuevo Usuario'; 
            actionBtn.className = 'btn btn-dark btn-action';
        }
        else {
            actionBtn.style.display = 'none';
        }
        loadSection(currentSection);
    });
});

async function loadSection(section) {
    const container = document.getElementById('dynamicView');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div> Cargando...</div>';
    try {
        if (section === 'dashboard') await loadDashboard(container);
        else if (section === 'productos') await loadProductos(container);
        else if (section === 'menu') await loadMenu(container);
        else if (section === 'reportes') await loadReportes(container);
        else if (section === 'usuarios') await loadUsuarios(container);
        else if (section === 'configuracion') await loadConfiguracion(container);
    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Error al cargar sección: ${escHtml(err.message)}</div>`;
    }
}

async function loadDashboard(container) {
    // Llamadas API paralelas para optimizar rendimiento
    const [prodRes, menuRes, reportRes] = await Promise.all([
        callApi('admin_listar_productos'),
        callApi('admin_listar_menu'),
        callApi('admin_listar_reportes')
    ]);

    const totalProd = prodRes.ok ? prodRes.data.length : 0;
    const totalMenu = menuRes.ok ? menuRes.data.length : 0;
    const reportPend = reportRes.ok ? reportRes.data.filter(r => r.estado === 'pendiente').length : 0;

    // Detectar alertas críticas de stock
    const outOfStockProds = prodRes.ok ? prodRes.data.filter(p => p.stock === 0) : [];
    const lowStockProds = prodRes.ok ? prodRes.data.filter(p => p.stock > 0 && p.stock <= 5) : [];
    const criticalProds = [...outOfStockProds, ...lowStockProds];

    // Procesar datos para gráficos
    const catCounts = {};
    const catStock = {};
    if (prodRes.ok) {
        prodRes.data.forEach(p => {
            catCounts[p.categoria_nombre] = (catCounts[p.categoria_nombre] || 0) + 1;
            catStock[p.categoria_nombre] = (catStock[p.categoria_nombre] || 0) + p.stock;
        });
    }

    let statsHtml = `
    <!-- Tarjetas de estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-stats border-0 p-3">
                <div class="d-flex align-items-center">
                    <div class="p-3 rounded-4 me-3" style="background: rgba(211,84,0,0.1); color: #d35400;">
                        <i class="bi bi-box-seam fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 fw-bold">Productos</h6>
                        <h3 class="mb-0 fw-bold">${totalProd}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stats border-0 p-3">
                <div class="d-flex align-items-center">
                    <div class="p-3 rounded-4 me-3" style="background: rgba(39,174,96,0.1); color: #27ae60;">
                        <i class="bi bi-calendar-week fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 fw-bold">Menús Registrados</h6>
                        <h3 class="mb-0 fw-bold">${totalMenu}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stats border-0 p-3">
                <div class="d-flex align-items-center">
                    <div class="p-3 rounded-4 me-3" style="background: rgba(231,76,60,0.1); color: #e74c3c;">
                        <i class="bi bi-flag fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 fw-bold">Reportes Pendientes</h6>
                        <h3 class="mb-0 fw-bold">${reportPend}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Gráficos -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="chart-container">
                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-pie-chart-fill me-2"></i>Productos por Categoría</h6>
                <canvas id="chartCategorias"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-container">
                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-bar-chart-line-fill me-2"></i>Nivel de Stock por Categoría</h6>
                <canvas id="chartStock"></canvas>
            </div>
        </div>
    </div>
    `;

    // Widget de Reposición Rápida
    let restockHtml = '';
    if (criticalProds.length > 0) {
        restockHtml = `
        <div class="card border-0 shadow-sm p-4 rounded-4 mt-4">
            <h5 class="fw-bold mb-3 text-danger d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i> Reposición Rápida de Inventario
            </h5>
            <p class="small text-muted mb-3">Productos con quiebre o niveles críticos de stock.</p>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr class="text-muted">
                            <th>Producto</th>
                            <th>Stock Actual</th>
                            <th>Agregar Unidades</th>
                        </tr>
                    </thead>
                    <tbody>`;
        criticalProds.forEach(p => {
            restockHtml += `
            <tr>
                <td>
                    <strong class="text-dark">${escHtml(p.nombre)}</strong>
                    <br><span class="badge bg-light text-secondary mt-1">${escHtml(p.categoria_nombre)}</span>
                </td>
                <td>
                    <span class="badge ${p.stock === 0 ? 'bg-danger' : 'bg-warning'} px-3 py-2">
                        ${p.stock === 0 ? 'Agotado' : 'Solo ' + p.stock + ' uds'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-success restock-btn rounded-pill px-3 me-2" onclick="restockRapido(${p.id}, 10)"><i class="bi bi-plus-lg"></i> +10</button>
                    <button class="btn btn-sm btn-outline-success restock-btn rounded-pill px-3" onclick="restockRapido(${p.id}, 50)"><i class="bi bi-plus-lg"></i> +50</button>
                </td>
            </tr>`;
        });
        restockHtml += `
                    </tbody>
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

    container.innerHTML = statsHtml + restockHtml;

    // Renderizar Gráficos dinámicos con Chart.js
    setTimeout(() => {
        const ctxCat = document.getElementById('chartCategorias')?.getContext('2d');
        if (ctxCat && Object.keys(catCounts).length > 0) {
            new Chart(ctxCat, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(catCounts),
                    datasets: [{
                        data: Object.values(catCounts),
                        backgroundColor: ['#d35400', '#f39c12', '#27ae60', '#2980b9', '#8e44ad', '#2c3e50', '#16a085', '#c0392b', '#7f8c8d'],
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
                });
            }

            const ctxStock = document.getElementById('chartStock')?.getContext('2d');
            if (ctxStock && Object.keys(catStock).length > 0) {
                new Chart(ctxStock, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(catStock),
                        datasets: [{
                            label: 'Stock Total',
                            data: Object.values(catStock),
                            backgroundColor: 'rgba(211, 84, 0, 0.75)',
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

async function restockRapido(id, cantidad) {
    try {
        const res = await callApi('admin_restock_producto', 'POST', { id, cantidad });
        if (res.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Inventario Actualizado',
                text: res.message,
                timer: 2000,
                showConfirmButton: false
            });
            loadSection(currentSection);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: res.message
            });
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error de Red',
            text: 'No se pudo registrar la reposición rápida.'
        });
    }
}

async function loadProductos(container) {
    const res = await callApi('admin_listar_productos');
    if (!res.ok) { 
        container.innerHTML = `<div class="alert alert-danger">Error cargando productos: ${escHtml(res.message)}</div>`; 
        return; 
    }
    
    const catsOptions = categoriasList.map(c => `<option value="${escHtml(c.nombre)}">${escHtml(c.nombre)}</option>`).join('');
    
    let html = `
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" id="busquedaProducto" class="form-control" placeholder="Buscar por nombre..." onkeyup="filtrarTablaProductos()">
        </div>
        <div class="col-md-4">
            <select id="filtroCategoria" class="form-select" onchange="filtrarTablaProductos()">
                <option value="">Todas las categorías</option>
                ${catsOptions}
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0" id="tablaProductos">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th style="width:160px">Acciones</th>
                </tr>
            </thead>
            <tbody>`;
            
    res.data.forEach(p => { 
        html += `
        <tr>
            <td>${p.id}</td>
            <td>
                <strong class="text-dark">${escHtml(p.nombre)}</strong>
                ${p.descripcion ? `<br><small class="text-muted">${escHtml(p.descripcion.substring(0,60))}${p.descripcion.length > 60 ? '...' : ''}</small>` : ''}
            </td>
            <td>${escHtml(p.categoria_nombre)}</td>
            <td><strong>${formatPrecio(p.precio)}</strong></td>
            <td>
                <span class="badge ${p.stock == 0 ? 'bg-danger' : p.stock <= 5 ? 'bg-warning text-dark' : 'bg-success'} px-2 py-1">
                    ${p.stock} unidades
                </span>
            </td>
            <td>${p.disponible ? '<span class="badge bg-light text-success border border-success border-opacity-25 px-2 py-1">Disponible</span>' : '<span class="badge bg-light text-secondary border px-2 py-1">No disponible</span>'}</td>
            <td>
                <button class="btn btn-sm btn-outline-info me-1 rounded-circle" onclick="verDetallesProducto(${p.id})" title="Ver detalles"><i class="bi bi-eye"></i></button>
                <button class="btn btn-sm btn-outline-primary me-1 rounded-circle" onclick="editarProducto(${p.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger rounded-circle" onclick="eliminarProducto(${p.id}, '${escHtml(p.nombre)}')" title="Eliminar"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`; 
    });
    
    html += `</tbody></table></div>`;
    container.innerHTML = html;
}

async function verDetallesProducto(id) {
    const res = await callApi('admin_listar_productos');
    if (!res.ok) return;
    const p = res.data.find(prod => prod.id == id);
    if (!p) return;
    
    const imgUrl = p.imagen ? (p.imagen.startsWith('http') ? p.imagen : `../assets/img/${p.imagen}`) : '';
    const advs = (p.advertencias_arr || []).map(a => `<span class="badge bg-danger me-1">${escHtml(a)}</span>`).join('') || '<span class="text-muted">Ninguna</span>';
    const stockClass = p.stock === 0 ? 'text-danger fw-bold' : p.stock <= 5 ? 'text-warning fw-bold' : 'text-success';
    
    Swal.fire({
        title: `<strong>${escHtml(p.nombre)}</strong>`,
        html: `
            <div class="text-start">
                <div class="text-center mb-3">
                    <img src="${escHtml(imgUrl)}" style="max-height: 180px; max-width: 100%; border-radius: 12px; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400&q=80'">
                </div>
                <p><strong>ID:</strong> #${p.id}</p>
                <p><strong>Categoría:</strong> ${escHtml(p.categoria_nombre)}</p>
                <p><strong>Precio:</strong> <span class="text-success fw-bold">${formatPrecio(p.precio)}</span></p>
                <p><strong>Stock:</strong> <span class="${stockClass}">${p.stock} unidades</span></p>
                <p><strong>Descripción:</strong> ${p.descripcion ? escHtml(p.descripcion) : '<span class="text-muted">Sin descripción</span>'}</p>
                <p><strong>Advertencias:</strong> ${advs}</p>
                <p><strong>Estado:</strong> ${p.disponible ? '<span class="badge bg-success">Disponible</span>' : '<span class="badge bg-secondary">No disponible</span>'} ${p.destacado ? '<span class="badge bg-warning text-dark">Destacado</span>' : ''}</p>
            </div>
        `,
        confirmButtonText: 'Cerrar',
        confirmButtonColor: '#a04000',
        customClass: {
            popup: 'rounded-4'
        }
    });
}

async function loadMenu(container) {
    const res = await callApi('admin_listar_menu');
    if (!res.ok) { 
        container.innerHTML = `<div class="alert alert-danger">Error cargando menús: ${escHtml(res.message)}</div>`; 
        return; 
    }
    
    let html = `
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Plato Principal</th>
                    <th>Precio</th>
                    <th>Componentes</th>
                    <th style="width:120px">Acciones</th>
                </tr>
            </thead>
            <tbody>`;
            
    res.data.forEach(m => { 
        let comps = [
            m.acompanamiento ? `🍚 ${m.acompanamiento}` : '', 
            m.ensalada ? `🥗 ${m.ensalada}` : '', 
            m.jugo ? `🧃 ${m.jugo}` : '', 
            m.postre ? `🍮 ${m.postre}` : '', 
            m.fruta ? `🍎 ${m.fruta}` : ''
        ].filter(Boolean).join(' | ');
        
        html += `
        <tr>
            <td><strong>${m.fecha}</strong></td>
            <td>
                <strong class="text-dark">${escHtml(m.plato_nombre)}</strong>
                ${m.plato_desc ? `<br><small class="text-muted">${escHtml(m.plato_desc)}</small>` : ''}
            </td>
            <td><strong>${formatPrecio(m.precio)}</strong></td>
            <td><small class="text-secondary">${comps || '—'}</small></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1 rounded-circle" onclick="editarMenu(${m.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger rounded-circle" onclick="eliminarMenu(${m.id}, '${escHtml(m.plato_nombre)}')" title="Eliminar"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`; 
    });
    
    html += `</tbody></table></div>`;
    container.innerHTML = html;
}

async function loadReportes(container) {
    const res = await callApi('admin_listar_reportes');
    if (!res.ok) { 
        container.innerHTML = `<div class="alert alert-danger">Error cargando reportes: ${escHtml(res.message)}</div>`; 
        return; 
    }
    
    const dateRangeFilterHtml = `
        <div class="row g-3 align-items-center mb-3">
            <div class="col-md-3">
                <select id="filtroRangoReportes" class="form-select" onchange="filtrarRangoReportes()">
                    <option value="todo">Ver todos los registros</option>
                    <option value="dia">Hoy (Diario)</option>
                    <option value="semana">Esta semana</option>
                    <option value="mes" selected>Este mes (Mensual)</option>
                    <option value="anio">Este año (Anual)</option>
                </select>
            </div>
            <div class="col-md-9 text-md-end d-flex gap-2 justify-content-md-end flex-wrap">
                <button class="btn btn-outline-danger btn-sm" onclick="exportarReportesPDF()"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                <button class="btn btn-outline-success btn-sm" onclick="exportarReportesExcel()"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Excel</button>
                <button class="btn btn-outline-secondary btn-sm" onclick="imprimirReportesTable()"><i class="bi bi-printer me-1"></i> Imprimir</button>
            </div>
        </div>
    `;

    let html = `
    <!-- Navegación por pestañas -->
    <ul class="nav nav-pills mb-4" id="reportesTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="user-reports-tab" data-bs-toggle="pill" data-bs-target="#user-reports" type="button" role="tab">Reportes de Usuarios</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="price-history-tab" data-bs-toggle="pill" data-bs-target="#price-history" type="button" role="tab" onclick="loadHistorialPreciosTab()">Historial de Variación de Precios</button>
        </li>
    </ul>
    
    <div class="tab-content" id="reportesTabsContent">
        <!-- Pestaña 1: Reportes de Usuarios -->
        <div class="tab-pane fade show active" id="user-reports" role="tabpanel">
            ${dateRangeFilterHtml}
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0" id="tablaReportesUsuarios">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Precio Sitio</th>
                            <th>Precio Real</th>
                            <th>Comentarios</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th class="no-export" style="width:160px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>`;
            
    res.data.forEach(r => { 
        html += `
        <tr data-date="${r.created_at.substring(0,10)}">
            <td>${r.id}</td>
            <td><strong>${escHtml(r.producto_nombre)}</strong></td>
            <td class="text-danger">${formatPrecio(r.precio_sitio)}</td>
            <td class="text-success fw-bold">${formatPrecio(r.precio_real)}</td>
            <td><small>${escHtml(r.comentarios || '—')}</small></td>
            <td>
                <span class="badge ${r.estado == 'pendiente' ? 'bg-warning text-dark' : r.estado == 'revisado' ? 'bg-info text-dark' : 'bg-success'} px-2 py-1">
                    ${r.estado}
                </span>
            </td>
            <td><small class="text-secondary">${new Date(r.created_at).toLocaleString('es-CL')}</small></td>
            <td class="no-export">
                ${r.estado === 'pendiente' ? `<button class="btn btn-sm btn-outline-success me-1 rounded-circle" onclick="resolverReporte(${r.id}, ${r.precio_real}, '${escHtml(r.producto_nombre)}')" title="Resolver y actualizar precio"><i class="bi bi-check-lg"></i></button>` : ''}
                <button class="btn btn-sm btn-outline-primary me-1 rounded-circle" onclick="editarReporte(${r.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger rounded-circle" onclick="eliminarReporte(${r.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`; 
    });
    
    html += `
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pestaña 2: Historial de Variación de Precios -->
        <div class="tab-pane fade" id="price-history" role="tabpanel">
            <div id="priceHistoryContainer">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div> Cargando historial...</div>
            </div>
        </div>
    </div>`;
    
    container.innerHTML = html;
    
    setTimeout(filtrarRangoReportes, 20);
}

function filtrarRangoReportes() {
    const filterSelect = document.getElementById('filtroRangoReportes');
    if (!filterSelect) return;
    const range = filterSelect.value;
    const rows = document.querySelectorAll('#tablaReportesUsuarios tbody tr');
    const hoy = new Date();
    
    rows.forEach(row => {
        const rowDateStr = row.dataset.date;
        if (!rowDateStr) return;
        const rowDate = new Date(rowDateStr);
        
        let match = false;
        if (range === 'todo') {
            match = true;
        } else if (range === 'dia') {
            const todayStr = hoy.toISOString().substring(0, 10);
            match = (rowDateStr === todayStr);
        } else if (range === 'semana') {
            const diff = hoy.getDate() - hoy.getDay() + (hoy.getDay() === 0 ? -6 : 1);
            const monday = new Date(hoy.setDate(diff));
            monday.setHours(0, 0, 0, 0);
            const now = new Date();
            match = (rowDate >= monday && rowDate <= now);
        } else if (range === 'mes') {
            match = (rowDate.getMonth() === hoy.getMonth() && rowDate.getFullYear() === hoy.getFullYear());
        } else if (range === 'anio') {
            match = (rowDate.getFullYear() === hoy.getFullYear());
        }
        
        row.style.display = match ? '' : 'none';
    });
}

function exportarReportesPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.text("Casino Universitario - Reportes de Errores de Precios", 14, 15);
    
    const table = document.getElementById('tablaReportesUsuarios');
    const headers = [];
    const data = [];
    
    const ths = table.querySelectorAll('thead th');
    for (let i = 0; i < ths.length - 1; i++) {
        headers.push(ths[i].innerText);
    }
    
    const trs = table.querySelectorAll('tbody tr');
    trs.forEach(tr => {
        if (tr.style.display === 'none') return;
        const row = [];
        const tds = tr.querySelectorAll('td');
        for (let i = 0; i < tds.length - 1; i++) {
            row.push(tds[i].innerText);
        }
        data.push(row);
    });
    
    doc.autoTable({
        head: [headers],
        body: data,
        startY: 20,
        theme: 'striped',
        headStyles: { fillColor: [160, 64, 0] }
    });
    doc.save('reportes_precios_usuarios.pdf');
}

function exportarReportesExcel() {
    const table = document.getElementById('tablaReportesUsuarios');
    const rows = [];
    
    const headers = [];
    const ths = table.querySelectorAll('thead th');
    for (let i = 0; i < ths.length - 1; i++) {
        headers.push(ths[i].innerText);
    }
    rows.push(headers);
    
    const trs = table.querySelectorAll('tbody tr');
    trs.forEach(tr => {
        if (tr.style.display === 'none') return;
        const row = [];
        const tds = tr.querySelectorAll('td');
        for (let i = 0; i < tds.length - 1; i++) {
            row.push(tds[i].innerText);
        }
        rows.push(row);
    });
    
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(rows);
    XLSX.utils.book_append_sheet(wb, ws, "Reportes");
    XLSX.writeFile(wb, 'reportes_precios_usuarios.xlsx');
}

function imprimirReportesTable() {
    imprimirReportes("Reportes de Precios de Usuarios", 'tablaReportesUsuarios');
}

function imprimirReportes(titulo, tableId) {
    const el = document.getElementById(tableId);
    const win = window.open('', '', 'height=700,width=900');
    win.document.write('<html><head><title>Imprimir Reporte</title>');
    win.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
    win.document.write('<style>body{padding:20px;} th,td{padding:8px; border:1px solid #dee2e6;} th{background-color:#f8f9fa;} .no-export{display:none;}</style>');
    win.document.write('</head><body>');
    win.document.write(`<h3 class="mb-4">${titulo}</h3>`);
    
    const clone = el.cloneNode(true);
    clone.querySelectorAll('.no-export, th:last-child, td:last-child').forEach(e => e.remove());
    
    win.document.write(clone.outerHTML);
    win.document.write('</body></html>');
    win.document.close();
    setTimeout(() => { win.print(); win.close(); }, 500);
}

async function loadHistorialPreciosTab() {
    const container = document.getElementById('priceHistoryContainer');
    container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div> Cargando historial...</div>';
    
    const res = await callApi('admin_listar_historial_precios');
    if (!res.ok) {
        container.innerHTML = `<div class="alert alert-danger">Error: ${escHtml(res.message)}</div>`;
        return;
    }
    
    let html = `
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-muted"><i class="bi bi-clock-history me-1"></i> Historial de Cambios Registrados</h6>
            <button class="btn btn-warning btn-sm text-dark fw-bold" onclick="abrirHistorialPrecioModal()"><i class="bi bi-plus-lg"></i> Registrar Cambio de Precio</button>
        </div>
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0" id="tablaHistorialPrecios">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Precio Anterior</th>
                        <th>Precio Nuevo</th>
                        <th>Modificado Por</th>
                        <th>Motivo / Razón</th>
                        <th>Fecha</th>
                        <th style="width:100px">Acción</th>
                    </tr>
                </thead>
                <tbody>`;
                
    res.data.forEach(h => {
        html += `
        <tr>
            <td>${h.id}</td>
            <td><strong>${escHtml(h.producto_nombre)}</strong></td>
            <td class="text-secondary">${formatPrecio(h.precio_anterior)}</td>
            <td class="text-success fw-bold">${formatPrecio(h.precio_nuevo)}</td>
            <td><small><i class="bi bi-person me-1"></i>${escHtml(h.admin_nombre || 'Sistema')}</small></td>
            <td><small>${escHtml(h.motivo || '—')}</small></td>
            <td><small class="text-secondary">${new Date(h.created_at).toLocaleString('es-CL')}</small></td>
            <td>
                <button class="btn btn-sm btn-outline-danger rounded-circle" onclick="eliminarHistorialPrecio(${h.id})" title="Eliminar registro de historial"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    container.innerHTML = html;
}

async function resolverReporte(id, precioReal, prodNombre) {
    const confirm = await Swal.fire({
        title: 'Resolver Reporte',
        text: `¿Deseas marcar este reporte como resuelto? También puedes actualizar el precio de "${prodNombre}" a ${formatPrecio(precioReal)} automáticamente.`,
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Sí, resolver y actualizar precio',
        denyButtonText: 'Solo resolver',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#27ae60',
        denyButtonColor: '#2980b9'
    });
    
    if (confirm.isDismissed) return;
    
    try {
        if (confirm.isConfirmed) {
            const prodRes = await callApi('admin_listar_productos');
            if (prodRes.ok) {
                const prod = prodRes.data.find(p => p.nombre.toLowerCase().trim() === prodNombre.toLowerCase().trim());
                if (prod) {
                    await callApi('admin_crear_historial_precio', 'POST', {
                        producto_id: prod.id,
                        precio_nuevo: precioReal,
                        motivo: `Resuelto reporte de precio de usuario (Reporte #${id})`
                    });
                }
            }
        }
        
        const res = await callApi('admin_marcar_reporte', 'POST', { id, estado: 'resuelto' });
        if (res.ok) {
            loadSection('reportes');
            Swal.fire('Éxito', 'El reporte ha sido resuelto.', 'success');
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'No se pudo resolver el reporte.', 'error');
    }
}

async function editarReporte(id) {
    const res = await callApi('admin_listar_reportes');
    if (!res.ok) return;
    const rep = res.data.find(r => r.id == id);
    if (rep) {
        document.getElementById('rep_id').value = rep.id;
        document.getElementById('rep_producto_nombre').value = rep.producto_nombre;
        document.getElementById('rep_precio_sitio').value = rep.precio_sitio;
        document.getElementById('rep_precio_real').value = rep.precio_real;
        document.getElementById('rep_comentarios').value = rep.comentarios || '';
        document.getElementById('rep_estado').value = rep.estado;
        
        new bootstrap.Modal(document.getElementById('reporteModal')).show();
    }
}

async function guardarReporte() {
    const id = document.getElementById('rep_id').value;
    const data = {
        id: parseInt(id),
        producto_nombre: document.getElementById('rep_producto_nombre').value.trim(),
        precio_sitio: parseInt(document.getElementById('rep_precio_sitio').value),
        precio_real: parseInt(document.getElementById('rep_precio_real').value),
        comentarios: document.getElementById('rep_comentarios').value.trim(),
        estado: document.getElementById('rep_estado').value
    };
    
    if (!data.producto_nombre || isNaN(data.precio_sitio) || isNaN(data.precio_real)) {
        Swal.fire('Formulario Incompleto', 'El nombre y precios son obligatorios.', 'warning');
        return;
    }
    
    try {
        const res = await callApi('admin_actualizar_reporte_completo', 'POST', data);
        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('reporteModal')).hide();
            loadSection('reportes');
            Swal.fire('Éxito', res.message, 'success');
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch {
        Swal.fire('Error', 'No se pudo guardar el reporte.', 'error');
    }
}

async function eliminarReporte(id) {
    const confirm = await Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Eliminar este reporte permanentemente?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        confirmButtonText: 'Sí, eliminar'
    });
    
    if (confirm.isConfirmed) {
        try {
            const res = await callApi('admin_eliminar_reporte', 'POST', { id });
            if (res.ok) {
                loadSection('reportes');
                Swal.fire('Eliminado', res.message, 'success');
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        } catch {
            Swal.fire('Error', 'No se pudo eliminar el reporte.', 'error');
        }
    }
}

async function abrirHistorialPrecioModal() {
    document.getElementById('historialPrecioForm').reset();
    const prodRes = await callApi('admin_listar_productos');
    const select = document.getElementById('hp_producto_id');
    if (prodRes.ok && select) {
        select.innerHTML = prodRes.data.map(p => `<option value="${p.id}">${escHtml(p.nombre)} (${formatPrecio(p.precio)})</option>`).join('');
    }
    new bootstrap.Modal(document.getElementById('historialPrecioModal')).show();
}

async function guardarHistorialPrecio() {
    const data = {
        producto_id: parseInt(document.getElementById('hp_producto_id').value),
        precio_nuevo: parseInt(document.getElementById('hp_precio_nuevo').value),
        motivo: document.getElementById('hp_motivo').value.trim()
    };
    
    if (isNaN(data.producto_id) || isNaN(data.precio_nuevo) || data.precio_nuevo <= 0 || !data.motivo) {
        Swal.fire('Campos inválidos', 'Todos los campos son obligatorios.', 'warning');
        return;
    }
    
    try {
        const res = await callApi('admin_crear_historial_precio', 'POST', data);
        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('historialPrecioModal')).hide();
            loadHistorialPreciosTab();
            Swal.fire('Éxito', res.message, 'success');
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch {
        Swal.fire('Error', 'No se pudo registrar la variación de precio.', 'error');
    }
}

async function eliminarHistorialPrecio(id) {
    const confirm = await Swal.fire({
        title: '¿Eliminar log?',
        text: '¿Estás seguro de eliminar este registro del historial?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        confirmButtonText: 'Sí, eliminar'
    });
    
    if (confirm.isConfirmed) {
        try {
            const res = await callApi('admin_eliminar_historial_precio', 'POST', { id });
            if (res.ok) {
                loadHistorialPreciosTab();
                Swal.fire('Eliminado', res.message, 'success');
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        } catch {
            Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
        }
    }
}

function abrirProductoModal(id=null) { 
    document.getElementById('productoForm').reset(); 
    document.getElementById('prod_id').value=''; 
    document.getElementById('productoModalTitle').innerText = id ? 'Editar Producto' : 'Nuevo Producto'; 
    if (id) cargarProductoParaEditar(id); 
    new bootstrap.Modal(document.getElementById('productoModal')).show(); 
}

async function cargarProductoParaEditar(id) { 
    const res = await callApi('admin_listar_productos'); 
    if (!res.ok) return; 
    const prod = res.data.find(p => p.id == id); 
    if (prod) { 
        document.getElementById('prod_id').value = prod.id; 
        document.getElementById('prod_categoria').value = prod.categoria_id; 
        document.getElementById('prod_nombre').value = prod.nombre; 
        document.getElementById('prod_descripcion').value = prod.descripcion || ''; 
        document.getElementById('prod_precio').value = prod.precio; 
        document.getElementById('prod_stock').value = prod.stock; 
        document.getElementById('prod_imagen').value = prod.imagen || ''; 
        document.getElementById('prod_advertencias').value = (prod.advertencias_arr || []).join(', '); 
        document.getElementById('prod_disponible').checked = prod.disponible == 1; 
        document.getElementById('prod_destacado').checked = prod.destacado == 1; 

        document.getElementById('prod_imagen_file').value = '';
        const preview = document.getElementById('prod_preview');
        if (prod.imagen_url) {
            preview.src = prod.imagen_url;
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    } 
}

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}

function filtrarTablaProductos() {
    const term = document.getElementById('busquedaProducto').value.toLowerCase();
    const cat = document.getElementById('filtroCategoria').value;
    const rows = document.querySelectorAll('#tablaProductos tbody tr');
    rows.forEach(r => {
        const name = r.cells[1].innerText.toLowerCase();
        const rowCat = r.cells[2].innerText;
        const matchName = name.includes(term);
        const matchCat = cat === '' || rowCat === cat;
        r.style.display = (matchName && matchCat) ? '' : 'none';
    });
}

async function guardarProducto() { 
    const id = document.getElementById('prod_id').value; 
    const data = new FormData();
    data.append('categoria_id', document.getElementById('prod_categoria').value);
    data.append('nombre', document.getElementById('prod_nombre').value.trim());
    data.append('descripcion', document.getElementById('prod_descripcion').value.trim());
    data.append('precio', document.getElementById('prod_precio').value);
    data.append('stock', document.getElementById('prod_stock').value);
    data.append('imagen', document.getElementById('prod_imagen').value.trim());
    data.append('advertencias', JSON.stringify(document.getElementById('prod_advertencias').value.split(',').map(s => s.trim()).filter(Boolean)));
    data.append('disponible', document.getElementById('prod_disponible').checked ? 1 : 0);
    data.append('destacado', document.getElementById('prod_destacado').checked ? 1 : 0);
    
    const fileInput = document.getElementById('prod_imagen_file');
    if (fileInput.files.length > 0) {
        data.append('imagen_file', fileInput.files[0]);
    }
    
    if (!data.get('nombre') || isNaN(data.get('precio')) || data.get('precio') <= 0) {
        Swal.fire('Formulario Incompleto', 'El nombre y un precio válido son obligatorios.', 'warning');
        return;
    } 
    const action = id ? 'admin_actualizar_producto' : 'admin_crear_producto'; 
    if (id) data.append('id', id); 
    
    try {
        const res = await callApi(action, 'POST', data); 
        if (res.ok) { 
            bootstrap.Modal.getInstance(document.getElementById('productoModal')).hide(); 
            loadSection('productos'); 
            Swal.fire('Éxito', res.message, 'success'); 
        } else {
            Swal.fire('Error', res.message, 'error'); 
        }
    } catch {
        Swal.fire('Error', 'No se pudieron guardar los datos.', 'error');
    }
}

async function eliminarProducto(id, nombre) { 
    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Eliminar producto "${nombre}" permanentemente?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#7f8c8d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            const res = await callApi('admin_eliminar_producto', 'POST', { id }); 
            if (res.ok) { 
                loadSection('productos'); 
                Swal.fire('Eliminado', res.message, 'success'); 
            } else {
                Swal.fire('Error', res.message, 'error'); 
            }
        } catch {
            Swal.fire('Error', 'No se pudo completar la solicitud.', 'error');
        }
    }
}

function editarProducto(id) { abrirProductoModal(id); }

function abrirMenuModal(id=null) { 
    document.getElementById('menuForm').reset(); 
    document.getElementById('menu_id').value=''; 
    document.getElementById('menuModalTitle').innerText = id ? 'Editar Menú del Día' : 'Nuevo Menú del Día'; 
    if (id) cargarMenuParaEditar(id); 
    new bootstrap.Modal(document.getElementById('menuModal')).show(); 
}

async function cargarMenuParaEditar(id) { 
    const res = await callApi('admin_listar_menu'); 
    if (!res.ok) return; 
    const menu = res.data.find(m => m.id == id); 
    if (menu) { 
        document.getElementById('menu_id').value = menu.id; 
        document.getElementById('menu_fecha').value = menu.fecha; 
        document.getElementById('menu_plato_nombre').value = menu.plato_nombre; 
        document.getElementById('menu_plato_desc').value = menu.plato_desc || ''; 
        document.getElementById('menu_acompanamiento').value = menu.acompanamiento || ''; 
        document.getElementById('menu_ensalada').value = menu.ensalada || ''; 
        document.getElementById('menu_jugo').value = menu.jugo || ''; 
        document.getElementById('menu_postre').value = menu.postre || ''; 
        document.getElementById('menu_fruta').value = menu.fruta || ''; 
        document.getElementById('menu_precio').value = menu.precio; 
        document.getElementById('menu_disponible_hasta').value = menu.disponible_hasta.substring(0,5); 
        document.getElementById('menu_imagen').value = menu.imagen || '';
        
        document.getElementById('menu_imagen_file').value = '';
        const preview = document.getElementById('menu_preview');
        if (menu.imagen_url) {
            preview.src = menu.imagen_url;
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    } 
}

async function guardarMenu() { 
    const id = document.getElementById('menu_id').value; 
    const data = new FormData();
    data.append('fecha', document.getElementById('menu_fecha').value);
    data.append('plato_nombre', document.getElementById('menu_plato_nombre').value.trim());
    data.append('plato_desc', document.getElementById('menu_plato_desc').value.trim());
    data.append('acompanamiento', document.getElementById('menu_acompanamiento').value.trim());
    data.append('ensalada', document.getElementById('menu_ensalada').value.trim());
    data.append('jugo', document.getElementById('menu_jugo').value.trim());
    data.append('postre', document.getElementById('menu_postre').value.trim());
    data.append('fruta', document.getElementById('menu_fruta').value.trim());
    data.append('precio', document.getElementById('menu_precio').value);
    data.append('disponible_hasta', document.getElementById('menu_disponible_hasta').value);
    data.append('imagen', document.getElementById('menu_imagen').value.trim());
    
    const fileInput = document.getElementById('menu_imagen_file');
    if (fileInput.files.length > 0) {
        data.append('imagen_file', fileInput.files[0]);
    }
    
    if (!data.get('fecha') || !data.get('plato_nombre') || isNaN(data.get('precio')) || data.get('precio') <= 0) {
        Swal.fire('Formulario Incompleto', 'La fecha, el plato y un precio válido son obligatorios.', 'warning');
        return;
    } 
    const action = id ? 'admin_actualizar_menu' : 'admin_crear_menu'; 
    if (id) data.append('id', id); 
    
    try {
        const res = await callApi(action, 'POST', data); 
        if (res.ok) { 
            bootstrap.Modal.getInstance(document.getElementById('menuModal')).hide(); 
            loadSection('menu'); 
            Swal.fire('Éxito', res.message, 'success'); 
        } else {
            Swal.fire('Error', res.message, 'error'); 
        }
    } catch {
        Swal.fire('Error', 'No se pudo guardar el menú.', 'error');
    }
}

async function eliminarMenu(id, nombre) { 
    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Eliminar menú "${nombre}" de la lista de programación?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#7f8c8d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            const res = await callApi('admin_eliminar_menu', 'POST', { id }); 
            if (res.ok) { 
                loadSection('menu'); 
                Swal.fire('Eliminado', res.message, 'success'); 
            } else {
                Swal.fire('Error', res.message, 'error'); 
            }
        } catch {
            Swal.fire('Error', 'No se pudo eliminar el menú.', 'error');
        }
    }
}

function editarMenu(id) { abrirMenuModal(id); }

async function marcarReporte(id) { 
    try {
        const res = await callApi('admin_marcar_reporte', 'POST', { id, estado: 'revisado' }); 
        if (res.ok) { 
            loadSection('reportes'); 
            Swal.fire('Resuelto', 'El reporte ha sido marcado como revisado.', 'success'); 
        } else {
            Swal.fire('Error', res.message, 'error'); 
        }
    } catch {
        Swal.fire('Error', 'No se pudo actualizar el estado del reporte.', 'error');
    }
}

// ---- Módulo: Usuarios (Administradores) ----
async function loadUsuarios(container) {
    const res = await callApi('admin_listar_usuarios');
    if (!res.ok) {
        container.innerHTML = `<div class="alert alert-danger">Error cargando usuarios: ${escHtml(res.message)}</div>`;
        return;
    }
    
    let html = `
    <div class="mb-3 col-md-4">
        <input type="text" id="busquedaUsuario" class="form-control" placeholder="Buscar usuario por nombre o username..." onkeyup="filtrarTablaUsuarios()">
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0" id="tablaUsuarios">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Estado</th>
                    <th>Último Acceso</th>
                    <th style="width:120px">Acciones</th>
                </tr>
            </thead>
            <tbody>`;
            
    res.data.forEach(u => {
        const estadoBadge = u.activo 
            ? '<span class="badge bg-light text-success border border-success border-opacity-25 px-2 py-1">Activo</span>' 
            : '<span class="badge bg-light text-secondary border px-2 py-1">Inactivo</span>';
            
        html += `
        <tr>
            <td>${u.id}</td>
            <td><strong>${escHtml(u.nombre)}</strong></td>
            <td><code>${escHtml(u.username)}</code></td>
            <td>${escHtml(u.email || '—')}</td>
            <td>${estadoBadge}</td>
            <td><small>${u.ultimo_login ? new Date(u.ultimo_login).toLocaleString('es-CL') : 'Nunca'}</small></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1 rounded-circle" onclick="editarUsuario(${u.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger rounded-circle" onclick="eliminarUsuario(${u.id}, '${escHtml(u.nombre)}')" title="Eliminar"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    });
    
    html += `</tbody></table></div>`;
    container.innerHTML = html;
}

function filtrarTablaUsuarios() {
    const term = document.getElementById('busquedaUsuario').value.toLowerCase();
    const rows = document.querySelectorAll('#tablaUsuarios tbody tr');
    rows.forEach(r => {
        const name = r.cells[1].innerText.toLowerCase();
        const username = r.cells[2].innerText.toLowerCase();
        const match = name.includes(term) || username.includes(term);
        r.style.display = match ? '' : 'none';
    });
}

function abrirUsuarioModal(id = null) {
    document.getElementById('usuarioForm').reset();
    document.getElementById('us_id').value = '';
    document.getElementById('usuarioModalTitle').innerText = id ? 'Editar Administrador' : 'Nuevo Administrador';
    
    const passRequired = document.getElementById('us_pass_required');
    const passHelp = document.getElementById('us_pass_help');
    
    if (id) {
        passRequired.style.display = 'none';
        passHelp.style.display = 'block';
        cargarUsuarioParaEditar(id);
    } else {
        passRequired.style.display = 'inline';
        passHelp.style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('usuarioModal')).show();
}

async function cargarUsuarioParaEditar(id) {
    const res = await callApi('admin_listar_usuarios');
    if (!res.ok) return;
    const user = res.data.find(u => u.id == id);
    if (user) {
        document.getElementById('us_id').value = user.id;
        document.getElementById('us_nombre').value = user.nombre;
        document.getElementById('us_username').value = user.username;
        document.getElementById('us_email').value = user.email || '';
        document.getElementById('us_activo').checked = user.activo == 1;
    }
}

async function guardarUsuario() {
    const id = document.getElementById('us_id').value;
    const data = {
        nombre: document.getElementById('us_nombre').value.trim(),
        username: document.getElementById('us_username').value.trim(),
        email: document.getElementById('us_email').value.trim(),
        password: document.getElementById('us_password').value,
        activo: document.getElementById('us_activo').checked ? 1 : 0
    };
    
    if (!data.nombre || !data.username || !data.email) {
        Swal.fire('Campos obligatorios', 'Nombre, Usuario y Email son requeridos.', 'warning');
        return;
    }
    
    if (!id && !data.password) {
        Swal.fire('Contraseña requerida', 'Debes ingresar una contraseña para el nuevo usuario.', 'warning');
        return;
    }
    
    if (data.password && data.password.length < 6) {
        Swal.fire('Contraseña corta', 'La contraseña debe tener al menos 6 caracteres.', 'warning');
        return;
    }
    
    const action = id ? 'admin_actualizar_usuario' : 'admin_crear_usuario';
    if (id) data.id = parseInt(id);
    
    try {
        const res = await callApi(action, 'POST', data);
        if (res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('usuarioModal')).hide();
            loadSection('usuarios');
            Swal.fire('Éxito', res.message, 'success');
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch {
        Swal.fire('Error', 'No se pudo guardar el usuario.', 'error');
    }
}

async function eliminarUsuario(id, nombre) {
    const confirm = await Swal.fire({
        title: '¿Eliminar administrador?',
        text: `¿Estás seguro de eliminar a "${nombre}" permanentemente?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        confirmButtonText: 'Sí, eliminar'
    });
    
    if (confirm.isConfirmed) {
        try {
            const res = await callApi('admin_eliminar_usuario', 'POST', { id });
            if (res.ok) {
                loadSection('usuarios');
                Swal.fire('Eliminado', res.message, 'success');
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        } catch {
            Swal.fire('Error', 'No se pudo eliminar el administrador.', 'error');
        }
    }
}

function editarUsuario(id) { abrirUsuarioModal(id); }

// ---- Módulo: Configuración ----
async function loadConfiguracion(container) {
    const res = await callApi('admin_obtener_config');
    if (!res.ok) {
        container.innerHTML = `<div class="alert alert-danger">Error cargando configuración: ${escHtml(res.message)}</div>`;
        return;
    }
    
    const conf = res.data;
    
    let html = `
    <div class="card border-0 shadow-sm p-4 rounded-4 col-lg-8 mx-auto">
        <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-gear-fill me-1"></i> Configuración del Sitio</h5>
        <form id="configForm" onsubmit="event.preventDefault(); guardarConfiguracion();">
            <div class="mb-3">
                <label class="form-label fw-bold">Nombre del Casino (SITE_NAME)</label>
                <input type="text" id="conf_site_name" class="form-control" value="${escHtml(conf.site_name)}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">URL del Sitio (SITE_URL)</label>
                <input type="url" id="conf_site_url" class="form-control" value="${escHtml(conf.site_url)}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Email de Contacto</label>
                <input type="email" id="conf_contact_email" class="form-control" value="${escHtml(conf.contact_email)}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Horario de Atención</label>
                <input type="text" id="conf_open_hours" class="form-control" value="${escHtml(conf.open_hours)}" required>
            </div>
            
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="conf_maintenance_mode" ${conf.maintenance_mode ? 'checked' : ''}>
                <label class="form-check-label fw-bold" for="conf_maintenance_mode">Modo Mantenimiento (Activar modo de construcción)</label>
            </div>
            
            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" role="switch" id="conf_enable_reports" ${conf.enable_reports ? 'checked' : ''}>
                <label class="form-check-label fw-bold" for="conf_enable_reports">Permitir Reportes de Precios de Usuarios</label>
            </div>
            
            <div class="text-end">
                <button type="submit" class="btn btn-primary btn-action px-5"><i class="bi bi-save me-1"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
    `;
    
    container.innerHTML = html;
}

async function guardarConfiguracion() {
    const data = {
        site_name: document.getElementById('conf_site_name').value.trim(),
        site_url: document.getElementById('conf_site_url').value.trim(),
        contact_email: document.getElementById('conf_contact_email').value.trim(),
        open_hours: document.getElementById('conf_open_hours').value.trim(),
        maintenance_mode: document.getElementById('conf_maintenance_mode').checked,
        enable_reports: document.getElementById('conf_enable_reports').checked
    };
    
    try {
        const res = await callApi('admin_guardar_config', 'POST', data);
        if (res.ok) {
            Swal.fire('Configuración Guardada', res.message, 'success');
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch {
        Swal.fire('Error', 'No se pudo guardar la configuración.', 'error');
    }
}

// ---- Event Listeners para vistas previas de imágenes ----
document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('input', (e) => {
        if (e.target.id === 'prod_imagen') {
            const preview = document.getElementById('prod_preview');
            if (preview) {
                preview.src = e.target.value.trim();
                preview.style.display = e.target.value.trim() ? 'block' : 'none';
            }
        }
        if (e.target.id === 'menu_imagen') {
            const preview = document.getElementById('menu_preview');
            if (preview) {
                preview.src = e.target.value.trim();
                preview.style.display = e.target.value.trim() ? 'block' : 'none';
            }
        }
    });
});

function formatPrecio(valor) { 
    return '$' + parseInt(valor).toLocaleString('es-CL'); 
}

function escHtml(str) { 
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;'); 
}

loadCategorias();
let startupSection = 'dashboard';
if (location.hash) {
    const hash = location.hash.replace('#', '');
    if (['dashboard', 'productos', 'menu', 'reportes', 'usuarios', 'configuracion'].includes(hash)) {
        startupSection = hash;
        document.querySelectorAll('.sidebar .nav-link').forEach(l => {
            if (l.dataset.section === hash) l.classList.add('active');
            else l.classList.remove('active');
        });
        const matchingLink = Array.from(document.querySelectorAll('.sidebar .nav-link')).find(l => l.dataset.section === hash);
        if (matchingLink) {
            document.getElementById('sectionTitle').innerText = matchingLink.innerText.trim();
            const actionBtn = document.getElementById('actionBtn');
            if (hash === 'productos') { 
                actionBtn.style.display = 'block'; 
                actionBtn.onclick = () => abrirProductoModal(); 
                actionBtn.innerHTML = '<i class="bi bi-plus-lg"></i> Nuevo Producto'; 
                actionBtn.className = 'btn btn-primary btn-action';
            } else if (hash === 'menu') {
                actionBtn.style.display = 'block'; 
                actionBtn.onclick = () => abrirMenuModal(); 
                actionBtn.innerHTML = '<i class="bi bi-plus-lg"></i> Nuevo Menú'; 
                actionBtn.className = 'btn btn-success btn-action';
            } else if (hash === 'usuarios') {
                actionBtn.style.display = 'block'; 
                actionBtn.onclick = () => abrirUsuarioModal(); 
                actionBtn.innerHTML = '<i class="bi bi-plus-lg"></i> Nuevo Usuario'; 
                actionBtn.className = 'btn btn-dark btn-action';
            } else {
                actionBtn.style.display = 'none';
            }
        }
    }
}
loadSection(startupSection);
</script>
</body>
</html>