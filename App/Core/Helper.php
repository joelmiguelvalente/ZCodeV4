<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace App\Core;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class Helper
{
    public static function isInstallerNeeded(): bool
    {
        return !file_exists(dirname(__DIR__, 2) . '/.env') || ($_ENV['ZCODE_DB_HOST'] ?? '') === 'dbhost';
    }

    public static function ensureLogDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }
}
