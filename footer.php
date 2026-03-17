<?php

/**
 * @package		ZCode
 * @author 		Miguel92
 * @copyright 	2024 - 2026
 * @version 	4.0.0
*/

declare(strict_types=1);

if (!defined('ZCODE_ULTIMATE')) {
	exit('No se permite el acceso directo al script');
}

// Pagina solicitada
$smarty->assign("tsPage", $tsPage);
# Por si quieren cambiar la pagina de error
# Si no encuentra la plantilla t.$tsPage.tpl
# Mostrar esta pagina
$smarty->templateError = '404.html';

$smarty->setTheme(TS_TEMA);
$smarty->setPage($tsPage);

$smarty->load($tsPage);