<?php
// ============================================================
//   PANEL ADMINISTRADOR - LOGIN
//   Archivo: admin/login.php
// ============================================================
require_once __DIR__ . '/../includes/auth.php';

// Si ya está logueado, redirigir al panel
if (!empty($_SESSION['admin_id'])) {
    redirect(ADMIN_URL . '/index.php');
}

// Leer mensajes de error pasados desde validar_login.php
$error = '';
if (!empty($_GET['error'])) {
    $errorCode = $_GET['error'];
    $errores = [
        'credenciales' => 'Usuario o contraseña incorrectos.',
        'vacio'        => 'Por favor completa todos los campos.',
        'bloqueado'    => 'Esta cuenta está desactivada. Contacta al administrador.',
    ];
    $error = $errores[$errorCode] ?? 'Error de autenticación. Intenta de nuevo.';
}

// Recuperar usuario previo (para no perder lo escrito)
$prevUser = e($_GET['user'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión – <?= SITE_NAME ?> Admin</title>
    <meta name="description" content="Panel de administración del Casino Universitario. Inicia sesión para gestionar productos, menús y más.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:   #a04000;
            --accent:    #d35400;
            --light:     #f39c12;
            --dark:      #1a0a00;
            --card-bg:   rgba(255,255,255,0.97);
            --shadow:    0 32px 80px rgba(100,30,0,0.28);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background:
                radial-gradient(ellipse at 70% 0%,   rgba(243,156,18,0.25)  0%, transparent 60%),
                radial-gradient(ellipse at 20% 100%, rgba(160,64,0,0.35)   0%, transparent 60%),
                linear-gradient(150deg, #1a0900 0%, #3b1200 40%, #5c1e00 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        /* ── Partículas decorativas ── */
        .bg-orbs {
            position: fixed; inset: 0; overflow: hidden; pointer-events: none; z-index: 0;
        }
        .bg-orbs span {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.18;
            animation: floatOrb 14s ease-in-out infinite alternate;
        }
        .bg-orbs span:nth-child(1) { width:500px; height:500px; background:#d35400; top:-100px; right:-80px; animation-delay:0s; }
        .bg-orbs span:nth-child(2) { width:350px; height:350px; background:#f39c12; bottom:-80px; left:-60px; animation-delay:-5s; }
        .bg-orbs span:nth-child(3) { width:280px; height:280px; background:#a04000; top:40%; left:40%; animation-delay:-9s; }
        @keyframes floatOrb {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(20px, 30px) scale(1.08); }
        }

        /* ── Tarjeta principal ── */
        .login-wrapper {
            position: relative; z-index: 1;
            width: 100%; max-width: 440px;
        }

        .login-card {
            background: var(--card-bg);
            border-radius: 28px;
            padding: 2.8rem 2.4rem 2.4rem;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255,255,255,0.3);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Logo / Icono ── */
        .login-logo {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, var(--accent), var(--light));
            border-radius: 22px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.4rem;
            box-shadow: 0 12px 30px -8px rgba(211,84,0,0.55);
            animation: pulseLogo 3s ease-in-out infinite;
        }
        @keyframes pulseLogo {
            0%, 100% { box-shadow: 0 12px 30px -8px rgba(211,84,0,0.55); }
            50%       { box-shadow: 0 16px 40px -6px rgba(243,156,18,0.65); }
        }
        .login-logo i { font-size: 2.2rem; color: #fff; }

        .login-title {
            text-align: center;
            font-size: 1.55rem;
            font-weight: 800;
            color: #1a0900;
            letter-spacing: -0.5px;
            margin-bottom: 0.2rem;
        }
        .login-subtitle {
            text-align: center;
            font-size: 0.9rem;
            color: #888;
            margin-bottom: 2rem;
        }

        /* ── Alerta de error ── */
        .alert-login {
            border-radius: 14px;
            padding: 0.9rem 1.1rem;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.4rem;
            animation: shake 0.5s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60%  { transform: translateX(-5px); }
            40%, 80%  { transform: translateX(5px); }
        }

        /* ── Labels ── */
        label.form-label {
            font-weight: 700;
            font-size: 0.85rem;
            color: #444;
            margin-bottom: 0.4rem;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Inputs ── */
        .input-group .input-group-text {
            background: #f8f4f0;
            border: 2px solid #e8ddd5;
            border-right: none;
            color: var(--accent);
            border-radius: 12px 0 0 12px;
            padding: 0 1rem;
            font-size: 1rem;
        }
        .input-group .form-control {
            border: 2px solid #e8ddd5;
            border-left: none;
            border-radius: 0 12px 12px 0;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #f8f4f0;
        }
        .input-group .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(211,84,0,0.12);
            background: #fff;
            outline: none;
        }
        .input-group .form-control:focus + .input-group-text,
        .input-group:focus-within .input-group-text {
            border-color: var(--accent);
            background: #fff5ef;
        }

        /* ── Botón principal ── */
        .btn-login {
            width: 100%;
            padding: 0.9rem;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            font-family: inherit;
            letter-spacing: 0.3px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--light) 100%);
            color: #fff;
            box-shadow: 0 10px 30px -8px rgba(211,84,0,0.5);
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s, filter 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.6rem;
        }
        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 16px 40px -8px rgba(211,84,0,0.6);
            filter: brightness(1.05);
        }
        .btn-login:active:not(:disabled) {
            transform: translateY(0);
        }
        .btn-login:disabled {
            opacity: 0.75;
            cursor: not-allowed;
        }

        /* ── Spinner dentro del botón ── */
        .btn-spinner { display: none; }
        .btn-login.loading .btn-text    { display: none; }
        .btn-login.loading .btn-spinner { display: inline-block; }

        /* ── Divisor ── */
        .divider {
            display: flex; align-items: center; gap: 0.8rem;
            margin: 1.4rem 0 1.2rem;
            color: #bbb;
            font-size: 0.8rem;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e5e5;
        }

        /* ── Info de credenciales ── */
        .creds-hint {
            text-align: center;
            font-size: 0.82rem;
            color: #999;
        }
        .creds-hint code {
            color: var(--accent);
            font-weight: 700;
            background: rgba(211,84,0,0.08);
            padding: 0.1rem 0.4rem;
            border-radius: 6px;
        }

        /* ── Volver al sitio ── */
        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            margin-top: 1.6rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover { color: #fff; }

        /* ── Toggle contraseña ── */
        .toggle-pass {
            position: absolute;
            right: 0.8rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #aaa;
            cursor: pointer;
            font-size: 1rem;
            padding: 0;
            transition: color 0.2s;
            z-index: 5;
        }
        .toggle-pass:hover { color: var(--accent); }
        .pass-wrapper { position: relative; }
        .pass-wrapper .form-control { padding-right: 2.8rem; }

        @media (max-width: 480px) {
            .login-card { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

<!-- Orbes decorativos de fondo -->
<div class="bg-orbs" aria-hidden="true">
    <span></span><span></span><span></span>
</div>

<div class="login-wrapper">
    <div class="login-card">

        <!-- Logo -->
        <div class="login-logo" aria-hidden="true">
            <i class="bi bi-shop"></i>
        </div>

        <h1 class="login-title"><?= SITE_NAME ?></h1>
        <p class="login-subtitle">Panel de Administración</p>

        <!-- Alerta de error -->
        <?php if ($error): ?>
        <div class="alert alert-danger alert-login" role="alert" id="alertError">
            <i class="bi bi-exclamation-circle-fill fs-5" aria-hidden="true"></i>
            <span><?= e($error) ?></span>
        </div>
        <?php endif; ?>

        <!-- Formulario → procesa validar_login.php -->
        <form method="POST" action="validar_login.php" id="loginForm" novalidate autocomplete="off">
            <!-- Token CSRF -->
            <?php
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
            ?>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <!-- Campo: Usuario -->
            <div class="mb-3">
                <label for="username" class="form-label">
                    <i class="bi bi-person me-1"></i>Usuario
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        placeholder="Ingresa tu usuario"
                        value="<?= $prevUser ?>"
                        required
                        autofocus
                        autocomplete="username"
                        maxlength="80"
                    >
                </div>
                <div class="invalid-feedback" id="usernameError" style="display:none; color:#dc3545; font-size:0.82rem; margin-top:0.3rem;">
                    El campo usuario es obligatorio.
                </div>
            </div>

            <!-- Campo: Contraseña -->
            <div class="mb-1">
                <label for="password" class="form-label">
                    <i class="bi bi-lock me-1"></i>Contraseña
                </label>
                <div class="input-group pass-wrapper">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                        maxlength="255"
                    >
                    <button type="button" class="toggle-pass" id="togglePass" title="Mostrar/ocultar contraseña" aria-label="Mostrar contraseña">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
                <div class="invalid-feedback" id="passwordError" style="display:none; color:#dc3545; font-size:0.82rem; margin-top:0.3rem;">
                    El campo contraseña es obligatorio.
                </div>
            </div>

            <!-- Botón Iniciar Sesión -->
            <button type="submit" class="btn-login" id="btnLogin">
                <span class="btn-text">
                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                </span>
                <span class="btn-spinner">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Verificando...
                </span>
            </button>
        </form>

        <!-- Sugerencia de credenciales por defecto -->
        <div class="divider"><span>acceso por defecto</span></div>
        <p class="creds-hint">
            Usuario: <code>admin</code> &nbsp;|&nbsp; Contraseña: <code>password</code>
        </p>

    </div><!-- /.login-card -->

    <!-- Enlace volver al sitio -->
    <a href="<?= SITE_URL ?>" class="back-link">
        <i class="bi bi-arrow-left-circle"></i> Volver al sitio principal
    </a>
</div><!-- /.login-wrapper -->

<script>
// ── Toggle Mostrar/Ocultar contraseña ──────────────────────
const togglePass = document.getElementById('togglePass');
const passInput  = document.getElementById('password');
const toggleIcon = document.getElementById('toggleIcon');

togglePass?.addEventListener('click', () => {
    const isText = passInput.type === 'text';
    passInput.type = isText ? 'password' : 'text';
    toggleIcon.className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
});

// ── Validación del lado del cliente ───────────────────────
const form    = document.getElementById('loginForm');
const btnLogin = document.getElementById('btnLogin');

form.addEventListener('submit', function(e) {
    let valid = true;

    const user = document.getElementById('username').value.trim();
    const pass = document.getElementById('password').value;
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

// ── Auto-cerrar alerta de error tras 6 segundos ───────────
const alertEl = document.getElementById('alertError');
if (alertEl) {
    setTimeout(() => {
        alertEl.style.transition = 'opacity 0.5s';
        alertEl.style.opacity = '0';
        setTimeout(() => alertEl.remove(), 500);
    }, 6000);
}
</script>
</body>
</html>
