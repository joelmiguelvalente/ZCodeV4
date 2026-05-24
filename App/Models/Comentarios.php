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

use App\Models\{Actividad,Core,User};
use App\Utils\Paginator;
use App\Traits\Extras;
use App\Database\DB;

class Comentarios
{
    use Extras;

    private $limitar = 500;

    protected Core $Core;
    protected User $User;
    protected Actividad $Actividad;
    protected Paginator $Paginator;

    public function __construct(Core $Core, User $User, Actividad $Actividad, Paginator $Paginator)
    {
        $this->Core = $Core;
        $this->User = $User;
        $this->Actividad = $Actividad;
        $this->Paginator = $Paginator;
    }

    protected function isAdmod(): bool
    {
        return ($this->User->is_admod and (int)$this->Core->settings['c_see_mod'] === 1);
    }

    /*
        getLastComentarios()
        : PARA EL PORTAL
    */
    public function getLastComentarios(): array
    {
        //
        $isAdmod = $this->isAdmod() ? '' : 'WHERE p.post_status = 0 AND cm.c_status = 0 AND u.user_activo = 1 && u.user_baneado = 0';
        $query = "SELECT cm.cid, cm.c_status, u.user_id, u.user_name, u.user_activo, u.user_baneado, p.post_id, p.post_title, p.post_status, c.c_seo FROM @posts_comentarios AS cm LEFT JOIN @miembros AS u ON cm.c_user = u.user_id LEFT JOIN @posts AS p ON p.post_id = cm.c_post_id LEFT JOIN @posts_categorias AS c ON c.cid = p.post_category $isAdmod ORDER BY cid DESC LIMIT 10";
        $data = DB::fetchAll($query);
        foreach ($data as $cid => $comentario) {
            $data[$cid] = [
                'post_title_a' => stripslashes($comentario['post_title']),
                'cm_url' => $this->Core->route('url') . $this->createLink('post', (int)$comentario['post_id'], "#comment{$comentario['cid']}")
            ];
        }
        //
        return $data;
    }

    /**
     * Obtiene los comentarios y respuestas de un post o answer.
     *
     * @param int $id El ID del post o answer.
     * @param string $type El tipo de objeto (post o answer).
     * @return mixed Resultado de la consulta a la base de datos.
    */
    private function getCommentsAndAnswer(int $id = 0, string $type = 'post')
    {
        // Establecer el límite de página
        $start = $this->Paginator->setPageLimit((int)$this->Core->settings['c_max_com']);
    // Condiciones según el rol del usuario
        $cstatus = $this->User->is_admod ? '' : "AND c_status = 0";
        $admod = $this->User->is_admod ? '' : "$cstatus AND u.user_activo = 1 && u.user_baneado = 0";
       // Determinar la columna para la consulta
        $column = ($type === 'post') ? "c_post_id = '$id' AND c.c_answer = 0" : "c_answer = '$id'";
       // Ejecutar la consulta
        $query = db_exec([__FILE__, __LINE__], 'query', "SELECT u.user_id, u.user_name, u.user_activo, u.user_baneado, c.* FROM @miembros AS u LEFT JOIN @posts_comentarios AS c ON u.user_id = c.c_user WHERE $column $admod ORDER BY c.cid LIMIT $start");
        return $query;
    }

    /**
     * Verifica si un usuario ha votado por un comentario específico.
     *
     * @param int $cid El ID del comentario.
     * @return int Retorna 1 si el usuario ha votado, 0 si no.
    */
    private function isVoted(int $cid = 0)
    {
        $voto = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(r_comment_id) FROM @comentarios_reaccion WHERE r_comment_id = $cid LIMIT 1"))[0];
        return $voto;
    }

    /**
     * Procesa cada comentario para agregar información adicional como si está bloqueado o si ha sido votado.
     *
     * @param array $foreach Arreglo de comentarios a procesar.
     * @param bool $isAnswer Indica si se deben obtener las respuestas de los comentarios.
     * @return array Arreglo de comentarios procesados con información adicional.
    */
    private function getCommentsAndAnswerForeach(array $foreach = [], bool $isAswer = false)
    {
        $response = [];
        foreach ($foreach as $id => $comment) {
          // Verificar si el usuario está bloqueado
            $response[$id]['block'] = $this->User->isUserBloqued($comment['c_user'], $this->User->uid);
            // Agregar información adicional al comentario
            $response[$id] = $comment;
            $response[$id]['votado'] = $this->isVoted($comment['cid']);
            $response[$id]['c_html'] = $this->Core->parseBadWords($this->Core->parseBBCode($comment['c_body']), true);
            $response[$id]['c_avatar'] = $this->Core->getAvatar($comment['user_id'], 'use');
            $response[$id]['respuesta'] = !empty($comment['c_answer']);
            // Obtener respuestas si es necesario
            if ($isAswer) {
                $response[$id]['respuestas'] = $this->getComentarios((int)$comment['cid'], 'answer');
            }
        }
        return $response;
    }

    /**
     * Obtiene todos los comentarios de un post o respuesta, junto con información adicional.
     *
     * @param int $objectID El ID del post o respuesta.
     * @param string $type El tipo de objeto (post o respuesta).
     * @return array Arreglo que contiene el número total de comentarios y los comentarios procesados.
    */
    public function getComentarios(int $objectID = 0, string $type = 'post'): array
    {
        $response = [];
        // Obtener todos los comentarios
        $getAllComments = result_array($this->getCommentsAndAnswer($objectID, $type));
        // Determinar la columna para la consulta
        $column = ($type === 'post') ? 'c_post_id' : 'c_answer';
        // Obtener el número total de comentarios
        $cstatus = $this->User->is_admod ? '' : "AND c_status = 0";
        $response['num'] = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT cid FROM @posts_comentarios WHERE $column = '$objectID' $cstatus"));
        // Procesar cada comentario
        $response['data'] = $this->getCommentsAndAnswerForeach($getAllComments, ($type === 'post'));
        //
        return $response;
    }

    private function limitComment(bool $parsear = false)
    {
        $substrCommnet = substr(urldecode($_POST['comentario']), 0, $this->limitar);
        return $parsear ? $this->Core->parseBadWords($this->Core->setSecure($substrCommnet, true)) : $substrCommnet;
    }

    private function verifyComment(string $comentario = ''): ?string
    {
        /* COMPROBACIONES */
        $tsText = preg_replace('# +#', "", $comentario);
        $tsText = str_replace("\n", "", $tsText);
        if (empty($tsText)) {
            return '0: El campo <strong>Comentario</strong> es requerido para esta operaci&oacute;n';
        }
        return $tsText;
    }

    private function manageStats(int $post_id = 0, int $user_id = 0, bool $sumar = true)
    {
        //SUMAMOS A LAS ESTADÍSTICAS
        $updates = [
            ['@stats', 'stats_comments', 'stats_no = 1'],
            ['@posts', 'post_comments', "post_id = $post_id"],
            ['@miembros', 'user_comentarios', "user_id = $user_id"]
        ];
        foreach ($updates as $update) {
            statsUpdate([__FILE__, __LINE__], ['table' => $update[0], 'columna' => $update[1], 'donde' => $update[2]], $sumar);
        }
    }

    /*
        newComentario()
    */
    public function newComentario()
    {
        // NO MAS DE 1500 CARACTERES PUES NADIE COMENTA TANTO xD
        $comentario = $this->limitComment();
        $post_id = (int)$_POST['postid'];
        /* DE QUIEN ES EL POST */
        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT post_user, post_block_comments FROM @posts WHERE post_id = $post_id LIMIT 1"));

        /* COMPROBACIONES */
        $tsText = $this->verifyComment($comentario);
        /*        ------       */
        $most_resp = $_POST['mostrar_resp'];
        $respuesta = isset($_POST['respuesta']) ? (int)$_POST['respuesta'] : 0;
        $fecha = time();
        //
        if (!$data['post_user']) {
            return '0: El post no existe.';
        }

        if ($data['post_block_comments'] != 1 || $data['post_user'] == $this->User->uid || $this->User->is_admod || $this->User->permisos['mocepc']) {
            if (empty($this->User->is_admod) && $this->User->permisos['gopcp'] == false) {
                return '0: No deber&iacute;as hacer estas pruebas.';
            }
            // ANTI FLOOD
            $this->Core->antiFlood();
            $IP = $this->Core->executeIP();

            if (db_exec([__FILE__, __LINE__], 'query', "INSERT INTO @posts_comentarios (`c_post_id`, `c_user`, `c_date`, `c_body`, `c_answer`, `c_ip`) VALUES ($post_id, {$this->User->uid}, $fecha, '$comentario', $respuesta, '$IP')")) {
                $cid = db_exec('insert_id');
                $this->manageStats((int)$post_id, (int)$this->User->uid);
                // NOTIFICAR SI FUE CITADO Y A LOS QUE SIGUEN ESTE POST, DUEÑO
                $this->createNewNotificationForUser((int)$post_id, (int)$data['post_user'], (int)$cid, $comentario);
                // ACTIVIDAD
                $this->Actividad->setActividad(5, $post_id);
                // array(comid, comhtml, combbc, fecha, autor_del_post)
                $parseado = $this->Core->parseBadWords($this->Core->parseBBCode($comentario), true);
                $act = !empty($most_resp) ? [$cid, $parseado, $comentario, $fecha, $_POST['auser'], '', $IP] : '1: Tu comentario fue agregado satisfactoriamente.';
                return $act;
            } else {
                return '0: Ocurri&oacute; un error int&eacute;ntalo m&aacute;s tarde.';
            }
        } else {
            return '0: El post se encuentra cerrado y no se permiten comentarios.';
        }
    }
     /*
          createNewNotificationForUser()
          :: Avisa cuando citan los comentarios.
     */
    private function createNewNotificationForUser($post_id, $post_user, $comentario_id, $comentario)
    {
        global $tsCore, $tsUser, $tsMonitor;
        $excluid_ids = [];
        $total = 0;
        //
        preg_match_all("/\[quote=(.*?)\]/is", $comentario, $users);
        if (!empty($users[1])) {
            foreach ($users[1] as $user) {
               // Obtener datos del usuario citado
                $udata = explode('|', $user);
                $user = empty($udata[0]) ? $user : $udata[0];
                $lcid = empty($udata[1]) ? $comentario_id : (int)$udata[1];
               // Comprobar y agregar notificación si el usuario no es el mismo que el actual
                if ($user != $this->User->nick) {
                    $uid = $this->User->getUserID($this->Core->setSecure($user));
                    if (!empty($uid) && $uid != $this->User->uid && !in_array($uid, $excluid_ids)) {
                        $excluid_ids[] = $uid;
                       # Solo si hay mencion hacia algun usuario
                        $tsMonitor->setNotificacion(9, $uid, $this->User->uid, $post_id, $lcid);
                    }
                    ++$total;
                }
            }
        }
        // AGREGAR AL MONITOR DEL DUEÑO DEL POST SI NO FUE CITADO
        if (!in_array($post_user, $excluid_ids)) {
            $tsMonitor->setNotificacion(2, $post_user, $this->User->uid, $post_id, $comentario_id);
        }
        $tsMonitor->setFollowNotificacion(7, 2, $this->User->uid, $post_id, $comentario_id, $excluid_ids);
        //
        return true;
    }

     /*
          editComentario()
     */
    public function editComentario()
    {
        global $tsUser, $tsCore;
        //
        $cid = (int)$_POST['cid'];
        $comentario = $this->limitComment(true);
        /* COMPROBACIONES */
        $tsText = $this->verifyComment($comentario);
          //
        $user = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT c_user FROM @posts_comentarios WHERE cid = $cid LIMIT 1"));
        //
        if ($this->User->is_admod || ((int)$this->User->uid == (int)$user['c_user'] && $this->User->permisos['goepc']) || $this->User->permisos['moedcopo']) {
            // ANTI FLOOD
            $this->Core->antiFlood();
            $time = time();
            $response = $this->Core->parseBBCode($comentario) . ' <small class="fst-italic" style="color:#888; font-size: 0.75rem;">- Editado Hace instantes</small>';
            return (db_exec([__FILE__, __LINE__], 'query', "UPDATE @posts_comentarios SET c_body = '$comentario', c_update = $time WHERE cid = $cid")) ? "1: $response" : '0: Ocurri&oacute; un error, no se pudo editar.';
        } else {
            return '0: Hey, este comentario no es tuyo.';
        }
    }
    /*
        delComentario()
    */
    public function delComentario()
    {
        global $tsCore, $tsUser;
        //
        $comid = (int)$_POST['comid'];
        $autor = (int)$_POST['autor'];
        $post_id = (int)$_POST['postid'] ?? (int)$_POST['post_id'];
        // Cargamos los comentarios solamente del post acutual
        if (!db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT cid FROM @posts_comentarios WHERE cid = $comid"))) {
            return '0: El comentario no existe';
        }
        // Es mi post?...
        $is_mypost = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT post_id FROM @posts WHERE post_id = $post_id AND post_user = {$this->User->uid}"));
        // Es mi comentario?...
        $is_mycmt = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT cid FROM @posts_comentarios WHERE cid = $comid AND c_user = {$this->User->uid}"));
        // SI ES....
        if (!empty($is_mypost) || (!empty($is_mycmt) && !empty($this->User->permisos['godpc'])) || !empty($this->User->is_admod) || !empty($this->User->permisos['moecp'])) {
            // DELETE FROM @posts_comentarios WHERE @posts_comentarios .`cid` = 2
            if (db_exec([__FILE__, __LINE__], 'query', "DELETE FROM @posts_comentarios WHERE cid = $comid AND c_user = $autor AND c_post_id = $post_id")) {
                // BORRAR LOS VOTOS
                db_exec([__FILE__, __LINE__], 'query', "DELETE FROM @posts_votos WHERE tid = $comid");
                // RESTAR EN LAS ESTADÍSTICAS
                $this->manageStats((int)$post_id, (int)$autor, false);
                //
                return '1: Comentario borrado.';
            } else {
                return '0: Ocurri&oacute; un error, intentalo m&aacute;s tarde.';
            }
        } else {
            return '0: No tienes permiso para hacer esto.';
        }
    }

    /*
        ocultarComentario()
    */
    public function ocultarComentario()
    {
        global $tsCore, $tsUser;
        //
        if (!$this->User->is_admod || !$this->User->permisos['moaydcp']) {
            return '0: No tienes permiso para hacer eso.';
        }
        //
        $comid = (int)$_POST['comid'];
        $autor = (int)$_POST['autor'];
        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT cid, c_user, c_post_id, c_status, user_id FROM @posts_comentarios LEFT JOIN @miembros ON user_id = $autor WHERE cid = $comid"));
        // RESTAR O SUMAR EN LAS ESTADÍSTICAS
        $type = $data['c_status'] == 1;
        $this->manageStats((int)$data['c_post_id'], (int)$data['c_user'], $type);
        // OCULTAMOS O MOSTRAMOS
        $status = $type ? 0 : 1;
        if (!db_exec([__FILE__, __LINE__], 'query', "UPDATE @posts_comentarios SET c_status = $status WHERE cid = $comid")) {
            return 'Ocurri&oacute; un error';
        }
        return $type ? '2: El comentario fue habilitado.' : '1: El comentario fue ocultado.';
    }
    /*
        votarComentario()
    */
    public function reaccionarComentario()
    {
        global $tsCore, $tsUser, $tsMonitor, $tsActividad;
        //
        $cid = (int)$this->Core->setSecure($_POST['cid']);
        $post_id = (int)$this->Core->setSecure($_POST['postid']);
        $reaccion = $this->Core->setSecure($_POST['reaccion']);
        $reacciones = ['like',  'love',  'haha',  'wow',  'sad',  'angry'];
        $indice = array_search($reaccion, $reacciones);
        //
        if (!in_array($reaccion, $reacciones)) {
            return '0: No tienes permiso para hacer eso.';
        }
        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT cid, c_user FROM @posts_comentarios WHERE cid = $cid LIMIT 1"));
        // ES MI COMENTARIO?
        $is_mypost = ($data['c_user'] !== $this->User->uid);
        if ($is_mypost) {
            // YA LO VOTE?
            $votado = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT rid FROM @comentarios_reaccion WHERE r_comment_id = $cid AND r_user_id = {$this->User->uid} LIMIT 1"));
            if ($votado === 1) {
                db_exec([__FILE__, __LINE__], 'query', "UPDATE @comentarios_reaccion SET r_reaction = '$reaccion' WHERE rid = r_comment_id = $cid AND r_user_id = {$this->User->uid}");
                return '0: Reacción cambiada.';
            }
            // INSERTAR EN TABLA
            $time = time();
            if (db_exec([__FILE__, __LINE__], 'query', "INSERT INTO @comentarios_reaccion (r_comment_id, r_user_id, r_reaction, r_date) VALUES ($cid, {$this->User->uid}, '$reaccion', $time)")) {
                // AGREGAR AL MONITOR
                $tsMonitor->setNotificacion(8, $data['c_user'], $this->User->uid, $post_id, $cid, $indice);
                // ACTIVIDAD
                $this->Actividad->setActividad(6, $post_id, $indice);
            }
            return '1: Gracias por reaccionar.';
        } else {
            return '0: No puedes votar tu propio comentario';
        }
    }

    public function votarComentario()
    {
        global $tsCore, $tsUser, $tsMonitor, $tsActividad;

        // VOTAR
        $cid = $this->Core->setSecure($_POST['cid']);
        $post_id = $this->Core->setSecure($_POST['postid']);

        //COMPROBAMOS PERMISOS
        if (in_array($votoVal, $reacciones) && ($this->User->is_admod || $this->User->permisos['govpp'])) {
        //
            $query = db_exec([__FILE__, __LINE__], 'query', 'SELECT c_user FROM @posts_comentarios WHERE cid = \'' . (int)$cid . '\' LIMIT 1');
            $data = db_exec('fetch_assoc', $query);

        // ES MI COMENTARIO?
            $is_mypost = ($data['c_user'] == $this->User->uid) ? true : false;
        // NO ES MI COMENTARIO, PUEDO VOTAR
            if (!$is_mypost) {
                // YA LO VOTE?
                $query = db_exec([__FILE__, __LINE__], 'query', 'SELECT tid FROM @posts_votos WHERE tid = \'' . (int)$cid . '\' AND tuser = \'' . $this->User->uid . '\' AND type = \'2\' LIMIT 1');
                $votado = db_exec('num_rows', $query);

                if (empty($votado)) {
                    // SUMAR VOTO
                    db_exec([__FILE__, __LINE__], 'query', 'UPDATE @posts_comentarios SET c_votos = c_votos ' . $voto . ' WHERE cid = \'' . (int)$cid . '\'');
                    // INSERTAR EN TABLA
                    if (db_exec([__FILE__, __LINE__], 'query', 'INSERT INTO @posts_votos (tid, tuser, type) VALUES (\'' . (int)$cid . '\', \'' . $this->User->uid . '\', \'2\' ) ')) {
                        // SUMAR PUNTOS??
                        if ($votoVal == 1 && $this->Core->settings['c_allow_sump'] == 1) {
                             db_exec([__FILE__, __LINE__], 'query', 'UPDATE @miembros SET user_puntos = user_puntos +1 WHERE user_id = \'' . $data['c_user'] . '\'');
                                $this->subirRango($data['c_user']);
                        }
                        // AGREGAR AL MONITOR
                        $tsMonitor->setNotificacion(8, $data['c_user'], $this->User->uid, $post_id, $cid, $votoVal);
                          // ACTIVIDAD
                          $this->Actividad->setActividad(6, $post_id, $votoVal);
                    }
                    //
                    return '1: Gracias por tu voto';
                } return '0: Ya has votado este comentario';
            } else {
                return '0: No puedes votar tu propio comentario';
            }
        } else {
            return '0: No tienes permiso para hacer eso.';
        }
    }
}
