/**
 * admin-dashboard-page.js
 * Scripts del dashboard independiente (admin/dashboard.php):
 * - Toggle del sidebar mobile
 * - Confirmación de logout con SweetAlert2
 */

'use strict';

/* ── Toggle Sidebar (mobile) ── */
const sidebar   = document.getElementById('mainSidebar');
const overlay   = document.getElementById('sidebarOverlay');
const toggleBtn = document.getElementById('sidebarToggle');

function openSidebar()  { sidebar?.classList.add('open');    overlay?.classList.add('active'); }
function closeSidebar() { sidebar?.classList.remove('open'); overlay?.classList.remove('active'); }

toggleBtn?.addEventListener('click', () => {
    sidebar?.classList.contains('open') ? closeSidebar() : openSidebar();
});
overlay?.addEventListener('click', closeSidebar);

/* ── Confirmación de logout ── */
function confirmLogout(e) {
    e.preventDefault();
    const href = e.currentTarget.href || 'logout.php';

    Swal.fire({
        title: '¿Cerrar sesión?',
        text: 'Se cerrará tu sesión de administrador.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-door-open me-1"></i> Sí, salir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d35400',
        cancelButtonColor: '#888',
    }).then(result => {
        if (result.isConfirmed) window.location.href = href;
    });
}

/* ── Botón logout del navbar ── */
document.getElementById('btnLogout')?.addEventListener('click', function(e) {
    e.preventDefault();
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: 'Se cerrará tu sesión de administrador.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d35400',
        cancelButtonColor: '#888',
    }).then(result => {
        if (result.isConfirmed) window.location.href = 'logout.php';
    });
});

/* ── Aplicar confirmLogout a links de logout ── */
document.getElementById('link-logout')?.addEventListener('click', confirmLogout);
document.getElementById('quickLogout')?.addEventListener('click', confirmLogout);
