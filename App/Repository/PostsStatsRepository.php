<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

namespace App\Repository;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Database\DB;

final class PostsStatsRepository
{
    public function countComments(int $postId): int
    {
        return DB::rowCount(
            "SELECT 1 FROM @posts_comentarios c JOIN @miembros u ON u.user_id = c.c_user WHERE c.c_post_id = :pid AND c.c_status = 0 AND u.user_activo = 1 AND u.user_baneado = 0",
            ['pid' => $postId]
        );
    }

    public function countFollowers(int $postId): int
    {
        return DB::rowCount(
            "SELECT 1 FROM @follows f JOIN @miembros u ON u.user_id = f.f_user WHERE f.f_type = 2 AND f.f_id = :pid AND u.user_activo = 1 AND u.user_baneado = 0",
            ['pid' => $postId]
        );
    }

    public function countShares(int $postId): int
    {
        return DB::rowCount("SELECT 1 FROM @follows WHERE f_type = 3 AND f_id = :pid", ['pid' => $postId]);
    }

    public function countFavorites(int $postId): int
    {
        return DB::rowCount("SELECT 1 FROM @posts_favoritos WHERE fav_post_id = :pid", ['pid' => $postId]);
    }

    public function updateStats(int $postId, array $data): void
    {
        DB::update('posts', $data, ['post_id' => $postId]);
    }
}
