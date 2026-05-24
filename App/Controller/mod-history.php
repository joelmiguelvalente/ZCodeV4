<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

use App\Models\Moderacion;

$tsPage = "mod-history";

$tsLevel = 2;

$tsAjax = empty($_GET['ajax']) ? 0 : 1;

$tsContinue = true;

include dirname(__DIR__, 2) . "/header.php";

$tsTitle = $tsCore->settings['titulo'] . ' - ' . $tsCore->settings['slogan'];

// VERIFICAMOS EL NIVEL DE ACCSESO ANTES CONFIGURADO
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if (!$tsLevelMsg) {
    $tsPage = 'aviso';
    $tsAjax = 0;
    $smarty->assign("tsAviso", $tsLevelMsg);
    //
    $tsContinue = false;
}

if ($tsContinue) {
    $tsMod = new Moderacion();

    // ACTION
    $action = htmlspecialchars($_GET['ver'] ?? '');

   // HISTORIAL
    $history = ($action === 'fotos') ? 'fotos' : 1;
    $smarty->assign("tsHistory", $tsMod->getHistory($history));

    // ACCION?
    $smarty->assign("tsAction", $action);
}

if (empty($tsAjax)) {
    $smarty->assign("tsTitle", $tsTitle);

    include BASEPATH . 'footer.php';
}
