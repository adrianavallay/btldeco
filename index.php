<?php
require_once __DIR__ . '/config.php';

// Fetch featured products (destacados)
try {
    $featured = pdo()->query("SELECT p.*, c.nombre AS categoria_nombre, c.slug AS categoria_slug
                              FROM productos p
                              LEFT JOIN categorias c ON p.categoria_id = c.id
                              WHERE p.estado = 'activo' AND p.destacado = 1
                              ORDER BY p.fecha_creacion DESC
                              LIMIT 6")->fetchAll();
} catch (Exception $e) {
    $featured = [];
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BTLDECO — Decoracion de Diseño</title>
    <meta name="description" content="BTLDECO — Productos de decoracion de diseño para tu hogar. Piezas unicas, macetas, figuras, portavelas y mas. Fabricacion artesanal en Argentina.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css?v=13">
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar" id="navbar">
        <div class="container navbar__inner">
            <a href="#" class="navbar__logo">BTLDECO<span class="logo-dot"></span></a>
            <ul class="navbar__links" id="navLinks">
                <li><a href="#inicio" data-i18n="nav-inicio">Inicio</a></li>
                <li><a href="#productos" data-i18n="nav-productos">Productos</a></li>
                <li><a href="#galeria" data-i18n="nav-galeria">Galeria</a></li>
                <li><a href="#nosotros" data-i18n="nav-nosotros">Nosotros</a></li>
                <li><a href="#contacto" data-i18n="nav-contacto">Contacto</a></li>
            </ul>
            <div class="navbar__actions">
                <a href="tienda.php" class="btn btn--primary btn--sm" data-i18n="nav-cta">TIENDA</a>
                <div class="lang-switch" id="langSwitch">
                    <button class="lang-switch__btn active" data-lang="es">ES</button>
                    <button class="lang-switch__btn" data-lang="en">EN</button>
                    <div class="lang-switch__indicator"></div>
                </div>
                <button class="theme-toggle" id="themeToggle" aria-label="Cambiar tema">
                    <svg class="theme-toggle__sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg class="theme-toggle__moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                </button>
                <button class="cart-btn" id="cartBtn" aria-label="Carrito">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                    <span class="cart-badge" id="cartBadge">0</span>
                </button>
                <button class="navbar__toggle" id="navToggle" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- SVG FILTERS (from paper-design shaders) -->
    <svg class="svg-filters" style="position:absolute;width:0;height:0;">
        <defs>
            <filter id="glass-effect" x="-50%" y="-50%" width="200%" height="200%">
                <feTurbulence baseFrequency="0.005" numOctaves="1" result="noise"/>
                <feDisplacementMap in="SourceGraphic" in2="noise" scale="0.3"/>
                <feColorMatrix type="matrix" values="1 0 0 0 0.02  0 1 0 0 0.02  0 0 1 0 0.05  0 0 0 0.9 0" result="tint"/>
            </filter>
            <filter id="gooey-filter" x="-50%" y="-50%" width="200%" height="200%">
                <feGaussianBlur in="SourceGraphic" stdDeviation="4" result="blur"/>
                <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="gooey"/>
                <feComposite in="SourceGraphic" in2="gooey" operator="atop"/>
            </filter>
        </defs>
    </svg>

    <!-- HERO WITH SHADER -->
    <section class="hero" id="inicio">
        <canvas class="hero__shader" id="heroShader"></canvas>
        <canvas class="hero__shader hero__shader--wireframe" id="heroShaderWire"></canvas>
        <div class="hero__overlay"></div>

        <!-- Content grid: text left + image right -->
        <div class="container hero__content">
            <div class="hero__grid">
                <div class="hero__text">
                    <div class="hero__glass-tag reveal" style="filter: url(#glass-effect);">
                        <div class="hero__glass-tag-shine"></div>
                        <span class="pulse-dot"></span>
                        <span data-i18n="hero-tag">DECORACION DE DISEÑO</span>
                    </div>
                    <h1 class="hero__title reveal">
                        <span class="hero__title-line" data-i18n="hero-t1">Piezas que</span>
                        <span class="hero__title-line hero__title-line--accent" data-i18n="hero-t2">transforman</span>
                        <span class="hero__title-line" data-i18n="hero-t3">espacios</span>
                    </h1>
                    <p class="hero__subtitle reveal" data-i18n="hero-sub">
                        Objetos decorativos unicos, fabricados con tecnologia 3D y terminaciones artesanales.
                        Macetas, figuras, portavelas y mas — diseño argentino para tu hogar.
                    </p>
                    <div class="hero__cta reveal">
                        <a href="#contacto" class="btn btn--hero-outline btn--lg" data-i18n="hero-btn1">
                            VER PRODUCTOS
                        </a>
                        <a href="#productos" class="btn btn--hero-solid btn--lg" data-i18n="hero-btn2">
                            EXPLORAR
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                        </a>
                    </div>
                </div>
                <div class="hero__accordion reveal">
                    <div class="accordion-item" data-index="0">
                        <img src="https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=500&h=600&fit=crop" alt="Maceta Geometrica">
                        <div class="accordion-item__overlay"></div>
                        <span class="accordion-item__title">Maceta Geometrica</span>
                    </div>
                    <div class="accordion-item" data-index="1">
                        <img src="https://images.unsplash.com/photo-1602028915047-37269d1a73f7?w=500&h=600&fit=crop" alt="Figura Abstracta">
                        <div class="accordion-item__overlay"></div>
                        <span class="accordion-item__title">Figura Abstracta</span>
                    </div>
                    <div class="accordion-item" data-index="2">
                        <img src="https://images.unsplash.com/photo-1603204077167-2fa0397f49de?w=500&h=600&fit=crop" alt="Portavela Moon">
                        <div class="accordion-item__overlay"></div>
                        <span class="accordion-item__title">Portavela Moon</span>
                    </div>
                    <div class="accordion-item" data-index="3">
                        <img src="https://images.unsplash.com/photo-1578500494198-246f612d3b3d?w=500&h=600&fit=crop" alt="Florero Spiral">
                        <div class="accordion-item__overlay"></div>
                        <span class="accordion-item__title">Florero Spiral</span>
                    </div>
                    <div class="accordion-item active" data-index="4">
                        <img src="https://images.unsplash.com/photo-1507473885765-e6ed057ab6fe?w=500&h=600&fit=crop" alt="Lampara Deco">
                        <div class="accordion-item__overlay"></div>
                        <span class="accordion-item__title">Lampara Deco</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pulsing circle (bottom-right like original) -->
        <div class="hero__pulsing" id="heroPulsing">
            <canvas class="hero__pulsing-canvas" id="pulsingCanvas" width="120" height="120"></canvas>
            <svg class="hero__pulsing-text" viewBox="0 0 100 100">
                <defs>
                    <path id="circlePath" d="M 50, 50 m -38, 0 a 38,38 0 1,1 76,0 a 38,38 0 1,1 -76,0"/>
                </defs>
                <text>
                    <textPath href="#circlePath" startOffset="0%">BTLDECO • DISEÑO 3D • DECORACION • BTLDECO • DISEÑO 3D • DECORACION •</textPath>
                </text>
            </svg>
        </div>

        <!-- Stats bar at very bottom -->
        <div class="hero__stats-bar reveal">
            <div class="hero__stat">
                <span class="hero__stat-value">+<span data-count="500">0</span></span>
                <span class="hero__stat-label" data-i18n="hero-s1">Piezas vendidas</span>
            </div>
            <div class="hero__stat-divider"></div>
            <div class="hero__stat">
                <span class="hero__stat-value"><span data-count="50">0</span>+</span>
                <span class="hero__stat-label" data-i18n="hero-s2">Diseños unicos</span>
            </div>
            <div class="hero__stat-divider"></div>
            <div class="hero__stat">
                <span class="hero__stat-value"><span data-count="98">0</span>%</span>
                <span class="hero__stat-label" data-i18n="hero-s3">Clientes felices</span>
            </div>
        </div>
    </section>

    <!-- MARQUEE -->
    <section class="marquee-section">
        <div class="marquee">
            <div class="marquee__track">
                <span>MACETAS</span><span class="marquee__sep">/</span>
                <span>FIGURAS</span><span class="marquee__sep">/</span>
                <span>PORTAVELAS</span><span class="marquee__sep">/</span>
                <span>ORGANIZADORES</span><span class="marquee__sep">/</span>
                <span>LETRAS DECO</span><span class="marquee__sep">/</span>
                <span>FLOREROS</span><span class="marquee__sep">/</span>
                <span>LAMPARAS</span><span class="marquee__sep">/</span>
                <span>DISEÑO 3D</span><span class="marquee__sep">/</span>
                <span>MACETAS</span><span class="marquee__sep">/</span>
                <span>FIGURAS</span><span class="marquee__sep">/</span>
                <span>PORTAVELAS</span><span class="marquee__sep">/</span>
                <span>ORGANIZADORES</span><span class="marquee__sep">/</span>
                <span>LETRAS DECO</span><span class="marquee__sep">/</span>
                <span>FLOREROS</span><span class="marquee__sep">/</span>
                <span>LAMPARAS</span><span class="marquee__sep">/</span>
                <span>DISEÑO 3D</span><span class="marquee__sep">/</span>
            </div>
        </div>
    </section>

    <!-- PRODUCTOS — CAROUSEL -->
    <section class="section products-carousel" id="productos">
        <div class="container">
            <div class="pc-header reveal">
                <div class="pc-header__text">
                    <span class="section__tag" data-i18n="prod-tag">NUESTROS PRODUCTOS</span>
                    <h2 class="section__title" data-i18n="prod-title">Diseño que decora,<br>detalle que enamora</h2>
                </div>
                <div class="pc-header__nav">
                    <button class="pc-arrow pc-arrow--prev" id="pcPrev" aria-label="Anterior">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    </button>
                    <button class="pc-arrow pc-arrow--next" id="pcNext" aria-label="Siguiente">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <?php if (!empty($featured)): ?>
        <div class="pc-viewport" id="pcViewport">
            <div class="pc-track" id="pcTrack">
                <?php foreach ($featured as $i => $fp): ?>
                <div class="pc-slide" style="--i:<?= $i ?>">
                    <div class="pc-card">
                        <a href="producto_detalle.php?slug=<?= urlencode($fp['slug']) ?>" class="pc-card__link">
                            <div class="pc-card__img">
                                <img src="<?= img_url($fp['imagen_principal']) ?>" alt="<?= sanitize($fp['nombre']) ?>" loading="lazy">
                                <span class="pc-card__cat"><?= sanitize($fp['categoria_nombre'] ?? '') ?></span>
                            </div>
                        </a>
                        <div class="pc-card__body">
                            <a href="producto_detalle.php?slug=<?= urlencode($fp['slug']) ?>" class="pc-card__name"><?= sanitize($fp['nombre']) ?></a>
                            <span class="pc-card__price"><?= price(($fp['precio_oferta'] && $fp['precio_oferta'] < $fp['precio']) ? $fp['precio_oferta'] : $fp['precio']) ?></span>
                            <button class="pc-card__cart" onclick="window.btlCart.add(<?= $fp['id'] ?>)">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Dots -->
        <div class="pc-dots" id="pcDots">
            <?php foreach ($featured as $i => $fp): ?>
            <button class="pc-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>" aria-label="Producto <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
        </div>

        <!-- CTA -->
        <div class="container" style="text-align:center;margin-top:40px;">
            <a href="tienda.php" class="btn btn--primary btn--lg reveal">
                VER TODA LA TIENDA
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
        <?php else: ?>
        <div class="container" style="text-align:center;padding:60px 24px;color:var(--text-muted);">
            Proximamente productos destacados
        </div>
        <?php endif; ?>
    </section>

    <!-- GALERIA DUAL CAROUSEL -->
    <section class="gallery" id="galeria">
        <div class="container">
            <div class="section__header reveal" style="text-align:center;">
                <span class="section__tag" data-i18n="gal-tag">NUESTROS TRABAJOS</span>
                <h2 class="section__title" data-i18n="gal-title">Galeria de<br>productos</h2>
            </div>
        </div>
        <!-- Fila 1: scroll izquierda -->
        <div class="gallery__track-wrapper">
            <div class="gallery__track gallery__track--left" id="galleryTrack1">
                <div class="gallery__item" data-product="Maceta Hexa">
                    <img src="https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=600&h=450&fit=crop" alt="Maceta Hexa" loading="lazy">
                    <div class="gallery__item-overlay"><span>Maceta Hexa</span></div>
                </div>
                <div class="gallery__item" data-product="Figura Ondas">
                    <img src="https://images.unsplash.com/photo-1602028915047-37269d1a73f7?w=600&h=450&fit=crop" alt="Figura Ondas" loading="lazy">
                    <div class="gallery__item-overlay"><span>Figura Ondas</span></div>
                </div>
                <div class="gallery__item" data-product="Portavela Moon">
                    <img src="https://images.unsplash.com/photo-1603204077167-2fa0397f49de?w=600&h=450&fit=crop" alt="Portavela Moon" loading="lazy">
                    <div class="gallery__item-overlay"><span>Portavela Moon</span></div>
                </div>
                <div class="gallery__item" data-product="Organizador Cubic">
                    <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=600&h=450&fit=crop" alt="Organizador Cubic" loading="lazy">
                    <div class="gallery__item-overlay"><span>Organizador Cubic</span></div>
                </div>
                <div class="gallery__item" data-product="Florero Spiral">
                    <img src="https://images.unsplash.com/photo-1578500494198-246f612d3b3d?w=600&h=450&fit=crop" alt="Florero Spiral" loading="lazy">
                    <div class="gallery__item-overlay"><span>Florero Spiral</span></div>
                </div>
                <div class="gallery__item" data-product="Lampara Geo">
                    <img src="https://images.unsplash.com/photo-1507473885765-e6ed057ab6fe?w=600&h=450&fit=crop" alt="Lampara Geo" loading="lazy">
                    <div class="gallery__item-overlay"><span>Lampara Geo</span></div>
                </div>
                <div class="gallery__item" data-product="Bandeja Cloud">
                    <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=600&h=450&fit=crop" alt="Bandeja Cloud" loading="lazy">
                    <div class="gallery__item-overlay"><span>Bandeja Cloud</span></div>
                </div>
                <div class="gallery__item" data-product="Maceta Drop">
                    <img src="https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?w=600&h=450&fit=crop" alt="Maceta Drop" loading="lazy">
                    <div class="gallery__item-overlay"><span>Maceta Drop</span></div>
                </div>
                <!-- Duplicados para loop infinito -->
                <div class="gallery__item" data-product="Maceta Hexa">
                    <img src="https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=600&h=450&fit=crop" alt="Maceta Hexa" loading="lazy">
                    <div class="gallery__item-overlay"><span>Maceta Hexa</span></div>
                </div>
                <div class="gallery__item" data-product="Figura Ondas">
                    <img src="https://images.unsplash.com/photo-1602028915047-37269d1a73f7?w=600&h=450&fit=crop" alt="Figura Ondas" loading="lazy">
                    <div class="gallery__item-overlay"><span>Figura Ondas</span></div>
                </div>
                <div class="gallery__item" data-product="Portavela Moon">
                    <img src="https://images.unsplash.com/photo-1603204077167-2fa0397f49de?w=600&h=450&fit=crop" alt="Portavela Moon" loading="lazy">
                    <div class="gallery__item-overlay"><span>Portavela Moon</span></div>
                </div>
                <div class="gallery__item" data-product="Organizador Cubic">
                    <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=600&h=450&fit=crop" alt="Organizador Cubic" loading="lazy">
                    <div class="gallery__item-overlay"><span>Organizador Cubic</span></div>
                </div>
                <div class="gallery__item" data-product="Florero Spiral">
                    <img src="https://images.unsplash.com/photo-1578500494198-246f612d3b3d?w=600&h=450&fit=crop" alt="Florero Spiral" loading="lazy">
                    <div class="gallery__item-overlay"><span>Florero Spiral</span></div>
                </div>
                <div class="gallery__item" data-product="Lampara Geo">
                    <img src="https://images.unsplash.com/photo-1507473885765-e6ed057ab6fe?w=600&h=450&fit=crop" alt="Lampara Geo" loading="lazy">
                    <div class="gallery__item-overlay"><span>Lampara Geo</span></div>
                </div>
                <div class="gallery__item" data-product="Bandeja Cloud">
                    <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=600&h=450&fit=crop" alt="Bandeja Cloud" loading="lazy">
                    <div class="gallery__item-overlay"><span>Bandeja Cloud</span></div>
                </div>
                <div class="gallery__item" data-product="Maceta Drop">
                    <img src="https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?w=600&h=450&fit=crop" alt="Maceta Drop" loading="lazy">
                    <div class="gallery__item-overlay"><span>Maceta Drop</span></div>
                </div>
            </div>
        </div>
        <!-- Fila 2: scroll derecha -->
        <div class="gallery__track-wrapper">
            <div class="gallery__track gallery__track--right" id="galleryTrack2">
                <div class="gallery__item" data-product="Letra Deco A">
                    <img src="https://images.unsplash.com/photo-1513519245088-0e12902e35ca?w=600&h=450&fit=crop" alt="Letra Deco A" loading="lazy">
                    <div class="gallery__item-overlay"><span>Letra Deco A</span></div>
                </div>
                <div class="gallery__item" data-product="Maceta Bauhaus">
                    <img src="https://images.unsplash.com/photo-1501004318855-e73a3fc04086?w=600&h=450&fit=crop" alt="Maceta Bauhaus" loading="lazy">
                    <div class="gallery__item-overlay"><span>Maceta Bauhaus</span></div>
                </div>
                <div class="gallery__item" data-product="Escultura Twist">
                    <img src="https://images.unsplash.com/photo-1544967082-d9d25d867d66?w=600&h=450&fit=crop" alt="Escultura Twist" loading="lazy">
                    <div class="gallery__item-overlay"><span>Escultura Twist</span></div>
                </div>
                <div class="gallery__item" data-product="Difusor Minimal">
                    <img src="https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=600&h=450&fit=crop" alt="Difusor Minimal" loading="lazy">
                    <div class="gallery__item-overlay"><span>Difusor Minimal</span></div>
                </div>
                <div class="gallery__item" data-product="Porta Retrato Arc">
                    <img src="https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?w=600&h=450&fit=crop" alt="Porta Retrato Arc" loading="lazy">
                    <div class="gallery__item-overlay"><span>Porta Retrato Arc</span></div>
                </div>
                <div class="gallery__item" data-product="Reloj de Pared">
                    <img src="https://images.unsplash.com/photo-1563861826100-9cb868fdbe1c?w=600&h=450&fit=crop" alt="Reloj de Pared" loading="lazy">
                    <div class="gallery__item-overlay"><span>Reloj de Pared</span></div>
                </div>
                <div class="gallery__item" data-product="Maceta Vertex">
                    <img src="https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=600&h=450&fit=crop" alt="Maceta Vertex" loading="lazy">
                    <div class="gallery__item-overlay"><span>Maceta Vertex</span></div>
                </div>
                <div class="gallery__item" data-product="Cuenco Zen">
                    <img src="https://images.unsplash.com/photo-1493552832785-8ae4e09e480f?w=600&h=450&fit=crop" alt="Cuenco Zen" loading="lazy">
                    <div class="gallery__item-overlay"><span>Cuenco Zen</span></div>
                </div>
                <!-- Duplicados para loop infinito -->
                <div class="gallery__item" data-product="Letra Deco A">
                    <img src="https://images.unsplash.com/photo-1513519245088-0e12902e35ca?w=600&h=450&fit=crop" alt="Letra Deco A" loading="lazy">
                    <div class="gallery__item-overlay"><span>Letra Deco A</span></div>
                </div>
                <div class="gallery__item" data-product="Maceta Bauhaus">
                    <img src="https://images.unsplash.com/photo-1501004318855-e73a3fc04086?w=600&h=450&fit=crop" alt="Maceta Bauhaus" loading="lazy">
                    <div class="gallery__item-overlay"><span>Maceta Bauhaus</span></div>
                </div>
                <div class="gallery__item" data-product="Escultura Twist">
                    <img src="https://images.unsplash.com/photo-1544967082-d9d25d867d66?w=600&h=450&fit=crop" alt="Escultura Twist" loading="lazy">
                    <div class="gallery__item-overlay"><span>Escultura Twist</span></div>
                </div>
                <div class="gallery__item" data-product="Difusor Minimal">
                    <img src="https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=600&h=450&fit=crop" alt="Difusor Minimal" loading="lazy">
                    <div class="gallery__item-overlay"><span>Difusor Minimal</span></div>
                </div>
                <div class="gallery__item" data-product="Porta Retrato Arc">
                    <img src="https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?w=600&h=450&fit=crop" alt="Porta Retrato Arc" loading="lazy">
                    <div class="gallery__item-overlay"><span>Porta Retrato Arc</span></div>
                </div>
                <div class="gallery__item" data-product="Reloj de Pared">
                    <img src="https://images.unsplash.com/photo-1563861826100-9cb868fdbe1c?w=600&h=450&fit=crop" alt="Reloj de Pared" loading="lazy">
                    <div class="gallery__item-overlay"><span>Reloj de Pared</span></div>
                </div>
                <div class="gallery__item" data-product="Maceta Vertex">
                    <img src="https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=600&h=450&fit=crop" alt="Maceta Vertex" loading="lazy">
                    <div class="gallery__item-overlay"><span>Maceta Vertex</span></div>
                </div>
                <div class="gallery__item" data-product="Cuenco Zen">
                    <img src="https://images.unsplash.com/photo-1493552832785-8ae4e09e480f?w=600&h=450&fit=crop" alt="Cuenco Zen" loading="lazy">
                    <div class="gallery__item-overlay"><span>Cuenco Zen</span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- EXPANDED PRODUCT VIEW -->
    <div class="gallery-expand" id="galleryExpand">
        <button class="gallery-expand__close" id="galleryExpandClose">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div class="gallery-expand__content">
            <img class="gallery-expand__img" id="galleryExpandImg" src="" alt="">
            <div class="gallery-expand__info">
                <span class="gallery-expand__name" id="galleryExpandName"></span>
                <a href="#contacto" class="btn btn--primary btn--sm" data-i18n="gal-consult" onclick="document.getElementById('galleryExpand').classList.remove('active');document.body.style.overflow='';">CONSULTAR</a>
            </div>
        </div>
    </div>

    <!-- NOSOTROS / POR QUE BTLDECO -->
    <section class="section about dark-section" id="nosotros">
        <div class="about__noise"></div>
        <div class="container">
            <div class="about__layout">
                <div class="about__intro reveal">
                    <span class="section__tag" data-i18n="about-tag">SOBRE BTLDECO</span>
                    <h2 class="section__title" data-i18n="about-title">Diseño argentino,<br>fabricacion artesanal</h2>
                    <p class="about__desc" data-i18n="about-desc">Combinamos tecnologia de impresion 3D con acabados manuales para crear piezas decorativas unicas. Cada producto esta pensado para aportar caracter y calidez a tus espacios.</p>
                </div>
                <div class="about__features">
                    <div class="about__feature reveal">
                        <div class="about__feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                        </div>
                        <div>
                            <h3 data-i18n="about-f1-t">Diseño Original</h3>
                            <p data-i18n="about-f1-d">Cada pieza nace de un proceso creativo propio. No revendemos, creamos.</p>
                        </div>
                    </div>
                    <div class="about__feature reveal">
                        <div class="about__feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <div>
                            <h3 data-i18n="about-f2-t">Calidad Premium</h3>
                            <p data-i18n="about-f2-d">Terminaciones a mano, control pieza por pieza. Sin defectos, sin apuro.</p>
                        </div>
                    </div>
                    <div class="about__feature reveal">
                        <div class="about__feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div>
                            <h3 data-i18n="about-f3-t">Envios Seguros</h3>
                            <p data-i18n="about-f3-d">Packaging protector a medida. Envios a todo el pais con seguimiento.</p>
                        </div>
                    </div>
                    <div class="about__feature reveal">
                        <div class="about__feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div>
                            <h3 data-i18n="about-f4-t">Entrega Rapida</h3>
                            <p data-i18n="about-f4-d">Produccion agil con entrega promedio en 48 horas habiles.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="section cta-section">
        <div class="container">
            <div class="cta-block reveal">
                <span class="section__tag" data-i18n="cta-tag">PEDIDOS PERSONALIZADOS</span>
                <h2 class="section__title" data-i18n="cta-title">¿Tenes una idea?<br>La hacemos realidad.</h2>
                <p class="cta-block__desc" data-i18n="cta-desc">Tambien hacemos piezas a medida. Contanos que necesitas y te armamos un presupuesto sin compromiso.</p>
                <div class="cta-block__actions">
                    <a href="#contacto" class="btn btn--primary btn--lg" data-i18n="cta-btn">
                        HACER PEDIDO
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                    </a>
                    <a href="https://wa.me/5491162743425" target="_blank" class="btn btn--outline btn--lg">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.611.611l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.37 0-4.567-.68-6.434-1.852l-.448-.29-2.648.888.888-2.648-.29-.448A9.96 9.96 0 012 12C2 6.486 6.486 2 12 2s10 4.486 10 10-4.486 10-10 10z"/></svg>
                        WHATSAPP
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACTO -->
    <section class="section contact" id="contacto">
        <div class="container">
            <div class="contact__grid">
                <div class="contact__info reveal">
                    <span class="section__tag" data-i18n="ct-tag">CONTACTO</span>
                    <h2 class="section__title" data-i18n="ct-title">Hablemos de<br>tu pedido</h2>
                    <p class="contact__desc" data-i18n="ct-desc">Respondemos en menos de 24hs. Tambien podes escribirnos directo por WhatsApp.</p>

                    <div class="contact__cards">
                        <a href="mailto:hola@btldeco.com.ar" class="contact-card">
                            <div class="contact-card__icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </div>
                            <div>
                                <span class="contact-card__label" data-i18n="ct-label-email">EMAIL</span>
                                <span class="contact-card__value">hola@btldeco.com.ar</span>
                            </div>
                        </a>
                        <a href="https://wa.me/5491162743425" target="_blank" class="contact-card">
                            <div class="contact-card__icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                            </div>
                            <div>
                                <span class="contact-card__label" data-i18n="ct-label-phone">WHATSAPP</span>
                                <span class="contact-card__value">+54 11 6274-3425</span>
                            </div>
                        </a>
                        <div class="contact-card">
                            <div class="contact-card__icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <div>
                                <span class="contact-card__label" data-i18n="ct-label-location">UBICACION</span>
                                <span class="contact-card__value">Buenos Aires, Argentina</span>
                            </div>
                        </div>
                    </div>

                    <div class="contact__social-card">
                        <span class="contact-card__label" data-i18n="ct-label-social">REDES SOCIALES</span>
                        <div class="contact__social">
                            <a href="#" aria-label="Instagram">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none"/></svg>
                            </a>
                            <a href="#" aria-label="Facebook">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                            </a>
                            <a href="#" aria-label="TikTok">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12a4 4 0 104 4V4a5 5 0 005 5"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
                <form class="contact__form reveal">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name" data-i18n="ct-label-name">NOMBRE</label>
                            <input type="text" id="name" placeholder="Tu nombre completo" data-i18n-ph="ct-ph-name" required>
                        </div>
                        <div class="form-group">
                            <label for="phone" data-i18n="ct-label-phone2">TELEFONO</label>
                            <input type="tel" id="phone" placeholder="+54 11 ..." data-i18n-ph="ct-ph-phone">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" data-i18n="ct-label-email">EMAIL</label>
                            <input type="email" id="email" placeholder="tu@email.com" data-i18n-ph="ct-ph-email" required>
                        </div>
                        <div class="form-group">
                            <label for="product" data-i18n="ct-label-product">PRODUCTO</label>
                            <select id="product">
                                <option value="" disabled selected data-i18n="ct-ph-product">Selecciona un producto</option>
                                <option data-i18n="ct-opt1">Macetas de diseño</option>
                                <option data-i18n="ct-opt2">Figuras & esculturas</option>
                                <option data-i18n="ct-opt3">Portavelas & difusores</option>
                                <option data-i18n="ct-opt4">Organizadores & bandejas</option>
                                <option data-i18n="ct-opt5">Pedido personalizado</option>
                                <option data-i18n="ct-opt6">Consulta general</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="message" data-i18n="ct-label-msg">MENSAJE</label>
                        <textarea id="message" rows="4" placeholder="Contanos que te gustaria..." data-i18n-ph="ct-ph-msg"></textarea>
                    </div>
                    <button type="submit" class="btn btn--primary btn--lg btn--full" data-i18n="ct-btn">
                        ENVIAR MENSAJE
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </form>
            </div>
            <div class="contact__bottom">
                <p data-i18n="ct-copy">&copy; 2026 BTLDECO. Todos los derechos reservados.</p>
            </div>
        </div>
    </section>

    <!-- FLOATING BUTTONS -->
    <a href="https://wa.me/5491162743425" target="_blank" class="float-btn float-btn--wa" aria-label="WhatsApp">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="#fff"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.611.611l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.37 0-4.567-.68-6.434-1.852l-.448-.29-2.648.888.888-2.648-.29-.448A9.96 9.96 0 012 12C2 6.486 6.486 2 12 2s10 4.486 10 10-4.486 10-10 10z"/></svg>
    </a>
    <button class="float-btn float-btn--top" id="scrollTop" aria-label="Volver arriba">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15l-6-6-6 6"/></svg>
    </button>

    <?php include "includes/cart_drawer.php"; ?>
    <script src="js/main.js?v=13"></script>
    <script src="js/carousel.js?v=15"></script>
</body>
</html>
