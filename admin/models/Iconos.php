<?php

declare(strict_types=1);

namespace Admin\models;

use Admin\models\Core;
use DirectoryIterator;
use RuntimeException;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class Iconos
{
    private const PER_PAGE = 40;
    private const ALLOWED_EXT = ['svg', 'png', 'jpg', 'jpeg', 'gif', 'webp'];

    private array $packs = ['categorias', 'medallas', 'rangos'];

    protected Core $Core;

    public function __construct(Core $Core)
    {
        $this->Core = $Core;
    }

    /* =====================================================
     *  Resolución y validación de pack
     * ===================================================== */

    private function resolvePack(): string
    {
        $pack = $_GET['path'] ?? '';

        if (!in_array($pack, $this->packs, true)) {
            throw new RuntimeException('Pack inválido');
        }

        return $pack;
    }

    /* =====================================================
     *  Hash corto para identificar iconos
     * ===================================================== */

    private function hashFileIcon(string $icon): string
    {
        return substr(md5(pathinfo($icon, PATHINFO_FILENAME)), 0, 6);
    }

    /* =====================================================
     *  Obtener archivos paginados
     * ===================================================== */

    private function getImagesPaginated(string $folder, int $page): array
    {
        $offset = ($page - 1) * self::PER_PAGE;
        $index  = 0;
        $files  = [];

        foreach (new DirectoryIterator($folder) as $file) {
            if (!$file->isFile()) {
                continue;
            }

            if (!in_array($file->getExtension(), self::ALLOWED_EXT, true)) {
                continue;
            }

            if ($index++ < $offset) {
                continue;
            }

            $files[] = $file->getFilename();

            if (count($files) === self::PER_PAGE) {
                break;
            }
        }

        return $files;
    }

    /* =====================================================
     *  Construcción de metadata de imagen
     * ===================================================== */

    private function buildImageData(string $file, string $folder, string $pack): array
    {
        $path = $folder . DIRECTORY_SEPARATOR . $file;
        $ext  = pathinfo($file, PATHINFO_EXTENSION);

        $data = [
            'hash' => $this->hashFileIcon($file),
            'icon' => pathinfo($file, PATHINFO_FILENAME),
            'url'  => $this->Core->route('assets:images') . "/{$pack}/{$file}",
        ];

        $this->fillImageInfo($data, $path, $ext);

        return $data;
    }

    /* =====================================================
     *  Info técnica de imagen
     * ===================================================== */

    private function fillImageInfo(array &$data, string $path, string $ext): void
    {
        if ($ext === 'svg') {
            $svg = file_get_contents($path);

            preg_match('/width="([^"]+)"/i', $svg, $w);
            preg_match('/height="([^"]+)"/i', $svg, $h);

            $data['width']  = $w[1] ?? null;
            $data['height'] = $h[1] ?? null;
            $data['type']   = 'image/svg+xml';
            return;
        }

        $info = getimagesize($path);

        $data['width']  = $info[0];
        $data['height'] = $info[1];
        $data['type']   = $info['mime'];
    }

    private function countImages(string $folder): int
    {
        $total = 0;

        foreach (new DirectoryIterator($folder) as $file) {
            if ($file->isFile() && in_array($file->getExtension(), self::ALLOWED_EXT, true)) {
                $total++;
            }
        }

        return $total;
    }

    /* =====================================================
     *  API pública: obtener pack paginado
     * ===================================================== */

    public function getPack(): array
    {
        $pack   = $this->resolvePack();
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $folder = TS_IMAGES . $pack;

        $files = $this->getImagesPaginated($folder, $page);

        $data = [];
        foreach ($files as $file) {
            $data[] = $this->buildImageData($file, $folder, $pack);
        }
        $total = $this->countImages($folder);
        $totalPages = (int) ceil($total / self::PER_PAGE);

        return [
           'pack'        => $pack,
           'page'        => $page,
           'perPage'     => self::PER_PAGE,
           'total'       => $total,
           'totalPages' => $totalPages,
           'data'        => $data,
        ];
    }
}
