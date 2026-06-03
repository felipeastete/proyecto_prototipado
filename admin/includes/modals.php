<?php
/**
 * admin/includes/modals.php
 * Todos los modales Bootstrap del panel admin (admin/index.php).
 * Se incluye una sola vez al final del <body>.
 */
?>

<!-- ══════════════════════════════════════════════ -->
<!-- Modal: Producto                               -->
<!-- ══════════════════════════════════════════════ -->
<div class="modal fade" id="productoModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-box me-1"></i>
                    <span id="productoModalTitle">Nuevo Producto</span>
                </h5>
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
                            <input type="file" id="prod_imagen_file" class="form-control" accept="image/*"
                                onchange="previewImage(this, 'prod_preview')">
                            <img id="prod_preview" src="" style="max-height:100px;display:none;" class="mt-2 rounded">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Advertencias (separar por comas)</label>
                            <input type="text" id="prod_advertencias" class="form-control"
                                placeholder="Ej: Alto en calorías, Alto en sodio">
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

<!-- ══════════════════════════════════════════════ -->
<!-- Modal: Menú del Día                          -->
<!-- ══════════════════════════════════════════════ -->
<div class="modal fade" id="menuModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-calendar me-1"></i>
                    <span id="menuModalTitle">Nuevo Menú</span>
                </h5>
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
                            <input type="file" id="menu_imagen_file" class="form-control" accept="image/*"
                                onchange="previewImage(this, 'menu_preview')">
                            <img id="menu_preview" src="" style="max-height:100px;display:none;" class="mt-2 rounded">
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

<!-- ══════════════════════════════════════════════ -->
<!-- Modal: Usuario / Administrador               -->
<!-- ══════════════════════════════════════════════ -->
<div class="modal fade" id="usuarioModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person me-1"></i>
                    <span id="usuarioModalTitle">Nuevo Usuario</span>
                </h5>
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
                        <label class="form-label fw-bold">
                            Contraseña <span id="us_pass_required" class="text-danger">*</span>
                        </label>
                        <input type="password" id="us_password" class="form-control" placeholder="Mínimo 6 caracteres">
                        <small class="text-muted d-block mt-1" id="us_pass_help" style="display:none;">
                            Deja en blanco para conservar la contraseña actual.
                        </small>
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

<!-- ══════════════════════════════════════════════ -->
<!-- Modal: Editar Reporte                        -->
<!-- ══════════════════════════════════════════════ -->
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

<!-- ══════════════════════════════════════════════ -->
<!-- Modal: Registrar Cambio de Precio (Historial) -->
<!-- ══════════════════════════════════════════════ -->
<div class="modal fade" id="historialPrecioModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-currency-dollar me-1"></i> Registrar Cambio de Precio
                </h5>
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
                        <input type="text" id="hp_motivo" class="form-control"
                            placeholder="Ej: Alza de costos de insumos" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning text-dark fw-bold"
                    onclick="guardarHistorialPrecio()">Actualizar Precio</button>
            </div>
        </div>
    </div>
</div>
