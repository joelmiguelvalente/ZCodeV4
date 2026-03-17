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

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class CacheService
{
    private FilesystemAdapter $cache;
    private int $ttl;

    public function __construct(string $namespace = 'zcode', int $ttl = 300, string $path = STORAGE . '/queries')
    {
        $this->ttl = $ttl;
        $this->cache = new FilesystemAdapter($namespace, $ttl, $path);
    }

    /**
     * Genera o recupera del caché una consulta de posts.
     */
    public function getCached(string $baseKey, array $params, callable $callback, ?callable $changeDetector = null)
    {
        // Armar clave de caché segura y única
        $keyParts = array_merge([$baseKey], $params);
        if ($changeDetector) {
            $state = call_user_func($changeDetector);
            $keyParts[] = $state;
        }

        $cacheKey = md5(implode('_', $keyParts));

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($callback) {
            $item->expiresAfter($this->ttl);
            return call_user_func($callback);
        });
    }

    public function clear(string $baseKey, array $params = [], ?callable $changeDetector = null): void
    {
        $keyParts = array_merge([$baseKey], $params);
        if ($changeDetector) {
            $state = call_user_func($changeDetector);
            $keyParts[] = $state;
        }

        $cacheKey = md5(implode('_', $keyParts));
        $this->cache->deleteItem($cacheKey);
    }
}
