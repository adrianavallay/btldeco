<?php
/**
 * Correo Argentino — config + helpers compartidos.
 *
 * - Carga el .env raíz reutilizando load_env() del config principal.
 * - Define las constantes CORREO_* a partir de las variables de entorno.
 * - Expone correo_log() con rotación mensual a correo-argentino/logs/YYYY-MM.log.
 * - Expone correo_pdo() (alias del pdo() del proyecto) para mantener una sola conexión.
 *
 * Este archivo NO contiene lógica HTTP ni de negocio — sólo wiring.
 */

// Cargar config raíz (incluye load_env y abre la sesión).
require_once __DIR__ . '/../config.php';

// ── Constantes derivadas del .env ────────────────────────────────────────

if (!defined('CORREO_ENV')) {
    define('CORREO_ENV', strtolower(env('CORREO_ARGENTINO_ENV', 'test')));
}

if (!defined('CORREO_BASE_URL')) {
    $base = (CORREO_ENV === 'prod')
        ? 'https://api.correoargentino.com.ar'
        : 'https://apitest.correoargentino.com.ar';
    define('CORREO_BASE_URL', $base);
}

if (!defined('CORREO_API_KEY'))            define('CORREO_API_KEY',            env('CORREO_ARGENTINO_API_KEY', ''));
if (!defined('CORREO_AGREEMENT'))          define('CORREO_AGREEMENT',          env('CORREO_ARGENTINO_AGREEMENT', ''));
if (!defined('CORREO_DEFAULT_SELLER_ID'))  define('CORREO_DEFAULT_SELLER_ID',  env('CORREO_ARGENTINO_DEFAULT_SELLER_ID', ''));
if (!defined('CORREO_EXT_CLIENT'))         define('CORREO_EXT_CLIENT',         env('CORREO_ARGENTINO_EXT_CLIENT', ''));
if (!defined('CORREO_TIMEOUT'))            define('CORREO_TIMEOUT',            (int) env('CORREO_ARGENTINO_TIMEOUT', '30'));
if (!defined('CORREO_TIMEOUT_LABELS'))     define('CORREO_TIMEOUT_LABELS',     (int) env('CORREO_ARGENTINO_TIMEOUT_LABELS', '60'));

if (!defined('CORREO_LOG_DIR')) {
    define('CORREO_LOG_DIR', __DIR__ . '/logs');
}

// ── PDO ──────────────────────────────────────────────────────────────────

/** Misma conexión PDO que usa el resto del proyecto. */
function correo_pdo(): PDO {
    return pdo();
}

// ── Logging ──────────────────────────────────────────────────────────────

/**
 * Escribe una línea estructurada al log mensual.
 *
 * @param string $level   info | warn | error
 * @param string $msg     Mensaje principal
 * @param array  $ctx     Contexto serializable (request_id, http_status, payload, etc.)
 */
function correo_log(string $level, string $msg, array $ctx = []): void {
    if (!is_dir(CORREO_LOG_DIR)) {
        @mkdir(CORREO_LOG_DIR, 0755, true);
    }
    $file = CORREO_LOG_DIR . '/' . date('Y-m') . '.log';

    $line = sprintf(
        "[%s] %-5s %s%s\n",
        date('c'),                                 // ISO 8601 con offset
        strtoupper($level),
        $msg,
        $ctx ? ' ' . json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : ''
    );

    @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
}

// ── Validación temprana de configuración ─────────────────────────────────

/**
 * Verifica que API_KEY y AGREEMENT estén configurados. Útil para que los tests fallen
 * con un mensaje claro antes de hacer la primera request.
 *
 * @throws RuntimeException si falta alguna credencial requerida.
 */
function correo_assert_credentials_loaded(): void {
    $missing = [];
    if (CORREO_API_KEY === '')   $missing[] = 'CORREO_ARGENTINO_API_KEY';
    if (CORREO_AGREEMENT === '') $missing[] = 'CORREO_ARGENTINO_AGREEMENT';
    if ($missing) {
        throw new RuntimeException(
            "Faltan credenciales de Correo Argentino en .env: " . implode(', ', $missing)
        );
    }
}
