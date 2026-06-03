<?php
// ============================================================
//   PANEL ADMINISTRADOR - VALIDACIÓN DE LOGIN
//   Archivo: admin/validar_login.php
//
//   Solo acepta peticiones POST. Valida credenciales contra
//   la tabla `administradores`, crea sesión segura y redirige.
// ============================================================
require_once __DIR__ . '/../includes/auth.php';

// 1. Solo se permite método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(ADMIN_URL . '/login.php');
}

// 2. Validar token CSRF
$csrfToken     = $_POST['csrf_token'] ?? '';
$csrfExpected  = $_SESSION['csrf_token'] ?? '';

if (empty($csrfToken) || !hash_equals($csrfExpected, $csrfToken)) {
    // Token inválido o manipulado
    redirect(ADMIN_URL . '/login.php?error=credenciales');
}

// 3. Limpiar y recoger inputs
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// 4. Validar que los campos no estén vacíos
if ($username === '' || $password === '') {
    redirect(ADMIN_URL . '/login.php?error=vacio&user=' . urlencode($username));
}

// 5. Longitudes máximas para evitar ataques
if (strlen($username) > 80 || strlen($password) > 255) {
    redirect(ADMIN_URL . '/login.php?error=credenciales');
}

// 6. Intentar login usando la función de auth.php (prepared statements + password_verify)
if (doLogin($username, $password)) {
    // Regenerar ID de sesión para prevenir Session Fixation
    session_regenerate_id(true);

    // Invalidar el token CSRF usado (uno por uso)
    unset($_SESSION['csrf_token']);

    // Redirigir al dashboard
    redirect(ADMIN_URL . '/index.php');
} else {
    // Credenciales incorrectas: volver al login con mensaje de error
    redirect(ADMIN_URL . '/login.php?error=credenciales&user=' . urlencode($username));
}
