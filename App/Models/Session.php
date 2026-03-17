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

use App\Traits\{IP,System,Url};

class Session
{
    use IP;
    use System;
    use Url;

    public $ID = '';

    public int $sess_expiration = 7200;

    public bool $sess_match_ip = false;

    public int $sess_time_online = 300;

    public string $cookie_prefix = 'zcode_';

    public string $cookie_name = '';

    public string $cookie_path = '/';

    public string $cookie_domain = '';

    public $userdata;

    public $ip_address;

    public $time_now;

    public function __construct()
    {
        $query = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT url, c_allow_sess_ip, c_last_active FROM @configuracion WHERE tscript_id = 1"));
        // Tiempo
        $this->time_now = time();
        // Obtener el dominio o subdominio para la cookie
        $host = parse_url($this->getSSLProtocol(true) . $query['url']);
        $host = str_replace('www.', '', strtolower($host['host']));
        // Establecer variables
        $this->cookie_domain = ($host == 'localhost') ? '' : '.' . $host;
        $this->cookie_name = $this->cookie_prefix . substr(md5($host), 0, 6);
        // IP
        $this->ip_address = $this->executeIP();
        // Cada que un usuario cambie de IP, requerir nueva session?
        $this->sess_match_ip = ((int)$query['c_allow_sess_ip'] === 1);
        // Cada cuanto actualizar la sesión? && Expires
        $lastActive = (int)$query['c_last_active'];
        $this->sess_time_online = empty($lastActive) ? $this->sess_time_online : ($lastActive * 60);
    }

    /**
     * Leer session activa
     *
     * @access  public
     * @return  bool
    */
    public function read()
    {
        $this->ID = $_COOKIE[$this->cookie_name . '_sid'] ?? null;
        // Es un ID válido?
        if (!$this->ID || strlen($this->ID) != 32) {
            return false;
        }
        // ** Obtener session desde la base de datos
        $session = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT session_id, session_user_id, session_ip, session_token, session_time, session_autologin FROM @sessions WHERE session_id = '{$this->ID}'"));
        // Existe en la DB?
        if (!isset($session['session_id'])) {
            $this->destroy();
            $this->userdata = [];
            return false;
        }
        // Is the session current?
        if (($session['session_time'] + $this->sess_expiration) < $this->time_now and empty($session['session_autologin'])) {
            $this->destroy();
            return false;
        }
        // Si cambió de IP creamos una nueva session
        if ($this->sess_match_ip == true && $session['session_ip'] != $this->ip_address) {
            $this->destroy();
            return false;
        }
        // Listo guardamos y retornamos
        $this->userdata = $session;
        unset($session);
        return true;
    }

    /**
     * Create a new session
     *
     * @access  public
     * @return  void
    */
    public function create()
    {
        // Generar ID de sesión
        $this->ID = $this->genSessionID();
        // Guardar en la base de datos, session_user_id siemrpe será 0 aquí | si inicia sesión se "actualiza"
        db_exec([__FILE__, __LINE__], 'query', "INSERT INTO @sessions (session_id, session_user_id, session_ip, session_time) VALUES ('{$this->ID}', 0, '{$this->ip_address}', {$this->time_now})");
        // Establecemos la cookie
        $this->setCookie('sid', $this->ID, $this->sess_expiration);
    }

    /**
     * Update an existing session
     *
     * @access  public
     * @return  void
     */
    public function update(int $user_id = 0, bool $autologin = false, bool $force_update = false): bool
    {
        if (empty($this->userdata) || !is_array($this->userdata)) {
            $this->create();
            return true;
        }

        if (($this->userdata['session_time'] + $this->sess_time_online) >= $this->time_now && $force_update === false) {
            return true;
        }

        // Datos para actualizar
        $this->userdata['session_user_id'] = empty($user_id) ? $this->userdata['session_user_id'] : $user_id;
        $this->userdata['session_ip'] = $this->ip_address;
        $this->userdata['session_time'] = $this->time_now;
        $this->userdata['session_token'] = bin2hex(random_bytes(32));
        // Autologin requiere una comprovación doble
        $autologin = ($autologin == false) ? 0 : 1;
        $this->userdata['session_autologin'] = empty($this->userdata['session_autologin']) ? $autologin : $this->userdata['session_autologin'];
        // Actualizar en la DB
        db_exec([__FILE__, __LINE__], 'query', "UPDATE @sessions SET session_user_id = '{$this->userdata['session_user_id']}', session_ip = '{$this->userdata['session_ip']}', session_token = '{$this->userdata['session_token']}', session_time = {$this->userdata['session_time']}, session_autologin = '{$this->userdata['session_autologin']}' WHERE session_id = '{$this->ID}'");
        // Limpiar sesiones
        $this->sessGC();
        // Actualizar cookie | Si el usuario quiere recordar su sesión, se guardará por 1 año
        $expiration = (!empty($this->userdata['session_autologin'])) ? 31500000 : $this->sess_expiration;
        //
        $this->setCookie('sid', $this->ID, $expiration);
        return true;
    }

    /**
     * Destroy the current session
     *
     * @access  public
     * @return  void
     */
    public function destroy()
    {
        // Elminar de la DB
        db_exec([__FILE__, __LINE__], 'query', "DELETE FROM @sessions WHERE session_id = '{$this->ID}'");
        // Reset a la cookie
        $this->setCookie('sid', '', -31500000);
    }

     /**
      * Crear cookie
      * @access public
      * @param string
      * @param string
      * @param int
      */
    public function setCookie(string $name = '', ?string $cookiedata = '', int $cookietime = 0)
    {
        $cookiedata = $cookiedata ?? '';

        $cookiename = rawurlencode($this->cookie_name . '_' . $name);
        $cookiedata = rawurlencode($cookiedata);

        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        setcookie($cookiename, $cookiedata, $this->time_now + $cookietime, '/', $this->cookie_domain, $isSecure, true);
    }

    /**
     * Generar un ID de sesión
     *
     * @access public
     * @param void
    */
    public function genSessionID()
    {
        $sessid = '';
        while (strlen($sessid) < 32) {
            $sessid .= mt_rand(0, mt_getrandmax());
        }
        // To make the session ID even more secure we'll combine it with the user's IP
        $sessid .= $this->ip_address;
        return md5(uniqid($sessid, true));
    }

    /**
     * Eliminar sesiones expiradas
     *
     * @access  public
     * @return  void
    */
    public function sessGC()
    {
        // Esto es para no eliminar con cada llamada a esta función
        // sólo si se cumple la siguiente sentencia se eliminan las sesiones
        if ((rand() % 100) < 30) {
            // Usuario sin actividad
            $expire = $this->time_now - $this->sess_time_online;
            db_exec([__FILE__, __LINE__], 'query', "DELETE FROM @sessions WHERE session_time < $expire AND session_autologin = 0");
        }
    }
}
