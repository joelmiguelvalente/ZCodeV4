<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

namespace App\Repository;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Database\DB;

class AutenticarRepository
{
    public function __construct()
    {
    }

    public function getUserConfig(int|string $user): ?array
    {
        $data = DB::fetch("SELECT user_id, user_name, user_password, user_recovery, user_secret_2fa, user_activo, user_baneado FROM @miembros WHERE user_name = :usermail OR user_email = :usermail LIMIT 1", ['usermail' => $user]);
        if (!$data) {
            return null;
        }
      // Garantizar claves completas
        return $data;
    }
}
