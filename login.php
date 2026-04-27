<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';

// Already logged in? Go to account
if (is_cliente()) {
    redirect('mi-cuenta.php');
}

// Detect which tab to show after a POST error
$active_tab = 'login';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            flash('error', 'Completá todos los campos');
        } else {
            $res = cliente_login($email, $password);
            if ($res['ok']) {
                flash('success', $res['mensaje']);
                redirect('mi-cuenta.php');
            } else {
                flash('error', $res['mensaje']);
            }
        }
    }

    if ($action === 'register') {
        $active_tab = 'register';
        $nombre   = trim($_POST['nombre'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if (!$nombre || !$email || !$password) {
            flash('error', 'Completá todos los campos obligatorios');
        } elseif ($password !== $confirm) {
            flash('error', 'Las contraseñas no coinciden');
        } else {
            $pwCheck = validate_password($password);
            if (!$pwCheck['ok']) {
                flash('error', $pwCheck['mensaje']);
            } else {
                $res = cliente_register($nombre, $email, $password, $telefono);
                if ($res['ok']) {
                    flash('success', $res['mensaje']);
                    redirect('mi-cuenta.php');
                } else {
                    flash('error', $res['mensaje']);
                }
            }
        }
    }
}

$flash_error   = flash('error');
$flash_success = flash('success');
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar — BTLDECO</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css?v=51">
    <style>
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 24px 80px;
            background:
                radial-gradient(1100px 600px at 80% 0%, rgba(var(--accent-rgb, 200,140,80), 0.07), transparent 60%),
                radial-gradient(800px 500px at 0% 100%, rgba(var(--accent-rgb, 200,140,80), 0.04), transparent 60%),
                var(--bg);
        }
        .auth-card {
            max-width: 480px;
            width: 100%;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 40px 40px 36px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.03);
            animation: cardIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @media (max-width: 600px) {
            .auth-card { padding: 32px 24px 28px; border-radius: 20px; }
        }
        @keyframes cardIn {
            0% { opacity: 0; transform: translateY(16px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .auth-eyebrow {
            font-family: 'Inter', sans-serif;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--accent);
            margin: 0 0 6px;
        }
        .auth-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.85rem;
            font-weight: 600;
            letter-spacing: -0.02em;
            color: var(--text);
            margin: 0 0 22px;
            line-height: 1.15;
        }
        .auth-title em { font-style: italic; color: var(--accent); }

        /* Tabs */
        .auth-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 100px;
            padding: 4px;
            margin-bottom: 24px;
            position: relative;
        }
        .auth-tab {
            background: transparent;
            border: none;
            padding: 11px 16px;
            font-family: 'Inter', sans-serif;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--text-muted);
            cursor: pointer;
            border-radius: 100px;
            transition: color 0.25s ease;
            position: relative;
            z-index: 1;
        }
        .auth-tab.active { color: #fff; }
        .auth-tabs::before {
            content: '';
            position: absolute;
            top: 4px;
            left: 4px;
            width: calc(50% - 4px);
            height: calc(100% - 8px);
            background: var(--accent);
            border-radius: 100px;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 0;
        }
        .auth-tabs[data-active="register"]::before {
            transform: translateX(100%);
        }

        /* Forms */
        .auth-form { display: none; }
        .auth-form.active { display: block; animation: formIn 0.3s ease; }
        @keyframes formIn {
            0% { opacity: 0; transform: translateY(6px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .auth-field {
            margin-bottom: 14px;
        }
        .auth-field label {
            display: block;
            font-family: 'Inter', sans-serif;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
        .auth-field input {
            width: 100%;
            padding: 13px 16px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            box-sizing: border-box;
        }
        .auth-field input::placeholder {
            color: var(--text-muted);
            opacity: 0.6;
        }
        .auth-field input:focus {
            outline: none;
            border-color: var(--accent);
            background: var(--bg-card);
            box-shadow: 0 0 0 4px rgba(var(--accent-rgb, 200,140,80), 0.12);
        }

        .auth-submit {
            width: 100%;
            padding: 15px 24px;
            margin-top: 8px;
            background: var(--accent);
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            border: none;
            border-radius: 100px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        .auth-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            background: var(--accent-hover, var(--accent));
        }
        .auth-submit:active { transform: translateY(0); }

        .auth-link {
            text-align: center;
            margin: 18px 0 0;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .auth-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.2s;
        }
        .auth-link a:hover { opacity: 0.75; text-decoration: underline; }

        /* Flash messages */
        .flash-msg {
            padding: 12px 16px;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            margin-bottom: 18px;
            line-height: 1.5;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .flash-msg svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }
        .flash-error {
            background: rgba(239, 68, 68, 0.08);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, 0.18);
        }
        .flash-success {
            background: rgba(16, 185, 129, 0.08);
            color: #047857;
            border: 1px solid rgba(16, 185, 129, 0.18);
        }

        /* Minimal navbar */
        .auth-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            padding: 24px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }
        .auth-nav__logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text);
            text-decoration: none;
            letter-spacing: -0.01em;
        }
        .auth-nav__logo span { color: var(--accent); }
        .auth-nav__home {
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .auth-nav__home:hover { color: var(--text); }
    </style>
</head>
<body>

<nav class="auth-nav">
    <a href="<?= SITE_URL ?>/" class="auth-nav__logo">BTLDECO<span>.</span></a>
    <a href="<?= SITE_URL ?>/" class="auth-nav__home">← Volver al inicio</a>
</nav>

<main class="auth-page">
    <div class="auth-card">
        <p class="auth-eyebrow">Tu cuenta</p>
        <h1 class="auth-title">Bienvenido a <em>BTLDECO</em></h1>

        <?php if ($flash_error): ?>
            <div class="flash-msg flash-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span><?= sanitize($flash_error) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($flash_success): ?>
            <div class="flash-msg flash-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <span><?= sanitize($flash_success) ?></span>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="auth-tabs" id="authTabs" data-active="<?= $active_tab ?>">
            <button class="auth-tab <?= $active_tab === 'login' ? 'active' : '' ?>" data-tab="login" type="button">Ingresar</button>
            <button class="auth-tab <?= $active_tab === 'register' ? 'active' : '' ?>" data-tab="register" type="button">Crear cuenta</button>
        </div>

        <!-- LOGIN FORM -->
        <form class="auth-form <?= $active_tab === 'login' ? 'active' : '' ?>" id="form-login" action="login.php" method="POST">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="login">

            <div class="auth-field">
                <label for="login-email">Email</label>
                <input type="email" id="login-email" name="email" required placeholder="tu@email.com" autocomplete="email">
            </div>

            <div class="auth-field">
                <label for="login-password">Contraseña</label>
                <input type="password" id="login-password" name="password" required placeholder="Tu contraseña" autocomplete="current-password">
            </div>

            <button type="submit" class="auth-submit">Iniciar sesión</button>

            <p class="auth-link">
                ¿Sos nuevo? <a href="#" onclick="switchTab('register');return false;">Creá tu cuenta</a>
            </p>
        </form>

        <!-- REGISTER FORM -->
        <form class="auth-form <?= $active_tab === 'register' ? 'active' : '' ?>" id="form-register" action="login.php" method="POST">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="register">

            <div class="auth-field">
                <label for="reg-nombre">Nombre completo</label>
                <input type="text" id="reg-nombre" name="nombre" required placeholder="Tu nombre completo" autocomplete="name">
            </div>

            <div class="auth-field">
                <label for="reg-email">Email</label>
                <input type="email" id="reg-email" name="email" required placeholder="tu@email.com" autocomplete="email">
            </div>

            <div class="auth-field">
                <label for="reg-telefono">Teléfono <span style="text-transform:none;font-weight:400;color:var(--text-muted);">(opcional)</span></label>
                <input type="tel" id="reg-telefono" name="telefono" placeholder="+54 11 1234-5678" autocomplete="tel">
            </div>

            <div class="auth-field">
                <label for="reg-password">Contraseña</label>
                <input type="password" id="reg-password" name="password" required minlength="10" placeholder="Mínimo 10 caracteres, con letras y números" autocomplete="new-password">
            </div>

            <div class="auth-field">
                <label for="reg-password-confirm">Confirmar contraseña</label>
                <input type="password" id="reg-password-confirm" name="password_confirm" required placeholder="Repetí tu contraseña" autocomplete="new-password">
            </div>

            <button type="submit" class="auth-submit">Crear cuenta</button>

            <p class="auth-link">
                ¿Ya tenés cuenta? <a href="#" onclick="switchTab('login');return false;">Iniciá sesión</a>
            </p>
        </form>
    </div>
</main>

<script>
function switchTab(tab) {
    var tabs = document.getElementById('authTabs');
    tabs.dataset.active = tab;
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
    document.querySelector('[data-tab="' + tab + '"]').classList.add('active');
    document.getElementById('form-' + tab).classList.add('active');
}

document.querySelectorAll('.auth-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        switchTab(this.dataset.tab);
    });
});
</script>

</body>
</html>
