<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
*/

declare(strict_types=1);

if (!defined('ZCODE_ULTIMATE')) {
   exit('No direct script access allowed');
}

/*
|--------------------------------------------------------------------------
| CONSTANTES DEL SISTEMA
|--------------------------------------------------------------------------
*/
$logsDir = BASEPATH . 'storage/logs/%s_%s.log';
$date = date('d_m_y');
define('ERROR_LOG',     sprintf($logsDir, 'Application', $date));
define('DASHBOARD_LOG', sprintf($logsDir, 'Dashboard', $date));
define('MYSQLI_LOG',    sprintf($logsDir, 'Connection', $date));
define('EMAIL_LOG',     sprintf($logsDir, 'PHPMailer', $date));

/*
|--------------------------------------------------------------------------
| FLAGS DE DEPURACIÓN
|--------------------------------------------------------------------------
*/

const DEBUG              = true;
const DEBUG_FULL         = true;
const DEBUG_PRINT_SCREEN = true;

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN DE ERRORES
|--------------------------------------------------------------------------
*/
error_reporting(DEBUG_FULL ? E_ALL : (DEBUG ? (E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED) : 0));

ini_set('display_errors', DEBUG ? '1' : '0');
ini_set('log_errors',     '1');
ini_set('error_log',      ERROR_LOG);

/*
|--------------------------------------------------------------------------
| LIMPIEZA DE LOGS ANTIGUOS
|--------------------------------------------------------------------------
*/

$days      = 1;
$threshold = time() - ($days * 86400);
$logFiles  = glob($logsDir . '*.log') ?: [];

foreach ($logFiles as $file) {
   if (filemtime($file) < $threshold) {
      @unlink($file);
   }
}
