<?php

declare(strict_types=1);

namespace App\Plugins\Services;

final class UserPermission
{
    private array $scope;

    private const SPECIAL = [
      'moacp', 'most', 'moayca', 'mosu', 'modu',
      'moep', 'moop', 'moedcopo', 'moaydcp', 'moecp'
    ];

    public function __construct(array $scope = [])
    {
        $this->scope  = $scope;
    }

    public function userId(): int
    {
        return (int) $this->scope['tsUser']->info['user_id'];
    }

    public function isLogged(): bool
    {
        return ((int)$this->scope['tsUser']->is_member === 1);
    }

    public function isAdmod()
    {
        return in_array((int)$this->scope['tsUser']->is_admod, [1, 2]);
    }

    public function hasPermission(string $perm): bool
    {
        return $this->scope['tsUser']->permisos[$perm] ?? false;
    }

    public function getRoute(string $param = '')
    {
        $routes = $this->scope['tsRoutes'];
        if ($param === '') {
           // Caso 1: devolver todas las rutas
            return $routes;
        }
        // Separar por ":"
        $parts = explode(':', $param, 2);
        if (count($parts) === 1) {
           // Caso 2: getRoute('assets')
            return $routes[$parts[0]] ?? null;
        }
        // Caso 3: getRoute('assets:css')
        [$key, $subkey] = $parts;
        return $routes[$key][$subkey] ?? null;
    }

    public function verify(string $choice = '', string $subchoice = ''): bool
    {
        return match ($choice) {
            'live'       => (int)$this->scope['tsConfig']['c_allow_live'] === 1,
            'notLive'    => !in_array($this->ctx['tsPage'], ['login', 'registro'], true),
            'admin'      => $this->ctx['tsPage'] === $choice && $this->ctx['action'] === $subchoice,
            'php_files'  => $this->ctx['tsPage'] === "php_files/p.$subchoice.home",
            default      => false
        };
    }

    public function hasSpecialPermission(): bool
    {
        if ($this->User->is_admod) {
            return true;
        }
        foreach (self::SPECIAL as $perm) {
            if ($this->hasPermission($perm) ?? false) {
                return true;
            }
        }
        return false;
    }
}
