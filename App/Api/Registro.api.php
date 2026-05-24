<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Models\{Core,User,Registro};
use App\Repository\AvatarRepository;
use App\Services\ImageService;
use App\Utils\{Avatar,PasswordHandler};

// NIVELES DE ACCESO Y PLANTILLAS DE CADA ACCI�N
$files = [
    'registro-form' => ['n' => 1, 'p' => 'form'],
    'registro-check-nick' => ['n' => 1, 'p' => ''],
    'registro-check-email' => ['n' => 1, 'p' => ''],
    'registro-geo' => ['n' => 0, 'p' => ''],
    'registro-nuevo' => ['n' => 1, 'p' => ''],
];

// REDEFINIR VARIABLES
$tsPage = 'php_files/p.registro.' . $files[$action]['p'];

$tsLevel = $files[$action]['n'];

$tsAjax = empty($files[$action]['p']) ? 1 : 0;

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);

if (!$tsLevelMsg) {
    echo '0: ' . $tsLevelMsg;
    die();
}

// CLASE
$Container->set(Core::class, Core::class);
$Container->set(Logger::class, Logger::class);
$Container->set(PasswordHandler::class, PasswordHandler::class);

$Container->set(AvatarRepository::class, AvatarRepository::class);
$Container->set(ImageService::class, ImageService::class);
$Container->set(Avatar::class, Avatar::class, [ImageService::class, AvatarRepository::class]);

$Container->set(Registro::class, Registro::class, [
    Core::class,
    User::class,
    PasswordHandler::class,
    Avatar::class
]);
$tsRegistro = $Container->get(Registro::class);

// CODIGO
switch ($action) {
    case 'registro-form':
        if ($tsCore->settings['c_reg_active'] == 0) {
            $tsAjax = '1';
            echo '0: <div class="dialog_box">El registro se encuentra momentaneamente desactivado.</div>';
        } else {
            // SOLO MENORES DE 84 A�OS xD Y MAYORES DE...
            $now_year = date("Y", time());
            // 100a�os - 16a�os = 84a�os
            $edad = (int)$tsCore->settings['c_allow_edad'];
            $max_year = 100 - $edad;
            $start_year = (int)$now_year - (int)$max_year;
            $end_year = (int)$now_year - (int)$tsCore->settings['c_allow_edad'];
            //
            $smarty->assign("tsMax", (int)$max_year);
            $smarty->assign("tsMaxY", (int)$start_year);
            $smarty->assign("tsEndY", (int)$end_year);
            $smarty->assign('OAuth', $tsCore->OAuth());
        }
        break;
    case 'registro-check-nick':
    case 'registro-check-email':
        echo $tsRegistro->checkUserEmail();
        break;
    case 'registro-geo':
        require_once UTILITIES . "/extras/geodata.php";
        $pais = isset($_GET['pais_code']) ? htmlspecialchars($_GET['pais_code']) : '';
        $paises = [];
        //
        foreach ($estados[$pais] as $key => $estado) {
            $paises[] = '<option value="' . ($key + 1) . '">' . $estado . '</option>' . "\n";
        }
        $html = implode("\n", $paises);
        //
        echo (strlen($html) > 31) ? "1: $html" : '0: Código de pais incorrecto.';
        break;
    case 'registro-nuevo':
        $result = $tsRegistro->registerUser();
        echo $result;
        break;
}
