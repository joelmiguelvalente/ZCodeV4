<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

namespace Admin\models;

use Admin\models\Core;
use App\Database\DB;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

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
    # * saveSEO() :: Guardamos la informacion
    # * addRobotsTXT() :: Generamos el robots.txt
    # ===================================================
    public function getSeo()
    {
        $sql = DB::fetch("SELECT * FROM @seo WHERE seo_id = 1");
        if ($sql === null) {
            return [];
        }
        return $sql;
    }

    private function filter(): array
    {
        $data = $_POST; // mejor filtrado antes, pero eso ya lo sabés
        unset($data['save'], $data['csrf_token']);
        return $data;
    }

    public function saveSEO()
    {
        if (DB::update('seo', $this->filter(), ['seo' => 1])) {
            return '1: Configuarciones guardadas.';
        }
        return '0: Hubo un error al guardar las configuraciones.';
    }

    public function addRobotsTXT()
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
