<?php
/**
 * Fix: reemplaza imagenes rotas con URLs que funcionan
 * Ejecutar UNA vez y borrar.
 */
require_once __DIR__ . '/config.php';
$db = pdo();

$fixes = [
    'portavela-moon'    => 'https://images.unsplash.com/photo-1572726729207-a78d6feb18d7?w=800&h=800&fit=crop',
    'set-bano-minimal'  => 'https://images.unsplash.com/photo-1620626011761-996317b8d101?w=800&h=800&fit=crop',
    'jabonera-zen'      => 'https://images.unsplash.com/photo-1585412727339-54e4bae3bbf9?w=800&h=800&fit=crop',
    'centro-mesa-geo'   => 'https://images.unsplash.com/photo-1616046229478-9901c5536a45?w=800&h=800&fit=crop',
];

$results = [];
foreach ($fixes as $slug => $img) {
    $stmt = $db->prepare("UPDATE productos SET imagen_principal = ? WHERE slug = ?");
    $stmt->execute([$img, $slug]);
    $rows = $stmt->rowCount();
    $results[] = ($rows > 0 ? "✅" : "⚠️") . " {$slug} → actualizado ({$rows} filas)";
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Fix Imagenes</title>
<style>body{font-family:sans-serif;background:#0a0a0f;color:#e4e4e7;padding:40px;}.item{padding:8px;margin:4px 0;}</style>
</head><body>
<h2>Fix Imagenes — BTLDECO</h2>
<?php foreach ($results as $r): ?><div class="item"><?= $r ?></div><?php endforeach; ?>
<p style="color:#f87171;margin-top:20px;">⚠️ BORRAR este archivo despues de usar</p>
</body></html>
