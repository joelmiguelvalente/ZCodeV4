<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

namespace App\Traits;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

/**
 * Este trait asume que las propiedades $core y $user son inyectadas
 * desde la clase que lo utiliza, típicamente vía constructor.
 */

trait Country
{
    /**
     * Obtiene el icono y nombre del país del usuario
     *
     * @param string $country Código del país
     * @return array Array con el icono y nombre del país
    */
    public function countryUser(string $country = ''): array
    {
        include UTILITIES . '/extras/Paises.php';
        return [
            'icon' => strtolower($country ?? 'xx'),
            'name' => !empty($country) ? $tsPaises[$country] : 'unknown'
        ];
    }
}
