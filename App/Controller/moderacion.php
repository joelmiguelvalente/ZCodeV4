<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

use App\Models\Moderacion;

$tsPage = "moderacion";

$tsLevel = 3;

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
    // ACTION
    $action = htmlspecialchars($_GET['action'] ?? '');

    // ACTION 2
    $act = htmlspecialchars($_GET['act'] ?? '');

    // CLASE POSTS
    $tsMod = new Moderacion();

    if ($action === '') {
        $smarty->assign("tsMods", $tsMod->getMods());

        // DENUNCIAS
    } elseif (in_array($action, ['posts', 'users', 'mps', 'fotos'])) {
        // DATOS EXTRA
        include TS_JUNK . 'Denuncias.php';
        // SEGUNDA ACCION
        if (empty($act)) {
            $smarty->assign("tsReports", $tsMod->getDenuncias($action));
        } elseif ($act == 'info') {
            $smarty->assign("tsDenuncia", $tsMod->getDenuncia($action));
        }
        $smarty->assign("tsDenuncias", $tsDenuncias[$action]);

    // SUSPENSIONES
    } elseif ($action === 'banusers') {
        $smarty->assign("tsSuspendidos", $tsMod->getSuspendidos());

    //PAPELERAS
    } elseif ($action === 'pospelera') {
        $smarty->assign("tsPospelera", $tsMod->getPospelera());

    // FOTOS ELIMINADAS
    } elseif ($action === 'fopelera') {
        $smarty->assign("tsFopelera", $tsMod->getFopelera());

    // CONTENIDO DESAPROBADO
    } elseif ($action === 'revcomentarios') {
        $smarty->assign("tsComentarios", $tsMod->getComentariosD());

    // CONTENIDO DESAPROBADO
    } elseif ($action === 'revposts') {
        $smarty->assign("tsPosts", $tsMod->getPostsD());

    // BUSCADOR DE IP Y CONTENIDO
    } elseif ($action === 'buscador') {
        if ($_POST['buscar']) {
            $texto = $_POST['texto'];
            $metodo = $_POST['m'];
            $tipo = $_POST['t'];
            $tsCore->redirectTo($tsCore->settings['url'] . '/moderacion/buscador/' . $metodo . '/' . $tipo . '/' . $texto);
        } elseif ($act == 'search') {
            $smarty->assign("tsContenido", $tsMod->getContenido());
        }
    }

    // ACCION?
    $smarty->assign("tsAction", $action);
    $smarty->assign("tsAct", $act);
}

if (empty($tsAjax)) {
    $smarty->assign("tsTitle", $tsTitle);
    $smarty->assign("tsSave", $_GET['save']);

    include BASEPATH . 'footer.php';
}
