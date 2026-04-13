/* ============================================================
   BTLDECO — PRODUCTO: Product Detail Page
   ============================================================ */

(function () {
    'use strict';

    // Product database
    const products = {
        'maceta-hexa': {
            name: 'Maceta Hexa',
            category: 'Macetas',
            price: '$4.500',
            desc: 'Maceta geometrica hexagonal con diseño minimalista. Fabricada en PLA de alta calidad con acabado mate. Ideal para suculentas, cactus y plantas pequeñas de interior. Incluye orificio de drenaje.',
            material: 'PLA biodegradable',
            dimensions: '12 x 12 x 10 cm',
            images: [
                'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800&h=800&fit=crop',
            ],
        },
        'maceta-drop': {
            name: 'Maceta Drop',
            category: 'Macetas',
            price: '$5.200',
            desc: 'Maceta con forma de gota organica. Diseño fluido y contemporaneo que se adapta a cualquier ambiente. Acabado liso con textura sutil. Perfecta para plantas colgantes o de mesa.',
            material: 'PLA biodegradable',
            dimensions: '14 x 14 x 16 cm',
            images: [
                'https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1501004318855-e73a3fc04086?w=800&h=800&fit=crop',
            ],
        },
        'maceta-bauhaus': {
            name: 'Maceta Bauhaus',
            category: 'Macetas',
            price: '$6.800',
            desc: 'Inspirada en el movimiento Bauhaus, esta maceta combina formas geometricas puras con funcionalidad. Lineas rectas y angulos definidos. Ideal para ambientes modernos y minimalistas.',
            material: 'PLA biodegradable',
            dimensions: '15 x 15 x 13 cm',
            images: [
                'https://images.unsplash.com/photo-1501004318855-e73a3fc04086?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?w=800&h=800&fit=crop',
            ],
        },
        'maceta-vertex': {
            name: 'Maceta Vertex',
            category: 'Macetas',
            price: '$7.500',
            desc: 'Maceta con diseño facetado tipo low-poly. Cada cara refleja la luz de manera diferente creando un efecto visual unico. Ideal como pieza central en estanterias y escritorios.',
            material: 'PLA biodegradable',
            dimensions: '16 x 16 x 14 cm',
            images: [
                'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1501004318855-e73a3fc04086?w=800&h=800&fit=crop',
            ],
        },
        'figura-ondas': {
            name: 'Figura Ondas',
            category: 'Figuras',
            price: '$3.800',
            desc: 'Escultura decorativa con formas ondulantes organicas. Pieza de arte moderno para repisas, mesas y bibliotecas. Acabado suave al tacto con detalles de capas visibles que le dan caracter.',
            material: 'PLA premium',
            dimensions: '10 x 8 x 18 cm',
            images: [
                'https://images.unsplash.com/photo-1602028915047-37269d1a73f7?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1544967082-d9d25d867d66?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?w=800&h=800&fit=crop',
            ],
        },
        'escultura-twist': {
            name: 'Escultura Twist',
            category: 'Figuras',
            price: '$5.500',
            desc: 'Figura escultural con torsion helicoidal. Diseño parametrico que juega con la luz y las sombras. Pieza de conversacion para cualquier espacio. Acabado premium en color a eleccion.',
            material: 'PLA premium',
            dimensions: '8 x 8 x 24 cm',
            images: [
                'https://images.unsplash.com/photo-1544967082-d9d25d867d66?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1602028915047-37269d1a73f7?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?w=800&h=800&fit=crop',
            ],
        },
        'figura-minimal': {
            name: 'Figura Minimal',
            category: 'Figuras',
            price: '$7.200',
            desc: 'Escultura minimalista con formas abstractas. Diseño limpio que aporta sofisticacion sin saturar el espacio. Disponible en multiples colores para combinar con tu decoracion.',
            material: 'PLA premium',
            dimensions: '12 x 10 x 20 cm',
            images: [
                'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1602028915047-37269d1a73f7?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1544967082-d9d25d867d66?w=800&h=800&fit=crop',
            ],
        },
        'portavela-moon': {
            name: 'Portavela Moon',
            category: 'Portavelas',
            price: '$2.900',
            desc: 'Portavelas con forma de luna creciente. Crea una atmosfera calida y acogedora. Diseñado para velas tipo tealight. Textura suave con acabado mate.',
            material: 'PLA resistente al calor',
            dimensions: '10 x 10 x 8 cm',
            images: [
                'https://images.unsplash.com/photo-1603204077167-2fa0397f49de?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800&h=800&fit=crop',
            ],
        },
        'portavela-stone': {
            name: 'Portavela Stone',
            category: 'Portavelas',
            price: '$3.500',
            desc: 'Portavelas con textura de piedra natural. Efecto rustico y elegante. Compatible con velas tealight y pequeñas velas cilindricas. Ideal para centros de mesa.',
            material: 'PLA texturado',
            dimensions: '9 x 9 x 7 cm',
            images: [
                'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1603204077167-2fa0397f49de?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800&h=800&fit=crop',
            ],
        },
        'organizador-cubic': {
            name: 'Organizador Cubic',
            category: 'Organizadores',
            price: '$3.200',
            desc: 'Organizador modular con compartimentos cubicos. Perfecto para escritorios, baños o cocinas. Apila multiples unidades para crear tu propia configuracion.',
            material: 'PLA biodegradable',
            dimensions: '12 x 12 x 10 cm',
            images: [
                'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1493552832785-8ae4e09e480f?w=800&h=800&fit=crop',
            ],
        },
        'bandeja-cloud': {
            name: 'Bandeja Cloud',
            category: 'Organizadores',
            price: '$4.800',
            desc: 'Bandeja decorativa con forma de nube. Ideal para llaves, joyas o pequeños objetos. Diseño suave y organico que aporta un toque ludico a cualquier superficie.',
            material: 'PLA biodegradable',
            dimensions: '20 x 14 x 3 cm',
            images: [
                'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1493552832785-8ae4e09e480f?w=800&h=800&fit=crop',
            ],
        },
        'cuenco-zen': {
            name: 'Cuenco Zen',
            category: 'Organizadores',
            price: '$3.900',
            desc: 'Cuenco decorativo inspirado en la estetica zen japonesa. Formas simples y puras. Uso decorativo o funcional para frutas, llaves o como centro de mesa.',
            material: 'PLA biodegradable',
            dimensions: '18 x 18 x 6 cm',
            images: [
                'https://images.unsplash.com/photo-1493552832785-8ae4e09e480f?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800&h=800&fit=crop',
            ],
        },
        'florero-spiral': {
            name: 'Florero Spiral',
            category: 'Floreros',
            price: '$5.800',
            desc: 'Florero con diseño espiral ascendente. Pieza escultorica que luce espectacular con o sin flores. Diseño parametrico con textura de capas que le da profundidad visual.',
            material: 'PLA premium',
            dimensions: '10 x 10 x 22 cm',
            images: [
                'https://images.unsplash.com/photo-1578500494198-246f612d3b3d?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=800&h=800&fit=crop',
            ],
        },
        'florero-vertex': {
            name: 'Florero Vertex',
            category: 'Floreros',
            price: '$6.500',
            desc: 'Florero con geometria facetada. Cada angulo crea un juego de luces y sombras unico. Diseño moderno para flores frescas o secas. Impermeable con tratamiento interior.',
            material: 'PLA + tratamiento impermeable',
            dimensions: '11 x 11 x 25 cm',
            images: [
                'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1578500494198-246f612d3b3d?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1501004318855-e73a3fc04086?w=800&h=800&fit=crop',
            ],
        },
        'lampara-geo': {
            name: 'Lampara Geo',
            category: 'Lamparas',
            price: '$8.500',
            desc: 'Lampara de mesa con pantalla geometrica. Proyecta patrones de luz a traves de sus aperturas. Incluye portalamparas E27. Crea una atmosfera unica en cualquier habitacion.',
            material: 'PLA + componentes electricos',
            dimensions: '18 x 18 x 28 cm',
            images: [
                'https://images.unsplash.com/photo-1507473885765-e6ed057ab6fe?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1563861826100-9cb868fdbe1c?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?w=800&h=800&fit=crop',
            ],
        },
        'lampara-arc': {
            name: 'Lampara Arc',
            category: 'Lamparas',
            price: '$9.800',
            desc: 'Lampara de mesa con diseño de arco curvo. Elegancia y modernidad en una pieza. Luz calida difusa que genera un ambiente acogedor. Incluye portalamparas E27.',
            material: 'PLA + componentes electricos',
            dimensions: '15 x 15 x 32 cm',
            images: [
                'https://images.unsplash.com/photo-1563861826100-9cb868fdbe1c?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1507473885765-e6ed057ab6fe?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1544967082-d9d25d867d66?w=800&h=800&fit=crop',
            ],
        },
        'letra-deco-a': {
            name: 'Letra Deco A',
            category: 'Letras Deco',
            price: '$3.500',
            desc: 'Letra decorativa individual. Tipografia moderna sans-serif con profundidad 3D. Podes armar nombres, iniciales o palabras. Disponible en todo el abecedario.',
            material: 'PLA biodegradable',
            dimensions: '12 x 4 x 15 cm',
            images: [
                'https://images.unsplash.com/photo-1513519245088-0e12902e35ca?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1586281380117-5a60ae2050cc?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1602028915047-37269d1a73f7?w=800&h=800&fit=crop',
            ],
        },
        'letras-love': {
            name: 'Letras LOVE',
            category: 'Letras Deco',
            price: '$4.200',
            desc: 'Set de letras decorativas formando la palabra LOVE. Ideal para repisas, mesas de luz o como regalo. Tipografia elegante con acabado mate premium.',
            material: 'PLA biodegradable',
            dimensions: '40 x 4 x 12 cm (set)',
            images: [
                'https://images.unsplash.com/photo-1586281380117-5a60ae2050cc?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1513519245088-0e12902e35ca?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1544967082-d9d25d867d66?w=800&h=800&fit=crop',
            ],
        },
        'difusor-minimal': {
            name: 'Difusor Minimal',
            category: 'Difusores',
            price: '$4.100',
            desc: 'Difusor de aromas con diseño minimalista. Forma cilindrica con aperturas geometricas para la difusion del aroma. Compatible con varillas de bambu estandar.',
            material: 'PLA resistente',
            dimensions: '7 x 7 x 14 cm',
            images: [
                'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1603204077167-2fa0397f49de?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800&h=800&fit=crop',
            ],
        },
        'difusor-onyx': {
            name: 'Difusor Onyx',
            category: 'Difusores',
            price: '$5.400',
            desc: 'Difusor premium con textura simil piedra onyx. Acabado oscuro elegante que combina con cualquier decoracion. Incluye set de 6 varillas de bambu.',
            material: 'PLA texturado',
            dimensions: '8 x 8 x 16 cm',
            images: [
                'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=800&h=800&fit=crop',
                'https://images.unsplash.com/photo-1603204077167-2fa0397f49de?w=800&h=800&fit=crop',
            ],
        },
    };

    // Get product ID from URL
    const params = new URLSearchParams(window.location.search);
    const productId = params.get('id');

    if (!productId || !products[productId]) {
        window.location.href = 'tienda.html';
        return;
    }

    const product = products[productId];

    // Populate page
    document.title = product.name + ' — BTLDECO';
    document.getElementById('breadcrumbName').textContent = product.name;
    document.getElementById('productoName').textContent = product.name;
    document.getElementById('productoCategory').textContent = product.category;
    document.getElementById('productoPrice').textContent = product.price;
    document.getElementById('productoDesc').textContent = product.desc;
    document.getElementById('productoMaterial').textContent = product.material;
    document.getElementById('productoDimensions').textContent = product.dimensions;

    // Main image
    const mainImg = document.getElementById('productoMainImg');
    mainImg.src = product.images[0];
    mainImg.alt = product.name;

    // Thumbnails
    const thumbsContainer = document.getElementById('productoThumbs');
    product.images.forEach((src, i) => {
        const thumb = document.createElement('button');
        thumb.className = 'producto__thumb' + (i === 0 ? ' active' : '');
        thumb.innerHTML = '<img src="' + src.replace('w=800&h=800', 'w=200&h=200') + '" alt="' + product.name + ' vista ' + (i + 1) + '">';
        thumb.addEventListener('click', () => {
            mainImg.src = src;
            thumbsContainer.querySelectorAll('.producto__thumb').forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
        });
        thumbsContainer.appendChild(thumb);
    });

    // WhatsApp link with product name
    const waBtn = document.getElementById('productoWaBtn');
    const waText = encodeURIComponent('Hola! Me interesa el producto: ' + product.name + ' (' + product.price + ')');
    waBtn.href = 'https://wa.me/5491162743425?text=' + waText;

    // Related products (same category, excluding current)
    const relatedContainer = document.getElementById('productoRelated');
    const relatedIds = Object.keys(products).filter(id => {
        return id !== productId && products[id].category === product.category;
    });

    // If not enough from same category, add from others
    if (relatedIds.length < 4) {
        const otherIds = Object.keys(products).filter(id => {
            return id !== productId && !relatedIds.includes(id);
        });
        while (relatedIds.length < 4 && otherIds.length > 0) {
            relatedIds.push(otherIds.shift());
        }
    }

    relatedIds.slice(0, 4).forEach(id => {
        const p = products[id];
        const card = document.createElement('a');
        card.href = 'producto.html?id=' + id;
        card.className = 'tienda-card';
        card.innerHTML = `
            <div class="tienda-card__image">
                <img src="${p.images[0].replace('w=800&h=800', 'w=600&h=600')}" alt="${p.name}" loading="lazy">
            </div>
            <div class="tienda-card__info">
                <h3 class="tienda-card__name">${p.name}</h3>
                <span class="tienda-card__price">${p.price}</span>
            </div>
        `;
        relatedContainer.appendChild(card);
    });

})();
