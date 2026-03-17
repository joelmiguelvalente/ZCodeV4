<?php

declare(strict_types=1);

namespace App\Themes;

use App\Models\Core;
use App\Models\User;
use App\Helpers\Appearance;
use App\Database\DB;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class Theme
{
    protected Core $Core;
    protected User $User;

    /** Cache interno para evitar múltiples consultas. */
    private ?array $profileData = null;

    public function __construct(Core $Core, User $User)
    {
        $this->Core = $Core;
        $this->User = $User;
    }

    /**
     * Obtiene todos los ajustes visuales del usuario con cache interno.
     */
    protected function getProfileData(): array
    {
        if ($this->profileData !== null) {
            return $this->profileData;
        }
        if (!$this->User->is_member) {
            // Consistencia: algunos métodos esperan claves puntuales.
            return $this->profileData = [
                'user_scheme'      => 0,
                'user_color'       => 1,
                'user_customize'   => '#212121;#F4F4F4',
                'user_font_family' => 'tema',
                'user_font_size'   => 'md',
                'user_pagebox'     => 0
            ];
        }
        $data = DB::fetch("SELECT `user_scheme`, `user_color`, `user_customize`, `user_font_family`, `user_font_size`, `user_pagebox` FROM @perfil WHERE `user_id` = :uid", ['uid' => $this->User->uid]);
        return $this->profileData = $data ?: [];
    }

    public function setSchemeColor(string $type = ''): string
    {
        $profile = $this->getProfileData();
        $scheme = Appearance::getSchemes((int)$profile['user_scheme'], false) ?? 'light';
        $color  = Appearance::getNameColors((int)$profile['user_color']) ?? 'default';
        return match ($type) {
            'scheme' => $scheme,
            'color'  => $color,
            default  => ''
        };
    }

    public function setColorCustomize(): array
    {
        $profile = $this->getProfileData();
        $raw = trim((string)$profile['user_customize']);
        if ($raw === '') {
            return [];
        }
        return array_filter(explode(';', $raw), fn($v) => $v !== '');
    }

    public function setThemeFont(string $type = ''): string
    {
        $profile = $this->getProfileData();
        $family = $profile['user_font_family'] ?: 'tema';
        $size   = $profile['user_font_size']   ?: 'md';
        return match ($type) {
            'family' => $family,
            'size'   => $size,
            default  => ''
        };
    }

    public function getSettingPageBox(): bool
    {
        $profile = $this->getProfileData();
        return ((int)$profile['user_pagebox'] === 1);
    }

    public function getSettingsTheme(): string
    {
        return sprintf(
            'data-theme="%s" data-theme-color="%s" data-font-family="%s" data-font-size="%s"',
            $this->setSchemeColor('scheme'),
            $this->setSchemeColor('color'),
            $this->setThemeFont('family'),
            $this->setThemeFont('size')
        );
    }

    public function preloadFont(): string
    {
        $family = $this->setThemeFont('family');
        $fontFile = match ($family) {
            "dinpro"    => "DINPro-CondensedRegular.woff2",
            "inter"     => "Inter.woff2",
            "neomatrix" => "NeomatrixCode.ttf",
            "pixellari" => "Pixellari.ttf",
            "roboto"    => "RobotoMono.ttf",
            "ubuntu"    => "UbuntuMono.ttf",
            default     => "Inter.woff2"
        };
        $parts = explode('.', $fontFile);
        $ext   = strtolower($parts[1] ?? 'woff2');
        $file = $this->Core->route('assets:fonts') . '/' . $fontFile;
        return "<link rel=\"preload\" href=\"{$file}\" as=\"font\" type=\"font/{$ext}\" crossorigin=\"anonymous\">";
    }
}
