<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

use App\Models\Afiliado;

/** @var Container $Container */
$Container->set(Afiliado::class, Afiliado::class);

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

// NIVELES DE ACCESO Y PLANTILLAS DE CADA ACCI�N
$files = [
    'afiliado-nuevo-form' => ['n' => 0, 'p' => 'nuevo-form'],
    'afiliado-enviando' => ['n' => 0, 'p' => ''],
    'afiliado-borrar' => ['n' => 0, 'p' => ''],
    'afiliado-setaction' => ['n' => 0, 'p' => ''],
    'afiliado-url' => ['n' => 0, 'p' => ''],
    'afiliado-detalles' => ['n' => 0, 'p' => 'detalles'],
];

// REDEFINIR VARIABLES
$tsPage = 'php_files/p.afiliado.' . $files[$action]['p'];

$tsLevel = $files[$action]['n'];

$tsAjax = empty($files[$action]['p']) ? 1 : 0;

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if ($tsLevelMsg != 1) {
    echo '0: ' . $tsLevelMsg['mensaje'];
    die();
}

// CLASS
$tsAfiliado = $Container->get(Afiliado::class);

// CODIGO
switch ($action) {
    case 'afiliado-nuevo-form':
        break;
    case 'afiliado-enviando':
        echo $tsAfiliado->newAfiliado();
        break;
    case 'afiliado-borrar':
        echo $tsAfiliado->deleteAfiliado();
        break;
    case 'afiliado-setactive':
        echo $tsAfiliado->setActionAfiliado();
        break;
    case 'afiliado-url':
        $tsAfiliado->urlOut();
        break;
    case 'afiliado-detalles':
        $smarty->assign("tsAf", $tsAfiliado->getAfiliado());
        break;
    default:
        die('0: Este archivo no existe.');
    break;
}
