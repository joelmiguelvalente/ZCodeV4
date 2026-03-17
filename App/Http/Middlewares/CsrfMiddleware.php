<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
 */

declare(strict_types=1);

namespace App\Http\Middlewares;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class CsrfMiddleware
{
    public function handle($smarty): void
    {
        $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
        $smarty->assign('csrf_token', $_SESSION['csrf']);
    }
}
