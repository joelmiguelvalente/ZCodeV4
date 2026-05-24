<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace App\Services;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Models\{Core,User};
use App\Utils\Images;
use App\Traits\Extras;

class ContentService
{
    use Extras;

    protected Core $Core;
    protected User $User;
    protected Images $Images;

    private string $url;

    public function __construct(
        Core $Core,
        Images $Images,
        User $User
    ) {
        $this->Core = $Core;
        $this->User = $User;
        $this->Images = $Images;
        $this->url = $this->Core->route('url');
    }

    public function general(&$postData, int $pid = 0, string $title = '')
    {
        $postData['post_url'] = $this->url . $this->createLink('post', (int)$pid);
        $postData['post_title'] = $this->cleaner($title);
        $postData['post_portada'] = $this->Images->setImageCover($pid);
    }

    public function isAdmodSeeMod(): bool
    {
        return ($this->User->is_admod and (int)$this->Core->settings['c_see_mod'] === 1);
    }

    public function isAdmod(string $prefix = 'u.', string $addSql = ''): ?string
    {
        return $this->isAdmodSeeMod() ? '' : " {$prefix}user_activo = 1 AND {$prefix}user_baneado = 0{$addSql}";
    }

    public function isAdmodPost(string $prefix = 'u.', string $prefixSecondary = 'p.', string $append = '')
    {
        return $this->isAdmodSeeMod() ? "{$prefixSecondary}post_id > 0" : $this->isAdmod($prefix, " AND {$prefixSecondary}post_status = 0 $append");
    }

    public function sanitizeContent(string $string, bool $badwords = false): string
    {
        $string = $this->Core->setSecure($string);
        $string = $this->Core->parseBadWords($string, $badwords);
        return $string;
    }

    public function applyFiltersToInput(string $string): string
    {
        $string = $this->sanitizeContent($string, true);
        $string = $this->Core->parseBBCode($string);
        return $string;
    }

    public function redirectLinkPost(int $pid = 0)
    {
        $tsDir = $this->createLink('post', $pid);
        header("Location: $tsDir");
    }

    public function getCategoryId(string $category = ''): int
    {
        $result = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT cid FROM @posts_categorias WHERE c_seo = '$category' LIMIT 1"));
        $cid = isset($result['cid']) ? (int)$result['cid'] : 0;
    }

    public function setParser(string $parse = '', string $type = 'normal')
    {
        $result = $this->Core->parseBBCode($parse, $type);
        return $this->Core->parseBadWords($result, true);
    }
}
