<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

require_once dirname(__DIR__, 2) . "/header.php";

#require_once UTILITIES . '/extras/Paises.php';

use App\Models\{Cuenta,Muro};
use App\Helpers\{RedesSociales,UserHelper};

$Container->set(Cuenta::class, Cuenta::class);
$Container->set(Muro::class, Muro::class);
$Container->set(UserHelper::class, UserHelper::class);

$tsPage = "perfil";

$tsLevel = 0;

$tsAjax = empty($_GET['ajax']) ? 0 : 1;

$tsContinue = true;

$tsTitle = $tsCore->settings['titulo'];

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
    $UserHelper = $Container->get(UserHelper::class);
    $usuario = $UserHelper->getStatusUser();
    $uid = (int)$usuario['user_id'];

    // EXISTE?
    if ($usuario['noExiste'] || $usuario['inactivo'] || $usuario['baneado']) {
        $tsPage = 'aviso';
        $tsAjax = 0;
        $smarty->assign("tsAviso", [
            'titulo' => 'Opps!',
            'mensaje' => $uid === 0 ? 'El usuario no existe' : "La cuenta de {$usuario['user_name']} se encuentra inhabilitada",
            'but' => 'Ir a p&aacute;gina principal'
        ]);
    } else {
        $tsCuenta = $Container->get(Cuenta::class);

        $tsInfo = $tsCuenta->loadHeadInfo($uid);
        $tsInfo['uid'] = $uid;
        // IS ONLINE?
        $tsInfo['status'] = $tsCore->statusUser($uid);
        // GENERAL
        $tsGeneral = $tsCuenta->loadGeneral($uid);
        $tsInfo['nick'] = $tsInfo['user_name'];
        $tsInfo = array_merge($tsInfo, $tsGeneral);
        // PAIS
        $tsInfo['pais'] = [
            'icon' => $tsInfo['user_pais'],
            'name' => $tsPaises[$tsInfo['user_pais']] ?? ''
        ];
        // LO SIGO?
        $tsInfo['follow'] = $tsCuenta->iyfollow($uid, 'iFollow');
        // ME SIGUE?
        $tsInfo['yfollow'] = $tsCuenta->iyfollow($uid, 'yFollow');
        // MANDAR A PLANTILLA
        $smarty->assign("tsInfo", $tsInfo);
        $smarty->assign("tsRedes", RedesSociales::getRedes());
        $smarty->assign("tsGeneral", $tsGeneral);
        // MURO
        $tsMuro = $Container->get(Muro::class);
        // PERMISOS
        $privacity = $tsMuro->getPrivacity((int)$uid, $tsInfo['nick'], (int)$tsInfo['follow'], (int)$tsInfo['yfollow']);
        // SE PERMITE VER EL MURO?
        if ($privacity['m']['v'] == true) {
            // Determinar el tipo de contenido a cargar
            $tsType = 'wall';
            $tsData = null;
            // CARGAR HISTORIA
            if (!empty($_GET['pid'])) {
                $pub_id = $tsCore->setSecure($_GET['pid']);
                $story = $tsMuro->getStory($pub_id, $uid);
                //
                if (!is_array($story)) {
                    $tsPage = 'aviso';
                    $smarty->assign("tsAviso", [
                        'titulo' => 'Opps...',
                        'mensaje' => $story,
                        'but' => 'Ir a pagina principal',
                        'link' => $tsCore->settings['url']
                    ]);
                } else {
                    $story['data'][1] = $story;
                    $tsType = 'story';
                    $tsData = $story;
                }
            } elseif ((int)$tsCore->settings['c_allow_portal'] == 0 && $tsInfo['uid'] == $tsUser->uid) {
                $tsType = 'news';
                $tsData = $tsMuro->getNews();
            } else {
                $tsType = 'wall';
                $tsData = $tsMuro->getWall($uid);
            }
            $smarty->assign("tsMuro", $tsData);
            $smarty->assign("tsType", $tsType);
        }
        $smarty->assign("tsPrivacidad", $privacity);
        // TITULO
        $tsTitle = 'Perfil de ' . $tsInfo['nick'] . ' - ' . $tsTitle;
    }
}

if (empty($tsAjax)) {
    $smarty->assign("tsTitle", $tsTitle);

    include BASEPATH . 'footer.php';
}
