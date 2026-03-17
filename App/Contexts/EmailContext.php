<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @version     4.0.0
 */

declare(strict_types=1);

namespace App\Contexts;

use App\Models\Core;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class EmailContext
{
    private array $settings;
    private string $domain;

    public function __construct(Core $Core)
    {
        $this->settings = $Core->settings;
        $this->domain   = $Core->route('domain');
    }

    public function getUrl(): string
    {
        return $this->settings['url'];
    }

    public function getTitle(): string
    {
        return $this->settings['titulo'];
    }

    public function getSlogan(): string
    {
        return $this->settings['slogan'];
    }

    public function getEmail(): string
    {
        return $this->settings['email'];
    }

    public function getDomain(): string
    {
        return $this->domain;
    }
}
