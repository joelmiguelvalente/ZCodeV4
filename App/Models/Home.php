<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

namespace App\Models;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Contexts\HomeContext;
use App\Traits\{PostsHelper,Extras};
use App\Database\DB;

class Home
{
    use PostsHelper;
    use Extras;

    protected HomeContext $context;

    public function __construct(HomeContext $context)
    {
        $this->context = $context;
    }

    /**
     * Verifica si el usuario actual es administrador o moderador
     */
    private function isAdmod(): bool
    {
        return $this->context->User->is_admod && (int)$this->context->Core->settings['c_see_mod'] === 1;
    }

    /**
     * Devuelve condiciones SQL para visibilidad de usuarios
     */
    private function userVisibilitySql(string $prefix = 'u.'): string
    {
        return $this->isAdmod() ? '' : " AND {$prefix}user_activo = 1 AND {$prefix}user_baneado = 0";
    }

    /**
     * SQL base para obtener posts con usuario y categoría
     */
    private function postVisibilitySql(string $postPrefix = 'p.'): string
    {
        return $this->isAdmod() ? '' : " AND {$postPrefix}post_status = 0";
    }

    /**
     * Normaliza y completa datos adicionales de los posts
     */
    private function hydratePosts(array $posts): array
    {
        foreach ($posts as &$post) {
            $pid = (int)$post['post_id'];
            $post['post_url']      = $this->context->Core->route('url') . $this->createLink('post', $pid);
            $post['post_title']    = $this->cleaner($post['post_title']);
            $post['post_portada']  = $this->context->Images->setImageCover($pid);
            $post['c_img']         = $this->context->Core->imageCat($post['c_img']);
            $post['visto']         = $this->context->Visitas->wasVisited($pid, 3, '1');
            $post['post_new']      = $this->tagsNew((int)$post['post_date'], 3);
        }

        return $posts;
    }

    /**
     * @access public
     * @return array
    */
    public function getCategory(?string $category = ''): array
    {
        // Obtenemos categoría
        $data = DB::fetch("SELECT c_nombre, c_seo, c_img, c_color, c_descripcion FROM @posts_categorias WHERE c_seo = :seo LIMIT 1", [
            'seo' => $category
        ]);
        $data['c_img'] = $this->context->Core->route('assets:categorias') . "/{$data['c_img']}";
        return $data;
    }

    private function basePostSql(): string
    {
        return "
			SELECT
				p.post_id, p.post_user, p.post_category, p.post_title,
				p.post_hits, p.post_portada, p.post_date, p.post_comments,
				p.post_puntos, p.post_private, p.post_sponsored,
				p.post_status, p.post_sticky,
				u.user_id, u.user_name,
				c.c_nombre, c.c_seo, c.c_img
			FROM @posts p
			LEFT JOIN @miembros u ON u.user_id = p.post_user {$this->userVisibilitySql()}
			LEFT JOIN @posts_categorias c ON c.cid = p.post_category
			WHERE p.post_id > 0 {$this->postVisibilitySql()}
		";
    }

    private function resolveCategory(?string $category): ?int
    {
        if (!$category) {
            return null;
        }
        $result = DB::fetch("SELECT cid FROM @posts_categorias WHERE c_seo = :seo LIMIT 1", [
            'seo' => $this->context->Core->setSecure($category)
        ]);
        return isset($result['cid']) ? (int)$result['cid'] : null;
    }

    /**
     * @access public
     * @param string
     * @param bool
     * @return array
    */
    public function getLastPostsStickys(): array
    {
        $posts = DB::fetchAll($this->basePostSql() . " AND p.post_sticky = 1 ORDER BY p.post_date DESC LIMIT 5");
        return $this->hydratePosts($posts);
    }

    /**
     * Obtiene los últimos posts con paginación opcional por categoría
     */
    public function getLastPosts(?string $category = null): array
    {
        $cid = $this->resolveCategory($category);
        $whereCategory = $cid ? " AND p.post_category = {$cid}" : '';

        $total = (int) DB::rowCount("SELECT COUNT(*) FROM @posts p LEFT JOIN @miembros u ON u.user_id = p.post_user WHERE p.post_sticky = 0 {$this->postVisibilitySql()} {$whereCategory}");

        $max = (int)$this->context->Core->settings['c_max_posts'];
        $limit = $this->context->Paginator->setPageLimit($max, false, $total);

        $data = DB::fetchAll($this->basePostSql() . " AND p.post_sticky = 0 {$whereCategory} ORDER BY p.post_id DESC LIMIT {$limit}");

        return [
            'pages' => $this->context->Paginator->systemPagination($total, $max),
            'data'  => $this->hydratePosts($data),
        ];
    }
}
