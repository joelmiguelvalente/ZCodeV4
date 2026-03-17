<?php

/**
 * Controlador: Bienvenida
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

$tsTitle = "Bienvenida | ZCode v4";

// Acción del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Redirigimos al siguiente paso
    header('Location: ?action=licencia');
    exit;
}
