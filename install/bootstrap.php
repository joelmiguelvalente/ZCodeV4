<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
*/

declare(strict_types=1);

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

# Definimos variables
$action         = isset($_GET['action']) ? htmlentities((string) $_GET['action']) : 'licencia';
$allows_steps   = ['bienvenida', 'licencia', 'requisitos', 'datos', 'finalizar', 'fallo'];
$continue       = true;
$environment    = ABSPATH . '/.env';
$lock               = ABSPATH . '/.lock';
$message            = "";
$sample             = ABSPATH . '/.env.example';
$version            = 'ZCode v4.0.0';
$version_code   = slugify($version);

$version_support = '8.3';
$systemCheck = [
    'PHP >= ' . $version_support    => version_compare(PHP_VERSION, $version_support, '>='),
    'Extensión GD'                  => (extension_loaded('gd') and function_exists('gd_info')),
    'MySQLi/PDO disponible'         => class_exists('mysqli') and class_exists('pdo'),
    'cURL habilitado'               => function_exists('curl_init'),
    'mbstring habilitado'           => extension_loaded('mbstring'),
    'ZIP habilitado'                    => extension_loaded('zip'),
    '.htaccess presente'            => file_exists(ABSPATH . '/.htaccess'),
];

// Verificación de permisos de carpetas
$path = ABSPATH . '/storage';
$verfyPath = [
    'avatar' => "{$path}/avatar",
    'cache' => "{$path}/cache",
    'logs' => "{$path}/logs",
    'portadas' => "{$path}/portadas",
    'uploads' => "{$path}/uploads"
];

$systemStatus = [];
$pathCheck = [];
