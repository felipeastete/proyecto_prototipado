/**
 * admin-dashboard.js
 * Módulo del Dashboard: estadísticas, gráficos Chart.js y reposición rápida.
 * Depende de: admin-api.js (callApi, formatPrecio, escHtml)
 */

'use strict';

/**
 * Carga el contenido de la sección Dashboard en el contenedor dado.
 * @param {HTMLElement} container
 */
async function loadDashboard(container) {
    // Llamadas API paralelas para optimizar rendimiento
    const [prodRes, menuRes, reportRes] = await Promise.all([
        callApi('admin_listar_productos'),
        callApi('admin_listar_menu'),
        callApi('admin_listar_reportes')
    ]);

    const totalProd   = prodRes.ok   ? prodRes.data.length   : 0;
    const totalMenu   = menuRes.ok   ? menuRes.data.length   : 0;
    const reportPend  = reportRes.ok ? reportRes.data.filter(r => r.estado === 'pendiente').length : 0;

    // Detectar alertas críticas de stock
    const outOfStockProds = prodRes.ok ? prodRes.data.filter(p => p.stock === 0)                    : [];
    const lowStockProds   = prodRes.ok ? prodRes.data.filter(p => p.stock > 0 && p.stock <= 5)      : [];
    const criticalProds   = [...outOfStockProds, ...lowStockProds];

    // Procesar datos para gráficos por categoría
    const catCounts = {};
    const catStock  = {};
    if (prodRes.ok) {
        prodRes.data.forEach(p => {
            catCounts[p.categoria_nombre] = (catCounts[p.categoria_nombre] || 0) + 1;
            catStock[p.categoria_nombre]  = (catStock[p.categoria_nombre]  || 0) + p.stock;
        });
    }

    // HTML de tarjetas de estadísticas
    const statsHtml = `
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-stats border-0 p-3">
                <div class="d-flex align-items-center">
                    <div class="p-3 rounded-4 me-3" style="background:rgba(211,84,0,0.1);color:#d35400;">
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
                    <div class="p-3 rounded-4 me-3" style="background:rgba(39,174,96,0.1);color:#27ae60;">
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
                    <div class="p-3 rounded-4 me-3" style="background:rgba(231,76,60,0.1);color:#e74c3c;">
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

    <!-- Gráficos -->
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
    </div>`;

    // HTML del widget de reposición rápida
    let restockHtml = '';
    if (criticalProds.length > 0) {
        let rows = '';
        criticalProds.forEach(p => {
            rows += `
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
                    <button class="btn btn-sm btn-outline-success restock-btn rounded-pill px-3 me-2"
                        onclick="restockRapido(${p.id}, 10)"><i class="bi bi-plus-lg"></i> +10</button>
                    <button class="btn btn-sm btn-outline-success restock-btn rounded-pill px-3"
                        onclick="restockRapido(${p.id}, 50)"><i class="bi bi-plus-lg"></i> +50</button>
                </td>
            </tr>`;
        });
        restockHtml = `
        <div class="card border-0 shadow-sm p-4 rounded-4 mt-4">
            <h5 class="fw-bold mb-3 text-danger d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i> Reposición Rápida de Inventario
            </h5>
            <p class="small text-muted mb-3">Productos con quiebre o niveles críticos de stock.</p>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr class="text-muted">
                        <th>Producto</th><th>Stock Actual</th><th>Agregar Unidades</th>
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

    container.innerHTML = statsHtml + restockHtml;

    // Renderizar gráficos con Chart.js
    setTimeout(() => {
        const COLORS = ['#d35400','#f39c12','#27ae60','#2980b9','#8e44ad','#2c3e50','#16a085','#c0392b','#7f8c8d'];

        const ctxCat = document.getElementById('chartCategorias')?.getContext('2d');
        if (ctxCat && Object.keys(catCounts).length > 0) {
            new Chart(ctxCat, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(catCounts),
                    datasets: [{
                        data: Object.values(catCounts),
                        backgroundColor: COLORS,
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
            new Chart(ctxStock, {
                type: 'bar',
                data: {
                    labels: Object.keys(catStock),
                    datasets: [{
                        label: 'Stock Total',
                        data: Object.values(catStock),
                        backgroundColor: 'rgba(211,84,0,0.75)',
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

/**
 * Reposición rápida de stock para un producto.
 * @param {number} id        - ID del producto
 * @param {number} cantidad  - Unidades a agregar
 */
async function restockRapido(id, cantidad) {
    try {
        const res = await callApi('admin_restock_producto', 'POST', { id, cantidad });
        if (res.ok) {
            Swal.fire({ icon: 'success', title: 'Inventario Actualizado', text: res.message, timer: 2000, showConfirmButton: false });
            loadSection(currentSection);
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.message });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error de Red', text: 'No se pudo registrar la reposición rápida.' });
    }
}
