<?php if (!defined('SITE_NAME')) require_once __DIR__ . '/../config.php'; ?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php if (isset($seo_title) && isset($seo_desc)): ?>
<?= seo_tags($seo_title, $seo_desc, $seo_image ?? '', $seo_type ?? 'website') ?>
<?php else: ?>
<title><?= isset($page_title) ? sanitize($page_title) . ' — ' : '' ?><?= SITE_NAME ?></title>
<?php endif; ?>
<meta name="csrf-token" content="<?= csrf_token() ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/styles.css?v=51">
<script>
(function(){var t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',t);})();
window.SITE_URL = '<?= SITE_URL ?>';
</script>
<?php if (isset($extra_css)): ?><link rel="stylesheet" href="<?= $extra_css ?>"><?php endif; ?>
<style>
/* ── Estilos compartidos para páginas internas (mi-cuenta, pedidos, wishlist, carrito, buscar) ── */
body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); margin: 0; }

.page-wrap {
    max-width: 1200px;
    margin: 0 auto;
    padding: 120px 24px 80px;
}
.page-wrap--narrow { max-width: 880px; }

.page-header {
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--border);
}
.page-eyebrow {
    font-family: 'Inter', sans-serif;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--accent);
    margin: 0 0 6px;
}
.page-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.8rem, 3.5vw, 2.4rem);
    font-weight: 600;
    letter-spacing: -0.02em;
    margin: 0 0 6px;
    line-height: 1.15;
}
.page-title em { font-style: italic; color: var(--accent); }
.page-subtitle {
    font-size: 0.95rem;
    color: var(--text-muted);
    margin: 0;
}

/* Account layout (sidebar + content) */
.account-layout {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 32px;
    padding-top: 120px;
    padding-bottom: 80px;
}
@media (max-width: 768px) {
    .account-layout { grid-template-columns: 1fr; gap: 20px; padding-top: 100px; }
}
.account-sidebar {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px;
    height: fit-content;
    position: sticky;
    top: 100px;
}
.account-sidebar h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0 0 14px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--border);
}
.account-sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.9rem;
    border-radius: 8px;
    transition: all 0.2s;
    margin-bottom: 2px;
}
.account-sidebar a svg { flex-shrink: 0; }
.account-sidebar a:hover { background: var(--bg); color: var(--text); }
.account-sidebar a.active { background: var(--accent); color: #fff; font-weight: 500; }
.account-sidebar .logout-link {
    margin-top: 12px;
    padding-top: 14px;
    border-top: 1px solid var(--border);
    color: #b91c1c;
}
.account-sidebar .logout-link:hover { background: rgba(239,68,68,0.06); color: #b91c1c; }

.account-content { min-width: 0; }
.account-section {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 20px;
}
.account-section h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0 0 18px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.profile-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 18px;
}
.profile-info-item .label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-muted);
    margin-bottom: 4px;
}
.profile-info-item .value {
    font-size: 0.98rem;
    color: var(--text);
}

/* Forms */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
@media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
.form-group { margin-bottom: 14px; }
.form-group label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 6px;
}
.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 14px;
    font-family: 'Inter', sans-serif;
    font-size: 0.95rem;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text);
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(var(--accent-rgb, 200,140,80), 0.12);
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 28px;
    background: var(--accent);
    color: #fff;
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 0.85rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    border: none;
    border-radius: 100px;
    text-decoration: none;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(0,0,0,0.1); }
.btn-secondary {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    padding: 12px 24px; background: transparent; color: var(--text);
    border: 1px solid var(--border); border-radius: 100px;
    font-size: 0.85rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase;
    text-decoration: none; cursor: pointer; transition: border-color 0.2s, background 0.2s;
}
.btn-secondary:hover { border-color: var(--text); background: var(--bg); }

/* Flash messages */
.flash-msg {
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 0.88rem;
    margin-bottom: 18px;
    line-height: 1.5;
}
.flash-error { background: rgba(239,68,68,0.08); color: #b91c1c; border: 1px solid rgba(239,68,68,0.18); }
.flash-success { background: rgba(16,185,129,0.08); color: #047857; border: 1px solid rgba(16,185,129,0.18); }

/* Cards/lista de pedidos y wishlist */
.card-list { display: flex; flex-direction: column; gap: 14px; }
.card-item {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 18px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    transition: border-color 0.2s, transform 0.2s;
}
.card-item:hover { border-color: var(--accent); transform: translateY(-1px); }
.card-item-info { min-width: 0; }
.card-item-meta { font-size: 0.78rem; color: var(--text-muted); margin-bottom: 4px; }
.card-item-title { font-weight: 600; font-size: 0.98rem; margin-bottom: 4px; }
.card-item-detail { font-size: 0.85rem; color: var(--text-muted); }

/* Estado badges */
.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.badge-pendiente { background: rgba(245,158,11,0.12); color: #b45309; }
.badge-pagado    { background: rgba(6,182,212,0.12);  color: #0e7490; }
.badge-preparando{ background: rgba(139,92,246,0.12); color: #6d28d9; }
.badge-enviado   { background: rgba(59,130,246,0.12); color: #1d4ed8; }
.badge-entregado { background: rgba(16,185,129,0.12); color: #047857; }
.badge-cancelado { background: rgba(239,68,68,0.12);  color: #b91c1c; }

/* Empty state */
.empty-state {
    text-align: center;
    padding: 60px 24px;
    background: var(--bg-card);
    border: 1px dashed var(--border);
    border-radius: 16px;
}
.empty-state svg { width: 56px; height: 56px; color: var(--text-muted); opacity: 0.5; margin-bottom: 12px; }
.empty-state h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
    font-weight: 600;
    margin: 0 0 6px;
}
.empty-state p { color: var(--text-muted); margin: 0 0 18px; }

/* Wishlist grid */
.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 18px;
}
.wishlist-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    transition: transform 0.2s, border-color 0.2s;
}
.wishlist-card:hover { transform: translateY(-2px); border-color: var(--accent); }
.wishlist-card img { width: 100%; aspect-ratio: 1; object-fit: cover; }
.wishlist-card-body { padding: 14px; }
.wishlist-card-title { font-weight: 500; font-size: 0.95rem; margin: 0 0 4px; color: var(--text); text-decoration: none; display: block; }
.wishlist-card-price { color: var(--accent); font-weight: 600; }

/* Footer minimalista */
.site-footer {
    margin-top: 80px;
    padding: 40px 24px 24px;
    border-top: 1px solid var(--border);
    background: var(--bg);
}
.footer-grid {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 28px;
}
.footer-col h4 {
    font-family: 'Inter', sans-serif;
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text);
    margin: 0 0 12px;
}
.footer-col ul { list-style: none; padding: 0; margin: 0; }
.footer-col ul li,
.footer-col ul li a {
    font-size: 0.88rem;
    color: var(--text-muted);
    text-decoration: none;
    line-height: 2;
    transition: color 0.2s;
}
.footer-col ul li a:hover { color: var(--accent); }
.footer-logo {
    font-family: 'Playfair Display', serif;
    font-size: 1.4rem;
    font-weight: 600;
    color: var(--text);
    text-decoration: none;
    display: inline-block;
    margin-bottom: 10px;
}
.footer-desc { font-size: 0.85rem; color: var(--text-muted); line-height: 1.6; margin: 0; }
.footer-social { display: flex; gap: 10px; margin-top: 14px; }
.footer-social a {
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    color: var(--text-muted);
    border: 1px solid var(--border);
    border-radius: 50%;
    transition: all 0.2s;
}
.footer-social a:hover { color: var(--accent); border-color: var(--accent); }
.footer-bottom {
    max-width: 1200px;
    margin: 32px auto 0;
    padding-top: 20px;
    border-top: 1px solid var(--border);
    text-align: center;
    font-size: 0.82rem;
    color: var(--text-muted);
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
    <div class="container navbar__inner">
        <a href="<?= SITE_URL ?>/" class="navbar__logo"><?= SITE_NAME ?><span class="logo-dot"></span></a>
        <ul class="navbar__links" id="navLinks">
            <li><a href="<?= SITE_URL ?>/">Inicio</a></li>
            <li><a href="<?= url_pagina('tienda') ?>">Tienda</a></li>
            <li><a href="<?= SITE_URL ?>/#nosotros">Nosotros</a></li>
            <li><a href="<?= SITE_URL ?>/#contacto">Contacto</a></li>
        </ul>
        <div class="navbar__actions">
            <a href="<?= url_pagina('tienda') ?>" class="btn btn--primary btn--sm">TIENDA</a>
            <button class="theme-toggle" id="themeToggle" aria-label="Cambiar tema">
                <svg class="theme-toggle__sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                <svg class="theme-toggle__moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
            </button>
            <a href="<?= is_cliente() ? url_pagina('mi-cuenta') : url_pagina('login') ?>" class="cart-btn" aria-label="<?= is_cliente() ? 'Mi cuenta' : 'Ingresar' ?>" title="<?= is_cliente() ? 'Mi cuenta' : 'Ingresar' ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </a>
            <button class="cart-btn" id="cartBtn" aria-label="Carrito">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                <?php $cc = cart_count(); ?>
                <span class="cart-badge" id="cartBadge"<?= $cc > 0 ? '' : ' style="display:none;"' ?>><?= $cc ?></span>
            </button>
            <button class="navbar__toggle" id="navToggle" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>
