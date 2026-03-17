<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
*/

declare(strict_types=1);

if (!defined('ZCODE_ULTIMATE')) {
   exit('No direct script access allowed');
}

define('VERSION',   BASEPATH . '.version');
define('LOCK',      BASEPATH . '.lock');

const PATHS = [
   'TS_ADMIN'        => BASEPATH . '/admin',
   'TS_ASSETS'       => BASEPATH . '/assets',
   'TS_AVATARES'     => BASEPATH . '/assets/images/avatares',
   'APPLICATION'     => BASEPATH . '/app',
   'UTILITIES'       => BASEPATH . '/app/utils',
   'HELPERS'         => BASEPATH . '/app/helpers',
   'PLUGINS'         => BASEPATH . '/app/plugins',
   'STORAGE'         => BASEPATH . '/storage',
   'TS_AVATAR'       => BASEPATH . '/storage/avatar',
   'TS_AVATAR_USER'  => BASEPATH . '/storage/avatar/user',
   'TS_BACKUP'       => BASEPATH . '/storage/backup',
   'TS_CACHE'        => BASEPATH . '/storage/cache',
   'TS_IMAGES'       => BASEPATH . '/assets/images',
   'TS_PORTADAS'     => BASEPATH . '/storage/portadas',
   'TS_UPLOADS'      => BASEPATH . '/storage/uploads',
   'TS_THEMES'       => BASEPATH . '/views/themes',
   'TS_AUTH'         => BASEPATH . '/views/auth',
   'TS_COMPONENTS'   => BASEPATH . '/views/components',
   'TS_HTML'         => BASEPATH . '/views/html',
];

foreach (PATHS as $name => $path) {
   define($name, $path);
}

set_include_path(get_include_path() . PATH_SEPARATOR . realpath('./'));