<?php
/**
 * Agrega campo nombre_en a categorias + rellena traducciones
 * Ejecutar UNA vez y borrar.
 */
require_once __DIR__ . '/config.php';
$db = pdo();

// Add column
try {
    $db->exec("ALTER TABLE categorias ADD COLUMN nombre_en VARCHAR(100) DEFAULT NULL");
    echo "✅ Columna nombre_en agregada<br>";
} catch (Exception $e) {
    echo "⏭ Columna ya existe<br>";
}

// Fill translations
$traducciones = [
    'Floreros' => 'Vases',
    'Aromatización' => 'Aromatherapy',
    'Cocina' => 'Kitchen',
    'Baño' => 'Bathroom',
    'Decoración' => 'Decor',
    'Living - Comedor' => 'Living & Dining',
];

foreach ($traducciones as $es => $en) {
    $stmt = $db->prepare("UPDATE categorias SET nombre_en = ? WHERE nombre = ?");
    $stmt->execute([$en, $es]);
    echo "✅ {$es} → {$en}<br>";
}

echo "<br><strong>⚠️ BORRAR este archivo despues de usar</strong>";
