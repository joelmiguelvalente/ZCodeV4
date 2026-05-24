<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}


// NIVELES DE ACCESO Y PLANTILLAS DE CADA ACCI�N
$files = [
    'test-cambiar' => ['n' => 0, 'p' => ''],
];

// REDEFINIR VARIABLES
$tsPage = 'php_files/p.test.' . $files[$action]['p'];

$tsLevel = $files[$action]['n'];

$tsAjax = empty($files[$action]['p']) ? 1 : 0;


// CODIGO
switch ($action) {
    case 'test-cambiar':
        var_dump($_POST);
        break;
}
