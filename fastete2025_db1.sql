-- ============================================================
--   CASINO UNIVERSITARIO - BASE DE DATOS COMPLETA
--   Versión 1.0 | Compatible con MySQL 5.7+ / MariaDB 10.3+
--   Importar en phpMyAdmin o con: mysql -u root -p < casino_universitario.sql
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET time_zone = '-04:00'; -- Hora de Chile

-- Crear y usar la base de datos
CREATE DATABASE IF NOT EXISTS fastete2025_db1
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE fastete2025_db1;

-- ============================================================
--   TABLA: categorias
-- ============================================================
CREATE TABLE IF NOT EXISTS categorias (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(80)  NOT NULL,
    slug       VARCHAR(80)  NOT NULL UNIQUE,
    icono      VARCHAR(50)  NOT NULL DEFAULT 'bi-tag',
    orden      INT          NOT NULL DEFAULT 0,
    activa     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categorias (nombre, slug, icono, orden) VALUES
('Bebidas Calientes',  'bebidas-calientes',  'bi-cup-hot',        1),
('Bebidas Frías',      'bebidas-frias',       'bi-cup-straw',      2),
('Jugos y Néctares',   'jugos',               'bi-droplet',        3),
('Energéticas',        'energeticas',         'bi-lightning-fill', 4),
('Desayunos',          'desayunos',           'bi-sunrise',        5),
('Sándwiches',         'sandwiches',          'bi-layers',         6),
('Almuerzos',          'almuerzos',           'bi-bowl-hot',       7),
('Snacks',             'snacks',              'bi-bag',            8),
('Postres',            'postres',             'bi-cake',           9);

-- ============================================================
--   TABLA: productos
-- ============================================================
CREATE TABLE IF NOT EXISTS productos (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id  INT          NOT NULL,
    nombre        VARCHAR(120) NOT NULL,
    descripcion   TEXT,
    precio        INT          NOT NULL DEFAULT 0,
    stock         INT          NOT NULL DEFAULT 50,
    imagen        VARCHAR(255) DEFAULT NULL,
    advertencias  VARCHAR(255) DEFAULT NULL,   -- JSON array: ["Alto en sodio","Alto en grasas"]
    disponible    TINYINT(1)   NOT NULL DEFAULT 1,
    destacado     TINYINT(1)   NOT NULL DEFAULT 0,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- BEBIDAS CALIENTES (cat 1)
-- --------------------------------------------------------
INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, advertencias) VALUES
(1, 'Café Expresso',         'Café espresso preparado en máquina. Concentrado e intenso.',                         1200, 80, NULL),
(1, 'Café con Leche',        'Café suave mezclado con leche caliente al gusto.',                                   1500, 80, NULL),
(1, 'Capuchino',             'Espresso con leche vaporizada y espuma cremosa.',                                    1800, 60, NULL),
(1, 'Té',                    'Selección de té negro, verde o de hierbas. Con azúcar opcional.',                    900, 100, NULL),
(1, 'Té con Leche',          'Té negro servido con leche caliente.',                                               1100, 70, NULL),
(1, 'Leche con Chocolate',   'Leche entera caliente con chocolate en polvo. Ideal para el frío.',                  1400, 60, NULL),
(1, 'Nescafé',               'Café instantáneo clásico, servido caliente con azúcar.',                             1000, 100, NULL),
(1, 'Milo',                  'Bebida de malta con cacao, energizante y nutritiva.',                                1300, 50, NULL);

-- --------------------------------------------------------
-- BEBIDAS FRÍAS (cat 2)
-- --------------------------------------------------------
INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, advertencias) VALUES
(2, 'Coca-Cola 350 ml',      'Bebida gaseosa clásica, fría y burbujeante.',                                        1200, 120, '["Alto en azúcar"]'),
(2, 'Coca-Cola 500 ml',      'Botella personal de Coca-Cola fría.',                                                1600, 80, '["Alto en azúcar"]'),
(2, 'Sprite 350 ml',         'Bebida gaseosa de limón, refrescante y sin cafeína.',                                1200, 100, '["Alto en azúcar"]'),
(2, 'Fanta Naranja 350 ml',  'Bebida gaseosa de naranja, sabor frutal y dulce.',                                   1200, 90,  '["Alto en azúcar"]'),
(2, 'Bilz 350 ml',           'Bebida gaseosa chilena de sabor característico y único.',                            1100, 80,  '["Alto en azúcar"]'),
(2, 'Pap 350 ml',            'La clásica bebida gaseosa chilena, inseparable del Bilz.',                           1100, 80,  '["Alto en azúcar"]'),
(2, 'Kem Piña 350 ml',       'Bebida gaseosa de piña, refrescante y tropical.',                                    1000, 70,  '["Alto en azúcar"]'),
(2, 'Powerade 500 ml',       'Bebida isotónica con electrolitos. Ideal post-actividad física.',                    1800, 60,  NULL),
(2, 'Agua Mineral 500 ml',   'Agua mineral sin gas, pura y refrescante.',                                          800,  150, NULL),
(2, 'Agua Mineral 1.5 L',    'Botella familiar de agua mineral sin gas.',                                          1500, 60,  NULL);

-- --------------------------------------------------------
-- JUGOS Y NÉCTARES (cat 3)
-- --------------------------------------------------------
INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, advertencias) VALUES
(3, 'Jugo Watts Naranja 200 ml',   'Néctar de naranja natural, vitamina C. Formato individual.',        950,  100, '["Contiene azúcar"]'),
(3, 'Jugo Watts Durazno 200 ml',   'Néctar de durazno suave y dulce. Formato individual.',              950,  100, '["Contiene azúcar"]'),
(3, 'Jugo Watts Manzana 200 ml',   'Néctar de manzana, sabor natural y refrescante.',                   950,  80,  '["Contiene azúcar"]'),
(3, 'Néctar Andina Piña 250 ml',   'Néctar de piña tropical, formato tetra.',                           1000, 80,  '["Contiene azúcar"]'),
(3, 'Néctar Andina Mango 250 ml',  'Néctar de mango exótico y dulce.',                                  1000, 70,  '["Contiene azúcar"]'),
(3, 'Jugo Natural del Día',        'Jugo preparado en casino, varía según temporada. Pregunta en caja.',2000, 30,  NULL),
(3, 'Limonada Natural',            'Limonada fresca preparada en casino con limón de pica.',             1800, 25,  NULL);

-- --------------------------------------------------------
-- ENERGÉTICAS (cat 4)
-- --------------------------------------------------------
INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, advertencias) VALUES
(4, 'Red Bull 250 ml',      'La bebida energética original. Aumenta la concentración.',  2500, 40, '["Alto en cafeína","Alto en azúcar","No recomendado para menores"]'),
(4, 'Monster Energy 473 ml','Bebida energética intensa, sabor clásico verde.',           3200, 30, '["Alto en cafeína","Alto en azúcar","No recomendado para menores"]'),
(4, 'Score Energy 355 ml',  'Energética chilena, precio accesible. Varios sabores.',     1500, 50, '["Alto en cafeína","Alto en azúcar"]'),
(4, 'Volt Energy 250 ml',   'Bebida energética compacta, bajo costo.',                   1200, 60, '["Alto en cafeína","Alto en azúcar"]');

-- --------------------------------------------------------
-- DESAYUNOS (cat 5)
-- --------------------------------------------------------
INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, advertencias) VALUES
(5, 'Pan con Mantequilla',          'Marraqueta o hallulla con mantequilla y mermelada opcionales.',          600,  80, NULL),
(5, 'Pan con Palta',                'Marraqueta con palta fresca sazonada. Desayuno saludable.',              1200, 50, NULL),
(5, 'Pan con Queso',                'Hallulla con queso fresco chileno derretido al gusto.',                  900,  70, '["Alto en sodio"]'),
(5, 'Pan con Huevo Frito',          'Marraqueta con huevo frito al punto. Clásico energizante.',              1400, 60, NULL),
(5, 'Pan con Palta y Huevo',        'La combinación perfecta: palta y huevo en marraqueta.',                  1800, 40, NULL),
(5, 'Yogur con Granola',            'Yogur natural con granola crocante. Opción ligera y nutritiva.',         1500, 50, NULL),
(5, 'Tostadas con Mermelada',       'Dos tostadas con mantequilla y mermelada de frutilla o naranja.',        800,  60, NULL),
(5, 'Desayuno Completo',            'Café + pan con mantequilla + yogur. La combinación clásica.',            2500, 40, NULL);

-- --------------------------------------------------------
-- SÁNDWICHES (cat 6)
-- --------------------------------------------------------
INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, advertencias) VALUES
(6, 'Barros Luco',                  'El sándwich chileno por excelencia. Carne y queso en marraqueta caliente.',              3500, 40, '["Alto en sodio","Alto en grasas"]'),
(6, 'Barros Jarpa',                 'Jamón de vacuno y queso derretido en pan caliente. Clásico infaltable.',                 3200, 40, '["Alto en sodio"]'),
(6, 'Completo Italiano',            'Vienesa, tomate, palta y mayonesa. El clásico chileno por excelencia.',                  2800, 50, '["Alto en calorías","Alto en grasas"]'),
(6, 'Completo con Todo',            'Vienesa con chucrut, tomate, mayo y mostaza. Versión completa.',                         3000, 40, '["Alto en calorías","Alto en grasas"]'),
(6, 'Chacarero',                    'Carne, porotos verdes, tomate y ají verde en pan. Único y delicioso.',                   3800, 30, '["Alto en sodio"]'),
(6, 'Ave Pimentón',                 'Pollo desmenuzado con pimentón asado y mayonesa. Suave y sabroso.',                      3200, 35, NULL),
(6, 'Veggie Palta',                 'Palta, tomate, lechuga y queso en pan integral. Opción vegetariana.',                    2800, 25, NULL),
(6, 'Sándwich de Jamón y Queso',    'Pan de molde con jamón de cerdo y queso laminado.',                                      2200, 50, '["Alto en sodio"]');

-- --------------------------------------------------------
-- ALMUERZOS (cat 7)
-- --------------------------------------------------------
INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, advertencias, destacado) VALUES
(7, 'Menú del Día',                 'Plato del día + ensalada + jugo + fruta o postre. Ver menú en el panel.',    4200, 60, NULL, 1),
(7, 'Cazuela de Vacuno',            'Cazuela tradicional chilena con papa, choclo, zanahoria y zapallo.',         4500, 30, NULL, 0),
(7, 'Charquicán',                   'Guiso de papas, zapallo y carne picada. Plato típico chileno.',              3800, 30, NULL, 0),
(7, 'Porotos con Riendas',          'Porotos con tallarines. Clásico de la cocina popular chilena.',              3500, 35, NULL, 0),
(7, 'Lentejas con Arroz',           'Guiso de lentejas sazonadas acompañado de arroz blanco.',                    3500, 35, NULL, 0),
(7, 'Pastel de Papa',               'Pino de carne cubierto con puré de papa, gratinado al horno.',               4000, 25, NULL, 0),
(7, 'Tallarines con Salsa',         'Tallarines spaghetti con salsa bolognesa o napolitana.',                     3500, 40, NULL, 0),
(7, 'Arroz con Pollo',              'Arroz con presas de pollo y verduras. Clásico reconfortante.',               4000, 35, NULL, 0),
(7, 'Milanesa con Papas Fritas',    'Milanesa de vacuno apanada con papas fritas y ensalada.',                    4500, 30, '["Alto en calorías","Alto en grasas"]', 0),
(7, 'Empanada de Pino',             'Empanada tradicional chilena horneada, rellena de pino con huevo y aceituna.',1800, 40, '["Alto en sodio"]', 0),
(7, 'Empanada de Queso',            'Empanada horneada con queso derretido. Opción vegetariana.',                  1500, 40, NULL, 0),
(7, 'Sopaipillas (3 uds)',          'Sopaipillas fritas chilenas. Con o sin salsa.',                               1200, 50, '["Alto en grasas"]', 0),
(7, 'Pizza Porción',                'Porción de pizza de queso + tomate. Preparada al momento.',                   2000, 25, '["Alto en sodio","Alto en grasas"]', 0);

-- --------------------------------------------------------
-- SNACKS (cat 8)
-- --------------------------------------------------------
INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, advertencias) VALUES
(8, 'Galletas Tritón',              'Galletas surtidas de vainilla y chocolate. Pack individual.',               500,  100, '["Alto en azúcar"]'),
(8, 'Galletas Oreo 3 pack',         'Tres galletas Oreo de chocolate con relleno de crema.',                     600,  80,  '["Alto en azúcar","Alto en grasas"]'),
(8, 'Papas Fritas Lays 42g',        'Papas fritas clásicas sabor natural o con limón.',                          900,  80,  '["Alto en sodio","Alto en grasas"]'),
(8, 'Doritos Nacho 45g',            'Totopos de maíz sabor nacho, crujientes.',                                  900,  70,  '["Alto en sodio","Alto en grasas"]'),
(8, 'Super 8',                      'Galleta de vainilla con cobertura de chocolate. Clásico chileno.',           600,  100, '["Alto en azúcar","Alto en grasas"]'),
(8, 'Chocolate Barra Sahne-Nuss',   'Chocolate con leche y avellanas. Clásico nacional.',                        1200, 60,  '["Alto en azúcar","Alto en grasas"]'),
(8, 'Yogur Soprole 165g',           'Yogur batido individual. Variados sabores.',                                 900,  60,  NULL),
(8, 'Barra de Cereal',              'Barra de cereal con miel y semillas. Opción saludable.',                     800,  50,  NULL),
(8, 'Maní Salado 50g',              'Maní salado tostado, snack energético y sabroso.',                           700,  80,  '["Alto en sodio"]'),
(8, 'Queque de Plátano (porción)',   'Queque casero de plátano. Receta clásica del casino.',                      900,  30,  NULL);

-- --------------------------------------------------------
-- POSTRES (cat 9)
-- --------------------------------------------------------
INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, advertencias) VALUES
(9, 'Mousse de Chocolate',          'Mousse esponjosa de chocolate negro. Irresistible.',                         1500, 20, '["Alto en azúcar","Alto en grasas"]'),
(9, 'Kuchen de Berries',            'Kuchen alemán tradicional con relleno de berries.',                          1800, 15, '["Alto en azúcar"]'),
(9, 'Flan Casero',                  'Flan cremoso con salsa de caramelo.',                                        1200, 20, '["Alto en azúcar"]'),
(9, 'Fruta del Día',                'Selección de fruta de temporada. Opción saludable.',                          800, 40, NULL),
(9, 'Leche Asada',                  'Postre típico chileno, suave y cremoso con topping de canela.',              1200, 20, '["Alto en azúcar"]'),
(9, 'Arroz con Leche',              'Arroz cremoso con canela y manjar. Postre reconfortante.',                   1100, 25, '["Alto en azúcar"]'),
(9, 'Queque de Limón (porción)',     'Bizcochuelo esponjoso con cobertura de limón.',                             1000, 20, '["Alto en azúcar"]');


-- ============================================================
--   TABLA: menu_dia
--   Un registro por fecha. El admin puede editarlo.
-- ============================================================
CREATE TABLE IF NOT EXISTS menu_dia (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    fecha         DATE         NOT NULL UNIQUE,
    plato_nombre  VARCHAR(120) NOT NULL,
    plato_desc    TEXT,
    acompanamiento VARCHAR(120) DEFAULT NULL,
    ensalada      VARCHAR(120) DEFAULT NULL,
    jugo          VARCHAR(80)  DEFAULT NULL,
    postre        VARCHAR(80)  DEFAULT NULL,
    fruta         VARCHAR(80)  DEFAULT NULL,
    precio        INT          NOT NULL DEFAULT 4200,
    disponible_hasta TIME      DEFAULT '15:00:00',
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar menús para los próximos días (semana típica casino universitario chileno)
INSERT INTO menu_dia (fecha, plato_nombre, plato_desc, acompanamiento, ensalada, jugo, postre, fruta, precio) VALUES
(CURDATE() - INTERVAL 1 DAY, 'Porotos con Riendas',        'Porotos granados con tallarines y longaniza.', 'Arroz blanco', 'Ensalada de tomate', 'Jugo de naranja', 'Fruta', 'Manzana', 3800),
(CURDATE(),                   'Cazuela de Vacuno',           'Cazuela tradicional con papa, choclo y verduras de temporada.', 'Marraqueta', 'Ensalada chilena', 'Jugo de pera', 'Leche asada', 'Plátano', 4200),
(CURDATE() + INTERVAL 1 DAY,  'Arroz con Pollo',             'Arroz con presas de pollo al merkén y verduras salteadas.', 'Puré de papas', 'Ensalada de zanahoria', 'Jugo de manzana', 'Arroz con leche', 'Naranja', 4200),
(CURDATE() + INTERVAL 2 DAY,  'Charquicán',                  'Guiso de papas, zapallo camote y carne molida de vacuno.', 'Huevo frito', 'Ensalada de betarraga', 'Jugo de durazno', 'Flan casero', 'Uva', 3800),
(CURDATE() + INTERVAL 3 DAY,  'Pastel de Papa',              'Pino de carne con aceitunas y huevo, cubierto con puré gratinado.', 'Ensalada fresca', 'Ensalada de lechuga', 'Jugo de naranja', 'Kuchen de berries', 'Pera', 4500),
(CURDATE() + INTERVAL 4 DAY,  'Tallarines con Salsa Carne',  'Spaghetti con salsa bolognesa casera y queso parmesano.', 'Pan', 'Ensalada de tomate y pepino', 'Jugo de frambuesa', 'Mousse de chocolate', 'Kiwi', 4000),
(CURDATE() + INTERVAL 5 DAY,  'Lentejas con Arroz',          'Lentejas cocidas con tocino, zanahoria y papas. Plato de invierno.', 'Arroz blanco', 'Ensalada de repollo', 'Jugo de mango', 'Fruta', 'Plátano', 3500),
(CURDATE() + INTERVAL 6 DAY,  'Milanesa Napolitana',         'Milanesa de vacuno con salsa de tomate, jamón y queso derretido.', 'Papas fritas', 'Ensalada mixta', 'Jugo de piña', 'Queque de limón', 'Manzana', 4500);

-- ============================================================
--   TABLA: administradores
-- ============================================================
CREATE TABLE IF NOT EXISTS administradores (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,   -- bcrypt hash
    nombre     VARCHAR(100) NOT NULL,
    email      VARCHAR(120) DEFAULT NULL,
    activo     TINYINT(1)   NOT NULL DEFAULT 1,
    ultimo_login DATETIME   DEFAULT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin por defecto: usuario = admin | contraseña = Casino2026!
-- Hash bcrypt generado con password_hash('Casino2026!', PASSWORD_BCRYPT)
INSERT INTO administradores (username, password, nombre, email) VALUES
('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin@casino.cl');
-- NOTA: La contraseña de ejemplo es "password". Cámbiala después del primer login.
-- Para generar un hash real usa: php -r "echo password_hash('TuContraseña', PASSWORD_BCRYPT);"

-- ============================================================
--   TABLA: reportes_precios  (reportes de usuarios)
-- ============================================================
CREATE TABLE IF NOT EXISTS reportes_precios (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    producto_nombre VARCHAR(120) NOT NULL,
    precio_sitio  INT          NOT NULL,
    precio_real   INT          NOT NULL,
    comentarios   TEXT         DEFAULT NULL,
    estado        ENUM('pendiente','revisado','resuelto') DEFAULT 'pendiente',
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--   TABLA: historial_stock  (log de cambios de stock)
-- ============================================================
CREATE TABLE IF NOT EXISTS historial_stock (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    producto_id   INT          NOT NULL,
    stock_anterior INT         NOT NULL,
    stock_nuevo   INT          NOT NULL,
    admin_id      INT          DEFAULT NULL,
    motivo        VARCHAR(255) DEFAULT NULL,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES administradores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--   ÍNDICES PARA RENDIMIENTO
-- ============================================================
CREATE INDEX idx_productos_categoria ON productos(categoria_id);
CREATE INDEX idx_productos_disponible ON productos(disponible);
CREATE INDEX idx_productos_stock      ON productos(stock);
CREATE INDEX idx_menu_fecha           ON menu_dia(fecha);
CREATE INDEX idx_reportes_estado      ON reportes_precios(estado);
