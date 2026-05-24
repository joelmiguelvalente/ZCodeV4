<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @version     4.1.0
 */

declare(strict_types=1);

namespace App\Contexts;

use App\Models\{Core,User,Visitas};
use App\Utils\{Avatar,Paginator,Images};
use App\Services\ContentService;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class HomeContext
{
    public Avatar $Avatar;
    public Core $Core;
    public Images $Images;
    public Paginator $Paginator;
    public User $User;
    public Visitas $Visitas;
    public ContentService $Content;

    public function __construct(
        Avatar $Avatar,
        Core $Core,
        Images $Images,
        Paginator $Paginator,
        User $User,
        Visitas $Visitas,
        ContentService $Content
    ) {
        $this->Avatar = $Avatar;
        $this->Core = $Core;
        $this->Paginator = $Paginator;
        $this->Images = $Images;
        $this->User = $User;
        $this->Visitas = $Visitas;
        $this->Content = $Content;
    }
}
