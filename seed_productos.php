<?php
/**
 * SEED: Crea categorias y productos de muestra
 * Ejecutar UNA sola vez: https://btldeco.com.ar/seed_productos.php
 * BORRAR despues de usar.
 */
require_once __DIR__ . '/config.php';

$db = pdo();
$results = [];

// ============================================================
// CATEGORIAS
// ============================================================
$categorias = [
    ['nombre' => 'Floreros',           'slug' => 'floreros',          'descripcion' => 'Floreros de diseño para flores frescas y secas', 'orden' => 1, 'imagen' => 'https://images.unsplash.com/photo-1578500494198-246f612d3b3d?w=600&h=400&fit=crop'],
    ['nombre' => 'Aromatización',      'slug' => 'aromatizacion',     'descripcion' => 'Difusores, portavelas y aromas para tu hogar', 'orden' => 2, 'imagen' => 'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=600&h=400&fit=crop'],
    ['nombre' => 'Cocina',             'slug' => 'cocina',            'descripcion' => 'Organizadores y accesorios para tu cocina', 'orden' => 3, 'imagen' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=600&h=400&fit=crop'],
    ['nombre' => 'Baño',               'slug' => 'bano',              'descripcion' => 'Accesorios decorativos para el baño', 'orden' => 4, 'imagen' => 'https://images.unsplash.com/photo-1507473885765-e6ed057ab6fe?w=600&h=400&fit=crop'],
    ['nombre' => 'Decoración',         'slug' => 'decoracion',        'descripcion' => 'Figuras, esculturas y piezas decorativas', 'orden' => 5, 'imagen' => 'https://images.unsplash.com/photo-1544967082-d9d25d867d66?w=600&h=400&fit=crop'],
    ['nombre' => 'Living - Comedor',   'slug' => 'living-comedor',    'descripcion' => 'Piezas para living, comedor y espacios compartidos', 'orden' => 6, 'imagen' => 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?w=600&h=400&fit=crop'],
];

$catIds = [];
foreach ($categorias as $cat) {
    try {
        $stmt = $db->prepare("INSERT INTO categorias (nombre, slug, descripcion, orden, activa, imagen) VALUES (?, ?, ?, ?, 1, ?)
                              ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), imagen = VALUES(imagen)");
        $stmt->execute([$cat['nombre'], $cat['slug'], $cat['descripcion'], $cat['orden'], $cat['imagen'] ?? null]);
        $catIds[$cat['slug']] = $db->lastInsertId() ?: $db->query("SELECT id FROM categorias WHERE slug = '{$cat['slug']}'")->fetchColumn();
        $results[] = "✅ Categoria: {$cat['nombre']} (ID: {$catIds[$cat['slug']]})";
    } catch (Exception $e) {
        $results[] = "❌ Categoria {$cat['nombre']}: " . $e->getMessage();
    }
}

// ============================================================
// PRODUCTOS (2 por categoria, 1 destacado cada una)
// ============================================================
$productos = [
    // FLOREROS
    [
        'categoria' => 'floreros', 'nombre' => 'Florero Spiral', 'slug' => 'florero-spiral',
        'descripcion' => 'Florero con diseño espiral ascendente. Pieza escultorica que luce espectacular con o sin flores. Diseño parametrico con textura de capas que le da profundidad visual. Fabricado en PLA premium con acabado mate.',
        'descripcion_corta' => 'Florero espiral con diseño parametrico, ideal para flores frescas y secas.',
        'precio' => 5800, 'stock' => 15, 'destacado' => 1,
        'imagen' => 'https://images.unsplash.com/photo-1578500494198-246f612d3b3d?w=800&h=800&fit=crop',
    ],
    [
        'categoria' => 'floreros', 'nombre' => 'Florero Vertex', 'slug' => 'florero-vertex',
        'descripcion' => 'Florero con geometria facetada tipo low-poly. Cada angulo crea un juego de luces y sombras unico. Diseño moderno para flores frescas o secas. Incluye tratamiento interior impermeable.',
        'descripcion_corta' => 'Florero facetado con geometria low-poly y tratamiento impermeable.',
        'precio' => 6500, 'stock' => 12, 'destacado' => 0,
        'imagen' => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800&h=800&fit=crop',
    ],

    // AROMATIZACIÓN
    [
        'categoria' => 'aromatizacion', 'nombre' => 'Difusor Minimal', 'slug' => 'difusor-minimal',
        'descripcion' => 'Difusor de aromas con diseño minimalista. Forma cilindrica con aperturas geometricas para la difusion del aroma. Compatible con varillas de bambu estandar. Incluye set de 6 varillas.',
        'descripcion_corta' => 'Difusor minimalista con aperturas geometricas, incluye varillas de bambu.',
        'precio' => 4100, 'stock' => 20, 'destacado' => 1,
        'imagen' => 'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=800&h=800&fit=crop',
    ],
    [
        'categoria' => 'aromatizacion', 'nombre' => 'Portavela Moon', 'slug' => 'portavela-moon',
        'descripcion' => 'Portavelas con forma de luna creciente. Crea una atmosfera calida y acogedora con la luz de vela filtrando a traves de su diseño. Compatible con velas tipo tealight. Textura suave con acabado mate.',
        'descripcion_corta' => 'Portavela con forma de luna para velas tealight, acabado mate.',
        'precio' => 2900, 'stock' => 25, 'destacado' => 0,
        'imagen' => 'https://images.unsplash.com/photo-1603204077167-2fa0397f49de?w=800&h=800&fit=crop',
    ],

    // COCINA
    [
        'categoria' => 'cocina', 'nombre' => 'Organizador Especias', 'slug' => 'organizador-especias',
        'descripcion' => 'Organizador modular para especias y condimentos. Diseño escalonado que permite ver todos los frascos. Fabricado en PLA resistente con base antideslizante. Capacidad para 8 frascos estandar.',
        'descripcion_corta' => 'Organizador escalonado para 8 frascos de especias, base antideslizante.',
        'precio' => 4500, 'stock' => 18, 'destacado' => 1,
        'imagen' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800&h=800&fit=crop',
    ],
    [
        'categoria' => 'cocina', 'nombre' => 'Porta Utensilios Hexa', 'slug' => 'porta-utensilios-hexa',
        'descripcion' => 'Porta utensilios con forma hexagonal para la mesada de cocina. Compartimento amplio para cucharas, espatulas y pinzas. Diseño geometrico que aporta estilo a tu cocina.',
        'descripcion_corta' => 'Porta utensilios hexagonal para mesada, diseño geometrico.',
        'precio' => 3800, 'stock' => 22, 'destacado' => 0,
        'imagen' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&h=800&fit=crop',
    ],

    // BAÑO
    [
        'categoria' => 'bano', 'nombre' => 'Set Baño Minimal', 'slug' => 'set-bano-minimal',
        'descripcion' => 'Set de 3 piezas para baño: porta cepillos, jabonera y vaso. Diseño minimalista con lineas puras y formas cilindricas. Acabado mate resistente a la humedad. Colores disponibles: blanco, gris y negro.',
        'descripcion_corta' => 'Set de 3 piezas para baño: porta cepillos, jabonera y vaso.',
        'precio' => 6200, 'stock' => 10, 'destacado' => 1,
        'imagen' => 'https://images.unsplash.com/photo-1507473885765-e6ed057ab6fe?w=800&h=800&fit=crop',
    ],
    [
        'categoria' => 'bano', 'nombre' => 'Jabonera Zen', 'slug' => 'jabonera-zen',
        'descripcion' => 'Jabonera con diseño zen inspirado en piedras de rio. Base con drenaje para mantener el jabon seco. Textura suave y organica que aporta calidez al espacio.',
        'descripcion_corta' => 'Jabonera con forma organica y base con drenaje.',
        'precio' => 2200, 'stock' => 30, 'destacado' => 0,
        'imagen' => 'https://images.unsplash.com/photo-1493552832785-8ae4e09e480f?w=800&h=800&fit=crop',
    ],

    // DECORACIÓN
    [
        'categoria' => 'decoracion', 'nombre' => 'Escultura Twist', 'slug' => 'escultura-twist',
        'descripcion' => 'Figura escultural con torsion helicoidal. Diseño parametrico que juega con la luz y las sombras. Pieza de conversacion para cualquier espacio. Acabado premium en color a eleccion. Altura 24cm.',
        'descripcion_corta' => 'Escultura helicoidal parametrica, pieza de conversacion unica.',
        'precio' => 5500, 'stock' => 14, 'destacado' => 1,
        'imagen' => 'https://images.unsplash.com/photo-1544967082-d9d25d867d66?w=800&h=800&fit=crop',
    ],
    [
        'categoria' => 'decoracion', 'nombre' => 'Figura Ondas', 'slug' => 'figura-ondas',
        'descripcion' => 'Escultura decorativa con formas ondulantes organicas. Arte moderno para repisas, mesas y bibliotecas. Acabado suave al tacto con detalles de capas visibles que le dan caracter unico.',
        'descripcion_corta' => 'Escultura con ondas organicas para repisas y mesas.',
        'precio' => 3800, 'stock' => 16, 'destacado' => 0,
        'imagen' => 'https://images.unsplash.com/photo-1602028915047-37269d1a73f7?w=800&h=800&fit=crop',
    ],

    // LIVING - COMEDOR
    [
        'categoria' => 'living-comedor', 'nombre' => 'Bandeja Cloud', 'slug' => 'bandeja-cloud',
        'descripcion' => 'Bandeja decorativa con forma de nube. Ideal como centro de mesa, para llaves, joyas o como pieza decorativa independiente. Diseño suave y organico que aporta un toque ludico y moderno.',
        'descripcion_corta' => 'Bandeja decorativa con forma de nube, ideal como centro de mesa.',
        'precio' => 4800, 'stock' => 17, 'destacado' => 1,
        'imagen' => 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?w=800&h=800&fit=crop',
    ],
    [
        'categoria' => 'living-comedor', 'nombre' => 'Centro de Mesa Geo', 'slug' => 'centro-mesa-geo',
        'descripcion' => 'Centro de mesa con diseño geometrico facetado. Pieza funcional y decorativa que combina con cualquier estilo de comedor. Capacidad para frutas, velas o como pieza standalone.',
        'descripcion_corta' => 'Centro de mesa geometrico facetado, funcional y decorativo.',
        'precio' => 5200, 'stock' => 13, 'destacado' => 0,
        'imagen' => 'https://images.unsplash.com/photo-1513519245088-0e12902e35ca?w=800&h=800&fit=crop',
    ],
];

foreach ($productos as $p) {
    try {
        $catId = $catIds[$p['categoria']] ?? null;
        if (!$catId) { $results[] = "⚠️ Categoria no encontrada: {$p['categoria']}"; continue; }

        $stmt = $db->prepare("INSERT INTO productos (categoria_id, nombre, slug, descripcion, descripcion_corta, precio, stock, imagen_principal, estado, destacado, fecha_creacion, fecha_modificacion)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'activo', ?, NOW(), NOW())
                              ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)");
        $stmt->execute([$catId, $p['nombre'], $p['slug'], $p['descripcion'], $p['descripcion_corta'], $p['precio'], $p['stock'], $p['imagen'], $p['destacado']]);
        $pid = $db->lastInsertId() ?: $db->query("SELECT id FROM productos WHERE slug = '{$p['slug']}'")->fetchColumn();
        $star = $p['destacado'] ? ' ⭐' : '';
        $results[] = "✅ Producto: {$p['nombre']} — \${$p['precio']}{$star}";
    } catch (Exception $e) {
        $results[] = "❌ Producto {$p['nombre']}: " . $e->getMessage();
    }
}

// ============================================================
// OUTPUT
// ============================================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seed — BTLDECO</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:-apple-system,BlinkMacSystemFont,sans-serif;background:#0a0a0f;color:#e4e4e7;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
  .card{background:#12121a;border:1px solid #27272a;border-radius:12px;padding:40px;max-width:600px;width:100%}
  h1{font-size:1.4rem;margin-bottom:24px;text-align:center}
  .item{padding:8px 12px;border-radius:6px;margin-bottom:6px;font-size:0.9rem}
  .summary{margin-top:20px;text-align:center;padding:16px;border-radius:8px;background:rgba(34,197,94,0.1);border:1px solid #22c55e;color:#4ade80}
  .warning{margin-top:16px;color:#f87171;font-size:0.85rem;text-align:center;font-weight:600}
</style>
</head>
<body>
<div class="card">
  <h1>Seed Productos — BTLDECO</h1>
  <?php foreach ($results as $r): ?>
    <div class="item"><?= $r ?></div>
  <?php endforeach; ?>
  <div class="summary">
    <?= count($categorias) ?> categorias + <?= count($productos) ?> productos creados<br>
    ⭐ = Producto destacado (aparece en Home)
  </div>
  <div class="warning">⚠️ BORRAR este archivo despues de usar</div>
</div>
</body>
</html>
