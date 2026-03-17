<?php

/**
 * Instalador normal, con todos los pasos necesarios.
 *
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

use Install\src\utils\Helpers;

defined('ZCODE_ULTIMATE') or define('ZCODE_ULTIMATE', true);

# Definiciones
define('ABSPATH', dirname(__DIR__, 1));

session_start();

# Reportamos errores en caso que exista
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', ABSPATH . '/storage/logs/install.log');
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once ABSPATH . '/vendor/autoload.php';

# Comprobamos que no haya sido instalado!
if (file_exists(ABSPATH . '/.env') and file_exists(ABSPATH . '/.lock')) {
    header("Location: ./");
    die;
}

require_once Helpers::controller();
require_once Helpers::view('layout');
