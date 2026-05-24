<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace App\Models;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Traits\Extras;
use App\Models\Core;
use App\Database\DB;

class Foro
{
    use Extras;

    protected Core $Core;

    public function __construct(Core $Core)
    {
        $this->Core = $Core;
    }

    private function getPostsOfCategorie(array &$data = [])
    {
        foreach ($data['super_subcategorias'] as $key => $posts) {
            $lastPost = DB::fetch("SELECT p.post_id, p.post_user, p.post_category, p.post_title, p.post_private, p.post_sponsored, p.post_sticky, p.post_block_comments, p.post_date, u.user_id, u.user_name, u.user_rango, r.r_name, r.r_color, c.c_nombre FROM @posts AS p LEFT JOIN @miembros AS u ON p.post_user = u.user_id LEFT JOIN @rangos AS r ON u.user_rango = r.rango_id LEFT JOIN @posts_categorias AS c ON p.post_category = c.cid WHERE p.post_category = :category ORDER BY p.post_id DESC LIMIT 1", [
                'category' => $posts['cid']
            ]);

            if ($lastPost !== null) {
                $lastPost['post_url'] = $this->createLink('post', (int)$lastPost['post_id']);
            }
            $data['super_subcategorias'][$key]['ultimo'] = $lastPost;

            $data['super_subcategorias'][$key]['super_stats'] = DB::fetch("SELECT COUNT(p.post_id) as posts, SUM(p.post_comments) as comentarios, SUM(post_hits) as hits FROM @posts AS p LEFT JOIN @posts_categorias AS c ON p.post_category = c.cid WHERE p.post_category = :category", ['category' => $posts['cid']]);
        }
    }

    public function getForoPosts()
    {
        # Obtenemos todos los foros
        $data = DB::fetchAll("SELECT fid, super_nombre, super_descripcion, super_color, super_img FROM @posts_supercategorias");
        # Mostraremos 3 categorías
        foreach ($data as $k => $super) {
            $data[$k]['super_img'] = $this->Core->imageCat($super['super_img'] ?? '1f30d.svg');
            $data[$k]['super_subcategorias'] = DB::fetchAll("SELECT cid, c_nombre, c_seo FROM @posts_categorias WHERE c_foro = :foro", ['foro' => $super['fid']]);
            $this->getPostsOfCategorie($data[$k]);
        }
        return $data;
    }
}
