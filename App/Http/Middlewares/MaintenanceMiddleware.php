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

class MaintenanceMiddleware
{
    public function handle($tsCore, $tsUser, $smarty): void
    {
        $inAction = !in_array($_GET['action'] ?? '', ['login-user', 'login']);

        if (
            (int)$tsCore->settings['offline'] &&
            ((int)$tsUser->is_admod === 0 && !$tsUser->permisos['govwm']) &&
            $inAction
        ) {
            $smarty->assign('tsTitle', 'En mantenimiento | ' . $tsCore->settings['titulo']);
            $smarty->display('views/mantenimiento.html');
            exit;
        }
    }
}
