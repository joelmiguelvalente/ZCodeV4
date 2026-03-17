<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @version     4.0.0
 */

declare(strict_types=1);

namespace App\Contexts;

use App\Utils\Avatar;
use App\Models\Autenticar;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class UserContext
{
    public Avatar $Avatar;
    public Autenticar $Autenticar;

    public function __construct(Avatar $Avatar, Autenticar $Autenticar)
    {
        $this->Autenticar = $Autenticar;
        $this->Avatar = $Avatar;
    }
}
