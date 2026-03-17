<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

namespace App\Services;

use App\Repository\PostsStatsRepository;

final class PostStatsService
{
    public function __construct(
        private PostsStatsRepository $statsRepo,
        private int $cacheMinutes = 3
    ) {
    }

    public function hydrate(array &$postData, int $postId): void
    {
        $now = time();
        $cacheLimit = $this->cacheMinutes * 60;

        if (isset($postData['post_cache']) && $postData['post_cache'] > ($now - $cacheLimit)) {
            return;
        }

        $data = [
            'post_cache'      => $now,
            'post_comments'   => $this->statsRepo->countComments($postId),
            'post_seguidores' => $this->statsRepo->countFollowers($postId),
            'post_shared'     => $this->statsRepo->countShares($postId),
            'post_favoritos'  => $this->statsRepo->countFavorites($postId),
        ];

        $postData += $data;

        $this->statsRepo->updateStats($postId, $data);
    }
}
