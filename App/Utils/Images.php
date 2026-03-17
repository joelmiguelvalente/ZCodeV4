<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
 */

declare(strict_types=1);

namespace App\Utils;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Models\Core;

use RuntimeException;
use InvalidArgumentException;

class Images
{
    // --- Protected / Public properties (API-compatible) ---
    protected string $url;
    protected string $assets;
    protected string $storage;

    /** @var array<string> */
    public array $en_arreglo = ['png', 'jpg', 'jpeg', 'webp', 'jfif'];

    public int $quality = 90;
    public int $limitStr = 6;
    public int $megabytes = 10; // MB
    protected int $image_in_bytes;

    protected string $pattern = '/\[img(?:=|])([^]]+)\[\/img]|(?:\[img=)([^]]+)\]/i';

    protected Core $Core;

    /**
     * Constructor.
     */
    public function __construct(Core $Core)
    {
        $this->Core = $Core;
        $this->url = (string)($this->Core->settings['url'] ?? '');
        $this->assets = (string)$this->Core->route('assets:images');
        $this->storage = (string)$this->Core->route('storage:base');
        $this->image_in_bytes = $this->megabytes * 1024 * 1024;
        $this->ensureFoldersExist();
    }

    // ----------------------------
    // Utilities (protected/private)
    // ----------------------------

    protected function getInformationImage(string $image = '', string $type = ''): string
    {
        return match ($type) {
            'extension' => strtolower(pathinfo($image, PATHINFO_EXTENSION)),
            'filename'  => pathinfo($image, PATHINFO_FILENAME),
            'basename'  => pathinfo($image, PATHINFO_BASENAME),
            default     => '',
        };
    }

    private function logError(string $message): void
    {
        error_log($message);
    }

    /**
     * Asegura que las carpetas base existan.
     */
    private function ensureFoldersExist(): void
    {
        foreach ([TS_PORTADAS, TS_UPLOADS] as $folder) {
            if (!is_dir($folder)) {
                @mkdir($folder, 0777, true);
            }
        }
    }

    /**
     * Mueve un archivo subido a la carpeta temporal y lo valida como imagen.
     *
     * @param array<string,mixed> $fileArray estructura típica de $_FILES['...']
     * @param string|null $targetName basename a usar (si null genera uno)
     * @return string ruta absoluta del archivo movido (en TS_UPLOADS)
     * @throws RuntimeException
     */
    private function storeUploadedFile(array $fileArray, ?string $targetName = null): string
    {
        if (($fileArray['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Error en la subida del archivo.');
        }

        if (!is_uploaded_file((string)$fileArray['tmp_name'])) {
            throw new RuntimeException('El archivo no proviene de una subida válida.');
        }

        $originalName = $fileArray['name'] ?? 'upload';
        $basename = $targetName ?? $this->generateTempFilename($originalName);
        $destination = rtrim(TS_UPLOADS, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $basename;

        if (!move_uploaded_file((string)$fileArray['tmp_name'], $destination)) {
            throw new RuntimeException('No se pudo mover el archivo subido al destino temporal.');
        }

        // Validar que sea imagen
        $imgInfo = @getimagesize($destination);
        if ($imgInfo === false) {
            @unlink($destination);
            throw new RuntimeException('El archivo subido no es una imagen válida.');
        }

        return $basename;
    }

    /**
     * Genera nombre temporal basado en el nombre original (MD5 del filename + timestamp).
     */
    private function generateTempFilename(string $originalName): string
    {
        $name = $this->getInformationImage($originalName, 'filename') ?: bin2hex(random_bytes(6));
        $hash = md5($name . microtime(true));
        $ext = $this->getInformationImage($originalName, 'extension') ?: 'jpg';
        return $hash . '.' . $ext;
    }

    /**
     * Guarda una imagen remota (URL) en la carpeta temporal y devuelve el basename generado.
     *
     * @param string $urlImage
     * @param string|null $preferredExt
     * @return string basename generado
     * @throws RuntimeException
     */
    private function fetchRemoteImageToTemp(string $urlImage, ?string $preferredExt = null): string
    {
        if (!filter_var($urlImage, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('URL no válida para imagen remota.');
        }

        // Opciones para timeouts mínimos
        $context = stream_context_create([
            'http' => ['timeout' => 5],
            'https' => ['timeout' => 5],
        ]);

        $content = @file_get_contents($urlImage, false, $context);
        if ($content === false || strlen($content) === 0) {
            throw new RuntimeException('No se pudo descargar la imagen remota.');
        }

        // Intentar detectar extensión por cabecera o por URL
        $ext = $preferredExt ?: $this->getInformationImage($urlImage, 'extension');
        if (empty($ext)) {
            // Fallback: intentar detectar con getimagesizefromstring
            $info = @getimagesizefromstring($content);
            if ($info !== false && !empty($info['mime'])) {
                $mime = $info['mime']; // e.g. image/jpeg
                $ext = match ($mime) {
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                    default      => 'jpg',
                };
            } else {
                $ext = 'jpg';
            }
        }

        $basename = md5($urlImage . microtime(true)) . '.' . $ext;
        $destination = rtrim(TS_UPLOADS, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $basename;

        if (@file_put_contents($destination, $content) === false) {
            throw new RuntimeException('No se pudo guardar la imagen remota en temporal.');
        }

        // Validar imagen
        if (@getimagesize($destination) === false) {
            @unlink($destination);
            throw new RuntimeException('La imagen remota descargada no es válida.');
        }

        return $basename;
    }

    /**
     * Devuelve rutas base: link, temp, cover.
     */
    private function route(string $folder = '', string $type = 'link'): string
    {
        return match ($type) {
            'link'  => rtrim($this->storage, '/') . '/' . trim($folder, '/') . (empty($folder) ? '' : '/'),
            'temp'  => rtrim(TS_UPLOADS, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
            'cover' => rtrim(TS_PORTADAS, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
            default => '',
        };
    }

    /**
     * Crea image name (basename) a partir de un posible nombre.
     */
    private function makeBasenameFrom(string $image): string
    {
        $name = $this->generateTempFilename($image);
        return $name;
    }

    /**
     * Redimensiona y guarda en WebP en 3 tamaños.
     *
     * @param string $destDir ruta absoluta del folder de destino (sin slash final obligatorio)
     * @param string $fileBasename nombre del archivo en TS_UPLOADS (basename)
     */
    private function resizeAndConvertToWebP(string $destDir, string $fileBasename): void
    {
        $sourcePath = rtrim(TS_UPLOADS, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileBasename;

        if (!file_exists($sourcePath)) {
            $this->logError("Archivo fuente no existe: {$sourcePath}");
            return;
        }

        $imageInfo = @getimagesize($sourcePath);
        if ($imageInfo === false) {
            $this->logError("No se puede determinar el tipo de imagen de {$sourcePath}");
            return;
        }

        $srcWidth = (int)$imageInfo[0];
        $srcHeight = (int)$imageInfo[1];
        $srcType = (int)($imageInfo[2] ?? IMAGETYPE_JPEG);

        $sourceImage = $this->System->Zcode->getFormatImage($srcType, $sourcePath, $srcType);
        if (!is_resource($sourceImage) && !($sourceImage instanceof \GdImage)) {
            $this->logError("No se pudo crear recurso GD para {$sourcePath}");
            return;
        }

        if (!is_dir($destDir)) {
            @mkdir($destDir, 0777, true);
        }

        $sizes = [
            'sm' => ['width' => 120, 'height' => 90],
            'md' => ['width' => 240, 'height' => 180],
            'lg' => ['width' => 360, 'height' => 270],
        ];

        $quality = max(1, min(100, $this->quality));

        foreach ($sizes as $prefix => $size) {
            $destPath = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "image_{$prefix}.webp";
            // Si ya existe, skipear (no RETURN para que generen los otros tamaños)
            if (file_exists($destPath)) {
                continue;
            }

            $destW = (int)$size['width'];
            $destH = (int)$size['height'];
            $srcRatio = $srcWidth / max(1, $srcHeight);
            $destRatio = $destW / max(1, $destH);

            if ($srcRatio > $destRatio) {
                // fuente más ancha -> fit by height then crop sides
                $newHeight = $destH;
                $newWidth = (int)round($srcWidth * ($destH / $srcHeight));
                $cropX = (int)round(($newWidth - $destW) / 2);
                $cropY = 0;
            } else {
                // fuente más alta -> fit by width then crop top/bottom
                $newWidth = $destW;
                $newHeight = (int)round($srcHeight * ($destW / $srcWidth));
                $cropX = 0;
                $cropY = (int)round(($newHeight - $destH) / 2);
            }

            $resizedImage = imagecreatetruecolor($destW, $destH);
            // Preserve transparency for PNG/WebP
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);

            // Copy and resample
            $ok = imagecopyresampled(
                $resizedImage,
                $sourceImage,
                -$cropX,
                -$cropY,
                0,
                0,
                $newWidth,
                $newHeight,
                $srcWidth,
                $srcHeight
            );

            if ($ok === false) {
                $this->logError("imagecopyresampled falló para {$destPath}");
                imagedestroy($resizedImage);
                continue;
            }

            // Guardar como webp
            if (!@imagewebp($resizedImage, $destPath, $quality)) {
                $this->logError("Error al guardar la imagen {$destPath}");
            }
            imagedestroy($resizedImage);
        }

        // Liberar y eliminar origen temporal si existe
        if (is_resource($sourceImage) || ($sourceImage instanceof \GdImage)) {
            imagedestroy($sourceImage);
        }
        // El archivo fuente sólo se elimina si está dentro de TS_UPLOADS (evitar borrar originales locales fuera del temp)
        if (strpos($sourcePath, rtrim(TS_UPLOADS, DIRECTORY_SEPARATOR)) === 0) {
            @unlink($sourcePath);
        }
    }

    /**
     * Transforma (genera la carpeta codificada y las versiones WebP).
     * Devuelve links mediante getPortadaLink.
     */
    private function transformImage(int $pid, string $imageBasename): array
    {
        if ($imageBasename === '') {
            return $this->defaultImages();
        }

        $encoded = $this->setEncodeNameFolder($pid);
        $encodedFolder = rtrim(TS_PORTADAS, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $encoded;

        // Forzar recreación si se está en modo editar
        $isEditar = (($_GET['action'] ?? '') === 'editar');

        if (!is_dir($encodedFolder) || $isEditar) {
            $this->resizeAndConvertToWebP($encodedFolder, $imageBasename);
        }

        return $this->getPortadaLink($encoded);
    }

    /**
     * Construye las URLs de portada por tamaños (sm, md, lg)
     */
    private function getPortadaLink(string $encode = ''): array
    {
        $images = [];
        $sizes = ['sm', 'md', 'lg'];
        foreach ($sizes as $s) {
            $images[$s] = $this->route("portadas/{$encode}", 'link') . "image_{$s}.webp";
        }
        return $images;
    }

    private function defaultImages(): array
    {
        return [
            'sm' => rtrim($this->assets, '/') . '/favicon/logo-64.webp',
            'md' => rtrim($this->assets, '/') . '/favicon/logo-128.webp',
            'lg' => rtrim($this->assets, '/') . '/favicon/logo-512.webp',
        ];
    }

    /**
     * Verifica tamaño. $local puede ser:
     *  - int (bytes)
     *  - array como $_FILES['...']
     *
     * Devuelve true si el tamaño es válido, o string con mensaje de error.
     *
     * @param int|array<string,mixed> $local
     * @param string $type 'local'|'url'
     * @return true|string
     */
    private function checkImageSize(mixed $local, string $type = 'local'): mixed
    {
        $allowedBytes = $this->image_in_bytes;

        if ($type === 'url') {
            // Espera $local sea la URL
            $headers = @get_headers((string)$local, 1);
            if ($headers === false) {
                return 'No se pudieron obtener los headers de la URL.';
            }
            $size = 0;
            if (isset($headers['Content-Length'])) {
                $size = (int)$headers['Content-Length'];
            }
            if ($size === 0) {
                return 'No se pudo determinar el tamaño del recurso remoto.';
            }
            return $size <= $allowedBytes ? true : sprintf('La imagen remota excede %dMB.', $this->megabytes);
        }

        // Local
        if (is_array($local)) {
            $size = (int)($local['size'] ?? 0);
        } elseif (is_int($local)) {
            $size = $local;
        } else {
            return 'Parámetro inválido para verificación de tamaño.';
        }

        return $size <= $allowedBytes ? true : sprintf('La imagen excede %dMB.', $this->megabytes);
    }

    private function updateTable(int $pid, string $encoded): void
    {
        // Mantengo la llamada original a db_exec
        db_exec([__FILE__, __LINE__], 'query', "UPDATE @posts SET `post_portada` = '{$encoded}' WHERE `post_id` = {$pid}");
    }

    /**
     * Crea imagen desde link (remote url) y procesa.
     */
    private function createImageFromUrl(int $pid, string $url, string $folder, string $encoded = ''): array
    {
        try {
            $ext = $this->getInformationImage($url, 'extension');
            if (!in_array($ext, $this->en_arreglo, true)) {
                // intentar detección interna
                $ext = '';
            }

            $basename = $this->fetchRemoteImageToTemp($url, $ext);
            if ($basename === '') {
                return $this->defaultImages();
            }

            $images = $this->transformImage($pid, $basename);
            $this->updateTable($pid, $encoded);

            return $images;
        } catch (\Throwable $e) {
            $this->logError("createImageFromUrl error: " . $e->getMessage());
            return $this->defaultImages();
        }
    }

    /**
     * Elimina carpeta codificada y archivos internos (silencioso).
     */
    private function deleteFolderContent(string $encoded): void
    {
        $folderPath = rtrim(TS_PORTADAS, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $encoded;
        if (!is_dir($folderPath)) {
            return;
        }

        $files = @scandir($folderPath);
        if (!is_array($files)) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }
        @rmdir($folderPath);
    }

    /**
     * Maneja parámetros/archivos entrantes (array tipo $_FILES o string URL).
     *
     * Retorna basename (string) en TS_UPLOADS o lanza excepción.
     *
     * @param mixed $input array|string
     * @return string
     */
    private function handleIncomingFile(mixed $input): string
    {
        if (is_array($input)) {
            // Subida local
            return $this->storeUploadedFile($input, null);
        }

        if (is_string($input) && filter_var($input, FILTER_VALIDATE_URL)) {
            // Descargar remota a temporal
            return $this->fetchRemoteImageToTemp($input);
        }

        throw new InvalidArgumentException('Entrada de imagen inválida.');
    }

    /**
     * Genera el nombre codificado de carpeta (público, no cambiar).
     */
    public function setEncodeNameFolder(int $folder_id = 0): string
    {
        return substr(md5("P{$folder_id}"), 0, $this->limitStr);
    }

    /**
     * Obtiene la imagen de portada para un post.
     * Mantiene la firma pública original.
     */
    public function setImageCover(int $pid = 0): array
    {
        $pid = (int)$pid;
        $encoded = $this->setEncodeNameFolder($pid);
        $returnImages = $this->defaultImages();

        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT post_id, post_portada, post_body FROM @posts WHERE post_id = {$pid}"));
        $image = $data['post_portada'] ?? null;
        $content = $data['post_body'] ?? '';

        // Si no hay portada registrada o no es URL válida -> intentar extraer del contenido
        if (empty($image) || !filter_var($image, FILTER_VALIDATE_URL)) {
            $returnImages = $this->getImageOfContent($content, $pid, $encoded);
            $this->updateTable($pid, $encoded);
            return $returnImages;
        }

        // Si la portada es el código corto (6 chars) -> carpeta existente
        if (is_string($image) && strlen($image) === (int)$this->limitStr) {
            if (!is_dir($this->route('', 'cover') . $encoded)) {
                return $returnImages;
            }
            return $this->getPortadaLink($encoded);
        }

        // Si es un nombre de archivo (subido previamente)
        if (is_string($image) && strlen($image) > (int)$this->limitStr && strlen($image) < 255) {
            return $this->createImage($pid, $image);
        }

        // Si es una URL remota
        if (filter_var($image, FILTER_VALIDATE_URL) && strlen($image) !== (int)$this->limitStr) {
            return $this->createImageFromUrl($pid, $image, 'portadas', $encoded);
        }
        return $returnImages;
    }

    /**
     * Crea la imagen de la portada desde un archivo local en TS_UPLOADS.
     * Firma pública original mantenida.
     */
    public function createImage(int $pid = 0, ?string $image = null): array
    {
        $pid = (int)$pid;
        $encoded = $this->setEncodeNameFolder($pid);
        $returnImages = $this->defaultImages();

        if (empty($image)) {
            return $returnImages;
        }

        $sourcePath = rtrim(TS_UPLOADS, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $image;
        if (!file_exists($sourcePath)) {
            return $returnImages;
        }

        $encodedFolder = rtrim(TS_PORTADAS, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $encoded;
        $this->resizeAndConvertToWebP($encodedFolder, $image);

        return $this->getPortadaLink($encoded);
    }

    /**
     * Extrae imágenes del contenido (BBCode u otros) y genera la portada.
     * Firma pública mantenida.
     */
    public function getImageOfContent(?string $bodyContent = null, int $pid = 0, string $encoded = ''): array
    {
        if (empty($bodyContent)) {
            return $this->defaultImages();
        }

        preg_match_all($this->pattern, $bodyContent, $matches, PREG_SET_ORDER);
        $found = [];

        foreach ($matches as $m) {
            // grupos: [0]=full, [1] o [2] posible URL
            if (!empty($m[1])) {
                $found[] = $m[1];
            } elseif (!empty($m[2])) {
                $found[] = $m[2];
            }
        }

        $found = array_filter($found, fn($v) => !empty($v));
        if (empty($found)) {
            return $this->defaultImages();
        }

        // Tomo la primera imagen válida (podrías cambiar a otra lógica si quieres)
        $candidate = reset($found);
        try {
            // Crear en temp y procesar
            $basename = $this->fetchRemoteImageToTemp($candidate);
            return $this->transformImage($pid, $basename);
        } catch (\Throwable $e) {
            $this->logError("getImageOfContent error: " . $e->getMessage());
            return $this->defaultImages();
        }
    }

    /**
     * Obtiene imagen desde input (POST URL o FILES).
     * Devuelve basename/identifier (string) o vacío si no hay entrada.
     */
    public function getImageOfInput(): string
    {
        $field = 'portada';
        $portada = '';

        // POST URL
        if (!empty($_POST[$field]) && filter_var($_POST[$field], FILTER_VALIDATE_URL)) {
            try {
                $portada = $this->fetchRemoteImageToTemp((string)$_POST[$field]);
            } catch (\Throwable $e) {
                $this->logError("getImageOfInput fetch error: " . $e->getMessage());
                $portada = '';
            }
        } elseif (!empty($_FILES[$field]) && is_array($_FILES[$field]) && !empty($_FILES[$field]['tmp_name'])) {
            try {
                $portada = $this->storeUploadedFile($_FILES[$field]);
            } catch (\Throwable $e) {
                $this->logError("getImageOfInput upload error: " . $e->getMessage());
                $portada = '';
            }
        }

        return (string)$portada;
    }

    /**
     * Actualiza la portada del post. Firma pública mantenida.
     *
     * - $update: si true escribe la tabla
     * - $pid: id del post; si se pasa 0 intenta leer de $_GET['pid'].
     */
    public function updateImagePost(bool $update = true, int $pid = 0): void
    {
        $pid = $pid ?: (int)($_GET['pid'] ?? 0);
        if ($pid <= 0) {
            throw new InvalidArgumentException('post id inválido para updateImagePost.');
        }

        $encoded = $this->setEncodeNameFolder($pid);

        // Eliminar carpeta previa si existe
        if (is_dir(rtrim(TS_PORTADAS, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $encoded)) {
            $this->deleteFolderContent($encoded);
        }

        // Determinar origen de la imagen
        $imageInput = null;
        if (!empty($_FILES['portada']) && is_array($_FILES['portada']) && !empty($_FILES['portada']['tmp_name'])) {
            $imageInput = $_FILES['portada'];
        } elseif (!empty($_POST['portada'])) {
            $imageInput = $_POST['portada'];
        }

        if ($imageInput === null) {
            // nada para actualizar
            return;
        }

        // Si es URL remota
        if (is_string($imageInput) && filter_var($imageInput, FILTER_VALIDATE_URL) && strlen($imageInput) !== (int)$this->limitStr) {
            $this->createImageFromUrl($pid, $imageInput, 'portadas', $encoded);
        } elseif (is_array($imageInput)) {
            // archivo subido -> guardar temp y transformar
            try {
                $basename = $this->storeUploadedFile($imageInput);
                $this->transformImage($pid, $basename);
            } catch (\Throwable $e) {
                $this->logError("updateImagePost upload/process error: " . $e->getMessage());
            }
        }

        if ($update) {
            $this->updateTable($pid, $encoded);
        }
    }
}
