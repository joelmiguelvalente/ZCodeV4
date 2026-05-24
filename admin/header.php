<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

defined('ZCODE_ULTIMATE') or define('ZCODE_ULTIMATE', true);

require_once dirname(__DIR__, 1) . '/config/App.configuration.php';
//require_once __DIR__ . '/AppRoutes.php';

// Sesión
if (!isset($_SESSION)) {
    session_start();
}

header('Content-Type: text/html; charset=utf-8');

// Establece el encabezado Cache-Control con max-age de un año
header("Cache-Control: max-age=31536000");

// Límite de ejecución
set_time_limit(300);
define('TS_TEMA', 'default');

/*
 * -------------------------------------------------------------------
 *  Agregamos los archivos globales
 * -------------------------------------------------------------------
 */
include UTILITIES . '/Functions.php';

use Admin\models\{Core,User,Monitor,Actividad,Mensajes,Smarty};
use App\Core\Container;
use App\Database\DB;
use App\Http\middlewares\CorsMiddleware;
use App\Utils\OAuthentication;
use App\Themes\Theme;

(new CorsMiddleware())->handle($_REQUEST);

$Container = new Container();
# Nueva forma de conexion con la base de datos
# para realizar consultas
$db = require_once BASEPATH . '/config/db.php' ;
DB::init($db);

$Container->set(Core::class, Core::class);
$Container->set(User::class, User::class);
$Container->set(Monitor::class, Monitor::class);
$Container->set(Actividad::class, Actividad::class);
$Container->set(Mensajes::class, Mensajes::class);
$Container->set(Smarty::class, Smarty::class);
$Container->set(Theme::class, Theme::class);

$tsCore = $Container->get(Core::class);
$tsUser = $Container->get(User::class);
$tsMonitor = $Container->get(Monitor::class);
$tsActividad = $Container->get(Actividad::class);
$tsMP = $Container->get(Mensajes::class);
$smarty = $Container->get(Smarty::class);
$Theme      = $Container->get(Theme::class);

// Configuraciones
$smarty->assign('tsConfig', $tsCore->settings);
$smarty->assign('tsRoutes', $tsCore->route());
$smarty->assign('tsNews', $tsCore->getNews());

// Obtejo usuario
$smarty->assign('tsUser', $tsUser);

// Avisos
$smarty->assign('tsAvisos', $tsMonitor->avisos);

// Nofiticaciones
$smarty->assign('tsNots', $tsMonitor->notificaciones);

// Mensajes
$smarty->assign('tsMPs', $tsMP->mensajes);

include UTILITIES . '/extras/MenuUserAccount.php';
$smarty->assign('tsMenuCuenta', $MenuCuenta);

$smarty->assign('Theme', $Theme);
