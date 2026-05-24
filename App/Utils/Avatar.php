<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
 */

declare(strict_types=1);

namespace App\Utils;

use App\Repository\AvatarRepository;
use App\Services\ImageService;
use App\Traits\Url;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class Avatar
{
    use Url;

    private int $avatarSize = 160;

    protected ImageService $ImageService;
    protected AvatarRepository $repo;

    protected $Image;

    private array $source;

    public function __construct(ImageService $ImageService, AvatarRepository $repo)
    {
        $this->Image = $ImageService;
        $this->repo = $repo;

        $this->source = [
            'avatar'   => TS_AVATAR,
            'avatares' => TS_AVATARES
        ];
    }

    private function createLinkImage(string $type = 'avatar', string $param = ''): string
    {
        $return = [
            'avatar' => $this->url() . "/storage/avatar",
            'favicon' => $this->url() . "/assets/favicon"
        ];
        return "{$return[$type]}/{$param}";
    }

    public function createNameFolder(int $id = 0): string
    {
        return substr(md5("user{$id}"), 0, 10);
    }

    private function createPath(string $type = '', string $value = ''): string
    {
        return "{$this->source[$type]}/{$value}/";
    }

    /**
     * Crea la carpeta de avatares del usuario si no existe.
     */
    private function createFolderAvatar(int $id = 0, string $name = 'web'): string
    {
        $path = $this->createPath('avatar', $this->createNameFolder($id));
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
            chmod($path, 0777);
        }
        return rtrim($path, '/');
    }

    /**
     * Crea un avatar desde una red social o URL externa.
     */
    public function createAvatarSocial(int $id, string $social, string $sourcePath): bool
    {
        if ($id <= 0 || !$social) {
            return false;
        }
        $isTempFile = false;
        $destinationPath = $this->createFolderAvatar($id, $social);
        // Si la fuente es una URL, descargar temporalmente
        if (filter_var($sourcePath, FILTER_VALIDATE_URL)) {
            $sourcePath = $this->downloadImage($sourcePath);
            if (!$sourcePath) {
                return false;
            }
            $isTempFile = true;
        }
        if (!is_file($sourcePath)) {
            return false;
        }
        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            return false;
        }
        $mimeType = $imageInfo[2];
        $sourceImage = $this->Image->load($sourcePath);
        if (!$sourceImage) {
            return false;
        }
        [$width, $height] = $imageInfo;
        // Mantener proporción
        $newWidth = $newHeight = $this->avatarSize;
        $newHeight = ($width > $height) ? intval(($height * $newWidth) / $width) : intval(($width * $newHeight) / $height);
        $scaled = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($scaled, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        $result = imagewebp($scaled, $destinationPath . "/image_{$name}.webp");
        imagedestroy($sourceImage);
        imagedestroy($scaled);
        if ($isTempFile && is_file($sourcePath)) {
            unlink($sourcePath);
        }
        return $result;
    }

    /**
     * Descarga una imagen remota a un archivo temporal.
     */
    private function downloadImage(string $url): string|false
    {
        $tempPath = tempnam(TS_UPLOADS, 'img_');
        $context = stream_context_create([
            'http' => ['timeout' => 5],
            'https' => ['timeout' => 5]
        ]);
        $content = @file_get_contents($url, false, $context);
        if ($content === false) {
            return false;
        }
        file_put_contents($tempPath, $content);
        return $tempPath;
    }

    /**
     * Copia un avatar aleatorio predefinido para el usuario.
     */
    public function copyAvatar(int $id, string $type = 'none', string $name = 'web'): bool
    {
        if ($id <= 0) {
            return false;
        }
        $destination = $this->createFolderAvatar((int)$id, 'web');
        $basePath = $this->createPath('avatares', $type);
        // Si no existe retornamos falso
        if (!is_dir($basePath)) {
            return false;
        }
        // Recorrecmos la carpeta
        $files = glob($basePath . '*.webp');
        if (!$files) {
            return false;
        }
        // Obtenemos un avatar aleatoreamente
        $randomAvatar = $files[array_rand($files)];
        return copy($randomAvatar, $destination . "/image_{$name}.webp");
    }

    public function newAvatar(int $id, string $type = 'none', int $image = 0)
    {
        if ($id <= 0) {
            return false;
        }
        $destination = $this->createFolderAvatar((int)$id, 'web');
        $basePath = $this->createPath('avatares', $type);

        return copy("{$basePath}{$image}.webp", $destination . "/image_{$name}.webp");
    }

    /**
     * Cargamos el avatar del usuario
     */
    public function loadAvatar(int $uid): string
    {
        if ($uid <= 0) {
            return $this->createLinkImage('favicon', 'logo-128.webp');
        }
        $config = $this->repo->getUserAvatarConfig((int)$uid);
        if (!$config) {
            return $this->createLinkImage('favicon', 'logo-128.webp');
        }
        // Si el usuario tiene GIF activo, es prioridad absoluta
        if ((int)$config['uavatar_gif_active'] === 1 and !empt($config['uavatar_gif'])) {
            return $config['uavatar_gif'];
        }
        // Avatar actual (web, social, etc.)
        $current = $config['uavatar_use'] ?: 'web';
    // Creamos la ruta pública
        $folder = $this->createNameFolder($uid);
        return $this->createLinkImage('avatar', "$folder/image_{$current}.webp");
    }

    public function loadAvatarUrl(int $uid = 0, string $image = ''): string
    {
        $nameFolder = $this->createNameFolder((int)$uid);
        return $this->createLinkImage('avatar', "$nameFolder/image_$image.webp");
    }
}
