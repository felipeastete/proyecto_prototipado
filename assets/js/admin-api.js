/**
 * admin-api.js
 * Helpers compartidos por todos los módulos JS del panel admin.
 * - callApi()     → wrapper de fetch a la API
 * - formatPrecio() → formato de precio en pesos CLP
 * - escHtml()     → escape HTML para prevenir XSS
 */

'use strict';

const API_BASE = '../api/api.php';

/**
 * Realiza una petición a la API del sistema.
 * @param {string} action  - Acción de la API (parámetro GET ?action=)
 * @param {string} method  - Método HTTP: 'GET' | 'POST'
 * @param {Object|FormData|null} body - Cuerpo de la petición
 * @returns {Promise<Object>} Respuesta JSON parseada
 */
async function callApi(action, method = 'GET', body = null) {
    const url = `${API_BASE}?action=${action}`;
    let opts = { method };

    if (body) {
        if (body instanceof FormData) {
            opts.body = body;
        } else {
            opts.headers = { 'Content-Type': 'application/json' };
            opts.body = JSON.stringify(body);
        }
    }

    const res = await fetch(url, opts);
    return res.json();
}

/**
 * Formatea un número como precio en pesos chilenos.
 * @param {number|string} valor
 * @returns {string} Ej: '$4.200'
 */
function formatPrecio(valor) {
    return '$' + parseInt(valor).toLocaleString('es-CL');
}

/**
 * Escapa caracteres especiales HTML para prevenir XSS.
 * @param {string|null|undefined} str
 * @returns {string}
 */
function escHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
