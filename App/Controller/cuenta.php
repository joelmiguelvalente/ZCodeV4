<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

include dirname(__DIR__, 2) . "/header.php";

use App\Models\{Core,Cuenta,User,Visitas};
use App\Helpers\{Appearance,RedesSociales};
use App\Repository\AvatarRepository;
use App\Services\ImageService;
use App\Utils\Avatar;

$Container->set(AvatarRepository::class, AvatarRepository::class);
$Container->set(ImageService::class, ImageService::class);
$Container->set(Avatar::class, Avatar::class, [ImageService::class,AvatarRepository::class]);
$Container->set(Visitas::class, Visitas::class, [Core::class,User::class]);
$Container->set(Cuenta::class, Cuenta::class, [Core::class,User::class,Visitas::class,Avatar::class]);

$tsPage  = 'cuenta';
$tsLevel = 2;
$tsAjax  = isset($_GET['ajax']) ? 1 : 0;
$tsTitle = $tsCore->settings['titulo'] . ' - ' . $tsCore->settings['slogan'];
/* -----------------------------------------------------------
 * Verificación de acceso
 * -----------------------------------------------------------*/
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);

if (!$tsLevelMsg) {
    $tsPage = 'aviso';
    $tsAjax = 0;
    $smarty->assign('tsAviso', $tsLevelMsg);

    if ($tsAjax === 0) {
        $smarty->assign('tsTitle', $tsTitle);
        include BASEPATH . 'footer.php';
    }
    return;
}

/* -----------------------------------------------------------
 * Acción principal
 * -----------------------------------------------------------*/
$action    = $_GET['action'] ?? '';
$accion    = $_GET['accion'] ?? '';
$tab       = $_GET['tab'] ?? '';
$tsCuenta  = $Container->get(Cuenta::class);

/* -----------------------------------------------------------
 * Datos para apariencia / avatar
 * -----------------------------------------------------------*/
if ($accion && in_array($accion, ['avatar', 'apariencia'], true)) {
    $smarty->assign('tsColoresValue', Appearance::getColors('base'));
    $smarty->assign('tsColoresTxt', Appearance::getColors('text'));
    $smarty->assign('tsFontFamily', Appearance::getFonts('family'));
    $smarty->assign('tsFontSize', Appearance::getFonts('size'));
   //s
    $smarty->assign('tsAvatarSocials', $tsCuenta->getAvatarSocials());
    $smarty->assign('tsAvatarSelect', $tsCuenta->getAvatarImages('avatares'));
    $smarty->assign('tsSetAvatares', $tsCuenta->getAvatarImages());
}

/* -----------------------------------------------------------
 * Estado del 2FA
 * -----------------------------------------------------------*/
$smarty->assign('tsG2FA', !empty($tsUser->info['user_secret_2fa']));

/* -----------------------------------------------------------
 * Acciones
 * -----------------------------------------------------------*/
if ($action === '') {
    include UTILITIES . '/extras/datos.php';
    include UTILITIES . '/extras/Paises.php';
    include UTILITIES . '/extras/geodata.php';

   /* Edad permitida */
    $now_year   = (int) date('Y');
    $edadMinima = (int) $tsCore->settings['c_allow_edad'];

    $max_year   = 100 - $edadMinima;
    $start_year = $now_year - $max_year;
    $end_year   = $now_year - $edadMinima;

    $smarty->assign('tsMax', $max_year);
    $smarty->assign('tsMaxY', $start_year);
    $smarty->assign('tsEndY', $end_year);

   /* Perfil */
    $tsPerfil = $tsCuenta->loadPerfil();

    $smarty->assign('tsPerfil', $tsPerfil);
    $smarty->assign('tsRedes', RedesSociales::getRedes());
    $smarty->assign('tsPrivacidad', $tsPrivacidad);

   /* Ubicación */
    $smarty->assign('tsPaises', $tsPaises);
    $smarty->assign('tsEstados', $estados[$tsPerfil['user_pais']] ?? '');
    $smarty->assign('tsMeses', $tsMeses);

   /* Bloqueos */
    $smarty->assign('tsBlocks', $tsCuenta->loadBloqueos());
} elseif ($action === 'desactivate') {
    if (!empty($_POST['validar'])) {
        echo $tsCuenta->desCuenta();
        return;
    }
}

/* -----------------------------------------------------------
 * Extras de navegación
 * -----------------------------------------------------------*/
if ($accion) {
    $smarty->assign('tsAccion', $accion);
}
if ($tab) {
    $smarty->assign('tsTab', $tab);
}

/* -----------------------------------------------------------
 * Render final
 * -----------------------------------------------------------*/
if ($tsAjax === 0) {
    $smarty->assign('tsTitle', $tsTitle);
    include BASEPATH . 'footer.php';
}
