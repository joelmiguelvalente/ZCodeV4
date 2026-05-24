<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace App\Repository;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Database\DB;

class AvatarRepository
{
    public function __construct()
    {
    }

    public function getUserAvatarConfig(int $uid): ?array
    {
        $row = DB::fetch("SELECT uavatar_gif, uavatar_gif_active, uavatar_type, uavatar_social, uavatar_use FROM @perfil_avatar WHERE uavatar_id = :uid", ['uid' => $uid]);
        if (!$row) {
            return null;
        }
      // Garantizar claves completas
        return [
         'uavatar_gif'        => $row['uavatar_gif']        ?? '',
         'uavatar_gif_active' => (int)($row['uavatar_gif_active'] ?? 0),
         'uavatar_type'       => (int)($row['uavatar_type'] ?? 0),
         'uavatar_social'     => $row['uavatar_social']     ?? 'web',
         'uavatar_use'        => $row['uavatar_use']        ?? 'web',
        ];
    }
}
