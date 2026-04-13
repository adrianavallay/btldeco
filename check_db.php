<?php
require_once __DIR__ . '/config.php';
header('Content-Type: text/plain; charset=utf-8');

echo "=== PRODUCTOS ===\n";
$prods = pdo()->query("SELECT id, nombre, imagen_principal, destacado FROM productos ORDER BY id")->fetchAll();
foreach ($prods as $p) {
    $img = $p['imagen_principal'] ?: '(VACIO)';
    $star = $p['destacado'] ? ' *DESTACADO*' : '';
    echo "ID:{$p['id']} | {$p['nombre']} | img: {$img}{$star}\n";
    echo "  -> img_url(): " . img_url($p['imagen_principal']) . "\n\n";
}

echo "\n=== CATEGORIAS ===\n";
$cats = pdo()->query("SELECT id, nombre, imagen FROM categorias ORDER BY id")->fetchAll();
foreach ($cats as $c) {
    $img = $c['imagen'] ?: '(VACIO)';
    echo "ID:{$c['id']} | {$c['nombre']} | img: {$img}\n";
}

echo "\n=== SITE_URL: " . SITE_URL . " ===\n";
