<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

if (! defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

// Página solicitada
$smarty->assign("tsPage", $tsPage);

if ($tsUser->is_member <= 0) {
    header("Location: ../login/");
    die;
}

$smarty->templateError = '404.html';

$smarty->setTheme(TS_TEMA);
$smarty->setPage($tsPage);

$smarty->load('main', (!isset($useExtension) ? true : $useExtension));
