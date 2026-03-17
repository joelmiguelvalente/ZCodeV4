<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
 */

declare(strict_types=1);

use App\Contexts\{SystemContext,UserContext};
use App\Core\Container;
use App\Database\DB;
use App\Debug\ErrorHandler;
use App\Http\Middlewares\CorsMiddleware;
use App\Models\{Actividad,Autenticar,Core,Mensajes,Monitor,Seo,Session,Smarty,User};
use App\Repository\AvatarRepository;
use App\Services\{ContentService,LoggerService,ImageService};
use App\Themes\Theme;
use App\Utils\{Avatar,Images,PasswordHandler};

require_once __DIR__ . '/../config/App.configuration.php';
require_once HELPERS . '/polyfills.php';

# Será reemplazado por App\Database\DB
require_once UTILITIES . '/Functions.php';

#ErrorHandler::register(true);

(new CorsMiddleware())->handle($_REQUEST);

$Container = new Container();

# Nueva forma de conexion con la base de datos para realizar consultas
$db = require_once BASEPATH . '/config/db.php' ;
DB::init($db);

# REGISTRO: SIN DEPENDENCIAS
$Container->set(LoggerService::class, LoggerService::class);
$Container->set(ImageService::class, ImageService::class);
$Container->set(AvatarRepository::class, AvatarRepository::class);
$Container->set(PasswordHandler::class, PasswordHandler::class);
# REGISTRO: PRINCIPAL
$Container->set(Core::class, Core::class);
$Container->set(User::class, User::class);
$Container->set(Session::class, Session::class);
# REGISTRO: CONTEXTS
$Container->set(HomeContext::class, HomeContext::class);
$Container->set(UserContext::class, UserContext::class);
# REGISTRO: SERVICES
$Container->set(ContentService::class, ContentService::class);
# REGISTRO: ADICIONALES
$Container->set(Images::class, Images::class);
$Container->set(Avatar::class, Avatar::class);
# REGISTRO: MODELS
$Container->set(Actividad::class, Actividad::class);
$Container->set(Autenticar::class, Autenticar::class);
$Container->set(Mensajes::class, Mensajes::class);
$Container->set(Monitor::class, Monitor::class);
$Container->set(Seo::class, Seo::class);
$Container->set(Smarty::class, Smarty::class);
$Container->set(Theme::class, Theme::class);
$Container->set(Visitas::class, Visitas::class);

// Obtenemos los servicios
$tsCore     = $Container->get(Core::class);
$tsUser     = $Container->get(User::class);
$tsMonitor  = $Container->get(Monitor::class);
$smarty     = $Container->get(Smarty::class);
$tsMP       = $Container->get(Mensajes::class);
$Theme      = $Container->get(Theme::class);
$Seo        = $Container->get(Seo::class);

/*$smpt = require_once __DIR__ . '/../config/phpmailer.php';
use App\Models\Email;
use App\Contexts\EmailContext;
$Container->set(EmailContext::class, EmailContext::class, [Core::class]);
$Container->set(Email::class, Email::class, [EmailContext::class, $smpt]);

$tsEmail = $Container->get(Email::class);
$tsEmail->to('joel92@live.com.ar')
->subject('welcome')
->template('sitio_creado')
->body('Este es el contenido del mensaje que se vera en la plantilla')
->withVars([
   //'{ZCODE_LINK}' => $tsCore->route('url'),
   '{titulo}'   => $tsCore->settings['titulo'],
   '{username}'   => 'Miguel92',
   '{password}'   => '123456789',
   '{version}' => 'v4.0.0'
])
->send();
die;*/
