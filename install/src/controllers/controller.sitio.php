<?php

/**
 * Controlador: Datos del sitio
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

$tsTitle = "Datos del sitio | ZCode v4";

// Estado por defecto
$status  = true;
$message = null;
$default = [
    'titulo'    => Helpers::getMethod('titulo', 'post'),
    'slogan'    => Helpers::getMethod('slogan', 'post'),
    'url'       => Helpers::getMethod('url', 'post'),
    'email'  => Helpers::getMethod('email', 'post'),
    'pkey'   => Helpers::getMethod('pkey', 'post'),
    'skey'   => Helpers::getMethod('skey', 'post')
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

    if (!Helpers::isEmail($default['email'])) {
        $message = 'Debe ser un correo válido.';
        $status = false;
    }

    # Actualizamos la categoría
    DB::execute("UPDATE @posts_categorias SET c_nombre = :nombre, c_seo = :seo WHERE cid = :id LIMIT 1", [
        'nombre' => $default['titulo'],
        'seo' => Helpers::slugify($default['titulo']),
        'id' => 33
    ]);

    # Actualizamos SEO
    DB::execute("UPDATE @seo SET seo_titulo = :titulo, seo_descripcion = :descripcion, seo_portada = :portada, seo_keywords = :keywords WHERE seo_id = :id", [
        'titulo' => "{$default['titulo']} - {$default['slogan']}",
        'descripcion' => 'Únete a nuestra comunidad para compartir experiencias y conocer gente nueva. ¡Conéctate hoy mismo!',
        'portada' => '/assets/images/favicon/logo-512.webp',
        'keywords' => 'comunidad, conocer, red, ampliar, interaccion, compartir, amigos, conectar, relaciones, intereses, encuentros, virtual',
        'id' => 1
    ]);

    # Actualizamos el sitio
    try {
        DB::execute("UPDATE @configuracion SET titulo = :titulo, slogan = :slogan, tema = :tema, url = :url, email = :email, pkey = :pkey, skey = :skey, version = :version, version_code = :version_code WHERE tscript_id = :id", [
            ...$default,
            'tema' => 'default',
            'version' => Helpers::version('full'),
            'version_code' => Helpers::version('code'),
            'id' => 1
        ]);
        $status = true;
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $status = false;
    }

    if ($status) {
        # Si se guardo bien, continuamos
        header('Location: ?action=phpmailer');
        exit;
    }
}
