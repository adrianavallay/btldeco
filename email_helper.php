<?php
require_once __DIR__ . '/config.php';

/**
 * BTLDECO — Email Templates
 * Design: warm cream bg, gold accent, Playfair+Inter style
 */

function email_template(string $title, string $body, string $footer_extra = ''): string {
    $site = SITE_NAME;
    $url = SITE_URL;
    $year = date('Y');

    return '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#F3EDE8;font-family:Arial,Helvetica,sans-serif;-webkit-font-smoothing:antialiased;">

<!-- Wrapper -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F3EDE8;padding:40px 20px;">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.06);">

    <!-- Header -->
    <tr>
        <td style="background-color:#0F0D0B;padding:36px 40px;text-align:center;">
            <h1 style="margin:0;font-family:Georgia,serif;font-size:28px;font-weight:700;color:#FFFFFF;letter-spacing:1px;">' . $site . '<span style="color:#D97706;">.</span></h1>
        </td>
    </tr>

    <!-- Gold accent line -->
    <tr>
        <td style="background:linear-gradient(90deg,#D97706,#F59E0B,#D97706);height:3px;font-size:0;line-height:0;">&nbsp;</td>
    </tr>

    <!-- Title -->
    <tr>
        <td style="padding:36px 40px 0;">
            <h2 style="margin:0;font-family:Georgia,serif;font-size:22px;font-weight:700;color:#0F172A;line-height:1.3;">' . $title . '</h2>
        </td>
    </tr>

    <!-- Body content -->
    <tr>
        <td style="padding:20px 40px 36px;">
            ' . $body . '
        </td>
    </tr>

    ' . $footer_extra . '

    <!-- Footer -->
    <tr>
        <td style="padding:24px 40px;border-top:1px solid #EAE3DC;text-align:center;">
            <p style="margin:0 0 8px;font-size:13px;color:#9A9189;">
                <a href="' . $url . '" style="color:#D97706;text-decoration:none;font-weight:600;">' . $site . '</a> &mdash; Decoracion de diseño
            </p>
            <p style="margin:0;font-size:12px;color:#9A9189;">&copy; ' . $year . ' ' . $site . '. Todos los derechos reservados.</p>
        </td>
    </tr>

</table>
</td></tr>
</table>
</body>
</html>';
}

function send_email(string $to, string $subject, string $htmlBody): bool {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SITE_NAME . " <noreply@btldeco.com.ar>\r\n";
    return @mail($to, $subject, $htmlBody, $headers);
}

// ──────────────────────────────────────────────────────────
// EMAIL AL CLIENTE: Confirmacion de pedido
// ──────────────────────────────────────────────────────────
function email_pedido_confirmacion(array $pedido, array $items): void {
    $metodo = $pedido['notas'] ?? '';
    $es_transferencia = ($metodo === 'transferencia' || strpos($metodo, 'transferencia') !== false);

    // Items table
    $rows = '';
    foreach ($items as $item) {
        $nombre = sanitize($item['nombre_producto'] ?? $item['nombre'] ?? '');
        $cant = (int)($item['cantidad'] ?? $item['qty'] ?? 1);
        $precio = (float)($item['precio_unitario'] ?? $item['precio'] ?? 0);
        $rows .= '
        <tr>
            <td style="padding:12px 0;border-bottom:1px solid #EAE3DC;font-size:14px;color:#0F172A;">' . $nombre . '</td>
            <td style="padding:12px 0;border-bottom:1px solid #EAE3DC;font-size:14px;color:#5C554D;text-align:center;">' . $cant . '</td>
            <td style="padding:12px 0;border-bottom:1px solid #EAE3DC;font-size:14px;color:#0F172A;text-align:right;font-weight:600;">' . price($precio * $cant) . '</td>
        </tr>';
    }

    // Payment status message
    if ($es_transferencia) {
        $pago_msg = '
        <div style="background:#FFF7ED;border:1px solid #FED7AA;border-radius:12px;padding:24px;margin:24px 0;">
            <p style="margin:0 0 8px;font-size:15px;font-weight:700;color:#D97706;">Pago pendiente — Transferencia bancaria</p>
            <p style="margin:0 0 16px;font-size:14px;color:#5C554D;line-height:1.6;">Tu pedido fue registrado. Para que podamos procesarlo, realiza la transferencia con los siguientes datos:</p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
                <tr><td style="padding:8px 0;font-size:13px;color:#9A9189;border-bottom:1px solid #FED7AA;">Banco</td><td style="padding:8px 0;font-size:14px;color:#0F172A;font-weight:600;text-align:right;border-bottom:1px solid #FED7AA;">Banco Galicia</td></tr>
                <tr><td style="padding:8px 0;font-size:13px;color:#9A9189;border-bottom:1px solid #FED7AA;">Titular</td><td style="padding:8px 0;font-size:14px;color:#0F172A;font-weight:600;text-align:right;border-bottom:1px solid #FED7AA;">BTLDECO SRL</td></tr>
                <tr><td style="padding:8px 0;font-size:13px;color:#9A9189;border-bottom:1px solid #FED7AA;">CBU</td><td style="padding:8px 0;font-size:14px;color:#0F172A;font-weight:600;text-align:right;border-bottom:1px solid #FED7AA;">0070000000000000000</td></tr>
                <tr><td style="padding:8px 0;font-size:13px;color:#9A9189;">Alias</td><td style="padding:8px 0;font-size:14px;color:#0F172A;font-weight:600;text-align:right;">BTLDECO.PAGOS</td></tr>
            </table>
            <p style="margin:0 0 16px;font-size:13px;color:#B45309;line-height:1.5;font-style:italic;">El pedido no sera despachado hasta que el pago se acredite en nuestra cuenta.</p>
            <table role="presentation" cellpadding="0" cellspacing="0"><tr><td>
                <a href="https://wa.me/5491162743425?text=' . urlencode('Hola! Realice la transferencia del pedido #' . $pedido['id'] . ' por ' . price($pedido['total'])) . '" style="display:inline-block;padding:14px 32px;background-color:#D97706;color:#FFFFFF;font-size:14px;font-weight:700;text-decoration:none;border-radius:50px;letter-spacing:0.5px;">ENVIAR COMPROBANTE DE PAGO</a>
            </td></tr></table>
        </div>';
    } else {
        $pago_msg = '
        <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:24px;margin:24px 0;">
            <p style="margin:0 0 8px;font-size:15px;font-weight:700;color:#16A34A;">Pago con MercadoPago</p>
            <p style="margin:0;font-size:14px;color:#5C554D;line-height:1.6;">Si el pago ya fue aprobado, tu pedido sera procesado automaticamente. Si quedo pendiente de acreditacion, te avisaremos por email cuando se confirme. El pedido no se despacha hasta la confirmacion del pago.</p>
        </div>';
    }

    $body = '
    <p style="font-size:15px;color:#5C554D;line-height:1.7;margin:0 0 8px;">Hola <strong style="color:#0F172A;">' . sanitize($pedido['nombre']) . '</strong>,</p>
    <p style="font-size:15px;color:#5C554D;line-height:1.7;margin:0 0 24px;">Recibimos tu pedido <strong style="color:#D97706;">#' . $pedido['id'] . '</strong>. Gracias por elegirnos.</p>

    ' . $pago_msg . '

    <!-- Items table -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;">
        <thead>
            <tr>
                <th style="padding:10px 0;border-bottom:2px solid #D97706;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#9A9189;text-align:left;">Producto</th>
                <th style="padding:10px 0;border-bottom:2px solid #D97706;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#9A9189;text-align:center;">Cant.</th>
                <th style="padding:10px 0;border-bottom:2px solid #D97706;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#9A9189;text-align:right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>' . $rows . '</tbody>
    </table>

    <!-- Totals -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:6px 0;font-size:14px;color:#9A9189;">Subtotal</td>
            <td style="padding:6px 0;font-size:14px;color:#0F172A;text-align:right;">' . price($pedido['subtotal']) . '</td>
        </tr>
        ' . ($pedido['descuento'] > 0 ? '<tr>
            <td style="padding:6px 0;font-size:14px;color:#16A34A;">Descuento</td>
            <td style="padding:6px 0;font-size:14px;color:#16A34A;text-align:right;">-' . price($pedido['descuento']) . '</td>
        </tr>' : '') . '
        <tr>
            <td style="padding:12px 0;border-top:2px solid #0F172A;font-size:18px;font-weight:700;color:#0F172A;">Total</td>
            <td style="padding:12px 0;border-top:2px solid #0F172A;font-size:18px;font-weight:700;color:#D97706;text-align:right;">' . price($pedido['total']) . '</td>
        </tr>
    </table>

    <p style="font-size:13px;color:#9A9189;margin:24px 0 0;line-height:1.5;">Si tenes alguna consulta, respondenos a este email o escribinos por <a href="https://wa.me/5491162743425" style="color:#D97706;text-decoration:none;font-weight:600;">WhatsApp</a>.</p>';

    $html = email_template('Recibimos tu pedido #' . $pedido['id'], $body);
    send_email($pedido['email'], 'Pedido #' . $pedido['id'] . ' — ' . SITE_NAME, $html);
}

// ──────────────────────────────────────────────────────────
// EMAIL AL CLIENTE: Cambio de estado
// ──────────────────────────────────────────────────────────
function email_pedido_estado(array $pedido): void {
    $estados = [
        'pendiente'   => ['Pendiente de pago', '#D97706', '#FFF7ED', '#FED7AA'],
        'pagado'      => ['Pago confirmado', '#16A34A', '#F0FDF4', '#BBF7D0'],
        'preparando'  => ['En preparacion', '#8B5CF6', '#F5F3FF', '#DDD6FE'],
        'enviado'     => ['Tu pedido fue enviado', '#3B82F6', '#EFF6FF', '#BFDBFE'],
        'entregado'   => ['Pedido entregado', '#16A34A', '#F0FDF4', '#BBF7D0'],
        'cancelado'   => ['Pedido cancelado', '#DC2626', '#FEF2F2', '#FECACA'],
        'reembolsado' => ['Pedido reembolsado', '#6B7280', '#F9FAFB', '#E5E7EB'],
    ];

    $info = $estados[$pedido['estado']] ?? ['Estado actualizado', '#6B7280', '#F9FAFB', '#E5E7EB'];

    $body = '
    <p style="font-size:15px;color:#5C554D;line-height:1.7;margin:0 0 8px;">Hola <strong style="color:#0F172A;">' . sanitize($pedido['nombre']) . '</strong>,</p>
    <p style="font-size:15px;color:#5C554D;line-height:1.7;margin:0 0 24px;">Te informamos sobre el estado de tu pedido <strong style="color:#D97706;">#' . $pedido['id'] . '</strong>:</p>

    <div style="background:' . $info[2] . ';border:1px solid ' . $info[3] . ';border-radius:12px;padding:28px;text-align:center;margin:24px 0;">
        <p style="margin:0;font-size:22px;font-weight:700;color:' . $info[0] . ';font-family:Georgia,serif;color:' . $info[1] . ';">' . $info[0] . '</p>
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0;">
        <tr>
            <td style="padding:8px 0;font-size:14px;color:#9A9189;">Pedido</td>
            <td style="padding:8px 0;font-size:14px;color:#0F172A;text-align:right;font-weight:600;">#' . $pedido['id'] . '</td>
        </tr>
        <tr>
            <td style="padding:8px 0;font-size:14px;color:#9A9189;">Total</td>
            <td style="padding:8px 0;font-size:14px;color:#D97706;text-align:right;font-weight:700;">' . price($pedido['total']) . '</td>
        </tr>
    </table>

    <p style="font-size:13px;color:#9A9189;margin:24px 0 0;line-height:1.5;">Si tenes alguna consulta, escribinos por <a href="https://wa.me/5491162743425" style="color:#D97706;text-decoration:none;font-weight:600;">WhatsApp</a>.</p>';

    $html = email_template('Pedido #' . $pedido['id'] . ' — ' . $info[0], $body);
    send_email($pedido['email'], 'Pedido #' . $pedido['id'] . ' — ' . $info[0] . ' | ' . SITE_NAME, $html);
}

// ──────────────────────────────────────────────────────────
// EMAIL AL ADMIN: Nuevo pedido
// ──────────────────────────────────────────────────────────
function email_admin_nuevo_pedido(array $pedido): void {
    $metodo = $pedido['notas'] ?? '';
    $es_transferencia = ($metodo === 'transferencia' || strpos($metodo, 'transferencia') !== false);
    $metodo_label = $es_transferencia ? 'Transferencia bancaria' : 'MercadoPago';

    $body = '
    <p style="font-size:15px;color:#5C554D;line-height:1.7;margin:0 0 24px;">Nuevo pedido recibido en la tienda:</p>

    <div style="background:#FFF7ED;border:1px solid #FED7AA;border-radius:12px;padding:24px;margin:0 0 24px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="padding:8px 0;font-size:13px;color:#9A9189;border-bottom:1px solid #FED7AA;">Pedido</td>
                <td style="padding:8px 0;font-size:15px;color:#0F172A;font-weight:700;text-align:right;border-bottom:1px solid #FED7AA;">#' . $pedido['id'] . '</td>
            </tr>
            <tr>
                <td style="padding:8px 0;font-size:13px;color:#9A9189;border-bottom:1px solid #FED7AA;">Cliente</td>
                <td style="padding:8px 0;font-size:14px;color:#0F172A;font-weight:600;text-align:right;border-bottom:1px solid #FED7AA;">' . sanitize($pedido['nombre']) . '</td>
            </tr>
            <tr>
                <td style="padding:8px 0;font-size:13px;color:#9A9189;border-bottom:1px solid #FED7AA;">Email</td>
                <td style="padding:8px 0;font-size:14px;color:#0F172A;text-align:right;border-bottom:1px solid #FED7AA;">' . sanitize($pedido['email']) . '</td>
            </tr>
            <tr>
                <td style="padding:8px 0;font-size:13px;color:#9A9189;border-bottom:1px solid #FED7AA;">Metodo de pago</td>
                <td style="padding:8px 0;font-size:14px;color:#D97706;font-weight:600;text-align:right;border-bottom:1px solid #FED7AA;">' . $metodo_label . '</td>
            </tr>
            <tr>
                <td style="padding:8px 0;font-size:13px;color:#9A9189;">Total</td>
                <td style="padding:8px 0;font-size:20px;color:#D97706;font-weight:700;text-align:right;">' . price($pedido['total']) . '</td>
            </tr>
        </table>
    </div>

    <table role="presentation" cellpadding="0" cellspacing="0"><tr><td>
        <a href="' . SITE_URL . '/admin_pedidos.php" style="display:inline-block;padding:14px 32px;background-color:#D97706;color:#FFFFFF;font-size:14px;font-weight:700;text-decoration:none;border-radius:50px;letter-spacing:0.5px;">VER EN EL ADMIN</a>
    </td></tr></table>';

    $html = email_template('Nuevo Pedido #' . $pedido['id'] . ' — ' . price($pedido['total']), $body);
    send_email(NOTIFY_EMAIL, 'Nuevo Pedido #' . $pedido['id'] . ' (' . price($pedido['total']) . ') — ' . SITE_NAME, $html);
}

// ──────────────────────────────────────────────────────────
// EMAIL AL ADMIN: Stock bajo
// ──────────────────────────────────────────────────────────
function email_stock_bajo(array $producto): void {
    $body = '
    <p style="font-size:15px;color:#5C554D;line-height:1.7;margin:0 0 16px;">El siguiente producto tiene stock bajo:</p>
    <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:12px;padding:24px;text-align:center;margin:0 0 24px;">
        <p style="margin:0 0 8px;font-size:16px;font-weight:700;color:#0F172A;">' . sanitize($producto['nombre']) . '</p>
        <p style="margin:0;font-size:32px;font-weight:700;color:#DC2626;">' . $producto['stock'] . ' unidades</p>
    </div>
    <table role="presentation" cellpadding="0" cellspacing="0"><tr><td>
        <a href="' . SITE_URL . '/admin_productos.php" style="display:inline-block;padding:14px 32px;background-color:#D97706;color:#FFFFFF;font-size:14px;font-weight:700;text-decoration:none;border-radius:50px;">VER PRODUCTOS</a>
    </td></tr></table>';

    $html = email_template('Stock Bajo — ' . sanitize($producto['nombre']), $body);
    send_email(NOTIFY_EMAIL, 'Stock bajo: ' . $producto['nombre'] . ' — ' . SITE_NAME, $html);
}
