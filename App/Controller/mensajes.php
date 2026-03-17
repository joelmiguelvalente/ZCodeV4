<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

$tsPage = "mensajes";

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
    $action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : '';
    $unread = empty($_GET['qt']) ? false : true;

    match ($action) {
        '' => $smarty->assign("tsMensajes", $tsMP->getMensajes(2, $unread)),
        'enviados' => $smarty->assign("tsMensajes", $tsMP->getMensajes(3)),
        'respondidos' => $smarty->assign("tsMensajes", $tsMP->getMensajes(4)),
        'search' => $smarty->assign("tsMensajes", $tsMP->getMensajes(5)),
        'leer' => $smarty->assign("tsMensajes", $tsMP->readMensaje()),
        'avisos' => match (true) {
            empty($_GET['aid']) && empty($_GET['did']) => $smarty->assign("tsMensajes", $tsMonitor->getAvisos()),
            isset($_GET['aid']) => $smarty->assign("tsMensaje", $tsMonitor->readAviso($_GET['aid'])),
            isset($_GET['did']) => $tsMonitor->delAviso($_GET['did']) ? $tsCore->redirectTo($tsCore->settings['url'] . '/mensajes/avisos/') : null,
        },
        default => null,
    };
    # VARIABLE
    $smarty->assign("tsQT", $_GET['qt']);
    $smarty->assign("tsAction", $action);
}

if (empty($tsAjax)) {
    $smarty->assign("tsTitle", $tsTitle);

    include BASEPATH . 'footer.php';
}
