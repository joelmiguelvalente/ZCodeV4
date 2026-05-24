<?php

/**
 * Controlador: Administrador
 *
 * @package    ZCode
 * @subpackage Installer.Controllers
 * @version    4.0.0
 */

declare(strict_types=1);

use Install\src\utils\Helpers;
use App\Database\DB;

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

$tsTitle = "Finalizar | ZCode v4";

// Estado por defecto
$status  = true;
$message = null;

if (file_exists(dirname(__DIR__, 3) . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 3) . '/', null, false, null);
    $dotenv->load();
}

$config = [
    'driver'    => 'mysql',
    'hostname'  => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_NAME'],
    'username'  => $_ENV['DB_USER'],
    'password'  => $_ENV['DB_PASS'],
    'prefix'    => $_ENV['DB_PREFIX'],
];
try {
    DB::init($config);
} catch (Exception $e) {
    $message = $e->getMessage() . ' - code ' . $e->getCode();
    $status = false;
}

$time = time();
$uid = $_GET['uid'] ?? 0;
// CONSULTA
$data = DB::fetch("SELECT titulo, slogan, url, version FROM @configuracion WHERE tscript_id = :id", ['id' => 1]);
$user = DB::fetch("SELECT user_id, user_name FROM @miembros WHERE user_id = :uid", ['uid' => $uid]);

$siteUrl = Helpers::getScheme(true) . $data['url'];

$params = http_build_query([
   'admin' => $user['user_name'],
   'id' => $user['user_id'],
   'key' => base64_encode($_ENV['APP_KEY']),
   'pin' => base64_encode($_ENV['APP_ID']),
   'secret' => base64_encode($_ENV['APP_SECRET']),
   'title' => $data['titulo'],
   'type' => 'install',
   'url' => $siteUrl,
   'version' => $data['version']
]);

$handle = fopen(ABSPATH . '/.lock', "w");
fwrite($handle, "Sistema instalado correctamente: " . date('d.m.Y'));
fclose($handle);

// Acción del formulario
$endpoint = "http://localhost/feed/index.php?$params";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Location: $endpoint");
    die;
    #header("Location: $siteUrl");
}
