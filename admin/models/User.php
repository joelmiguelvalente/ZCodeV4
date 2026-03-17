<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

namespace Admin\models;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Contexts\UserContext;
use Admin\models\Core;
use App\Models\Email;
use App\Utils\IP;
use App\Services\AntiFloodService;

class User
{
    // SI EL USUARIO ES MIEMBRO CARGAMOS DATOS DE LA TABLA
    public $info = [];

    // EL USUARIO ESTA LOGUEADO?
    public $is_member = 0;

    // ES USUARIO ES ADMINISTRADOR
    public $is_admod = 0;

    // EL USUARIO ESTA BANEADO
    public $is_banned = 0;

    // NOMBRE A MOSTRAR
    public $nick = 'Anonymous';

    // USER ID
    public $uid = 0;

    // SI OCURRE UN ERROR ESTA VARIABLE CONTENDRA EL NUMERO DE ERROR
    public $is_error;

    public $session;

    public $permisos;

    public $email;

    public $avatar = [];

    public $use_avatar;

    public $avatar_folder;

    // Usado por el login
    public $is_type;

    protected Core $Core;

    protected $Autenticar;
    protected $Avatar;
    public AntiFloodService $antiFlood;

    public function __construct(
        Core $Core,
        UserContext $UserContext
    ) {
        /* CARGAR SESSION */
        $this->Autenticar = $UserContext->Autenticar;
        $this->Avatar = $UserContext->Avatar;
        $this->Core = $Core;
        if ($this->Autenticar->setSession()) {
            $this->loadUser();
        }
    }

    private function loadUserRango()
    {
        // PERMISOS SEGUN RANGO
        $this->info['rango'] = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT r_name, r_color, r_image, r_allows FROM @rangos WHERE rango_id = {$this->info['user_id']} LIMIT 1"));
        // PERMISOS SEGUN RANGO
        $datis = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT r_allows FROM @rangos WHERE rango_id = {$this->info['user_rango']} LIMIT 1"));
        $this->permisos = unserialize($datis['r_allows']);
        if (!isset($this->permisos['moat'])) {
            $this->permisos['moat'] = false;
        }
        if (!isset($this->permisos['sumo'])) {
            $this->permisos['sumo'] = false;
        }
        if (!isset($this->permisos['suad'])) {
            $this->permisos['suad'] = false;
        }
        /* ES MIEMBRO */
        $this->is_member = 1;

        $this->antiFlood = new AntiFloodService((int)$this->permisos['goaf']);

        $this->is_admod = match (true) {
            !$this->permisos['sumo'] && $this->permisos['suad'] => 1,
            $this->permisos['sumo'] && !$this->permisos['suad'] => 2,
            $this->permisos['sumo'] || $this->permisos['suad']  => true,
            default => 0,
        };
    }

    /*
        CARGAR USUARIO POR SU ID
        loadUser()
    */
    public function loadUser($login = null)
    {
        $time = time();
        // Cargar datos
        $SessionID = $this->Autenticar->getSessionID();
        if (!$SessionID) {
            return false;
        }
        $sql = "SELECT u.*, s.* FROM @sessions s, @miembros u WHERE s.session_id = '$SessionID' AND u.user_id = s.session_user_id";
        $query = db_exec([__FILE__, __LINE__], 'query', $sql);
        $this->info = db_exec('fetch_assoc', $query);

        // Existe el usuario?
        if (!isset($this->info['user_id'])) {
            return false;
        }
        // PERMISOS SEGUN RANGO
        $this->loadUserRango();

        // NOMBRE
        $this->nick = $this->info['user_name'];
        $this->uid = (int)$this->info['user_id'];
        $this->email = $this->info['user_email'];
        $this->is_banned = (int)$this->info['user_baneado'];
        $this->use_avatar = $this->Avatar->loadAvatar((int)$this->uid);

        // ULTIMA ACCION
        db_exec([__FILE__, __LINE__], 'query', "UPDATE @miembros SET user_lastactive = $time WHERE user_id = {$this->uid}");
        // Si ha iniciado sesión cargamos estos datos.
        if ($login) {
            $getTimeNow = $this->Autenticar->getTimeNow();
            $getIpAddress = $this->Autenticar->getIpAddress();
            // Last login
            db_exec([__FILE__, __LINE__], 'query', "UPDATE @miembros SET user_lastlogin = $getTimeNow, user_last_ip = '$getIpAddress' WHERE user_id = {$this->uid}");
        }
    }

    /*
        HACEMOS LOGIN
        loginUser($username, $password, $remember = false, $redirectTo = NULL);
    */
    public function loginUser(string $username = '', string $password = '', bool $remember = false, bool $redirectTo = false)
    {
        $result = $this->Autenticar->login($username, $password, $remember, $redirectTo);
        if (!empty($result['success'])) {
            $this->uid = $result['user_id'];
            $this->loadUser($this->uid);
            // Cargamos la información del usuario
            $this->loadUser(true);
            // COMPROBAMOS SI TENEMOS QUE ASIGNAR MEDALLAS
            $this->DarMedalla();
        }
        return $result;
    }

    /**
     * Función para validar el código de autentificación
    */
    public function validateTwoFactor()
    {
        global $tsCore;

        $nick = $tsCore->setSecure($_POST['nick']);
        $rem = ($_POST['rem'] === 'true');

        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT user_id, user_secret_2fa, user_recovery FROM @miembros WHERE user_name = '$nick'"));

        $recovery = json_decode(base64_decode($data["user_recovery"]), true);

        $totp = TOTP::create($data['user_secret_2fa']);

        if ($totp->verify($_POST['code']) || in_array($_POST['code'], $recovery)) {
            $this->autenticar->update($data['user_id'], $rem, true);
            return '1: Código 2FA correcto.';
        }

        return '0: No se pudo comprobar la doble autentificación.';
    }

    /*
        CERRAR SESSION
        logoutUser($redirectTo)
    */
    public function logoutUser(int $user_id = 0, bool $redirectTo = false)
    {
        $this->Autenticar->logout();
        /* LIMPIAR VARIABLES */
        $this->info = '';
        $this->is_member = 0;
        # UPDATE
        $last_active = ((int)$this->Core->settings['c_last_active'] * 60);
        $last_active = time() - ($last_active * 3);
        db_exec([__FILE__, __LINE__], 'query', "UPDATE @miembros SET user_lastactive = $last_active WHERE user_id = $user_id");
        /* REDERIGIR */
        if ($redirectTo) {
            header("Location: {$this->Core->settings['url']}"); // REDIRIGIR
        }
        return true;
    }

    public function generateActivationKey(array $userData): string
    {
    // Por ejemplo, usar user_id + fecha de registro + un secreto
        $secret = 'mi_secreto_super_seguro';
        $data = $userData['user_id'] . '|' . $userData['user_registro'] . '|' . $secret;

    // Usamos hash moderno (sha256)
        return hash('sha256', $data);
    }

    /*
        userActivate()
    */
    public function userActivate(int $tsUserID = 0, string $tsKey = '')
    {
       // Obtener userID y key de $_GET si no se proporcionan
        if ($tsUserID === 0) {
            $tsUserID = (int)$_GET['uid'];
        }
        if (empty($tsKey)) {
            $tsKey = $this->Core->setSecure($_GET['key']);
        }
       // Consulta para obtener datos del usuario
        $tsData = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT user_id, user_name, user_password, user_registro FROM @miembros WHERE user_id = $tsUserID LIMIT 1"));
        if (!$tsData) {
            return false;
        }
       // Verificar si se encontraron datos y si la clave coincide
        if ($tsKey === $this->generateActivationKey($tsData)) {
           // Actualizar el estado del usuario a activo
            if (db_exec([__FILE__, __LINE__], 'query', "UPDATE @miembros SET user_activo = 1 WHERE user_id = $tsUserID")) {
                return $tsData;
            }
        }
        return false;
    }

    /*
        getUserBanned()
    */
    public function getUserBanned()
    {
        $uid = (int)$this->uid;
        $data = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT susp_id, user_id, susp_causa, susp_date, susp_termina, susp_mod, susp_ip FROM @suspension WHERE user_id = $uid LIMIT 1"));
        $now = time();
        if ((int)$data['susp_termina'] > 1 && (int)$data['susp_termina'] < $now) {
            db_exec([__FILE__, __LINE__], 'query', "UPDATE @miembros SET user_baneado = 0 WHERE user_id = $uid");
            db_exec([__FILE__, __LINE__], 'query', "DELETE FROM @suspension WHERE user_id = $uid");
            return false;
        } else {
            return $data;
        }
    }

    /*
        getUserID($tsUsername)
    */
    public function getUserID(string $tsUser = ''): int
    {
        $tsUser = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT user_id FROM @miembros WHERE user_name = '$tsUser' LIMIT 1"));
        $tsUserID = (int)$tsUser['user_id'] ?? 0;
        return $tsUserID;
    }
    /*
          getUserName($user_id)
     */
    public function getUserName(int $user_id = 0): string
    {
        $tsUser = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT user_name FROM @miembros WHERE user_id = $user_id LIMIT 1"));
        return $tsUser['user_name'];
    }
    /*
          getUserIsVerified($user_nick)
     */
    public function getUserIsVerified(string $user_name = ''): bool
    {
        $tsUser = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT user_verificado FROM @miembros WHERE user_name = '$user_name' LIMIT 1"));
        return ((int)$tsUser['user_verificado'] === 1);
    }
    /*
          getUserName($user_id)
     */
    public function getUserRango(int $user_id = 0, string $type = 'r_name')
    {
        $UserRango = db_exec('fetch_assoc', db_exec([__FILE__, __LINE__], 'query', "SELECT rango_id, r_name, r_color, r_image, user_rango FROM @rangos LEFT JOIN @miembros ON user_rango = rango_id WHERE user_id = $user_id LIMIT 1;"));
        return $UserRango[$type];
    }
    /**
     * @name iFollow
     * @access public
     * @param int
     * @return void
     */
    public function iFollow(int $user_id = 0): bool
    {
        # SIGO A ESTE USUARIO
        $data = db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT follow_id FROM @follows WHERE f_id = $user_id AND f_user = {$this->uid} AND f_type = 1 LIMIT 1"));
        //
        return ($data > 0) ? true : false;
    }

    public function isUserBloqued(int $b_user = 0, int $b_auser = 0)
    {
        db_exec('num_rows', db_exec([__FILE__, __LINE__], 'query', "SELECT bid, b_user, b_auser FROM @bloqueos WHERE b_user = $b_user AND b_auser = $b_auser LIMIT 1"));
    }
}
