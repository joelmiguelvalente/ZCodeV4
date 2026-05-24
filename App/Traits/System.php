<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace App\Traits;

/**
 * Trait con funciones auxiliares del sistema
 */
trait System
{
    // Obtener contenido de una URL
    public function getUrlContent(string $tsUrl): ?string
    {
        // Usamos cURL si está disponible (más seguro y configurable)
        if (function_exists('curl_init')) {
            // User-Agent del cliente (fallback a uno genérico si no existe)
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)';
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $tsUrl,
                CURLOPT_USERAGENT      => $userAgent,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_FOLLOWLOCATION => true,  // Permite redirecciones
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => true,  // Seguridad habilitada
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);
            $result = curl_exec($ch);
            // Si ocurrió algún error, devolver null
            if ($result === false) {
                curl_close($ch);
                return null;
            }
            curl_close($ch);
            return $result;
        }
        // Fallback sin cURL (menos seguro, pero útil en hosting muy limitado)
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'header'  => "User-Agent: Mozilla/5.0\r\n"
            ]
        ]);
        $result = @file_get_contents($tsUrl, false, $context);
        return $result !== false ? $result : null;
    }

    // Expresión "Hace X tiempo"
    public function setHace(int $fecha = 0, bool $show = false): string
    {
        if ($fecha <= 0) {
            return "Nunca";
        }
        $tiempo = time() - $fecha;

        $unidades = [
            31536000 => ["a&ntilde;o", "a&ntilde;os"],
            2678400  => ["mes", "meses"],
            604800   => ["semana", "semanas"],
            86400    => ["d&iacute;a", "d&iacute;as"],
            3600     => ["hora", "horas"],
            60       => ["minuto", "minutos"],
        ];

        foreach ($unidades as $segundos => $nombre) {
            if ($tiempo <= 60) {
                return ($show ? "Hace " : "") . "instantes";
            }

            $round = round($tiempo / $segundos);
            if ($round > 0) {
                return ($show ? "Hace " : "") . "{$round} {$nombre[($round > 1 ? 1 : 0)]}";
            }
        }

        return "Hace un momento";
    }

    // Genera una cadena SQL "campo = valor" getIUP
    public function buildSqlUpdateFields(array $fields, string $prefix = ''): string
    {
        $sets = [];

        foreach ($fields as $field => $value) {
            $safeValue = is_numeric($value) ? $value : "'" . str_replace("'", "''", $value) . "'";
            $sets[] = "{$prefix}{$field} = $safeValue";
        }

        return implode(', ', $sets);
    }

    // Genera un token aleatorio
    public function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    // Limpia HTML (seguro contra XSS básico)
    public function sanitizeHtml(string $input): string
    {
        return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
    }

    // Genera una cadena alfanumérica aleatoria
    public function randomString(int $length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $output = '';
        for ($i = 0; $i < $length; $i++) {
            $output .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $output;
    }

    // Valida si una cadena es una URL válida
    public function isValidUrl(string $url): bool
    {
        return (bool) filter_var($url, FILTER_VALIDATE_URL);
    }
}
