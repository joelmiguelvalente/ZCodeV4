<?php
declare(strict_types=1);

namespace App\Plugins\Services;

use App\Helpers\Appearance;
use App\Plugins\Services\UserPermission;
use App\Database\DB;

class Customizer
{

	private array $scope;
	private ?array $profileData = null;
	private UserPermission $permission;

	public function __construct(array $scope)
	{
		$this->scope = $scope;
		$this->permission = new UserPermission($scope);
	}

	protected function getProfileData(): array {
		if ($this->profileData !== null) {
			return $this->profileData;
		}
		if (!$this->permission->isLogged()) {
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
		$data = DB::fetch("SELECT `user_scheme`, `user_color`, `user_customize`, `user_font_family`, `user_font_size`, `user_pagebox` FROM @perfil WHERE `user_id` = :uid", ['uid' => $this->permission->userId()]);
		return $this->profileData = $data ?: [];
	}

	private function getUserColors(): array {
      $profile = $this->getProfileData();
      $raw = isset($profile['user_customize']) ? trim((string)$profile['user_customize']) : '';
      if ($raw === '') {
         return [];
      }
      return array_filter(explode(';', $raw), fn($v) => $v !== '');
   }

	public function customizer(): string {
      if (!$this->permission->isLogged()) {
         return '';
      }

      $colors = $this->getUserColors();
      if (count($colors) <= 1) {
         return '';
      }

      return $this->generateThemeColors('customizer', $colors[0], $colors[1]);
   }

	private function generateThemeColors(string $nameColor, string $lightColor, string $darkColor): string {
		$lightHover  = $this->lightenDarkenColor($lightColor, 20);
		$lightActive = $this->lightenDarkenColor($lightColor, -20);

		return <<<STYLE
		<style id="customizer_style">[data-theme-color="$nameColor"]{--color-base-triplet:{$this->hexToRgb($lightColor)};}[data-theme="dark"][data-theme-color="$nameColor"]{--color-base-triplet:{$this->hexToRgb($darkColor)};}</style>
		STYLE;
	}

	private function lightenDarkenColor(string $col, int $amt): string {
		$usePound = false;

		if (str_starts_with($col, '#')) {
			$col = substr($col, 1);
			$usePound = true;
		}

		$num = hexdec($col);

		$r = (($num >> 16) & 0xFF) + $amt;
		$g = (($num >> 8)  & 0xFF) + $amt;
		$b = (($num)       & 0xFF) + $amt;

		$r = max(0, min(255, $r));
		$g = max(0, min(255, $g));
		$b = max(0, min(255, $b));

		return ($usePound ? "#" : "")
				. str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
				. str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
				. str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
	}

	private function hexToRgb(string $hex): string {
		$hex = ltrim($hex, '#');

		$r = hexdec(substr($hex, 0, 2));
		$g = hexdec(substr($hex, 2, 2));
		$b = hexdec(substr($hex, 4, 2));

		return "$r, $g, $b";
	}

}