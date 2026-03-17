<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

namespace App\Traits;

use App\Database\DB;

/**
 * Trait con funciones auxiliares del sistema
 */
trait Url
{
    // Obtenemos el protocolo https o http
    public function getSSLProtocol(bool $withoutSlash = false): string
    {
        $ssl = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $ssl .= 's';
        }
        return $withoutSlash ? $ssl . '://' : $ssl;
    }

    # Obtenemos la url sin el protocolo
    public function url(bool $slash = true): string
    {
        $query = DB::fetch("SELECT url FROM @configuracion WHERE tscript_id = :id", ['id' => 1]);
        return ($slash ? $this->getSSLProtocol(true) : '') . $query['url'];
    }
}
