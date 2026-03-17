<?php

/**
 * Controlador: PHPMailer (opcional)
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

if (!$_SESSION['install']['license']) {
    if (file_exists(dirname(__DIR__, 2) . '/.env')) {
        unlink(dirname(__DIR__, 2) . '/.env');
    }
    header('Location: ?action=bienvenida');
    exit;
}

$tsTitle = "PHPMailer | ZCode v4";

// Estado por defecto
$status  = true;
$message = null;

$default = [
    'smtphost' => Helpers::getMethod('smtphost', 'post'),
    'smtpuser' => Helpers::getMethod('smtpuser', 'post'),
    'smtppass' => str_replace(' ', '', Helpers::getMethod('smtppass', 'post') ?? ''),
    'smtpname' => str_replace(' ', '', Helpers::getMethod('smtpname', 'post') ?? ''),
    'smtpport' => Helpers::getMethod('smtpport', 'post')
];

// Acción del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (in_array('', $default, true)) {
        $message = "Todos los campos son requeridos";
        $status = false;
    }

    if ((int)Helpers::getMethod('skip_smtp', 'post') === 1) {
        header('Location: ?action=administrador');
        exit;
    }
    if ($status && (int)Helpers::getMethod('next_smtp', 'post') === 1) {
        # Si todo es correcto, guardamos los datos de conexión
        Helpers::replaceInEnv($default);
        header('Location: ?action=administrador');
        exit;
    }
}
