<?php
/**
 * Migra clientes desde pedidos existentes a la tabla clientes.
 * No duplica: usa email como campo de cotejo.
 * Ejecutar UNA vez y borrar.
 */
require_once __DIR__ . '/config.php';
$db = pdo();

$pedidos = $db->query("SELECT DISTINCT email, nombre, telefono, direccion, ciudad, provincia, MAX(fecha) as ultima_compra FROM pedidos GROUP BY email ORDER BY ultima_compra DESC")->fetchAll();

$creados = 0;
$existentes = 0;
$results = [];

foreach ($pedidos as $p) {
    if (empty($p['email'])) continue;

    $existe = $db->prepare("SELECT id FROM clientes WHERE email = ?");
    $existe->execute([$p['email']]);

    if ($existe->fetch()) {
        $existentes++;
        $results[] = "⏭ Ya existe: " . $p['email'];
    } else {
        $db->prepare("INSERT INTO clientes (nombre, email, password, telefono, direccion, ciudad, provincia, activo, fecha_registro, ultimo_acceso) VALUES (?, ?, '', ?, ?, ?, ?, 1, ?, ?)")
           ->execute([$p['nombre'], $p['email'], $p['telefono'] ?? '', $p['direccion'] ?? '', $p['ciudad'] ?? '', $p['provincia'] ?? '', $p['ultima_compra'], $p['ultima_compra']]);
        $creados++;
        $results[] = "✅ Creado: " . $p['nombre'] . " (" . $p['email'] . ")";
    }
}

// Update cliente_id en pedidos que no lo tienen
$clientes = $db->query("SELECT id, email FROM clientes")->fetchAll();
$vinculados = 0;
foreach ($clientes as $c) {
    $upd = $db->prepare("UPDATE pedidos SET cliente_id = ? WHERE email = ? AND (cliente_id IS NULL OR cliente_id = 0)");
    $upd->execute([$c['id'], $c['email']]);
    $vinculados += $upd->rowCount();
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Migrar Clientes</title>
<style>body{font-family:sans-serif;background:#0a0a0f;color:#e4e4e7;padding:40px;max-width:600px;margin:0 auto;}.item{padding:6px 0;font-size:.9rem;}.summary{margin-top:20px;padding:16px;background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);border-radius:8px;color:#4ade80;}.warn{margin-top:12px;color:#f87171;font-size:.85rem;}</style>
</head><body>
<h2>Migrar Clientes desde Pedidos</h2>
<?php foreach ($results as $r): ?><div class="item"><?= $r ?></div><?php endforeach; ?>
<div class="summary">
    ✅ <?= $creados ?> clientes creados | ⏭ <?= $existentes ?> ya existian | 🔗 <?= $vinculados ?> pedidos vinculados
</div>
<p class="warn">⚠️ BORRAR este archivo despues de usar</p>
</body></html>
