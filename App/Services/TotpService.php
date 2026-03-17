<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

namespace App\Services;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use Symfony\Component\Clock\NativeClock;
use OTPHP\TOTP;

class TotpService
{
    public function verifyCodeSecret(string $secret = '', string $code = ''): bool|string
    {
        $clock = new NativeClock();
        $totp  = TOTP::createFromSecret($secret, $clock);

        if (!ctype_digit($code) || strlen($code) !== 6) {
            return '0: El código debe ser de 6 dígitos numéricos.';
        }

        return $totp->verify($code);
    }

    public function createSecret(string $secret = '', string $code = ''): bool|string
    {
        return $this->verifyCodeSecret($secret, $code);
    }
}
