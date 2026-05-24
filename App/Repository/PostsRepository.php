<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace App\Repository;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Database\DB;

final class PostsRepository
{
    public function findPublicPostId(int $postId, string $isAdmod): ?int
    {
        $condition = $isAdmod ? '' : 'AND  p.post_status = 0';
        $row = DB::fetch(
            "SELECT p.post_id FROM @posts p WHERE p.post_id = :id $condition LIMIT 1",
            ['id' => $postId]
        );
        return $row['post_id'] ?? null;
    }

    public function findPublicPostTitle(string $sign, string $order, int $postId): ?array
    {
        return DB::fetch("SELECT post_id, post_title, c_seo FROM @posts LEFT JOIN @posts_categorias ON post_category = cid WHERE post_status = 0 AND post_id $sign $postId ORDER BY post_id $order LIMIT 1") ?? [];
    }

    public function findAdjacentPost(string $direction, ?int $currentPostId, string $isAdmod): ?int
    {
        $conditions = ['p.post_status = 0'];
        $params = [];
        $conditions[] = $isAdmod ? '' : 'p.post_status = 0';
        if ($direction !== 'random' && $currentPostId !== null) {
            $operator = $direction === 'prev' ? '<' : '>';
            $conditions[] = "p.post_id $operator :pid";
            $params['pid'] = $currentPostId;
        }

        $order = match ($direction) {
            'prev'   => 'p.post_id DESC',
            'next'   => 'p.post_id ASC',
            'random' => 'RAND()',
            default  => 'p.post_id ASC',
        };

        $row = DB::fetch("SELECT p.post_id FROM @posts p WHERE " . implode(' AND ', $conditions) . " ORDER BY $order LIMIT 1", $params);
        return $row['post_id'] ?? null;
    }
}
