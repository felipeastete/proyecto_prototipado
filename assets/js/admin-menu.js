/**
 * admin-menu.js
 * Módulo CRUD del Menú del Día del panel admin.
 * Depende de: admin-api.js (callApi, formatPrecio, escHtml)
 */

'use strict';

/* ── Cargar lista de menús ── */
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
                    <th>Fecha</th><th>Plato Principal</th>
                    <th>Precio</th><th>Componentes</th>
                    <th style="width:120px">Acciones</th>
                </tr>
            </thead>
            <tbody>`;

    res.data.forEach(m => {
        const comps = [
            m.acompanamiento ? `🍚 ${m.acompanamiento}` : '',
            m.ensalada       ? `🥗 ${m.ensalada}`       : '',
            m.jugo           ? `🧃 ${m.jugo}`           : '',
            m.postre         ? `🍮 ${m.postre}`         : '',
            m.fruta          ? `🍎 ${m.fruta}`          : ''
        ].filter(Boolean).join(' | ');

        const descRow = m.plato_desc
            ? `<br><small class="text-muted">${escHtml(m.plato_desc)}</small>`
            : '';

        html += `
        <tr>
            <td><strong>${escHtml(m.fecha)}</strong></td>
            <td>
                <strong class="text-dark">${escHtml(m.plato_nombre)}</strong>${descRow}
            </td>
            <td><strong>${formatPrecio(m.precio)}</strong></td>
            <td><small class="text-secondary">${comps || '—'}</small></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1 rounded-circle"
                    onclick="editarMenu(${m.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger rounded-circle"
                    onclick="eliminarMenu(${m.id}, '${escHtml(m.plato_nombre)}')" title="Eliminar"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    });

    html += `</tbody></table></div>`;
    container.innerHTML = html;
}

/* ── Abrir modal de menú (crear o editar) ── */
function abrirMenuModal(id = null) {
    document.getElementById('menuForm').reset();
    document.getElementById('menu_id').value = '';
    document.getElementById('menuModalTitle').innerText = id ? 'Editar Menú del Día' : 'Nuevo Menú del Día';
    if (id) cargarMenuParaEditar(id);
    new bootstrap.Modal(document.getElementById('menuModal')).show();
}

/* ── Cargar datos del menú en el formulario de edición ── */
async function cargarMenuParaEditar(id) {
    const res = await callApi('admin_listar_menu');
    if (!res.ok) return;
    const menu = res.data.find(m => m.id == id);
    if (!menu) return;

    document.getElementById('menu_id').value              = menu.id;
    document.getElementById('menu_fecha').value           = menu.fecha;
    document.getElementById('menu_plato_nombre').value    = menu.plato_nombre;
    document.getElementById('menu_plato_desc').value      = menu.plato_desc || '';
    document.getElementById('menu_acompanamiento').value  = menu.acompanamiento || '';
    document.getElementById('menu_ensalada').value        = menu.ensalada || '';
    document.getElementById('menu_jugo').value            = menu.jugo || '';
    document.getElementById('menu_postre').value          = menu.postre || '';
    document.getElementById('menu_fruta').value           = menu.fruta || '';
    document.getElementById('menu_precio').value          = menu.precio;
    document.getElementById('menu_disponible_hasta').value = menu.disponible_hasta.substring(0, 5);
    document.getElementById('menu_imagen').value          = menu.imagen || '';
    document.getElementById('menu_imagen_file').value     = '';

    const preview = document.getElementById('menu_preview');
    if (menu.imagen_url) { preview.src = menu.imagen_url; preview.style.display = 'block'; }
    else { preview.style.display = 'none'; }
}

/* ── Guardar menú (crear o actualizar) ── */
async function guardarMenu() {
    const id   = document.getElementById('menu_id').value;
    const data = new FormData();

    data.append('fecha',             document.getElementById('menu_fecha').value);
    data.append('plato_nombre',      document.getElementById('menu_plato_nombre').value.trim());
    data.append('plato_desc',        document.getElementById('menu_plato_desc').value.trim());
    data.append('acompanamiento',    document.getElementById('menu_acompanamiento').value.trim());
    data.append('ensalada',          document.getElementById('menu_ensalada').value.trim());
    data.append('jugo',              document.getElementById('menu_jugo').value.trim());
    data.append('postre',            document.getElementById('menu_postre').value.trim());
    data.append('fruta',             document.getElementById('menu_fruta').value.trim());
    data.append('precio',            document.getElementById('menu_precio').value);
    data.append('disponible_hasta',  document.getElementById('menu_disponible_hasta').value);
    data.append('imagen',            document.getElementById('menu_imagen').value.trim());

    const fileInput = document.getElementById('menu_imagen_file');
    if (fileInput.files.length > 0) data.append('imagen_file', fileInput.files[0]);

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

/* ── Eliminar menú ── */
async function eliminarMenu(id, nombre) {
    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Eliminar menú "${nombre}" de la programación?`,
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
            if (res.ok) { loadSection('menu'); Swal.fire('Eliminado', res.message, 'success'); }
            else { Swal.fire('Error', res.message, 'error'); }
        } catch { Swal.fire('Error', 'No se pudo eliminar el menú.', 'error'); }
    }
}

/* ── Alias de edición ── */
function editarMenu(id) { abrirMenuModal(id); }
