<?php

/**
 * Controlador: Requisitos
 *
 * @package    ZCode
 * @subpackage Installer.Controllers
 * @version    4.0.0
 */

declare(strict_types=1);

use Install\src\utils\Helpers;

// Seguridad básica: acceso solo por el instalador
if (!defined('ZCODE_ULTIMATE')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

// Estado por defecto
$status  = true;
$message = null;
$allSystemOk = true;
$allPathsOk  = true;

$version_support = '8.3';
$systemCheck = [
    'PHP >= ' . $version_support    => version_compare(PHP_VERSION, $version_support, '>='),
    'Extensión GD'                  => (extension_loaded('gd') and function_exists('gd_info')),
    'MySQLi/PDO disponible'         => class_exists('mysqli') and class_exists('pdo'),
    'cURL habilitado'               => function_exists('curl_init'),
    'mbstring habilitado'           => extension_loaded('mbstring'),
    'ZIP habilitado'                    => extension_loaded('zip'),
    '.htaccess presente'            => file_exists(ABSPATH . '/.htaccess'),
    '.env presente'                 => file_exists(ABSPATH . '/.env'),
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

foreach ($systemCheck as $label => $check) {
    $row = Helpers::makeStatus($check, $label);

    if ($row['class'] === 'danger') {
        $allSystemOk = false;
        $message = "Debes cumplir los requisitos mínimos del sistema.";
    }

    $systemStatus[$label] = [
      ...$row,
      'icon'    => $row['class'] === 'success' ? '✔' : '✖',
      'subtext'    => $row['class'] === 'success' ? 'Correcto' : 'Incorrecto',
      'current' => match ($label) {
         default => null,
         'PHP >= ' . $version_support => PHP_VERSION
      }
    ];
}
//
foreach ($verfyPath as $label => $path) {
    $created = false;

    if (!is_dir($path)) {
        $created = mkdir($path, 0755, true);
    }

    $realPath = str_replace(ABSPATH, '..', $path);
    $perm = (int) substr(sprintf('%o', fileperms($path)), -3);

    $allIsCorrect = ($perm >= 755);

    if (!$allIsCorrect) {
        $allPathsOk = false;
        $message = "Debes otorgar permisos 755 o superiores a las carpetas.";
    }

    $pathCheck[$label] = [
      'chmod'   => $perm,
      'class'   => $allIsCorrect ? 'success' : 'danger',
      'text'    => $allIsCorrect ? 'Correcto' : 'Incorrecto',
      'route'   => $realPath,
      'created' => $created,
      'icon'    => $allIsCorrect ? '✔' : '✖'
    ];
}

// Acción del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verificar = isset($_POST['verificar']) && $_POST['verificar'] === 'si';
    if ($verificar) {
        header('Location: ?action=requisitos&verificado=true');
        die;
    }

    header('Location: ?action=database');
    die;
}
