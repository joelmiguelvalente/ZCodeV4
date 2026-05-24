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

use App\Database\DB;
use App\Models\{Core,User};
use App\Traits\Extras;

class Tops
{
    use Extras;

    protected Core $Core;
    protected User $User;

    private $filter = ['hoy' => 1, 'ayer' => 2, 'semana' => 3, 'mes' => 4, 'historico' => 5];

    public function __construct(Core $Core, User $User)
    {
        $this->Core = $Core;
        $this->User = $User;
    }

    private function getHomeTops(array|int $filter, string $method): array
    {
        if (is_array($filter)) {
            $result = [];
            foreach ($filter as $key => $rangeId) {
                $result[$key] = $this->{$method}($this->setTime($rangeId));
            }
            return $result;
        }
        return $this->{$method}($this->setTime($filter));
    }

    /*
        getHomeTopPosts() : TOP DE POST semana, histórico
    */
    public function getHomeTopPosts()
    {
        array_shift($this->filter);
        return $this->getHomeTops($this->filter, 'getHomeTopPostsQuery');
    }

    /*
        getHomeTopUsers() : TOP DE USUARIOS semana, histórico
    */
    public function getHomeTopUsers()
    {
        array_shift($this->filter);
        return $this->getHomeTops($this->filter, 'getHomeTopUsersQuery');
    }

    /*
        getTopUsers()
    */
    public function getTopUsers(int $fecha = 0, int $cat = 0)
    {
        $time = $this->setTime($fecha);
        $category = empty($cat) ? '' : 'AND post_category = ' . $cat;
        $params = [
            'start' => $time['start'],
            'end' => $time['end']
        ];
        // PUNTOS
        $data['puntos'] = DB::fetch("SELECT SUM(p.post_puntos) AS total, u.user_id, u.user_name FROM @posts AS p LEFT JOIN @miembros AS u ON p.post_user = u.user_id WHERE p.post_status = 0  AND p.post_date BETWEEN :start AND :end :category GROUP BY p.post_user ORDER BY total DESC LIMIT 10", [
            ...$params,
            'category' => $category
        ]);
        // SEGUIDORES
        $data['seguidores'] = DB::fetch("SELECT COUNT(f.follow_id) AS total, u.user_id, u.user_name FROM @follows AS f LEFT JOIN @miembros AS u ON f.f_id = u.user_id WHERE f.f_type = 1 AND f.f_date BETWEEN :start: AND :end: GROUP BY f.f_id ORDER BY total DESC LIMIT 10", $params);
        // MEDALLAS
        $data['medallas'] = DB::fetch("SELECT COUNT(m.medal_for) AS total, u.user_id, u.user_name, wm.medal_id FROM @medallas_assign AS m LEFT JOIN @miembros AS u ON m.medal_for = u.user_id LEFT JOIN @medallas AS wm ON wm.medal_id = m.medal_id WHERE wm.m_type = 1 AND m.medal_date BETWEEN :start AND :end GROUP BY m.medal_for ORDER BY total DESC LIMIT 10", $params);
        //
        return $data;
    }

    /*
        getTopPosts()
    */
    public function getTopPosts(int $fecha = 0, int $cat = 0): array
    {
        return [
            'puntos' => $this->getTopPostsVars($fecha, $cat, 'puntos'),
            'seguidores' => $this->getTopPostsVars($fecha, $cat, 'seguidores'),
            'comments' => $this->getTopPostsVars($fecha, $cat, 'comments'),
            'favoritos' => $this->getTopPostsVars($fecha, $cat, 'favoritos')
        ];
    }

    /*
        setTopPostsVars($text, $type)
    */
    private function getTopPostsVars(int $fecha = 0, int $cat = 0, string $type = ''): array
    {
        $data = $this->setTime($fecha);
        if (!empty($cat)) {
            $data['scat'] = 'AND c.cid = ' . $cat;
        }
        $data['type'] = 'p.post_' . $type;
        return $this->getTopPostsQuery($data);
    }

    /*
        getTopPostsQuery($data)
    */
    public function getTopPostsQuery(array $data = []): array
    {
        $sql = "SELECT p.post_id, p.post_category, p.post_portada, :type, p.post_puntos, p.post_title, c.c_seo, c.c_img FROM @posts AS p LEFT JOIN @posts_categorias AS c ON c.cid = p.post_category WHERE p.post_status = 0 AND p.post_date BETWEEN :start AND :end :scat ORDER BY :type DESC LIMIT 10";
        $datos = DB::fetchAll($sql, [
        'start' => $date['start'],
          'end' => $date['end'],
          'scat' => $data['scat'] ?? '',
          'type' => $date['type']
        ]);
        foreach ($datos as $pid => $post) {
            $datos[$pid]['post_title'] = stripslashes($post['post_title']);
        }
        //
        return $datos;
    }

    /*
        getHomeTopPostsQuery($data)
    */
    public function getHomeTopPostsQuery(array $date = []): array
    {
        $sql = "SELECT p.post_id, p.post_category, p.post_portada, p.post_title, p.post_puntos, c.c_seo FROM @posts AS p LEFT JOIN @posts_categorias AS c ON c.cid = p.post_category WHERE p.post_status = 0 AND p.post_date BETWEEN :start AND :end ORDER BY p.post_puntos DESC LIMIT 15";
        $data = DB::fetchAll($sql, [
        'start' => $date['start'],
          'end' => $date['end']
        ]);
        foreach ($data as $pid => $post) {
            $data[$pid]['post_title'] = stripslashes($post['post_title']);
            $data[$pid]['post_url'] = $this->createLink('post', $post['post_id']);
        }
        return $data;
    }

    /*
        getHomeTopUsersQuery($date)
    */
    public function getHomeTopUsersQuery(array $date = []): array
    {
        $sql = "SELECT SUM(p.post_puntos) AS total, u.user_id, u.user_name FROM @posts AS p LEFT JOIN @miembros AS u ON p.post_user = u.user_id WHERE p.post_status = 0 AND p.post_date BETWEEN :start AND :end GROUP BY p.post_user ORDER BY total DESC LIMIT 10";
        return DB::fetchAll($sql, [
        'start' => $date['start'],
          'end' => $date['end']
        ]);
    }

    private function getStatsWithoutTable(&$return)
    {
        $sentencias = [
            'miembros' => "SELECT COUNT(user_id) as total FROM @miembros WHERE user_activo = 1 AND user_baneado = 0",
            'posts' => "SELECT COUNT(post_id) as total FROM @posts WHERE post_status = 0",
            'fotos' => "SELECT COUNT(foto_id) as total FROM @fotos WHERE f_status = 0",
            'comments' => "SELECT COUNT(cid) as total FROM @posts_comentarios WHERE c_status = 0",
            'foto_comments' => "SELECT COUNT(cid) as total FROM @fotos_comentarios"
        ];
        foreach ($sentencias as $who => $sql) {
            $row = DB::fetch($sql);
            $return['stats_' . $who] = (int) ($row['total'] ?? 0);
        }
        return $return;
    }

    /*
        getStats() : NADA QUE VER CON LA CLASE PERO BUENO PARA AHORRAR ESPACIO...
        : ESTADISTICAS DE LA WEB
    */
    public function getStats()
    {
        $time = time();
        $ndat = '';
        // OBTENEMOS LAS ESTADISTICAS
        $return = DB::fetch("SELECT stats_max_online, stats_max_time, stats_time, stats_time_cache, stats_miembros, stats_posts, stats_fotos, stats_comments, stats_foto_comments FROM @stats WHERE stats_no = 1");
        if ((int)$return['stats_time_cache'] > $time - ((int)$this->Core->settings['c_stats_cache'] * 60)) {
            // MIEMBROS
            $this->getStatsWithoutTable($return);

            $ndat = ", stats_time_cache = {$time}, stats_miembros = {$return['stats_miembros']}, stats_posts = {$return['stats_posts']}, stats_fotos = {$return['stats_fotos']}, stats_comments = {$return['stats_comments']}, stats_foto_comments = {$return['stats_foto_comments']}";
        }
        // PARA SABER SI ESTA ONLINE
        $is_online = ($time - ((int)$this->Core->settings['c_last_active'] * 60));
        // USUARIOS ONLINE - COMPROBAMOS SI CONTAMOS A TODOS LOS USUARIOS O SOLO A REGISTRADOS
        if ((int)$this->Core->settings['c_count_guests']) {
            $sentencia = "COUNT(user_id) AS u FROM @miembros WHERE `user_lastactive`";
        } else {
            $sentencia = "COUNT(DISTINCT `session_ip`) AS s FROM @sessions WHERE `session_time`";
        }
        $return['stats_online'] = DB::rowCount("SELECT $sentencia > $is_online");

        $timen = ($return['stats_online'] > (int)$return['stats_max_online']) ? ", stats_max_online = {$return['stats_online']}, stats_max_time = $time" : '';

        DB::execute("UPDATE @stats SET stats_time = $time $ndat $timen");
        if ((int)$this->Core->settings['c_ver_vistas_global']) {
            $return['stats_global'] = $this->updateActivity();
        }
        if ((int)$this->Core->settings['c_quitar_vistas_global']) {
            $this->cleanInactiveUsers();
        }
        //
        return $return;
    }

    public function updateActivity()
    {
        $ip = $this->User->info['session_ip'] ?? $_SERVER['REMOTE_ADDR'];
        $session_id = session_id(); // ID único de la sesión
        $user_id = $this->User->info['user_id'] ?? 0; // ID único del usuario (si aplica)
        $current_time = time();

    // Identificador único: combina sesión y usuario
        $unique_id = $user_id ? $user_id : $session_id;

    // Insertar o actualizar actividad
        DB::execute([__FILE__, __LINE__], 'query', "INSERT INTO @conexion_actual (ip, session_id, last_activity) VALUES (:ip, :unique_id, :current_time) ON DUPLICATE KEY UPDATE last_activity = :current_time", [
        'ip' => $ip,
            'unique_id' => $unique_id,
            'current_time' => $current_time
        ]);
        $data = DB::fetch("SELECT COUNT(*) AS total_visitas FROM @conexion_actual");
        return (int)$data['total_visitas'];
    }

    public function cleanInactiveUsers()
    {
        $timeout = time() - ((int)$this->Core->settings['c_visitas_tiempo'] * 60); // Usuarios inactivos por más de 5 minutos
        DB::execute("DELETE FROM @conexion_actual WHERE last_activity < :timeout", ['timeout' => $timeout]);
    }

    /*
        setTime($fecha)
    */
    public function setTime(int $fecha = 0): array
    {
        $ahora = time();
        return match ($fecha) {
           // HOY
            1 => ['start' => strtotime('today'),'end' => strtotime('tomorrow') - 1],
           // AYER
            2 => ['start' => strtotime('yesterday'),'end' => strtotime('today') - 1],
           // SEMANA
            3 => ['start' => strtotime('-1 week'),'end' => strtotime('tomorrow') - 1],
           // MES
            4 => ['start' => strtotime('first day of this month', $ahora),'end' => strtotime('tomorrow', $ahora) - 1],
           // TODO EL TIEMPO (default)
            default => ['start' => 0,'end' => $ahora],
        };
    }
}
