<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
 */

declare(strict_types=1);

namespace App\Helpers;

use App\Database\DB;
use App\Models\Core;

final class UserHelper
{
    protected Core $Core;

    public function __construct(Core $Core)
    {
        $this->Core = $Core;
    }

    public function getStatusUser()
    {
        $username = $this->Core->setSecure($_GET['user']);

        $usuario = DB::fetch("SELECT user_id, user_name, user_activo, user_baneado FROM @miembros WHERE user_name = :user", [
            'user' => $username
        ]);
        $uid = (int)$usuario['user_id'] ?? 0;

        $usuario['noExiste'] = ($uid === 0);
        $usuario['inactivo'] = ((int)$usuario['user_activo'] !== 1 && !$tsUser->permisos['movcud'] && !$tsUser->is_admod);
        $usuario['baneado'] = ((int)$usuario['user_baneado'] !== 0 && !$tsUser->permisos['movcus'] && !$tsUser->is_admod);
        return $usuario;
    }
}
