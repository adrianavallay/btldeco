<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/auth_helper.php";
require_once __DIR__ . "/lang.php";

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
    <link rel="stylesheet" href="css/styles.css?v=12">
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar" id="navbar">
        <div class="container navbar__inner">
            <a href="index.php" class="navbar__logo">BTLDECO<span class="logo-dot"></span></a>
            <ul class="navbar__links" id="navLinks">
                <li><a href="index.php"><?= t("home") ?></a></li>
                <li><a href="tienda.php"><?= t("shop") ?></a></li>
                <li><a href="index.php#galeria"><?= t("gallery") ?></a></li>
                <li><a href="index.php#nosotros"><?= t("about") ?></a></li>
                <li><a href="index.php#contacto"><?= t("contact") ?></a></li>
            </ul>
            <div class="navbar__actions">
                <a href="tienda.php" class="btn btn--primary btn--sm"><?= t("shop_btn") ?></a>
                <button class="theme-toggle" id="themeToggle" aria-label="Cambiar tema">
                    <svg class="theme-toggle__sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg class="theme-toggle__moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
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
                <a href="index.php"><?= t("breadcrumb_home") ?></a>
                <span class="breadcrumb__sep">/</span>
                <a href="tienda.php"><?= t("breadcrumb_shop") ?></a>
                <span class="breadcrumb__sep">/</span>
                <span><?= t("breadcrumb_checkout") ?></span>
            </nav>
        </div>

        <div class="container">
            <div class="checkout-header">
                <h1 class="checkout-title"><?= t("checkout_title") ?></h1>
                <p class="checkout-subtitle"><?= t("checkout_subtitle") ?></p>
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

            <form action="checkout_process.php" method="POST" id="checkoutForm">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="metodo_pago" id="metodoPagoInput" value="mercadopago">

                <div class="checkout-grid">

                    <!-- ═══ LEFT COLUMN ═══ -->
                    <div class="checkout-form">

                        <!-- STEP 1: PRODUCTOS -->
                        <div class="checkout-section">
                            <h2 class="checkout-section__title">
                                <span class="checkout-step">1</span>
                                <?= t("step_order") ?>
                            </h2>

                            <div class="checkout-cart" id="checkoutCart">
                                <?php foreach ($cart_items as $item): ?>
                                <div class="ck-item" data-key="<?= $item['key'] ?>">
                                    <div class="ck-item__img">
                                        <?php
                                        $imgSrc = $item['imagen'] ?? '';
                                        if ($imgSrc && !str_starts_with($imgSrc, 'http')) $imgSrc = 'uploads/productos/' . $imgSrc;
                                        ?>
                                        <img src="<?= $imgSrc ?: 'assets/no-image.svg' ?>" alt="<?= sanitize($item['nombre']) ?>">
                                    </div>
                                    <div class="ck-item__details">
                                        <span class="ck-item__name"><?= sanitize($item['nombre']) ?></span>
                                        <?php if (!empty($item['variante'])): ?>
                                            <span class="ck-item__variant"><?= sanitize($item['variante']) ?></span>
                                        <?php endif; ?>
                                        <span class="ck-item__unit-price"><?= price($item['precio']) ?> c/u</span>
                                    </div>
                                    <div class="ck-item__controls">
                                        <div class="ck-item__qty">
                                            <button type="button" class="qty-btn" onclick="ckUpdateQty('<?= $item['key'] ?>', <?= $item['qty'] - 1 ?>)">−</button>
                                            <span><?= $item['qty'] ?></span>
                                            <button type="button" class="qty-btn" onclick="ckUpdateQty('<?= $item['key'] ?>', <?= $item['qty'] + 1 ?>)">+</button>
                                        </div>
                                        <span class="ck-item__total"><?= price($item['line_total']) ?></span>
                                        <button type="button" class="ck-item__remove" onclick="ckRemove('<?= $item['key'] ?>')" title="Eliminar">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Coupon -->
                            <?php if (!$cupon): ?>
                            <div class="checkout-coupon">
                                <input type="text" name="coupon_code" placeholder="<?= t("coupon_placeholder") ?>" class="checkout-coupon__input">
                                <button type="button" id="apply-coupon-btn" class="checkout-coupon__btn"><?= t("coupon_apply") ?></button>
                            </div>
                            <?php else: ?>
                            <div class="checkout-coupon checkout-coupon--active">
                                <span class="checkout-coupon__applied">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    <?= sanitize($cupon['codigo']) ?>
                                </span>
                                <button type="button" id="remove-coupon-btn" class="checkout-coupon__remove"><?= t("coupon_remove") ?></button>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- STEP 2: DATOS DE ENVIO -->
                        <div class="checkout-section">
                            <h2 class="checkout-section__title">
                                <span class="checkout-step">2</span>
                                <?= t("step_shipping") ?>
                            </h2>

                            <div class="form-group">
                                <label for="ck-nombre"><?= t("full_name") ?> *</label>
                                <input type="text" id="ck-nombre" name="nombre" required
                                       value="<?= sanitize($cliente['nombre'] ?? '') ?>"
                                       placeholder="Tu nombre completo">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="ck-email"><?= t("email") ?> *</label>
                                    <input type="email" id="ck-email" name="email" required
                                           value="<?= sanitize($cliente['email'] ?? ($_SESSION['cliente_email'] ?? '')) ?>"
                                           placeholder="tu@email.com">
                                </div>
                                <div class="form-group">
                                    <label for="ck-telefono"><?= t("phone") ?></label>
                                    <input type="tel" id="ck-telefono" name="telefono"
                                           value="<?= sanitize($cliente['telefono'] ?? '') ?>"
                                           placeholder="+54 11 1234-5678">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group" style="flex:2;">
                                    <label for="ck-direccion"><?= t("address") ?> *</label>
                                    <input type="text" id="ck-direccion" name="direccion" required
                                           value="<?= sanitize($cliente['direccion'] ?? '') ?>"
                                           placeholder="Calle">
                                </div>
                                <div class="form-group" style="flex:1;">
                                    <label for="ck-numero"><?= t("number") ?> *</label>
                                    <input type="text" id="ck-numero" name="numero" required
                                           placeholder="1234">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="ck-piso"><?= t("floor_apt") ?> <span style="color:var(--text-muted);font-weight:400;text-transform:none;"><?= t("floor_apt_opt") ?></span></label>
                                    <input type="text" id="ck-piso" name="piso_depto"
                                           placeholder="Ej: 3ro B">
                                </div>
                                <div class="form-group">
                                    <label for="ck-cp"><?= t("zip_code") ?></label>
                                    <input type="text" id="ck-cp" name="codigo_postal"
                                           placeholder="Ej: 1414" maxlength="8">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="ck-ciudad"><?= t("city") ?> *</label>
                                    <input type="text" id="ck-ciudad" name="ciudad" required
                                           value="<?= sanitize($cliente['ciudad'] ?? '') ?>"
                                           placeholder="Tu barrio o localidad">
                                </div>
                                <div class="form-group">
                                    <label for="ck-provincia"><?= t("province") ?> *</label>
                                    <select id="ck-provincia" name="provincia" required>
                                        <option value="" disabled <?= empty($cliente['provincia'] ?? '') ? 'selected' : '' ?>><?= t("select_province") ?></option>
                                        <?php
                                        $provincias = ['Buenos Aires','CABA','Catamarca','Chaco','Chubut','Cordoba','Corrientes','Entre Rios','Formosa','Jujuy','La Pampa','La Rioja','Mendoza','Misiones','Neuquen','Rio Negro','Salta','San Juan','San Luis','Santa Cruz','Santa Fe','Santiago del Estero','Tierra del Fuego','Tucuman'];
                                        foreach ($provincias as $prov):
                                            $sel = (($cliente['provincia'] ?? '') === $prov) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $prov ?>" <?= $sel ?>><?= $prov ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 3: METODO DE PAGO -->
                        <div class="checkout-section">
                            <h2 class="checkout-section__title">
                                <span class="checkout-step">3</span>
                                <?= t("step_payment") ?>
                            </h2>

                            <div class="payment-methods">
                                <label class="payment-method active" id="pmMercadopago" onclick="selectPayment('mercadopago')">
                                    <div class="payment-method__radio">
                                        <span class="payment-method__dot"></span>
                                    </div>
                                    <div class="payment-method__icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                    </div>
                                    <div class="payment-method__info">
                                        <span class="payment-method__name"><?= t("mercadopago") ?></span>
                                        <span class="payment-method__desc"><?= t("mp_desc") ?></span>
                                    </div>
                                </label>

                                <label class="payment-method" id="pmTransferencia" onclick="selectPayment('transferencia')">
                                    <div class="payment-method__radio">
                                        <span class="payment-method__dot"></span>
                                    </div>
                                    <div class="payment-method__icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                                    </div>
                                    <div class="payment-method__info">
                                        <span class="payment-method__name"><?= t("transfer") ?></span>
                                        <span class="payment-method__desc"><?= t("transfer_desc") ?></span>
                                    </div>
                                </label>
                            </div>

                            <!-- Transfer details (hidden by default) -->
                            <div class="transfer-details" id="transferDetails" style="display:none;">
                                <div class="transfer-details__card">
                                    <p class="transfer-details__label"><?= t("transfer_data") ?></p>
                                    <div class="transfer-details__row">
                                        <span>Banco</span>
                                        <strong>Banco Galicia</strong>
                                    </div>
                                    <div class="transfer-details__row">
                                        <span>Titular</span>
                                        <strong>BTLDECO SRL</strong>
                                    </div>
                                    <div class="transfer-details__row">
                                        <span>CBU</span>
                                        <strong>0070000000000000000</strong>
                                    </div>
                                    <div class="transfer-details__row">
                                        <span>Alias</span>
                                        <strong>BTLDECO.PAGOS</strong>
                                    </div>
                                    <p class="transfer-details__note"><?= t("transfer_note") ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ RIGHT COLUMN: SUMMARY ═══ -->
                    <div class="checkout-summary">
                        <h2 class="checkout-section__title" style="margin-bottom:20px;"><?= t("summary") ?></h2>

                        <div class="checkout-totals">
                            <div class="checkout-totals__row">
                                <span>Productos (<?= array_sum(array_column($cart_items, 'qty')) ?>)</span>
                                <span><?= price($subtotal) ?></span>
                            </div>
                            <div class="checkout-totals__row">
                                <span><?= t("shipping") ?></span>
                                <span class="checkout-free-shipping"><?= t("free_shipping") ?></span>
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

                        <button type="submit" class="btn btn--primary btn--lg btn--full checkout-submit">
                            <?= t("confirm_order") ?>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </button>

                        <p class="checkout-secure-text">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                            <?= t("secure_payment") ?>
                        </p>

                        <!-- Trust -->
                        <div class="checkout-trust checkout-trust--vertical">
                            <div class="checkout-trust__item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                <span><?= t("safe_payment") ?></span>
                            </div>
                            <div class="checkout-trust__item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <span><?= t("fast_delivery") ?></span>
                            </div>
                            <div class="checkout-trust__item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                <span><?= t("free_ship") ?></span>
                            </div>
                        </div>

                        <a href="tienda.php" class="cart-continue"><?= t("back_to_shop") ?></a>
                    </div>

                </div>
            </form>
        </div>
    </main>

    <!-- FOOTER -->
    <div class="contact__bottom" style="padding:20px 24px;margin-top:40px;">
        <p><?= t("copyright") ?></p>
    </div>

    <script src="js/main.js?v=8"></script>
    <script>
    // Checkout cart operations
    function ckUpdateQty(key, qty) {
        if (qty < 1) { ckRemove(key); return; }
        fetch('carrito_api.php?action=update', {
            method: 'POST',
            body: new URLSearchParams({ key: key, qty: qty })
        }).then(function(r) { return r.json(); })
          .then(function(data) { if (data.ok) location.reload(); });
    }
    function ckRemove(key) {
        fetch('carrito_api.php?action=remove', {
            method: 'POST',
            body: new URLSearchParams({ key: key })
        }).then(function(r) { return r.json(); })
          .then(function(data) { if (data.ok) location.reload(); });
    }

    // Payment method selection
    function selectPayment(method) {
        document.getElementById('metodoPagoInput').value = method;
        document.querySelectorAll('.payment-method').forEach(function(el) { el.classList.remove('active'); });
        document.getElementById(method === 'mercadopago' ? 'pmMercadopago' : 'pmTransferencia').classList.add('active');
        document.getElementById('transferDetails').style.display = method === 'transferencia' ? 'block' : 'none';
    }

    // Coupon
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
