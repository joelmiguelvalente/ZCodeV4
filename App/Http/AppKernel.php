<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
 */

declare(strict_types=1);

namespace App\Http;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Utils\LimpiarSolicitud;
use App\Http\Middlewares\CorsMiddleware;

class AppKernel
{
    public function boot(): void
    {
        // Sanitizar la solicitud
        (new LimpiarSolicitud())->run();

        // Middlewares globales
        (new CorsMiddleware())->handle($_REQUEST);
    }
}
