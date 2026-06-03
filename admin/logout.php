<?php
// ============================================================
//   PANEL ADMINISTRADOR - CERRAR SESIÓN
//   Archivo: admin/logout.php
// ============================================================
require_once __DIR__ . '/../includes/auth.php';

// Destruir sesión y redirigir al login
doLogout();
