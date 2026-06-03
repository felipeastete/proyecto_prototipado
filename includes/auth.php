<?php
// ============================================================
//   CASINO UNIVERSITARIO - SESIONES Y AUTH ADMIN
//   Archivo: includes/auth.php
// ============================================================

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name('casino_admin_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

/** Verifica si hay sesión activa. Redirige al login si no. */
function requireAuth(): void {
    if (empty($_SESSION['admin_id'])) {
        redirect(ADMIN_URL . '/login.php');
    }
}

/** Retorna el admin actual o null */
function currentAdmin(): ?array {
    if (empty($_SESSION['admin_id'])) return null;
    return [
        'id'     => $_SESSION['admin_id'],
        'nombre' => $_SESSION['admin_nombre'] ?? 'Admin',
    ];
}

/** Intenta hacer login. Retorna true si OK. */
function doLogin(string $username, string $password): bool {
    $db   = getDB();
    $stmt = $db->prepare("SELECT id, username, password, nombre FROM administradores WHERE username = :u AND activo = 1 LIMIT 1");
    $stmt->execute([':u' => $username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password'])) {
        return false;
    }

    // Actualizar último login
    $db->prepare("UPDATE administradores SET ultimo_login = NOW() WHERE id = :id")
       ->execute([':id' => $admin['id']]);

    // Guardar sesión
    // Nota: se asignan tanto admin_id como id para compatibilidad
    $_SESSION['admin_id']     = $admin['id'];
    $_SESSION['id']           = $admin['id'];   // alias estándar requerido
    $_SESSION['admin_nombre'] = $admin['nombre'];
    $_SESSION['nombre']       = $admin['nombre']; // alias estándar
    session_regenerate_id(true);
    return true;
}

/** Cierra la sesión del admin */
function doLogout(): void {
    $_SESSION = [];
    session_destroy();
    redirect(ADMIN_URL . '/login.php');
}
