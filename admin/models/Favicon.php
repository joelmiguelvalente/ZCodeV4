<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

namespace Admin\models;

use Admin\models\Core;
use App\Traits\Extras;

if (! defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class Favicon
{
    use Extras;

    private $sizes = [512, 256, 128, 64, 32, 16];

    private $folder = 'favicon';

    private $extension = 'webp';

    protected Core $Core;

    public function __construct(Core $Core)
    {
        $this->Core = $Core;
    }

    private function getLinkFavicon()
    {
        return $this->Core->route('assets:' . $this->folder) . '/';
    }

    private function getRootFavicon()
    {
        return TS_IMAGES . $this->folder . DIRECTORY_SEPARATOR;
    }

    public function getAllFavicons()
    {
        $root_favicon = $this->getRootFavicon();
        $favicons = scandir($root_favicon);
        $favdata = [];
        foreach ($favicons as $fv => $icon) {
            if (in_array($icon, ['.', '..'])) {
                continue;
            }
            $imagen = $root_favicon . $icon;
            $size = getimagesize($imagen)[0];

            $favdata[] = [
                'px' => "32px",
                'size' => $size,
                'weight' => $this->formatBytes(filesize($imagen)),
                'name' => ucfirst(str_replace('-', ' ', pathinfo($imagen, PATHINFO_FILENAME))),
                'ext' => pathinfo($imagen, PATHINFO_EXTENSION),
                'link' => $this->getLinkFavicon() . $icon
            ];
        }
        usort($favdata, function ($a, $b) {
            return $a['size'] - $b['size'];
        });
        return $favdata;
    }

    private function createFavicon($image, $size = '')
    {
       // Si el tamaño está vacío, usa el tamaño original de la imagen
        if (empty($size)) {
            $newSize = (imagesx($image) > 1024 ? 1024 : imagesx($image));
            $newName = $this->slugify($this->Core->settings['titulo']);
        } else {
            $newSize = $size;
            $newName = "logo-$size";
        }
       // Redimensionar la imagen
        $resized = imagescale($image, $newSize, $newSize);
        $output_filename = $this->getRootFavicon() . "$newName.{$this->extension}";
       // Guardar la imagen redimensionada
        imagewebp($resized, $output_filename);
       // Liberar la memoria
        imagedestroy($resized);
    }

    private function resizeImage($filename)
    {
        $image_info = getimagesize($filename);
        $mime_type = $image_info['mime'];

        $image = match ($mime_type) {
            'image/jpeg', 'image/jpg', 'image/jfif' => imagecreatefromjpeg($filename),
            'image/png' => imagecreatefrompng($filename),
            'image/gif' => imagecreatefromgif($filename),
            'image/webp' => imagecreatefromwebp($filename),
            default => die('Formato de imagen no soportado.'),
        };
        $this->createFavicon($image);
        foreach ($this->sizes as $f => $size) {
            $this->createFavicon($image, $size);
        }
        imagedestroy($image);
        return true;
    }

    public function uploadFavicon(): string
    {
        if (!isset($_FILES['favicon']) || $_FILES['favicon']['error'] !== UPLOAD_ERR_OK) {
            return '0: No se ha enviado ninguna imagen válida.';
        }
        $tmpFile = $_FILES['favicon']['tmp_name'];

       // 1. Validar que sea imagen
        $imageInfo = getimagesize($tmpFile);
        if ($imageInfo === false) {
            return '0: El archivo no es una imagen.';
        }
       // 2. Validar tamaño del archivo (ej: máx 1MB)
        if (filesize($tmpFile) > 1024 * 1024) {
            return '0: La imagen supera el tamaño permitido.';
        }
       // 3. Validar dimensiones mínimas
        [$width, $height] = $imageInfo;
        if ($width < 32 || $height < 32) {
            return '0: El favicon debe ser al menos 32x32 píxeles.';
        }
        $originalName = 'favicon.' . $this->extension;
        $originalFile = $this->getRootFavicon() . $originalName;
        if (!move_uploaded_file($tmpFile, $originalFile)) {
            return '0: Error al mover el archivo.';
        }
        if ($this->resizeImage($originalFile)) {
            unlink($originalFile);
            return '1: Favicon generados correctamente.';
        }
        return '0: Error al procesar el favicon.';
    }
}
