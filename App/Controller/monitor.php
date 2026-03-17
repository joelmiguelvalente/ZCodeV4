<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

$tsPage = "monitor";

$tsLevel = 2;

$tsAjax = empty($_GET['ajax']) ? 0 : 1;

$tsContinue = true;

include_once realpath('../../') . DIRECTORY_SEPARATOR . "header.php";

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
    $action = htmlspecialchars($_GET['action'] ?? '');

    if (empty($action)) {
        $tsMonitor->show_type = 2;
        $tsData = $tsMonitor->getNotificaciones();
        $smarty->assign("tsData", $tsData);
      // LIVE SOUND
        $smarty->assign("tsStatus", $_COOKIE);
    } else {
        $tsData = $tsMonitor->getFollows($action);
    }
    $smarty->assign("tsData", $tsData);

    $smarty->assign("tsAction", $action);
}

if (empty($tsAjax)) {
    $smarty->assign("tsTitle", $tsTitle);

    include_once BASEPATH . "footer.php";
}
