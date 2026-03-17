<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Models\Favoritos;

// NIVELES DE ACCESO Y PLANTILLAS DE CADA ACCI�N
$files = [
    'favoritos' => ['n' => 2, 'p' => 'home'],
    'favoritos-agregar' => ['n' => 2, 'p' => ''],
    'favoritos-borrar' => ['n' => 2, 'p' => ''],
];

// REDEFINIR VARIABLES
$tsPage = 'php_files/p.favoritos.' . $files[$action]['p'];

$tsLevel = $files[$action]['n'];

$tsAjax = empty($files[$action]['p']) ? 1 : 0;

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if ($tsLevelMsg != 1) {
    echo '0: ' . $tsLevelMsg['mensaje'];
    die();
}

// CLASE
$tsFavoritos = new Favoritos();

// CODIGO
switch ($action) {
    case 'favoritos':
        $smarty->assign("tsFavoritos", $tsFavoritos->getPostFavoritos());
        break;
    case 'favoritos-agregar':
        echo $tsFavoritos->savePostFavorito();
        break;
    case 'favoritos-borrar':
        echo $tsFavoritos->delPostFavorito();
        break;
}
