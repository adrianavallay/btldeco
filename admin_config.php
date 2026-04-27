<?php
// ============================================================
// ADMIN — CONFIGURACIÓN
// ============================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';
require_admin();

$db = pdo();
$flash_ok  = '';
$flash_err = '';

$test_db_result = null;
$test_email_result = null;
$password_result = null;
// ── POST ACTIONS ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    // ── TEST DB CONNECTION ──
    if ($action === 'test_db') {
        try {
            $db->query("SELECT 1");
            $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $test_db_result = [
                'ok' => true,
                'msg' => 'Conexion exitosa. ' . count($tables) . ' tabla(s) encontrada(s): ' . implode(', ', $tables)
            ];
        } catch (Exception $e) {
            $test_db_result = ['ok' => false, 'msg' => 'Error: ' . $e->getMessage()];
        }
    }

    // ── TEST EMAIL ──
    if ($action === 'test_email') {
        $to = NOTIFY_EMAIL;
        $subject = SITE_NAME . ' - Email de prueba';
        $body = "Este es un email de prueba enviado desde el panel de administracion.\n\nFecha: " . date('d/m/Y H:i:s');
        $headers = "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        if (@mail($to, $subject, $body, $headers)) {
            $test_email_result = ['ok' => true, 'msg' => "Email de prueba enviado a $to"];
        } else {
            $test_email_result = ['ok' => false, 'msg' => "No se pudo enviar el email. Verificar configuracion SMTP del servidor."];
        }
    }

    // ── CHANGE PASSWORD ──
    if ($action === 'change_password') {
        $current  = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (ADMIN_PASS === '' || !password_verify($current, ADMIN_PASS)) {
            $password_result = ['ok' => false, 'msg' => 'La contraseña actual es incorrecta.'];
        } elseif ($new_pass !== $confirm) {
            $password_result = ['ok' => false, 'msg' => 'Las contraseñas no coinciden.'];
        } else {
            $pwCheck = validate_password($new_pass);
            if (!$pwCheck['ok']) {
                $password_result = ['ok' => false, 'msg' => $pwCheck['mensaje']];
            } else {
                $hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $password_result = [
                    'ok'   => true,
                    'msg'  => "Hash generado. Copialo y reemplazá la línea ADMIN_PASS_HASH en el archivo .env del servidor (vía File Manager de Hostinger).",
                    'hash' => $hash,
                ];
            }
        }
    }
}

// ── System info ──
$php_version = phpversion();
$upload_max = ini_get('upload_max_filesize');
$post_max = ini_get('post_max_size');
$memory_limit = ini_get('memory_limit');
$session_status_text = session_status() === PHP_SESSION_ACTIVE ? 'Activa' : 'Inactiva';
$session_save_path = session_save_path() ?: 'Default del sistema';
$server_software = $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido';
$doc_root = $_SERVER['DOCUMENT_ROOT'] ?? __DIR__;
$pdo_version = $db->getAttribute(PDO::ATTR_SERVER_VERSION) ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Configuración — Admin</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">
<link rel="stylesheet" href="css/admin.css?v=30">
</head>
<body>
<?php $admin_page = 'config'; ?>

<?php include __DIR__ . '/includes/admin_header.php'; ?>

    <!-- Page header -->
    <div class="page-header">
        <h1>Configuracion</h1>
    </div>

    <!-- ── Config Info Cards ── -->
    <div class="config-grid">
        <div class="config-card">
            <h3>Sitio</h3>
            <div class="config-item">
                <span class="config-label">Nombre</span>
                <span class="config-value"><?= sanitize(SITE_NAME) ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">URL</span>
                <span class="config-value"><?= sanitize(SITE_URL) ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">Email notif.</span>
                <span class="config-value"><?= sanitize(NOTIFY_EMAIL) ?></span>
            </div>
        </div>

        <div class="config-card">
            <h3>Base de datos</h3>
            <div class="config-item">
                <span class="config-label">Host</span>
                <span class="config-value"><?= sanitize(DB_HOST) ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">Nombre DB</span>
                <span class="config-value"><?= sanitize(DB_NAME) ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">MySQL version</span>
                <span class="config-value"><?= sanitize($pdo_version) ?></span>
            </div>
        </div>

        <div class="config-card">
            <h3>PHP & Servidor</h3>
            <div class="config-item">
                <span class="config-label">PHP version</span>
                <span class="config-value"><?= sanitize($php_version) ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">Servidor</span>
                <span class="config-value"><?= sanitize($server_software) ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">Memory limit</span>
                <span class="config-value"><?= sanitize($memory_limit) ?></span>
            </div>
        </div>

        <div class="config-card">
            <h3>Uploads & Sesion</h3>
            <div class="config-item">
                <span class="config-label">Upload max</span>
                <span class="config-value"><?= sanitize($upload_max) ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">Post max</span>
                <span class="config-value"><?= sanitize($post_max) ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">Sesion</span>
                <span class="config-value"><?= sanitize($session_status_text) ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">Items por pagina</span>
                <span class="config-value"><?= ITEMS_PER_PAGE ?></span>
            </div>
        </div>
    </div>

    <!-- ── Test DB Connection ── -->
    <div class="config-section">
        <h3>Test conexion a base de datos</h3>
        <p style="color:#71717a;font-size:.88rem;margin-bottom:12px">Verifica que la conexion a MySQL funcione correctamente y lista las tablas disponibles.</p>
        <form method="POST">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="test_db">
            <button type="submit" class="btn btn-outline">Probar conexion DB</button>
        </form>
        <?php if ($test_db_result): ?>
            <div class="result-box <?= $test_db_result['ok'] ? 'ok' : 'err' ?>">
                <?= sanitize($test_db_result['msg']) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── Test Email ── -->
    <div class="config-section">
        <h3>Test envio de email</h3>
        <p style="color:#71717a;font-size:.88rem;margin-bottom:12px">Envia un email de prueba a <strong><?= sanitize(NOTIFY_EMAIL) ?></strong> usando la funcion mail() de PHP.</p>
        <form method="POST">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="test_email">
            <button type="submit" class="btn btn-outline">Enviar email de prueba</button>
        </form>
        <?php if ($test_email_result): ?>
            <div class="result-box <?= $test_email_result['ok'] ? 'ok' : 'err' ?>">
                <?= sanitize($test_email_result['msg']) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── Change Password ── -->
    <div class="config-section">
        <h3>Cambiar contrasena de administrador</h3>
        <p style="color:#71717a;font-size:.88rem;margin-bottom:16px">La nueva contrasena se guarda en <code style="background:#1e1e2e;padding:2px 6px;border-radius:4px">config_override.php</code>. Luego debes actualizar <code style="background:#1e1e2e;padding:2px 6px;border-radius:4px">config.php</code> manualmente.</p>
        <form method="POST" style="max-width:400px">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="change_password">
            <div class="form-group">
                <label for="current_password">Contrasena actual</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">Nueva contrasena</label>
                <input type="password" id="new_password" name="new_password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmar nueva contrasena</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Cambiar contrasena</button>
            </div>
        </form>
        <?php if ($password_result): ?>
            <div class="result-box <?= $password_result['ok'] ? 'ok' : 'err' ?>">
                <?= sanitize($password_result['msg']) ?>
                <?php if (!empty($password_result['hash'])): ?>
                    <div style="margin-top:12px;">
                        <strong>Nuevo ADMIN_PASS_HASH:</strong>
                        <pre style="background:#f5f5f5;border:1px solid #ddd;padding:10px;margin-top:6px;font-family:monospace;font-size:0.78rem;overflow-x:auto;user-select:all;"><?= sanitize($password_result['hash']) ?></pre>
                        <p style="font-size:0.78rem;color:#666;margin:6px 0 0;">Copialo y reemplazá la línea <code>ADMIN_PASS_HASH=...</code> del archivo <code>.env</code> en producción. La contraseña vieja seguirá funcionando hasta que actualices el .env.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── Maintenance Notes ── -->
    <div class="notes">
        <h3>Notas de mantenimiento</h3>
        <ul>
            <li>Realizar backups regulares de la base de datos (<?= sanitize(DB_NAME) ?>)</li>
            <li>Mantener PHP actualizado (actual: <?= sanitize($php_version) ?>)</li>
            <li>Revisar permisos de la carpeta uploads/ periodicamente</li>
            <li>El directorio de uploads es: <code style="background:#1e1e2e;padding:2px 6px;border-radius:4px"><?= sanitize(UPLOAD_DIR) ?></code></li>
            <li>Stock minimo de alerta configurado en: <?= STOCK_MINIMO_ALERTA ?> unidades</li>
            <li>MercadoPago: verificar que las credenciales esten configuradas antes de activar pagos</li>
            <li>Cambiar ADMIN_PASS_PLAIN en config.php luego del primer login</li>
        </ul>
    </div>

    <!-- Footer -->
    <footer class="admin-footer">
        <p>&copy; <?= date('Y') ?> DyP Consultora &mdash; Panel de gestión</p>
    </footer>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>

<script src="js/admin.js"></script>

</body>
</html>
