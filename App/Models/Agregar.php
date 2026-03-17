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

use App\Models\{Actividad,Core,Moderacion,Monitor,Sitemap,User};
use App\Traits\{Extras,System};
use App\Utils\Images;

class Agregar
{
    use Extras;
    use System;

    protected Actividad $Actividad;
    protected Core $Core;
    protected Images $Images;
    protected Monitor $Monitor;
    protected Sitemap $Sitemap;
    protected User $User;

    public function __construct(
        Actividad $Actividad,
        Core $Core,
        Images $Images,
        Monitor $Monitor,
        Sitemap $Sitemap,
        User $User
    ) {
        $this->Actividad = $Actividad;
        $this->Core = $Core;
        $this->Images = $Images;
        $this->Monitor = $Monitor;
        $this->Sitemap = $Sitemap;
        $this->User = $User;
    }

    /**
     * isAdmodPost('u.', 'p.', 'AND cm.status = 0'$fix, $add)
     * @access public
     * @param string
     * @param string
     * @return string
    */
    private function isAdmodPost(string $prefix = 'u.', string $prefixSecondary = 'p.', string $append = '')
    {
        return $this->isAdmodSeeMod() ? "{$prefixSecondary}post_id > 0" : $this->isAdmod($prefix, " AND {$prefixSecondary}post_status = 0 $append");
    }

    /**
     * genTags($q)
     * @access public
     * @param string
     * @return string
    */
    public function genTags(string $q = '')
    {
        $texto = preg_replace('/ {2,}/si', " ", trim(preg_replace("/[^ A-Za-z0-9]/", "", $q)));
        $array = []; # Para iniciar el arreglo
        foreach (explode(' ', $texto) as $tag) { # Solo agregamos de más de 4 y menos de 12 letras
            # Añadimos cada palabra al array
            if (strlen($tag) >= 4 and strlen($tag) <= 12) {
                array_push($array, trim(strtolower($tag)));
            }
        }
        return join(', ', $array);
    }

    /**
     * simiPosts($q, $like)
     * @access public
     * @param string
     * @param bool
     * @return array
     */
    public function simiPosts(string $q = '', bool $like = true)
    {
        // Es administrador o moderador?...
        $isAdmod = $this->isAdmod();
        // Modo de busqueda
        $typeSearch = $like ? "p.post_title LIKE '%$q%'" : "MATCH(p.post_title) AGAINST('$q' IN BOOLEAN MODE)";
        // Buscamos posts con el título similar...
        $data = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT p.post_id, p.post_title, c.c_seo FROM @posts AS p LEFT JOIN @miembros AS u ON u.user_id = p.post_user LEFT JOIN @posts_categorias AS c ON c.cid = p.post_category WHERE p.post_status = 0 $isAdmod && $typeSearch ORDER BY RAND() DESC LIMIT 5"));
        //
        return $data;
    }

    /**
     * getPreview()
     * @access public
     * @return array
     * En este caso solo se dejará el cuerpo
    */
    public function getPreview()
    {
        return ['cuerpo' => $this->applyFiltersToInput($_POST['cuerpo'])];
    }

    /**
     * validTags()
     * @access public
     * @param string
     * @return bool
    */
    public function validTags(string $tags = '')
    {
        $tags = preg_replace('/[^A-Za-z0-9, ]/', '', trim($tags));
        if (empty($tags)) {
            return false;
        }
        $tagsArray = array_filter(explode(',', $tags), 'trim');
        if (safe_count($tagsArray) < 4) {
            return false;
        }
        foreach ($tagsArray as $tag) {
            if (empty($tag)) {
                return false;
            }
        }
        return true;
    }

    /**
     * newEditPost($data, $type)
     * @access private
     * @param array
     * @param string = new
     * @return array
    */
    private function newEditPost(string $type = 'new')
    {
        $data = [
            'title' => $this->sanitizeContent($_POST['titulo']),
            'body' => $this->Core->setSecure($_POST['cuerpo']),
            'tags' => $this->sanitizeContent($_POST['tags'], true),
            'category' => (int)$_POST['categoria']
        ];
        if ($type === 'new') {
            $data['date'] = time();
        }

        return $data;
    }

    private function iCanEmpty(&$postData)
    {
        // VACIOS
        foreach ($postData as $key => $val) {
            $val = trim(preg_replace('/[^ A-Za-z0-9]/', '', $val));
            $val = str_replace(' ', '', $val);
            if (empty($val)) {
                return 0;
            }
        }
        // TAGS
        $tags = $this->validTags($postData['tags']);
        if (empty($tags)) {
            return 'Tienes que ingresar por lo menos <strong>4</strong> tags.';
        }
        // ESTOS PUEDEN IR VACIOS
        $keys = ['visitantes', 'smileys', 'private', 'block_comments', 'sponsored', 'sticky'];
        foreach ($keys as $key) {
            $postData[$key] = isset($_POST[$key]) ? ($_POST[$key] === 'on' ? 1 : 0) : 0;
            if ($key === 'sponsored' || $key === 'sticky') {
                $postData[$key] = (!$this->User->is_admod && $this->User->permisos['most'] != false) ? 0 : (isset($_POST[$key]) && $_POST[$key] === 'on' ? 1 : 0);
            }
        }
        return $postData;
    }

    private function getFuentes()
    {
        if (!empty($_POST['fuentes']) and (int)$this->Core->settings['c_allow_fuentes'] === 1) {
            $fuentes = $_POST['fuentes'];
            // Expresión regular para encontrar coincidencias de [texto](URL)
            preg_match_all('/\[(.*?)\]\((.*?)\)/', $fuentes, $matches);
            // Combinar los resultados en un array
            $result = array_combine($matches[1], $matches[2]);
            $result = json_encode($result);
            return $result;
        }
        return '';
    }

    public function getCategorias()
    {
        // CONSULTA
        $categoria_privada = ($this->User->is_admod) ? "" : "WHERE c_nombre != '{$this->Core->settings['titulo']}'";
        $categorias = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT cid, c_orden, c_nombre, c_seo, c_color, c_descripcion, c_img FROM @posts_categorias $categoria_privada ORDER BY c_orden"));
        foreach ($categorias as $cid => $cat) {
            $categorias[$cid]['c_img'] = $this->Core->route('assets:categorias') . "/{$cat['c_img']}";
        }
      //
        return $categorias;
    }

    /**
     * newPost()
     * @access public
     * @return ID
    */
    public function newPost()
    {
        //
        if (!($this->User->is_admod || $this->User->permisos['gopp'])) {
            return 'No tienes permiso para crear posts.';
        }
        # ID USUARIO
        $user_id = $this->User->uid;
        // Evitando que se repita en nuevo post y editar post
        $postData = $this->newEditPost();
        $exists = (int)db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(post_id) AS few FROM @posts WHERE post_body = '{$postData['body']}' LIMIT 1"))[0];
        if ($exists === 1) {
            die('No se puede agregar el post, porque el contenido ya existe.');
        }
        // Pueden ir vacios
        $this->iCanEmpty($postData);
        // ANTIFLOOD
        $antiflood = 2;
        $postData['fuentes'] = $this->getFuentes();
        if ((int)$this->User->info['user_lastpost'] < (time() - $antiflood)) {
            // EXISTE LA CATEGORIA?
            $query = db_exec([__FILE__, __LINE__], 'query', "SELECT cid FROM @posts_categorias WHERE cid = {$postData['category']} LIMIT 1");
            if (db_exec('num_rows', $query) === 0) {
                return 'La categor&iacute;a especificada no existe.';
            }
            // Agregamos este item al array
            $postData['ip'] = $this->Core->executeIP($postData['ip']);
            // Agregamos estos items al array
            $postData['user'] = $user_id;
            $postData['status'] = (!$this->User->is_admod and ((int)$this->Core->settings['c_desapprove_post'] === 1)) ? 3 : 0;
            // Generamos la imagen para la portada ya sea desde archivo o url
            $postData['portada'] = $this->Images->getImageOfInput();
            // INSERTAMOS
            if (addDataToTable([__FILE__, __LINE__], '@posts', $postData, 'post_')) {
                $pid = (int)db_exec('insert_id');
                $time = time();
                // Si está oculto, lo creamos en el historial e.e
                if (!$this->User->is_admod && ($this->isData['postDesapprove'] || $this->User->permisos['gorpap'] == true)) {
                    addDataToTable([__FILE__, __LINE__], '@historial', [
                        `pofid` => $pid,
                        `action` => 3,
                        `type` => 1,
                        `mod` => $user_id,
                        `reason` => 'Revisi&oacute;n al publicar',
                        `date` => $time,
                        `mod_ip` => $postData['ip']
                    ]);
                }
                // Actualizar
                $updates = [
                    // ESTADÍSTICAS (se me hace innecesario, pero lo dejo)
                    "UPDATE @stats SET `stats_posts` = stats_posts + 1 WHERE `stats_no` = 1",
                    // ULTIMO POST
                    "UPDATE @miembros SET `user_lastpost` = $time WHERE `user_id` = {$user_id}"
                ];
                if (!empty($postData['portada'])) {
                    // Creamos la imagen fisica en el servidor
                    $this->Images->createImage((int)$pid, $postData['portada']);
                    // y actualizamos los datos del posts con la id de la portada
                    $portada = $this->Images->setEncodeNameFolder((int)$pid);
                    // ACTUALIZAMOS DATO DE PORTADA
                    $updates[] = "UPDATE @posts SET `post_portada` = '$portada' WHERE `post_id` = $pid";
                }
                foreach ($updates as $sql) {
                    db_exec([__FILE__, __LINE__], 'query', $sql);
                }
                // Añadimos al sitemap
                $this->Sitemap->addSitemapInfo('add', (int)$pid);
                // AGREGAR AL MONITOR DE LOS USUARIOS QUE ME SIGUEN
                $this->Monitor->setFollowNotificacion(5, 1, (int)$user_id, (int)$pid);
                // REGISTRAR MI ACTIVIDAD
                $tsActividad->setActividad(1, (int)$pid);
                // SUBIR DE RANGO?
                $this->subirRango((int)$user_id);
                //
                return $pid;
            } else {
                return ShowError('Error al ejecutar la consulta de la l&iacute;nea ' . __LINE__ . ' de ' . __FILE__ . '.', 'db');
            }
        } else {
            return -1;
        }
    }

    /*
        getEditPost()
    */
    public function getEditPost()
    {
        $pid = (int)$_GET['pid'];
        // Para no traer todo y solo usamos las que requerimos
        $verify = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT post_id, post_category, post_title, post_body, post_user, post_portada, post_private, post_smileys, post_sponsored, post_status, post_sticky, post_tags, post_date, post_update, post_block_comments, post_visitantes, post_ip, post_fuentes FROM @posts WHERE post_id = $pid LIMIT 1"));

        $verify['post_fuentes'] = '';
        if (!empty($verify['post_fuentes'])) {
            $fuentes = json_decode($verify['post_fuentes'], true);
            $postFuentes = [];
            foreach ($fuentes as $name => $fuente) {
                $postFuentes[] = "[$name]($fuente)";
            }
            $verify['post_fuentes'] = join('; ', $postFuentes);
        }
        //
        $withPermissons = ($this->User->is_admod == 0 and $this->User->permisos['moedpo'] == false);
        //
        if (empty($verify['post_id'])) {
            return 'El post elegido no existe.';
        } elseif ((int)$verify['post_status'] != '0' && $withPermissons) {
            return 'El post no puede ser editado.';
        } elseif (($this->User->uid != (int)$verify['post_user']) && $withPermissons) {
            return 'No puedes editar un post que no es tuyo.';
        }
        // PEQUEÑO HACK
        foreach ($verify as $keyname => $content) {
            $isKey = str_replace('post_', 'b_', $keyname);
            $isReplace = ($keyname === 'post_body') ? str_replace(['\n', '\r', '\\'], ["\n", '', ''], $content) : $content;
            $data[$isKey] = $isReplace;
            if ($keyname === 'post_body' or $keyname === 'post_title') {
                $data[$isKey] = stripcslashes($isReplace);
            }
        }
        return $data;
    }

    /**
     * savePost()
     * @access public
     * @return ID
    */
    public function savePost()
    {
        // Buscamos el post por ID tsUser
        $post_id = (int)$_GET['pid'];
        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT post_user, post_sponsored, post_sticky, post_status FROM @posts WHERE post_id = $post_id LIMIT 1"));
        //
        if ((int)$data['post_status'] !== 0 && !$this->User->is_admod && !$this->User->permisos['moedpo']) {
            return 'El post no puede ser editado.';
        }
        //
        $postData = $this->newEditPost('edit');
        // Pueden ir vacios
        $this->iCanEmpty($postData);
        if (!empty($_POST['portada']) or !empty($_FILES['portada'])) {
            $postData["portada"] = $this->Images->updateImagePost();
        }
        $postData["update"] = time();

        // ACTUALIZAMOS
        if ((int)$this->User->uid === (int)$data['post_user'] || !empty($this->User->is_admod) || !empty($this->User->permisos['moedpo'])) {
            if (db_exec([__FILE__, __LINE__], 'query', "UPDATE @posts SET {$this->buildSqlUpdateFields($postData, 'post_')} WHERE post_id = $post_id")) {
                // Añadimos al sitemap (No le veo el sentido a este)
                $this->Sitemap->addSitemapInfo('update', $post_id);
                // Guardamos en el historial de moderación
                $razon = $_POST['razon'] ?? '';
                // Vaciar cache/sql
                $this->cleanerCacheSQL();
                if (($this->User->is_admod || $this->User->permisos['moedpo']) && $this->User->uid != $data['post_user'] && $razon) {
                    $tsMod = new Moderacion();
                    return $tsMod->setHistory('editar', 'post', [
                        'post_id' => $post_id,
                        'title' => $postData['title'],
                        'autor' => $data['post_user'],
                        'razon' => $this->Core->setSecure($razon)
                    ]);
                }
                return 1;
            } else {
                exit(ShowError('Error al ejecutar la consulta de la l&iacute;nea ' . __LINE__ . ' de ' . __FILE__ . '.', 'db'));
            }
        }
    }

    /*
        subirRango()
    */
    private function subirRango($user_id, $post_id = false)
    {
        // CONSULTA
          $query = db_exec([__FILE__, __LINE__], 'query', 'SELECT u.user_puntos, u.user_rango, r.r_type FROM @miembros AS u LEFT JOIN @rangos AS r ON u.user_rango = r.rango_id WHERE u.user_id = \'' . $user_id . '\' LIMIT 1');
        $data = db_exec('fetch_assoc', $query);

        // SI TIEN RANGO ESPECIAL NO ACTUALIZAMOS....
        if (empty($data['r_type']) && $data['user_rango'] != 3) {
            return true;
        }
          // SI SOLO SE PUEDE SUBIR POR UN POST
        if (!empty($post_id) && $this->Core->settings['c_newr_type'] == 0) {
            $query = db_exec([__FILE__, __LINE__], 'query', 'SELECT post_puntos FROM @posts WHERE post_id = \'' . (int)$post_id . '\' LIMIT 1');
              $puntos = db_exec('fetch_assoc', $query);

              // MODIFICAMOS
              $data['user_puntos'] = $puntos['post_puntos'];
        }
          //
        $puntos_actual = $data['user_puntos'];
          $posts = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(post_id) AS p FROM @posts WHERE post_user = \'' . (int)$user_id . '\' && post_status = \'0\''));
        $fotos = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(foto_id) AS f FROM @fotos WHERE f_user = \'' . (int)$user_id . '\' && f_status = \'0\''));
          $comentarios = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT COUNT(cid) AS c FROM @posts_comentarios WHERE c_user = \'' . (int)$user_id . '\' && c_status = \'0\''));

        // RANGOS
        $query = db_exec([__FILE__, __LINE__], 'query', 'SELECT rango_id, r_cant, r_type FROM @rangos WHERE r_type > \'0\' ORDER BY r_cant');

        //
        while ($rango = db_exec('fetch_assoc', $query)) {
            // SUBIR USUARIO
            if (!empty($rango['r_cant']) && $rango['r_type'] == 1 && $rango['r_cant'] <= $puntos_actual) {
                $newRango = $rango['rango_id'];
            } elseif (!empty($rango['r_cant']) && $rango['r_type'] == 2 && $rango['r_cant'] <= $posts[0]) {
                $newRango = $rango['rango_id'];
            } elseif (!empty($rango['r_cant']) && $rango['r_type'] == 3 && $rango['r_cant'] <= $fotos[0]) {
                $newRango = $rango['rango_id'];
            } elseif (!empty($rango['r_cant']) && $rango['r_type'] == 4 && $rango['r_cant'] <= $comentarios[0]) {
                $newRango = $rango['rango_id'];
            }
        }
        //HAY NUEVO RANGO?
        if (!empty($newRango) && $newRango != $data['user_rango']) {
            //
            if (db_exec([__FILE__, __LINE__], 'query', 'UPDATE @miembros SET user_rango = \'' . $newRango . '\' WHERE user_id = \'' . $user_id . '\' LIMIT 1')) {
                return true;
            }
        }
    }
}
