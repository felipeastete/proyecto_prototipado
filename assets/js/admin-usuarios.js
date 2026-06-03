/**
 * admin-usuarios.js
 * Módulo CRUD de Usuarios / Administradores del panel admin.
 * Depende de: admin-api.js (callApi, escHtml)
 */

'use strict';

/* ── Cargar lista de usuarios ── */
async function loadUsuarios(container) {
    const res = await callApi('admin_listar_usuarios');
    if (!res.ok) {
        container.innerHTML = `<div class="alert alert-danger">Error cargando usuarios: ${escHtml(res.message)}</div>`;
        return;
    }

    let rows = '';
    res.data.forEach(u => {
        const estadoBadge = u.activo
            ? '<span class="badge bg-light text-success border border-success border-opacity-25 px-2 py-1">Activo</span>'
            : '<span class="badge bg-light text-secondary border px-2 py-1">Inactivo</span>';
        const ultimoLogin = u.ultimo_login
            ? new Date(u.ultimo_login).toLocaleString('es-CL')
            : 'Nunca';

        rows += `
        <tr>
            <td>${u.id}</td>
            <td><strong>${escHtml(u.nombre)}</strong></td>
            <td><code>${escHtml(u.username)}</code></td>
            <td>${escHtml(u.email || '—')}</td>
            <td>${estadoBadge}</td>
            <td><small>${ultimoLogin}</small></td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1 rounded-circle"
                    onclick="editarUsuario(${u.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger rounded-circle"
                    onclick="eliminarUsuario(${u.id}, '${escHtml(u.nombre)}')" title="Eliminar">
                    <i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    });

    container.innerHTML = `
    <div class="mb-3 col-md-4">
        <input type="text" id="busquedaUsuario" class="form-control"
            placeholder="Buscar usuario por nombre o username..." onkeyup="filtrarTablaUsuarios()">
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0" id="tablaUsuarios">
            <thead>
                <tr>
                    <th>ID</th><th>Nombre</th><th>Usuario</th>
                    <th>Email</th><th>Estado</th><th>Último Acceso</th>
                    <th style="width:120px">Acciones</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
    </div>`;
}

/* ── Filtrar tabla de usuarios ── */
function filtrarTablaUsuarios() {
    const term = document.getElementById('busquedaUsuario').value.toLowerCase();
    document.querySelectorAll('#tablaUsuarios tbody tr').forEach(r => {
        const name     = r.cells[1].innerText.toLowerCase();
        const username = r.cells[2].innerText.toLowerCase();
        r.style.display = (name.includes(term) || username.includes(term)) ? '' : 'none';
    });
}

/* ── Abrir modal de usuario (crear o editar) ── */
function abrirUsuarioModal(id = null) {
    document.getElementById('usuarioForm').reset();
    document.getElementById('us_id').value = '';
    document.getElementById('usuarioModalTitle').innerText = id ? 'Editar Administrador' : 'Nuevo Administrador';

    const passRequired = document.getElementById('us_pass_required');
    const passHelp     = document.getElementById('us_pass_help');

    if (id) {
        passRequired.style.display = 'none';
        passHelp.style.display     = 'block';
        cargarUsuarioParaEditar(id);
    } else {
        passRequired.style.display = 'inline';
        passHelp.style.display     = 'none';
    }
    new bootstrap.Modal(document.getElementById('usuarioModal')).show();
}

/* ── Cargar datos del usuario en el formulario de edición ── */
async function cargarUsuarioParaEditar(id) {
    const res = await callApi('admin_listar_usuarios');
    if (!res.ok) return;
    const user = res.data.find(u => u.id == id);
    if (!user) return;

    document.getElementById('us_id').value       = user.id;
    document.getElementById('us_nombre').value   = user.nombre;
    document.getElementById('us_username').value = user.username;
    document.getElementById('us_email').value    = user.email || '';
    document.getElementById('us_activo').checked = user.activo == 1;
}

/* ── Guardar usuario (crear o actualizar) ── */
async function guardarUsuario() {
    const id   = document.getElementById('us_id').value;
    const data = {
        nombre:   document.getElementById('us_nombre').value.trim(),
        username: document.getElementById('us_username').value.trim(),
        email:    document.getElementById('us_email').value.trim(),
        password: document.getElementById('us_password').value,
        activo:   document.getElementById('us_activo').checked ? 1 : 0
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
        } else { Swal.fire('Error', res.message, 'error'); }
    } catch { Swal.fire('Error', 'No se pudo guardar el usuario.', 'error'); }
}

/* ── Eliminar usuario ── */
async function eliminarUsuario(id, nombre) {
    const confirm = await Swal.fire({
        title: '¿Eliminar administrador?',
        text: `¿Estás seguro de eliminar a "${nombre}" permanentemente?`,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#e74c3c', confirmButtonText: 'Sí, eliminar'
    });
    if (!confirm.isConfirmed) return;
    try {
        const res = await callApi('admin_eliminar_usuario', 'POST', { id });
        if (res.ok) { loadSection('usuarios'); Swal.fire('Eliminado', res.message, 'success'); }
        else { Swal.fire('Error', res.message, 'error'); }
    } catch { Swal.fire('Error', 'No se pudo eliminar el administrador.', 'error'); }
}

/* ── Alias de edición ── */
function editarUsuario(id) { abrirUsuarioModal(id); }
