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

use App\Traits\{PostsHelper,Country,System};
use App\Contexts\HomeContext;
use App\Database\DB;
use App\Repository\PostsRepository;

class Posts
{
    use PostsHelper;
    use Country;
    use System;

    protected $cache;

    protected HomeContext $Home;
    protected PostsRepository $repo;

    public function __construct(HomeContext $Home, PostsRepository $repo)
    {
        $this->Home = $Home;
        $this->repo = $repo;
    }

    /**
     * Acortador de post automático
     * @author KMario19
     * Formateado por
     * @author Miguel92
     * @link https://www.phpost.net/foro/topic/24984-mod-acortador-de-post-autom%C3%A1tico/
    */
    public function shortUrlPost(): void
    {
        $postId = (int) ($_GET['p'] ?? 0);
        if ($postId <= 0) {
            $this->redirectLinkPost();
            return;
        }
        $isAdmod = $this->Home->Content->isAdmod();
        $postIdFound = $this->repo->findPublicPostId($postId, $isAdmod);
        if ($postIdFound === null) {
            $this->redirectLinkPost();
            return;
        }
        $this->redirectLinkPost($postIdFound);
    }

    /*
      OBTENER LOS TITULOS DE LOS POSTS ANTERIOR/SIGUIENTE
   */
    public function getTitles(?string $from = ''): array|bool
    {
        $postid = (int)($_GET["post_id"] ?? 0);
       // Consulta para obtener el post más cercano en la dirección deseada
        $data = $this->repo->findPublicPostTitle(
            ($from === 'prev' ? "<" : ">"),
            ($from === 'prev' ? "DESC" : "ASC"),
            $postid
        );
        if (!empty($data)) {
            $data['post_title'] = stripslashes($data['post_title']);
            $data["post_url"] = $this->Home->Core->createLink('post', $data['post_id']);
        }
        return !empty($data) ? $data : false;
    }

    /**
     * setNP()
     * @access public
     * return redirecciona a post
    */
    public function setNP(): void
    {
        $action = $_GET['action'] ?? 'next';
        $direction = match ($action) {
            'prev'       => 'prev',
            'fortuitae'  => 'random',
            default      => 'next',
        };
        $currentPostId = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $isAdmod = $this->Home->Content->isAdmod();
        $postId = $this->repo->findAdjacentPost($direction, $currentPostId, $isAdmod);

        if ($postId === null) {
            $this->redirectLinkPost();
            return;
        }

        $this->redirectLinkPost($postId);
    }

    private function getPostStats(&$postData, int $post_id = 0): void
    {
        $data['post_cache'] = time();
        //ESTADÍSTICAS
        #if((int)$postData['post_cache'] <= $time - ((int)$this->Home->Core->settings['c_stats_cache'] * 60)) {
            // NÚMERO DE COMENTARIOS
            $data['post_comments'] = DB::rowCount("SELECT COUNT(u.user_name) AS c FROM @miembros AS u LEFT JOIN @posts_comentarios AS c ON u.user_id = c.c_user WHERE c.c_post_id = $post_id AND c.c_status = 0 AND u.user_activo = 1 AND u.user_baneado = 0");
            // NÚMERO DE SEGUIDORES
            $data['post_seguidores'] = DB::rowCount("SELECT COUNT(u.user_name) AS s FROM @miembros AS u LEFT JOIN @follows AS f ON u.user_id = f.f_user WHERE f.f_type = 2 AND f.f_id = $post_id AND u.user_activo = 1 AND u.user_baneado = 0");
            // NÚMERO DE SEGUIDORES
            $data['post_shared'] = DB::rowCount("SELECT COUNT(follow_id) AS m FROM @follows WHERE f_type = 3 AND f_id = $post_id");
            // NÚMERO DE FAVORITOS
            $data['post_favoritos'] = DB::rowCount("SELECT COUNT(fav_id) AS f FROM @posts_favoritos WHERE fav_post_id = $post_id");
            $postData += [...$data];
            //ACTUALIZAMOS LAS ESTADÍSTICAS
            DB::update('posts', $data, ['post_id' => $post_id]);
        #}
    }

    /*
        getPost()
    */
    public function getPost()
    {
        $post_id = (int)$_GET['post_id'];
        if (empty($post_id)) {
            return array('deleted','Oops! Este post no existe o fue eliminado.');
        }

        $this->darMedalla($post_id);
        $postData = $this->fetchPostData($post_id);

        if (empty($postData['post_id'])) {
            return $this->handleDeletedPost($post_id);
        } elseif ($this->isPostInReview($postData)) {
            return ['denunciado','Oops! El Post se encuentra en revisi&oacute;n.'];
        } elseif ($this->isPostPrivate($postData)) {
            return ['privado', $postData['post_title']];
        }

        $this->getPostStats($postData, $post_id);
        $postData = [
            ...$postData,
            'block' => $this->isUserBlocked((int)$postData['post_user']),
            'categoria' => $this->getPostCategory((int)$postData['post_category']),
            'follow' => $this->getPostFollowers($postData),
            'post_body' => $this->Home->Content->setParser($postData['post_body'], ($postData['post_smileys'] === 0 ? 'normal' : 'firma')),
            'post_descripcion' => $this->truncate($this->nobbcode($postData['post_body'])),
            'post_fuentes' => !empty($postData['post_fuentes']) ? json_decode($postData['post_fuentes'], true) : '',
            'post_hits' => $this->Home->Visitas->actualizarVisitas((int)$post_id, (int)$this->Home->User->uid, 3),
            'post_ip' => $postData['post_ip'] ?? $this->Home->Core->getIP(),
            'post_read' => $this->readingTime($postData['post_body']),
            'post_stats' => $this->countSharedIn($post_id, (int)$this->Home->User->uid),
            'post_tags' => explode(",", $postData['post_tags']),
            'post_vote' => $this->hasUserVoted($post_id),
            'user_firma' => $this->Home->Content->setParser($postData['user_firma'] ?? '', 'firma'),
        ];
        $this->Home->Content->general($postData, (int)$postData['post_id'], $postData['post_title']);
        $this->Home->Visitas->recordarVisita((int)$post_id, 3, (int)$this->Home->User->uid);
        return $postData;
    }

    private function fetchPostData(int $post_id = 0): array
    {
        $isAdmod = '';
        if (!empty($this->Home->Content->isAdmod())) {
            $isAdmod = "AND " . $this->Home->Content->isAdmod();
        }
        return DB::fetch(
            "SELECT c.* ,m.*, u.user_id FROM @posts AS c LEFT JOIN @miembros AS u ON c.post_user = u.user_id LEFT JOIN @perfil AS m ON c.post_user = m.user_id WHERE `post_id` = :pid $isAdmod LIMIT 1",
            ['pid' => $post_id]
        );
    }

    private function handleDeletedPost(int $post_id = 0): array
    {
        $tsDraft = DB::fetch(
            "SELECT b_title FROM @posts_borradores WHERE b_post_id = :pid LIMIT 1",
            ['pid' => $post_id]
        );
        $text = (!empty($tsDraft['b_title'])) ? 'Este post no existe o fue eliminado.' : 'El post fue eliminado!';
        return ['deleted', 'Oops! ' . $text];
    }

    private function isPostInReview(array $postData = []): bool
    {
        return
        ($postData['post_status'] === 1 and (!$this->Home->User->is_admod and $this->Home->User->permisos['moacp'] === false)) ||
        ($postData['post_status'] === 2 and (!$this->Home->User->is_admod and $this->Home->User->permisos['morp'] === false)) ||
        ($postData['post_status'] === 3 and (!$this->Home->User->is_admod and $this->Home->User->permisos['mocp'] === false));
    }

    private function isPostPrivate(array $postData = []): bool
    {
        return !empty($postData['post_private']) and empty($this->Home->User->is_member);
    }

    private function isUserBlocked(int $post_user = 0): int
    {
        return DB::rowCount("SELECT bid FROM @bloqueos WHERE b_user = :aUser AND b_auser = :bUser LIMIT 1", [
            'aUser' => $post_user,
            'bUser' => $this->Home->User->uid
        ]);
    }

    private function getPostFollowers(array $postData = []): int
    {
        if ((int)$postData['post_seguidores'] > 0) {
            return (int) DB::rowCount("SELECT COUNT(follow_id) AS f FROM @follows WHERE f_id = :id AND f_user = :user AND f_type = 2", [
                'id' => $postData['post_id'],
                'user' => $this->Home->User->uid
            ]);
        }
        return 0;
    }

    private function getPostPoints(array $postData = []): array
    {
        if ((int)$postData['post_user'] === $this->Home->User->uid || $this->Home->User->is_admod) {
            return DB::fetchAll("SELECT p.*, u.user_id, u.user_name FROM @posts_votos AS p LEFT JOIN @miembros AS u ON p.tuser = u.user_id WHERE p.tid = :pid AND p.type = 1 ORDER BY p.voto_id DESC", [
                'pid' => $postData['post_id']
            ]);
        }
        return [];
    }

    private function getPostCategory(int $post_category = 0): array
    {
        return DB::fetch("SELECT c.c_nombre, c.c_seo FROM @posts_categorias AS c WHERE c.cid = :category", [
            'category' => $post_category
        ]);
    }

    private function hasUserVoted(int $post_id = 0)
    {
        $vote = DB::rowCount("SELECT COUNT(voto_id) FROM @posts_votos WHERE tid = :tid AND tuser = :user LIMIT 1", [
            'tid' => $post_id,
            'user' => $this->Home->User->uid
        ]);
        return !empty($vote);
    }

    private function countSharedIn(int $pid = 0, int $uid = 0): array
    {
        $exists = DB::rowCount("SELECT stats_user FROM @posts_stats WHERE stats_post_id = :pid AND stats_in = :in LIMIT 1", [
            'pid' => $pid,
            'in' => $this->Home->Core->setSecure($_GET['in'] ?? '')
        ]);
        $visitas = [
            'facebook' => 0,
            'twitter' => 0,
            'telegram' => 0,
            'whatsapp' => 0
        ];
        foreach ($visitas as $vid => $visita) {
            $query = DB::fetch("SELECT COUNT(sid) AS total FROM @posts_stats WHERE stats_in = :vid AND stats_post_id = :pid", [
                'vid' => $vid,
                'pid' => $pid
            ]);
            $visitas[$vid] = (int)$query['total'];
        }
        return $visitas;
    }

    /*
        getSideData($array)
    */
    public function getAutor(int $user_id = 0)
    {
        // DATOS DEL AUTOR
        $data = DB::fetch("SELECT u.user_id, u.user_name, u.user_rango, u.user_puntos, u.user_lastactive, u.user_registro, u.user_last_ip, u.user_activo, u.user_baneado, p.user_pais, p.user_sexo, p.user_firma FROM @miembros AS u LEFT JOIN @perfil AS p ON u.user_id = p.user_id WHERE u.user_id = :uid LIMIT 1", [
            'uid' => $user_id
        ]);
        //
        $param = ['uid' => $user_id];
        $data['user_seguidores'] = DB::rowCount("SELECT follow_id FROM @follows WHERE f_id = :uid AND f_type = 1", $param);
        $data['user_comentarios'] = DB::rowCount("SELECT cid FROM @posts_comentarios WHERE c_user = :uid AND c_status = 0", $param);
        $data['user_posts'] = DB::rowCount("SELECT post_id FROM @posts WHERE post_user = :uid AND post_status = 0", $param);
        // RANGOS DE ESTE USUARIO
        $data['rango'] = DB::fetch("SELECT r_name, r_color, r_image FROM @rangos WHERE rango_id = :rid LIMIT 1", [
            'rid' => $data['user_rango']
        ]);
        $data['rango_image'] = $this->Home->Core->route('assets:images') . '/rangos/' . $data['rango']['r_image'];
        // STATUS
        $data['status'] = $this->Home->Core->statusUser($user_id);
        // PAIS
        $data['pais'] = $this->countryUser($data['user_pais']);
        // FOLLOWS
        if ($data['user_seguidores'] > 0) {
            $query = "SELECT follow_id FROM @follows WHERE f_id = :fid AND f_user = :f_user AND f_type = 1";
            $data['follow'] = DB::rowCount($query, [
                'f_id' => $user_id,
                'f_user' => $this->Home->User->uid
            ]);
        }
        $data['user_avatar'] = $this->Home->User->use_avatar;
        // RETURN
        return $data;
    }

    /*
        lalala
    */
    public function getPunteador(bool $puntuador = false)
    {
        $allow = $this->Home->Core->settings['c_allow_points'];
        $data['rango'] = ($allow > 0) ? $allow : ($allow == '-1' ? $this->Home->User->info['user_puntosxdar'] : ($allow == '-2' ? 999 : $this->Home->User->permisos['gopfp'] ?? 0));
        return $puntuador ? $data : $data['rango'];
    }

    /*
        deletePost()
    */
    public function deletePost()
    {
        $post_id = (int)$_POST['postid'];
        // ES SU POST EL Q INTENTA BORRAR?
        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT post_id, post_title, post_user, post_body, post_category FROM @posts WHERE post_id = $post_id AND post_user = {$this->Home->User->uid}"));
        //
        statsUpdate([__FILE__, __LINE__], ['table' => '@stats', 'columna' => 'stats_posts', 'donde' => "stats_no = 1"]);
        statsUpdate([__FILE__, __LINE__], ['table' => '@miembros', 'columna' => 'user_posts', 'donde' => "user_id = {$data['post_user']}"]);
        // ES MIO O SOY MODERADOR/ADMINISTRADOR...
        if (empty($data['post_id']) || empty($this->Home->User->is_admod)) {
            return '0: Lo que intentas no est&aacute; permitido.';
        }
        // SI ES MIS POST LO BORRAMOS Y MANDAMOS A BORRADORES
        if (removeDataById([__FILE__, __LINE__], '@posts', "post_id = $post_id")) {
            if (removeDataById([__FILE__, __LINE__], '@posts_comentarios', "c_post_id = $post_id")) {
                $info = [
                    'user' => $this->Home->User->uid,
                    'date' => time(),
                    'title' => $this->Home->Core->setSecure($data['post_title']),
                    'body' => $this->Home->Core->setSecure($data['post_body']),
                    'tags' => '',
                    'category' => $data['post_category'],
                    'status' => 2,
                    'causa' => ''
                ];
                if (addDataToTable([__FILE__, __LINE__], '@posts_borradores', $info, 'b_')) {
                    return "1: El post fue eliminado satisfactoriamente.";
                }
            }
        } else {
            if (db_exec([__FILE__, __LINE__], 'query', "UPDATE @posts SET post_status = 2 WHERE post_id = $post_id")) {
                return "1: El post se ha eliminado correctamente.";
            }
        }
    }

    public function deleteAdminPost()
    {
        $pid = (int)$_POST['postid'];
        if ($this->Home->User->is_admod !== 1) {
            return '0: Para el carro chacho';
        }
        if (!db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT post_id FROM @posts WHERE post_id = $pid AND post_status = 2"))) {
            return '0: El post ya se encuentra eliminado';
        }
        if (!removeDataById([__FILE__, __LINE__], '@posts', "post_id = $pid")) {
            return '0: Ha ocurrido un error eliminando el post.';
        }
        if (!removeDataById([__FILE__, __LINE__], '@posts_comentarios', "c_post_id = $pid")) {
            return '0: Ha ocurrido un error eliminando comentarios del post.';
        }
        db_exec([__FILE__, __LINE__], 'query', "UPDATE @stats SET stats_posts = stats_posts - 1 WHERE stats_no = 1");
        return "1: El post se ha eliminado correctamente.";
    }

    private function getRelatedPostAutor($postData)
    {
        foreach ($postData as $pid => $post) {
            $this->Home->Content->general($postData[$pid], $post['post_id'], $post['post_title']);
            $postData[$pid]['c_img'] = $this->Home->Core->route('assets:categorias') . '/' . $post['c_img'];
            // Portada
            $postData[$pid]['post_new'] = $this->tagsNew($post['post_date']);
        }
        return $postData;
    }
    /*
        getRelated()
    */
    public function getRelated(string|array $tags)
    {
        // ES UN ARRAY AHORA A UNA CADENA
        $search = !empty($tags) ? implode(",", $tags) : str_replace('-', ' ', $this->Home->Core->setSecure($_GET['title']));
        $match = !empty($tags) ? 'post_tags' : 'post_title';
        //
        $pid = (int)$_GET['post_id'] ?? 0;
        //
        $data = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT DISTINCT p.post_id, p.post_title, p.post_category, p.post_private, p.post_portada, p.post_body, p.post_date, c.c_nombre, c.c_seo, c.c_img, u.user_id, u.user_name FROM @posts AS p LEFT JOIN @posts_categorias AS c ON c.cid = p.post_category LEFT JOIN @miembros AS u ON u.user_id = p.post_user WHERE MATCH ($match) AGAINST ('$search' IN BOOLEAN MODE) AND p.post_status = 0 AND post_sticky = 0 AND p.post_id != $pid ORDER BY rand() LIMIT 0, 5"));
        $data = $this->getRelatedPostAutor($data);
        return $data;
    }

    public function getPostAutor(int $uid = 0)
    {
        $pid = (int)$_GET['post_id'] ?? 0;
        $data = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT DISTINCT p.post_id, p.post_title, p.post_category, p.post_private, p.post_portada, p.post_body, p.post_date, c.c_nombre, c.c_seo, c.c_img, u.user_id, u.user_name FROM @posts AS p LEFT JOIN @posts_categorias AS c ON c.cid = p.post_category LEFT JOIN @miembros AS u ON u.user_id = p.post_user WHERE p.post_status = 0 AND post_sticky = 0 AND p.post_user = $uid AND p.post_id != $pid ORDER BY rand() LIMIT 0, 10"));
        $data = $this->getRelatedPostAutor($data);
        return $data;
    }

    /*
        votarPost()
    */
    public function votarPost()
    {
        global $tsMonitor, $tsActividad;
        #GLOBALES
        if (!$this->Home->User->is_admod || !$this->Home->User->permisos['godp']) {
            return '0: No tienes permiso para hacer esto.';
        }
        //Comprobamos si otro usuario ha votado un post con esta ip
        $myIP = $this->Home->Core->executeIP();
        $time = time();
        /*
        if($this->Home->User->is_admod != 1) {
            if(
                db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT user_id FROM @miembros WHERE user_last_ip = '$myIP' AND user_id != {$this->Home->User->uid}")) ||
                db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT session_id FROM @sessions WHERE session_ip = '$myIP' AND session_user_id != {$this->Home->User->uid}"))
            ) return '0: Has usado otra cuenta anteriormente, deber&aacute;s contactar con la administraci&oacute;n.';
        }*/

        $post_id = (int)$_POST['postid'];
        $puntos  = (int)$_POST['puntos'] === 2 ? 2 : 1;
        // SUMAR PUNTOS
        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT post_user FROM @posts WHERE post_id = $post_id LIMIT 1"));
        $userPost = (int)$data['post_user'];
        // NO ES MI POST, PUEDO VOTAR
        if ($userPost === $this->Home->User->uid) {
            return '0: No puedes votar tu propio post.';
        }
        // YA LO VOTE?
        $votado = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT tid FROM @posts_votos WHERE tid = $post_id AND tuser = {$this->Home->User->uid} AND type = 1 LIMIT 1"));
        if (!empty($votado)) {
            return '0: No es posible votar a un mismo post m&aacute;s de una vez.';
        }
        // COMPROBAMOS LOS PUNTOS QUE PODEMOS DAR
        $max_points = $this->getPunteador(true);
        // TENGO SUFICIENTES PUNTOS
        if ($this->Home->User->info['user_puntosxdar'] <= $puntos) {
            return "'0: Voto no v&aacute;lido. No puedes dar $puntos puntos, s&oacute;lo te quedan {$this->Home->User->info['user_puntosxdar']}.'";
        }
        if ($puntos === 0) {
            return '0: Voto no v&aacute;lido. No puedes no dar puntos.';
        }
        if ($puntos >= $max_points) {
            return "0: Voto no v&aacute;lido. No puedes dar $puntos puntos, s&oacute;lo se permiten $max_points";
        }
        // SUMAR PUNTOS AL POST
        $mp = ($puntos == 2) ? "-" : "+";
        db_exec([__FILE__, __LINE__], 'query', "UPDATE @posts SET post_puntos = post_puntos $mp 1 WHERE post_id = $post_id");
        // SUMAR PUNTOS AL DUEÑO DEL POST
        db_exec([__FILE__, __LINE__], 'query', "UPDATE @miembros SET user_puntos = user_puntos $mp 1 WHERE user_id = $userPost");
        // RESTAR PUNTOS AL VOTANTE
        db_exec([__FILE__, __LINE__], 'query', "UPDATE @miembros SET user_puntosxdar = user_puntosxdar - 1 WHERE user_id = {$this->Home->User->uid}");
        // INSERTAR EN TABLA
        db_exec([__FILE__, __LINE__], 'query', "INSERT INTO @posts_votos (tid, tuser, cant, type, date) VALUES ($post_id, {$this->Home->User->uid}, $puntos, 1, $time)");
        // AGREGAR AL MONITOR
        $tsMonitor->setNotificacion(3, $userPost, $this->Home->User->uid, $post_id, $puntos);
        // ACTIVIDAD
        $tsActividad->setActividad(3, $post_id, $puntos);
        // SUBIR DE RANGO
        $this->subirRango($data['post_user'], $post_id);
        return '1: Puntos agregados!';
    }

    /*
        subirRango()
    */
    public function subirRango(int $user_id = 0, int $post_id = 0)
    {
        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT u.user_puntos, u.user_rango, r.r_type FROM 	@miembros AS u LEFT JOIN @rangos AS r ON u.user_rango = r.rango_id WHERE u.user_id = $user_id LIMIT 1"));
        if (empty($data['r_type']) and $data['user_rango'] !== 3) {
            return true;
        }
        if (!empty($post_id) and (int)$this->Home->Core->settings['c_newr_type'] === 0) {
            $puntos = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT post_puntos FROM @posts WHERE post_id = 	$post_id LIMIT 1"));
            $data['user_puntos'] = $puntos['post_puntos'];
        }
        $stats = [
          'puntos' => $data['user_puntos'],
          'posts' => db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(post_id) FROM @posts WHERE 	post_user = $user_id AND post_status = 0"))[0],
          'fotos' => db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(foto_id) FROM @fotos WHERE f_user = $user_id AND f_status = 0"))[0],
          'comentarios' => db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(cid) FROM @posts_comentarios 	WHERE c_user = $user_id AND c_status = 0"))[0]
        ];
        $rangos = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT rango_id, r_cant, r_type FROM @rangos WHERE r_type > 0 ORDER BY r_cant"));

        foreach ($rangos as $rango) {
            $dataRango = [
            1 => 'puntos',
            2 => 'posts',
            3 => 'fotos',
            4 => 'comentarios'
            ];
            if (!empty($rango['r_cant']) && (int)$rango['r_cant'] <= (int)$stats[array_search($rango['r_type'], $dataRango)]) {
                $newRango = $rango['rango_id'];
            }
        }
        if (!empty($newRango) && (int)$newRango !== (int)$data['user_rango']) {
            return db_exec([__FILE__, __LINE__], 'query', "UPDATE @miembros SET user_rango = $newRango WHERE user_id = $user_id 	LIMIT 1");
        }
    }

    /*
        darMedalla()
    */
    public function darMedalla($post_id)
    {
        $MYIP = $this->Home->Core->executeIP();
        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT post_id, post_user, post_puntos, post_hits FROM @posts WHERE post_id = $post_id LIMIT 1"));

        $queries = [
        "SELECT COUNT(follow_id) FROM @follows WHERE f_id = $post_id AND f_type = 2",
        "SELECT COUNT(cid) FROM @posts_comentarios WHERE c_post_id = $post_id AND c_status = 0",
        "SELECT COUNT(fav_id) FROM @posts_favoritos WHERE fav_post_id = $post_id",
        "SELECT COUNT(did) FROM @denuncias WHERE obj_id = $post_id AND d_type = 1",
        "SELECT COUNT(wm.medal_id) FROM @medallas AS wm LEFT JOIN @medallas_assign AS wma ON wm.medal_id = wma.medal_id WHERE wm.m_type = 2 AND wma.medal_for = $post_id",
        "SELECT COUNT(follow_id) FROM @follows WHERE f_id = $post_id AND f_type = 3"
        ];
        $results = array_map(fn($q) => db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', $q))[0], $queries);
        $datamedal = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT medal_id, m_cant, m_cond_post FROM @medallas WHERE m_type = 2 ORDER BY m_cant DESC"));

        foreach ($datamedal as $medalla) {
            $conditions = [
            1 => $data['post_puntos'],
            2 => $results[0],
            3 => $results[1],
            4 => $results[2],
            5 => $results[3],
            6 => $data['post_hits'],
            7 => $results[4],
            8 => $results[5]
            ];

            if (!empty($conditions[$medalla['m_cond_post']]) && $medalla['m_cant'] > 0 && $medalla['m_cant'] <= $conditions[$medalla['m_cond_post']]) {
                $newmedalla = $medalla['medal_id'];
                if (!db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT id FROM @medallas_assign WHERE medal_id = $newmedalla AND medal_for = $post_id"))) {
                      db_exec([__FILE__, __LINE__], 'query', "INSERT INTO @medallas_assign (medal_id, medal_for, medal_date, medal_ip) VALUES ($newmedalla, $post_id, time(), '$MYIP')");
                      db_exec([__FILE__, __LINE__], 'query', "INSERT INTO @monitor (user_id, obj_uno, obj_dos, not_type, not_date) VALUES ({$data['post_user']}, $newmedalla, $post_id, 16, time())");
                      db_exec([__FILE__, __LINE__], 'query', "UPDATE @medallas SET m_total = m_total + 1 WHERE medal_id = $newmedalla");
                }
            }
        }
    }
}
