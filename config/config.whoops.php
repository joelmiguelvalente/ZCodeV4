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

if ($_ENV['ENVIRONMENT'] === 'DEVELOPMENT' && $_ENV['DEBUG']) {
    $whoops = new \Whoops\Run();
    if (\Whoops\Util\Misc::isAjaxRequest()) {
        $handler = new \Whoops\Handler\JsonResponseHandler();
    } else {
        $handler = new \Whoops\Handler\PrettyPageHandler();

        $sensibles = [
            'DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME', 'DB_PREFIX', 'DB_CHARSET', 'DB_PORT',
            'SMTP_HOST', 'SMTP_USER', 'SMTP_PASS', 'SMTP_NAME', 'SMTP_PORT', 'SMTP_SECURE',
            'APP_ID', 'APP_KEY', 'APP_SECRET',
        ];

        foreach ($sensibles as $key) {
            $handler->blacklist('_ENV', $key);
            $handler->blacklist('_SERVER', $key);
        }
        // Ocultar todas las cookies dinámicamente
        foreach (array_keys($_COOKIE) as $cookie) {
            $handler->blacklist('_COOKIE', $cookie);
        }
        $handler->setPageTitle("ZCode! Tenemos un problema.");
    }
    $whoops->pushHandler($handler);

    $whoops->register();
}
