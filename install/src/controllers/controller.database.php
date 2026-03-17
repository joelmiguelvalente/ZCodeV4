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
use App\Database\{DB,Runner};

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

$tsTitle = "Base de datos | ZCode v4";

// Estado por defecto
$status  = true;
$message = null;
$default = [
    'hostname'   => Helpers::getMethod('hostname', 'post'),
    'database'   => Helpers::getMethod('database', 'post'),
    'username'   => Helpers::getMethod('username', 'post'),
    'password'   => Helpers::getMethod('password', 'post') ?? ''
];

// Obtener el prefijo enviado
$prefix = Helpers::getMethod('prefix', 'post', 'zc4_');
$prefix = strtolower(trim($prefix));

// Si NO termina en "_", lo agregamos
if (!str_ends_with($prefix, '_')) {
    $prefix .= '_';
}

// Finalmente lo guardamos en default
$default['prefix'] = $prefix;

// Acción del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($default['hostname']) || empty($default['database']) || empty($default['username'])) {
        $message = "Todos los campos son requeridos";
        $status = false;
    }

    try {
        DB::init([
            'driver' => 'mysql',
            ...$default
        ]);
        # Si todo es correcto, guardamos los datos de conexión
        Helpers::replaceInEnv($default);
    } catch (Exception $e) {
        $message = $e->getMessage() . ' - code ' . $e->getCode();
        $status = false;
    }

    # Mostramos las tablas si es que fue instalado y se borró .env|.lock
    if ($show_tables = DB::fetchAll("SHOW TABLES")) {
        foreach ($show_tables as $row) {
            $name = array_values($row)[0];
            DB::execute("DROP TABLE IF EXISTS `{$name}`");
        }
    }

    $runner = new Runner($default['prefix']);

    $migrationsRun = $runner->runAndLog('migrations', 'migrations.json');
    $seedersRun    = $runner->runAndLog('seeders', 'seeders.json');

    $success = array_filter(
        array_merge($migrationsRun, $seedersRun),
        fn($e) => $e['status'] === 'success'
    );

    //
    if (!empty($success) && $success) {
        header('Location: ?action=sitio');
        exit;
    }
}
