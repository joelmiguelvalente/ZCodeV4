<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
 */

declare(strict_types=1);

namespace App\Helpers;

final class Appearance
{
    public const COLORS = [
      'customizer'  => 'Customizar',
      'default'      => 'Default',
      'cyan'         => 'Cyan',
      'dracula'      => 'Dracula',
      'green'        => 'Verde',
      'monokai'      => 'Monokai',
      'onedark'      => 'One Dark',
      'orange'       => 'Naranja',
      'purple'       => 'Purpura',
      'red'          => 'Rojo',
      'sky'          => 'Cielo',
      'slate'        => 'Pizarra'
    ];

    public const FAMILY = [
      'arial'       => 'Arial',
        'century'   => 'Century gothic',
        'dinpro'    => 'Dinpro',
        'inter'         => 'Inter',
        'neomatrix' => 'Neomatrix Code',
        'pixellari' => 'Pixellari',
        'roboto'    => 'Roboto Mono',
        'system'    => 'Del sistema',
        'tema'      => 'Fuente del tema',
        'ubuntu'    => 'Ubuntu Mono',
    ];

    public const SIZE = [
        'xs' => 'Muy pequeña',
        'sm' => 'Pequeña',
        'md' => 'mediana (Recomendada)',
        'lg' => 'Grande',
        'xl' => 'Muy grande'
    ];

    public static function getColors(?string $type = null): string|array
    {
        $array = [];
        foreach (self::COLORS as $color => $nombre) {
              $array['base'][] = $color;
              $array['text'][] = $nombre;
        }
        return $type ? $array[$type] : $array;
    }

    public static function getNameColors(int $type = 0): string|array
    {
        $array = [];
        $i = 0;
        foreach (self::COLORS as $color => $nombre) {
            $array[$i] = $color;
            $i++;
        }
        return $type ? $array[$type] : $array;
    }

    public static function getSchemes(?int $type = 0, bool $inArray = true): string|array
    {
        $schemes = ['light', 'dark'];
        return $inArray ? $schemes : $schemes[$type];
    }

    public static function getFonts(?string $type = null): string|array
    {
        $array = [
        'family' => self::FAMILY,
        'size' => self::SIZE
        ];
        return $type ? $array[$type] : $array;
    }
}
