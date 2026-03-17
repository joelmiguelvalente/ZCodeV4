<?php
/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
 */

declare(strict_types=1);

use App\Http\MiddlewareKernel;
use App\Themes\ThemeResolver;
use App\Views\ViewComposer;

/*
|--------------------------------------------------------------------------
| CONSTANTES BÁSICAS DEL SISTEMA
|--------------------------------------------------------------------------
*/
defined('ZCODE_ULTIMATE') or define('ZCODE_ULTIMATE', true);

defined('SCRIPT_VERSION') or define(
    'SCRIPT_VERSION',
    trim(file_get_contents(__DIR__ . '/.version'))
);

defined('SCRIPT_NAME') or define('SCRIPT_NAME', 'ZCode');
defined('SCRIPT_AUTHOR') or define('SCRIPT_AUTHOR', 'Miguel92');

/*
|--------------------------------------------------------------------------
| BOOTSTRAP: AUTOLOAD + CONTENEDOR + CONFIG SISTEMA
|--------------------------------------------------------------------------
*/
$app = require __DIR__ . '/bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| TEMA
|--------------------------------------------------------------------------
*/
define('TS_TEMA', (new ThemeResolver)->resolve($tsCore));

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN DE SMARTY
|--------------------------------------------------------------------------
| Mantener esto limpio es crítico. Nada de lógica mezclada con asignaciones.
|--------------------------------------------------------------------------
*/
$smarty->output(false);

/*
|--------------------------------------------------------------------------
| MIDDLEWARES
|--------------------------------------------------------------------------
*/
(new MiddlewareKernel)->run($tsCore, $tsUser, $smarty);

/*
|--------------------------------------------------------------------------
| VIEW COMPOSER
|--------------------------------------------------------------------------
*/
(new ViewComposer)->compose($smarty, $tsCore, $tsUser, $tsMonitor, $tsMP, $Theme, $Seo);