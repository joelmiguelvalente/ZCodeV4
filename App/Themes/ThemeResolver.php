<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
 */

declare(strict_types=1);

namespace App\Themes;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class ThemeResolver
{
    public function resolve($tsCore): string
    {
        return $tsCore->settings['tema'] ?: 'default';
    }
}
