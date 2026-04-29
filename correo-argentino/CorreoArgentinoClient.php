<?php
/**
 * CorreoArgentinoClient — cliente HTTP de bajo nivel para la API Paq.ar v1.
 *
 * Responsabilidades:
 *   - cURL + headers de auth (Authorization: Apikey ..., agreement: ...)
 *   - Timeouts (30s default, 60s para /labels)
 *   - Retry SOLO en errores 5xx y errores de red — nunca en 4xx
 *   - Retornar response crudo (raw body + status + headers) — nunca parsear acá
 *   - Logear cada intento a correo-argentino/logs/YYYY-MM.log
 *
 * NO contiene lógica de negocio ni mapeo de orden → payload (eso es Service).
 *
 * Métodos por fase:
 *   FASE 2: auth()                                       ← implementado
 *   FASE 3: getAgencies(...)
 *   FASE 4: createOrder(array $payload)
 *   FASE 5: getLabel(array $items, ?string $labelFormat)
 *   FASE 6: getTracking(array $trackingNumbers, ?string $extClient)
 *   FASE 7: cancelOrder(string $trackingNumber)
 */

require_once __DIR__ . '/config.php';

final class CorreoArgentinoClient
{
    private string $apiKey;
    private string $agreement;
    private string $baseUrl;
    private int $timeout;
    private ?string $extClient;
    /** Cantidad máxima de reintentos adicionales (sólo 5xx / red). 0 = solo intento inicial. */
    private int $maxRetries;

    public function __construct(
        ?string $apiKey    = null,
        ?string $agreement = null,
        ?string $baseUrl   = null,
        ?int    $timeout   = null,
        ?string $extClient = null,
        int     $maxRetries = 2
    ) {
        $this->apiKey    = $apiKey    ?? CORREO_API_KEY;
        $this->agreement = $agreement ?? CORREO_AGREEMENT;
        $this->baseUrl   = rtrim($baseUrl ?? CORREO_BASE_URL, '/');
        $this->timeout   = $timeout   ?? CORREO_TIMEOUT;
        $this->extClient = $extClient ?? (CORREO_EXT_CLIENT !== '' ? CORREO_EXT_CLIENT : null);
        $this->maxRetries = max(0, $maxRetries);
    }

    // ── Endpoints ──────────────────────────────────────────────────────────

    /**
     * Validar credenciales. GET /paqar/v1/auth → 204 No Content si OK.
     *
     * @return array Ver request() para shape.
     */
    public function auth(): array
    {
        return $this->request('GET', '/paqar/v1/auth');
    }

    // ── HTTP core ──────────────────────────────────────────────────────────

    /**
     * Ejecuta una request HTTP contra Paq.ar.
     *
     * @param string $method  GET | POST | PATCH | etc.
     * @param string $path    Path relativo, empezando con '/' (ej: '/paqar/v1/auth')
     * @param array  $opts    {
     *     query?:           array assoc → query string
     *     json?:            mixed → body JSON-encoded (Content-Type: application/json)
     *     headers?:         array assoc → headers extra ('Header-Name' => 'value')
     *     timeout?:         int → override del timeout default
     *     use_ext_client?:  bool → si true, agrega header 'extClient: ...' (default false)
     * }
     * @return array {
     *     status:       int      HTTP status code (0 si error de red)
     *     body:         string   Raw body (vacío si 204)
     *     json:         mixed    json_decoded del body si era JSON; null en caso contrario
     *     headers:      array    Headers de respuesta (lowercase keys)
     *     request_id:   string   ID corto para correlacionar con logs
     *     duration_ms:  int      Duración total
     *     attempts:     int      Intentos efectuados (>=1)
     *     error:        ?string  Mensaje de error de red/cURL si status === 0
     *     url:          string   URL final llamada (sin credenciales)
     * }
     */
    private function request(string $method, string $path, array $opts = []): array
    {
        $url = $this->baseUrl . $path;
        if (!empty($opts['query'])) {
            $url .= '?' . http_build_query($opts['query']);
        }

        // Build headers
        $headers = [
            'Authorization: Apikey ' . $this->apiKey,
            'agreement: ' . $this->agreement,
            'Accept: application/json',
        ];
        if (!empty($opts['use_ext_client']) && $this->extClient !== null && $this->extClient !== '') {
            $headers[] = 'extClient: ' . $this->extClient;
        }
        foreach (($opts['headers'] ?? []) as $name => $value) {
            $headers[] = "$name: $value";
        }

        // Body
        $body = null;
        if (array_key_exists('json', $opts)) {
            $body = json_encode($opts['json'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $headers[] = 'Content-Type: application/json';
        }

        $timeout    = (int) ($opts['timeout'] ?? $this->timeout);
        $requestId  = bin2hex(random_bytes(6));
        $maxAttempts = 1 + $this->maxRetries;

        $started = microtime(true);
        $lastResult = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $attemptStarted = microtime(true);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_CUSTOMREQUEST  => $method,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_TIMEOUT        => $timeout,
                CURLOPT_CONNECTTIMEOUT => min(10, $timeout),
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT      => 'btldeco/correo-argentino-client/1.0',
            ]);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            $raw     = curl_exec($ch);
            $errno   = curl_errno($ch);
            $errstr  = curl_error($ch);
            $status  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $hsize   = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            // curl_close() is a no-op since PHP 8.0 and deprecated in 8.5; let GC handle the handle.

            $attemptDuration = (int) round((microtime(true) - $attemptStarted) * 1000);

            // Network/transport error → posibilita retry
            if ($errno !== 0 || $raw === false) {
                $lastResult = [
                    'status'      => 0,
                    'body'        => '',
                    'json'        => null,
                    'headers'     => [],
                    'request_id'  => $requestId,
                    'duration_ms' => $attemptDuration,
                    'attempts'    => $attempt,
                    'error'       => "cURL error #{$errno}: {$errstr}",
                    'url'         => $url,
                ];
                correo_log('error', "{$method} {$path} cURL fail", [
                    'request_id'  => $requestId,
                    'attempt'     => $attempt,
                    'error'       => $errstr,
                    'duration_ms' => $attemptDuration,
                ]);
                if ($attempt < $maxAttempts) {
                    usleep((int) (pow(2, $attempt - 1) * 500 * 1000)); // 500ms, 1s, 2s...
                    continue;
                }
                return $lastResult;
            }

            $rawHeaders = substr($raw, 0, $hsize);
            $rawBody    = (string) substr($raw, $hsize);
            $parsedHdrs = self::parseHeaders($rawHeaders);

            $json = null;
            $trim = ltrim($rawBody);
            if ($trim !== '' && ($trim[0] === '{' || $trim[0] === '[')) {
                $decoded = json_decode($rawBody, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $json = $decoded;
                }
            }

            $result = [
                'status'      => $status,
                'body'        => $rawBody,
                'json'        => $json,
                'headers'     => $parsedHdrs,
                'request_id'  => $requestId,
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'attempts'    => $attempt,
                'error'       => null,
                'url'         => $url,
            ];

            $level = $status >= 500 ? 'error' : ($status >= 400 ? 'warn' : 'info');
            correo_log($level, "{$method} {$path} → HTTP {$status}", [
                'request_id'       => $requestId,
                'attempt'          => $attempt,
                'duration_ms'      => $attemptDuration,
                'response_preview' => mb_substr($rawBody, 0, 500),
            ]);

            // Retry SOLO en 5xx — nunca en 4xx (regla del proyecto)
            if ($status >= 500 && $status <= 599 && $attempt < $maxAttempts) {
                $lastResult = $result;
                usleep((int) (pow(2, $attempt - 1) * 500 * 1000));
                continue;
            }

            return $result;
        }

        // Solo se llega acá si todos los intentos fallaron en red.
        return $lastResult;
    }

    private static function parseHeaders(string $raw): array
    {
        $headers = [];
        $lines   = preg_split("/\r\n|\n|\r/", trim($raw));
        foreach ($lines as $line) {
            if (strpos($line, ':') === false) continue;
            [$k, $v] = explode(':', $line, 2);
            $headers[strtolower(trim($k))] = trim($v);
        }
        return $headers;
    }
}
