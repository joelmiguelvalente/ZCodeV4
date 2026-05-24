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
use App\Repository\AvatarRepository;
use App\Services\ImageService;
use App\Utils\{Avatar,PasswordHandler};
use App\Models\{Core,Email};
use App\Contexts\EmailContext;

define('TS_IMAGES', ABSPATH . '/assets/images/');
define('TS_AVATARES', ABSPATH . '/assets/images/avatares');
define('TS_AVATAR', ABSPATH . '/storage/avatar');
define('UTILITIES', ABSPATH . '/app/utils/');

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

$tsTitle = "Administrador | ZCode v4";

// Estado por defecto
$status  = true;
$message = null;

$default = [
    'user_name'      => Helpers::getMethod('user_name', 'post'),
    'user_email'     => Helpers::getMethod('user_email', 'post'),
    'user_password' => Helpers::getMethod('user_password', 'post'),
    'confirmar'      => Helpers::getMethod('confirmar', 'post')
];

if (file_exists(dirname(__DIR__, 3) . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 3) . '/', null, false, null);
    $dotenv->load();
}

// Acción del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = [
        'driver'        => 'mysql',
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

    # Validar campos vacíos
    if (in_array('', $default, true)) {
        $status  = false;
        $message = "Todos los campos son requeridos.";
    }

   # Validar nick (solo si no hay errores previos)
    if ($status && !Helpers::isValidNick($default['user_name'])) {
        $status  = false;
        $message = "El nombre de usuario no es válido.";
    }

   # Validar email
    if ($status && !Helpers::isEmail($default['user_email'])) {
        $status  = false;
        $message = "Debe introducir un email válido.";
    }

   # Validar password
    if ($status && !Helpers::isStrongPassword($default['user_password'])) {
        $status  = false;
        $message = "La contraseña no cumple con los requisitos mínimos.";
    }

   # Confirmación de contraseña
    if ($status && $default['user_password'] !== $default['confirmar']) {
        $status  = false;
        $message = "Las contraseñas no coinciden.";
    }

    if ($status) {
     # CONTRASEÑA HASHEADA
        $hashed = (new PasswordHandler())->create($default['user_password']);
        $time = time();
        #
        DB::execute("INSERT INTO @miembros (user_name, user_password, user_email, user_rango, user_registro, user_puntosxdar, user_activo) VALUES (:name, :pass, :email, 1, :time, 50, 1)", [
            'name' => $default['user_name'],
            'pass' => $hashed,
            'email' => $default['user_email'],
            'time' => $time
        ]);
         $uid = DB::lastInsertId();
         # Creamos el avatar del usuario
         $Avatar = new Avatar(new ImageService(), new AvatarRepository());
         $Avatar->copyAvatar((int)$uid, 'none');
         # INSERTAMOS NUEVOS DATOS
         $data = [
            [
               "sql"  => "INSERT INTO @perfil (user_id, user_sexo) VALUES (:uid, 'none')",
               "bind" => ['uid' => $uid]
            ], [
               "sql"  => "INSERT INTO @perfil_avatar (uavatar_id, uavatar_use) VALUES (:uid, 'web')",
               "bind" => ['uid' => $uid]
            ], [
               "sql"  => "INSERT INTO @portal (user_id) VALUES (:uid)",
               "bind" => ['uid' => $uid]
            ], [
               "sql"  => "UPDATE @posts SET post_user = :uid, post_category = 33, post_date = :time WHERE post_id = 1",
               "bind" => ['uid' => $uid, 'time' => $time]
            ], [
               "sql"  => "UPDATE @stats SET stats_time_foundation = :time, stats_time_upgrade = :time WHERE stats_no = 1",
               "bind" => ['time' => $time]
            ]
         ];

         foreach ($data as $q) {
             DB::execute($q['sql'], $q['bind']);
         }

         $data = DB::fetch("SELECT url FROM @configuracion WHERE tscript_id = :id", ['id' => 1]);
         # DAMOS BIENVENIDA POR CORREO
         $smpt = require_once ABSPATH . '/config/phpmailer.php';
         $plantilla = require_once UTILITIES . '/emails/sitio_creado.php';

         $EmailContext = new EmailContext(new Core());
         $tsEmail = new Email($EmailContext, $smpt);
         if (
            $tsEmail->to($default['user_email'])
             ->subject('welcome')
             ->template('sitio_creado')
             ->body($plantilla)
             ->withVars([
             '{titulo}'    => Helpers::getScheme(true) . $data['url'],
             '{username}' => $default['user_name'],
             '{password}' => $default['user_password'],
             '{version}'  => Helpers::version()
             ])->send()
         ) {
             header("Location: ?action=finalizar&uid=" . $uid);
         }
    }
}
