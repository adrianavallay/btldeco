<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lang.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) { redirect('tienda.php'); }

// Fetch product
try {
    $stmt = pdo()->prepare("SELECT p.*, c.nombre AS categoria_nombre, c.nombre_en AS categoria_nombre_en, c.slug AS categoria_slug
                            FROM productos p
                            LEFT JOIN categorias c ON p.categoria_id = c.id
                            WHERE p.slug = ? AND p.estado = 'activo'");
    $stmt->execute([$slug]);
    $p = $stmt->fetch();
} catch (Exception $e) {
    $p = null;
}

if (!$p) { redirect('tienda.php'); }

// Fetch gallery images
try {
    $imgs = pdo()->prepare("SELECT imagen FROM producto_imagenes WHERE producto_id = ? ORDER BY orden");
    $imgs->execute([$p['id']]);
    $gallery = $imgs->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $gallery = [];
}

// Main image + gallery
$allImages = [];
if ($p['imagen_principal']) $allImages[] = $p['imagen_principal'];
foreach ($gallery as $gi) {
    if ($gi && !in_array($gi, $allImages)) $allImages[] = $gi;
}
if (empty($allImages)) $allImages[] = '';

// Fetch variants
try {
    $vars = pdo()->prepare("SELECT * FROM producto_variantes WHERE producto_id = ? ORDER BY nombre, valor");
    $vars->execute([$p['id']]);
    $variantes = $vars->fetchAll();
} catch (Exception $e) {
    $variantes = [];
}

// Related products (same category)
try {
    $rel = pdo()->prepare("SELECT p.*, c.slug AS categoria_slug FROM productos p
                           LEFT JOIN categorias c ON p.categoria_id = c.id
                           WHERE p.categoria_id = ? AND p.id != ? AND p.estado = 'activo'
                           ORDER BY RAND() LIMIT 4");
    $rel->execute([$p['categoria_id'], $p['id']]);
    $related = $rel->fetchAll();
} catch (Exception $e) {
    $related = [];
}

// If less than 4 related, fill with others
if (count($related) < 4) {
    $excludeIds = array_merge([$p['id']], array_column($related, 'id'));
    $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
    try {
        $fill = pdo()->prepare("SELECT p.*, c.slug AS categoria_slug FROM productos p
                                LEFT JOIN categorias c ON p.categoria_id = c.id
                                WHERE p.id NOT IN ($placeholders) AND p.estado = 'activo'
                                ORDER BY RAND() LIMIT " . (4 - count($related)));
        $fill->execute($excludeIds);
        $related = array_merge($related, $fill->fetchAll());
    } catch (Exception $e) {}
}

$precio_actual = ($p['precio_oferta'] && $p['precio_oferta'] < $p['precio']) ? $p['precio_oferta'] : $p['precio'];
$waText = urlencode('Hola! Me interesa el producto: ' . $p['nombre'] . ' (' . price($precio_actual) . ')');
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= seo_tags($p['meta_titulo'] ?: $p['nombre'], $p['meta_descripcion'] ?: ($p['descripcion_corta'] ?: substr($p['descripcion'], 0, 160)), $p['imagen_principal'] ? 'uploads/productos/' . $p['imagen_principal'] : '', 'product') ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css?v=7">
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar" id="navbar">
        <div class="container navbar__inner">
            <a href="https://btldeco.com.ar/" class="navbar__logo">BTLDECO<span class="logo-dot"></span></a>
            <ul class="navbar__links" id="navLinks">
                <li><a href="index.php"><?= t("home") ?></a></li>
                <li><a href="tienda.php"><?= t("shop") ?></a></li>
                <li><a href="index.php#galeria"><?= t("gallery") ?></a></li>
                <li><a href="index.php#nosotros"><?= t("about") ?></a></li>
                <li><a href="#contacto"><?= t("contact") ?></a></li>
            </ul>
            <div class="navbar__actions">
                <a href="tienda.php" class="btn btn--primary btn--sm"><?= t("shop_btn") ?></a>
                <button class="theme-toggle" id="themeToggle" aria-label="Cambiar tema">
                    <svg class="theme-toggle__sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg class="theme-toggle__moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                </button>
                <a href="<?= is_cliente() ? 'mi-cuenta.php' : 'login.php' ?>" class="cart-btn" aria-label="<?= is_cliente() ? 'Mi cuenta' : 'Ingresar' ?>" title="<?= is_cliente() ? 'Mi cuenta' : 'Ingresar' ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </a>
                <button class="cart-btn" id="cartBtn" aria-label="Carrito">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                    <?php $cc = cart_count(); ?>
                    <span class="cart-badge" id="cartBadge"<?= $cc > 0 ? '' : ' style="display:none;"' ?>><?= $cc ?></span>
                </button>
                <button class="navbar__toggle" id="navToggle" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- PRODUCTO -->
    <main class="producto">
        <div class="container">
            <nav class="breadcrumb">
                <a href="index.php"><?= t("breadcrumb_home") ?></a>
                <span class="breadcrumb__sep">/</span>
                <a href="tienda.php"><?= t("breadcrumb_shop") ?></a>
                <span class="breadcrumb__sep">/</span>
                <?php if ($p['categoria_nombre']): ?>
                    <a href="tienda.php?cat=<?= urlencode($p['categoria_slug']) ?>"><?= sanitize(cat_name($p)) ?></a>
                    <span class="breadcrumb__sep">/</span>
                <?php endif; ?>
                <span><?= sanitize($p['nombre']) ?></span>
            </nav>
        </div>

        <div class="container">
            <div class="producto__layout">
                <!-- Gallery -->
                <div class="producto__gallery">
                    <div class="producto__main-img" id="zoomContainer">
                        <img id="productoMainImg" src="<?= img_url($allImages[0]) ?>" alt="<?= sanitize($p['nombre']) ?>">
                        <div class="producto__zoom-lens" id="zoomLens"></div>
                    </div>
                    <?php if (count($allImages) > 1): ?>
                    <div class="producto__thumbs">
                        <?php foreach ($allImages as $i => $img): ?>
                            <button class="producto__thumb <?= $i === 0 ? 'active' : '' ?>" onclick="changeImage(this, '<?= img_url($img) ?>')">
                                <img src="<?= img_url($img) ?>" alt="<?= sanitize($p['nombre']) ?> vista <?= $i + 1 ?>">
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="producto__info">
                    <?php if ($p['categoria_nombre']): ?>
                        <span class="producto__category"><?= sanitize(cat_name($p)) ?></span>
                    <?php endif; ?>
                    <h1 class="producto__name"><?= sanitize($p['nombre']) ?></h1>

                    <?php if ($p['precio_oferta'] && $p['precio_oferta'] < $p['precio']): ?>
                        <span class="producto__price">
                            <?= price($p['precio_oferta']) ?>
                            <span class="producto__price-old"><?= price($p['precio']) ?></span>
                        </span>
                    <?php else: ?>
                        <span class="producto__price"><?= price($p['precio']) ?></span>
                    <?php endif; ?>

                    <?php if ($p['descripcion_corta']): ?>
                        <p class="producto__desc"><?= sanitize($p['descripcion_corta']) ?></p>
                    <?php endif; ?>

                    <?php if ($p['descripcion']): ?>
                        <div class="producto__desc-full"><?= nl2br(sanitize($p['descripcion'])) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($variantes)): ?>
                    <div class="producto__variantes">
                        <?php
                        $grouped = [];
                        foreach ($variantes as $v) { $grouped[$v['nombre']][] = $v; }
                        foreach ($grouped as $nombre => $vals): ?>
                            <div class="producto__variante-group">
                                <strong><?= sanitize($nombre) ?></strong>
                                <div class="producto__variante-options">
                                    <?php foreach ($vals as $val): ?>
                                        <span class="producto__variante-pill"><?= sanitize($val['valor']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="producto__details">
                        <?php if ($p['stock'] > 0): ?>
                        <div class="producto__detail">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <div><strong><?= t("stock") ?></strong><span><?= $p['stock'] ?> disponibles</span></div>
                        </div>
                        <?php else: ?>
                        <div class="producto__detail">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            <div><strong><?= t("stock") ?></strong><span>Agotado — Consultar</span></div>
                        </div>
                        <?php endif; ?>
                        <div class="producto__detail">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <div><strong><?= t("delivery") ?></strong><span><?= t("delivery_time") ?></span></div>
                        </div>
                        <div class="producto__detail">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <div><strong><?= t("shipping") ?></strong><span><?= t("shipping_where") ?></span></div>
                        </div>
                    </div>

                    <?php if ($p['stock'] > 0): ?>
                    <!-- Quantity selector -->
                    <div class="producto__qty-selector">
                        <button class="qty-btn" onclick="changeQty(-1)">−</button>
                        <span id="prodQty">1</span>
                        <button class="qty-btn" onclick="changeQty(1)">+</button>
                    </div>

                    <div class="producto__actions">
                        <button class="btn btn--primary btn--lg btn--full" onclick="window.btlCart.add(<?= $p['id'] ?>, parseInt(document.getElementById('prodQty').textContent))">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                            <?= t("add_to_cart") ?>
                        </button>
                        <a href="https://wa.me/5491162743425?text=<?= $waText ?>" target="_blank" class="btn btn--outline btn--lg btn--full">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.611.611l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.37 0-4.567-.68-6.434-1.852l-.448-.29-2.648.888.888-2.648-.29-.448A9.96 9.96 0 012 12C2 6.486 6.486 2 12 2s10 4.486 10 10-4.486 10-10 10z"/></svg>
                            <?= t("ask_whatsapp") ?>
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="producto__actions">
                        <span class="btn btn--outline btn--lg btn--full" style="opacity:0.5;justify-content:center;"><?= t("product_sold_out") ?></span>
                        <a href="https://wa.me/5491162743425?text=<?= $waText ?>" target="_blank" class="btn btn--primary btn--lg btn--full">
                            <?= t("check_availability") ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Related -->
        <?php if (!empty($related)): ?>
        <div class="container">
            <div class="producto__related">
                <h2 class="producto__related-title"><?= t("related") ?></h2>
                <div class="producto__related-grid">
                    <?php foreach ($related as $r): ?>
                        <a href="producto_detalle.php?slug=<?= urlencode($r['slug']) ?>" class="tienda-card">
                            <div class="tienda-card__image">
                                <img src="<?= img_url($r['imagen_principal']) ?>" alt="<?= sanitize($r['nombre']) ?>" loading="lazy">
                            </div>
                            <div class="tienda-card__info">
                                <h3 class="tienda-card__name"><?= sanitize($r['nombre']) ?></h3>
                                <span class="tienda-card__price"><?= price(($r['precio_oferta'] && $r['precio_oferta'] < $r['precio']) ? $r['precio_oferta'] : $r['precio']) ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
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
                    </div>
                </div>
                <form class="contact__form">
                    <div class="form-row">
                        <div class="form-group"><label for="name"><?= t("name_label") ?></label><input type="text" id="name" placeholder="<?= t("ph_name") ?>" required></div>
                        <div class="form-group"><label for="phone"><?= t("phone_label") ?></label><input type="tel" id="phone" placeholder="<?= t("ph_phone") ?>"></div>
                    </div>
                    <div class="form-group"><label for="email"><?= t("email_label") ?></label><input type="email" id="email" placeholder="<?= t("ph_email") ?>" required></div>
                    <div class="form-group"><label for="message"><?= t("message_label") ?></label><textarea id="message" rows="4" placeholder="<?= t("ph_message") ?>"></textarea></div>
                    <button type="submit" class="btn btn--primary btn--lg btn--full"><?= t("send_message") ?></button>
                </form>
            </div>
            <div class="contact__bottom"><p><?= t("copyright") ?></p></div>
        </div>
    </section>

    <a href="https://wa.me/5491162743425" target="_blank" class="float-btn float-btn--wa" aria-label="WhatsApp"><svg width="24" height="24" viewBox="0 0 24 24" fill="#fff"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.611.611l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.37 0-4.567-.68-6.434-1.852l-.448-.29-2.648.888.888-2.648-.29-.448A9.96 9.96 0 012 12C2 6.486 6.486 2 12 2s10 4.486 10 10-4.486 10-10 10z"/></svg></a>
    <button class="float-btn float-btn--top" id="scrollTop" aria-label="Volver arriba"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15l-6-6-6 6"/></svg></button>

    <?php include "includes/cart_drawer.php"; ?>
    <script src="js/main.js?v=7"></script>
    <script>
    function changeQty(delta) {
        var el = document.getElementById('prodQty');
        var qty = parseInt(el.textContent) + delta;
        if (qty < 1) qty = 1;
        if (qty > 99) qty = 99;
        el.textContent = qty;
    }

    function changeImage(btn, src) {
        document.getElementById('productoMainImg').src = src;
        document.querySelectorAll('.producto__thumb').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
    }

    // Zoom on hover
    (function() {
        const container = document.getElementById('zoomContainer');
        const img = document.getElementById('productoMainImg');
        const lens = document.getElementById('zoomLens');
        if (!container || !img || !lens) return;

        container.addEventListener('mouseenter', function() {
            lens.style.backgroundImage = 'url(' + img.src + ')';
            lens.classList.add('active');
        });

        container.addEventListener('mouseleave', function() {
            lens.classList.remove('active');
        });

        container.addEventListener('mousemove', function(e) {
            const rect = container.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width * 100;
            const y = (e.clientY - rect.top) / rect.height * 100;
            lens.style.backgroundPosition = x + '% ' + y + '%';
            lens.style.left = (e.clientX - rect.left - 75) + 'px';
            lens.style.top = (e.clientY - rect.top - 75) + 'px';
        });

        // Touch zoom for mobile
        container.addEventListener('touchmove', function(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const rect = container.getBoundingClientRect();
            const x = (touch.clientX - rect.left) / rect.width * 100;
            const y = (touch.clientY - rect.top) / rect.height * 100;
            lens.style.backgroundImage = 'url(' + img.src + ')';
            lens.style.backgroundPosition = x + '% ' + y + '%';
            lens.style.left = (touch.clientX - rect.left - 75) + 'px';
            lens.style.top = (touch.clientY - rect.top - 75) + 'px';
            lens.classList.add('active');
        }, { passive: false });

        container.addEventListener('touchend', function() {
            lens.classList.remove('active');
        });
    })();
    </script>
</body>
</html>
