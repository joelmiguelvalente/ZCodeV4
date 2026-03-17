<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
*/

declare(strict_types=1);

if (!defined('ZCODE_ULTIMATE')) {
    define('ZCODE_ULTIMATE', true);
}

error_reporting(E_ALL);
date_default_timezone_set('America/Argentina/Buenos_Aires');

$fileenv = dirname(__DIR__, 2) . '/.env';
if (file_exists($fileenv)) {
    $dotenv = fopen($fileenv, 'r');
    if ($dotenv) {
        while (($line = fgets($dotenv)) !== false) {
           // Ignorar comentarios y líneas vacías
            if (trim($line) === '' || strpos(trim($line), '#') === 0) {
                continue;
            }
            if (preg_match('/\A([a-zA-Z0-9_]+)=(.*)\z/', trim($line), $matches)) {
                $_ENV[$matches[1]] = $matches[2];
            }
        }
        fclose($dotenv);
    }
}

include dirname(__DIR__, 1) . '/services/HealthCheck.php';
$healthCheck = new \App\Services\HealthCheck();

include dirname(__DIR__, 1) . '/plugins/modifier.elapsed_time.php';
include dirname(__DIR__, 1) . '/plugins/modifier.hace.php';
include dirname(__DIR__, 1) . '/plugins/modifier.fecha.php';

$tsTitle = 'ZCode - Status';

// Uso
$healthCheck->run([
   'log' => false,
   'verify' => true,
   'check' => false
]);

$statusFile = dirname(__DIR__, 2) . '/storage/system_health.json';
$ZCODE_HEALTH = file_exists($statusFile) ? json_decode(file_get_contents($statusFile), true) : [];

$tiempo_ejecucion = $healthCheck->getExecutionTime();
include dirname(__DIR__, 2) . '/views/errors/status.html';
