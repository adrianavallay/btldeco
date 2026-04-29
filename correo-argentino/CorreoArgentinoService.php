<?php
/**
 * CorreoArgentinoService — capa de negocio sobre el cliente HTTP.
 *
 * Responsabilidades (FASE 8):
 *   - Tomar una orden de la tienda (tabla `pedidos`) y armar el payload Paq.ar:
 *       * Mapear provincia con ProvinciaMapper
 *       * Construir senderData (datos del comercio, fijos en config)
 *       * Construir shippingData (datos del cliente comprador del pedido)
 *       * Calcular peso/dimensiones/declaredValue en base a los items
 *       * Validar deliveryType + agencyId
 *       * Formatear saleDate como YYYY-MM-DDTHH:mm:ss-03:00
 *   - Persistir/actualizar la fila en `correo_argentino_envios`:
 *       * Guardar raw_alta_response y raw_last_tracking ANTES de parsear
 *       * Mantener status_code/status_description en sync con el último tracking
 *   - Generar y archivar el rótulo PDF (label_pdf_base64).
 *   - Cancelar el envío y actualizar la orden de la tienda.
 *
 * Esta clase es el ÚNICO lugar que toca la DB del proyecto + el cliente Paq.ar a la vez.
 * El controlador del admin (admin_pedidos.php) llama a esta clase, no al cliente directo.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/CorreoArgentinoClient.php';
require_once __DIR__ . '/helpers/ProvinciaMapper.php';

final class CorreoArgentinoService
{
    // Implementación en FASE 8.
}
