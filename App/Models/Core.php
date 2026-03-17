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

use App\Utils\BBCode;
use App\Database\DB;
use App\Traits\{IP,System,Url};

class Core
{
    use IP;
    use System;
    use Url;

    public array $settings = [];    // CONFIGURACIONES DEL SITIO

    # Aliviamos el constructor.
    public function __construct()
    {
        // CARGANDO CONFIGURACIONES
        $this->settings = $this->getSettings();
        //$this->settings['ip'] = $this->();
    }

    # Obtenemos la url actual
    public function currentUrl(bool $encode = false)
    {
        $current_url = $this->getSSLProtocol(true) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return $encode ? rawurlencode($current_url) : $current_url;
    }

    # Cargamos las configuraciones
    public function getSettings(): array
    {
        $query = DB::fetch("SELECT * FROM @configuracion WHERE tscript_id = :id", ['id' => 1]);
        # Reescribimos la URL
        $query['url'] = $this->getSSLProtocol(true) . $query['url'];
        return $query;
    }

    public function buildRoutes(): array
    {
        $baseUrl   = rtrim($this->settings['url'], '/');
        $theme     = $this->settings['tema'];

        $assets    = "$baseUrl/assets";
        $images    = "$assets/images";
        $storage   = "$baseUrl/storage";

        $routes = [
          'url'       => $baseUrl,
          'domain'    => $this->url(false),
          'canonical' => $this->currentUrl(true),

          'tema' => [
             'base'   => "$baseUrl/views/themes/$theme",
             'css'    => "$baseUrl/views/themes/$theme/css",
             'js'     => "$baseUrl/views/themes/$theme/js",
             'images' => "$baseUrl/views/themes/$theme/images"
          ],
          'assets' => [
             'base'   => $assets,
             'css'    => "$assets/css",
             'js'     => "$assets/js",
             'images' => $images,
             'fonts'  => "$assets/fonts",
             'favicon' => "$images/favicon",
             'categorias' => "$images/categorias",
          ],
          'storage' => [
             'base'      => $storage,
             'avatar'    => "$storage/avatar",
             'portadas'  => "$storage/portadas",
             'uploads'   => "$storage/uploads",
          ],

            'logos' => [
                '32' => "$images/favicon/logo-32.webp",
                '64' => "$images/favicon/logo-64.webp",
                '128' => "$images/favicon/logo-128.webp",
                '256' => "$images/favicon/logo-256.webp",
                'big' => "$images/favicon/{$this->setSEO($this->settings['titulo'])}.webp"
            ]
        ];
        return $routes;
    }

    public function route(string $path = ''): string|array|null
    {
        $routes = $this->buildRoutes();
        if ($path === '') {
            return $routes;
        }

        $segments = explode(':', $path);
        $current  = $routes;

        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }
        return $current;
    }

    # Funcion para obtener la ruta de la imagen de categoria
    public function imageCat(string $cat = ''): string
    {
        return $this->route('assets:categorias') . "/$cat";
    }

    # Funcion para mostrar acciones pendientes a admins/mods
    public function getNovemods(): array
    {
        $sql = 'SELECT 
	   	(SELECT COUNT(post_id) FROM @posts WHERE post_status = 3) AS revposts,
	   	(SELECT COUNT(cid) FROM @posts_comentarios WHERE c_status = 1) AS revcomentarios,
	   	(SELECT COUNT(DISTINCT obj_id) FROM @denuncias WHERE d_type = 1) AS repposts,
	   	(SELECT COUNT(DISTINCT obj_id) FROM @denuncias WHERE d_type = 2) AS repmps,
	   	(SELECT COUNT(DISTINCT obj_id) FROM @denuncias WHERE d_type = 3) AS repusers,
	   	(SELECT COUNT(DISTINCT obj_id) FROM @denuncias WHERE d_type = 4) AS repfotos,
	   	(SELECT COUNT(susp_id) FROM @suspension) AS suspusers,
	   	(SELECT COUNT(post_id) FROM @posts WHERE post_status = 2) AS pospelera,
	   	(SELECT COUNT(foto_id) FROM @fotos WHERE f_status = 2) AS fospelera
	   ';
        // Obtiene la fila como array asociativo
        $datos = DB::fetch($sql);
       // Calcular total solamente de los campos relevantes
        $keysToSum = ['repposts', 'repfotos', 'repmps', 'repusers', 'revposts', 'revcomentarios'];
        $datos['total'] = array_sum(array_intersect_key($datos, array_flip($keysToSum)));

        return $datos;
    }

    # Funcion para mostrar todas las categorías
    public function getCategorias(): array
    {
        $categorias = DB::fetchAll("SELECT cid, c_orden, c_nombre, c_seo, c_color, c_descripcion, c_img FROM @posts_categorias ORDER BY c_orden");
        foreach ($categorias as $cid => $cat) {
            $categorias[$cid]['c_img'] = $this->route('assets:categorias') . "/{$cat['c_img']}";
        }
        return $categorias;
    }

    # Obtenemos todas las noticias
    public function getNews(): array
    {
        // Ejecutar consulta para obtener noticias activas ordenadas aleatoriamente
        $data = DB::fetchAll('SELECT not_id, not_body, not_type FROM @noticias WHERE not_active = 1 ORDER BY RAND()');
        // Procesar resultados de la consulta
        foreach ($data as $nid => $new) {
            $data[$nid]['not_type'] = (int)$new['not_type'];
            // Parsear BBCode en el cuerpo de la noticia
            $data[$nid]['not_body'] = $this->parseBBCode($new['not_body'], 'news');
        }
        // Retornar los datos procesados
        return $data;
    }

    # Censura las palabras malas en una cadena dada.
    public function parseBadWords(string $censurar = '', bool $type = false): string
    {
        if (empty($censurar)) {
            return $censurar; // Retornar inmediatamente si la cadena esta vacia.
        }
        // Construir la consulta
        $query = 'SELECT word, swop, method, type FROM @badwords';
        if (!$type) {
            $query .= ' WHERE type = \'0\'';
        }
        $query = result_array(db_exec([__FILE__, __LINE__], 'query', $query));
        foreach ($query as $badword) {
            $search = ((int)$badword['method'] == 0) ? $badword['word'] : "{$badword['word']} ";
            $replace = ((int)$badword['type'] == 1) ? '<img title="' . $this->setSecure($badword['word']) . '" src="' . $this->setSecure($badword['swop']) . '" align="absmiddle"/>' : "{$badword['swop']} ";
            $censurar = str_ireplace($search, $replace, $censurar);
        }
        return $censurar;
    }

    # ESTABLECE EL NIVEL DE LA PAGINA | MIEMBROS o VISITANTES
    public function setLevel(int $tsLevel = 0, bool $message = false): string|array|bool
    {
        global $tsUser;
        // Los mensajes
        $setMessages = [
            1 => 'Esta p&aacute;gina solo es vista por los visitantes.',
            2 => 'Para poder ver esta p&aacute;gina debes iniciar sesi&oacute;n.',
            3 => 'Estas en un &aacute;rea restringida solo para moderadores.',
            4 => 'Estas intentando algo no permitido.'
        ];
        // Definimos los accesos!
        $conditions = [
            0 => true, // CUALQUIERA
            1 => $tsUser->is_member === 0, // SOLO VISITANTES
            2 => $tsUser->is_member === 1, // SOLO MIEMBROS
            3 => $tsUser->is_admod || (!empty($tsUser->permisos) && isset($tsUser->permisos['moacp']) && $tsUser->permisos['moacp']), // SOLO MODERADORES
            4 => $tsUser->is_admod === 1 // SOLO ADMIN
        ];
        $tsLevel = $tsLevel ?? 0;

        if (isset($conditions[$tsLevel]) && $conditions[$tsLevel]) {
            return true;
        }
        // Manejo de mensajes de error
        $msg = $setMessages[$tsLevel];
        return ($message) ? $msg : ['titulo' => 'Error', 'mensaje' => $msg ?? 'Error desconocido.'];
    }

    # Redirige a una pagina especifica dentro del sitio.
    public function redireccionar(string $page = '', string $subpage = '', string $param = ''): void
    {
        // Construir la URL de destino
        $url = "{$this->settings['url']}/$page/$subpage";
        if (!empty($param)) {
            $url .= "?$param";
        }
        // Redirigir al usuario
        $this->redirectTo($url);
    }

    # Redirige a la URL proporcionada.
    public function redirectTo(string $tsDir = '/'): void
    {
        $reloader = $tsDir === '/' ? $this->settings['url'] : $tsDir;
        header("Location: $reloader");
        exit();
    }

    # Realiza una sanitizacion de cadenas para evitar inyecciones SQL y XSS.
    public function setSecure(string $string = '', bool $xss = false): string
    {
        if ($string === '') {
            return $string;
        }
       // Sanitización SQL a través del adaptador
        $string = DB::escape($string);
       // Sanitización XSS opcional
        if ($xss) {
            $string = htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        return $string;
    }

    # Convierte una cadena en un formato amigable para SEO.
    public function setSEO(string $string = '', bool $lower = false): string
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

    # Parseamos desde BBCode a HTML
    public function parseBBCode(string $bbcode, string $type = 'normal'): string
    {
        // Class BBCode
        $parser = new BBCode();
        // Seleccionar texto
        $parser->setText($bbcode);
        //
        $buttons = [
            'normal' => ['url', 'code', 'quote', 'font', 'size', 'color', 'img', 'b', 'i', 'u', 's', 'align', 'spoiler', 'video', 'hr', 'sub', 'sup', 'table', 'td', 'tr', 'ul', 'li', 'ol', 'notice', 'info', 'warning', 'error', 'success'],
          'firma' => ['url', 'font', 'size', 'color', 'img', 'b', 'i', 'u', 's', 'align', 'spoiler'],
          'news' => ['url', 'b', 'i', 'u', 's']
        ];
        // Determinar si el tipo es 'normal' o 'smiles', en cuyo caso usar� los botones de 'normal'
        $allowed_buttons = ($type === 'normal' || $type === 'smiles') ? $buttons['normal'] : $buttons[$type];
        $parser->setRestriction($allowed_buttons);
        // Parsear menciones si el tipo es 'normal' o 'smiles'
        if ($type === 'normal' || $type === 'smiles') {
            $parser->parseMentions();
        }
        // Parsear smiles si el tipo es 'normal', 'smiles' o 'news'
        $parser->parseSmiles();
        // Retornar resultado en HTML
        return $parser->getAsHtml();
    }

    # Funcion para realizar menciones y notificar a dicho usuario
    public function setMenciones(string $html = ''): string
    {
        global $tsUser;
        return preg_replace_callback('/\B@([a-zA-Z0-9_-]{4,16})\b/', function ($matches) use ($tsUser) {
            $username = $matches[1];
            $uid = $tsUser->getUserID($username);
            if (!$uid) {
                return $matches[0]; // Mención sin reemplazo
            }
            $url = "{$this->settings['url']}/perfil/{$username}";
            return "@<a href=\"{$url}\">{$username}</a>";
        }, $html);
    }

    /**
     * Obtiene los tiempos de actividad del usuario
     *
     * @return array Array con el tiempo de última actividad online e inactiva
    */
    public function lastActive(): array
    {
        $c_last_active = (int)$this->settings['c_last_active'] * 60;
        return [
            'online' => time() - $c_last_active,
            'inactive' => time() - ($c_last_active * 2)
        ];
    }

    /**
     * Obtiene el estado de un usuario
     *
     * @param int $uid ID del usuario
     * @return array Array con el estado del usuario y la clase CSS correspondiente
     */
    public function statusUser(int $uid = 0): array
    {
        $lastActive = $this->lastActive();
        // Obtiene la información del usuario desde la base de datos
        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT user_lastactive, user_baneado FROM @miembros WHERE user_id = $uid"));
        $lastActivity = (int) $data['user_lastactive'];
        // Determina el estado del usuario basado en la última actividad y si está baneado
        $status = match (true) {
            (int)$data['user_baneado'] > 0 => 'banned',
            $lastActivity > (int)$lastActive['online']  => 'online',
            $lastActivity > (int)$lastActive['inactive'] => 'inactive',
            default => 'offline'
        };

        return [
            't' => ucfirst($status),
            'css' => $status
        ];
    }
}
