<?php

/**
 * @package		ZCode
 * @author 		Miguel92
 * @copyright 	2024 - 2026
 * @version 	4.0.0
*/

declare(strict_types=1);

/**
 * Antes de comenzar, comprobaremos que este instalado
 */
if(!file_exists(__DIR__ . '/.env') && !file_exists(__DIR__ . '/.lock')) {
	header("Location: ./install/index.php?action=bienvenida");
	# Evitamos que continue
	die;
}

/**
 * Si esta instalado, continuamos con el sistema
 */
// Incluimos header
require_once __DIR__ . '/header.php';

$doPage = filter_input(INPUT_GET, 'do', FILTER_UNSAFE_RAW) === 'portal';
// Checamos...
$controller = ((int)$tsCore->settings['c_allow_portal'] AND $tsUser->is_member AND $doPage) ? 'portal' : 'posts';
require_once __DIR__ . "/app/controller/{$controller}.php";