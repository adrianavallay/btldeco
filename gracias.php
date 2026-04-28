<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/lang.php";

$pedido_id = (int) ($_GET['id'] ?? 0);
$metodo = $_GET['metodo'] ?? '';

$pedido = null;
$items = [];
if ($pedido_id > 0) {
    try {
        $stmt = pdo()->prepare("SELECT * FROM pedidos WHERE id = ?");
        $stmt->execute([$pedido_id]);
        $pedido = $stmt->fetch();

        $stmtItems = pdo()->prepare("SELECT pi.*, p.imagen_principal FROM pedido_items pi LEFT JOIN productos p ON pi.producto_id = p.id WHERE pi.pedido_id = ?");
        $stmtItems->execute([$pedido_id]);
        $items = $stmtItems->fetchAll();
    } catch (Exception $e) {}
}

if ($pedido && $pedido['notas'] === 'transferencia') $metodo = 'transferencia';
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido confirmado — BTLDECO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css?v=7">
</head>
<body>

    <nav class="navbar" id="navbar">
        <div class="container navbar__inner">
            <a href="https://btldeco.com.ar/" class="navbar__logo">BTLDECO<span class="logo-dot"></span></a>
            <ul class="navbar__links" id="navLinks">
                <li><a href="/"><?= t("home") ?></a></li>
                <li><a href="tienda"><?= t("shop") ?></a></li>
                <li><a href="/#galeria"><?= t("gallery") ?></a></li>
                <li><a href="/#nosotros"><?= t("about") ?></a></li>
                <li><a href="/#contacto"><?= t("contact") ?></a></li>
            </ul>
            <div class="navbar__actions">
                <a href="tienda" class="btn btn--primary btn--sm"><?= t("shop_btn") ?></a>
                <button class="theme-toggle" id="themeToggle" aria-label="Cambiar tema">
                    <svg class="theme-toggle__sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg class="theme-toggle__moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                </button>
                <a href="<?= is_cliente() ? 'mi-cuenta' : 'login' ?>" class="cart-btn" aria-label="<?= is_cliente() ? 'Mi cuenta' : 'Ingresar' ?>" title="<?= is_cliente() ? 'Mi cuenta' : 'Ingresar' ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </a>
                <button class="navbar__toggle" id="navToggle" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </nav>

    <main class="gracias-page">
        <div class="container">
            <div class="gracias-card">
                <div class="gracias-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#16A34A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </div>

                <h1 class="gracias-title">¡Gracias por tu compra!</h1>

                <?php if ($pedido): ?>
                <p class="gracias-order-id">Pedido <strong>#<?= $pedido_id ?></strong></p>

                    <?php if ($metodo === 'transferencia'): ?>
                    <div class="gracias-transfer">
                        <p class="gracias-transfer__intro">Realiza la transferencia por el monto total:</p>
                        <span class="gracias-transfer__amount"><?= price($pedido['total']) ?></span>

                        <p style="margin:20px 0 0;font-size:.95rem;color:var(--text-muted);line-height:1.6;">
                            Te enviamos por email los datos de la cuenta (Banco, Titular, CBU y Alias) para que realices la transferencia. Si no lo recibís en unos minutos, revisá la carpeta de spam o escribinos por WhatsApp.
                        </p>

                        <a href="https://wa.me/5491162743425?text=<?= urlencode('Hola! Realice la transferencia del pedido #' . $pedido_id . ' por ' . price($pedido['total'])) ?>" target="_blank" class="btn btn--primary btn--lg btn--full" style="margin-top:24px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.611.611l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.37 0-4.567-.68-6.434-1.852l-.448-.29-2.648.888.888-2.648-.29-.448A9.96 9.96 0 012 12C2 6.486 6.486 2 12 2s10 4.486 10 10-4.486 10-10 10z"/></svg>
                            ENVIAR COMPROBANTE POR WHATSAPP
                        </a>
                    </div>
                    <?php else: ?>
                    <p class="gracias-subtitle">Tu pago fue procesado correctamente. Te enviaremos un email con los detalles.</p>
                    <?php endif; ?>

                    <!-- Order items -->
                    <div class="gracias-summary">
                        <h3>Detalle del pedido</h3>
                        <?php foreach ($items as $item): ?>
                        <div class="gracias-item">
                            <div class="gracias-item__left">
                                <div class="gracias-item__img">
                                    <img src="<?= img_url($item['imagen_principal'] ?? '') ?>" alt="">
                                </div>
                                <div>
                                    <span class="gracias-item__name"><?= sanitize($item['nombre_producto']) ?></span>
                                    <?php if ($item['variante']): ?><span class="gracias-item__variant"><?= sanitize($item['variante']) ?></span><?php endif; ?>
                                    <span class="gracias-item__qty">x<?= $item['cantidad'] ?></span>
                                </div>
                            </div>
                            <span class="gracias-item__price"><?= price($item['precio_unitario'] * $item['cantidad']) ?></span>
                        </div>
                        <?php endforeach; ?>

                        <?php if ($pedido['descuento'] > 0): ?>
                        <div class="gracias-row gracias-row--discount">
                            <span>Descuento</span><span>-<?= price($pedido['descuento']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="gracias-row gracias-row--total">
                            <span>Total</span><span><?= price($pedido['total']) ?></span>
                        </div>
                    </div>

                    <div class="gracias-shipping">
                        <h3>Envio a</h3>
                        <p><?= sanitize($pedido['nombre']) ?></p>
                        <p><?= sanitize($pedido['direccion']) ?>, <?= sanitize($pedido['ciudad']) ?></p>
                        <p><?= sanitize($pedido['provincia']) ?></p>
                    </div>

                <?php else: ?>
                <p class="gracias-subtitle">Tu pedido fue registrado. Te contactaremos pronto.</p>
                <?php endif; ?>

                <div class="gracias-actions">
                    <a href="tienda" class="btn btn--primary btn--lg"><?= t("back_to_shop") ?></a>
                    <a href="/" class="btn btn--outline btn--lg">VOLVER AL INICIO</a>
                </div>
            </div>
        </div>
    </main>

    <div class="contact__bottom" style="padding:20px 24px;margin-top:40px;">
        <p><?= t("copyright") ?></p>
    </div>

    <script src="js/main.js?v=7"></script>
</body>
</html>
