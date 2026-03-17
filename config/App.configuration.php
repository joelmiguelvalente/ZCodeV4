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
define('BASEPATH', dirname(__DIR__, 1) . DIRECTORY_SEPARATOR);

require_once __DIR__ . '/../vendor/autoload.php';
if(file_exists(dirname(__DIR__, 1) . '/.env')) {
   $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1) . '/', null, false, null);
   $dotenv->load();
}

require_once __DIR__ . '/config.define.php';
require_once __DIR__ . '/config.routes.php';

// Sesion
if(!isset($_SESSION)) session_start();

header('Content-Type: text/html; charset=utf-8');

// Establece el encabezado Cache-Control con max-age de un ano
header("Cache-Control: max-age=31536000");

// Limite de ejecucion
set_time_limit(300);
