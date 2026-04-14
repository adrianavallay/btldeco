<?php
/**
 * API de Codigo Postal — Devuelve provincia y localidades sugeridas
 * Combina datos locales + Zippopotam API como fallback
 * GET ?cp=1414
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$cp = trim($_GET['cp'] ?? '');
if (strlen($cp) < 4) {
    echo json_encode(['ok' => false, 'provincia' => '', 'localidades' => []]);
    exit;
}

$cpNum = (int) $cp;

// Mapeo de rangos de CP a provincias
$rangos = [
    // CABA: 1000-1099, 1400-1499
    [1000, 1099, 'CABA'],
    [1400, 1499, 'CABA'],
    // Buenos Aires (GBA y provincia)
    [1600, 1699, 'Buenos Aires'],
    [1700, 1799, 'Buenos Aires'],
    [1800, 1899, 'Buenos Aires'],
    [1900, 1999, 'Buenos Aires'],
    [2700, 2799, 'Buenos Aires'],
    [2800, 2899, 'Buenos Aires'],
    [2900, 2999, 'Buenos Aires'],
    [6000, 6099, 'Buenos Aires'],
    [6400, 6499, 'Buenos Aires'],
    [6500, 6599, 'Buenos Aires'],
    [6600, 6699, 'Buenos Aires'],
    [6700, 6799, 'Buenos Aires'],
    [7000, 7099, 'Buenos Aires'],
    [7400, 7499, 'Buenos Aires'],
    [7500, 7599, 'Buenos Aires'],
    [7600, 7699, 'Buenos Aires'],
    [8000, 8099, 'Buenos Aires'],
    // Santa Fe
    [2000, 2099, 'Santa Fe'],
    [2100, 2199, 'Santa Fe'],
    [2200, 2299, 'Santa Fe'],
    [2300, 2399, 'Santa Fe'],
    [2600, 2699, 'Santa Fe'],
    [3000, 3099, 'Santa Fe'],
    // Cordoba
    [2400, 2499, 'Cordoba'],
    [5000, 5199, 'Cordoba'],
    [5200, 5299, 'Cordoba'],
    [5800, 5899, 'Cordoba'],
    // Entre Rios
    [3100, 3199, 'Entre Rios'],
    [3200, 3299, 'Entre Rios'],
    // Corrientes
    [3400, 3499, 'Corrientes'],
    // Chaco
    [3500, 3599, 'Chaco'],
    // Formosa
    [3600, 3699, 'Formosa'],
    // Misiones
    [3300, 3399, 'Misiones'],
    // Tucuman
    [4000, 4199, 'Tucuman'],
    // Santiago del Estero
    [4200, 4299, 'Santiago del Estero'],
    // Salta
    [4300, 4399, 'Salta'],
    // Jujuy
    [4400, 4599, 'Jujuy'],
    // Catamarca
    [4700, 4799, 'Catamarca'],
    // La Rioja
    [5300, 5399, 'La Rioja'],
    // San Juan
    [5400, 5499, 'San Juan'],
    // Mendoza
    [5500, 5699, 'Mendoza'],
    // San Luis
    [5700, 5799, 'San Luis'],
    // La Pampa
    [6300, 6399, 'La Pampa'],
    // Neuquen
    [8300, 8399, 'Neuquen'],
    // Rio Negro
    [8400, 8599, 'Rio Negro'],
    // Chubut
    [9000, 9299, 'Chubut'],
    // Santa Cruz
    [9400, 9499, 'Santa Cruz'],
    // Tierra del Fuego
    [9410, 9499, 'Tierra del Fuego'],
];

$provincia = '';
foreach ($rangos as $r) {
    if ($cpNum >= $r[0] && $cpNum <= $r[1]) {
        $provincia = $r[2];
        break;
    }
}

// Intentar Zippopotam para localidades
$localidades = [];
$zUrl = "https://api.zippopotam.us/AR/" . urlencode($cp);
$ctx = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true]]);
$response = @file_get_contents($zUrl, false, $ctx);

if ($response) {
    $data = json_decode($response, true);
    if (!empty($data['places'])) {
        foreach ($data['places'] as $place) {
            $localidades[] = $place['place name'];
        }
        // Si Zippopotam devolvio provincia, usarla
        if (!$provincia && !empty($data['places'][0]['state'])) {
            $provincia = ucwords(strtolower($data['places'][0]['state']));
        }
    }
}

echo json_encode([
    'ok' => $provincia !== '',
    'provincia' => $provincia,
    'localidades' => $localidades,
]);
