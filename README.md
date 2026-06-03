# 🍽 Casino Universitario — Sistema Profesional

Sistema web completo para casino/comedor universitario chileno.
**Stack:** PHP puro + MySQL + HTML/CSS/JS · Sin frameworks pesados

---

## 📁 Estructura del Proyecto

```
casino_copia/
├── index.php                  ← Página principal (frontend dinámico con glassmorphism)
├── style.css                  ← Estilos premium (paleta terracota, responsive, animaciones)
├── fastete2025_db1.sql        ← Base de datos completa (importar aquí)
│
├── includes/
│   ├── config.php             ← Configuración MySQL + helpers + polyfill PHP 7.4+
│   └── auth.php               ← Sesiones seguras (cookies HttpOnly + SameSite=Lax)
│
├── api/
│   └── api.php                ← API REST (productos, menú, reportes, historial de stock)
│
└── admin/
    ├── login.php              ← Login del administrador (diseño terracota)
    ├── index.php              ← Panel de administración (Chart.js + SweetAlert2)
    └── logout.php             ← Cierre de sesión
```

---

## 🚀 Guía de Instalación en XAMPP

### Paso 1 — Instalar XAMPP
- Descarga XAMPP desde https://www.apachefriends.org
- Instala y abre el **Panel de Control de XAMPP**
- Inicia **Apache** y **MySQL** (botones Start)

### Paso 2 — Copiar el proyecto
1. Copia la carpeta `casino_copia/` completa a:
   - **Windows:** `C:\xampp\htdocs\casino_copia\`
   - **macOS/Linux:** `/opt/lampp/htdocs/casino_copia/`

### Paso 3 — Importar la base de datos
1. Abre tu navegador y ve a: **http://localhost/phpmyadmin**
2. Haz clic en **"Nueva"** → Escribe `fastete2025_db1` → **Crear**
3. Selecciona la base de datos recién creada → pestaña **Importar**
4. Elige el archivo: `casino_copia/fastete2025_db1.sql`
5. Haz clic en **"Continuar"** o **"Importar"**
6. ✅ Verás el mensaje de éxito

### Paso 4 — Configurar conexión MySQL
Abre `includes/config.php` y verifica:

```php
define('DB_HOST',  'localhost');
define('DB_NAME',  'fastete2025_db1');  // ← Nombre exacto de la BD
define('DB_USER',  'root');
define('DB_PASS',  '');                 // Vacío por defecto en XAMPP
define('SITE_URL', 'http://localhost/casino_copia');
```

> **⚠️ Nota:** Si tu MySQL tiene contraseña, cámbiala en `DB_PASS`.

### Paso 5 — ¡Abrir el sitio!
- **Sitio principal:** http://localhost/casino_copia/
- **Panel admin:**    http://localhost/casino_copia/admin/login.php

---

## 🔐 Credenciales de Administrador

| Campo      | Valor      |
|------------|------------|
| Usuario    | `admin`    |
| Contraseña | `password` |

> **⚠️ IMPORTANTE:** Cambia la contraseña después del primer ingreso.
>
> Para generar un nuevo hash bcrypt:
> ```bash
> php -r "echo password_hash('TuNuevaContraseña', PASSWORD_BCRYPT);"
> ```
> Luego actualiza el campo `password` en la tabla `administradores`.
>
> O desde phpMyAdmin ejecuta:
> ```sql
> UPDATE administradores SET password = '$hash_aqui' WHERE username = 'admin';
> ```

---

## ✨ Funcionalidades

### Frontend (index.php)
- ✅ Menú del día dinámico con carrusel automático (cambia por fecha)
- ✅ Lista de productos con precios en tiempo real
- ✅ Filtros por categoría (9 categorías)
- ✅ Buscador instantáneo con debounce
- ✅ Estado de stock: Agotado / Últimas unidades / Disponible
- ✅ Imagen de respaldo (emoji) cuando la URL de producto falla
- ✅ Modal de detalle por producto (descripción, advertencias, stock)
- ✅ Formulario de reporte de errores de precio (se guarda en BD)
- ✅ Diseño responsive (móvil, tablet, desktop)
- ✅ Animaciones glassmorphism y transiciones suaves

### Panel Admin (admin/)
- ✅ Login seguro con sesiones PHP (cookies HttpOnly, SameSite=Lax)
- ✅ Dashboard con **estadísticas visuales en tiempo real**
- ✅ **Gráfico de dona** — Distribución de productos por categoría (Chart.js)
- ✅ **Gráfico de barras** — Nivel de stock por categoría (Chart.js)
- ✅ **Reposición Rápida** — Widget para reabastecer productos con stock crítico
- ✅ Tabla de productos con edición y creación completa
- ✅ Confirmaciones premium antes de eliminar (SweetAlert2)
- ✅ Editor de menú del día (por fecha)
- ✅ Sección de reportes de precios con resolución de estado
- ✅ Llamadas API en paralelo para carga ultrarrápida del panel

### API REST (api/api.php)
- ✅ `productos` — Lista con filtros por categoría y búsqueda
- ✅ `categorias` — Listado de categorías activas
- ✅ `menu_dia` — Menú del día (con fallback al próximo disponible)
- ✅ `reporte` — Envío de reportes de precios (POST público)
- ✅ `admin_listar_productos` / `admin_crear_producto` / `admin_actualizar_producto` / `admin_eliminar_producto`
- ✅ `admin_listar_menu` / `admin_crear_menu` / `admin_actualizar_menu` / `admin_eliminar_menu`
- ✅ `admin_listar_reportes` / `admin_marcar_reporte`
- ✅ `admin_restock_producto` — Reposición rápida de stock (**nuevo**)

### Base de Datos
- ✅ 9 categorías de productos
- ✅ +60 productos chilenos reales con precios
- ✅ 8 menús del día precargados (semana completa)
- ✅ Historial de stock con FK a `administradores` y `productos`
- ✅ Tabla de reportes de precios

---

## 🛠 Solución de Problemas

**"Error de conexión a MySQL"**
→ Verifica que MySQL esté corriendo en XAMPP  
→ Confirma que importaste el archivo `fastete2025_db1.sql`  
→ Revisa usuario/contraseña en `config.php`

**"Página en blanco"**
→ Activa errores PHP: añade `error_reporting(E_ALL); ini_set('display_errors',1);` al inicio de `config.php`

**"No carga la API"**
→ Verifica que Apache esté corriendo  
→ Abre `http://localhost/casino_copia/api/api.php?action=productos` directamente

**"No puedo hacer login en el admin"**
→ La contraseña por defecto es **`password`** (en minúsculas)  
→ Si la olvidaste, ejecuta en phpMyAdmin:
```sql
UPDATE administradores
SET password = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE username = 'admin';
```
Esto restablece la contraseña a `password`.

---

## 📋 Tecnologías Usadas

| Tecnología         | Uso                                       |
|--------------------|-------------------------------------------|
| PHP 7.4+           | Backend, API REST, sesiones admin         |
| MySQL / MariaDB    | Base de datos relacional                  |
| Bootstrap 5.3      | UI components, grid y utilidades          |
| Bootstrap Icons    | Íconos SVG                                |
| Vanilla JS (ES6+)  | AJAX, filtros, interactividad, fetch API  |
| **Chart.js**       | Gráficos dinámicos en el dashboard        |
| **SweetAlert2**    | Modales de confirmación y alerta premium  |
| Google Fonts       | Tipografía (Outfit + Plus Jakarta Sans)   |

---

## 📝 Changelog

### v1.1.0 — Actualización Profesional (Junio 2026)

**🐛 Errores Corregidos:**
- Compatibilidad PHP 7.4: polyfill global para `str_starts_with()`
- Ruta 404 del CSS en el panel de administración corregida (`../style.css`)
- Contraseña de ejemplo en el login corregida de `admin123` → `password`
- Parámetros nulos en función `e()` ahora soportados (evita warnings PHP 8.1+)
- Corte incorrecto de caracteres UTF-8 (acentos, eñes) en carrusel de menús
- Función `escHtml` en JS ahora escapa comillas simples y dobles (XSS)
- Clave foránea faltante añadida en tabla `historial_stock`

**✨ Mejoras:**
- Rediseño visual completo: paleta terracota/ámbar, glassmorphism, sombras premium
- Gráficos dinámicos en el dashboard (Chart.js): dona + barras
- Widget de Reposición Rápida de inventario (+10 / +50 unidades desde el dashboard)
- Registro automático en `historial_stock` al modificar stock de cualquier producto
- Nueva acción API `admin_restock_producto` para reposición desde el panel
- Sesiones PHP endurecidas: cookies `HttpOnly`, `SameSite=Lax`, y `Secure` en HTTPS
- Imágenes rotas en tarjetas de productos reemplazadas por emoji de categoría (fallback)
- SweetAlert2 integrado: confirmaciones y alertas visuales premium
- Llamadas API en paralelo en el dashboard (`Promise.all`) para carga más rápida
- Metaetiquetas SEO añadidas al frontend (`description`, `keywords`, `author`)

---

*Sistema desarrollado para casino universitario chileno · 2026*
