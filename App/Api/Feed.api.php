<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

if (! defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Database\DB;

$files = [
   'feed-support' => ['n' => 4, 'p' => ''],
   'feed-version' => ['n' => 4, 'p' => ''],
];

// REDEFINIR VARIABLES
$tsPage = 'php_files/p.live.' . $files[$action]['p'];

$tsLevel = $files[$action]['n'] ?? 0;

$tsAjax = empty($files[$action]['p']) ? 1 : 0;

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if (!$tsLevelMsg) {
    echo '0: ' . $tsLevelMsg['mensaje'];
    die();
}

//
$params = http_build_query([
   'title' => $tsCore->settings['titulo'],
   'url' => $tsCore->settings['url'],
   'version' => $tsCore->settings['version'],
   'admin' => $tsUser->nick,
   'id' => $tsUser->uid,
   'key' => base64_encode($_ENV['APP_KEY']),
   'pin' => base64_encode($_ENV['APP_ID']),
   'secret' => base64_encode($_ENV['APP_SECRET']),
   'type' => explode('-', $action)[1] ?? '',
]);

$endpoint = ($_ENV['ENVIRONMENT'] === 'DEVELOPMENT' ? 'http://localhost' : 'https://zcodev.alwaysdata.net') . '/feed/index.php';

// CODIGO
switch ($action) {
    case 'feed-version':
        $time = time();
        $version_now = SCRIPT_NAME . ' ' . SCRIPT_VERSION;
        $version_code = str_replace([' ', '.'], '_', strtolower($version_now));
        # ACTUALIZAR VERSIÓN
        if ($tsCore->settings['version'] !== $version_now) {
            DB::execute("UPDATE @configuracion SET version = :version, version_code = :code WHERE tscript_id = 1 LIMIT 1", [
                'version' => $version_now,
                'code' => $version_code
            ]);
            DB::execute("UPDATE @stats SET stats_time_upgrade = :time WHERE stats_no = 1 LIMIT 1", ['time' => $time]);
        }
        break;
    case 'feed-support':
        // Solo para que no se corte
        break;
    default:
        die('0: Este archivo no existe.');
    break;
}

if (in_array($action, ['feed-support', 'feed-version'])) {
    $json = $tsCore->getUrlContent("$endpoint?$params");
    echo json_encode($json);
}
