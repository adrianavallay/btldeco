<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/email_helper.php';

// ── Always respond 200 (MercadoPago requirement) ──────────
// We buffer and send 200 early, then process
http_response_code(200);
header('Content-Type: application/json');

// ── Read raw body once (needed for signature validation + JSON parse) ──
$raw_body = file_get_contents('php://input');

// ── Read notification data ────────────────────────────────
$topic = $_GET['topic'] ?? $_GET['type'] ?? '';
$mp_id = $_GET['id'] ?? $_GET['data_id'] ?? '';

// MP may also send JSON body
if (empty($topic) || empty($mp_id)) {
    $json = json_decode($raw_body, true);
    if ($json) {
        $topic = $json['topic'] ?? $json['type'] ?? $topic;
        $mp_id = $json['data']['id'] ?? $json['id'] ?? $mp_id;

        // For v2 notifications, type is "payment" and data.id is the payment ID
        if (isset($json['action']) && str_contains($json['action'], 'payment')) {
            $topic = 'payment';
        }
    }
}

// ── Verificar firma HMAC X-Signature ──────────────────────
// MP envía: X-Signature: ts=<timestamp>,v1=<hmac>
// Manifiesto:  id:<data.id>;request-id:<x-request-id>;ts:<ts>;
// HMAC-SHA256(manifiesto, MP_WEBHOOK_SECRET) debe matchear v1
function mp_signature_valid(string $secret, string $data_id): bool {
    if ($secret === '') return false; // sin secret no podemos validar

    $sig_header = $_SERVER['HTTP_X_SIGNATURE'] ?? '';
    $req_id     = $_SERVER['HTTP_X_REQUEST_ID'] ?? '';
    if ($sig_header === '' || $req_id === '' || $data_id === '') return false;

    // Parsear "ts=...,v1=..."
    $parts = [];
    foreach (explode(',', $sig_header) as $kv) {
        $pair = array_pad(explode('=', trim($kv), 2), 2, '');
        $parts[trim($pair[0])] = trim($pair[1]);
    }
    $ts = $parts['ts'] ?? '';
    $v1 = $parts['v1'] ?? '';
    if ($ts === '' || $v1 === '') return false;

    $manifest = "id:{$data_id};request-id:{$req_id};ts:{$ts};";
    $expected = hash_hmac('sha256', $manifest, $secret);
    return hash_equals($expected, $v1);
}

if (defined('MP_WEBHOOK_SECRET') && MP_WEBHOOK_SECRET !== '') {
    // Modo enforce: rechaza si la firma no valida
    if (!mp_signature_valid(MP_WEBHOOK_SECRET, (string) $mp_id)) {
        error_log("MP webhook: firma X-Signature inválida o ausente. IP=" . ($_SERVER['REMOTE_ADDR'] ?? '?') . " body=" . substr($raw_body, 0, 200));
        http_response_code(401);
        echo json_encode(['status' => 'invalid_signature']);
        exit;
    }
} else {
    // Modo legacy: secret no configurado todavía. Loguea warning pero acepta.
    error_log("MP webhook: MP_WEBHOOK_SECRET no configurado — saltando validación de firma");
}

// ── Only process payment notifications ────────────────────
if ($topic !== 'payment' || empty($mp_id)) {
    echo json_encode(['status' => 'ignored']);
    exit;
}

// ── Fetch payment info from MercadoPago API ───────────────
$ch = curl_init("https://api.mercadopago.com/v1/payments/{$mp_id}");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . MP_ACCESS_TOKEN,
    ],
    CURLOPT_TIMEOUT        => 30,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error || $http_code !== 200) {
    error_log("MP webhook: error fetching payment {$mp_id} (HTTP {$http_code}): {$curl_error}");
    echo json_encode(['status' => 'error']);
    exit;
}

$payment = json_decode($response, true);
if (!$payment) {
    error_log("MP webhook: invalid JSON for payment {$mp_id}");
    echo json_encode(['status' => 'error']);
    exit;
}

$payment_status    = $payment['status'] ?? '';
$external_ref      = $payment['external_reference'] ?? '';
$mp_payment_id     = $payment['id'] ?? $mp_id;

if (empty($external_ref)) {
    error_log("MP webhook: no external_reference for payment {$mp_id}");
    echo json_encode(['status' => 'no_reference']);
    exit;
}

// ── Process approved payments ─────────────────────────────
if ($payment_status === 'approved') {
    try {
        $db = pdo();

        // Find pedido by external_reference (pedido ID)
        $stmt = $db->prepare("SELECT * FROM pedidos WHERE id = ? AND estado = 'pendiente'");
        $stmt->execute([(int) $external_ref]);
        $pedido = $stmt->fetch();

        if (!$pedido) {
            // Already processed or doesn't exist
            error_log("MP webhook: pedido {$external_ref} not found or not pendiente");
            echo json_encode(['status' => 'not_found']);
            exit;
        }

        $db->beginTransaction();

        // Update pedido to pagado
        $db->prepare("UPDATE pedidos SET estado = 'pagado', mp_payment_id = ? WHERE id = ?")
           ->execute([(string) $mp_payment_id, $pedido['id']]);

        // Get pedido items
        $stmtItems = $db->prepare("SELECT * FROM pedido_items WHERE pedido_id = ?");
        $stmtItems->execute([$pedido['id']]);
        $items = $stmtItems->fetchAll();

        // Decrease stock for each item
        $stmtStock = $db->prepare("UPDATE productos SET stock = GREATEST(stock - ?, 0), total_ventas = total_ventas + ? WHERE id = ?");
        foreach ($items as $item) {
            $stmtStock->execute([
                $item['cantidad'],
                $item['cantidad'],
                $item['producto_id'],
            ]);
        }

        $db->commit();

        // Check for low stock and send alerts
        foreach ($items as $item) {
            $stmtProd = $db->prepare("SELECT id, nombre, stock, stock_minimo FROM productos WHERE id = ?");
            $stmtProd->execute([$item['producto_id']]);
            $producto = $stmtProd->fetch();

            if ($producto && $producto['stock'] <= $producto['stock_minimo']) {
                try {
                    email_stock_bajo($producto);
                } catch (Exception $e) {
                    error_log("MP webhook: error sending stock alert for product {$producto['id']}: " . $e->getMessage());
                }
            }
        }

        // Send status update email to client
        $pedido['estado'] = 'pagado';
        try {
            email_pedido_estado($pedido);
        } catch (Exception $e) {
            error_log("MP webhook: error sending status email for pedido {$pedido['id']}: " . $e->getMessage());
        }

        error_log("MP webhook: pedido {$pedido['id']} marked as pagado (payment {$mp_payment_id})");

    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        error_log("MP webhook: DB error for pedido {$external_ref}: " . $e->getMessage());
    }
}

echo json_encode(['status' => 'ok']);
exit;
