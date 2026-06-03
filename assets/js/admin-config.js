/**
 * admin-config.js
 * Módulo de Configuración del Sitio del panel admin.
 * Depende de: admin-api.js (callApi, escHtml)
 */

'use strict';

/* ── Cargar sección de configuración ── */
async function loadConfiguracion(container) {
    const res = await callApi('admin_obtener_config');
    if (!res.ok) {
        container.innerHTML = `<div class="alert alert-danger">Error cargando configuración: ${escHtml(res.message)}</div>`;
        return;
    }

    const conf = res.data;

    container.innerHTML = `
    <div class="card border-0 shadow-sm p-4 rounded-4 col-lg-8 mx-auto">
        <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-gear-fill me-1"></i> Configuración del Sitio</h5>
        <form id="configForm" onsubmit="event.preventDefault(); guardarConfiguracion();">
            <div class="mb-3">
                <label class="form-label fw-bold">Nombre del Casino (SITE_NAME)</label>
                <input type="text" id="conf_site_name" class="form-control"
                    value="${escHtml(conf.site_name)}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">URL del Sitio (SITE_URL)</label>
                <input type="url" id="conf_site_url" class="form-control"
                    value="${escHtml(conf.site_url)}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Email de Contacto</label>
                <input type="email" id="conf_contact_email" class="form-control"
                    value="${escHtml(conf.contact_email)}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Horario de Atención</label>
                <input type="text" id="conf_open_hours" class="form-control"
                    value="${escHtml(conf.open_hours)}" required>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch"
                    id="conf_maintenance_mode" ${conf.maintenance_mode ? 'checked' : ''}>
                <label class="form-check-label fw-bold" for="conf_maintenance_mode">
                    Modo Mantenimiento (Activar modo de construcción)
                </label>
            </div>
            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" role="switch"
                    id="conf_enable_reports" ${conf.enable_reports ? 'checked' : ''}>
                <label class="form-check-label fw-bold" for="conf_enable_reports">
                    Permitir Reportes de Precios de Usuarios
                </label>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary btn-action px-5">
                    <i class="bi bi-save me-1"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>`;
}

/* ── Guardar configuración ── */
async function guardarConfiguracion() {
    const data = {
        site_name:        document.getElementById('conf_site_name').value.trim(),
        site_url:         document.getElementById('conf_site_url').value.trim(),
        contact_email:    document.getElementById('conf_contact_email').value.trim(),
        open_hours:       document.getElementById('conf_open_hours').value.trim(),
        maintenance_mode: document.getElementById('conf_maintenance_mode').checked,
        enable_reports:   document.getElementById('conf_enable_reports').checked
    };

    try {
        const res = await callApi('admin_guardar_config', 'POST', data);
        if (res.ok) { Swal.fire('Configuración Guardada', res.message, 'success'); }
        else { Swal.fire('Error', res.message, 'error'); }
    } catch {
        Swal.fire('Error', 'No se pudo guardar la configuración.', 'error');
    }
}
