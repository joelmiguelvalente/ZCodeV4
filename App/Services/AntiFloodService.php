<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace App\Services;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class AntiFloodService
{
    private int $limit;

    public function __construct(int $limitSeconds)
    {
        $this->limit = $limitSeconds;
    }


    public function check(string $type = 'post', bool $throw = false, string $msg = ''): true|string
    {
        $now = time();
        $msg = $msg ?: 'No puedes realizar tantas acciones en tan poco tiempo.';

        $lastTime = $_SESSION['flood'][$type] ?? 0;
        $elapsed  = $now - $lastTime;

        if ($elapsed < $this->limit) {
            $remaining = $this->limit - $elapsed;
            $text = "0: {$msg} Inténtalo en {$remaining} segundos.";

            if ($throw) {
                throw new \RuntimeException($text);
            }

            return $text;
        }

        $_SESSION['flood'][$type] = $now;
        return true;
    }
}
