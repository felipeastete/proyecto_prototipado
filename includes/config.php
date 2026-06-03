<?php
// ============================================================
//   CASINO UNIVERSITARIO - CONFIGURACIÓN DE BASE DE DATOS
//   Archivo: includes/config.php
// ============================================================

// ----- Configuración MySQL -----
define('DB_HOST',     'localhost');
define('DB_PORT',     '3306');
define('DB_NAME',     'fastete2025_db1');
define('DB_USER',     'root');
define('DB_PASS',     '');          // Vacío en XAMPP por defecto
define('DB_CHARSET',  'utf8mb4');

// ----- Configuración General Dinámica -----
$settingsFile = __DIR__ . '/settings.json';
$settings = [
    'site_name' => 'fastete2025_db1',
    'site_url' => 'http://localhost/casino_copia',
    'maintenance_mode' => false,
    'contact_email' => 'contacto@casino.cl',
    'open_hours' => 'Lunes a Viernes: 8:00 AM - 6:00 PM',
    'enable_reports' => true
];
if (file_exists($settingsFile)) {
    $loaded = json_decode(file_get_contents($settingsFile), true);
    if (is_array($loaded)) {
        $settings = array_merge($settings, $loaded);
    }
} else {
    file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

define('SITE_NAME',   $settings['site_name']);
define('SITE_URL',    $settings['site_url']);
define('ADMIN_URL',   SITE_URL . '/admin');
define('VERSION',     '1.0.0');

// ----- Zona horaria Chile -----
date_default_timezone_set('America/Santiago');

// ----- Modo debug (desactivar en producción) -----
define('DEBUG_MODE', true);

// ============================================================
//   FUNCIÓN: Obtener conexión PDO
// ============================================================
function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        // Crear tabla historial_precios si no existe
        $pdo->exec("CREATE TABLE IF NOT EXISTS historial_precios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            producto_id INT NOT NULL,
            precio_anterior INT NOT NULL,
            precio_nuevo INT NOT NULL,
            admin_id INT DEFAULT NULL,
            motivo VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
            FOREIGN KEY (admin_id) REFERENCES administradores(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            die('<div style="font-family:monospace;background:#fee;padding:20px;border:2px solid red;">
                <strong>❌ Error de conexión a MySQL:</strong><br>' . htmlspecialchars($e->getMessage()) . '<br><br>
                <strong>Verifica:</strong><ul>
                <li>XAMPP está corriendo (Apache + MySQL)</li>
                <li>La base de datos <strong>' . DB_NAME . '</strong> fue importada</li>
                <li>El usuario <strong>' . DB_USER . '</strong> tiene permisos</li>
                </ul></div>');
        } else {
            die('Error de conexión. Contacta al administrador.');
        }
    }
    return $pdo;
}

// ============================================================
//   HELPERS GENERALES
// ============================================================

/** Formatea precio en pesos chilenos: 4200 → $4.200 */
function formatPrecio(int $precio): string {
    return '$' . number_format($precio, 0, ',', '.');
}

/** Escapa HTML para evitar XSS */
function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redirige a una URL */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/** Retorna clase CSS según stock */
function stockClase(int $stock): string {
    if ($stock === 0)  return 'agotado';
    if ($stock <= 5)   return 'stock-bajo';
    return '';
}

/** Retorna texto de stock */
function stockTexto(int $stock): string {
    if ($stock === 0)  return 'Agotado';
    if ($stock <= 5)   return 'Últimas ' . $stock . ' unidades';
    return '';
}

/** Parsea advertencias JSON */
function parseAdvertencias(?string $json): array {
    if (!$json) return [];
    $arr = json_decode($json, true);
    return is_array($arr) ? $arr : [];
}

// Polyfill para PHP < 8.0
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
