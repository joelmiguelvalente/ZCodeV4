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

final class MuroHelper
{
    public function getMuroConfig(array &$privacidad, array $context, string $type = ''): void
    {
    // Excepciones globales
        if ($context['isMe'] || $this->User->is_admod) {
            return;
        }
        $this->evaluatePrivacyRule(
            $type,
            $context,
            function (string $message) use (&$privacidad) {
                $privacidad['muro']['status'] = false;
                $privacidad['muro']['message'] = $message;
            },
            [
            'nobody' => "Lo sentimos pero {$context['username']} no permite ver su muro a nadie.",
            'friends_mutual' => "Debes seguir a {$context['username']} y éste debe seguirte para poder ver su muro.",
            'friends_any' => "Debes seguir a {$context['username']} o éste debe seguirte para poder ver su muro.",
            'following' => "Debes seguir a {$context['username']} para poder ver su muro.",
            'followers' => "{$context['username']} debe seguirte para que puedas ver su muro.",
            'registered' => "Solo usuarios <a href=\"{$this->url}/registro/\">registrados</a> pueden ver el muro de {$context['username']}",
            'default' => 'No tenés permisos para ver este muro.',
            ]
        );
    }

    public function getMuroPublicar(array &$privacidad, array $context, string $type = ''): void
    {
    // Excepciones globales
        if ($context['isMe'] || $this->User->is_admod) {
            return;
        }
    //string $privacy, array $context, callable $deny, array $messages
        $this->evaluatePrivacyRule(
            $type,
            $context,
            function (string $message) use (&$privacidad) {
                $privacidad['muro_firma']['status'] = false;
                $privacidad['muro_firma']['message'] = $message;
            },
            [
            'nobody' => "Lo sentimos pero {$context['username']} no permite firmar su muro a nadie.",
            'friends_mutual' => "Debes seguir a {$context['username']} y éste debe seguirte para poder firmar su muro.",
            'friends_any' => "Debes seguir a {$context['username']} o éste debe seguirte para poder firmar su muro.",
            'following' => "Debes seguir a {$context['username']} para poder firmar su muro.",
            'followers' => "{$context['username']} debe seguirte para poder firmar su muro.",
            'registered' => "Solo usuarios registrados pueden firmar el muro de {$context['username']}",
            'default' => 'No tenés permisos para firmar este muro.',
            ]
        );
    }
}
