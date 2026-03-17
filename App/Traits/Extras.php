<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

namespace App\Traits;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

/**
 * Este trait asume que las propiedades $core y $user son inyectadas
 * desde la clase que lo utiliza, típicamente vía constructor.
 */

trait Extras
{
    # Convierte una cadena en un formato amigable para SEO.
    public function slugify(string $string = '', bool $lower = false): string
    {
        // Normalizar a UTF-8
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        // Eliminar acentos y caracteres diacríticos
        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        // Reemplazar cualquier cosa no alfanumérica por guiones
        $string = preg_replace('/[^a-zA-Z0-9]+/', '-', $string);
        // Minusculas opcionales
        if ($lower) {
            $string = strtolower($string);
        }
        // Limpiar guiones al inicio y final
        return trim($string, '-');
    }

    public function cleaner(string $value = ''): string
    {
        if (empty($value)) {
            return '';
        }
        return trim(stripslashes($value));
    }

    private function generateLinkPerfil(int $id = 0, ?string $param = null): string
    {
        return "/perfil/{$data['id']}$param";
    }

    private function generateLinkPost(int $id = 0, ?string $param = null)
    {
        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT post_id, post_title, c_seo FROM @posts LEFT JOIN @posts_categorias ON cid = post_category WHERE post_id = $id"));
        return "/posts/{$data['c_seo']}/{$id}/{$this->slugify($data['post_title'], true)}.html{$param}";
    }

    private function generateLinkFoto(int $id = 0, ?string $param = null)
    {
        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT user_name, foto_id, f_title FROM @miembros LEFT JOIN @fotos ON f_user = user_id WHERE foto_id = $id"));
        return "/fotos/{$data['user_name']}/{$id}/{$this->slugify($data['f_title'], true)}.html{$param}";
    }

    /**
     * Creates a URL based on the specified type and ID.
     *
     * @param string $type  The type of link to create ('post', 'perfil', 'foto').
     * @param mixed  $id    The ID associated with the link (post ID, user ID, etc.).
     * @param string $param Additional URL parameters.
     * @return string The generated URL.
     */
    public function createLink(string $type = 'post', $id = '', string $param = ''): string
    {
        return match ($type) {
            'post' => $this->generateLinkPost((int)$id, $param),
            'foto' => $this->generateLinkFoto((int)$id, $param),
            'perfil' => $this->generateLinkPerfil((int)$id, $param),
            default => '',
        };
    }

    public function cleanerCacheSQL()
    {
        $folder = TS_CACHE . '/sql' . DIRECTORY_SEPARATOR;
        $files = glob($folder . '*.json'); // Obtiene todos los archivos .json
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file); // Elimina el archivo
            }
        }
    }

   /**
    * Convierte bytes a un formato legible (KB, MB, GB, etc.).
    *
    * @param int $bytes       El tamaño en bytes que se desea formatear.
    * @param int $decimales   El número de decimales para mostrar.
    * @return string          El tamaño formateado en la unidad más apropiada.
   */
    public function formatBytes(int $bytes = 0, int $decimales = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $unidad = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = (int) floor(log($bytes, 1024));

        $formatted = number_format(
            $bytes / (1024 ** $factor),
            $decimales
        );

        return $formatted . ' ' . $unidad[$factor];
    }
}
