<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lang.php';

// --- Fetch categories ---
try {
    $cats = pdo()->query("SELECT id, nombre, nombre_en, slug FROM categorias WHERE activa = 1 ORDER BY orden, nombre")->fetchAll();
} catch (Exception $e) {
    $cats = [];
}

// --- Filters ---
$cat_filter = $_GET['cat'] ?? '';
$sort = $_GET['sort'] ?? 'recientes';

$where = "WHERE p.estado = 'activo'";
$params = [];

if ($cat_filter) {
    $where .= " AND c.slug = ?";
    $params[] = $cat_filter;
}

$orderBy = match ($sort) {
    'precio_asc' => 'p.precio ASC',
    'precio_desc' => 'p.precio DESC',
    'nombre' => 'p.nombre ASC',
    'vendidos' => 'p.total_ventas DESC',
    default => 'p.fecha_creacion DESC',
};

// --- Fetch products ---
try {
    $sql = "SELECT p.*, c.nombre AS categoria_nombre, c.nombre_en AS categoria_nombre_en, c.slug AS categoria_slug
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            $where
            ORDER BY $orderBy";
    $stmt = pdo()->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll();
} catch (Exception $e) {
    $productos = [];
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda — BTLDECO</title>
    <meta name="description" content="BTLDECO Tienda — Todos nuestros productos de decoracion.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css?v=7">
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar" id="navbar">
        <div class="container navbar__inner">
            <a href="index.php" class="navbar__logo">BTLDECO<span class="logo-dot"></span></a>
            <ul class="navbar__links" id="navLinks">
                <li><a href="index.php"><?= t('home') ?></a></li>
                <li><a href="tienda.php" class="active"><?= t('shop') ?></a></li>
                <li><a href="index.php#galeria"><?= t('gallery') ?></a></li>
                <li><a href="index.php#nosotros"><?= t('about') ?></a></li>
                <li><a href="#contacto"><?= t('contact') ?></a></li>
            </ul>
            <div class="navbar__actions">
                <a href="tienda.php" class="btn btn--primary btn--sm"><?= t('shop_btn') ?></a>
                <button class="theme-toggle" id="themeToggle" aria-label="Cambiar tema">
                    <svg class="theme-toggle__sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg class="theme-toggle__moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                </button>
                <button class="cart-btn" id="cartBtn" aria-label="Carrito">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                    <span class="cart-badge" id="cartBadge">0</span>
                </button>
                <button class="navbar__toggle" id="navToggle" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- TIENDA -->
    <main class="tienda">
        <div class="container">
            <nav class="breadcrumb">
                <a href="index.php"><?= t('breadcrumb_home') ?></a>
                <span class="breadcrumb__sep">/</span>
                <span><?= t('breadcrumb_shop') ?></span>
            </nav>
        </div>

        <div class="container">
            <div class="tienda__header">
                <h1 class="tienda__title"><?= t('shop_title') ?></h1>
                <p class="tienda__subtitle"><?= t('shop_subtitle') ?></p>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="container">
            <div class="tienda__toolbar">
                <div class="tienda__filters">
                    <a href="tienda.php" class="filter-pill <?= $cat_filter === '' ? 'active' : '' ?>"><?= t('filter_all') ?></a>
                    <?php foreach ($cats as $cat):
                        $catName = (current_lang() === 'en' && !empty($cat['nombre_en'])) ? $cat['nombre_en'] : $cat['nombre'];
                    ?>
                        <a href="tienda.php?cat=<?= urlencode($cat['slug']) ?>" class="filter-pill <?= $cat_filter === $cat['slug'] ? 'active' : '' ?>">
                            <?= sanitize($catName) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="tienda__sort">
                    <select class="sort-select" onchange="window.location='tienda.php?cat=<?= urlencode($cat_filter) ?>&sort='+this.value">
                        <option value="recientes" <?= $sort === 'recientes' ? 'selected' : '' ?>><?= t('sort_recent') ?></option>
                        <option value="precio_asc" <?= $sort === 'precio_asc' ? 'selected' : '' ?>><?= t('sort_price_asc') ?></option>
                        <option value="precio_desc" <?= $sort === 'precio_desc' ? 'selected' : '' ?>><?= t('sort_price_desc') ?></option>
                        <option value="nombre" <?= $sort === 'nombre' ? 'selected' : '' ?>><?= t('sort_name') ?></option>
                        <option value="vendidos" <?= $sort === 'vendidos' ? 'selected' : '' ?>><?= t('sort_sold') ?></option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="container">
            <?php if (empty($productos)): ?>
                <div class="tienda__empty">
                    <p><?= $cat_filter ? t('no_products_cat') : t('no_products') ?>.</p>
                    <?php if ($cat_filter): ?>
                        <a href="tienda.php" class="btn btn--outline" style="margin-top:16px;"><?= t('view_all') ?></a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="tienda__grid" id="tiendaGrid">
                    <?php foreach ($productos as $p): ?>
                        <div class="tienda-card">
                            <a href="producto_detalle.php?slug=<?= urlencode($p['slug']) ?>" class="tienda-card__link">
                                <div class="tienda-card__image">
                                    <?php if ($p['precio_oferta'] && $p['precio_oferta'] < $p['precio']): ?>
                                        <span class="tienda-card__badge"><?= t('offer') ?></span>
                                    <?php elseif ($p['destacado']): ?>
                                        <span class="tienda-card__badge tienda-card__badge--new"><?= t('featured') ?></span>
                                    <?php endif; ?>
                                    <img src="<?= img_url($p['imagen_principal']) ?>" alt="<?= sanitize($p['nombre']) ?>" loading="lazy">
                                </div>
                                <div class="tienda-card__info">
                                    <h3 class="tienda-card__name"><?= sanitize($p['nombre']) ?></h3>
                                    <?php if ($p['precio_oferta'] && $p['precio_oferta'] < $p['precio']): ?>
                                        <span class="tienda-card__price">
                                            <?= price($p['precio_oferta']) ?>
                                            <span class="tienda-card__price-old"><?= price($p['precio']) ?></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="tienda-card__price"><?= price($p['precio']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <?php if ($p['stock'] > 0): ?>
                            <button class="btn--add-cart" onclick="event.stopPropagation(); window.btlCart.add(<?= $p['id'] ?>)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                                <?= t('add_to_cart') ?>
                            </button>
                            <?php else: ?>
                            <span class="btn--add-cart" style="opacity:0.5;cursor:default;"><?= t('sold_out') ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- CONTACTO -->
    <section class="section contact" id="contacto">
        <div class="container">
            <div class="contact__grid">
                <div class="contact__info">
                    <span class="section__tag"><?= t("contact") ?></span>
                    <h2 class="section__title"><?= t("contact_title") ?></h2>
                    <p class="contact__desc"><?= t("contact_desc") ?></p>
                    <div class="contact__cards">
                        <a href="mailto:hola@btldeco.com.ar" class="contact-card">
                            <div class="contact-card__icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
                            <div><span class="contact-card__label"><?= t("email_label") ?></span><span class="contact-card__value">hola@btldeco.com.ar</span></div>
                        </a>
                        <a href="https://wa.me/5491162743425" target="_blank" class="contact-card">
                            <div class="contact-card__icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg></div>
                            <div><span class="contact-card__label">WHATSAPP</span><span class="contact-card__value">+54 11 6274-3425</span></div>
                        </a>
                        <div class="contact-card">
                            <div class="contact-card__icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
                            <div><span class="contact-card__label"><?= t("location") ?></span><span class="contact-card__value">Buenos Aires, Argentina</span></div>
                        </div>
                    </div>
                </div>
                <form class="contact__form">
                    <div class="form-row">
                        <div class="form-group"><label for="name"><?= t("name_label") ?></label><input type="text" id="name" placeholder="<?= t("ph_name") ?>" required></div>
                        <div class="form-group"><label for="phone"><?= t("phone_label") ?></label><input type="tel" id="phone" placeholder="<?= t("ph_phone") ?>"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label for="email"><?= t("email_label") ?></label><input type="email" id="email" placeholder="<?= t("ph_email") ?>" required></div>
                        <div class="form-group">
                            <label for="product"><?= t("product_label") ?></label>
                            <select id="product">
                                <option value="" disabled selected><?= t("select_product") ?></option>
                                <option><?= t("custom_order") ?></option>
                                <option><?= t("general_inquiry") ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group"><label for="message"><?= t("message_label") ?></label><textarea id="message" rows="4" placeholder="<?= t("ph_message") ?>"></textarea></div>
                    <button type="submit" class="btn btn--primary btn--lg btn--full"><?= t("send_message") ?> <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg></button>
                </form>
            </div>
            <div class="contact__bottom"><p><?= t("copyright") ?></p></div>
        </div>
    </section>

    <a href="https://wa.me/5491162743425" target="_blank" class="float-btn float-btn--wa" aria-label="WhatsApp"><svg width="24" height="24" viewBox="0 0 24 24" fill="#fff"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.611.611l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.37 0-4.567-.68-6.434-1.852l-.448-.29-2.648.888.888-2.648-.29-.448A9.96 9.96 0 012 12C2 6.486 6.486 2 12 2s10 4.486 10 10-4.486 10-10 10z"/></svg></a>
    <button class="float-btn float-btn--top" id="scrollTop" aria-label="Volver arriba"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15l-6-6-6 6"/></svg></button>

    <?php include "includes/cart_drawer.php"; ?>
    <script src="js/main.js?v=7"></script>
</body>
</html>
