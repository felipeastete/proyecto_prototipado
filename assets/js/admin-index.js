/**
 * admin-index.js
 * Controlador principal del panel SPA (admin/index.php).
 * Gestiona: variables globales, navegación por secciones, carga de categorías
 * y event listeners de preview de imágenes.
 *
 * Depende (en este orden de carga):
 *   1. admin-api.js       (callApi, formatPrecio, escHtml)
 *   2. admin-dashboard.js (loadDashboard, restockRapido)
 *   3. admin-productos.js (loadProductos, abrirProductoModal, guardarProducto, ...)
 *   4. admin-menu.js      (loadMenu, abrirMenuModal, guardarMenu, ...)
 *   5. admin-reportes.js  (loadReportes, ...)
 *   6. admin-usuarios.js  (loadUsuarios, abrirUsuarioModal, ...)
 *   7. admin-config.js    (loadConfiguracion, guardarConfiguracion)
 *   8. admin-index.js     ← este archivo (siempre el último)
 */

'use strict';

/* ══════════════════════════════════════════════
   VARIABLES GLOBALES
══════════════════════════════════════════════ */
let currentSection  = 'dashboard';
let categoriasList  = [];

/* ══════════════════════════════════════════════
   CARGAR CATEGORÍAS AL INICIAR
══════════════════════════════════════════════ */
async function loadCategorias() {
    try {
        const res = await callApi('categorias');
        if (res.ok) categoriasList = res.data;

        const sel = document.getElementById('prod_categoria');
        if (sel) {
            sel.innerHTML = categoriasList
                .map(c => `<option value="${c.id}">${c.nombre}</option>`)
                .join('');
        }
    } catch (e) {
        console.error('Error cargando categorías', e);
    }
}

/* ══════════════════════════════════════════════
   NAVEGACIÓN DEL SIDEBAR
══════════════════════════════════════════════ */
document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();

        // Marcar link activo
        document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
        link.classList.add('active');

        currentSection = link.dataset.section;
        document.getElementById('sectionTitle').innerText = link.innerText.trim();

        // Configurar botón de acción según sección
        _configurarActionBtn(currentSection);

        loadSection(currentSection);
    });
});

/**
 * Configura el botón de acción flotante según la sección activa.
 * @param {string} section
 */
function _configurarActionBtn(section) {
    const actionBtn = document.getElementById('actionBtn');

    if (section === 'productos') {
        actionBtn.style.display = 'block';
        actionBtn.onclick       = () => abrirProductoModal();
        actionBtn.innerHTML     = '<i class="bi bi-plus-lg"></i> Nuevo Producto';
        actionBtn.className     = 'btn btn-primary btn-action';
    } else if (section === 'menu') {
        actionBtn.style.display = 'block';
        actionBtn.onclick       = () => abrirMenuModal();
        actionBtn.innerHTML     = '<i class="bi bi-plus-lg"></i> Nuevo Menú';
        actionBtn.className     = 'btn btn-success btn-action';
    } else if (section === 'usuarios') {
        actionBtn.style.display = 'block';
        actionBtn.onclick       = () => abrirUsuarioModal();
        actionBtn.innerHTML     = '<i class="bi bi-plus-lg"></i> Nuevo Usuario';
        actionBtn.className     = 'btn btn-dark btn-action';
    } else {
        actionBtn.style.display = 'none';
    }
}

/* ══════════════════════════════════════════════
   DESPACHADOR DE SECCIONES
══════════════════════════════════════════════ */
async function loadSection(section) {
    const container = document.getElementById('dynamicView');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div> Cargando...</div>';

    try {
        if      (section === 'dashboard')    await loadDashboard(container);
        else if (section === 'productos')    await loadProductos(container);
        else if (section === 'menu')         await loadMenu(container);
        else if (section === 'reportes')     await loadReportes(container);
        else if (section === 'usuarios')     await loadUsuarios(container);
        else if (section === 'configuracion') await loadConfiguracion(container);
    } catch (err) {
        container.innerHTML = `<div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Error al cargar sección: ${escHtml(err.message)}
        </div>`;
    }
}

/* ══════════════════════════════════════════════
   EVENT LISTENERS: PREVIEW DE IMÁGENES (MODALES)
══════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('input', e => {
        if (e.target.id === 'prod_imagen') {
            const preview = document.getElementById('prod_preview');
            if (preview) {
                preview.src          = e.target.value.trim();
                preview.style.display = e.target.value.trim() ? 'block' : 'none';
            }
        }
        if (e.target.id === 'menu_imagen') {
            const preview = document.getElementById('menu_preview');
            if (preview) {
                preview.src          = e.target.value.trim();
                preview.style.display = e.target.value.trim() ? 'block' : 'none';
            }
        }
    });
});

/* ══════════════════════════════════════════════
   STARTUP: LEER URL HASH Y CARGAR SECCIÓN INICIAL
══════════════════════════════════════════════ */
loadCategorias();

const VALID_SECTIONS = ['dashboard', 'productos', 'menu', 'reportes', 'usuarios', 'configuracion'];
let startupSection = 'dashboard';

if (location.hash) {
    const hash = location.hash.replace('#', '');
    if (VALID_SECTIONS.includes(hash)) {
        startupSection = hash;

        // Marcar sidebar link correcto
        document.querySelectorAll('.sidebar .nav-link').forEach(l => {
            l.classList.toggle('active', l.dataset.section === hash);
        });

        // Actualizar título y botón de acción
        const matchingLink = Array.from(document.querySelectorAll('.sidebar .nav-link'))
            .find(l => l.dataset.section === hash);
        if (matchingLink) {
            document.getElementById('sectionTitle').innerText = matchingLink.innerText.trim();
        }
        _configurarActionBtn(hash);
    }
}

loadSection(startupSection);
