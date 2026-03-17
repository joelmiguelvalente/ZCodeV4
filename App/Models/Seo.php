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

use App\Models\Core;

class Seo
{
    public $robots;

    public $seo;

    protected Core $Core;

    public function __construct(Core $Core)
    {
        $this->Core = $Core;
        $this->robots = BASEPATH . 'robots.txt';
        $this->seo = $this->getSeo();
    }

    # ===================================================
    # SEO
    # * getSEO() :: Obtenemos toda la informacion
    # * addRobotsTXT() :: Generamos el robots.txt
    # ===================================================
    public function getSeo(): array
    {
        $sql = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', 'SELECT seo_id, seo_titulo, seo_descripcion, seo_portada, seo_keywords, seo_robots, seo_sitemap, seo_google_verification, seo_google_verification_active, seo_google_analytics FROM @seo WHERE seo_id = 1'));
        if ($sql === null) {
            return [];
        }
        return $sql;
    }

    public function addRobotsTXT(): void
    {
        $robots = "User-agent: *\n";
        $disallow = [
            'admin/', 'app/', 'assets/', 'auth/', 'config/', 'errors/', 'logs/', 'storage/',
            'cuenta/', 'admin/', 'moderacion/', 'monitor/', 'mensajes/', 'favoritos.php',
            'borradores.php', 'agregar/', 'agregar.php', 'ajax_files/', 'password/',
            'validar/', 'fotos/editar/', 'fotos/agregar/', '*.webp', '*.js', '*.css', '*.txt',
            '*.php', '*.html'
        ];
        foreach ($disallow as $dis) {
            $robots .= "Disallow: " . (substr($dis, 0, 1) !== '*' ? "/$dis" : $dis) . "\n";
        }
        if (file_exists(BASEPATH . "sitemap.xml")) {
            $robots .= "Sitemap: {$this->Core->settings['url']}/sitemap.xml\n";
        }
        if (!file_exists($this->robots)) {
            file_put_contents($this->robots, trim($robots));
        }
    }
}
