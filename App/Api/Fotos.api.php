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

use App\Models\Fotos;

$files = [
   'fotos-votar' => ['n' => 2, 'p' => ''],
];

// REDEFINIR VARIABLES
$tsPage = 'ajax/p.fotos.' . $files[$action]['p'];
$tsLevel = $files[$action]['n'];
$tsAjax = empty($files[$action]['p']) ? 1 : 0;

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if ($tsLevelMsg != 1) :
    echo '0: ' . $tsLevelMsg['mensaje'];
    die();
endif;

// CLASE
$tsFotos = new Fotos();

// CODIGO
switch ($action) {
    case 'fotos-votar':
        echo $tsFotos->votarFoto();
        break;
}
