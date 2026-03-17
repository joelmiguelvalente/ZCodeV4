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
use App\Traits\Extras;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class Sitemap
{
    use Extras;

    private string $sitemap = 'sitemap.xml';

    private bool $viewXML = false;

    private bool $show_file = false;

    public array $frecuencias = ['never', 'always', 'daily', 'hourly', 'weekly', 'monthly', 'yearly'];

    public array $prioridades = ['1.0', '0.9', '0.8', '0.7', '0.6', '0.5', '0.4', '0.3', '0.2', '0.1', '0'];

    private function revertFrecuencia(string $option = ''): int
    {
        $data = [
            'never'      => 0,
            'always'     => 1,
            'daily'      => 2,
            'hourly'     => 3,
            'weekly'  => 4,
            'monthly' => 5,
            'yearly'  => 6
        ];
        return $data[$option];
    }

    private function revertPrioridad(string $option = ''): int
    {
        $data = [
            "1.0" => 0,
            "0.9" => 1,
            "0.8" => 2,
            "0.7" => 3,
            "0.6" => 4,
            "0.5" => 5,
            "0.4" => 6,
            "0.3" => 7,
            "0.2" => 8,
            "0.1" => 9,
            "10"  => 10
        ];
        return $data[$option];
    }

    protected Core $Core;

    /**
     * Constructs a new instance.
     */
    public function __construct(Core $Core)
    {
        $this->Core = $Core;
        $this->sitemap = BASEPATH . $this->sitemap;
    }

    /**
     * { function_description }
     *
     * @return     bool  ( description_of_the_return_value )
     */
    public function syncSitemap()
    {
        if (file_exists($this->sitemap)) {
            unlink($this->sitemap);
        }
        $this->addSitemap();
        return true;
    }

    /**
     * { function_description }
     *
     * @param      int     $date   The date
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    private function dateSitemap(int $date = 0)
    {
        return date('Y-m-d\TH:i:sP', $date);
    }

    /**
     * Sets the url basic of system.
     *
     * @param      <type>  $data   The data
     */
    private function setURLBasicOfSystem(&$data)
    {
        $url = $this->Core->route('url');
        $time = time();
        $data["{$url}/"]                                            = ["never", $time, '1.0'];
        $data["{$url}/buscador/"]                               = ["never", $time, '0.80'];
        $data["{$url}/fotos/"]                                  = ["hourly", $time, '0.80'];
        $data["{$url}/posts/"]                                  = ["hourly", $time, '0.80'];
        $data["{$url}/tops/"]                                   = ["never", $time, '0.80'];
        $data["{$url}/tops/posts"]                          = ["hourly", $time, '0.80'];
        $data["{$url}/tops/usuarios"]                       = ["daily", $time, '0.80'];
        $data["{$url}/usuarios/"]                               = ["daily", $time, '0.80'];
        $data["{$url}/pages/ayuda/"]                            = ["never", $time, '0.80'];
        $data["{$url}/pages/chat/"]                             = ["never", $time, '0.80'];
        $data["{$url}/pages/dmca/"]                             = ["never", $time, '0.80'];
        $data["{$url}/pages/privacidad/"]                   = ["never", $time, '0.80'];
        $data["{$url}/pages/protocolo/posts"]               = ["never", $time, '0.80'];
        $data["{$url}/pages/terminos-y-condiciones/"]   = ["never", $time, '0.80'];
    }

    public function setURLPostsCreated(&$data)
    {
        $allPosts = DB::fetchAll("SELECT post_id, post_title, post_date, c_seo FROM @posts LEFT JOIN @posts_categorias ON cid = post_category");
        foreach ($allPosts as $pid => $post) {
            $url = $this->Core->route('url') . $this->createLink('post', (int)$post['post_id']);
            $data[$url] = ["monthly", (int)$post['post_date'], '0.50'];
        }
    }

    public function getSitemap()
    {
        return DB::fetchAll("SELECT id, url, frecuencia, fecha, prioridad FROM @sitemap");
    }

    /**
     * { function_description }
     *
     * @param      <type>  $urls   The urls
     * @param      bool    $mode   The mode
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function sitemap_generator($urls)
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
	 	<urlset
	 	   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	 	   xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
	 	   xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
	 	   xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $key => $value) {
            $xmlString .=  '<url>';
            $xmlString .=  "<loc>{$key}</loc>";
            $xmlString .=  "<changefreq>{$value[0]}</changefreq>";
            $xmlString .=  "<lastmod>{$this->dateSitemap($value[1])}</lastmod>";
            $xmlString .=  '</url>';
        }

        $xmlString .= '</urlset>';

        if ($this->viewXML) {
            var_dump($xmlString);
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xmlString);
        $dom->save($this->sitemap);

        if ($this->show_file) {
            return $this->sitemap;
        }
    }

    public function addSitemap()
    {
        $urls = [];
        $this->setURLBasicOfSystem($urls);
        $this->setURLPostsCreated($urls);
        //
        DB::query("TRUNCATE @sitemap");
        DB::query("ALTER TABLE @sitemap AUTO_INCREMENT 1");
        foreach ($urls as $url => $data) {
            DB::query("INSERT INTO @sitemap (url, frecuencia, fecha, prioridad) VALUES (:url, :frecuencia, :fecha, :prioridad)", [
                'url' => $url,
                'frecuencia' => $data[0],
                'fecha' => $data[1],
                'prioridad' => $data[2]
            ]);
        }
        $this->sitemap_generator($urls);
    }

    /*
     * AGREGAR URL
     */
    public function newUrlSitemap()
    {
        array_pop($_POST);
        $url = $this->Core->setSecure($_POST['url']);
        $frecuencia = $this->frecuencias[$_POST['frecuencia']];
        $prioridad = $this->prioridades[$_POST['prioridad']];
        $date = time();
        if (
            DB::query("INSERT INTO @sitemap (url, frecuencia, fecha, prioridad) VALUES (:url, :frecuencia, :date, :prioridad)", [
            'url' => $url,
            'frecuencia' => $frecuencia,
            'fecha' => $date,
            'prioridad' => $prioridad
            ])
        ) {
            #$this->setSiteMapUpdate();
            return true;
        }
    }

    /*
     * EDITAMOS
     */
    public function SitemapEditID()
    {
        $id = (int)$_GET['id'];
        $data = DB::fetch("SELECT url, frecuencia, fecha, prioridad FROM @sitemap WHERE id = :id", ['id' => $id]);
        $data['frecuencia'] = $this->revertFrecuencia($data['frecuencia']);
        $data['prioridad'] = $this->revertPrioridad($data['prioridad']);
        return $data;
    }
    public function SitemapSaveID()
    {
        if (isset($_POST['url'])) {
            $id = (int)$_GET['id'];
            $data['url'] = $_POST['url'];
            $data['prioridad'] = $this->prioridades[$_POST['prioridad']];
            $data['frecuencia'] = $this->frecuencias[$_POST['frecuencia']];
            return DB::update('sitemap', $data, ['id' => $id]) ? true : false;
        }
        return false;
    }
    /*
     * CONFIGURACION SITEMAP
     */
    public function setSettings()
    {
        $data = DB::fetchAll("SELECT register_post, register_foto, update_post, update_foto FROM @sitemap_control WHERE sid = 1")[0];
        foreach ($data as $k => $val) {
            $data[$k] = (int)$val;
        }
        return $data;
    }

    public function saveSettings()
    {
        if (isset($_POST['save'])) {
            array_pop($_POST);
            foreach ($_POST as $k => $val) {
                $_POST[$k] = (int)$val;
            }
            return DB::update('sitemap_control', $_POST, ['sid' => 1]) ? true : false;
        }
    }
}
