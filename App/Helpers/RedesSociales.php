<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
 */

declare(strict_types=1);

namespace App\Helpers;

final class RedesSociales
{
    public const REDES = [
      'facebook'  => 'https://facebook.com',
      'twitter'   => 'https://twitter.com',
      'instagram' => 'https://instagram.com',
      'youtube'   => 'https://youtube.com',
      'twitch'    => 'https://twitch.tv',
      'tiktok'    => 'https://www.tiktok.com/@',
      'discord'   => 'https://discord.com/users',
      'reddit'    => 'https://www.reddit.com/user'
    ];

    public static function getRedes(): array
    {
        return array_map(fn($url, $key) => [
         'icon'   => $key,
         'folder' => 'social',
         'nombre' => ucfirst($key),
         'url'    => $url
        ], self::REDES, array_keys(self::REDES));
    }
}

// Uso:
//$redes = RedesSociales::getRedes();
