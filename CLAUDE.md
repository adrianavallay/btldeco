# BTLDECO — guía de proyecto para Claude Code

Tienda online (PHP puro, MySQL) deployada en Hostinger. Repo: `git@github.com:adrianavallay/btldeco.git`. **Cualquier `git push origin main` se auto-deploya al server**, así que los cambios pushados se ven inmediatamente en `btldeco.com.ar`.

Convenciones generales:
- PHP 8+, sin framework. Funciones globales declaradas en `config.php`.
- DB MySQL. Conexión vía `pdo()` (PDO con prepared statements). **Nunca concatenar SQL.**
- Sesiones con flags `httpOnly`, `secure`, `SameSite=Lax`.
- Variables sensibles en `.env` (gitignored). Template en `.env.example`.

---

## Integración Correo Argentino — Paq.ar API v1

### Hosts
| Ambiente | Base URL |
|---|---|
| Test (default) | `https://apitest.correoargentino.com.ar` |
| Producción | `https://api.correoargentino.com.ar` |

Todos los endpoints viven bajo `/paqar/v1/<endpoint>`.

### Headers obligatorios en TODA request
```
Authorization: Apikey <API_KEY>
agreement: <AGREEMENT_ID>
```
(`Apikey` con mayúscula inicial, todo en una palabra; el agreement es string aunque sea numérico.)

### Endpoints

| Método | Ruta | Descripción | Notas |
|---|---|---|---|
| GET | `/v1/auth` | Validar credenciales | Devuelve **204 No Content** si OK |
| GET | `/v1/agencies` | Listar sucursales | Query: `stateId`, `pickup_availability` (bool), `package_reception` (bool) |
| POST | `/v1/orders` | Alta de orden | Ver schema más abajo |
| PATCH | `/v1/orders/{trackingNumber}/cancel` | Cancelar orden | Sin body |
| POST | `/v1/labels` | Obtener rótulo PDF base64 | Body = array de `{sellerId, trackingNumber}`. Param query opcional `labelFormat` |
| GET | `/v1/tracking` | Historial de pedido(s) | Body = array de `{trackingNumber}`. Header opcional `extClient` (3 dígitos) |

### Schema de body para POST `/v1/orders`
```json
{
  "sellerId": "string",
  "trackingNumber": "string (opcional, si no lo mando lo asigna el correo)",
  "senderData": {
    "name": "...", "businessName": "...",
    "areaCodePhone": "...", "phoneNumber": "...",
    "areaCodeCellphone": "...", "cellphoneNumber": "...",
    "email": "...", "observation": "...",
    "address": {
      "streetName": "obligatorio",
      "streetNumber": "obligatorio",
      "cityName": "obligatorio",
      "floor": "...", "department": "...",
      "state": "código 1 letra (ver tabla)",
      "zipCode": "obligatorio, validado vs state"
    }
  },
  "shippingData": { "...mismo shape que senderData..." },
  "parcels": [{
    "dimensions": { "height": "string", "width": "string", "depth": "string" },
    "productWeight": "string en gramos, ≤5 dígitos",
    "productCategory": "string",
    "declaredValue": "string numérico"
  }],
  "deliveryType": "homeDelivery | agency | locker",
  "agencyId": "obligatorio si deliveryType ≠ homeDelivery",
  "saleDate": "YYYY-MM-DDTHH:mm:ss-03:00",
  "serviceType": "string 2 letras (ej CP)",
  "shipmentClientId": "string opcional"
}
```

### Códigos de provincia (1 letra, ISO 3166-2 AR)
| Cód | Provincia |
|---|---|
| A | Salta |
| B | Buenos Aires |
| C | CABA / Ciudad Autónoma de Buenos Aires |
| D | San Luis |
| E | Entre Ríos |
| F | La Rioja |
| G | Santiago del Estero |
| H | Chaco |
| J | San Juan |
| K | Catamarca |
| L | La Pampa |
| M | Mendoza |
| N | Misiones |
| P | Formosa |
| Q | Neuquén |
| R | Río Negro |
| S | Santa Fe |
| T | Tucumán |
| U | Chubut |
| V | Tierra del Fuego |
| W | Corrientes |
| X | Córdoba |
| Y | Jujuy |
| Z | Santa Cruz |

Implementado en `correo-argentino/helpers/ProvinciaMapper.php`.

---

## Gotchas — leer SIEMPRE antes de tocar el cliente

1. **Provincia = 1 letra.** Mandar "Buenos Aires" como `state` → 400. Usar el mapper.
2. **`parcels[]` solo procesa el primero.** Los demás se ignoran silenciosamente — un bug invisible.
3. **`productWeight` en GRAMOS, máx 5 dígitos.** Cliente 18018 max 25000 (25 kg). String numérico.
4. **`dimensions` en CM, máx 3 dígitos por lado, como STRING numérico** (`"100"`, no `100`).
5. **`deliveryType`** acepta exactamente `"agency"`, `"locker"`, `"homeDelivery"`. Nada más, no traducir.
6. **Si `deliveryType` ≠ `homeDelivery`, `agencyId` es obligatorio** (lo sacás de `/v1/agencies`).
7. **`saleDate` formato exacto:** `YYYY-MM-DDTHH:mm:ss-03:00` (ej: `2026-04-29T15:30:00-03:00`).
8. **`zipCode` se valida cruzado contra `state`** — si no matchean, 400.
9. **`/labels` array:** cada TN tiene su propio `result` (`"OK"` o `"ERROR: ..."`). Procesar item por item, nunca asumir que todos OK porque el HTTP es 200.
10. **`labelFormat`:** solo `"10x15"`, `"label"`, o sin parámetro (3 SP distintos atrás). Cualquier otro string se ignora.

### Gotchas extra detectados en el PDF (no eran obvios)

11. **Inconsistencia `sellerId` vs `idSeller` en `/orders`.** El schema dice `sellerId`, el ejemplo de Success Response usa `idSeller`. **Probar primero `sellerId`** (es lo definido formalmente). Si falla, fallback a `idSeller`. Anotar comportamiento real una vez probado en TEST.
12. **`/tracking` es GET pero acepta body** (raro pero documentado). cURL: `CURLOPT_CUSTOMREQUEST = "GET"` + `CURLOPT_POSTFIELDS = json`.
13. **`extClient`** se manda en HEADER (no body). 3 dígitos numéricos exactos. Si no lo paso, el server agrega `"000"` al final del agreement. Si lo paso, agrega esos 3 dígitos. No cumplir → "extClient must be 3 characters long".
14. **`/labels` acepta `labelFormat` como query param** (no body), igual que `stateId` en `/agencies`.
15. **`/auth` responde 204** (no 200). Verificar con `if ($status === 204)` no con `<300`.

### Comportamiento real verificado contra `apitest.correoargentino.com.ar`

- **Api key inexistente** → cualquier endpoint devuelve **401** con
  `{"error":"Account not found by apiKey", "message":"Account not found by apiKey"}`
  (verificado en `/auth` y `/agencies`). El PDF lista 403 para `/agencies` con creds
  inválidas, pero en la práctica el 403 parece estar reservado para "api key válida
  pero sin permiso sobre ese agreement".
- El JSON de error siempre incluye los 5 campos: `timestamp`, `status`, `error`,
  `message`, `path` (con leading slash). `message` puede ser igual a `error` o
  agregar contexto.
- Booleans en query string van como literales `true` / `false` (no `1` / `0`).

---

## Reglas duras del proyecto (no negociables)

- Credenciales SIEMPRE en `.env`, nunca hardcodeadas, nunca commiteadas.
- `.env.example` con claves vacías; `.env` en `.gitignore` (ya está).
- Default = ambiente **TEST**. Solo paso a PROD cuando el usuario lo pida explícitamente.
- Antes de parsear cualquier response → guardar el raw JSON en la DB (`raw_alta_response`, `raw_last_tracking`).
- **Reintentar SOLO en errores 5xx**, nunca en 4xx.
- Timeout default **30s**. Endpoint `/labels` → **60s** (PDF base64 pesa).
- Logs con timestamp ISO en `correo-argentino/logs/YYYY-MM.log` (rotación mensual).
- **Solo prepared statements** — nunca concatenar SQL ni usar `mysql_*`.
- **Tracking number es UNIQUE** en la DB (constraint).
- Nunca mostrar credenciales ni body completo en respuestas web — solo en logs server-side.

---

## Estructura de archivos

```
btldeco/
├── CLAUDE.md                      ← este archivo
├── .env.example                   ← template (con claves vacías)
├── .env                           ← real (gitignored)
├── correo-argentino/
│   ├── config.php                 ← carga .env, define constants, log helper
│   ├── CorreoArgentinoClient.php  ← cliente HTTP (auth, getAgencies, createOrder, getLabel, getTracking, cancelOrder)
│   ├── CorreoArgentinoService.php ← lógica de negocio (mapea orden tienda → payload Paq.ar, persiste en DB)
│   ├── helpers/
│   │   └── ProvinciaMapper.php    ← lookup provincias ↔ códigos
│   ├── migrations/
│   │   └── 001_envios.sql         ← tabla correo_argentino_envios
│   ├── tests/                     ← scripts standalone (php tests/0X_xxx.php)
│   │   ├── 01_test_auth.php
│   │   ├── 02_test_agencies.php
│   │   ├── 03_test_alta_orden.php
│   │   ├── 04_test_label.php
│   │   ├── 05_test_tracking.php
│   │   └── 06_test_cancel.php
│   └── logs/                      ← logs rotativos por mes
│       └── YYYY-MM.log
```

---

## Mapping fases → archivos

| Fase | Qué se completa |
|---|---|
| **1** | CLAUDE.md, estructura, `.env.example`, `migrations/001_envios.sql`, `helpers/ProvinciaMapper.php`, `config.php` (skeleton), stubs de Client y Service |
| **2** | `CorreoArgentinoClient::auth()` + `tests/01_test_auth.php` |
| **3** | `CorreoArgentinoClient::getAgencies()` + `tests/02_test_agencies.php` |
| **4** | `CorreoArgentinoClient::createOrder()` + `tests/03_test_alta_orden.php` |
| **5** | `CorreoArgentinoClient::getLabel()` + `tests/04_test_label.php` |
| **6** | `CorreoArgentinoClient::getTracking()` + `tests/05_test_tracking.php` |
| **7** | `CorreoArgentinoClient::cancelOrder()` + `tests/06_test_cancel.php` |
| **8** | `CorreoArgentinoService` orquestando todo + integración con admin_pedidos |

**Antes de pasar de una fase a la siguiente, mostrar el resultado al usuario y esperar OK.**

---

## Cosas prohibidas

- Inventar campos, endpoints o headers que no estén en el PDF (`apiPaqAr-v2.pdf` en raíz).
- Usar mysqli_query con concatenación, mysql_*, deprecated APIs.
- Hardcodear credenciales, agreement IDs ni URLs de producción.
- Escribir código de fases siguientes antes de tener OK del usuario en la actual.
- Asumir que tengo credenciales de prod — empezar todo en TEST.
