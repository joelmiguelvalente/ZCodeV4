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

use App\Models\Tops;

// NIVELES DE ACCESO Y PLANTILLAS DE CADA ACCIÓN
$files = [
    'tops-posts' => ['n' => 0, 'p' => 'posts'],
    'tops-usuarios' => ['n' => 0, 'p' => 'usuarios'],
];

// REDEFINIR VARIABLES
$tsPage = 'php_files/p.tops.' . $files[$action]['p'];

$tsLevel = $files[$action]['n'];

$tsAjax = empty($files[$action]['p']) ? 1 : 0;

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if ($tsLevelMsg != 1) {
    echo '0: ' . $tsLevelMsg['mensaje'];
    die();
}

// CLASE
$tsTops = new Tops();

// CODIGO
switch ($action) {
    case 'tops-posts':
        $posts = $tsTops->getHomeTopPosts();
        $smarty->assign('tsTopPosts', $posts[$_POST['period']]);
        break;
    case 'tops-usuarios':
        $usuarios = $tsTops->getHomeTopUsers();
        $smarty->assign('tsTopUsers', $usuarios[$_POST['period']]);
        break;
}
