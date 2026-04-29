<?php
/**
 * Test 02 — Listar sucursales /paqar/v1/agencies.
 *
 * Demuestra:
 *   1) Una API key inválida devuelve 4xx (típicamente 403).
 *   2) Los filtros stateId / pickup_availability / package_reception se
 *      arman correctamente en query string (incluyendo el detalle de que
 *      los booleans van como literales "true"/"false" en lugar de 1/0).
 *   3) ProvinciaMapper se usa correctamente en la capa de prueba para
 *      traducir nombres de provincia → código de 1 letra.
 *   4) Si .env tiene credenciales reales, lista las primeras N sucursales
 *      y muestra estadísticas básicas.
 *
 * Uso:
 *     php correo-argentino/tests/02_test_agencies.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../CorreoArgentinoClient.php';
require_once __DIR__ . '/../helpers/ProvinciaMapper.php';

// ── Helpers ───────────────────────────────────────────────────────────────

function section(string $title): void {
    echo "\n" . str_repeat('=', 72) . "\n";
    echo "  {$title}\n";
    echo str_repeat('=', 72) . "\n";
}

function dump_request(string $method, string $url, array $headers): void {
    echo "REQUEST\n";
    echo "  {$method} {$url}\n";
    foreach ($headers as $h) echo "  {$h}\n";
}

function dump_response(array $r, int $bodyPreviewMax = 400): void {
    echo "RESPONSE\n";
    echo "  HTTP {$r['status']}    duration={$r['duration_ms']}ms    attempts={$r['attempts']}    request_id={$r['request_id']}\n";
    if ($r['error'] !== null) {
        echo "  ERROR: {$r['error']}\n";
        return;
    }
    if ($r['json'] !== null) {
        $size = is_array($r['json']) ? count($r['json']) : 'n/a';
        echo "  json: array de {$size} elementos\n";
        $preview = json_encode($r['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo preg_replace('/^/m', '    ', mb_substr($preview, 0, $bodyPreviewMax));
        echo "\n    ... (recortado)\n";
    } elseif ($r['body'] !== '') {
        echo "  body (raw): " . mb_substr($r['body'], 0, $bodyPreviewMax) . "\n";
    } else {
        echo "  body: (vacío)\n";
    }
}

function mask_key(string $key): string {
    if ($key === '') return '(empty)';
    $len = strlen($key);
    if ($len <= 8) return str_repeat('*', $len);
    return substr($key, 0, 4) . str_repeat('*', $len - 8) . substr($key, -4);
}

function show_agencies_summary(array $agencies, int $sample = 3): void {
    $total = count($agencies);
    echo "  Total sucursales: {$total}\n";
    if ($total === 0) return;

    // Estadísticas por provincia
    $byState = [];
    foreach ($agencies as $a) {
        $st = $a['location']['state_name'] ?? '(sin provincia)';
        $byState[$st] = ($byState[$st] ?? 0) + 1;
    }
    arsort($byState);
    echo "  Distribución por provincia:\n";
    foreach (array_slice($byState, 0, 5, true) as $st => $n) {
        echo "    - {$st}: {$n}\n";
    }
    if (count($byState) > 5) echo "    - (+ " . (count($byState) - 5) . " provincias más)\n";

    echo "  Primeras {$sample} sucursales:\n";
    foreach (array_slice($agencies, 0, $sample) as $a) {
        $loc = $a['location'] ?? [];
        echo sprintf(
            "    [%-8s] %s — %s, %s (%s) — pickup:%s recepción:%s\n",
            $a['agency_id'] ?? '?',
            $a['agency_name'] ?? '?',
            $loc['street_name'] ?? '?',
            $loc['city_name'] ?? '?',
            $loc['state_name'] ?? '?',
            !empty($a['pickup_availability']) ? 'sí' : 'no',
            !empty($a['package_reception'])   ? 'sí' : 'no'
        );
    }
}

// ──────────────────────────────────────────────────────────────────────────
// 1) Fake key → esperamos 4xx (403 según el PDF)
// ──────────────────────────────────────────────────────────────────────────

section('1) GET /agencies con API key inválida (esperamos 4xx)');

$fakeKey       = 'FAKE_KEY_TEST_PURPOSES_ONLY_' . bin2hex(random_bytes(8));
$fakeAgreement = '99999';

$urlExpected = CORREO_BASE_URL . '/paqar/v1/agencies';
dump_request('GET', $urlExpected, [
    'Authorization: Apikey ' . mask_key($fakeKey),
    'agreement: ' . $fakeAgreement,
]);

$client = new CorreoArgentinoClient($fakeKey, $fakeAgreement);
$res    = $client->getAgencies();
dump_response($res);

$test1Pass = false;
if ($res['status'] >= 400 && $res['status'] < 500) {
    echo "\n✓ PASS — credencial inválida rechazada (HTTP {$res['status']}).\n";
    $test1Pass = true;
} elseif ($res['status'] === 0) {
    echo "\n✗ FAIL — error de red: {$res['error']}\n";
} else {
    echo "\n✗ FAIL — esperábamos 4xx, recibimos HTTP {$res['status']}.\n";
}

// ──────────────────────────────────────────────────────────────────────────
// 2) Verificar query string con filtros (con fake key, igual da 4xx, pero
//    podemos inspeccionar la URL final que armó el cliente)
// ──────────────────────────────────────────────────────────────────────────

section('2) GET /agencies con filtros (stateId=B, pickup=true, package=false)');

$cabaCode = ProvinciaMapper::nombreToCodigo('Buenos Aires'); // → "B"
echo "  ProvinciaMapper::nombreToCodigo('Buenos Aires') = '{$cabaCode}'\n\n";

$res2 = $client->getAgencies($cabaCode, true, false);
echo "URL final (la que armó el cliente):\n  {$res2['url']}\n\n";

dump_response($res2);

$test2Pass = false;
$expectedQuery = 'stateId=B&pickup_availability=true&package_reception=false';
if (str_contains($res2['url'], $expectedQuery)) {
    echo "\n✓ PASS — query string contiene exactamente '{$expectedQuery}'.\n";
    $test2Pass = true;
} else {
    echo "\n✗ FAIL — query string esperado '{$expectedQuery}' no aparece en la URL.\n";
}

// ──────────────────────────────────────────────────────────────────────────
// 3) Real creds (si están cargadas) → esperamos 200 + array no vacío
// ──────────────────────────────────────────────────────────────────────────

section('3) GET /agencies con credenciales reales del .env (esperamos 200)');

if (CORREO_API_KEY === '' || CORREO_AGREEMENT === '') {
    echo "(skip) CORREO_ARGENTINO_API_KEY o AGREEMENT vacíos en .env.\n";
    echo "       Cargá tus credenciales y volvé a correr para ver agencias reales.\n";
    $test3Pass = null;
} else {
    $real    = new CorreoArgentinoClient();
    $resReal = $real->getAgencies(); // sin filtros
    dump_response($resReal, 200);
    if ($resReal['status'] === 200 && is_array($resReal['json'])) {
        echo "\n";
        show_agencies_summary($resReal['json'], 5);

        // Probar también con filtro por provincia
        echo "\n  Filtrando por stateId='C' (CABA), pickup_availability=true:\n";
        $resCABA = $real->getAgencies('C', true, null);
        if ($resCABA['status'] === 200 && is_array($resCABA['json'])) {
            show_agencies_summary($resCABA['json'], 3);
            $test3Pass = true;
        } else {
            echo "  filtro por CABA devolvió HTTP {$resCABA['status']}\n";
            $test3Pass = false;
        }
    } else {
        echo "\n✗ FAIL — esperábamos HTTP 200 + array, recibimos HTTP {$resReal['status']}.\n";
        $test3Pass = false;
    }
}

// ──────────────────────────────────────────────────────────────────────────
// Resumen
// ──────────────────────────────────────────────────────────────────────────

section('Resumen');
echo "  test 1 (fake key → 4xx)              : " . ($test1Pass ? 'PASS' : 'FAIL') . "\n";
echo "  test 2 (query string con filtros)    : " . ($test2Pass ? 'PASS' : 'FAIL') . "\n";
echo "  test 3 (creds reales → 200 + lista)  : " . ($test3Pass === null ? 'SKIPPED' : ($test3Pass ? 'PASS' : 'FAIL')) . "\n";
echo "  log file                              : " . CORREO_LOG_DIR . '/' . date('Y-m') . ".log\n";

exit($test1Pass && $test2Pass && ($test3Pass ?? true) ? 0 : 1);
