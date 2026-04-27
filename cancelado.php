<?php
require_once __DIR__ . '/config.php';

$pedido_id = (int) ($_GET['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago cancelado — BTLDECO</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css?v=51">
    <style>
        .status-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 24px 80px;
            background:
                radial-gradient(1200px 600px at 20% 0%, rgba(239,68,68,0.06), transparent 60%),
                radial-gradient(900px 500px at 80% 100%, rgba(var(--accent-rgb, 200,140,80), 0.05), transparent 60%),
                var(--bg);
        }
        .status-card {
            max-width: 560px;
            width: 100%;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 56px 48px 44px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.03);
            animation: cardIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @media (max-width: 600px) {
            .status-card { padding: 40px 28px 32px; border-radius: 20px; }
        }

        @keyframes cardIn {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .status-icon {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.10);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 28px;
            position: relative;
            animation: iconPop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s both;
        }
        .status-icon::after {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 2px solid rgba(239, 68, 68, 0.15);
            animation: ring 1.8s ease-out infinite;
        }
        @keyframes ring {
            0%   { transform: scale(0.95); opacity: 0.8; }
            100% { transform: scale(1.25); opacity: 0; }
        }
        .status-icon svg {
            width: 40px;
            height: 40px;
            stroke: #ef4444;
            stroke-width: 2.5;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
            animation: drawX 0.5s ease-out 0.4s both;
        }
        @keyframes iconPop {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes drawX {
            0% { stroke-dasharray: 60; stroke-dashoffset: 60; }
            100% { stroke-dasharray: 60; stroke-dashoffset: 0; }
        }

        .status-eyebrow {
            display: inline-block;
            font-family: 'Inter', sans-serif;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #ef4444;
            background: rgba(239, 68, 68, 0.08);
            padding: 6px 14px;
            border-radius: 100px;
            margin-bottom: 18px;
        }
        .status-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 4vw, 2.4rem);
            font-weight: 600;
            letter-spacing: -0.02em;
            color: var(--text);
            margin: 0 0 14px;
            line-height: 1.15;
        }
        .status-title em {
            font-style: italic;
            color: var(--accent);
        }
        .status-subtitle {
            font-family: 'Inter', sans-serif;
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.65;
            margin: 0 auto 6px;
            max-width: 440px;
        }
        .status-detail {
            font-family: 'Inter', sans-serif;
            color: var(--text-muted);
            font-size: 0.88rem;
            line-height: 1.65;
            margin: 0 0 36px;
            opacity: 0.85;
        }
        .ref-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            padding: 6px 14px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 100px;
            font-size: 0.82rem;
            color: var(--text);
            font-weight: 500;
        }
        .ref-pill strong {
            font-weight: 600;
            color: var(--accent);
        }

        .status-actions {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
            max-width: 320px;
            margin: 0 auto;
        }
        .status-actions .btn-retry {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 32px;
            background: var(--accent);
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 0.88rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border: none;
            border-radius: 100px;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        .status-actions .btn-retry:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            background: var(--accent-hover, var(--accent));
        }
        .status-actions .btn-retry svg {
            width: 16px; height: 16px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .status-actions .link-back {
            color: var(--text-muted);
            font-family: 'Inter', sans-serif;
            font-size: 0.88rem;
            text-decoration: none;
            font-weight: 500;
            padding: 10px;
            transition: color 0.2s;
        }
        .status-actions .link-back:hover {
            color: var(--text);
        }

        .help-box {
            margin-top: 36px;
            padding-top: 28px;
            border-top: 1px solid var(--border);
            font-family: 'Inter', sans-serif;
            font-size: 0.82rem;
            color: var(--text-muted);
            line-height: 1.6;
        }
        .help-box a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }
        .help-box a:hover { text-decoration: underline; }

        /* Minimal navbar — sólo logo */
        .status-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            padding: 24px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }
        .status-nav__logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text);
            text-decoration: none;
            letter-spacing: -0.01em;
        }
        .status-nav__logo span {
            color: var(--accent);
        }
        .status-nav__home {
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .status-nav__home:hover { color: var(--text); }
    </style>
</head>
<body>

<nav class="status-nav">
    <a href="<?= SITE_URL ?>/" class="status-nav__logo">BTLDECO<span>.</span></a>
    <a href="<?= SITE_URL ?>/" class="status-nav__home">← Volver al inicio</a>
</nav>

<main class="status-page">
    <div class="status-card">
        <div class="status-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <line x1="6" y1="6" x2="18" y2="18"/>
                <line x1="18" y1="6" x2="6" y2="18"/>
            </svg>
        </div>

        <span class="status-eyebrow">Transacción interrumpida</span>

        <h1 class="status-title">Pago <em>cancelado</em></h1>
        <p class="status-subtitle">Tu pedido no fue procesado y no se realizó ningún cargo a tu cuenta.</p>
        <p class="status-detail">
            Podés intentar nuevamente o seguir explorando la tienda.
            <?php if ($pedido_id > 0): ?>
                <br><span class="ref-pill">Referencia: <strong>#<?= $pedido_id ?></strong></span>
            <?php endif; ?>
        </p>

        <div class="status-actions">
            <a href="checkout" class="btn-retry">
                Reintentar pago
                <svg viewBox="0 0 24 24"><path d="M3 12h18M13 5l7 7-7 7"/></svg>
            </a>
            <a href="tienda" class="link-back">Seguir comprando</a>
        </div>

        <div class="help-box">
            ¿Tuviste algún problema? Escribinos a <a href="mailto:<?= NOTIFY_EMAIL ?>"><?= NOTIFY_EMAIL ?></a> y te ayudamos.
        </div>
    </div>
</main>

</body>
</html>
