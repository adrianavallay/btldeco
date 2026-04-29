<?php
/**
 * ProvinciaMapper — Argentina province name <-> 1-letter ISO 3166-2 code.
 *
 * Códigos según la tabla del manual Paq.ar (apiPaqAr-v2.pdf p.12) y
 * el estándar ISO 3166-2:AR. Un solo carácter por provincia.
 *
 * GOTCHA: la API rechaza nombres completos en el campo `state` (400 Bad Request).
 * Siempre normalizar el input del usuario con nombreToCodigo() antes de armar el payload.
 */

final class ProvinciaMapper
{
    /** Código 1 letra → Nombre canónico */
    private const CODIGOS = [
        'A' => 'Salta',
        'B' => 'Buenos Aires',
        'C' => 'Ciudad Autónoma de Buenos Aires',
        'D' => 'San Luis',
        'E' => 'Entre Ríos',
        'F' => 'La Rioja',
        'G' => 'Santiago del Estero',
        'H' => 'Chaco',
        'J' => 'San Juan',
        'K' => 'Catamarca',
        'L' => 'La Pampa',
        'M' => 'Mendoza',
        'N' => 'Misiones',
        'P' => 'Formosa',
        'Q' => 'Neuquén',
        'R' => 'Río Negro',
        'S' => 'Santa Fe',
        'T' => 'Tucumán',
        'U' => 'Chubut',
        'V' => 'Tierra del Fuego',
        'W' => 'Corrientes',
        'X' => 'Córdoba',
        'Y' => 'Jujuy',
        'Z' => 'Santa Cruz',
    ];

    /**
     * Aliases comunes (claves normalizadas: lowercase, sin acentos, sin espacios extras)
     * → código de 1 letra. Se chequea ANTES del lookup por nombre canónico.
     */
    private const ALIASES = [
        // CABA / Capital Federal
        'caba'                              => 'C',
        'capital federal'                   => 'C',
        'ciudad autonoma de buenos aires'   => 'C',
        'ciudad de buenos aires'            => 'C',
        // Buenos Aires (provincia) — para distinguir de la ciudad
        'provincia de buenos aires'         => 'B',
        'pba'                               => 'B',
        'gba'                               => 'B', // Gran Buenos Aires
        // Tierra del Fuego
        'tierra del fuego antartida e islas del atlantico sur' => 'V',
        // Variantes sin acento de las que llevan acento
        'rio negro'                         => 'R',
        'entre rios'                        => 'E',
        'cordoba'                           => 'X',
        'tucuman'                           => 'T',
        'neuquen'                           => 'Q',
        'jujuy'                             => 'Y',
    ];

    /** Normaliza: trim, lowercase, sin acentos, espacios colapsados. */
    private static function norm(string $s): string
    {
        $s = trim($s);
        if ($s === '') return '';
        // Bajar a minúsculas (UTF-8 safe)
        $s = mb_strtolower($s, 'UTF-8');
        // Sacar acentos
        $from = ['á','à','ä','â','é','è','ë','ê','í','ì','ï','î','ó','ò','ö','ô','ú','ù','ü','û','ñ'];
        $to   = ['a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','u','u','u','u','n'];
        $s = str_replace($from, $to, $s);
        // Colapsar espacios y signos repetidos
        $s = preg_replace('/\s+/', ' ', $s);
        return $s;
    }

    /**
     * Devuelve el código de 1 letra a partir de un nombre.
     * Acepta variantes ("CABA", "Capital Federal", con/sin acentos, mayúsculas, etc.).
     *
     * @throws InvalidArgumentException si no matchea ninguna provincia conocida.
     */
    public static function nombreToCodigo(string $nombre): string
    {
        $key = self::norm($nombre);
        if ($key === '') {
            throw new InvalidArgumentException('Nombre de provincia vacío.');
        }

        // 1) Match exacto contra alias
        if (isset(self::ALIASES[$key])) {
            return self::ALIASES[$key];
        }

        // 2) Match contra los nombres canónicos (también normalizados)
        foreach (self::CODIGOS as $codigo => $canon) {
            if (self::norm($canon) === $key) {
                return $codigo;
            }
        }

        // 3) Si lo que llegó YA es un código válido de 1 letra, devolverlo
        if (strlen($nombre) === 1 && isset(self::CODIGOS[strtoupper($nombre)])) {
            return strtoupper($nombre);
        }

        throw new InvalidArgumentException(
            "Provincia desconocida: '{$nombre}'. Códigos válidos: " . implode(', ', array_keys(self::CODIGOS))
        );
    }

    /**
     * Devuelve el nombre canónico a partir del código.
     *
     * @throws InvalidArgumentException si el código no existe.
     */
    public static function codigoToNombre(string $codigo): string
    {
        $codigo = strtoupper(trim($codigo));
        if (!isset(self::CODIGOS[$codigo])) {
            throw new InvalidArgumentException(
                "Código de provincia inválido: '{$codigo}'. Válidos: " . implode(', ', array_keys(self::CODIGOS))
            );
        }
        return self::CODIGOS[$codigo];
    }

    /** True si la string es un código válido de provincia. */
    public static function esCodigoValido(string $codigo): bool
    {
        return isset(self::CODIGOS[strtoupper(trim($codigo))]);
    }

    /** Devuelve toda la tabla código => nombre, útil para selects en UI. */
    public static function todas(): array
    {
        return self::CODIGOS;
    }
}
