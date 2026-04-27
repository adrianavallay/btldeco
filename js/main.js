/* ============================================================
   BTLDECO — INTERACTIONS, SHADER HERO, GALLERY EXPAND
   ============================================================ */

(function () {
    'use strict';

    // =========================================================
    // WEBGL SHADER HERO (paper-design MeshGradient style)
    // Colors: #000000, #8B4513, #ffffff, #3E2723, #5D4037
    // =========================================================
    function initShaderCanvas(canvasId, isWireframe) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
        if (!gl) return;

        const vertexSource = `
            attribute vec2 a_position;
            void main() { gl_Position = vec4(a_position, 0.0, 1.0); }
        `;

        // MeshGradient-style fragment shader
        const fragmentSource = isWireframe ? `
            precision mediump float;
            uniform float u_time;
            uniform vec2 u_resolution;
            uniform vec2 u_mouse;

            void main() {
                vec2 uv = gl_FragCoord.xy / u_resolution;
                vec2 p = uv * 2.0 - 1.0;
                p.x *= u_resolution.x / u_resolution.y;

                float t = u_time * 0.2;

                float gridSize = 0.12;
                vec2 grid = abs(fract(p / gridSize + t * 0.1) - 0.5);
                float line = min(grid.x, grid.y);
                float wire = 1.0 - smoothstep(0.0, 0.02, line);

                float distort = sin(p.x * 4.0 + t) * cos(p.y * 3.0 + t * 0.7) * 0.5;
                wire *= (0.3 + distort * 0.3);

                vec3 col = vec3(1.0) * wire;
                float alpha = wire * 0.4;

                gl_FragColor = vec4(col, alpha);
            }
        ` : `
            precision mediump float;
            uniform float u_time;
            uniform vec2 u_resolution;
            uniform vec2 u_mouse;

            vec2 hash(vec2 p) {
                p = vec2(dot(p, vec2(127.1, 311.7)), dot(p, vec2(269.5, 183.3)));
                return -1.0 + 2.0 * fract(sin(p) * 43758.5453123);
            }

            float noise(vec2 p) {
                vec2 i = floor(p);
                vec2 f = fract(p);
                vec2 u = f * f * (3.0 - 2.0 * f);
                return mix(mix(dot(hash(i), f),
                               dot(hash(i + vec2(1.0, 0.0)), f - vec2(1.0, 0.0)), u.x),
                           mix(dot(hash(i + vec2(0.0, 1.0)), f - vec2(0.0, 1.0)),
                               dot(hash(i + vec2(1.0, 1.0)), f - vec2(1.0, 1.0)), u.x), u.y);
            }

            void main() {
                vec2 uv = gl_FragCoord.xy / u_resolution;
                vec2 p = uv * 2.0 - 1.0;
                p.x *= u_resolution.x / u_resolution.y;

                float t = u_time * 0.3;

                vec3 c1 = vec3(0.0, 0.0, 0.0);
                vec3 c2 = vec3(0.545, 0.271, 0.075);
                vec3 c3 = vec3(1.0, 1.0, 1.0);
                vec3 c4 = vec3(0.243, 0.153, 0.137);
                vec3 c5 = vec3(0.365, 0.251, 0.216);

                // p3 (white light) follows mouse, others animate
                vec2 p1 = vec2(sin(t * 0.7) * 0.8, cos(t * 0.5) * 0.7);
                vec2 p2 = vec2(cos(t * 0.4) * 0.9, sin(t * 0.6) * 0.8);
                vec2 p3 = u_mouse; // white light follows mouse
                vec2 p4 = vec2(cos(t * 0.8 + 3.0) * 0.7, sin(t * 0.4 + 2.0) * 0.6);
                vec2 p5 = vec2(sin(t * 0.3 + 4.0) * 0.5, cos(t * 0.9 + 3.0) * 0.5);

                float w1 = 1.0 / (0.2 + length(p - p1) * 2.5);
                float w2 = 1.0 / (0.2 + length(p - p2) * 2.5);
                float w3 = 1.0 / (0.15 + length(p - p3) * 2.0);
                float w4 = 1.0 / (0.2 + length(p - p4) * 2.5);
                float w5 = 1.0 / (0.2 + length(p - p5) * 2.5);

                float totalW = w1 + w2 + w3 + w4 + w5;
                vec3 col = (c1 * w1 + c2 * w2 + c3 * w3 + c4 * w4 + c5 * w5) / totalW;

                float n = noise(p * 3.0 + t * 0.5) * 0.08;
                col += n;

                float vig = 1.0 - smoothstep(0.5, 1.8, length(p));
                col *= 0.8 + vig * 0.2;

                col = clamp(col, 0.0, 1.0);

                gl_FragColor = vec4(col, 1.0);
            }
        `;

        function createShader(type, source) {
            const shader = gl.createShader(type);
            gl.shaderSource(shader, source);
            gl.compileShader(shader);
            if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
                gl.deleteShader(shader);
                return null;
            }
            return shader;
        }

        const vs = createShader(gl.VERTEX_SHADER, vertexSource);
        const fs = createShader(gl.FRAGMENT_SHADER, fragmentSource);
        if (!vs || !fs) return;

        const program = gl.createProgram();
        gl.attachShader(program, vs);
        gl.attachShader(program, fs);
        gl.linkProgram(program);
        if (!gl.getProgramParameter(program, gl.LINK_STATUS)) return;

        gl.useProgram(program);

        const buf = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, buf);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1,-1,1,-1,-1,1,-1,1,1,-1,1,1]), gl.STATIC_DRAW);

        const posLoc = gl.getAttribLocation(program, 'a_position');
        gl.enableVertexAttribArray(posLoc);
        gl.vertexAttribPointer(posLoc, 2, gl.FLOAT, false, 0, 0);

        if (isWireframe) {
            gl.enable(gl.BLEND);
            gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA);
        }

        const timeLoc = gl.getUniformLocation(program, 'u_time');
        const resLoc = gl.getUniformLocation(program, 'u_resolution');
        const mouseLoc = gl.getUniformLocation(program, 'u_mouse');

        // Mouse tracking state
        let mouseX = 0.0, mouseY = 0.0;
        let targetMouseX = 0.0, targetMouseY = 0.0;
        let mouseInHero = false;

        if (!isWireframe) {
            const heroEl = canvas.closest('.hero');
            if (heroEl) {
                heroEl.addEventListener('mousemove', function(e) {
                    const rect = heroEl.getBoundingClientRect();
                    // Convert to -1..1 range, flip Y
                    targetMouseX = ((e.clientX - rect.left) / rect.width) * 2.0 - 1.0;
                    targetMouseY = -(((e.clientY - rect.top) / rect.height) * 2.0 - 1.0);
                    // Scale by aspect ratio
                    targetMouseX *= rect.width / rect.height;
                    mouseInHero = true;
                });
                heroEl.addEventListener('mouseleave', function() {
                    mouseInHero = false;
                });
            }
        }

        function resize() {
            const dpr = Math.min(window.devicePixelRatio, 1.5);
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;
            gl.viewport(0, 0, canvas.width, canvas.height);
        }

        window.addEventListener('resize', resize);
        resize();

        let startTime = performance.now();
        let animId = null;

        function render() {
            const elapsed = (performance.now() - startTime) / 1000;

            // Smooth interpolation toward target or auto-animate
            if (mouseInHero) {
                mouseX += (targetMouseX - mouseX) * 0.05;
                mouseY += (targetMouseY - mouseY) * 0.05;
            } else {
                // Auto-animate when mouse is outside
                const autoX = Math.sin(elapsed * 0.5 + 2.0) * 0.6;
                const autoY = Math.cos(elapsed * 0.3 + 1.0) * 0.9;
                mouseX += (autoX - mouseX) * 0.03;
                mouseY += (autoY - mouseY) * 0.03;
            }

            gl.uniform1f(timeLoc, elapsed);
            gl.uniform2f(resLoc, canvas.width, canvas.height);
            gl.uniform2f(mouseLoc, mouseX, mouseY);
            gl.drawArrays(gl.TRIANGLES, 0, 6);
            animId = requestAnimationFrame(render);
        }

        // Only render when hero visible
        const heroEl = canvas.closest('.hero');
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (!animId) { startTime = performance.now(); render(); }
                } else {
                    if (animId) { cancelAnimationFrame(animId); animId = null; }
                }
            });
        }, { threshold: 0 });
        obs.observe(heroEl);
    }

    // Init both shader layers
    initShaderCanvas('heroShader', false);
    initShaderCanvas('heroShaderWire', true);

    // =========================================================
    // PULSING BORDER CIRCLE (Canvas 2D)
    // =========================================================
    const pulsingCanvas = document.getElementById('pulsingCanvas');
    if (pulsingCanvas) {
        const ctx = pulsingCanvas.getContext('2d');
        const colors = ['#BEECFF', '#E77EDC', '#FF4C3E', '#00FF88', '#FFD700', '#FF6B35', '#8A2BE2'];
        let pulseStart = performance.now();

        function drawPulse() {
            const elapsed = (performance.now() - pulseStart) / 1000;
            const w = pulsingCanvas.width;
            const h = pulsingCanvas.height;
            const cx = w / 2;
            const cy = h / 2;
            const radius = Math.min(w, h) * 0.38;

            ctx.clearRect(0, 0, w, h);

            // Draw pulsing colored ring
            const segments = colors.length * 3;
            for (let i = 0; i < segments; i++) {
                const angle = (i / segments) * Math.PI * 2 + elapsed * 0.5;
                const nextAngle = ((i + 1) / segments) * Math.PI * 2 + elapsed * 0.5;
                const colorIdx = i % colors.length;
                const pulseAmount = Math.sin(elapsed * 1.5 + i * 0.8) * 0.1 + 0.9;
                const r = radius * pulseAmount;

                ctx.beginPath();
                ctx.arc(cx, cy, r, angle, nextAngle + 0.05);
                ctx.strokeStyle = colors[colorIdx];
                ctx.lineWidth = 3;
                ctx.shadowBlur = 12;
                ctx.shadowColor = colors[colorIdx];
                ctx.globalAlpha = 0.7 + Math.sin(elapsed * 2 + i) * 0.3;
                ctx.stroke();
            }
            ctx.globalAlpha = 1;
            ctx.shadowBlur = 0;

            requestAnimationFrame(drawPulse);
        }
        drawPulse();
    }

    // =========================================================
    // THEME TOGGLE
    // =========================================================
    const html = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');
    const STORAGE_KEY = 'btl-theme';

    function setTheme(theme) {
        html.setAttribute('data-theme', theme);
        localStorage.setItem(STORAGE_KEY, theme);
    }

    setTheme('light');

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const current = html.getAttribute('data-theme') || 'light';
            setTheme(current === 'light' ? 'dark' : 'light');
        });
    }

    // =========================================================
    // i18n LANGUAGE TOGGLE
    // =========================================================
    const langSwitch = document.getElementById('langSwitch');
    const langBtns = langSwitch ? langSwitch.querySelectorAll('.lang-switch__btn') : [];
    const LANG_KEY = 'btl-lang';

    const translations = {
        es: {
            'nav-inicio': 'Inicio',
            'nav-productos': 'Productos',
            'nav-galeria': 'Galeria',
            'nav-nosotros': 'Nosotros',
            'nav-contacto': 'Contacto',
            'nav-cta': 'TIENDA',
            'hero-tag': 'DECORACION DE DISEÑO',
            'hero-t1': 'Piezas que',
            'hero-t2': 'transforman',
            'hero-t3': 'espacios',
            'hero-sub': 'Objetos decorativos unicos, fabricados con tecnologia 3D y terminaciones artesanales. Macetas, figuras, portavelas y mas — diseño argentino para tu hogar.',
            'hero-btn1': 'VER PRODUCTOS',
            'hero-btn2': 'EXPLORAR GALERIA',
            'hero-s1': 'Piezas vendidas',
            'hero-s2': 'Diseños unicos',
            'hero-s3': 'Clientes felices',
            'prod-tag': 'NUESTROS PRODUCTOS',
            'prod-title': 'Diseño que decora,<br>detalle que enamora',
            'prod-sub': 'Cada pieza esta pensada para aportar personalidad y calidez a tus ambientes.',
            'prod1-badge': 'POPULAR',
            'prod1-t': 'Macetas de Diseño',
            'prod1-d': 'Geometricas, organicas y minimalistas. Ideales para suculentas, cactus y plantas de interior.',
            'prod2-badge': 'NUEVO',
            'prod2-t': 'Figuras & Esculturas',
            'prod2-d': 'Piezas esculturales para repisas, mesas y bibliotecas. Arte moderno para tu hogar.',
            'prod3-t': 'Portavelas & Difusores',
            'prod3-d': 'Ambientacion con estilo. Portavelas texturados y difusores con diseño unico.',
            'prod4-t': 'Organizadores & Bandejas',
            'prod4-d': 'Funcionalidad y diseño en uno. Organizadores para escritorio, baño y cocina.',
            'prod-link': 'Consultar',
            'gal-tag': 'NUESTROS TRABAJOS',
            'gal-title': 'Galeria de<br>productos',
            'gal-consult': 'CONSULTAR',
            'about-tag': 'SOBRE BTLDECO',
            'about-title': 'Diseño argentino,<br>fabricacion artesanal',
            'about-desc': 'Combinamos tecnologia de impresion 3D con acabados manuales para crear piezas decorativas unicas. Cada producto esta pensado para aportar caracter y calidez a tus espacios.',
            'about-f1-t': 'Diseño Original',
            'about-f1-d': 'Cada pieza nace de un proceso creativo propio. No revendemos, creamos.',
            'about-f2-t': 'Calidad Premium',
            'about-f2-d': 'Terminaciones a mano, control pieza por pieza. Sin defectos, sin apuro.',
            'about-f3-t': 'Envios Seguros',
            'about-f3-d': 'Packaging protector a medida. Envios a todo el pais con seguimiento.',
            'about-f4-t': 'Entrega Rapida',
            'about-f4-d': 'Produccion agil con entrega promedio en 48 horas habiles.',
            'cta-tag': 'PEDIDOS PERSONALIZADOS',
            'cta-title': '¿Tenes una idea?<br>La hacemos realidad.',
            'cta-desc': 'Tambien hacemos piezas a medida. Contanos que necesitas y te armamos un presupuesto sin compromiso.',
            'cta-btn': 'HACER PEDIDO',
            'ct-tag': 'CONTACTO',
            'ct-title': 'Hablemos de<br>tu pedido',
            'ct-desc': 'Respondemos en menos de 24hs. Tambien podes escribirnos directo por WhatsApp.',
            'ct-label-name': 'NOMBRE',
            'ct-label-email': 'EMAIL',
            'ct-label-product': 'PRODUCTO',
            'ct-label-msg': 'MENSAJE',
            'ct-ph-name': 'Tu nombre completo',
            'ct-ph-email': 'tu@email.com',
            'ct-ph-product': 'Selecciona un producto',
            'ct-ph-msg': 'Contanos que te gustaria...',
            'ct-label-phone': 'WHATSAPP',
            'ct-label-phone2': 'TELEFONO',
            'ct-ph-phone': '+54 11 ...',
            'ct-label-location': 'UBICACION',
            'ct-label-social': 'REDES SOCIALES',
            'ct-btn': 'ENVIAR MENSAJE',
            'ct-copy': '&copy; 2026 BTLDECO. Todos los derechos reservados.',
            'ct-opt1': 'Macetas de diseño',
            'ct-opt2': 'Figuras & esculturas',
            'ct-opt3': 'Portavelas & difusores',
            'ct-opt4': 'Organizadores & bandejas',
            'ct-opt5': 'Pedido personalizado',
            'ct-opt6': 'Consulta general',
            // Tienda
            'tienda-title': 'Tienda',
            'tienda-sub': 'Todos nuestros productos de decoracion',
            'tienda-todos': 'Todos',
            'tienda-sort-default': 'Mas recientes',
            'tienda-sort-price-asc': 'Precio: menor a mayor',
            'tienda-sort-price-desc': 'Precio: mayor a menor',
            'tienda-sort-name': 'Nombre: A-Z',
            'tienda-sort-sold': 'Mas vendidos',
            'tienda-add': 'AÑADIR AL CARRITO',
            'tienda-agotado': 'AGOTADO',
            // Producto
            'prod-delivery': '48hs habiles',
            'prod-shipping': 'A todo el pais',
            'prod-stock': 'disponibles',
            'prod-add-cart': 'AÑADIR AL CARRITO',
            'prod-wa': 'CONSULTAR POR WHATSAPP',
            'prod-related': 'Tambien te puede gustar',
            // Carrito
            'cart-title': 'Tu Carrito',
            'cart-empty': 'Tu carrito esta vacio',
            'cart-explore': 'EXPLORAR PRODUCTOS',
            'cart-subtotal': 'Subtotal',
            'cart-total': 'Total',
            'cart-checkout': 'FINALIZAR COMPRA',
            'cart-continue': 'Seguir comprando',
            // CTA
            'cta-banner-title': 'Descubri toda nuestra <em>coleccion</em>',
            'cta-banner-desc': 'Toca cualquier producto para ver el detalle y agregalo a tu carrito.',
            'cta-banner-scroll': 'SCROLL PARA EXPLORAR',
            'gal-header-tag': 'EXPLORA NUESTRA COLECCION',
            'gal-header-title': 'Mas productos que<br><em>vas a amar</em>',
            'gal-header-sub': 'Cada pieza tiene una historia. Descubri la tuya.',
        },
        en: {
            'nav-inicio': 'Home',
            'nav-productos': 'Products',
            'nav-galeria': 'Gallery',
            'nav-nosotros': 'About',
            'nav-contacto': 'Contact',
            'nav-cta': 'SHOP',
            'hero-tag': 'DESIGNER DECOR',
            'hero-t1': 'Pieces that',
            'hero-t2': 'transform',
            'hero-t3': 'spaces',
            'hero-sub': 'Unique decorative objects, made with 3D technology and artisanal finishes. Planters, figures, candle holders and more — Argentine design for your home.',
            'hero-btn1': 'VIEW PRODUCTS',
            'hero-btn2': 'EXPLORE GALLERY',
            'hero-s1': 'Pieces sold',
            'hero-s2': 'Unique designs',
            'hero-s3': 'Happy clients',
            'prod-tag': 'OUR PRODUCTS',
            'prod-title': 'Design that decorates,<br>detail that captivates',
            'prod-sub': 'Each piece is designed to add personality and warmth to your spaces.',
            'prod1-badge': 'POPULAR',
            'prod1-t': 'Design Planters',
            'prod1-d': 'Geometric, organic and minimalist. Perfect for succulents, cacti and indoor plants.',
            'prod2-badge': 'NEW',
            'prod2-t': 'Figures & Sculptures',
            'prod2-d': 'Sculptural pieces for shelves, tables and bookcases. Modern art for your home.',
            'prod3-t': 'Candle Holders & Diffusers',
            'prod3-d': 'Stylish ambiance. Textured candle holders and uniquely designed diffusers.',
            'prod4-t': 'Organizers & Trays',
            'prod4-d': 'Function meets design. Organizers for desk, bathroom and kitchen.',
            'prod-link': 'Inquire',
            'gal-tag': 'OUR WORK',
            'gal-title': 'Product<br>gallery',
            'gal-consult': 'INQUIRE',
            'about-tag': 'ABOUT BTLDECO',
            'about-title': 'Argentine design,<br>artisanal craftsmanship',
            'about-desc': 'We combine 3D printing technology with hand-finished details to create unique decorative pieces. Every product is designed to add character and warmth to your spaces.',
            'about-f1-t': 'Original Design',
            'about-f1-d': 'Every piece comes from our own creative process. We don\'t resell, we create.',
            'about-f2-t': 'Premium Quality',
            'about-f2-d': 'Hand-finished, inspected piece by piece. No defects, no rush.',
            'about-f3-t': 'Safe Shipping',
            'about-f3-d': 'Custom protective packaging. Nationwide shipping with tracking.',
            'about-f4-t': 'Fast Delivery',
            'about-f4-d': 'Agile production with average delivery in 48 business hours.',
            'cta-tag': 'CUSTOM ORDERS',
            'cta-title': 'Have an idea?<br>We make it real.',
            'cta-desc': 'We also make custom pieces. Tell us what you need and we\'ll put together a no-commitment quote.',
            'cta-btn': 'PLACE ORDER',
            'ct-tag': 'CONTACT',
            'ct-title': 'Let\'s talk about<br>your order',
            'ct-desc': 'We respond within 24 hours. You can also message us directly on WhatsApp.',
            'ct-label-name': 'NAME',
            'ct-label-email': 'EMAIL',
            'ct-label-product': 'PRODUCT',
            'ct-label-msg': 'MESSAGE',
            'ct-ph-name': 'Your full name',
            'ct-ph-email': 'you@email.com',
            'ct-ph-product': 'Select a product',
            'ct-ph-msg': 'Tell us what you\'d like...',
            'ct-label-phone': 'WHATSAPP',
            'ct-label-phone2': 'PHONE',
            'ct-ph-phone': '+54 11 ...',
            'ct-label-location': 'LOCATION',
            'ct-label-social': 'SOCIAL MEDIA',
            'ct-btn': 'SEND MESSAGE',
            'ct-copy': '&copy; 2026 BTLDECO. All rights reserved.',
            'ct-opt1': 'Design planters',
            'ct-opt2': 'Figures & sculptures',
            'ct-opt3': 'Candle holders & diffusers',
            'ct-opt4': 'Organizers & trays',
            'ct-opt5': 'Custom order',
            'ct-opt6': 'General inquiry',
            // Store
            'tienda-title': 'Shop',
            'tienda-sub': 'All our decoration products',
            'tienda-todos': 'All',
            'tienda-sort-default': 'Most recent',
            'tienda-sort-price-asc': 'Price: low to high',
            'tienda-sort-price-desc': 'Price: high to low',
            'tienda-sort-name': 'Name: A-Z',
            'tienda-sort-sold': 'Best sellers',
            'tienda-add': 'ADD TO CART',
            'tienda-agotado': 'SOLD OUT',
            // Product
            'prod-delivery': '48 business hours',
            'prod-shipping': 'Nationwide',
            'prod-stock': 'available',
            'prod-add-cart': 'ADD TO CART',
            'prod-wa': 'ASK VIA WHATSAPP',
            'prod-related': 'You might also like',
            // Cart
            'cart-title': 'Your Cart',
            'cart-empty': 'Your cart is empty',
            'cart-explore': 'EXPLORE PRODUCTS',
            'cart-subtotal': 'Subtotal',
            'cart-total': 'Total',
            'cart-checkout': 'CHECKOUT',
            'cart-continue': 'Continue shopping',
            // CTA
            'cta-banner-title': 'Discover our full <em>collection</em>',
            'cta-banner-desc': 'Tap any product to see details and add it to your cart.',
            'cta-banner-scroll': 'SCROLL TO EXPLORE',
            'gal-header-tag': 'EXPLORE OUR COLLECTION',
            'gal-header-title': 'More products you\'ll<br><em>love</em>',
            'gal-header-sub': 'Every piece has a story. Discover yours.',
        }
    };

    function applyLang(lang) {
        const t = translations[lang];
        if (!t) return;
        document.querySelectorAll('[data-i18n]').forEach((el) => {
            const key = el.getAttribute('data-i18n');
            if (t[key]) el.innerHTML = t[key];
        });
        document.querySelectorAll('[data-i18n-ph]').forEach((el) => {
            const key = el.getAttribute('data-i18n-ph');
            if (t[key]) el.placeholder = t[key];
        });
        document.documentElement.lang = lang === 'es' ? 'es' : 'en';
        localStorage.setItem(LANG_KEY, lang);
        document.cookie = 'btl-lang=' + lang + ';path=/;max-age=31536000';
        if (langSwitch) {
            langSwitch.setAttribute('data-active', lang);
            langBtns.forEach((btn) => {
                btn.classList.toggle('active', btn.dataset.lang === lang);
            });
        }
    }

    const savedLang = localStorage.getItem(LANG_KEY);
    let currentLang = "es";
    if (false) {
        currentLang = savedLang;
    } else {
        const browserLang = (navigator.language || navigator.userLanguage || 'es').slice(0, 2);
        currentLang = browserLang === 'en' ? 'en' : 'es';
    }
    applyLang(currentLang);

    langBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            const lang = btn.dataset.lang;
            if (lang !== currentLang) {
                currentLang = lang;
                applyLang(lang);
                // Reload PHP pages to apply server-side translations
                var isPhpPage = window.location.pathname.match(/\.(php)$/);
                if (isPhpPage) { location.reload(); return; }
            }
        });
    });

    // =========================================================
    // IMAGE ACCORDION (hover to expand)
    // =========================================================
    const accordionItems = document.querySelectorAll('.accordion-item');
    accordionItems.forEach((item) => {
        item.addEventListener('mouseenter', () => {
            accordionItems.forEach((el) => el.classList.remove('active'));
            item.classList.add('active');
        });
    });

    // =========================================================
    // SCROLL REVEAL
    // =========================================================
    const revealElements = document.querySelectorAll('.reveal');
    const revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const siblings = entry.target.parentElement.querySelectorAll('.reveal');
                    let sibIndex = 0;
                    siblings.forEach((s, i) => { if (s === entry.target) sibIndex = i; });
                    entry.target.style.transitionDelay = (sibIndex % 6) * 80 + 'ms';
                    entry.target.classList.add('visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.1, rootMargin: '0px 0px -30px 0px' }
    );
    revealElements.forEach((el) => revealObserver.observe(el));

    // =========================================================
    // NAVBAR
    // =========================================================
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 50);
    }, { passive: true });

    // Mobile toggle
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    if (navToggle) {
        navToggle.addEventListener('click', () => {
            navToggle.classList.toggle('active');
            navLinks.classList.toggle('active');
        });
        navLinks.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                navToggle.classList.remove('active');
                navLinks.classList.remove('active');
            });
        });
    }

    // =========================================================
    // COUNTER ANIMATION
    // =========================================================
    const counters = document.querySelectorAll('[data-count]');
    const counterObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.5 }
    );
    counters.forEach((el) => counterObserver.observe(el));

    function animateCounter(el) {
        const target = parseInt(el.dataset.count, 10);
        const duration = 1800;
        const start = performance.now();

        function update(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(target * eased);
            if (progress < 1) requestAnimationFrame(update);
        }

        requestAnimationFrame(update);
    }

    // =========================================================
    // SMOOTH ANCHOR SCROLL
    // =========================================================
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', (e) => {
            const id = anchor.getAttribute('href');
            if (id === '#') return;
            const target = document.querySelector(id);
            if (target) {
                e.preventDefault();
                const scrollMargin = parseFloat(getComputedStyle(target).scrollMarginTop) || 0;
                const offset = navbar.offsetHeight + 20 + scrollMargin;
                const top = target.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top, behavior: 'smooth' });
            }
        });
    });

    // =========================================================
    // NAVBAR DARK SECTION DETECTION
    // =========================================================
    const darkSections = document.querySelectorAll('.dark-section');
    function checkNavbarOverDark() {
        const navRect = navbar.getBoundingClientRect();
        const navCenter = navRect.top + navRect.height / 2;
        let overDark = false;
        darkSections.forEach((section) => {
            const rect = section.getBoundingClientRect();
            if (navCenter >= rect.top && navCenter <= rect.bottom) {
                overDark = true;
            }
        });
        navbar.classList.toggle('over-dark', overDark);
    }
    window.addEventListener('scroll', checkNavbarOverDark, { passive: true });
    checkNavbarOverDark();

    // =========================================================
    // SCROLL TO TOP
    // =========================================================
    const scrollTopBtn = document.getElementById('scrollTop');
    if (scrollTopBtn) {
        window.addEventListener('scroll', () => {
            scrollTopBtn.classList.toggle('visible', window.scrollY > 400);
        }, { passive: true });

        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // =========================================================
    // ACTIVE NAV LINK ON SCROLL
    // =========================================================
    const sections = document.querySelectorAll('section[id]');
    const navLinkItems = document.querySelectorAll('.navbar__links a');
    const sectionObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    navLinkItems.forEach((link) => {
                        link.classList.toggle('active', link.getAttribute('href') === '#' + id);
                    });
                }
            });
        },
        { threshold: 0.3, rootMargin: '-80px 0px -50% 0px' }
    );
    sections.forEach((s) => sectionObserver.observe(s));

    // =========================================================
    // GALLERY EXPAND (CLICK TO ENLARGE WITH PRODUCT NAME)
    // =========================================================
    const galleryExpand = document.getElementById('galleryExpand');
    const galleryExpandImg = document.getElementById('galleryExpandImg');
    const galleryExpandName = document.getElementById('galleryExpandName');
    const galleryExpandClose = document.getElementById('galleryExpandClose');

    if (galleryExpand) {
        document.querySelectorAll('.gallery__item').forEach((item) => {
            item.addEventListener('click', () => {
                const img = item.querySelector('img');
                const productName = item.getAttribute('data-product') || '';
                if (img) {
                    galleryExpandImg.src = img.src.replace('w=600&h=450', 'w=1400&h=1050');
                    galleryExpandImg.alt = productName;
                    galleryExpandName.textContent = productName;
                    galleryExpand.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            });
        });

        galleryExpandClose.addEventListener('click', closeGalleryExpand);
        galleryExpand.addEventListener('click', (e) => {
            if (e.target === galleryExpand) closeGalleryExpand();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && galleryExpand.classList.contains('active')) closeGalleryExpand();
        });

        function closeGalleryExpand() {
            galleryExpand.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // =========================================================
    // FORM SUBMIT FEEDBACK
    // =========================================================
    const form = document.querySelector('.contact__form');
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg> MENSAJE ENVIADO';
            btn.style.pointerEvents = 'none';
            btn.style.opacity = '0.7';
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.style.pointerEvents = '';
                btn.style.opacity = '';
                form.reset();
            }, 3000);
        });
    }

})();
