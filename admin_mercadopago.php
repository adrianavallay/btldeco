<?php
// ============================================================
// ADMIN — MERCADOPAGO (credenciales + test de conexión)
// ============================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';
require_admin();

$db = pdo();

// Garantizar la tabla de configuración (idempotente, igual que en admin_redes.php)
$db->exec("CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    grupo VARCHAR(50) DEFAULT 'general',
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$mp_result      = null;  // resultado de guardar
$mp_test_result = null;  // resultado del test de conexión

// ── POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    // ── GUARDAR CREDENCIALES ──
    if ($action === 'save_mercadopago') {
        $mp_token  = trim($_POST['mp_access_token'] ?? '');
        $mp_public = trim($_POST['mp_public_key'] ?? '');
        $mp_secret = trim($_POST['mp_webhook_secret'] ?? '');
        try {
            set_config('mp_access_token',   $mp_token,  'mercadopago');
            set_config('mp_public_key',     $mp_public, 'mercadopago');
            set_config('mp_webhook_secret', $mp_secret, 'mercadopago');
            $mp_result = ['ok' => true, 'msg' => 'Credenciales guardadas. Ya quedan activas.'];
        } catch (Throwable $e) {
            $mp_result = ['ok' => false, 'msg' => 'No se pudieron guardar: ' . $e->getMessage()];
        }
    }

    // ── PROBAR CONEXIÓN CON LA API ──
    if ($action === 'test_mercadopago') {
        // Se prueba el Access Token guardado (get_config con respaldo en .env)
        $token = get_config('mp_access_token', env('MP_ACCESS_TOKEN', ''));

        if ($token === '' || $token === 'MP_ACCESS_TOKEN_AQUI') {
            $mp_test_result = ['ok' => false, 'msg' => 'No hay Access Token cargado. Guardá las credenciales primero.'];
        } elseif (!function_exists('curl_init')) {
            $mp_test_result = ['ok' => false, 'msg' => 'El servidor no tiene cURL disponible para conectarse.'];
        } else {
            // GET /users/me: si el token es válido, devuelve los datos de la cuenta
            $ch = curl_init('https://api.mercadopago.com/users/me');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
                CURLOPT_TIMEOUT        => 15,
            ]);
            $resp = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err  = curl_error($ch);
            curl_close($ch);

            if ($err !== '') {
                $mp_test_result = ['ok' => false, 'msg' => 'No se pudo conectar con MercadoPago: ' . $err];
            } elseif ($code >= 200 && $code < 300) {
                $data  = json_decode($resp, true) ?: [];
                $nick  = $data['nickname'] ?? trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
                $email = $data['email'] ?? '';
                $site  = $data['site_id'] ?? '';
                $detalle = trim(($nick !== '' ? $nick : 'cuenta verificada')
                        . ($email !== '' ? " ($email)" : '')
                        . ($site !== '' ? " — $site" : ''));
                $mp_test_result = ['ok' => true, 'msg' => 'Conexión exitosa. Cuenta: ' . $detalle];
            } elseif ($code === 401) {
                $mp_test_result = ['ok' => false, 'msg' => 'El Access Token es inválido o expiró (HTTP 401). Verificá que sea el de Producción.'];
            } else {
                $mp_test_result = ['ok' => false, 'msg' => "MercadoPago respondió con un error (HTTP $code)."];
            }
        }
    }
}

// ── Valores actuales (base con respaldo en .env) ──
$mp_access_token_val   = get_config('mp_access_token',   env('MP_ACCESS_TOKEN', ''));
$mp_public_key_val     = get_config('mp_public_key',     env('MP_PUBLIC_KEY', ''));
$mp_webhook_secret_val = get_config('mp_webhook_secret', env('MP_WEBHOOK_SECRET', ''));
$mp_configurado        = ($mp_access_token_val !== '' && $mp_public_key_val !== '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MercadoPago — Admin</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">
<link rel="stylesheet" href="css/admin.css?v=30">
</head>
<body>
<?php $admin_page = 'mercadopago'; ?>

<?php include __DIR__ . '/includes/admin_header.php'; ?>

    <!-- Page header -->
    <div class="page-header">
        <h1>MercadoPago</h1>
    </div>

    <!-- ── Credenciales ── -->
    <div class="config-section">
        <h3>Credenciales</h3>
        <p style="color:#71717a;font-size:.88rem;margin-bottom:12px">
            Cargá las credenciales de tu cuenta para poder cobrar. Las obtenés en tu
            <a href="https://www.mercadopago.com.ar/developers/panel/app" target="_blank" rel="noopener">panel de desarrollador de MercadoPago</a>
            (usá las credenciales de <strong>Producción</strong>).
        </p>
        <p style="font-size:.85rem;margin-bottom:16px;">
            Estado:
            <?php if ($mp_configurado): ?>
                <span style="color:#166534;font-weight:700;">&#10003; Configurado</span>
            <?php else: ?>
                <span style="color:#991b1b;font-weight:700;">&#10007; Falta configurar</span>
            <?php endif; ?>
        </p>
        <form method="POST" style="max-width:560px">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="save_mercadopago">

            <div class="form-group">
                <label for="mp_public_key">Public Key</label>
                <input type="text" id="mp_public_key" name="mp_public_key"
                       value="<?= sanitize($mp_public_key_val) ?>" placeholder="APP_USR-..." autocomplete="off">
            </div>

            <div class="form-group">
                <label for="mp_access_token">Access Token</label>
                <input type="password" id="mp_access_token" name="mp_access_token"
                       value="<?= sanitize($mp_access_token_val) ?>" placeholder="APP_USR-..." autocomplete="off">
                <label style="font-size:.78rem;color:#666;display:inline-flex;align-items:center;gap:5px;margin-top:5px;cursor:pointer;">
                    <input type="checkbox" onclick="toggleMpField('mp_access_token', this.checked)"> Mostrar
                </label>
            </div>

            <div class="form-group">
                <label for="mp_webhook_secret">Clave secreta del webhook <span style="color:#999;font-weight:400;">(opcional)</span></label>
                <input type="password" id="mp_webhook_secret" name="mp_webhook_secret"
                       value="<?= sanitize($mp_webhook_secret_val) ?>" placeholder="Para validar las notificaciones de pago" autocomplete="off">
                <label style="font-size:.78rem;color:#666;display:inline-flex;align-items:center;gap:5px;margin-top:5px;cursor:pointer;">
                    <input type="checkbox" onclick="toggleMpField('mp_webhook_secret', this.checked)"> Mostrar
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar credenciales</button>
            </div>
        </form>
        <?php if ($mp_result): ?>
            <div class="result-box <?= $mp_result['ok'] ? 'ok' : 'err' ?>">
                <?= sanitize($mp_result['msg']) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── Test de conexión ── -->
    <div class="config-section">
        <h3>Probar conexión con la API</h3>
        <p style="color:#71717a;font-size:.88rem;margin-bottom:12px">
            Hace una consulta real a MercadoPago con el Access Token guardado para verificar que las credenciales sean válidas.
        </p>
        <form method="POST">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="test_mercadopago">
            <button type="submit" class="btn btn-outline">Probar conexión</button>
        </form>
        <?php if ($mp_test_result): ?>
            <div class="result-box <?= $mp_test_result['ok'] ? 'ok' : 'err' ?>">
                <?= sanitize($mp_test_result['msg']) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="admin-footer">
        <p>&copy; <?= date('Y') ?> DyP Consultora &mdash; Panel de gestión</p>
    </footer>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>

<script src="js/admin.js"></script>
<script>
function toggleMpField(id, show){
    var f = document.getElementById(id);
    if(f) f.type = show ? 'text' : 'password';
}
</script>

</body>
</html>
