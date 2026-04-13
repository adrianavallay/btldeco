<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';

// Cart check
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    redirect('tienda.php');
}

// Cart summary
$subtotal = 0;
$cart_items = [];
foreach ($cart as $key => $item) {
    $line_total = $item['precio'] * $item['qty'];
    $subtotal += $line_total;
    $cart_items[] = array_merge($item, ['key' => $key, 'line_total' => $line_total]);
}

$descuento = 0;
$cupon = $_SESSION['cupon'] ?? null;
if ($cupon) {
    if ($cupon['tipo'] === 'porcentaje') {
        $descuento = round($subtotal * $cupon['valor'] / 100, 2);
    } else {
        $descuento = min($cupon['valor'], $subtotal);
    }
}
$total = max(0, $subtotal - $descuento);

// Pre-fill client data
$cliente = null;
if (is_cliente()) {
    $cliente = cliente_data();
}

$flash_error = flash('error');
$flash_success = flash('success');
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — BTLDECO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css?v=2">
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar" id="navbar">
        <div class="container navbar__inner">
            <a href="index.php" class="navbar__logo">BTLDECO<span class="logo-dot"></span></a>
            <ul class="navbar__links" id="navLinks">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="tienda.php">Tienda</a></li>
                <li><a href="index.php#galeria">Galeria</a></li>
                <li><a href="index.php#nosotros">Nosotros</a></li>
                <li><a href="index.php#contacto">Contacto</a></li>
            </ul>
            <div class="navbar__actions">
                <a href="tienda.php" class="btn btn--primary btn--sm">TIENDA</a>
                <div class="lang-switch" id="langSwitch">
                    <button class="lang-switch__btn active" data-lang="es">ES</button>
                    <button class="lang-switch__btn" data-lang="en">EN</button>
                    <div class="lang-switch__indicator"></div>
                </div>
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

    <!-- CHECKOUT -->
    <main class="checkout-page">
        <div class="container">
            <nav class="breadcrumb">
                <a href="index.php">Inicio</a>
                <span class="breadcrumb__sep">/</span>
                <a href="tienda.php">Tienda</a>
                <span class="breadcrumb__sep">/</span>
                <span>Checkout</span>
            </nav>
        </div>

        <div class="container">
            <div class="checkout-header">
                <h1 class="checkout-title">Finalizar compra</h1>
                <p class="checkout-subtitle">Completa tus datos para procesar el pedido</p>
            </div>

            <?php if ($flash_error): ?>
            <div class="checkout-alert checkout-alert--error">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <?= sanitize($flash_error) ?>
            </div>
            <?php endif; ?>

            <?php if ($flash_success): ?>
            <div class="checkout-alert checkout-alert--success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <?= sanitize($flash_success) ?>
            </div>
            <?php endif; ?>

            <form action="checkout_process.php" method="POST">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

                <div class="checkout-grid">
                    <!-- LEFT: Shipping Form -->
                    <div class="checkout-form">
                        <div class="checkout-section">
                            <h2 class="checkout-section__title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                Datos de envio
                            </h2>

                            <div class="form-group">
                                <label for="ck-nombre">Nombre completo *</label>
                                <input type="text" id="ck-nombre" name="nombre" required
                                       value="<?= sanitize($cliente['nombre'] ?? '') ?>"
                                       placeholder="Tu nombre completo">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="ck-email">Email *</label>
                                    <input type="email" id="ck-email" name="email" required
                                           value="<?= sanitize($cliente['email'] ?? ($_SESSION['cliente_email'] ?? '')) ?>"
                                           placeholder="tu@email.com">
                                </div>
                                <div class="form-group">
                                    <label for="ck-telefono">Telefono</label>
                                    <input type="tel" id="ck-telefono" name="telefono"
                                           value="<?= sanitize($cliente['telefono'] ?? '') ?>"
                                           placeholder="+54 11 1234-5678">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="ck-direccion">Direccion *</label>
                                <input type="text" id="ck-direccion" name="direccion" required
                                       value="<?= sanitize($cliente['direccion'] ?? '') ?>"
                                       placeholder="Calle, numero, piso, depto">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="ck-ciudad">Ciudad *</label>
                                    <input type="text" id="ck-ciudad" name="ciudad" required
                                           value="<?= sanitize($cliente['ciudad'] ?? '') ?>"
                                           placeholder="Tu ciudad">
                                </div>
                                <div class="form-group">
                                    <label for="ck-provincia">Provincia *</label>
                                    <input type="text" id="ck-provincia" name="provincia" required
                                           value="<?= sanitize($cliente['provincia'] ?? '') ?>"
                                           placeholder="Tu provincia">
                                </div>
                            </div>
                        </div>

                        <!-- Security badges -->
                        <div class="checkout-trust">
                            <div class="checkout-trust__item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                <span>Pago seguro</span>
                            </div>
                            <div class="checkout-trust__item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                <span>Datos encriptados</span>
                            </div>
                            <div class="checkout-trust__item">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                <span>Envio a todo el pais</span>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: Order Summary -->
                    <div class="checkout-summary">
                        <h2 class="checkout-section__title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                            Resumen del pedido
                        </h2>

                        <!-- Items -->
                        <div class="checkout-items">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="checkout-item">
                                <div class="checkout-item__img">
                                    <?php
                                    $imgSrc = $item['imagen'] ?? '';
                                    if ($imgSrc && !str_starts_with($imgSrc, 'http')) {
                                        $imgSrc = 'uploads/productos/' . $imgSrc;
                                    }
                                    ?>
                                    <img src="<?= $imgSrc ?: 'assets/no-image.svg' ?>" alt="<?= sanitize($item['nombre']) ?>">
                                </div>
                                <div class="checkout-item__info">
                                    <span class="checkout-item__name"><?= sanitize($item['nombre']) ?></span>
                                    <?php if (!empty($item['variante'])): ?>
                                        <span class="checkout-item__variant"><?= sanitize($item['variante']) ?></span>
                                    <?php endif; ?>
                                    <span class="checkout-item__qty"><?= $item['qty'] ?> x <?= price($item['precio']) ?></span>
                                </div>
                                <span class="checkout-item__total"><?= price($item['line_total']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Coupon -->
                        <?php if (!$cupon): ?>
                        <div class="checkout-coupon">
                            <input type="text" name="coupon_code" placeholder="Codigo de cupon" class="checkout-coupon__input">
                            <button type="button" id="apply-coupon-btn" class="checkout-coupon__btn">Aplicar</button>
                        </div>
                        <?php else: ?>
                        <div class="checkout-coupon checkout-coupon--active">
                            <span class="checkout-coupon__applied">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                <?= sanitize($cupon['codigo']) ?>
                            </span>
                            <button type="button" id="remove-coupon-btn" class="checkout-coupon__remove">Quitar</button>
                        </div>
                        <?php endif; ?>

                        <!-- Totals -->
                        <div class="checkout-totals">
                            <div class="checkout-totals__row">
                                <span>Subtotal</span>
                                <span><?= price($subtotal) ?></span>
                            </div>
                            <?php if ($descuento > 0): ?>
                            <div class="checkout-totals__row checkout-totals__row--discount">
                                <span>Descuento</span>
                                <span>-<?= price($descuento) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="checkout-totals__row checkout-totals__row--total">
                                <span>Total</span>
                                <span><?= price($total) ?></span>
                            </div>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn--primary btn--lg btn--full checkout-submit">
                            CONFIRMAR PEDIDO
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </button>

                        <p class="checkout-secure-text">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                            Pago seguro procesado por MercadoPago
                        </p>

                        <a href="tienda.php" class="cart-continue">Volver a la tienda</a>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- FOOTER -->
    <div class="contact__bottom" style="padding:20px 24px;">
        <p>&copy; 2026 BTLDECO. Todos los derechos reservados.</p>
    </div>

    <?php include "includes/cart_drawer.php"; ?>
    <script src="js/main.js?v=2"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var applyBtn = document.getElementById('apply-coupon-btn');
        if (applyBtn) {
            applyBtn.addEventListener('click', function() {
                var code = document.querySelector('input[name="coupon_code"]').value.trim();
                if (!code) return;
                var fd = new FormData();
                fd.append('action', 'apply_coupon');
                fd.append('codigo', code);
                fetch('carrito_api.php', { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.ok) location.reload();
                        else alert(data.mensaje || 'Cupon invalido');
                    });
            });
        }
        var removeBtn = document.getElementById('remove-coupon-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                var fd = new FormData();
                fd.append('action', 'remove_coupon');
                fetch('carrito_api.php', { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(data) { if (data.ok) location.reload(); });
            });
        }
    });
    </script>
</body>
</html>
