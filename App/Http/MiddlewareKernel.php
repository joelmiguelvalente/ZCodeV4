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

use App\Http\Middlewares\{
    SanitizeRequestMiddleware,
    BlacklistMiddleware,
    MaintenanceMiddleware,
    CsrfMiddleware
};

class MiddlewareKernel
{
    protected array $middlewares = [];

    public function __construct()
    {
        $this->middlewares = [
            'sanitize' => SanitizeRequestMiddleware::class,
            'blacklist' => BlacklistMiddleware::class,
            'maintenance' => MaintenanceMiddleware::class,
            'csrf' => CsrfMiddleware::class,
        ];
    }

    public function run($tsCore, $tsUser, $smarty): void
    {
        foreach ($this->middlewares as $name => $middleware) {
            match ($name) {
                'csrf' => (new $middleware())->handle($smarty),
                'blacklist' => (new $middleware())->handle($tsCore, $smarty),
                default => (new $middleware())->handle($tsCore, $tsUser, $smarty),
            };
        }
    }
}
