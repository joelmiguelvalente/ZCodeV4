<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
*/

declare(strict_types=1);

if (!defined('ZCODE_ULTIMATE')) {
    exit('No direct script access allowed');
}

/**
 * Devuelve un bloque <pre> listo para mostrar código, con líneas numeradas
 * y la línea del error resaltada.
 *
 * @param string $file  Ruta del archivo a leer
 * @param int    $targetLine  Línea donde ocurrió el error
 * @param int    $padding  Cantidad de líneas antes/después para mostrar
 * @return string  HTML seguro con el código resaltado
 */
function highlight_file_segment($file, $line, $padding = 8)
{
    if (!is_readable($file)) {
        return "<div class='code-block'>Archivo no disponible.</div>";
    }

    $lines = file($file);
    $total = count($lines);

    $start = max($line - $padding, 1);
    $end   = min($line + $padding, $total);

    $html = "";

    for ($i = $start; $i <= $end; $i++) {
        $code = htmlspecialchars($lines[$i - 1]);

        if ($i == $line) {
            $html .= "<div class='error-line'><span class='ln'>{$i}</span> {$code}</div>";
        } else {
            $html .= "<div><span class='ln'>{$i}</span> {$code}</div>";
        }
    }

    return $html;
}
