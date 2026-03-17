<?php

declare(strict_types=1);

namespace App\Plugins\Services;

final class SystemPlugin
{
    private array $scope;

    public function __construct(array $scope = [])
    {
        $this->scope  = $scope;
    }

    public function inArray(array $data = []): bool
    {
        return in_array($this->scope['tsPage'], $data);
    }

    public function getRoute(string $param = '', ?string $subparam = ''): string|array
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
        return $routes[$key][$subkey] . (!empty($subparam) ? "/$subparam" : '') ?? null;
    }
}
