<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

use App\Models\Buscador;

$tsPage = "buscador";

$tsLevel = 0;

$tsAjax = empty($_GET['ajax']) ? 0 : 1;

$tsContinue = true;

include dirname(__DIR__, 2) . "/header.php";  // INCLUIR EL HEADER

$tsTitle = $tsCore->settings['titulo'] . ' - ' . $tsCore->settings['slogan'];   // TITULO DE LA PAGINA ACTUAL

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
    $query = htmlspecialchars($_GET['query'] ?? '');
    $engine = htmlspecialchars($_GET['engine'] ?? '');
    $author = htmlspecialchars($_GET['autor'] ?? '');
    $category = (int)$_GET['category'] ?? '-1';
    $tsBuscador = new Buscador();

    if ($engine !== 'google') {
        $smarty->assign("tsResults", $tsBuscador->getQuery());
    }
    //
    $smarty->assign("tsQuery", $query);
    $smarty->assign("tsEngine", $engine);
    $smarty->assign("tsCategory", $category);
    $smarty->assign("tsAutor", $author);
}

if (empty($tsAjax)) {
    $smarty->assign("tsTitle", $tsTitle);

    include BASEPATH . 'footer.php';
}
