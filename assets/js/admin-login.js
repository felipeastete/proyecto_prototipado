/**
 * admin-login.js
 * Scripts de la página de login (admin/login.php):
 * - Toggle mostrar/ocultar contraseña
 * - Validación client-side del formulario
 * - Spinner en botón de submit
 * - Auto-cerrar alerta de error tras 6 segundos
 */

'use strict';

/* ── Toggle Mostrar/Ocultar contraseña ── */
const togglePassBtn = document.getElementById('togglePass');
const passInput     = document.getElementById('password');
const toggleIcon    = document.getElementById('toggleIcon');

togglePassBtn?.addEventListener('click', () => {
    const isText       = passInput.type === 'text';
    passInput.type     = isText ? 'password' : 'text';
    toggleIcon.className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
});

/* ── Validación del lado del cliente ── */
const loginForm = document.getElementById('loginForm');
const btnLogin  = document.getElementById('btnLogin');

loginForm?.addEventListener('submit', function(e) {
    let valid = true;

    const user    = document.getElementById('username').value.trim();
    const pass    = document.getElementById('password').value;
    const userErr = document.getElementById('usernameError');
    const passErr = document.getElementById('passwordError');

    // Ocultar errores previos
    userErr.style.display = 'none';
    passErr.style.display = 'none';

    if (!user) {
        userErr.style.display = 'block';
        document.getElementById('username').focus();
        valid = false;
    }
    if (!pass) {
        passErr.style.display = 'block';
        if (valid) document.getElementById('password').focus();
        valid = false;
    }

    if (!valid) {
        e.preventDefault();
        return;
    }

    // Mostrar spinner mientras procesa
    btnLogin.classList.add('loading');
    btnLogin.disabled = true;
});

/* ── Auto-cerrar alerta de error tras 6 segundos ── */
const alertEl = document.getElementById('alertError');
if (alertEl) {
    setTimeout(() => {
        alertEl.style.transition = 'opacity 0.5s';
        alertEl.style.opacity    = '0';
        setTimeout(() => alertEl.remove(), 500);
    }, 6000);
}
