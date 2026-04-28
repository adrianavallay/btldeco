<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';

require_cliente();

$db = pdo();
$cliente = cliente_data();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $nombre   = trim($_POST['nombre'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if (!$nombre || !$email) {
            flash('error', 'Nombre y email son obligatorios');
        } else {
            // Check if email is taken by another user
            $stmt = $db->prepare("SELECT id FROM clientes WHERE email = ? AND id != ?");
            $stmt->execute([$email, cliente_id()]);
            if ($stmt->fetch()) {
                flash('error', 'Ese email ya está en uso por otra cuenta');
            } else {
                $db->prepare("UPDATE clientes SET nombre = ?, email = ?, telefono = ? WHERE id = ?")
                   ->execute([$nombre, $email, $telefono, cliente_id()]);
                $_SESSION['cliente_nombre'] = $nombre;
                $_SESSION['cliente_email']  = $email;
                flash('success', 'Perfil actualizado correctamente');
            }
        }
        redirect(url_pagina('mi-cuenta'));
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!$current || !$new || !$confirm) {
            flash('error', 'Completá todos los campos de contraseña');
        } elseif ($new !== $confirm) {
            flash('error', 'Las contraseñas nuevas no coinciden');
        } else {
            $pwCheck = validate_password($new);
            if (!$pwCheck['ok']) {
                flash('error', $pwCheck['mensaje']);
            } else {
                $res = cliente_change_password(cliente_id(), $current, $new);
                if ($res['ok']) {
                    flash('success', $res['mensaje']);
                } else {
                    flash('error', $res['mensaje']);
                }
            }
        }
        redirect(url_pagina('mi-cuenta'));
    }
}

// Get recent orders (last 5)
$stmt = $db->prepare("SELECT id, fecha, total, estado FROM pedidos WHERE cliente_id = ? ORDER BY fecha DESC LIMIT 5");
$stmt->execute([cliente_id()]);
$pedidos_recientes = $stmt->fetchAll();

$flash_error   = flash('error');
$flash_success = flash('success');
$item_count    = cart_count();

// Reload client data after possible update
$cliente = cliente_data();

$page_title = 'Mi cuenta';
include __DIR__ . '/includes/header.php';
?>

<!-- ═══════════ ACCOUNT ═══════════ -->
<div class="container account-layout">

    <!-- SIDEBAR -->
    <aside class="account-sidebar">
        <h3>Mi cuenta</h3>
        <a href="<?= url_pagina('mi-cuenta') ?>" class="active">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Mi cuenta
        </a>
        <a href="<?= url_pagina('mis-pedidos') ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            Mis pedidos
        </a>
        <a href="<?= url_pagina('wishlist') ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            Wishlist
        </a>
        <a href="<?= SITE_URL ?>/logout" class="logout-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Cerrar sesión
        </a>
    </aside>

    <!-- CONTENT -->
    <div class="account-content">

        <?php if ($flash_error): ?>
            <div class="flash-msg flash-error"><?= sanitize($flash_error) ?></div>
        <?php endif; ?>

        <?php if ($flash_success): ?>
            <div class="flash-msg flash-success"><?= sanitize($flash_success) ?></div>
        <?php endif; ?>

        <h1 style="font-size:1.5rem;margin-bottom:24px;">
            Hola, <?= sanitize($cliente['nombre'] ?? 'Cliente') ?>
        </h1>

        <!-- Profile Info Card -->
        <div class="account-section">
            <h2>Información personal</h2>
            <div class="profile-info">
                <div class="profile-info-item">
                    <div class="label">Nombre</div>
                    <div class="value"><?= sanitize($cliente['nombre'] ?? '-') ?></div>
                </div>
                <div class="profile-info-item">
                    <div class="label">Email</div>
                    <div class="value"><?= sanitize($cliente['email'] ?? '-') ?></div>
                </div>
                <div class="profile-info-item">
                    <div class="label">Teléfono</div>
                    <div class="value"><?= sanitize($cliente['telefono'] ?: 'No especificado') ?></div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="account-section">
            <h2>Editar perfil</h2>
            <form action="<?= url_pagina('mi-cuenta') ?>" method="POST">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="update_profile">

                <div class="form-row">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" value="<?= sanitize($cliente['nombre'] ?? '') ?>" required>
                </div>

                <div class="form-grid-2">
                    <div class="form-row">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= sanitize($cliente['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-row">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" value="<?= sanitize($cliente['telefono'] ?? '') ?>" placeholder="+54 11 1234-5678">
                    </div>
                </div>

                <button type="submit" class="btn-save">Guardar cambios</button>
            </form>
        </div>

        <!-- Change Password Form -->
        <div class="account-section">
            <h2>Cambiar contraseña</h2>
            <form action="<?= url_pagina('mi-cuenta') ?>" method="POST">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="change_password">

                <div class="form-row">
                    <label for="current_password">Contraseña actual</label>
                    <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                </div>

                <div class="form-grid-2">
                    <div class="form-row">
                        <label for="new_password">Nueva contraseña</label>
                        <input type="password" id="new_password" name="new_password" required autocomplete="new-password" placeholder="Mínimo 6 caracteres">
                    </div>
                    <div class="form-row">
                        <label for="confirm_password">Confirmar nueva contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="btn-save">Cambiar contraseña</button>
            </form>
        </div>

        <!-- Recent Orders -->
        <div class="account-section">
            <h2>Pedidos recientes</h2>

            <?php if (empty($pedidos_recientes)): ?>
                <p style="color:var(--text-muted);font-size:.9rem;padding:20px 0;">No tenés pedidos todavía.</p>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos_recientes as $p): ?>
                            <tr>
                                <td style="font-weight:600;">#<?= (int)$p['id'] ?></td>
                                <td><?= date('d/m/Y', strtotime($p['fecha'])) ?></td>
                                <td style="font-weight:600;"><?= price((float)$p['total']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= sanitize($p['estado']) ?>">
                                        <?= sanitize(ucfirst($p['estado'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= url_pagina('mis-pedidos') ?>?id=<?= (int)$p['id'] ?>" style="color:var(--accent-light);font-size:.82rem;text-decoration:none;font-weight:600;">
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:16px;">
                    <a href="<?= url_pagina('mis-pedidos') ?>" style="font-size:.85rem;color:var(--accent-light);text-decoration:none;font-weight:600;">
                        Ver todos los pedidos &rarr;
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
