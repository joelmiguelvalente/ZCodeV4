<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

$tsPage = "saliendo";    // tsPage.tpl -> PLANTILLA PARA MOSTRAR CON ESTE ARCHIVO.

$tsLevel = 0;        // NIVEL DE ACCESO A ESTA PAGINA. => VER FAQs

$tsAjax = empty($_GET['ajax']) ? 0 : 1; // LA RESPUESTA SERA AJAX?

$tsContinue = true;    // CONTINUAR EL SCRIPT

require_once dirname(__DIR__, 2) . "/header.php"; // INCLUIR EL HEADER

$tsTitle = "Saliendo de {$tsCore->settings['titulo']}";     // TITULO DE LA PAGINA ACTUAL


// VERIFICAMOS EL NIVEL DE ACCSESO ANTES CONFIGURADO
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if (!$tsLevelMsg) {
    $tsPage = 'aviso';
    $tsAjax = 0;
    $smarty->assign("tsAviso", $tsLevelMsg);
   //
    $tsContinue = false;
}
    //
if ($tsContinue) {
    $action = $_GET['action'];

    $page = isset($_GET['p']) ? $_GET['p'] : '';
    $decode = base64_decode($page);
    $smarty->assign("tsDecode", $decode);
}

if (empty($tsAjax)) {
    $smarty->assign("tsTitle", $tsTitle);    // AGREGAR EL TITULO DE LA PAGINA ACTUAL

    include BASEPATH . "/footer.php";
}
