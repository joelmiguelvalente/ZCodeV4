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

class ImageService
{
    public function load(string $path): \GdImage|false
    {
        $info = @getimagesize($path);
        if (!$info) {
            return false;
        }

        return match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => imagecreatefrompng($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            IMAGETYPE_GIF  => imagecreatefromgif($path),
            default        => false,
        };
    }

    public function resize(\GdImage $img, int $newW, int $newH): \GdImage
    {
        $dst = imagecreatetruecolor($newW, $newH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $newW, $newH, imagesx($img), imagesy($img));
        return $dst;
    }

    public function resizeCover(\GdImage $img, int $w, int $h): \GdImage
    {
        $srcW = imagesx($img);
        $srcH = imagesy($img);

        $srcRatio = $srcW / $srcH;
        $dstRatio = $w / $h;

        if ($srcRatio > $dstRatio) {
            $newH = $h;
            $newW = (int)($h * $srcRatio);
        } else {
            $newW = $w;
            $newH = (int)($w / $srcRatio);
        }
        $temp = $this->resize($img, $newW, $newH);

        $dst = imagecreatetruecolor($w, $h);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);

        $cropX = (int)(($newW - $w) / 2);
        $cropY = (int)(($newH - $h) / 2);
        imagecopy($dst, $temp, 0, 0, $cropX, $cropY, $w, $h);
        imagedestroy($temp);
        return $dst;
    }

    public function saveWebp(\GdImage $img, string $dest, int $quality = 90): bool
    {
        return imagewebp($img, $dest, $quality);
    }

    public function download(string $url): string|false
    {
        $tmp = tempnam(sys_get_temp_dir(), 'img_');
        $data = @file_get_contents($url);
        if (!$data) {
            return false;
        }
        file_put_contents($tmp, $data);
        return $tmp;
    }
}
