<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

$tsPage = "";

$tsLevel = 0;

$tsAjax = empty($_GET['ajax']) ? 0 : 1;

require_once dirname(__DIR__, 2) . '/header.php';

$tsTitle = $tsCore->settings['titulo'] . ' - ' . $tsCore->settings['slogan'];

$action = htmlspecialchars($_GET['action'] ?? ''); # ej: admin-foto-borrar
$action_type = explode('-', $action)[0]; # ej: admin

// Determinar el archivo necesario
$file = ucfirst($action_type) . '.api.php'; # ej: Admin.api.php

// Verificar si el archivo existe y luego incluirlo
if ($file && file_exists($file)) {
    include $file;
} else {
    die("0: No se encontró el archivo solicitado: " . htmlspecialchars($file));
}

if (empty($tsAjax)) {
    $smarty->assign("tsTitle", $tsTitle);

    include BASEPATH . 'footer.php';
}
