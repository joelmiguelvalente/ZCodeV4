<?php

/**
 * Controlador: Licencia
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

$tsTitle = "Licencia | ZCode v4";

// Estado por defecto
$status  = true;
$message = null;
$license = htmlspecialchars(file_get_contents(ABSPATH . '/LICENSE'));

// Acción del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accepted = isset($_POST['accept_license']) && $_POST['accept_license'] === '1';

    if (!$accepted) {
        $status  = 'error';
        $message = 'Debe aceptar la licencia para continuar.';
    } else {
       // Guardamos el estado de aceptación en sesión
        $_SESSION['install']['license'] = true;

        Helpers::replaceInEnv([
            'appdebug' => (Helpers::isLocalhost() ? 'TRUE' : 'FALSE'),
            'appenvironment' => Helpers::generateInstall('Mode'),
            'appid' => Helpers::generateInstall('ID'),
            'appkey' => Helpers::generateInstall('Key', 12),
            'appsecret' => Helpers::generateInstall('Secret')
        ]);

       // Redirigimos al siguiente paso
        header('Location: ?action=requisitos');
        exit;
    }
}
