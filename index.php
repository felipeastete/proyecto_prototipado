<?php
// ============================================================
//   CASINO UNIVERSITARIO - PÁGINA PRINCIPAL (PROFESIONAL)
//   Archivo: index.php
// ============================================================
require_once __DIR__ . '/includes/config.php';

// Iniciar sesión para detectar si el admin ya está logueado
if (session_status() === PHP_SESSION_NONE) {
    session_name('casino_admin_session');
    session_start();
}
$adminLogged = !empty($_SESSION['admin_id']);

$db   = getDB();
$hoy  = date('Y-m-d');

// Cargar TODOS los menús de la semana para el carrusel
$stmtMenus = $db->prepare("SELECT * FROM menu_dia WHERE fecha >= CURDATE() - INTERVAL 1 DAY ORDER BY fecha LIMIT 8");
$stmtMenus->execute();
$menusSemana = $stmtMenus->fetchAll();

// Menú de hoy (para el hero principal)
$menuHoy = null;
foreach ($menusSemana as $m) {
    if ($m['fecha'] === $hoy) { $menuHoy = $m; break; }
}
if (!$menuHoy && !empty($menusSemana)) $menuHoy = $menusSemana[0];

// Cargar categorías activas
$categorias = $db->query("SELECT * FROM categorias WHERE activa=1 ORDER BY orden")->fetchAll();

// Día en español
$dias = ['Sunday'=>'Domingo','Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles',
         'Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado'];
$meses = ['January'=>'Enero','February'=>'Febrero','March'=>'Marzo','April'=>'Abril',
          'May'=>'Mayo','June'=>'Junio','July'=>'Julio','August'=>'Agosto',
          'September'=>'Septiembre','October'=>'Octubre','November'=>'Noviembre','December'=>'Diciembre'];
$diaEs  = $dias[date('l')] ?? date('l');
$mesEs  = $meses[date('F')] ?? date('F');
$fechaEs = "$diaEs, " . date('j') . " de $mesEs de " . date('Y');

// Imágenes por categoría (Unsplash temáticas)
$imgCategorias = [
    'bebidas-calientes' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=400&q=80',
    'bebidas-frias'     => 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=400&q=80',
    'jugos'             => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=400&q=80',
    'energeticas'       => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80',
    'desayunos'         => 'https://images.unsplash.com/photo-1533089860892-a9b969df67a3?w=400&q=80',
    'sandwiches'        => 'https://images.unsplash.com/photo-1509722747041-616f39b57569?w=400&q=80',
    'almuerzos'         => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400&q=80',
    'snacks'            => 'https://images.unsplash.com/photo-1621939514649-280e2ee25f60?w=400&q=80',
    'postres'           => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=400&q=80',
];

// Imágenes específicas por producto (nombre → url)
$imgProductos = [
    'Café Expresso'         => 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?w=400&q=80',
    'Café con Leche'        => 'https://images.unsplash.com/photo-1561882468-9110e03e0f78?w=400&q=80',
    'Capuchino'             => 'https://images.unsplash.com/photo-1534778101976-62847782c213?w=400&q=80',
    'Té'                    => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400&q=80',
    'Té con Leche'          => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?w=400&q=80',
    'Leche con Chocolate'   => 'https://images.unsplash.com/photo-1542990253-a781e5585fde?w=400&q=80',
    'Nescafé'               => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=400&q=80',
    'Milo'                  => 'https://images.unsplash.com/photo-1542990253-a781e5585fde?w=400&q=80',
    'Coca-Cola 350 ml'      => 'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=400&q=80',
    'Coca-Cola 500 ml'      => 'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=400&q=80',
    'Sprite 350 ml'         => 'https://images.unsplash.com/photo-1625772299848-391b6a87d7b3?w=400&q=80',
    'Fanta Naranja 350 ml'  => 'https://images.unsplash.com/photo-1625772299848-391b6a87d7b3?w=400&q=80',
    'Agua Mineral 500 ml'   => 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?w=400&q=80',
    'Agua Mineral 1.5 L'    => 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?w=400&q=80',
    'Powerade 500 ml'       => 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?w=400&q=80',
    'Jugo Natural del Día'  => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?w=400&q=80',
    'Limonada Natural'      => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?w=400&q=80',
    'Red Bull 250 ml'       => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80',
    'Monster Energy 473 ml' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80',
    'Completo'              => 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=400&q=80',
    'Tostadas con Palta'    => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=400&q=80',
    'Yogur con Granola'     => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400&q=80',
    'Churrasco'             => 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=400&q=80',
    'Barros Jarpa'          => 'https://images.unsplash.com/photo-1509722747041-616f39b57569?w=400&q=80',
    'Lomito'                => 'https://images.unsplash.com/photo-1509722747041-616f39b57569?w=400&q=80',
    'Menú del Día'          => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400&q=80',
    'Cazuela de Vacuno'     => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400&q=80',
    'Charquicán'            => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400&q=80',
    'Pastel de Papa'        => 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=400&q=80',
    'Tallarines con Salsa'  => 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=400&q=80',
    'Arroz con Pollo'       => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=400&q=80',
    'Milanesa con Papas Fritas' => 'https://images.unsplash.com/photo-1585325701956-60dd9c8399b6?w=400&q=80',
    'Empanada de Pino'      => 'https://images.unsplash.com/photo-1639024471283-03518883512d?w=400&q=80',
    'Empanada de Queso'     => 'https://images.unsplash.com/photo-1639024471283-03518883512d?w=400&q=80',
    'Sopaipillas (3 uds)'   => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80',
    'Pizza Porción'         => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=400&q=80',
    'Porotos con Riendas'   => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400&q=80',
    'Lentejas con Arroz'    => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400&q=80',
    'Galletas Tritón'       => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&q=80',
    'Galletas Oreo 3 pack'  => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&q=80',
    'Papas Fritas Lays 42g' => 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=400&q=80',
    'Doritos Nacho 45g'     => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&q=80',
    'Super 8'               => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&q=80',
    'Chocolate Barra Sahne-Nuss' => 'https://images.unsplash.com/photo-1548907040-4baa42d10919?w=400&q=80',
    'Yogur Soprole 165g'    => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400&q=80',
    'Barra de Cereal'       => 'https://images.unsplash.com/photo-1505576399279-565b52d4ac71?w=400&q=80',
    'Queque de Plátano (porción)' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=400&q=80',
    'Mousse de Chocolate'   => 'https://images.unsplash.com/photo-1541599540903-216a46ab667a?w=400&q=80',
    'Kuchen de Berries'     => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=400&q=80',
    'Flan Casero'           => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400&q=80',
    'Fruta del Día'         => 'https://images.unsplash.com/photo-1519996409144-56c88c4e2a1a?w=400&q=80',
    'Leche Asada'           => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400&q=80',
    'Arroz con Leche'       => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400&q=80',
    'Queque de Limón (porción)' => 'https://images.unsplash.com/photo-1519869325930-281384150729?w=400&q=80',
];

// Imágenes para platos del menú del día
$imgMenuDia = [
    'Cazuela de Vacuno'         => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=600&q=80',
    'Arroz con Pollo'           => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=600&q=80',
    'Charquicán'                => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=600&q=80',
    'Pastel de Papa'            => 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=600&q=80',
    'Tallarines con Salsa Carne'=> 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=600&q=80',
    'Lentejas con Arroz'        => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=600&q=80',
    'Milanesa Napolitana'       => 'https://images.unsplash.com/photo-1585325701956-60dd9c8399b6?w=600&q=80',
    'Porotos con Riendas'       => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=600&q=80',
];
$imgMenuDefault = 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80';

// Exportar datos al JS
// Mapear menús para agregar información necesaria tanto para PHP como para JSON
$menusSemana = array_map(function($m) use ($imgMenuDia, $imgMenuDefault) {
    $fecha = new DateTime($m['fecha']);
    $diaEn = $fecha->format('l');
    $mesEn = $fecha->format('F');
    $diasES = ['Sunday'=>'Domingo','Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles',
               'Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado'];
    $mesesES = ['January'=>'Enero','February'=>'Febrero','March'=>'Marzo','April'=>'Abril',
                'May'=>'Mayo','June'=>'Junio','July'=>'Julio','August'=>'Agosto',
                'September'=>'Septiembre','October'=>'Octubre','November'=>'Noviembre','December'=>'Diciembre'];
    $m['fecha_es'] = ($diasES[$diaEn] ?? $diaEn) . ' ' . $fecha->format('j') . ' de ' . ($mesesES[$mesEn] ?? $mesEn);
    $m['precio_fmt'] = '$' . number_format((int)$m['precio'], 0, ',', '.');
    
    // Si hay imagen guardada en base de datos, resolverla. Sino, fallback a las imágenes por plato.
    $m['imagen_url'] = !empty($m['imagen']) ? (str_starts_with($m['imagen'], 'http') ? $m['imagen'] : SITE_URL . '/assets/img/' . $m['imagen']) : ($imgMenuDia[$m['plato_nombre']] ?? $imgMenuDefault);
    
    $m['disponible_hasta'] = substr($m['disponible_hasta'], 0, 5);
    return $m;
}, $menusSemana);

$menusSemanaJson = json_encode($menusSemana);

$imgProductosJson = json_encode($imgProductos);
$imgCategoriasJson = json_encode($imgCategorias);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casino Universitario – Lista de Precios Oficial</title>
    <meta name="description" content="Consulta la lista oficial de precios, menús del día y disponibilidad de productos del Casino Universitario. Buscador inteligente y filtros por categoría.">
    <meta name="keywords" content="casino universitario, comedor universitario, menu del dia, lista de precios, almuerzos, comida universitaria">
    <meta name="author" content="Casino Universitario">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ==================== NAVBAR FLOTANTE ==================== -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
            <i class="bi bi-fork-knife fs-4"></i>
            <div>
                <span class="d-block" style="font-size:1rem;line-height:1.15;">Casino Universitario</span>
                <small class="d-block" style="font-size:.68rem;opacity:.8;font-weight:400;">Lista de Precios Oficial</small>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-house me-1"></i>Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="#productos"><i class="bi bi-grid me-1"></i>Productos</a></li>
                <!-- Botón dinámico de acceso a administración -->
                <?php if ($adminLogged): ?>
                    <li class="nav-item">
                        <a href="admin/index.php" class="btn btn-success btn-sm rounded-pill px-3">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="admin/login.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Admin
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- ==================== MAIN ==================== -->
<main class="container main-content py-3">

    <!-- Barra de info -->
    <div class="info-bar d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2 text-success">
            <i class="bi bi-check-circle-fill"></i>
            <span>Actualizado: <?= $fechaEs ?></span>
        </div>
        <div class="d-flex align-items-center gap-2 text-muted">
            <i class="bi bi-arrow-clockwise"></i>
            <span>Próxima actualización: Lunes 8:00 AM</span>
        </div>
    </div>

    <!-- ====== CARRUSEL MENÚ DEL DÍA ====== -->
    <?php if (!empty($menusSemana)): ?>
    <div class="menu-carousel-wrap" id="menuCarousel">
        <?php foreach ($menusSemana as $idx => $menu): ?>
        <div class="menu-slide <?= $idx === 0 ? 'active' : '' ?>" data-index="<?= $idx ?>">
            <div class="menu-slide-img" style="background-image:url('<?= htmlspecialchars($menu['imagen_url']) ?>')"></div>
            <div class="menu-slide-overlay"></div>
            <div class="menu-slide-body">
                <div>
                    <div class="menu-slide-label">
                        <i class="bi bi-star-fill" style="font-size:.65rem;"></i>
                        Menú del Día · <?= $menu['fecha_es'] ?>
                    </div>
                    <h2 class="menu-slide-title"><?= e($menu['plato_nombre']) ?></h2>
                    <?php if ($menu['plato_desc']): ?>
                        <p class="menu-slide-desc"><?= e(mb_substr($menu['plato_desc'], 0, 90, 'UTF-8')) ?>…</p>
                    <?php endif; ?>
                    <div class="menu-chips">
                        <?php if ($menu['acompanamiento']): ?><span class="menu-chip">🍚 <?= e($menu['acompanamiento']) ?></span><?php endif; ?>
                        <?php if ($menu['ensalada']): ?><span class="menu-chip">🥗 <?= e($menu['ensalada']) ?></span><?php endif; ?>
                        <?php if ($menu['jugo']): ?><span class="menu-chip">🧃 <?= e($menu['jugo']) ?></span><?php endif; ?>
                        <?php if ($menu['postre']): ?><span class="menu-chip">🍮 <?= e($menu['postre']) ?></span><?php endif; ?>
                        <?php if ($menu['fruta']): ?><span class="menu-chip">🍎 <?= e($menu['fruta']) ?></span><?php endif; ?>
                    </div>
                </div>
                <div class="menu-slide-footer">
                    <span class="menu-slide-price"><?= $menu['precio_fmt'] ?></span>
                    <span class="menu-slide-time"><i class="bi bi-clock"></i> Hasta las <?= $menu['disponible_hasta'] ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <button class="menu-arrow menu-arrow-left" onclick="menuNav(-1)"><i class="bi bi-chevron-left"></i></button>
        <button class="menu-arrow menu-arrow-right" onclick="menuNav(1)"><i class="bi bi-chevron-right"></i></button>
        <div class="menu-dots" id="menuDots">
            <?php foreach ($menusSemana as $idx => $menu): ?>
            <button class="menu-dot <?= $idx === 0 ? 'active' : '' ?>" onclick="menuGoTo(<?= $idx ?>)"></button>
            <?php endforeach; ?>
        </div>
        <div class="menu-progress" id="menuProgress"></div>
    </div>
    <?php else: ?>
    <div class="bg-dark text-white p-4 rounded-4 mb-3 text-center">
        <i class="bi bi-emoji-frown fs-1"></i>
        <h5 class="mt-2">Menú no disponible</h5>
        <p class="mb-0 small">Consulta en caja o vuelve más tarde.</p>
    </div>
    <?php endif; ?>

    <!-- ====== BUSCADOR ====== -->
    <div class="search-wrap">
        <i class="bi bi-search icon"></i>
        <input type="text" id="buscador" placeholder="Buscar producto..." oninput="onBuscar(this)" autocomplete="off">
        <button class="clear-btn" id="btnClear" onclick="limpiarBusqueda()"><i class="bi bi-x-circle-fill"></i></button>
    </div>

    <!-- ====== TABS CATEGORÍAS ====== -->
    <div class="cats-tabs" id="catsTabs">
        <button class="cat-btn active" data-cat="todas" onclick="filtrarCat('todas', this)">
            <i class="bi bi-grid-fill me-1"></i>Todas
        </button>
        <?php foreach ($categorias as $c): ?>
        <button class="cat-btn" data-cat="<?= e($c['slug']) ?>" onclick="filtrarCat('<?= e($c['slug']) ?>', this)">
            <i class="<?= e($c['icono']) ?> me-1"></i><?= e($c['nombre']) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- ====== LISTA PRODUCTOS ====== -->
    <section id="productos">
        <div id="productos-container"><div class="loading-state"><div class="spinner-ring"></div><div>Cargando productos...</div></div></div>
        <div id="sin-resultados" class="text-center py-5 d-none"><i class="bi bi-search fs-1 d-block mb-2"></i><div class="fw-bold">Sin resultados</div><div class="small">Prueba con otro término de búsqueda.</div></div>
    </section>

    <!-- ====== BOTÓN REPORTAR ====== -->
    <div class="text-center my-4 py-2">
        <button class="btn-reportar" onclick="abrirReporte()">
            <i class="bi bi-exclamation-circle-fill me-2"></i>Reportar Error de Precio
        </button>
    </div>

</main>

<!-- ==================== FOOTER ==================== -->
<footer>
    <p class="mb-1 text-muted"><i class="bi bi-fork-knife me-1"></i><strong>Casino Universitario</strong></p>
    <p class="mb-0 small text-muted">Lista de Precios Oficial &middot; <?= date('Y') ?></p>
</footer>

<!-- ==================== MODALES ==================== -->
<div class="modal fade" id="modalReporte" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header border-0 pb-0"><div class="d-flex align-items-center text-danger gap-2"><i class="bi bi-exclamation-circle-fill fs-3"></i><h5 class="modal-title fw-bold mb-0">Reportar Error de Precio</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body pt-2"><p class="text-muted small mb-3">¿Encontraste un precio incorrecto? Ayúdanos a mantener la lista actualizada.</p><div id="reporteMsg" class="d-none alert mb-3"></div><div class="mb-3"><label class="form-label fw-semibold">Producto *</label><input type="text" class="form-control" id="rProducto" placeholder="Nombre del producto" required></div><div class="row g-2 mb-3"><div class="col-6"><label class="form-label fw-semibold">Precio en el sitio *</label><input type="number" class="form-control" id="rPrecioSitio" placeholder="0" min="0"></div><div class="col-6"><label class="form-label fw-semibold">Precio real *</label><input type="number" class="form-control" id="rPrecioReal" placeholder="0" min="0"></div></div><div class="mb-4"><label class="form-label fw-semibold">Comentarios</label><textarea class="form-control" id="rComentarios" rows="3" placeholder="Detalles adicionales..."></textarea></div><div class="d-flex gap-2"><button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Cancelar</button><button type="button" class="btn btn-danger flex-fill fw-bold" onclick="enviarReporte()"><i class="bi bi-send me-1"></i>Enviar Reporte</button></div></div></div></div></div>

<div class="modal fade" id="modalInfo" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content"><div class="modal-body py-0 px-0"><img id="infoImg" src="" alt="" class="modal-prod-img" style="border-radius:var(--radius-lg) var(--radius-lg) 0 0; margin-bottom:0;"><div class="p-3 pt-2 text-center"><h6 class="fw-bold mb-1 mt-1" id="infoNombre" style="font-family:var(--font-head);font-size:1.05rem;"></h6><p class="text-muted small mb-2" id="infoDesc">—</p><div id="infoAdv" class="d-flex flex-wrap justify-content-center gap-1 mb-2"></div><div class="fw-bold text-success fs-5" id="infoPrecio" style="font-family:var(--font-head);"></div><div id="infoStock" class="small mt-1 mb-2"></div><button class="btn btn-light btn-sm mt-1 w-100 fw-bold" data-bs-dismiss="modal">Cerrar</button></div></div></div></div></div>

<div class="toast-custom" id="toast"><i class="bi bi-check-circle-fill" id="toastIcon"></i><span id="toastMsg">Mensaje</span></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ============================================================
//   CASINO UNIVERSITARIO – JS PRINCIPAL
// ============================================================
const API      = 'api/api.php';
let modalRep   = new bootstrap.Modal(document.getElementById('modalReporte'));
let modalInfo  = new bootstrap.Modal(document.getElementById('modalInfo'));
let catActual  = 'todas';
let busqueda   = '';
let todosProds = [];
let debTimer   = null;

const IMG_PRODUCTOS  = <?= $imgProductosJson ?>;
const IMG_CATEGORIAS = <?= $imgCategoriasJson ?>;
const MENUS_SEMANA   = <?= $menusSemanaJson ?>;

// Carrusel
let menuIdx = 0, menuTotal = MENUS_SEMANA.length, menuTimer = null;
const MENU_INTERVAL = 10000;
function menuGoTo(idx) { if(menuTotal===0)return; const slides=document.querySelectorAll('.menu-slide'),dots=document.querySelectorAll('.menu-dot'); slides[menuIdx]?.classList.remove('active'); dots[menuIdx]?.classList.remove('active'); menuIdx=((idx%menuTotal)+menuTotal)%menuTotal; slides[menuIdx]?.classList.add('active'); dots[menuIdx]?.classList.add('active'); startMenuProgress(); resetMenuTimer(); }
function menuNav(dir) { menuGoTo(menuIdx+dir); }
function startMenuProgress() { const bar=document.getElementById('menuProgress'); if(!bar)return; bar.style.transition='none'; bar.style.width='0%'; requestAnimationFrame(()=>{ requestAnimationFrame(()=>{ bar.style.transition=`width ${MENU_INTERVAL}ms linear`; bar.style.width='100%'; }); }); }
function resetMenuTimer() { clearInterval(menuTimer); menuTimer=setInterval(()=>menuGoTo(menuIdx+1), MENU_INTERVAL); }
if(menuTotal>0) { startMenuProgress(); resetMenuTimer(); }

// Productos
document.addEventListener('DOMContentLoaded',()=>cargarProductos());
async function cargarProductos(cat='todas',q='') {
    mostrarLoader();
    try {
        let url=`${API}?action=productos`;
        if(cat && cat!=='todas') url+=`&categoria=${encodeURIComponent(cat)}`;
        if(q) url+=`&buscar=${encodeURIComponent(q)}`;
        const res=await fetch(url), json=await res.json();
        if(!json.ok) throw new Error(json.message);
        todosProds=json.data;
        renderProductos(todosProds);
    } catch(err) {
        document.getElementById('productos-container').innerHTML=`<div class="loading-state text-danger"><i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i><div class="fw-bold">Error al cargar productos</div><div class="small">${err.message}</div><button class="btn btn-outline-primary btn-sm mt-3" onclick="cargarProductos()">Reintentar</button></div>`;
    }
}
function renderProductos(productos) {
    const container=document.getElementById('productos-container'), sinRes=document.getElementById('sin-resultados');
    if(!productos.length) { container.innerHTML=''; sinRes.classList.remove('d-none'); return; }
    sinRes.classList.add('d-none');
    const grupos={};
    productos.forEach(p=>{ const key=p.categoria_slug; if(!grupos[key]) grupos[key]={nombre:p.categoria_nombre, icono:p.icono, slug:p.categoria_slug, items:[]}; grupos[key].items.push(p); });
    let html='';
    Object.entries(grupos).forEach(([slug,grupo])=>{
        html+=`<div class="seccion-header"><div class="cat-icon-wrap"><i class="${escHtml(grupo.icono)}"></i></div><h5>${escHtml(grupo.nombre)}</h5><span class="count-badge">${grupo.items.length}</span></div><div class="prod-list">`;
        grupo.items.forEach(p=>{ html+=cardProducto(p); });
        html+=`</div>`;
    });
    container.innerHTML=html;
}
function getImgProducto(p) {
    if(p.imagen_url) return p.imagen_url;
    if(IMG_PRODUCTOS[p.nombre]) return IMG_PRODUCTOS[p.nombre];
    if(IMG_CATEGORIAS[p.categoria_slug]) return IMG_CATEGORIAS[p.categoria_slug];
    return null;
}
function getEmojiCategoria(slug) { const map={'bebidas-calientes':'☕','bebidas-frias':'🥤','jugos':'🍊','energeticas':'⚡','desayunos':'🍳','sandwiches':'🥪','almuerzos':'🍽','snacks':'🍿','postres':'🍰'}; return map[slug]||'🍴'; }
function cardProducto(p) {
    const agotado=p.stock===0, stockBajo=!agotado && p.stock>0 && p.stock<=5, advs=Array.isArray(p.advertencias)?p.advertencias:[], imgUrl=getImgProducto(p), emoji=getEmojiCategoria(p.categoria_slug);
    let imgHtml;
    if(imgUrl) {
        let badge='';
        if(agotado) badge='<span class="prod-img-badge agotado-badge">Agotado</span>';
        if(stockBajo) badge=`<span class="prod-img-badge stock-bajo-badge">Últimas ${p.stock}</span>`;
        imgHtml=`<div class="prod-img-wrap">
            <img class="prod-img" src="${escHtml(imgUrl)}" alt="${escHtml(p.nombre)}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
            <div class="prod-img-fallback d-none">${emoji}</div>
            ${badge}
        </div>`;
    } else { imgHtml=`<div class="prod-img-wrap"><div class="prod-img-fallback">${emoji}</div></div>`; }
    let stockBadge='';
    if(agotado) stockBadge='<span class="badge-agotado ms-1"><i class="bi bi-x-circle me-1"></i>Agotado</span>';
    if(stockBajo) stockBadge=`<span class="badge-stock-bajo ms-1">Últimas ${p.stock}</span>`;
    let advHtml='';
    if(advs.length) advHtml=advs.map(a=>`<span class="badge-adv">${escHtml(a)}</span>`).join('');
    const infoData=JSON.stringify(p).replace(/"/g,'&quot;');
    return `<div class="prod-card ${agotado?'agotado':''}">${imgHtml}<div class="prod-body"><div><div class="prod-body-top"><div class="flex-grow-1" style="min-width:0"><div class="prod-nombre">${escHtml(p.nombre)}${stockBadge}</div>${p.descripcion?`<div class="prod-desc">${escHtml(p.descripcion)}</div>`:''}</div><span class="prod-precio${agotado?' agotado':''}">${escHtml(p.precio_fmt)}</span></div></div><div class="prod-footer"><div class="prod-advs">${advHtml}</div><button class="btn-info-prod" onclick='mostrarInfo(${infoData})' title="Ver más info"><i class="bi bi-info-circle"></i></button></div></div></div>`;
}
function filtrarCat(cat,btn) { catActual=cat; document.querySelectorAll('.cat-btn').forEach(b=>b.classList.remove('active')); btn.classList.add('active'); cargarProductos(cat,busqueda); }
function onBuscar(input) { busqueda=input.value.trim(); document.getElementById('btnClear').style.display=busqueda?'block':'none'; clearTimeout(debTimer); debTimer=setTimeout(()=>cargarProductos(catActual,busqueda),280); }
function limpiarBusqueda() { document.getElementById('buscador').value=''; busqueda=''; document.getElementById('btnClear').style.display='none'; cargarProductos(catActual,''); }
function mostrarInfo(p) {
    const imgUrl=getImgProducto(p), imgEl=document.getElementById('infoImg');
    if(imgUrl) { imgEl.src=imgUrl; imgEl.style.display='block'; } else { imgEl.style.display='none'; }
    document.getElementById('infoNombre').textContent=p.nombre;
    document.getElementById('infoDesc').textContent=p.descripcion||'Sin descripción.';
    document.getElementById('infoPrecio').textContent=p.precio_fmt;
    const advContainer=document.getElementById('infoAdv'); advContainer.innerHTML='';
    if(Array.isArray(p.advertencias)) p.advertencias.forEach(a=>{ const span=document.createElement('span'); span.className='badge-adv'; span.innerHTML=`<i class="bi bi-exclamation-triangle me-1"></i>${escHtml(a)}`; advContainer.appendChild(span); });
    const stockEl=document.getElementById('infoStock');
    if(p.stock===0) stockEl.innerHTML='<span class="badge-agotado">Agotado</span>';
    else if(p.stock<=5) stockEl.innerHTML=`<span class="badge-stock-bajo">Últimas ${p.stock} unidades</span>`;
    else stockEl.textContent='';
    modalInfo.show();
}
function abrirReporte() { document.getElementById('reporteMsg').classList.add('d-none'); ['rProducto','rPrecioSitio','rPrecioReal','rComentarios'].forEach(id=>document.getElementById(id).value=''); modalRep.show(); }
async function enviarReporte() {
    const producto=document.getElementById('rProducto').value.trim(), precioSitio=parseInt(document.getElementById('rPrecioSitio').value)||0, precioReal=parseInt(document.getElementById('rPrecioReal').value)||0, comentarios=document.getElementById('rComentarios').value.trim(), msgEl=document.getElementById('reporteMsg');
    if(!producto || precioSitio<=0 || precioReal<=0) { msgEl.className='alert alert-danger'; msgEl.textContent='Completa todos los campos requeridos con valores válidos.'; msgEl.classList.remove('d-none'); return; }
    try {
        const res=await fetch(`${API}?action=reporte`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({producto,precio_sitio:precioSitio,precio_real:precioReal,comentarios})});
        const json=await res.json();
        if(json.ok) { modalRep.hide(); showToast(json.message,'success'); } else { msgEl.className='alert alert-danger'; msgEl.textContent=json.message; msgEl.classList.remove('d-none'); }
    } catch { showToast('Error de conexión. Intenta de nuevo.','error'); }
}
function mostrarLoader() { document.getElementById('productos-container').innerHTML=`<div class="loading-state"><div class="spinner-ring"></div><div>Cargando productos...</div></div>`; document.getElementById('sin-resultados').classList.add('d-none'); }
function showToast(msg,tipo='success') { const t=document.getElementById('toast'), i=document.getElementById('toastIcon'); document.getElementById('toastMsg').textContent=msg; t.className=`toast-custom ${tipo}`; i.className=tipo==='success'?'bi bi-check-circle-fill':'bi bi-exclamation-circle-fill'; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),3800); }
function escHtml(str) { if(typeof str!=='string') return str??''; return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
</script>
</body>
</html>