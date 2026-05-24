<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace App\Utils;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class PasswordHandler
{
    private string $algorithm = PASSWORD_ARGON2ID;

    private array $options = [
        // Configuración recomendada para Argon2id (puedes ajustarla según tu servidor)
        'memory_cost' => 1 << 17, // 128 MB
        'time_cost'   => 4,
        'threads'     => 2,
    ];

    /**
     * Genera un hash seguro
     */
    public function create(string $password): string
    {
        return password_hash($password, $this->algorithm, $this->options);
    }

    /**
     * Verifica una contraseña contra su hash
     */
    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Comprueba si un hash necesita ser actualizado (por cambio de config o algoritmo)
     */
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, $this->algorithm, $this->options);
    }

    /**
     * Permite cambiar a BCRYPT (por compatibilidad)
     */
    public function useBcrypt(int $cost = 12): void
    {
        $this->algorithm = PASSWORD_BCRYPT;
        $this->options = ['cost' => $cost];
    }

    /**
     * Permite ajustar las opciones de Argon2id manualmente
     */
    public function setArgonOptions(int $memory, int $time, int $threads): void
    {
        $this->algorithm = PASSWORD_ARGON2ID;
        $this->options = [
            'memory_cost' => $memory,
            'time_cost'   => $time,
            'threads'     => $threads,
        ];
    }

    /**
     * Verifica si dos contraseñas coinciden
     */
    public function match(string $password, string $confirm): bool
    {
        return hash_equals($password, $confirm);
    }

    /**
     * Comprueba si la contraseña cumple con los requisitos mínimos
     * - Al menos una mayúscula
     * - Al menos un número
     * - Al menos un carácter especial
     */
    public function isStrong(string $password): bool
    {
        $hasUppercase = preg_match('/[A-Z]/', $password);
        $hasNumber    = preg_match('/\d/', $password);
        $hasSpecial   = preg_match('/[\W_]/', $password);

        return $hasUppercase && $hasNumber && $hasSpecial;
    }
}
