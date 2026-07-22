<?php
// ============================================================
// install.php — Instalador / Migraciones de base de datos
// ------------------------------------------------------------
// Aplica los cambios pendientes de la base de datos que viajan
// por git en la carpeta /migrations. Es SEGURO correrlo varias
// veces: solo ejecuta lo que todavía no se aplicó.
//
// Uso: entrar (logueado como admin) a  https://TU-SITIO/install.php
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_helper.php';
require_admin();                       // solo el admin puede abrir esto
require_once __DIR__ . '/migrations/lib.php';

$db = pdo();

// ── Tabla de control (registra qué migraciones ya corrieron) ──
$db->exec("CREATE TABLE IF NOT EXISTS _migrations (
    id            VARCHAR(191) PRIMARY KEY,
    ejecutada_en  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── Cargar las migraciones disponibles desde /migrations/*.php ──
function cargar_migraciones(): array
{
    $migraciones = [];
    foreach (glob(__DIR__ . '/migrations/*.php') as $ruta) {
        if (basename($ruta) === 'lib.php') continue;   // helper, no es migración
        $id  = basename($ruta, '.php');
        $def = require $ruta;
        if (!is_array($def) || !isset($def['up']) || !is_callable($def['up'])) {
            continue; // archivo mal formado: lo ignoramos
        }
        $migraciones[$id] = [
            'id'          => $id,
            'descripcion' => $def['descripcion'] ?? '(sin descripción)',
            'up'          => $def['up'],
        ];
    }
    ksort($migraciones);               // orden por nombre de archivo (0001, 0002, ...)
    return $migraciones;
}

function ya_ejecutadas(PDO $db): array
{
    return $db->query("SELECT id FROM _migrations")->fetchAll(PDO::FETCH_COLUMN) ?: [];
}

$migraciones = cargar_migraciones();
$ejecutadas  = ya_ejecutadas($db);
$pendientes  = array_diff(array_keys($migraciones), $ejecutadas);

$resultados = [];   // se llena si se ejecuta el POST
$hubo_error = false;

// ── Ejecutar pendientes (POST) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Token CSRF inválido. Recargá la página.');
    }

    foreach ($pendientes as $id) {
        if ($hubo_error) {             // si una falló, no seguimos con las siguientes
            $resultados[] = ['id' => $id, 'estado' => 'omitida', 'detalle' => 'Se omitió por un error anterior'];
            continue;
        }
        try {
            ($migraciones[$id]['up'])($db);
            $stmt = $db->prepare("INSERT INTO _migrations (id) VALUES (?)");
            $stmt->execute([$id]);
            $resultados[] = ['id' => $id, 'estado' => 'ok', 'detalle' => $migraciones[$id]['descripcion']];
        } catch (Throwable $e) {
            $hubo_error = true;
            $resultados[] = ['id' => $id, 'estado' => 'error', 'detalle' => $e->getMessage()];
        }
    }

    // Refrescar estado tras ejecutar
    $ejecutadas = ya_ejecutadas($db);
    $pendientes = array_diff(array_keys($migraciones), $ejecutadas);
}

function h($s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instalador de base de datos &mdash; <?= h(SITE_NAME) ?></title>
<style>
    * { box-sizing: border-box; }
    body { margin:0; background:#f4f4f5; color:#18181b; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif; padding:40px 16px; }
    .wrap { max-width:760px; margin:0 auto; }
    h1 { font-size:1.6rem; margin:0 0 4px; }
    .sub { color:#71717a; font-size:0.9rem; margin:0 0 28px; }
    .card { background:#fff; border:1px solid #e4e4e7; border-radius:12px; padding:24px; margin-bottom:20px; }
    .badge { display:inline-block; padding:2px 10px; border-radius:999px; font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; }
    .b-ok   { background:#dcfce7; color:#166534; }
    .b-pend { background:#fef9c3; color:#854d0e; }
    .b-err  { background:#fee2e2; color:#991b1b; }
    .b-omit { background:#e4e4e7; color:#52525b; }
    ul.list { list-style:none; margin:12px 0 0; padding:0; }
    ul.list li { display:flex; align-items:center; gap:12px; padding:12px 0; border-top:1px solid #f4f4f5; font-size:0.9rem; }
    ul.list li code { background:#f4f4f5; padding:2px 6px; border-radius:5px; font-size:0.82rem; }
    .desc { color:#71717a; font-size:0.82rem; }
    button { width:100%; padding:14px; background:#111; color:#fff; border:none; border-radius:8px; font-size:0.9rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; cursor:pointer; }
    button:hover { background:#000; }
    .empty { text-align:center; color:#71717a; padding:20px 0; }
    .ok-msg { background:#dcfce7; border:1px solid #86efac; color:#166534; padding:14px 16px; border-radius:8px; font-size:0.9rem; margin-bottom:20px; }
    .err-msg { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:14px 16px; border-radius:8px; font-size:0.9rem; margin-bottom:20px; }
    a.back { color:#71717a; font-size:0.85rem; text-decoration:none; }
    a.back:hover { text-decoration:underline; }
</style>
</head>
<body>
<div class="wrap">
    <h1>Instalador de base de datos</h1>
    <p class="sub">Aplica los cambios pendientes de la base &mdash; es seguro correrlo las veces que quieras.</p>

    <?php if ($resultados): ?>
        <?php if ($hubo_error): ?>
            <div class="err-msg">Hubo un error al aplicar una migraci&oacute;n. Revis&aacute; el detalle abajo. Lo que s&iacute; se aplic&oacute; qued&oacute; guardado; pod&eacute;s corregir y volver a ejecutar.</div>
        <?php else: ?>
            <div class="ok-msg">&iexcl;Listo! Se aplicaron <?= count($resultados) ?> cambio(s) correctamente.</div>
        <?php endif; ?>
        <div class="card">
            <strong style="font-size:0.95rem;">Resultado de esta ejecuci&oacute;n</strong>
            <ul class="list">
                <?php foreach ($resultados as $r): ?>
                    <li>
                        <span class="badge b-<?= $r['estado'] === 'ok' ? 'ok' : ($r['estado'] === 'error' ? 'err' : 'omit') ?>"><?= h($r['estado']) ?></span>
                        <code><?= h($r['id']) ?></code>
                        <span class="desc"><?= h($r['detalle']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <strong style="font-size:0.95rem;">Estado actual</strong>
        <?php if (!$migraciones): ?>
            <div class="empty">No hay migraciones en la carpeta <code>/migrations</code> todav&iacute;a.</div>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($migraciones as $id => $m): ?>
                    <?php $aplicada = in_array($id, $ejecutadas, true); ?>
                    <li>
                        <span class="badge <?= $aplicada ? 'b-ok' : 'b-pend' ?>"><?= $aplicada ? 'aplicada' : 'pendiente' ?></span>
                        <code><?= h($id) ?></code>
                        <span class="desc"><?= h($m['descripcion']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <?php if ($pendientes): ?>
        <div class="card">
            <p style="margin:0 0 16px; font-size:0.9rem;">Hay <strong><?= count($pendientes) ?></strong> cambio(s) pendiente(s) de aplicar.</p>
            <form method="POST">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <button type="submit">Aplicar cambios pendientes</button>
            </form>
        </div>
    <?php elseif ($migraciones): ?>
        <div class="card">
            <div class="empty">&#10003; La base de datos est&aacute; al d&iacute;a. No hay nada pendiente.</div>
        </div>
    <?php endif; ?>

    <p><a class="back" href="admin.php">&larr; Volver al panel</a></p>
</div>
</body>
</html>
