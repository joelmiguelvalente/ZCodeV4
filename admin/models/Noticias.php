<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

namespace Admin\models;

use Admin\models\{Core,User};
use Admin\services\AdminService;
use App\Database\DB;

if (! defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class Noticias
{
    protected Core $Core;

    protected User $User;

    protected AdminService $AdminService;

    public function __construct(Core $Core, User $User, AdminService $AdminService)
    {
        $this->Core = $Core;
        $this->User = $User;
        $this->AdminService = $AdminService;
    }

    # ===================================================
    # NOTICIAS
    # * getNoticias() :: Obtenemos todas las noticias
    # * getNoticia() :: Obtenemos la noticia por ID
    # * delNoticia() :: Eliminamos la noticia por ID
    # * newNoticia() :: Creamos una nueva notica
    # * editNoticia() :: Editamos la noticia
    # ===================================================
    private function sameNoticeSave(bool $new = true)
    {
        $data = $_POST;
        foreach ($data as $key => $val) {
            if ($key === 'not_body') {
                $val = $this->Core->setSecure($this->Core->parseBadWords(substr($val, 0, 190)));
            }
            $data[$key] = is_numeric($val) ? (int)$val : (string)$val;
        }
        if ($new) {
            $data['not_autor'] = $this->User->uid;
            $data['not_date'] = time();
        }
        return $data;
    }

    private function getID(): array
    {
        return [
            'not_id' => (int)$_GET['id'] ?? (int)$_POST['id']
        ];
    }

    # Obtenemos todas las noticias
    public function getNoticias()
    {
        $data = DB::fetchAll("SELECT u.user_id, u.user_name, n.not_id, n.not_body, n.not_autor, n.not_date, n.not_type, n.not_active FROM @noticias AS n LEFT JOIN @miembros AS u ON n.not_autor = u.user_id WHERE n.not_id > 0 ORDER BY n.not_id DESC");
        foreach ($data as $nid => $noticia) {
            $data[$nid]['not_body'] = $this->Core->parseBBCode($noticia['not_body']);
        }
        return $data;
    }

    # Obtenemos la noticia por ID
    public function getNoticia()
    {
        $data = DB::fetch("SELECT not_id, not_body, not_autor, not_date, not_type, not_active FROM @noticias WHERE not_id = :not_id LIMIT 1", $this->getID());
        return $data;
    }

    # Eliminamos la noticia por ID
    public function delNoticia()
    {
        if (!DB::rowCount("SELECT not_id FROM @noticias WHERE not_id = :not_id", $this->getID())) {
            return '0: El id ingresado no existe.';
        }
        return (!DB::execute("DELETE FROM @noticias WHERE not_id = :not_id", $this->getID())) ? '1: Noticia eliminada' : '0: No se pudo borrar noticia.';
    }

    # Creamos una nueva notica
    public function newNoticia()
    {
        if (empty($_POST['not_body'])) {
            return false;
        }
        return DB::execute("INSERT INTO @noticias (not_body, not_active, not_type, not_date) VALUES (:not_body, :not_active, :not_type, :not_date)", $this->sameNoticeSave()) ? true : false;
    }

    # Editamos la noticia
    public function editNoticia()
    {
        if (empty($_POST['not_body'])) {
            return false;
        }
        return DB::update('noticias', $this->sameNoticeSave(false), $this->getID()) ? true : false;
    }
}
