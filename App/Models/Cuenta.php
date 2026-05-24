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

use App\Models\{Core,User,Visitas};
use App\Services\TotpService;
use App\Traits\{Extras,System};
use App\Utils\Avatar;
use App\Helpers\RedesSociales;

class Cuenta
{
    use Extras;
    use System;

    protected Core $Core;
    protected User $User;
    protected Visitas $Visitas;
    protected Avatar $Avatar;

    public function __construct(Core $Core, User $User, Visitas $Visitas, Avatar $Avatar)
    {
        $this->Core = $Core;
        $this->User = $User;
        $this->Visitas = $Visitas;
        $this->Avatar = $Avatar;
    }

    private function getSocialUser(int $user_id = 0): array
    {
        // Redes viculadas
        $socials = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT social_name as name FROM @miembros_social WHERE social_user_id = $user_id"));
        $array_social = [
            'discord' => false,
            'facebook' => false,
            'github' => false,
            'google' => false
        ];
        foreach ($socials as $sn) {
            $name = $sn['name'];
            if (isset($array_social[$name])) {
                $array_social[$name] = true;
            }
        }
        return $array_social;
    }

    /**
     * @name loadPerfil()
     * @access public
     * @uses Cargamos el perfil de un usuario
     * @param int
     * @return array
    */
    public function loadPerfil(int $user_id = 0)
    {
        if (empty($user_id)) {
            $user_id = $this->User->uid;
        }
        $perfilInfo = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT p.*, u.user_registro, u.user_lastactive, u.user_outtime_type FROM @perfil AS p LEFT JOIN @miembros AS u ON p.user_id = u.user_id WHERE p.user_id = $user_id LIMIT 1"));

        // FECHA DE NACIMIENTO
        $fecha = "{$perfilInfo['user_dia']}-{$perfilInfo['user_mes']}-{$perfilInfo['user_ano']}";
        $perfilInfo['nacimiento'] = date("Y-m-d", strtotime($fecha));
        // CAMBIOS
        $perfilInfo = $this->unData($perfilInfo);
        // CUSTOMIZAR
        $custom = explode(';', $perfilInfo['user_customize']);
        $perfilInfo['custom'] = [
            'light' => $custom[0] ?? '#F4F4F4',
            'dark' => $custom[1] ?? '#212121'
        ];
        return $perfilInfo;
    }

    /*
        loadExtras()
    */
    private function unData(array $data = [])
    {
        // Configuraciones
        $data['p_configs'] = safe_unserialize($data['p_configs']);
        // Redes sociales
        $data['p_socials'] = $this->setSocialData($data, true);
        return $data;
    }

    public function setSocialData(&$data, bool $n = false): array
    {
        $redes = RedesSociales::REDES;
        if ($n) {
            $data['redes'] = RedesSociales::getRedes();
        }
        $socials = [];
        if (!empty($data['p_socials'])) {
            $decoded = json_decode($data['p_socials'], true);
            if (is_array($decoded)) {
                $socials = $decoded;
            }
        }
        // Normalizar usando la fuente de verdad
        foreach ($redes as $name => $base) {
            $socials[$name] = $socials[$name] ?? '';
        }
        $data['p_socials'] = $socials;
        return $socials;
    }

    private function loadHits(&$data, int $user_id = 0)
    {
        $data['p_configs'] = safe_unserialize($data['p_configs']);
        if (!isset($data['p_configs']['hits'])) {
            $data['p_configs']['hits'] = 0;
        }
        //
        if ((int)$data['p_configs']['hits'] === 0) {
            $data['can_hits'] = false;
        } elseif ((int)$data['p_configs']['hits'] === 3 && ($this->iyfollow($user_id, 'iFollow') || $this->User->is_admod)) {
            $data['can_hits'] = true;
        } elseif ((int)$data['p_configs']['hits'] === 4 && ($this->iyfollow($user_id, 'yFollow') || $this->User->is_admod)) {
            $data['can_hits'] = true;
        } elseif ((int)$data['p_configs']['hits'] === 5 && $this->User->is_member) {
            $data['can_hits'] = true;
        } elseif ((int)$data['p_configs']['hits'] === 6) {
            $data['can_hits'] = true;
        }
        // PUEDE RECIBIR VISITAS
        if ($data['can_hits']) {
            $data['visitas'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT v.*, u.user_id, u.user_name FROM @visitas AS v LEFT JOIN @miembros AS u ON v.user = u.user_id WHERE v.for = $user_id.AND v.type = 1 AND user > 0 ORDER BY v.date DESC LIMIT 8"));
            $q1 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(u.user_id) AS a FROM @visitas AS v LEFT JOIN @miembros AS u ON v.user = u.user_id WHERE v.for = $user_id AND v.type = 1"));
            $data['visitas_total'] = $q1[0];
            foreach ($data['visitas'] as $uid => $user) {
                $data['visitas'][$uid]['avatar'] = $this->zcode->getAvatar($user['user_id'], 'use');
            }
        }
    }

    /*
        loadHeadInfo($user_id)
    */
    public function loadHeadInfo(int $user_id = 0)
    {
        // INFORMACION GENERAL
        $new = "u.user_verificado, p.user_gif, p.user_gif_active, p.user_portada, p.user_scheme, p.user_color, p.user_customize, p.user_font_family, p.user_font_size, p.user_pagebox, p.p_socials";
        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT u.user_id, u.user_name, u.user_registro, u.user_lastactive, u.user_activo, u.user_baneado, $new, p.user_sexo, p.user_pais, p.p_nombre, p.p_avatar, p.p_mensaje, p.p_configs FROM @miembros AS u, @perfil AS p WHERE u.user_id = $user_id AND p.user_id = $user_id"));
        //
        $data['avatar'] = $this->Avatar->loadAvatar((int)$user_id);
        $data['p_nombre'] = $this->Core->parseBadWords($data['p_nombre'] ?? '');
        $data['p_mensaje'] = $this->Core->parseBBCode($this->Core->parseBadWords($data['p_mensaje'] ?? ''), 'firma');
        // Redes Sociales
        if (!empty($data['p_socials'])) {
            $this->setSocialData($data, true);
        }
        $this->loadHits($data, (int)$user_id);
        // VISITAS
        $this->Visitas->recordarVisita((int)$user_id, 1, (int)$this->User->uid);
        $data['stats'] = $this->loadHeadInfoEstadisticas((int)$user_id);
        // BLOQUEADO
        $data['block'] = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT * FROM @bloqueos WHERE b_user = {$this->User->uid} AND b_auser = $user_id LIMIT 1"));
        //
        return $data;
    }

    public function getAvatarSocials()
    {
        $uid = (int)$this->User->uid;
        $data = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT social_name, social_avatar, user_avatar_type, user_avatar_social FROM @miembros_social LEFT JOIN @perfil ON social_user_id = user_id WHERE social_user_id = $uid"));
        foreach ($data as $key => $user) {
            if ($user['user_avatar_social'] !== 'web' and $user['user_avatar_type'] > 0) {
                $social = $user['social_name'];
                $folder = "user$uid";
                $this->Avatar->createAvatarSocial((int)$uid, $social, $user['social_avatar']);
                $data[$key]['social_avatar'] = $this->Core->route('storage:avatar') . "/$folder/$social.webp";
            }
        }

        return $data;
    }

    public function activeAvatarSocial()
    {
        if ($this->User->is_member) {
            $name = $this->Core->setSecure($_POST['name']);
            $active = (int)$_POST['active'] ?? 0;
            if (db_exec([__FILE__, __LINE__], 'query', "UPDATE @perfil SET user_avatar_type = $active, user_avatar_social = '$name' WHERE user_id = {$this->User->uid}")) {
                return '1: Activado correctamente.';
            }
            return '0: Hubo un error al activar';
        }
    }

    public function saveColorCustomizer()
    {
        if ($this->User->is_member) {
            $light = empty($_POST['light']) ? '#212121' : $this->Core->setSecure($_POST['light']);
            $dark = empty($_POST['dark']) ? '#F4F4F4' : $this->Core->setSecure($_POST['dark']);
            return (db_exec([__FILE__, __LINE__], 'query', "UPDATE @perfil SET user_customize = '$light;$dark' WHERE user_id = {$this->User->uid}"));
        }
        return false;
    }

    private function loadHeadInfoEstadisticas($user_id)
    {
        // REAL STATS
        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT u.user_id, u.user_rango, u.user_puntos, u.user_posts, u.user_comentarios, u.user_seguidores, u.user_cache, r.r_name, r.r_color FROM @miembros AS u LEFT JOIN @rangos AS r ON u.user_rango = r.rango_id WHERE u.user_id = $user_id"));
        //
        #if((int)$data['user_cache'] > time() - ((int)$this->Core->settings['c_stats_cache'] * 60)) {
            // POSTS
            $q1 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(post_id) FROM @posts WHERE post_user = $user_id AND post_status = 0"));
            $data['user_posts'] = $q1[0];
            // SEGUIDORES
            $q2 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(follow_id) FROM @follows WHERE f_id =$user_id AND f_type = 1"));
            $data['user_seguidores'] = $q2[0];
            // COMENTARIOS
            $q3 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(cid) FROM @posts_comentarios WHERE c_user = $user_id AND c_status = 0"));
            $data['user_comentarios'] = $q3[0];
            // SEGUIDORES
            $q4 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(follow_id) FROM @follows WHERE f_user = $user_id AND f_type = 1"));
            $data['user_seguidos'] = $q4[0];
            // Amigos
            $q5 = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(f1.follow_id) FROM @follows AS f1 JOIN @follows AS f2 ON f1.f_id = f2.f_user AND f1.f_user = f2.f_id WHERE f1.f_user = $user_id AND f1.f_type = 1 AND f2.f_type = 1"));
            $data['user_amigos'] = $q5[0];
            // ACTUALIZAMOS
            $user = $this->buildSqlUpdateFields([
                'posts' => (int)$data['user_posts'],
                'comentarios' => (int)$data['user_comentarios'],
                'seguidores' => (int)$data['user_seguidores'],
                'seguidos' => (int)$data['user_seguidos'],
                'amigos' => (int)$data['user_amigos'],
                'cache' => time()
            ], 'user_');
            db_exec([__FILE__, __LINE__], 'query', "UPDATE @miembros SET $user WHERE user_id = $user_id");
        #}
        $data['user_fotos'] = db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', "SELECT COUNT(foto_id) AS f FROM @fotos WHERE f_user = $user_id AND f_status = 0"))[0];
        return $data;
    }

    private function loadMedallasTotal(&$data, int $mfor = 0, int $total = 0)
    {
        $limit = ($total === 0) ? '' : " LIMIT $total";
        $data['medallas'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT m.*, a.* FROM @medallas AS m LEFT JOIN @medallas_assign AS a ON a.medal_id = m.medal_id WHERE a.medal_for = $mfor AND m.m_type = 1 ORDER BY a.medal_date DESC$limit"));
        $data['total'] = safe_count($data['medallas']);
        foreach ($data['medallas'] as $mid => $medalla) {
            $data['medallas'][$mid]['m_image'] = $this->Core->route('assets:images') . "/medallas/{$medalla['m_image']}";
        }
        return $data;
    }

    /*
        loadGeneral($user_id)
    */
    public function loadGeneral(int $user_id = 0)
    {
        // SEGUIDORES
        $data['seguidores']['data'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT f.follow_id, u.user_id, u.user_name FROM @follows AS f LEFT JOIN @miembros AS u ON f.f_user = u.user_id WHERE f.f_id = $user_id && f.f_type = 1 && u.user_activo = 1 && u.user_baneado = 0 ORDER BY f.f_date DESC LIMIT 21"));
        $data['seguidores']['total'] = safe_count($data['seguidores']['data']);
        foreach ($data['seguidores']['data'] as $uid => $user) {
            $data['seguidores']['data'][$uid]['avatar'] = $this->zcode->getAvatar($user['user_id'], 'use');
        }
        // SIGUIENDO
        $data['siguiendo']['data'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT f.follow_id, u.user_id, u.user_name FROM @follows AS f LEFT JOIN @miembros AS u ON f.f_id = u.user_id WHERE f.f_user = $user_id AND f.f_type = 1 && u.user_activo = 1 && u.user_baneado = 0 ORDER BY f.f_date DESC LIMIT 21"));
        $data['siguiendo']['total'] = safe_count($data['siguiendo']['data']);
        foreach ($data['siguiendo']['data'] as $uid => $user) {
            $data['siguiendo']['data'][$uid]['avatar'] = $this->zcode->getAvatar($user['user_id'], 'use');
        }
        // ULTIMAS FOTOS
        if (empty($_GET['pid'])) {
            $data['fotos'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT foto_id, f_title, f_url FROM @fotos WHERE f_user = $user_id ORDER BY RAND() DESC LIMIT 6"));
            $data['fotos_total'] = safe_count($data['fotos']);
        }
        $this->loadMedallasTotal($data, $this->User->uid, 21);
        //
        return $data;
    }
    /**
     * Private function
     * iyfollow() Casi son lo mismo
    */
    public function iyfollow(int $user_id = 0, string $type = 'iFollow')
    {
        $id = ($type === 'iFollow') ? $user_id : $this->User->uid;
        $user = ($type === 'iFollow') ? $this->User->uid : $user_id;
        // SEGUIR
        $data = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT follow_id FROM @follows WHERE f_id = $id AND f_user = $user AND f_type = 1 LIMIT 1"));
        return ($data > 0) ? true : false;
    }
    /*
        loadPosts($user_id)
    */
    public function loadPosts(int $user_id = 0)
    {
        $data['posts'] = result_array(db_exec([__FILE__, __LINE__], 'query', "SELECT p.post_id, p.post_title, p.post_puntos, c.c_seo, c.c_img, c.c_nombre FROM @posts AS p LEFT JOIN @posts_categorias AS c ON c.cid = p.post_category WHERE p.post_status = 0 AND p.post_user = $user_id ORDER BY p.post_date DESC LIMIT 18"));
        $data['total'] = safe_count($data['posts']);
        foreach ($data['posts'] as $pid => $post) {
            $data['posts'][$pid]["post_url"] = $this->createLink('post', $post['post_id']);
            $data['posts'][$pid]['c_img'] = $this->Core->imageCat($post['c_img']);
        }
        // USUARIO
        $data['username'] = $this->User->getUserName($user_id);
        //
        return $data;
    }
    /*
        loadMedallas($user_id)
    */
    public function loadMedallas(int $user_id = 0)
    {
        $data = [];
        $this->loadMedallasTotal($data, $user_id);
        return $data;
    }

    public function saveAvatarGif()
    {
        $avatar = $this->Core->setSecure($_POST['gif']);
        $active = $_POST['active'] === 'false' ? 0 : 1;
        if (db_exec([__FILE__, __LINE__], 'query', "UPDATE @perfil SET `user_gif_active` = $active, `user_gif` = '$avatar' WHERE user_id = {$this->User->uid}")) {
            return '1: Se guardo correctamente';
        }
        return '0: Hubo un error.';
    }

    public function regenerateToken(): string
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = bin2hex(random_bytes(5)); // 10 chars hex
        }
        $encoded = base64_encode(json_encode($codes));
        $uid = (int) $this->User->uid;
        if (db_exec([__FILE__, __LINE__], "query", "UPDATE @miembros SET user_recovery = '{$encoded}' WHERE user_id = {$uid}")) {
            return json_encode([
                "status" => true,
                "message" => implode(',', $codes)
            ]);
        }
        return json_encode([
            "status"   => false,
            "message"  => "No se pudo generar nuevos token"
        ]);
    }

    # Para activar el doble factor
    public function activeTwoFactor()
    {
        $secret = $_POST['secret'] ?? '';
        $code = $_POST['code'] ?? '';
        $TOTP = new TotpService();
        $result = $TOTP->createSecret($secret, $code);

        if (is_string($result)) {
            return json_encode(['status' => false, 'message' => 'El código ingresado no es válido.']);
        }

        // Guardar el secreto en la base de datos
        $uid = (int) $this->User->uid;
        if (db_exec([__FILE__, __LINE__], "query", "UPDATE @miembros SET user_secret_2fa = '$secret' WHERE user_id = $uid")) {
            $this->regenerateToken();
            return json_encode(["status" => true, "message" => "2FA activado correctamente"]);
        }

        return json_encode([
            "status" => false,
            "message" => "No se pudo guardar el secreto en la base de datos"
        ]);
    }


    # Para desactivar el doble factor
    public function removeTwoFactor()
    {
        return (db_exec([__FILE__, __LINE__], "query", "UPDATE @miembros SET user_secret_2fa = '', user_recovery = '' WHERE user_id = {$this->User->uid}")) ? '1: Ha sido desactivado correctamente.' : '0: Hubo un problema al querer desactivar.';
    }

    public function saveCuenta()
    {
        // NUEVOS DATOS
        $nac = explode('-', $_POST['nacimiento']);
        $perfilData = [
            'email' => $this->Core->setSecure($_POST['email'], true),
            'pais' => $this->Core->setSecure($_POST['pais']),
            'estado' => $this->Core->setSecure($_POST['estado'] ?? ''),
            'sexo' => $this->Core->setSecure($_POST['sexo']),
            'dia' => (int)$nac[2],
            'mes' => (int)$nac[1],
            'ano' =>  (int)$nac[0],
            'firma' => $this->Core->setSecure($this->Core->parseBadWords($_POST['firma']), true),
        ];
        $year = date("Y", time());
        // ANTIGUOS DATOS
        $info = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT user_dia, user_mes, user_ano, user_pais, user_estado, user_sexo, user_firma FROM @perfil WHERE user_id = {$this->User->uid} LIMIT 1"));
        // EMAIL
         $email_ok = $this->isEmail($perfilData['email']);
         // CORRECCIONES
        if (!$email_ok) {
            $message = 'El formato de email ingresado no es v&aacute;lido.';
            // EL ANTERIOR
            $perfilData['email'] = $this->User->info['user_email'];
        // CHEQUEAMOS FECHA DE NACIMIENTO
        } elseif (!checkdate($perfilData['mes'], $perfilData['dia'], $perfilData['ano']) || ($perfilData['ano'] > $year || $perfilData['ano'] < ($year - 100))) {
            $message = 'La fecha de nacimiento no es v&aacute;lida.';
            // LOS ANTERIORES
            $perfilData['mes'] = $info['user_mes'];
            $perfilData['dia'] = $info['user_dia'];
            $perfilData['ano'] = $info['user_ano'];
        // SEXO / GÉNERO
        } elseif (empty($perfilData['sexo'])) {
            $message = 'Especifica un g&eacute;nero sexual.';
            $perfilData['sexo'] = $info['user_sexo'];
        // PAÍS
        } elseif (empty($perfilData['pais'])) {
            $message = 'Por favor, especifica tu pa&iacute;s.';
            $perfilData['pais'] = $info['user_pais'];
        // ESTADO / PROVINCIA
        } elseif (empty($perfilData['estado'])) {
            $message = 'Por favor, especifica tu estado. ' . $perfilData['estado'];
            $perfilData['estado'] = $info['user_estado'];
        // FIRMA DEL USUARIO
        } elseif (strlen($perfilData['firma']) > 300) {
            $message = 'La firma no puede superar los 300 caracteres.';
            $perfilData['firma'] = $info['user_firma'];
         // ES EL MISMO CORREO?
        } elseif ($this->User->info['user_email'] != $perfilData['email']) {
            $exists = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT user_id FROM @miembros WHERE user_email = '{$perfilData['email']}' LIMIT 1"));
            // EXISTE?...
            if ($exists) {
                $message = 'Este email ya existe, ingresa uno distinto.';
                $perfilData['email'] = $this->User->info['user_email'];
            // NO EXISTE?
            } else {
                $message = 'Los cambios fueron aceptados y ser&aacute;n aplicados en los pr&oacute;ximos minutos. NO OBSTANTE, la nueva direcci&oacute;n de correo electr&oacute;nico especificada debe ser comprobada. ' . $this->Core->settings['titulo'] . ' envi&oacute; un mensaje de correo electr&oacute;nico con las instrucciones necesarias';
            }
        }

        // Siempre guardaremos el email
        db_exec([__FILE__, __LINE__], "query", "UPDATE @miembros SET user_email = '{$perfilData['email']}' WHERE user_id = {$this->User->uid}");
        // Eliminaremos el email del array
        array_splice($perfilData, 0, 1);
        // Guardamos los datos restantes
        $updates = $this->buildSqlUpdateFields($perfilData, 'user_');
        if (db_exec([__FILE__, __LINE__], "query", "UPDATE @perfil SET {$updates} WHERE user_id = {$this->User->uid}") or ShowError('Error al ejecutar la consulta de la l&iacute;nea ' . __LINE__ . ' de ' . __FILE__ . '.', 'Base de datos')) {
            $message = 'Los cambios fueron aplicados.';
        }
        return $message;
    }

    public function saveSeguridad()
    {
        $passwd = $this->Core->setSecure($_POST['passwd']);
        $new_passwd = $this->Core->setSecure($_POST['new_passwd']);
        $confirm_passwd = $this->Core->setSecure($_POST['confirm_passwd']);
        // Los campos estan vacios?
        if (empty($new_passwd) || empty($confirm_passwd)) {
            $message = 'Debes ingresar una contrase&ntilde;a.';
        }
        // La nueva contraseña es corta?
        if (strlen($new_passwd) < 5) {
            $message = 'Contrase&ntilde;a no v&aacute;lida.';
        }
        // Las contraseñas coinciden?
        if ($new_passwd != $confirm_passwd) {
            $message = 'Tu nueva contrase&ntilde;a debe ser igual a la confirmaci&oacute;n de la misma.';
        }
            // Verificamos que la contraseña sea correcta
        if (!$this->zcode->createPassword($this->User->nick, $passwd, $this->User->info['user_password'])) {
            $message = 'Tu contrase&ntilde;a actual no es correcta.';
        }
        // Guardamos la nueva contraseña
        $new_key = $this->zcode->createPassword($this->User->nick, $new_passwd);
        if (db_exec([__FILE__, __LINE__], 'query', "UPDATE @miembros SET user_password = '$new_key' WHERE user_id = {$this->User->uid}")) {
            $message = 'Tu contrase&ntilde;a se actualizó correctamente.';
        }
        return $message;
    }

    public function savePrivacidad()
    {
        $configs = serialize([
            'm' => (int)$_POST['muro'],
            'mf' => (((int)$_POST['muro_firm'] > 4) ? 5 : (int)$_POST['muro_firm']),
            'rmp' => (((int)$_POST['rec_mps'] > 6) ? 5 : (int)$_POST['rec_mps']),
            'hits' => (((int)$_POST['last_hits'] == 1 || (int)$_POST['last_hits'] == 2) ? 0 : (int)$_POST['last_hits'])
        ]);
        if (db_exec([__FILE__, __LINE__], "query", "UPDATE @perfil SET p_configs = '$configs' WHERE user_id = {$this->User->uid}")) {
            return 'Los cambios fueron aplicados.';
        }
    }

    public function saveNick()
    {
        $nuevo_nick = $this->Core->setSecure($_POST['new_nick']);
        // Hay un nick en la lista negra?...
        if (db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT id FROM @blacklist WHERE type = 4 && value = '$nuevo_nick' LIMIT 1"))) {
            $message = 'Nick no permitido';
        }
        // El nick esta en uso?
        if (db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT user_id FROM @miembros WHERE user_name = '$nuevo_nick' LIMIT 1"))) {
            $message = 'Nombre en uso';
        }
        // Buscamos al usuario, para verificar si ha hecho un cambio
        $data = db_exec("fetch_assoc", db_exec([__FILE__, __LINE__], "query", "SELECT id, user_id, time FROM @nicks WHERE user_id = {$this->User->uid} AND estado = 0 LIMIT 1"));
        if ($data !== null) {
            if (!empty((int)$data['id'])) {
                $message = 'Ya tiene una petici&oacute;n de cambio en curso';
            // Realizamos petición
            } elseif (time() - $data['time'] >= 31536000) {
                db_exec([__FILE__, __LINE__], 'query', "UPDATE @miembros SET user_name_changes = 3 WHERE user_id = {$data['user_id']}");
            }
        }
        // Verificamos la contraseña
        $key = $this->zcode->createPassword($this->User->nick, $_POST['password']);
        $message = 'Tu contrase&ntilde;a actual no es correcta.';
        // Verificamos el correo
        $email_ok = $this->isEmail($_POST['pemail']);
        if (!$email_ok) {
            return ['field' => 'email', 'error' => 'El formato de email ingresado no es v&aacute;lido.'];
        }
        $email = empty($_POST['pemail']) ? $this->User->info['user_email'] : $_POST['pemail'];
        // Si el nick tiene más de 4 y menos de 20 carácteres
        if (strlen($nuevo_nick) < 4 || strlen($nuevo_nick) > 20) {
            $message = 'El nick debe tener entre 4 y 20 car&aacute;cteres';
        }
        // Que no tenga espacios, ni carácteres especiales
        if (!preg_match('/^([A-Za-z0-9]+)$/', $nuevo_nick)) {
            $message = 'El nick debe ser alfanum&eacute;rico';
        }
        // Creamos la nueva contraseña
        $key = $this->zcode->createPassword($nuevo_nick, $_POST['password']);
        // Verificamos la IP
        $myIP = $this->Core->executeIP();
        $datos = [
            'user_id' => $this->User->uid,
            'user_email' => $this->Core->setSecure($email),
            'name_1' => $this->User->nick,
            'name_2' => $nuevo_nick,
            'hash' => $key,
            'time' => time(),
            'ip' => $myIP
        ];
        if (addDataToTable([__FILE__, __LINE__], '@nicks', $datos)) {
            $message = 'Proceso iniciado, recibir&aacute; la respuesta en el correo indicado cuando valoremos el cambio.';
        }
        return $message;
    }

    public function savePerfil()
    {
        // INTERNOS
        $sitio = trim($_POST['sitio']);
        if (!empty($sitio) && substr($sitio, 0, 4) !== 'http') {
            $sitio = 'http://' . $sitio;
        }
        // EXTERNAS, Redes sociales
        $redsocial = [];
        foreach (RedesSociales::REDES as $name => $baseUrl) {
            $value = $_POST['red'][$name] ?? '';
            $redsocial[$name] = $this->Core->setSecure($this->Core->parseBadWords($value), true);
        }
        $perfilData = array(
            'nombre' => $this->Core->setSecure($this->Core->parseBadWords($_POST['nombre']), true),
            'mensaje' => $this->Core->setSecure($this->Core->parseBadWords($_POST['mensaje']), true),
            'sitio' => $this->Core->setSecure($this->Core->parseBadWords($sitio), true),
            'socials' => json_encode($redsocial),
        );
        // COMPROBACIONES
        if (!empty($perfilData['sitio']) && !filter_var($perfilData['sitio'], FILTER_VALIDATE_URL)) {
            $message = 'El sitio web introducido no es correcto.';
        }

        $updates = $this->buildSqlUpdateFields($perfilData, 'p_');

        if (db_exec([__FILE__, __LINE__], "query", "UPDATE @perfil SET {$updates} WHERE user_id = {$this->User->uid}") || ShowError('Error al ejecutar la consulta de la l&iacute;nea ' . __LINE__ . ' de ' . __FILE__ . '.', 'Base de datos')) {
            $message = 'Los cambios fueron aplicados.';
        }

        return $message;
    }

    public function saveAppearence()
    {
        $avatar = $this->Core->setSecure($_POST['gif']);
        $active = (int)$_POST['active'];
        // p.user_scheme, p.user_color,
        if (db_exec([__FILE__, __LINE__], 'query', "UPDATE @perfil SET user_avatar_gif = $active, user_avatar = '$avatar' WHERE user_id = {$this->User->uid}")) {
            return '1: Se guardo correctamente';
        }
        return '0: Hubo un error.';
    }

    public function saveThemeOption(string $type = '')
    {
        $selected = $this->Core->setSecure($_POST['selected']);
        if (db_exec([__FILE__, __LINE__], 'query', "UPDATE @perfil SET $type = '$selected' WHERE `user_id` = {$this->User->uid}")) {
            return true;
        }
        return false;
    }

    /*
        savePerfil()
    */
    public function saveSettings(string $save = '')
    {
        // GUARDAR...
        return match ($save) {
            '' => $this->saveCuenta(),
            'seguridad' => $this->saveSeguridad(),
            'privacidad' => $this->savePrivacidad(),
            'nick' => $this->saveNick(),
            'perfil' => $this->savePerfil(),
            default => null
        };
    }

    public function getAvatarImages(?string $folder = null)
    {
        $uid = (int) $this->User->uid;
        # Obtenemos el sexo del usuario
        $sexo = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT user_sexo FROM @perfil WHERE user_id = $uid"))['user_sexo'];
        # Definir el directorio según el folder
        $directory = ($folder === 'avatares') ? TS_AVATARES . $sexo : TS_AVATAR_USER . $uid;
        $data = [];

        if (is_dir($directory)) {
            $files = array_diff(scandir($directory, ($folder === 'avatares') ? SCANDIR_SORT_NONE : SCANDIR_SORT_DESCENDING), ['.', '..']);
            # Ordenar por fecha de creación si no es "avatares"
            if ($folder !== 'avatares') {
                usort($files, function ($a, $b) use ($directory) {
                    return filectime($directory . '/' . $b) - filectime($directory . '/' . $a);
                });
            }

            foreach ($files as $file) {
                $image = ($folder === 'avatares') ?
                $this->Core->route('assets:images') . '/avatares/' . $sexo :
                $this->Core->route('storage:avatar') . '/user' . $uid;
                $image .= "/$file";

                $data[] = [
                    'id' => pathinfo($file, PATHINFO_FILENAME),
                    'image' => $image,
                    'avatar' => $file
                ];
            }
        }

         return $data;
    }

    private function updateAvatarUser(?string $data = '')
    {
        $uid = (int)$this->User->uid;
        db_exec([__FILE__, __LINE__], 'query', "UPDATE @perfil_avatar SET uavatar_use = '$data' WHERE uavatar_id = $uid");
    }

    public function changeAvatar()
    {
        $Avatar = new Avatar();
        $Avatar->routes = $this->Core->route();
        //
        $image = $_POST['image'];
        # ID del usuario
        $uid = (int)$this->User->uid;
        # Obtenemos el sexo del usuario
        $query = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT user_sexo, uavatar_use FROM @perfil LEFT JOIN @perfil_avatar ON uavatar_id = user_id WHERE user_id = $uid"));

        if (!is_numeric($image) && !empty($empty)) {
            $this->updateAvatarUser($image);
            return $Avatar->loadAvatarUrl((int)$uid, (string)$image);
        }

        if ($this->User->is_member and is_numeric($image)) {
            return $Avatar->newAvatar((int)$uid, (string)$query['user_sexo'], (int)$image);
        }
    }

    /**
     * Verifica si una cadena es una dirección de correo electrónico válida.
     *
     * @param string $email La dirección de correo electrónico a validar.
     * @return bool `true` si la dirección de correo electrónico es válida, `false` en caso contrario.
    */
    public function isEmail(string $email = ''): bool
    {
        $regex = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
        return preg_match($regex, $email) === 1 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function desCuenta()
    {
        if (db_exec([__FILE__, __LINE__], 'query', 'UPDATE @miembros SET user_activo = 0 WHERE user_id = ' . $this->User->uid)) {
            $this->Core->redirectTo($this->Core->settings['url'] . '/login-salir.php');
        }
        return 1;
    }

    public function bloqueosCambiar()
    {
        $auser = $this->Core->setSecure($_POST['user']);
        $bloquear = empty($_POST['bloquear']) ? 0 : 1;
        // EXISTE?
        $exists = $this->User->getUserName($auser);
        // SI EXISTE Y NO SOY YO
        if (!$exists && $this->User->uid === $auser) {
            return '0: El usuario seleccionado no existe.';
        }
        if ($bloquear === 1) {
            // YA BLOQUEADO?
            $noexists = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT bid FROM @bloqueos WHERE b_user = {$this->User->uid} AND b_auser = $auser LIMIT 1"));
            // NO HA SIDO BLOQUEADO
            if (!empty($noexists)) {
                return '0: Ya has bloqueado a este usuario.';
            }
            if (db_exec([__FILE__, __LINE__], 'query', "INSERT INTO @bloqueos (b_user, b_auser) VALUES ({$this->User->uid}, $auser)")) {
                return "1: El usuario fue bloqueado satisfactoriamente.";
            }
        } else {
            if (db_exec([__FILE__, __LINE__], 'query', "DELETE FROM @bloqueos WHERE b_user = {$this->User->uid}  AND b_auser = $auser")) {
                return "1: El usuario fue desbloqueado satisfactoriamente.";
            }
        }
    }
    /*
        loadBloqueos()
    */
    public function loadBloqueos()
    {
        $data = result_array(db_exec([__FILE__, __LINE__], 'query', 'SELECT b.*, u.user_name FROM @miembros AS u LEFT JOIN @bloqueos AS b ON u.user_id = b.b_auser WHERE b.b_user = ' . (int)$this->User->uid));
        //
        return $data;
    }
}
