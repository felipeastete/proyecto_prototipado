/**
 * admin-reportes.js
 * Módulo de Reportes de Precios e Historial de Variación del panel admin.
 * Depende de: admin-api.js (callApi, formatPrecio, escHtml)
 */

'use strict';

/* ══════════════════════════════════════════════
   CARGAR SECCIÓN REPORTES
══════════════════════════════════════════════ */
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
                <button class="btn btn-outline-danger btn-sm" onclick="exportarReportesPDF()">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF</button>
                <button class="btn btn-outline-success btn-sm" onclick="exportarReportesExcel()">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel</button>
                <button class="btn btn-outline-secondary btn-sm" onclick="imprimirReportesTable()">
                    <i class="bi bi-printer me-1"></i>Imprimir</button>
            </div>
        </div>`;

    let rows = '';
    res.data.forEach(r => {
        const estadoClass = r.estado == 'pendiente' ? 'bg-warning text-dark'
                          : r.estado == 'revisado'  ? 'bg-info text-dark' : 'bg-success';
        const resolverBtn = r.estado === 'pendiente'
            ? `<button class="btn btn-sm btn-outline-success me-1 rounded-circle"
                onclick="resolverReporte(${r.id}, ${r.precio_real}, '${escHtml(r.producto_nombre)}')"
                title="Resolver"><i class="bi bi-check-lg"></i></button>`
            : '';
        rows += `
        <tr data-date="${r.created_at.substring(0,10)}">
            <td>${r.id}</td>
            <td><strong>${escHtml(r.producto_nombre)}</strong></td>
            <td class="text-danger">${formatPrecio(r.precio_sitio)}</td>
            <td class="text-success fw-bold">${formatPrecio(r.precio_real)}</td>
            <td><small>${escHtml(r.comentarios || '—')}</small></td>
            <td><span class="badge ${estadoClass} px-2 py-1">${r.estado}</span></td>
            <td><small class="text-secondary">${new Date(r.created_at).toLocaleString('es-CL')}</small></td>
            <td class="no-export">
                ${resolverBtn}
                <button class="btn btn-sm btn-outline-primary me-1 rounded-circle"
                    onclick="editarReporte(${r.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger rounded-circle"
                    onclick="eliminarReporte(${r.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    });

    container.innerHTML = `
    <ul class="nav nav-pills mb-4" id="reportesTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#user-reports" type="button">
                Reportes de Usuarios</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#price-history" type="button"
                onclick="loadHistorialPreciosTab()">
                Historial de Variación de Precios</button>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="user-reports" role="tabpanel">
            ${dateRangeFilterHtml}
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0" id="tablaReportesUsuarios">
                    <thead>
                        <tr>
                            <th>ID</th><th>Producto</th><th>Precio Sitio</th><th>Precio Real</th>
                            <th>Comentarios</th><th>Estado</th><th>Fecha</th>
                            <th class="no-export" style="width:160px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="price-history" role="tabpanel">
            <div id="priceHistoryContainer">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div> Cargando historial...</div>
            </div>
        </div>
    </div>`;

    setTimeout(filtrarRangoReportes, 20);
}

/* ══════════════════════════════════════════════
   FILTRO POR RANGO DE FECHAS
══════════════════════════════════════════════ */
function filtrarRangoReportes() {
    const filterSelect = document.getElementById('filtroRangoReportes');
    if (!filterSelect) return;
    const range = filterSelect.value;
    const rows  = document.querySelectorAll('#tablaReportesUsuarios tbody tr');
    const hoy   = new Date();

    rows.forEach(row => {
        const rowDateStr = row.dataset.date;
        if (!rowDateStr) return;
        const rowDate = new Date(rowDateStr);
        let match = false;

        if (range === 'todo') {
            match = true;
        } else if (range === 'dia') {
            match = rowDateStr === hoy.toISOString().substring(0, 10);
        } else if (range === 'semana') {
            const diff   = hoy.getDate() - hoy.getDay() + (hoy.getDay() === 0 ? -6 : 1);
            const monday = new Date(hoy); monday.setDate(diff); monday.setHours(0,0,0,0);
            match = rowDate >= monday && rowDate <= new Date();
        } else if (range === 'mes') {
            match = rowDate.getMonth() === hoy.getMonth() && rowDate.getFullYear() === hoy.getFullYear();
        } else if (range === 'anio') {
            match = rowDate.getFullYear() === hoy.getFullYear();
        }
        row.style.display = match ? '' : 'none';
    });
}

/* ══════════════════════════════════════════════
   EXPORTAR PDF
══════════════════════════════════════════════ */
function exportarReportesPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.text('Casino Universitario - Reportes de Errores de Precios', 14, 15);

    const table   = document.getElementById('tablaReportesUsuarios');
    const ths     = table.querySelectorAll('thead th');
    const headers = [];
    for (let i = 0; i < ths.length - 1; i++) headers.push(ths[i].innerText);

    const data = [];
    table.querySelectorAll('tbody tr').forEach(tr => {
        if (tr.style.display === 'none') return;
        const row = [];
        const tds = tr.querySelectorAll('td');
        for (let i = 0; i < tds.length - 1; i++) row.push(tds[i].innerText);
        data.push(row);
    });

    doc.autoTable({ head: [headers], body: data, startY: 20, theme: 'striped', headStyles: { fillColor: [160,64,0] } });
    doc.save('reportes_precios_usuarios.pdf');
}

/* ══════════════════════════════════════════════
   EXPORTAR EXCEL
══════════════════════════════════════════════ */
function exportarReportesExcel() {
    const table   = document.getElementById('tablaReportesUsuarios');
    const ths     = table.querySelectorAll('thead th');
    const headers = [];
    for (let i = 0; i < ths.length - 1; i++) headers.push(ths[i].innerText);

    const rows = [headers];
    table.querySelectorAll('tbody tr').forEach(tr => {
        if (tr.style.display === 'none') return;
        const row = [];
        const tds = tr.querySelectorAll('td');
        for (let i = 0; i < tds.length - 1; i++) row.push(tds[i].innerText);
        rows.push(row);
    });

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(rows);
    XLSX.utils.book_append_sheet(wb, ws, 'Reportes');
    XLSX.writeFile(wb, 'reportes_precios_usuarios.xlsx');
}

/* ══════════════════════════════════════════════
   IMPRIMIR
══════════════════════════════════════════════ */
function imprimirReportesTable() { imprimirReportes('Reportes de Precios de Usuarios', 'tablaReportesUsuarios'); }

function imprimirReportes(titulo, tableId) {
    const el  = document.getElementById(tableId);
    const win = window.open('', '', 'height=700,width=900');
    win.document.write('<html><head><title>Imprimir Reporte</title>');
    win.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
    win.document.write('<style>body{padding:20px;} th,td{padding:8px;border:1px solid #dee2e6;} th{background-color:#f8f9fa;} .no-export{display:none;}</style>');
    win.document.write('</head><body>');
    win.document.write(`<h3 class="mb-4">${titulo}</h3>`);

    const clone = el.cloneNode(true);
    clone.querySelectorAll('.no-export, th:last-child, td:last-child').forEach(e => e.remove());
    win.document.write(clone.outerHTML);
    win.document.write('</body></html>');
    win.document.close();
    setTimeout(() => { win.print(); win.close(); }, 500);
}

/* ══════════════════════════════════════════════
   HISTORIAL DE PRECIOS
══════════════════════════════════════════════ */
async function loadHistorialPreciosTab() {
    const container = document.getElementById('priceHistoryContainer');
    container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div> Cargando historial...</div>';

    const res = await callApi('admin_listar_historial_precios');
    if (!res.ok) {
        container.innerHTML = `<div class="alert alert-danger">Error: ${escHtml(res.message)}</div>`;
        return;
    }

    let rows = '';
    res.data.forEach(h => {
        rows += `
        <tr>
            <td>${h.id}</td>
            <td><strong>${escHtml(h.producto_nombre)}</strong></td>
            <td class="text-secondary">${formatPrecio(h.precio_anterior)}</td>
            <td class="text-success fw-bold">${formatPrecio(h.precio_nuevo)}</td>
            <td><small><i class="bi bi-person me-1"></i>${escHtml(h.admin_nombre || 'Sistema')}</small></td>
            <td><small>${escHtml(h.motivo || '—')}</small></td>
            <td><small class="text-secondary">${new Date(h.created_at).toLocaleString('es-CL')}</small></td>
            <td>
                <button class="btn btn-sm btn-outline-danger rounded-circle"
                    onclick="eliminarHistorialPrecio(${h.id})" title="Eliminar">
                    <i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    });

    container.innerHTML = `
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h6 class="fw-bold mb-0 text-muted"><i class="bi bi-clock-history me-1"></i>Historial de Cambios Registrados</h6>
        <button class="btn btn-warning btn-sm text-dark fw-bold" onclick="abrirHistorialPrecioModal()">
            <i class="bi bi-plus-lg"></i> Registrar Cambio de Precio</button>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0" id="tablaHistorialPrecios">
            <thead>
                <tr>
                    <th>ID</th><th>Producto</th><th>Precio Anterior</th><th>Precio Nuevo</th>
                    <th>Modificado Por</th><th>Motivo / Razón</th><th>Fecha</th><th style="width:100px">Acción</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
    </div>`;
}

/* ── Resolver reporte ── */
async function resolverReporte(id, precioReal, prodNombre) {
    const confirm = await Swal.fire({
        title: 'Resolver Reporte',
        text: `¿Deseas marcar este reporte como resuelto? También puedes actualizar el precio de "${prodNombre}" a ${formatPrecio(precioReal)} automáticamente.`,
        icon: 'question',
        showCancelButton: true, showDenyButton: true,
        confirmButtonText: 'Sí, resolver y actualizar precio',
        denyButtonText: 'Solo resolver',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#27ae60', denyButtonColor: '#2980b9'
    });
    if (confirm.isDismissed) return;

    try {
        if (confirm.isConfirmed) {
            const prodRes = await callApi('admin_listar_productos');
            if (prodRes.ok) {
                const prod = prodRes.data.find(p => p.nombre.toLowerCase().trim() === prodNombre.toLowerCase().trim());
                if (prod) {
                    await callApi('admin_crear_historial_precio', 'POST', {
                        producto_id: prod.id, precio_nuevo: precioReal,
                        motivo: `Resuelto reporte de precio de usuario (Reporte #${id})`
                    });
                }
            }
        }
        const res = await callApi('admin_marcar_reporte', 'POST', { id, estado: 'resuelto' });
        if (res.ok) { loadSection('reportes'); Swal.fire('Éxito', 'El reporte ha sido resuelto.', 'success'); }
        else { Swal.fire('Error', res.message, 'error'); }
    } catch { Swal.fire('Error', 'No se pudo resolver el reporte.', 'error'); }
}

/* ── Editar reporte ── */
async function editarReporte(id) {
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
}

/* ── Guardar reporte ── */
async function guardarReporte() {
    const id   = document.getElementById('rep_id').value;
    const data = {
        id:              parseInt(id),
        producto_nombre: document.getElementById('rep_producto_nombre').value.trim(),
        precio_sitio:    parseInt(document.getElementById('rep_precio_sitio').value),
        precio_real:     parseInt(document.getElementById('rep_precio_real').value),
        comentarios:     document.getElementById('rep_comentarios').value.trim(),
        estado:          document.getElementById('rep_estado').value
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
        } else { Swal.fire('Error', res.message, 'error'); }
    } catch { Swal.fire('Error', 'No se pudo guardar el reporte.', 'error'); }
}

/* ── Eliminar reporte ── */
async function eliminarReporte(id) {
    const confirm = await Swal.fire({
        title: '¿Estás seguro?', text: '¿Eliminar este reporte permanentemente?',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#e74c3c', confirmButtonText: 'Sí, eliminar'
    });
    if (!confirm.isConfirmed) return;
    try {
        const res = await callApi('admin_eliminar_reporte', 'POST', { id });
        if (res.ok) { loadSection('reportes'); Swal.fire('Eliminado', res.message, 'success'); }
        else { Swal.fire('Error', res.message, 'error'); }
    } catch { Swal.fire('Error', 'No se pudo eliminar el reporte.', 'error'); }
}

/* ── Abrir modal historial de precio ── */
async function abrirHistorialPrecioModal() {
    document.getElementById('historialPrecioForm').reset();
    const prodRes = await callApi('admin_listar_productos');
    const select  = document.getElementById('hp_producto_id');
    if (prodRes.ok && select) {
        select.innerHTML = prodRes.data
            .map(p => `<option value="${p.id}">${escHtml(p.nombre)} (${formatPrecio(p.precio)})</option>`)
            .join('');
    }
    new bootstrap.Modal(document.getElementById('historialPrecioModal')).show();
}

/* ── Guardar historial de precio ── */
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
        } else { Swal.fire('Error', res.message, 'error'); }
    } catch { Swal.fire('Error', 'No se pudo registrar la variación de precio.', 'error'); }
}

/* ── Eliminar historial de precio ── */
async function eliminarHistorialPrecio(id) {
    const confirm = await Swal.fire({
        title: '¿Eliminar log?', text: '¿Estás seguro de eliminar este registro del historial?',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#e74c3c', confirmButtonText: 'Sí, eliminar'
    });
    if (!confirm.isConfirmed) return;
    try {
        const res = await callApi('admin_eliminar_historial_precio', 'POST', { id });
        if (res.ok) { loadHistorialPreciosTab(); Swal.fire('Eliminado', res.message, 'success'); }
        else { Swal.fire('Error', res.message, 'error'); }
    } catch { Swal.fire('Error', 'No se pudo eliminar el registro.', 'error'); }
}
