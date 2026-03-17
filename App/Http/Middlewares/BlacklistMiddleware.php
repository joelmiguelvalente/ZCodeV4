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

use App\Database\DB;

class BlacklistMiddleware
{
    public function handle($tsCore, $smarty): void
    {
        $blacklist = DB::fetch(
            "SELECT id FROM @blacklist WHERE type = 1 AND value = :value",
            ['value' => $tsCore->executeIP()]
        );

        if ($blacklist) {
            $smarty->assign('tsTitle', 'Bloqueado de ' . $tsCore->settings['titulo']);
            $smarty->display('views/bloqueado.html');
            exit;
        }
    }
}
