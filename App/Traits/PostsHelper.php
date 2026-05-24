<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace App\Traits;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

/**
 * Este trait asume que las propiedades $core y $user son inyectadas
 * desde la clase que lo utiliza, típicamente vía constructor.
 */

trait PostsHelper
{
    public function tagsNew(int $date = 0, int $days = 2)
    {
        // Obtener la fecha actual como timestamp UNIX
        $currentTimestamp = time();
        // Calcular la diferencia en segundos (2 días = 2 * 24 * 60 * 60)
        $twoDaysInSeconds = $days * 24 * 60 * 60;
        $differenceInSeconds = $currentTimestamp - $date;
        return ($differenceInSeconds < $twoDaysInSeconds) ? '&iexcl;Nuevo!' : '';
    }

    /**
     * Elimina BBcodes y URLs de una cadena de texto.
     *
     * @param string $text La cadena de texto de la que se eliminarán los BBcodes y URLs.
     * @return string La cadena de texto sin BBcodes ni URLs.
     */
    public function nobbcode(string $text = ''): string
    {
        // Elimina los códigos BBcodes
        $text = preg_replace('/\[.*?\]/', '', $text);
        // Elimina las URLs
        $text = preg_replace('@https?://[^\s]+@', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Trunca una cadena de texto a una longitud específica y añade puntos suspensivos al final.
     *
     * @param string $string La cadena de texto que se va a truncar.
     * @param int|null $length La longitud máxima de la cadena truncada. Si es null, se usa 150 como valor predeterminado.
     * @return string La cadena truncada con puntos suspensivos al final.
     */
    public function truncate(string $string = '', int $length = 150): string
    {
        // Usa la longitud proporcionada o el valor por defecto
        $length = $length <= 0 ? 150 : $length;
        // Envuelve la cadena en líneas de longitud máxima
        $wrapped = wordwrap($string, $length, "\n", true);
        // Toma la primera línea y añade puntos suspensivos si es necesario
        $truncated = explode("\n", $wrapped)[0] . '...';
        return $truncated;
    }

    public function readingTime(string $content = '', int $wpm = 250)
    {
        // Eliminar los BBCode usando una expresión regular
        $content = $this->nobbcode($content);
        // Contar las palabras después de eliminar los BBCode y URLs
        $word_count = str_word_count($content);
        // Calcular el tiempo estimado de lectura en minutos
        $total_minutes = $word_count / $wpm;
        // Calcular el tiempo en minutos y segundos
        $minutes = floor($total_minutes);
        $seconds = round(($total_minutes - $minutes) * 60);
        // Formatear el resultado
        if ($minutes > 0) {
            $reading_time = "Tiempo de lectura {$minutes}";
            $reading_time .= ($seconds > 0 ? ":{$seconds} " : "") . " min";
        } else {
            $reading_time = "Tiempo de lectura {$seconds} segundos";
        }

        return $reading_time;
    }
}
