/**
 * admin-productos.js
 * Módulo CRUD de Productos del panel admin.
 * Depende de: admin-api.js (callApi, formatPrecio, escHtml)
 *             Las variables globales `categoriasList` y `currentSection`
 *             se definen en admin-index.js
 */

'use strict';

/* ── Cargar lista de productos ── */
async function loadProductos(container) {
    const res = await callApi('admin_listar_productos');
    if (!res.ok) {
        container.innerHTML = `<div class="alert alert-danger">Error cargando productos: ${escHtml(res.message)}</div>`;
        return;
    }

    const catsOptions = categoriasList
        .map(c => `<option value="${escHtml(c.nombre)}">${escHtml(c.nombre)}</option>`)
        .join('');

    let html = `
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" id="busquedaProducto" class="form-control" placeholder="Buscar por nombre..."
                onkeyup="filtrarTablaProductos()">
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
                    <th>ID</th><th>Nombre</th><th>Categoría</th>
                    <th>Precio</th><th>Stock</th><th>Estado</th>
                    <th style="width:160px">Acciones</th>
                </tr>
            </thead>
            <tbody>`;

    res.data.forEach(p => {
        const descShort = p.descripcion
            ? `<br><small class="text-muted">${escHtml(p.descripcion.substring(0,60))}${p.descripcion.length > 60 ? '...' : ''}</small>`
            : '';
        const stockBadge = p.stock == 0
            ? 'bg-danger'
            : p.stock <= 5 ? 'bg-warning text-dark' : 'bg-success';
        const estadoBadge = p.disponible
            ? '<span class="badge bg-light text-success border border-success border-opacity-25 px-2 py-1">Disponible</span>'
            : '<span class="badge bg-light text-secondary border px-2 py-1">No disponible</span>';

        html += `
        <tr>
            <td>${p.id}</td>
            <td><strong class="text-dark">${escHtml(p.nombre)}</strong>${descShort}</td>
            <td>${escHtml(p.categoria_nombre)}</td>
            <td><strong>${formatPrecio(p.precio)}</strong></td>
            <td><span class="badge ${stockBadge} px-2 py-1">${p.stock} unidades</span></td>
            <td>${estadoBadge}</td>
            <td>
                <button class="btn btn-sm btn-outline-info me-1 rounded-circle"
                    onclick="verDetallesProducto(${p.id})" title="Ver detalles"><i class="bi bi-eye"></i></button>
                <button class="btn btn-sm btn-outline-primary me-1 rounded-circle"
                    onclick="editarProducto(${p.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger rounded-circle"
                    onclick="eliminarProducto(${p.id}, '${escHtml(p.nombre)}')" title="Eliminar"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    });

    html += `</tbody></table></div>`;
    container.innerHTML = html;
}

/* ── Filtrar tabla de productos ── */
function filtrarTablaProductos() {
    const term = document.getElementById('busquedaProducto').value.toLowerCase();
    const cat  = document.getElementById('filtroCategoria').value;
    document.querySelectorAll('#tablaProductos tbody tr').forEach(r => {
        const name   = r.cells[1].innerText.toLowerCase();
        const rowCat = r.cells[2].innerText;
        r.style.display = (name.includes(term) && (cat === '' || rowCat === cat)) ? '' : 'none';
    });
}

/* ── Ver detalles de un producto ── */
async function verDetallesProducto(id) {
    const res = await callApi('admin_listar_productos');
    if (!res.ok) return;
    const p = res.data.find(prod => prod.id == id);
    if (!p) return;

    const imgUrl    = p.imagen ? (p.imagen.startsWith('http') ? p.imagen : `../assets/img/${p.imagen}`) : '';
    const advs      = (p.advertencias_arr || []).map(a => `<span class="badge bg-danger me-1">${escHtml(a)}</span>`).join('') || '<span class="text-muted">Ninguna</span>';
    const stockClass = p.stock === 0 ? 'text-danger fw-bold' : p.stock <= 5 ? 'text-warning fw-bold' : 'text-success';

    Swal.fire({
        title: `<strong>${escHtml(p.nombre)}</strong>`,
        html: `
            <div class="text-start">
                <div class="text-center mb-3">
                    <img src="${escHtml(imgUrl)}" style="max-height:180px;max-width:100%;border-radius:12px;object-fit:cover;"
                        onerror="this.src='https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400&q=80'">
                </div>
                <p><strong>ID:</strong> #${p.id}</p>
                <p><strong>Categoría:</strong> ${escHtml(p.categoria_nombre)}</p>
                <p><strong>Precio:</strong> <span class="text-success fw-bold">${formatPrecio(p.precio)}</span></p>
                <p><strong>Stock:</strong> <span class="${stockClass}">${p.stock} unidades</span></p>
                <p><strong>Descripción:</strong> ${p.descripcion ? escHtml(p.descripcion) : '<span class="text-muted">Sin descripción</span>'}</p>
                <p><strong>Advertencias:</strong> ${advs}</p>
                <p><strong>Estado:</strong>
                    ${p.disponible ? '<span class="badge bg-success">Disponible</span>' : '<span class="badge bg-secondary">No disponible</span>'}
                    ${p.destacado  ? '<span class="badge bg-warning text-dark ms-1">Destacado</span>' : ''}
                </p>
            </div>`,
        confirmButtonText: 'Cerrar',
        confirmButtonColor: '#a04000',
        customClass: { popup: 'rounded-4' }
    });
}

/* ── Abrir modal de producto (crear o editar) ── */
function abrirProductoModal(id = null) {
    document.getElementById('productoForm').reset();
    document.getElementById('prod_id').value = '';
    document.getElementById('productoModalTitle').innerText = id ? 'Editar Producto' : 'Nuevo Producto';
    if (id) cargarProductoParaEditar(id);
    new bootstrap.Modal(document.getElementById('productoModal')).show();
}

/* ── Cargar datos del producto en el formulario de edición ── */
async function cargarProductoParaEditar(id) {
    const res = await callApi('admin_listar_productos');
    if (!res.ok) return;
    const prod = res.data.find(p => p.id == id);
    if (!prod) return;

    document.getElementById('prod_id').value             = prod.id;
    document.getElementById('prod_categoria').value      = prod.categoria_id;
    document.getElementById('prod_nombre').value         = prod.nombre;
    document.getElementById('prod_descripcion').value    = prod.descripcion || '';
    document.getElementById('prod_precio').value         = prod.precio;
    document.getElementById('prod_stock').value          = prod.stock;
    document.getElementById('prod_imagen').value         = prod.imagen || '';
    document.getElementById('prod_advertencias').value   = (prod.advertencias_arr || []).join(', ');
    document.getElementById('prod_disponible').checked   = prod.disponible == 1;
    document.getElementById('prod_destacado').checked    = prod.destacado == 1;
    document.getElementById('prod_imagen_file').value    = '';

    const preview = document.getElementById('prod_preview');
    if (prod.imagen_url) { preview.src = prod.imagen_url; preview.style.display = 'block'; }
    else { preview.style.display = 'none'; }
}

/* ── Preview de imagen antes de subir ── */
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}

/* ── Guardar producto (crear o actualizar) ── */
async function guardarProducto() {
    const id   = document.getElementById('prod_id').value;
    const data = new FormData();

    data.append('categoria_id',  document.getElementById('prod_categoria').value);
    data.append('nombre',        document.getElementById('prod_nombre').value.trim());
    data.append('descripcion',   document.getElementById('prod_descripcion').value.trim());
    data.append('precio',        document.getElementById('prod_precio').value);
    data.append('stock',         document.getElementById('prod_stock').value);
    data.append('imagen',        document.getElementById('prod_imagen').value.trim());
    data.append('advertencias',  JSON.stringify(
        document.getElementById('prod_advertencias').value.split(',').map(s => s.trim()).filter(Boolean)
    ));
    data.append('disponible',    document.getElementById('prod_disponible').checked ? 1 : 0);
    data.append('destacado',     document.getElementById('prod_destacado').checked  ? 1 : 0);

    const fileInput = document.getElementById('prod_imagen_file');
    if (fileInput.files.length > 0) data.append('imagen_file', fileInput.files[0]);

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

/* ── Eliminar producto ── */
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
            if (res.ok) { loadSection('productos'); Swal.fire('Eliminado', res.message, 'success'); }
            else { Swal.fire('Error', res.message, 'error'); }
        } catch { Swal.fire('Error', 'No se pudo completar la solicitud.', 'error'); }
    }
}

/* ── Alias de edición ── */
function editarProducto(id) { abrirProductoModal(id); }
