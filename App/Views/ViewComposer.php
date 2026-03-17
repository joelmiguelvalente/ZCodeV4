<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
 */

declare(strict_types=1);

namespace App\Views;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Utils\OAuthentication;

class ViewComposer
{
    public function compose($smarty, $tsCore, $tsUser, $tsMonitor, $tsMP, $Theme, $Seo): void
    {
        // Configuración general
        $smarty->assign('tsConfig', $tsCore->settings);
        $smarty->assign('tsCategorias', $tsCore->getCategorias());
        $smarty->assign('tsRoutes', $tsCore->route());
        $smarty->assign('tsNews', $tsCore->getNews());
        $smarty->assign('tsNovemods', $tsCore->getNovemods());
        $smarty->assign('tsUser', $tsUser);

        // Monitor y mensajes
        $smarty->assign('tsAvisos', $tsMonitor->avisos);
        $smarty->assign('tsNots', $tsMonitor->notificaciones);
        $smarty->assign('tsMPs', $tsMP->mensajes);

        // Tema
        $smarty->assign('Theme', $Theme);
        $smarty->assign('tsThemeBox', $Theme->getSettingPageBox());
        $smarty->assign('tsSeo', $Seo);

        // Menú de cuenta
        require_once UTILITIES . '/extras/MenuUserAccount.php';
        $smarty->assign('tsMenuCuenta', $MenuCuenta);

        // OAuth
        $smarty->assign('SocialMager', (new OAuthentication())->OAuth());
    }
}
