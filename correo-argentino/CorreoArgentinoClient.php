<?php
/**
 * CorreoArgentinoClient — cliente HTTP de bajo nivel para la API Paq.ar v1.
 *
 * Responsabilidades:
 *   - cURL + headers de auth (Authorization: Apikey ..., agreement: ...)
 *   - Timeouts (30s default, 60s para /labels)
 *   - Retry SOLO en errores 5xx (nunca en 4xx)
 *   - Retornar response crudo (raw body + status + headers) — nunca parsear acá
 *   - Logear cada request/response a correo-argentino/logs/YYYY-MM.log
 *
 * NO contiene lógica de negocio ni mapeo de orden → payload (eso es Service).
 *
 * Métodos a implementar (uno por fase):
 *   FASE 2: auth()
 *   FASE 3: getAgencies(?stateId, ?pickup_availability, ?package_reception)
 *   FASE 4: createOrder(array $payload)
 *   FASE 5: getLabel(array $items, ?string $labelFormat)
 *   FASE 6: getTracking(array $trackingNumbers, ?string $extClient)
 *   FASE 7: cancelOrder(string $trackingNumber)
 */

require_once __DIR__ . '/config.php';

final class CorreoArgentinoClient
{
    // Implementación incremental por fase. Stub mantenido para que require_once no falle.
}
