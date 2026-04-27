<?php
// ============================================================
// CONFIGURACIÓN GLOBAL — TIENDA
// ============================================================

// ── Cargar variables del archivo .env ──
function load_env(string $path): void {
    if (!is_file($path) || !is_readable($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Quitar comillas envolventes si las hay
        if (strlen($value) >= 2 && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
            $value = substr($value, 1, -1);
        }
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

function env(string $key, ?string $default = null): ?string {
    $val = $_ENV[$key] ?? getenv($key);
    return ($val === false || $val === null || $val === '') ? $default : $val;
}

load_env(__DIR__ . '/.env');

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME'));
define('DB_USER', env('DB_USER'));
define('DB_PASS', env('DB_PASS', ''));

define('SITE_NAME', 'BTLDECO');
define('SITE_URL', rtrim((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), '/'));
define('ADMIN_USER', env('ADMIN_USER', 'admin'));
define('ADMIN_PASS', env('ADMIN_PASS_HASH', ''));

define('MP_ACCESS_TOKEN', env('MP_ACCESS_TOKEN', ''));
define('MP_PUBLIC_KEY', env('MP_PUBLIC_KEY', ''));

define('ITEMS_PER_PAGE', 12);
define('UPLOAD_DIR', __DIR__ . '/uploads/productos/');
define('UPLOAD_URL', SITE_URL . '/uploads/productos/');

define('NOTIFY_EMAIL', env('NOTIFY_EMAIL', 'noreply@example.com'));
define('STOCK_MINIMO_ALERTA', 5);

// Sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexión PDO
function pdo(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER, DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    }
    return $pdo;
}

// Helpers
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function sanitize(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function slug(string $str): string {
    $str = mb_strtolower($str, 'UTF-8');
    $str = preg_replace('/[áàäâ]/u', 'a', $str);
    $str = preg_replace('/[éèëê]/u', 'e', $str);
    $str = preg_replace('/[íìïî]/u', 'i', $str);
    $str = preg_replace('/[óòöô]/u', 'o', $str);
    $str = preg_replace('/[úùüû]/u', 'u', $str);
    $str = preg_replace('/ñ/u', 'n', $str);
    $str = preg_replace('/[^a-z0-9]+/', '-', $str);
    return trim($str, '-');
}

function price(float $amount): string {
    return '$' . number_format($amount, 2, ',', '.');
}

// URL helpers for friendly URLs
function url_producto(string $slug): string { return SITE_URL . '/producto/' . $slug; }
function url_categoria(string $slug): string { return SITE_URL . '/categoria/' . $slug; }
function url_buscar(string $q): string { return SITE_URL . '/buscar/' . urlencode($q); }
function url_pagina(string $pagina): string { return SITE_URL . '/' . $pagina; }

// Image URL helper — always returns absolute URL
function img_url(?string $ruta, string $carpeta = 'productos'): string {
    if (empty($ruta)) return SITE_URL . '/assets/no-image.svg';
    if (str_starts_with($ruta, 'http')) return $ruta;
    // If it already has uploads/ prefix, use as-is
    if (str_starts_with($ruta, 'uploads/')) return SITE_URL . '/' . $ruta;
    // Otherwise assume it's just the filename
    return SITE_URL . '/uploads/' . $carpeta . '/' . $ruta;
}

// SEO meta tags
function seo_tags(string $titulo, string $descripcion, string $imagen = '', string $tipo = 'website'): string {
    $site_name = SITE_NAME;
    $titulo_completo = sanitize($titulo) . ' | ' . $site_name;
    $descripcion = sanitize($descripcion);
    $imagen_url = $imagen ? (strpos($imagen, 'http') === 0 ? $imagen : SITE_URL . '/' . $imagen) : SITE_URL . '/assets/og-image.jpg';
    $canonical = SITE_URL . $_SERVER['REQUEST_URI'];

    return '
    <title>' . $titulo_completo . '</title>
    <meta name="description" content="' . $descripcion . '">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="' . $canonical . '">
    <meta property="og:title" content="' . $titulo_completo . '">
    <meta property="og:description" content="' . $descripcion . '">
    <meta property="og:image" content="' . $imagen_url . '">
    <meta property="og:type" content="' . $tipo . '">
    <meta property="og:url" content="' . $canonical . '">
    <meta property="og:site_name" content="' . $site_name . '">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="' . $titulo_completo . '">
    <meta name="twitter:description" content="' . $descripcion . '">
    <meta name="twitter:image" content="' . $imagen_url . '">
    ';
}

// Configuration helpers (tabla `configuracion`)
function get_config(string $clave, string $default = ''): string {
    try {
        $stmt = pdo()->prepare("SELECT valor FROM configuracion WHERE clave = ?");
        $stmt->execute([$clave]);
        $row = $stmt->fetch();
        return $row ? ($row['valor'] ?? $default) : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function get_redes_sociales(): array {
    try {
        $stmt = pdo()->query("SELECT clave, valor FROM configuracion WHERE grupo = 'redes_sociales' AND valor != ''");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Exception $e) {
        return [];
    }
}

function set_config(string $clave, string $valor, string $grupo = 'general'): bool {
    $stmt = pdo()->prepare("INSERT INTO configuracion (clave, valor, grupo, fecha_modificacion)
                            VALUES (?, ?, ?, NOW())
                            ON DUPLICATE KEY UPDATE valor = VALUES(valor), fecha_modificacion = NOW()");
    return $stmt->execute([$clave, $valor, $grupo]);
}

function is_admin(): bool {
    return ($_SESSION['admin_auth'] ?? false) === true;
}

function is_cliente(): bool {
    return isset($_SESSION['cliente_id']);
}

function cliente_id(): ?int {
    return $_SESSION['cliente_id'] ?? null;
}

function cart_count(): int {
    $cart = $_SESSION['cart'] ?? [];
    $count = 0;
    foreach ($cart as $item) {
        if (!is_array($item)) continue;
        if (empty($item['producto_id']) || empty($item['nombre'])) continue;
        $qty = (int)($item['qty'] ?? 0);
        if ($qty > 0) $count += $qty;
    }
    return $count;
}

function flash(string $key, ?string $msg = null) {
    if ($msg !== null) {
        $_SESSION['flash'][$key] = $msg;
    } else {
        $val = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $val;
    }
}

function json_response(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void {
    $token = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        json_response(['ok' => false, 'mensaje' => 'Token inválido'], 403);
    }
}
