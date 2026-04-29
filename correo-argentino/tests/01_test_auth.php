<?php
/**
 * Test 01 — Validar /paqar/v1/auth.
 *
 * Demuestra:
 *   1) Una API key inválida devuelve HTTP 401 con un JSON de error.
 *   2) Si .env tiene credenciales reales (CORREO_ARGENTINO_API_KEY +
 *      CORREO_ARGENTINO_AGREEMENT cargadas), la auth devuelve 204.
 *
 * Uso:
 *     php correo-argentino/tests/01_test_auth.php
 *
 * Hits ambiente TEST por default (https://apitest.correoargentino.com.ar).
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../CorreoArgentinoClient.php';

// ──────────────────────────────────────────────────────────────────────────
// Helpers de presentación
// ──────────────────────────────────────────────────────────────────────────

function section(string $title): void {
    echo "\n" . str_repeat('=', 72) . "\n";
    echo "  {$title}\n";
    echo str_repeat('=', 72) . "\n";
}

function dump_request(string $method, string $url, array $headersMasked, ?string $body = null): void {
    echo "REQUEST\n";
    echo "  {$method} {$url}\n";
    foreach ($headersMasked as $h) echo "  {$h}\n";
    if ($body !== null) echo "  body: {$body}\n";
}

function dump_response(array $r): void {
    echo "RESPONSE\n";
    echo "  HTTP {$r['status']}    duration={$r['duration_ms']}ms    attempts={$r['attempts']}    request_id={$r['request_id']}\n";
    if ($r['error'] !== null) {
        echo "  ERROR: {$r['error']}\n";
        return;
    }
    if ($r['json'] !== null) {
        echo "  json:\n" . preg_replace('/^/m', '    ', json_encode($r['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "\n";
    } elseif ($r['body'] !== '') {
        echo "  body (raw): " . mb_substr($r['body'], 0, 500) . "\n";
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

// ──────────────────────────────────────────────────────────────────────────
// 1) Test con API KEY FALSA — debe responder 401
// ──────────────────────────────────────────────────────────────────────────

section('1) Auth con API key inválida (esperamos HTTP 401)');

$fakeKey       = 'FAKE_KEY_TEST_PURPOSES_ONLY_' . bin2hex(random_bytes(8));
$fakeAgreement = '99999';

dump_request('GET', CORREO_BASE_URL . '/paqar/v1/auth', [
    'Authorization: Apikey ' . mask_key($fakeKey),
    'agreement: ' . $fakeAgreement,
]);

$client = new CorreoArgentinoClient($fakeKey, $fakeAgreement);
$res    = $client->auth();

dump_response($res);

$test1Pass = false;
if ($res['status'] === 401) {
    echo "\n✓ PASS — el server respondió 401 ante credencial inválida.\n";
    $test1Pass = true;
} elseif ($res['status'] === 0) {
    echo "\n✗ FAIL — no hubo respuesta HTTP. {$res['error']}\n";
    echo "  Verificá conectividad a " . CORREO_BASE_URL . " desde esta máquina.\n";
} else {
    echo "\n✗ FAIL — esperábamos 401, recibimos HTTP {$res['status']}.\n";
}

// ──────────────────────────────────────────────────────────────────────────
// 2) Test con CREDENCIALES REALES — solo si están cargadas en .env
// ──────────────────────────────────────────────────────────────────────────

section('2) Auth con credenciales reales del .env (esperamos HTTP 204)');

if (CORREO_API_KEY === '' || CORREO_AGREEMENT === '') {
    echo "(skip) CORREO_ARGENTINO_API_KEY o CORREO_ARGENTINO_AGREEMENT vacíos en .env.\n";
    echo "       Cargá tus credenciales reales y volvé a correr este test.\n";
    echo "       Esperado: HTTP 204 No Content cuando estén bien.\n";
    $test2Pass = null; // skipped
} else {
    dump_request('GET', CORREO_BASE_URL . '/paqar/v1/auth', [
        'Authorization: Apikey ' . mask_key(CORREO_API_KEY),
        'agreement: ' . CORREO_AGREEMENT,
    ]);

    $client2 = new CorreoArgentinoClient(); // toma del .env
    $res2    = $client2->auth();

    dump_response($res2);

    $test2Pass = false;
    if ($res2['status'] === 204) {
        echo "\n✓ PASS — credenciales válidas, server respondió 204 No Content.\n";
        $test2Pass = true;
    } elseif ($res2['status'] === 401) {
        echo "\n✗ FAIL — 401 con las credenciales del .env.\n";
        echo "  Revisá CORREO_ARGENTINO_API_KEY y CORREO_ARGENTINO_AGREEMENT.\n";
    } elseif ($res2['status'] === 0) {
        echo "\n✗ FAIL — sin respuesta HTTP. {$res2['error']}\n";
    } else {
        echo "\n✗ FAIL — esperábamos 204, recibimos HTTP {$res2['status']}.\n";
    }
}

// ──────────────────────────────────────────────────────────────────────────
// Resumen
// ──────────────────────────────────────────────────────────────────────────

section('Resumen');
echo "  test 1 (fake key → 401)      : " . ($test1Pass ? 'PASS' : 'FAIL') . "\n";
echo "  test 2 (creds reales → 204)  : " . ($test2Pass === null ? 'SKIPPED' : ($test2Pass ? 'PASS' : 'FAIL')) . "\n";
echo "  log file                      : " . CORREO_LOG_DIR . '/' . date('Y-m') . ".log\n";

exit($test1Pass && ($test2Pass ?? true) ? 0 : 1);
