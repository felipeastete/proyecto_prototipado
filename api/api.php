<?php
// ============================================================
//   CASINO UNIVERSITARIO - API COMPLETA (pública + admin)
//   Archivo: api/api.php
// ============================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Iniciar sesión para verificar admin si es necesario
if (session_status() === PHP_SESSION_NONE) {
    session_name('casino_admin_session');
    session_start();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Función para verificar admin (solo para acciones privadas)
function requireAdminApi() {
    if (empty($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'message' => 'No autorizado. Inicia sesión como administrador.']);
        exit;
    }
}

function procesarSubidaImagen($fileKey, $carpeta) {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES[$fileKey]['tmp_name'];
        $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $filename = uniqid('img_') . '.' . $ext;
            $path = __DIR__ . '/../assets/img/' . $carpeta . '/' . $filename;
            if (move_uploaded_file($tmp, $path)) {
                return $carpeta . '/' . $filename;
            }
        }
    }
    return null;
}

try {
    switch ($action) {

        // ========== ACCIONES PÚBLICAS ==========
        case 'productos':
            $db = getDB();
            $where  = ['p.disponible = 1'];
            $params = [];

            if (!empty($_GET['categoria']) && $_GET['categoria'] !== 'todas') {
                $where[]  = 'c.slug = :slug';
                $params[':slug'] = $_GET['categoria'];
            }
            if (!empty($_GET['buscar'])) {
                $where[]  = '(p.nombre LIKE :q OR p.descripcion LIKE :q)';
                $params[':q'] = '%' . $_GET['buscar'] . '%';
            }

            $sql = "SELECT p.id, p.nombre, p.descripcion, p.precio, p.stock,
                           p.imagen, p.advertencias, p.disponible, p.destacado,
                           c.nombre AS categoria_nombre, c.slug AS categoria_slug, c.icono
                    FROM productos p
                    JOIN categorias c ON p.categoria_id = c.id
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY c.orden, p.nombre";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            foreach ($rows as &$r) {
                $r['precio_fmt']    = formatPrecio((int)$r['precio']);
                $r['advertencias']  = parseAdvertencias($r['advertencias']);
                $r['stock_texto']   = stockTexto((int)$r['stock']);
                $r['stock_clase']   = stockClase((int)$r['stock']);
                $r['imagen_url']    = $r['imagen'] ? (str_starts_with($r['imagen'], 'http') ? $r['imagen'] : SITE_URL . '/assets/img/' . $r['imagen']) : null;
            }
            echo json_encode(['ok' => true, 'data' => $rows]);
            break;

        case 'categorias':
            $db = getDB();
            $rows = $db->query("SELECT * FROM categorias WHERE activa = 1 ORDER BY orden")->fetchAll();
            echo json_encode(['ok' => true, 'data' => $rows]);
            break;

        case 'menu_dia':
            $db    = getDB();
            $fecha = $_GET['fecha'] ?? date('Y-m-d');
            $stmt  = $db->prepare("SELECT * FROM menu_dia WHERE fecha = :f LIMIT 1");
            $stmt->execute([':f' => $fecha]);
            $menu  = $stmt->fetch();
            if ($menu) {
                $menu['precio_fmt'] = formatPrecio((int)$menu['precio']);
                $menu['imagen_url'] = !empty($menu['imagen']) ? (str_starts_with($menu['imagen'], 'http') ? $menu['imagen'] : SITE_URL . '/assets/img/' . $menu['imagen']) : null;
                echo json_encode(['ok' => true, 'data' => $menu]);
            } else {
                $stmt = $db->prepare("SELECT * FROM menu_dia WHERE fecha >= :f ORDER BY fecha LIMIT 1");
                $stmt->execute([':f' => $fecha]);
                $menu = $stmt->fetch();
                if ($menu) {
                    $menu['precio_fmt'] = formatPrecio((int)$menu['precio']);
                    $menu['imagen_url'] = !empty($menu['imagen']) ? (str_starts_with($menu['imagen'], 'http') ? $menu['imagen'] : SITE_URL . '/assets/img/' . $menu['imagen']) : null;
                    echo json_encode(['ok' => true, 'data' => $menu, 'fallback' => true]);
                } else {
                    echo json_encode(['ok' => false, 'message' => 'No hay menú disponible']);
                }
            }
            break;

        case 'reporte':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $body    = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $nombre  = trim($body['producto'] ?? '');
            $pSitio  = (int)($body['precio_sitio'] ?? 0);
            $pReal   = (int)($body['precio_real']  ?? 0);
            $coment  = trim($body['comentarios']   ?? '');
            if (!$nombre || $pSitio <= 0 || $pReal <= 0) {
                echo json_encode(['ok' => false, 'message' => 'Completa todos los campos requeridos.']);
                break;
            }
            $db   = getDB();
            $stmt = $db->prepare("INSERT INTO reportes_precios (producto_nombre, precio_sitio, precio_real, comentarios) VALUES (:n, :ps, :pr, :c)");
            $stmt->execute([':n' => $nombre, ':ps' => $pSitio, ':pr' => $pReal, ':c' => $coment ?: null]);
            echo json_encode(['ok' => true, 'message' => '¡Reporte enviado! Gracias por tu colaboración.']);
            break;

        // ========== ACCIONES DE ADMIN (requieren autenticación) ==========

        // ---- CRUD Productos ----
        case 'admin_listar_productos':
            requireAdminApi();
            $db = getDB();
            $sql = "SELECT p.*, c.nombre as categoria_nombre, c.slug as categoria_slug 
                    FROM productos p 
                    JOIN categorias c ON p.categoria_id = c.id 
                    ORDER BY c.orden, p.nombre";
            $rows = $db->query($sql)->fetchAll();
            foreach ($rows as &$r) {
                $r['precio_fmt'] = formatPrecio((int)$r['precio']);
                $r['advertencias_arr'] = parseAdvertencias($r['advertencias']);
            }
            echo json_encode(['ok' => true, 'data' => $rows]);
            break;

        case 'admin_crear_producto':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) $input = $_POST;
            $categoria_id = (int)($input['categoria_id'] ?? 0);
            $nombre = trim($input['nombre'] ?? '');
            $descripcion = trim($input['descripcion'] ?? '');
            $precio = (int)($input['precio'] ?? 0);
            $stock = (int)($input['stock'] ?? 0);
            $imagen = procesarSubidaImagen('imagen_file', 'productos') ?? trim($input['imagen'] ?? '');
            $advRaw = $input['advertencias'] ?? null;
            $advertencias = $advRaw ? (is_string($advRaw) ? $advRaw : json_encode($advRaw)) : null;
            $disponible = (int)($input['disponible'] ?? 1);
            $destacado = (int)($input['destacado'] ?? 0);

            if ($categoria_id <= 0 || empty($nombre) || $precio < 0) {
                echo json_encode(['ok' => false, 'message' => 'Datos inválidos']);
                break;
            }
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, imagen, advertencias, disponible, destacado) 
                                   VALUES (:cat, :nom, :desc, :pre, :stock, :img, :adv, :disp, :dest)");
            $stmt->execute([
                ':cat' => $categoria_id, ':nom' => $nombre, ':desc' => $descripcion,
                ':pre' => $precio, ':stock' => $stock, ':img' => $imagen,
                ':adv' => $advertencias, ':disp' => $disponible, ':dest' => $destacado
            ]);
            echo json_encode(['ok' => true, 'message' => 'Producto creado correctamente', 'id' => $db->lastInsertId()]);
            break;

        case 'admin_actualizar_producto':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) $input = $_POST;
            $id = (int)($input['id'] ?? 0);
            if (!$id) {
                echo json_encode(['ok' => false, 'message' => 'ID inválido']);
                break;
            }
            $categoria_id = (int)($input['categoria_id'] ?? 0);
            $nombre = trim($input['nombre'] ?? '');
            $descripcion = trim($input['descripcion'] ?? '');
            $precio = (int)($input['precio'] ?? 0);
            $stock = (int)($input['stock'] ?? 0);
            $imagen = procesarSubidaImagen('imagen_file', 'productos') ?? trim($input['imagen'] ?? '');
            $advRaw = $input['advertencias'] ?? null;
            $advertencias = $advRaw ? (is_string($advRaw) ? $advRaw : json_encode($advRaw)) : null;
            $disponible = (int)($input['disponible'] ?? 1);
            $destacado = (int)($input['destacado'] ?? 0);

            $db = getDB();

            // Obtener stock y precio anterior para el historial
            $stmtPrev = $db->prepare("SELECT stock, precio FROM productos WHERE id = :id");
            $stmtPrev->execute([':id' => $id]);
            $prevRow = $stmtPrev->fetch();
            $prevStock = ($prevRow !== false) ? (int)$prevRow['stock'] : null;
            $prevPrecio = ($prevRow !== false) ? (int)$prevRow['precio'] : null;

            $stmt = $db->prepare("UPDATE productos SET categoria_id=:cat, nombre=:nom, descripcion=:desc, precio=:pre, stock=:stock, imagen=:img, advertencias=:adv, disponible=:disp, destacado=:dest WHERE id=:id");
            $stmt->execute([
                ':id' => $id, ':cat' => $categoria_id, ':nom' => $nombre, ':desc' => $descripcion,
                ':pre' => $precio, ':stock' => $stock, ':img' => $imagen,
                ':adv' => $advertencias, ':disp' => $disponible, ':dest' => $destacado
            ]);

            // Registrar en el historial si el stock cambió
            if ($prevStock !== null && $prevStock !== $stock) {
                $stmtHist = $db->prepare("INSERT INTO historial_stock (producto_id, stock_anterior, stock_nuevo, admin_id, motivo) VALUES (:prod_id, :old, :new, :admin_id, :motivo)");
                $stmtHist->execute([
                    ':prod_id' => $id,
                    ':old' => $prevStock,
                    ':new' => $stock,
                    ':admin_id' => $_SESSION['admin_id'] ?? null,
                    ':motivo' => 'Edición desde el panel de administración'
                ]);
            }

            // Registrar en el historial si el precio cambió
            if ($prevPrecio !== null && $prevPrecio !== $precio) {
                $stmtHistPrecio = $db->prepare("INSERT INTO historial_precios (producto_id, precio_anterior, precio_nuevo, admin_id, motivo) VALUES (:prod_id, :old, :new, :admin_id, :motivo)");
                $stmtHistPrecio->execute([
                    ':prod_id' => $id,
                    ':old' => $prevPrecio,
                    ':new' => $precio,
                    ':admin_id' => $_SESSION['admin_id'] ?? null,
                    ':motivo' => 'Edición desde el panel de administración'
                ]);
            }

            echo json_encode(['ok' => true, 'message' => 'Producto actualizado']);
            break;

        case 'admin_restock_producto':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);
            $cantidad = (int)($input['cantidad'] ?? 0);
            if (!$id || $cantidad <= 0) {
                echo json_encode(['ok' => false, 'message' => 'Datos inválidos o cantidad no válida']);
                break;
            }
            $db = getDB();
            // Obtener stock anterior
            $stmtPrev = $db->prepare("SELECT stock, nombre FROM productos WHERE id = :id");
            $stmtPrev->execute([':id' => $id]);
            $prod = $stmtPrev->fetch();
            if (!$prod) {
                echo json_encode(['ok' => false, 'message' => 'Producto no encontrado']);
                break;
            }
            $prevStock = (int)$prod['stock'];
            $newStock = $prevStock + $cantidad;

            // Actualizar stock
            $stmt = $db->prepare("UPDATE productos SET stock = :stock WHERE id = :id");
            $stmt->execute([':stock' => $newStock, ':id' => $id]);

            // Registrar en historial_stock
            $stmtHist = $db->prepare("INSERT INTO historial_stock (producto_id, stock_anterior, stock_nuevo, admin_id, motivo) VALUES (:prod_id, :old, :new, :admin_id, :motivo)");
            $stmtHist->execute([
                ':prod_id' => $id,
                ':old' => $prevStock,
                ':new' => $newStock,
                ':admin_id' => $_SESSION['admin_id'] ?? null,
                ':motivo' => 'Reposición rápida desde el dashboard'
            ]);

            echo json_encode(['ok' => true, 'message' => "Se añadieron {$cantidad} unidades a '{$prod['nombre']}' correctamente."]);
            break;

        case 'admin_eliminar_producto':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);
            if (!$id) {
                echo json_encode(['ok' => false, 'message' => 'ID inválido']);
                break;
            }
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM productos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['ok' => true, 'message' => 'Producto eliminado']);
            break;

        // ---- CRUD Menú del Día ----
        case 'admin_listar_menu':
            requireAdminApi();
            $db = getDB();
            $fechaIni = $_GET['fecha_ini'] ?? date('Y-m-d', strtotime('-30 days'));
            $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d', strtotime('+60 days'));
            $stmt = $db->prepare("SELECT * FROM menu_dia WHERE fecha BETWEEN :ini AND :fin ORDER BY fecha");
            $stmt->execute([':ini' => $fechaIni, ':fin' => $fechaFin]);
            $rows = $stmt->fetchAll();
            foreach ($rows as &$r) {
                $r['precio_fmt'] = formatPrecio((int)$r['precio']);
                $r['imagen_url'] = !empty($r['imagen']) ? (str_starts_with($r['imagen'], 'http') ? $r['imagen'] : SITE_URL . '/assets/img/' . $r['imagen']) : null;
            }
            echo json_encode(['ok' => true, 'data' => $rows]);
            break;

        case 'admin_crear_menu':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) $input = $_POST;
            $fecha = $input['fecha'] ?? '';
            $plato_nombre = trim($input['plato_nombre'] ?? '');
            $plato_desc = trim($input['plato_desc'] ?? '');
            $acompanamiento = trim($input['acompanamiento'] ?? '');
            $ensalada = trim($input['ensalada'] ?? '');
            $jugo = trim($input['jugo'] ?? '');
            $postre = trim($input['postre'] ?? '');
            $fruta = trim($input['fruta'] ?? '');
            $precio = (int)($input['precio'] ?? 0);
            $disponible_hasta = $input['disponible_hasta'] ?? '15:00:00';
            $imagen = procesarSubidaImagen('imagen_file', 'menus') ?? trim($input['imagen'] ?? '');

            if (!$fecha || !$plato_nombre || $precio <= 0) {
                echo json_encode(['ok' => false, 'message' => 'Datos incompletos']);
                break;
            }
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO menu_dia (fecha, plato_nombre, plato_desc, acompanamiento, ensalada, jugo, postre, fruta, precio, disponible_hasta, imagen) 
                                   VALUES (:f, :pn, :pd, :ac, :en, :ju, :po, :fr, :pr, :dh, :img)");
            $stmt->execute([
                ':f' => $fecha, ':pn' => $plato_nombre, ':pd' => $plato_desc,
                ':ac' => $acompanamiento, ':en' => $ensalada, ':ju' => $jugo,
                ':po' => $postre, ':fr' => $fruta, ':pr' => $precio, ':dh' => $disponible_hasta, ':img' => $imagen
            ]);
            echo json_encode(['ok' => true, 'message' => 'Menú agregado']);
            break;

        case 'admin_actualizar_menu':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) $input = $_POST;
            $id = (int)($input['id'] ?? 0);
            if (!$id) {
                echo json_encode(['ok' => false, 'message' => 'ID inválido']);
                break;
            }
            $fecha = $input['fecha'] ?? '';
            $plato_nombre = trim($input['plato_nombre'] ?? '');
            $plato_desc = trim($input['plato_desc'] ?? '');
            $acompanamiento = trim($input['acompanamiento'] ?? '');
            $ensalada = trim($input['ensalada'] ?? '');
            $jugo = trim($input['jugo'] ?? '');
            $postre = trim($input['postre'] ?? '');
            $fruta = trim($input['fruta'] ?? '');
            $precio = (int)($input['precio'] ?? 0);
            $disponible_hasta = $input['disponible_hasta'] ?? '15:00:00';
            $imagen = procesarSubidaImagen('imagen_file', 'menus') ?? trim($input['imagen'] ?? '');

            $db = getDB();
            $stmt = $db->prepare("UPDATE menu_dia SET fecha=:f, plato_nombre=:pn, plato_desc=:pd, acompanamiento=:ac, ensalada=:en, jugo=:ju, postre=:po, fruta=:fr, precio=:pr, disponible_hasta=:dh, imagen=:img WHERE id=:id");
            $stmt->execute([
                ':id' => $id, ':f' => $fecha, ':pn' => $plato_nombre, ':pd' => $plato_desc,
                ':ac' => $acompanamiento, ':en' => $ensalada, ':ju' => $jugo,
                ':po' => $postre, ':fr' => $fruta, ':pr' => $precio, ':dh' => $disponible_hasta, ':img' => $imagen
            ]);
            echo json_encode(['ok' => true, 'message' => 'Menú actualizado']);
            break;

        case 'admin_eliminar_menu':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);
            if (!$id) {
                echo json_encode(['ok' => false, 'message' => 'ID inválido']);
                break;
            }
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM menu_dia WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['ok' => true, 'message' => 'Menú eliminado']);
            break;

        // ---- Reportes (admin) ----
        case 'admin_listar_reportes':
            requireAdminApi();
            $db = getDB();
            $stmt = $db->query("SELECT * FROM reportes_precios ORDER BY created_at DESC");
            $rows = $stmt->fetchAll();
            echo json_encode(['ok' => true, 'data' => $rows]);
            break;

        case 'admin_marcar_reporte':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) $input = $_POST;
            $id = (int)($input['id'] ?? 0);
            $estado = $input['estado'] ?? 'revisado';
            if (!$id) {
                echo json_encode(['ok' => false, 'message' => 'ID inválido']);
                break;
            }
            $db = getDB();
            $stmt = $db->prepare("UPDATE reportes_precios SET estado = :estado WHERE id = :id");
            $stmt->execute([':estado' => $estado, ':id' => $id]);
            echo json_encode(['ok' => true, 'message' => 'Estado actualizado']);
            break;

        case 'admin_editar_reporte':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) $input = $_POST;
            $id = (int)($input['id'] ?? 0);
            $pReal = (int)($input['precio_real'] ?? 0);
            $estado = $input['estado'] ?? 'revisado';
            if (!$id) {
                echo json_encode(['ok' => false, 'message' => 'ID inválido']);
                break;
            }
            $db = getDB();
            $stmt = $db->prepare("UPDATE reportes_precios SET precio_real = :pr, estado = :estado WHERE id = :id");
            $stmt->execute([':pr' => $pReal, ':estado' => $estado, ':id' => $id]);
            echo json_encode(['ok' => true, 'message' => 'Reporte actualizado']);
            break;

        case 'admin_eliminar_reporte':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) $input = $_POST;
            $id = (int)($input['id'] ?? 0);
            if (!$id) {
                echo json_encode(['ok' => false, 'message' => 'ID inválido']);
                break;
            }
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM reportes_precios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['ok' => true, 'message' => 'Reporte eliminado']);
            break;

        case 'admin_estadisticas_reportes':
            requireAdminApi();
            $db = getDB();
            $rango = $_GET['rango'] ?? 'mes';
            $whereDate = "";
            if ($rango === 'dia') $whereDate = "DATE(created_at) = CURDATE()";
            else if ($rango === 'semana') $whereDate = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
            else if ($rango === 'mes') $whereDate = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
            else if ($rango === 'anio') $whereDate = "YEAR(created_at) = YEAR(CURDATE())";
            else $whereDate = "1=1";

            $stmt = $db->query("SELECT * FROM reportes_precios WHERE $whereDate ORDER BY created_at DESC");
            $reportes = $stmt->fetchAll();
            echo json_encode(['ok' => true, 'data' => $reportes]);
            break;

        // ---- CRUD Usuarios (Administradores) ----
        case 'admin_listar_usuarios':
            requireAdminApi();
            $db = getDB();
            $rows = $db->query("SELECT id, username, nombre, email, activo, ultimo_login, created_at FROM administradores ORDER BY nombre")->fetchAll();
            echo json_encode(['ok' => true, 'data' => $rows]);
            break;

        case 'admin_crear_usuario':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) $input = $_POST;
            $username = trim($input['username'] ?? '');
            $nombre = trim($input['nombre'] ?? '');
            $email = trim($input['email'] ?? '');
            $password = $input['password'] ?? '';
            $activo = (int)($input['activo'] ?? 1);

            if (empty($username) || empty($nombre) || empty($password)) {
                echo json_encode(['ok' => false, 'message' => 'El usuario, nombre y contraseña son obligatorios']);
                break;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['ok' => false, 'message' => 'Email inválido']);
                break;
            }

            $db = getDB();
            $check = $db->prepare("SELECT id FROM administradores WHERE username = :u");
            $check->execute([':u' => $username]);
            if ($check->fetch()) {
                echo json_encode(['ok' => false, 'message' => 'El nombre de usuario ya está registrado']);
                break;
            }

            $passHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO administradores (username, password, nombre, email, activo) VALUES (:u, :p, :n, :e, :a)");
            $stmt->execute([':u' => $username, ':p' => $passHash, ':n' => $nombre, ':e' => $email, ':a' => $activo]);
            echo json_encode(['ok' => true, 'message' => 'Administrador creado correctamente']);
            break;

        case 'admin_actualizar_usuario':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) $input = $_POST;
            $id = (int)($input['id'] ?? 0);
            if (!$id) {
                echo json_encode(['ok' => false, 'message' => 'ID inválido']);
                break;
            }
            $username = trim($input['username'] ?? '');
            $nombre = trim($input['nombre'] ?? '');
            $email = trim($input['email'] ?? '');
            $password = $input['password'] ?? '';
            $activo = (int)($input['activo'] ?? 1);

            if (empty($username) || empty($nombre)) {
                echo json_encode(['ok' => false, 'message' => 'El usuario y nombre son obligatorios']);
                break;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['ok' => false, 'message' => 'Email inválido']);
                break;
            }

            $db = getDB();
            $check = $db->prepare("SELECT id FROM administradores WHERE username = :u AND id != :id");
            $check->execute([':u' => $username, ':id' => $id]);
            if ($check->fetch()) {
                echo json_encode(['ok' => false, 'message' => 'El nombre de usuario ya está registrado por otro administrador']);
                break;
            }

            if (!empty($password)) {
                $passHash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE administradores SET username = :u, password = :p, nombre = :n, email = :e, activo = :a WHERE id = :id");
                $stmt->execute([':u' => $username, ':p' => $passHash, ':n' => $nombre, ':e' => $email, ':a' => $activo, ':id' => $id]);
            } else {
                $stmt = $db->prepare("UPDATE administradores SET username = :u, nombre = :n, email = :e, activo = :a WHERE id = :id");
                $stmt->execute([':u' => $username, ':n' => $nombre, ':e' => $email, ':a' => $activo, ':id' => $id]);
            }

            if ($id === (int)($_SESSION['admin_id'] ?? 0)) {
                $_SESSION['admin_nombre'] = $nombre;
                $_SESSION['nombre'] = $nombre;
            }

            echo json_encode(['ok' => true, 'message' => 'Administrador actualizado correctamente']);
            break;

        case 'admin_eliminar_usuario':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);
            if (!$id) {
                echo json_encode(['ok' => false, 'message' => 'ID inválido']);
                break;
            }
            if ($id === (int)($_SESSION['admin_id'] ?? 0)) {
                echo json_encode(['ok' => false, 'message' => 'No puedes eliminarte a ti mismo']);
                break;
            }

            $db = getDB();
            $stmt = $db->prepare("DELETE FROM administradores WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['ok' => true, 'message' => 'Administrador eliminado']);
            break;

        // ---- Configuración ----
        case 'admin_obtener_config':
            requireAdminApi();
            $settingsFile = __DIR__ . '/../includes/settings.json';
            $data = [
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
                    $data = array_merge($data, $loaded);
                }
            }
            echo json_encode(['ok' => true, 'data' => $data]);
            break;

        case 'admin_guardar_config':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) $input = $_POST;

            $settingsFile = __DIR__ . '/../includes/settings.json';
            $data = [
                'site_name' => trim($input['site_name'] ?? 'fastete2025_db1'),
                'site_url' => trim($input['site_url'] ?? 'http://localhost/casino_copia'),
                'maintenance_mode' => (bool)($input['maintenance_mode'] ?? false),
                'contact_email' => trim($input['contact_email'] ?? 'contacto@casino.cl'),
                'open_hours' => trim($input['open_hours'] ?? 'Lunes a Viernes: 8:00 AM - 6:00 PM'),
                'enable_reports' => (bool)($input['enable_reports'] ?? true)
            ];

            if (empty($data['site_name']) || empty($data['site_url'])) {
                echo json_encode(['ok' => false, 'message' => 'El nombre y la URL del sitio son obligatorios']);
                break;
            }

            file_put_contents($settingsFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            echo json_encode(['ok' => true, 'message' => 'Configuración guardada correctamente']);
            break;

        // ---- Historial de Precios ----
        case 'admin_listar_historial_precios':
            requireAdminApi();
            $db = getDB();
            $rows = $db->query("SELECT h.*, p.nombre as producto_nombre, a.nombre as admin_nombre 
                                FROM historial_precios h 
                                JOIN productos p ON h.producto_id = p.id 
                                LEFT JOIN administradores a ON h.admin_id = a.id 
                                ORDER BY h.created_at DESC")->fetchAll();
            echo json_encode(['ok' => true, 'data' => $rows]);
            break;

        case 'admin_crear_historial_precio':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) $input = $_POST;
            $producto_id = (int)($input['producto_id'] ?? 0);
            $precio_nuevo = (int)($input['precio_nuevo'] ?? 0);
            $motivo = trim($input['motivo'] ?? 'Cambio manual de precio');

            if ($producto_id <= 0 || $precio_nuevo <= 0) {
                echo json_encode(['ok' => false, 'message' => 'Producto o precio no válido']);
                break;
            }

            $db = getDB();
            $stmtPrev = $db->prepare("SELECT precio FROM productos WHERE id = :id");
            $stmtPrev->execute([':id' => $producto_id]);
            $prod = $stmtPrev->fetch();
            if (!$prod) {
                echo json_encode(['ok' => false, 'message' => 'Producto no encontrado']);
                break;
            }
            $precio_anterior = (int)$prod['precio'];

            $stmtUpdate = $db->prepare("UPDATE productos SET precio = :p WHERE id = :id");
            $stmtUpdate->execute([':p' => $precio_nuevo, ':id' => $producto_id]);

            $stmtHist = $db->prepare("INSERT INTO historial_precios (producto_id, precio_anterior, precio_nuevo, admin_id, motivo) VALUES (:prod_id, :old, :new, :admin_id, :motivo)");
            $stmtHist->execute([
                ':prod_id' => $producto_id,
                ':old' => $precio_anterior,
                ':new' => $precio_nuevo,
                ':admin_id' => $_SESSION['admin_id'] ?? null,
                ':motivo' => $motivo
            ]);

            echo json_encode(['ok' => true, 'message' => 'Precio actualizado e historial registrado correctamente']);
            break;

        case 'admin_eliminar_historial_precio':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? 0);
            if (!$id) {
                echo json_encode(['ok' => false, 'message' => 'ID inválido']);
                break;
            }
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM historial_precios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['ok' => true, 'message' => 'Registro de historial eliminado']);
            break;

        // ---- Reportes de Precios en Detalle ----
        case 'admin_actualizar_reporte_completo':
            requireAdminApi();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) $input = $_POST;
            $id = (int)($input['id'] ?? 0);
            $producto_nombre = trim($input['producto_nombre'] ?? '');
            $precio_sitio = (int)($input['precio_sitio'] ?? 0);
            $precio_real = (int)($input['precio_real'] ?? 0);
            $comentarios = trim($input['comentarios'] ?? '');
            $estado = trim($input['estado'] ?? 'pendiente');

            if (!$id || empty($producto_nombre) || $precio_sitio <= 0 || $precio_real <= 0) {
                echo json_encode(['ok' => false, 'message' => 'Datos incompletos o inválidos']);
                break;
            }

            $db = getDB();
            $stmt = $db->prepare("UPDATE reportes_precios SET producto_nombre = :n, precio_sitio = :ps, precio_real = :pr, comentarios = :c, estado = :e WHERE id = :id");
            $stmt->execute([
                ':id' => $id,
                ':n' => $producto_nombre,
                ':ps' => $precio_sitio,
                ':pr' => $precio_real,
                ':c' => $comentarios ?: null,
                ':e' => $estado
            ]);

            echo json_encode(['ok' => true, 'message' => 'Reporte de precio actualizado correctamente']);
            break;

        // ---- Estadísticas Completas de Dashboard ----
        case 'admin_estadisticas_dashboard':
            requireAdminApi();
            $db = getDB();

            $stmtPopular = $db->query("SELECT id, nombre, precio, stock, (100 - stock) as ventas_mock, (precio * (100 - stock)) as ingresos_mock 
                                       FROM productos 
                                       ORDER BY ventas_mock DESC LIMIT 5");
            $popularProds = $stmtPopular->fetchAll();

            $stmtMenus = $db->query("SELECT id, plato_nombre, precio, (precio * 12) as ingresos_mock 
                                     FROM menu_dia 
                                     ORDER BY id DESC LIMIT 5");
            $popularMenus = $stmtMenus->fetchAll();

            $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            $ingresosMensuales = [];
            $mesActual = (int)date('m');
            for ($i = 0; $i < 12; $i++) {
                if ($i < $mesActual) {
                    $ingresosMensuales[] = [
                        'mes' => $meses[$i],
                        'ingresos' => rand(1500000, 3200000)
                    ];
                } else {
                    $ingresosMensuales[] = [
                        'mes' => $meses[$i],
                        'ingresos' => 0
                    ];
                }
            }

            $stmtVarPrecios = $db->query("SELECT h.*, p.nombre as producto_nombre 
                                          FROM historial_precios h 
                                          JOIN productos p ON h.producto_id = p.id 
                                          ORDER BY h.created_at DESC LIMIT 5");
            $variacionPrecios = $stmtVarPrecios->fetchAll();

            echo json_encode([
                'ok' => true,
                'data' => [
                    'productos_populares' => $popularProds,
                    'menus_populares' => $popularMenus,
                    'ingresos_mensuales' => $ingresosMensuales,
                    'variaciones_precios' => $variacionPrecios
                ]
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Acción no reconocida']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'      => false,
        'message' => DEBUG_MODE ? $e->getMessage() : 'Error del servidor'
    ]);
}