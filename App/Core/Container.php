<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use Exception;
use ReflectionClass;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class Container
{
    protected array $instances = [];
    protected array $entries = [];
    protected array $groups = [];

    /**
     * Registrar un servicio
     */
    public function set(string $id, string|callable $concrete, array $params = []): void
    {
        if (is_callable($concrete)) {
            $this->entries[$id] = $concrete;
            return;
        }

        $this->entries[$id] = [
            'class'  => $concrete,
            'params' => $params
        ];
    }

    /**
     * Obtener un servicio (con autowiring completo)
     */
    public function get(string $id)
    {

        // Singleton
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        /*
         |--------------------------------------------------------------------------
         | 1. Si está registrado → usar entrada
         |--------------------------------------------------------------------------
        */
        if (isset($this->entries[$id])) {
            $entry = $this->entries[$id];

            // Closure (factory)
            if ($entry instanceof Closure) {
                $instance = $entry($this);
                return $this->instances[$id] = $instance;
            }

            // Clase + parámetros manuales
            if (!empty($entry['params'])) {
                $params = $this->resolveParams($entry['params']);
                $instance = new $entry['class'](...$params);
                return $this->instances[$id] = $instance;
            }

            // Autowire por clase registrada
            $instance = $this->autowire($entry['class']);
            return $this->instances[$id] = $instance;
        }

        /*
         |--------------------------------------------------------------------------
         | 2. Si NO está registrado → intentar autowire directo
         |--------------------------------------------------------------------------
        */
        if (class_exists($id)) {
            $instance = $this->autowire($id);
            return $this->instances[$id] = $instance;
        }

        throw new Exception("Servicio o clase '{$id}' no encontrado.");
    }

    /**
     * Autowiring REAL con Reflection (sin requerir set previo)
     */
    private function autowire(string $class)
    {

        if (!class_exists($class)) {
            throw new Exception("Clase '{$class}' no existe para autowire.");
        }

        $ref = new ReflectionClass($class);

        if (!$ref->isInstantiable()) {
            throw new Exception("La clase '{$class}' no es instanciable.");
        }

        $constructor = $ref->getConstructor();

        if (!$constructor || $constructor->getNumberOfParameters() === 0) {
            return new $class();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            // Dependencia por tipo (clase)
            if ($type && !$type->isBuiltin()) {
                $depClass = $type->getName();

                $dependencies[] = $this->get($depClass);
                continue;
            }

            // Escalar con valor por defecto
            if ($param->isDefaultValueAvailable()) {
                $dependencies[] = $param->getDefaultValue();
                continue;
            }

            throw new Exception(
                "No se puede resolver el parámetro '{$param->getName()}' en {$class}"
            );
        }

        return $ref->newInstanceArgs($dependencies);
    }

    /**
     * Resolver lista de parámetros manuales y grupos
     */
    private function resolveParams(array $params): array
    {
        $resolved = [];

        foreach ($params as $param) {
            // Grupo (@name)
            if (is_string($param) && str_starts_with($param, '@')) {
                $groupName = substr($param, 1);

                if (!isset($this->groups[$groupName])) {
                    throw new Exception("Grupo '{$groupName}' no encontrado.");
                }

                foreach ($this->groups[$groupName] as $service) {
                    $resolved[] = $this->get($service);
                }
                continue;
            }

            // Servicio registrado
            if (is_string($param) && $this->has($param)) {
                $resolved[] = $this->get($param);
                continue;
            }

            // Valor manual
            $resolved[] = $param;
        }

        return $resolved;
    }

    public function load(string $class, ?string $path = null, ?string $file = null)
    {
        if ($this->has($class)) {
            return $this->get($class);
        }

        if ($this->smartRegister($class, $path, $file)) {
            return $this->get($class);
        }

        throw new Exception("No se pudo cargar la clase '{$class}'.");
    }

    private function smartRegister(string $class, ?string $path = null, ?string $file = null): bool
    {
        // Caso 1: ya existe por autoload
        if (class_exists($class)) {
            $this->set($class, $class);
            return true;
        }

        $base = BASEPATH . '/app/';

        // Obtenemos el nombre simple de la clase (Posts, User, etc)
        $parts = explode('\\', $class);
        $className = end($parts);

        $folder = $path ?? str_replace('App\\', '', dirname(str_replace('\\', '/', $class)));
        $folder = trim($folder, '/\\.');

        $fileName = $file ?? $className;
        $filePath = $base . ($folder ? "{$folder}/" : '') . "{$fileName}.php";

        if (file_exists($filePath)) {
            require_once $filePath;

            if (class_exists($class)) {
                $this->set($class, $class);
                return true;
            }
        }

        return false;
    }

    /**
     * Check de existencia
     */
    public function has(string $id): bool
    {
        return isset($this->entries[$id]) || class_exists($id);
    }

    /**
     * Remover servicio
     */
    public function remove(string $id): void
    {
        unset($this->entries[$id], $this->instances[$id]);
    }

    /**
     * Crear grupo de clases
     */
    public function group(string $name, array $items): void
    {
        $this->groups[$name] = $items;
    }
}
