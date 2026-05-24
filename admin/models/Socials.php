<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace Admin\models;

use admin\models\Core;
use App\Database\DB;

if (! defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class Socials
{
    protected Core $Core;

    public function __construct(Core $Core)
    {
        $this->Core = $Core;
    }

    private function redirect_uri_create(string $param = '/'): string
    {
        return $this->Core->route('url') . ($param === '/' ? $param : strtolower($param) . '.php');
    }

    private function getID(): int
    {
        $input = [
          [INPUT_GET, 'id'],
          [INPUT_POST, 'social_id'],
          [INPUT_POST, 'id']
        ];
        foreach ($input as [$method, $key]) {
            $id = filter_input($method, $key, FILTER_VALIDATE_INT);
            if ($id !== false && $id !== null) {
                return (int)$id;
            }
        }
        return 0;
    }

    private function filter(): array
    {
        $data = $_POST; // mejor filtrado antes, pero eso ya lo sabés
        unset($data['save'], $data['csrf_token']);
        return $data;
    }

    private function getData(?string $param = '')
    {
        $social = [];
        $data = ['name', 'client_id', 'client_secret'];
        foreach ($data as $item) {
            $social[$item] = $this->Core->setSecure($_POST["social_$item"]);
        }
        return $social[$param];
    }

    public function getSocials()
    {
        $data = DB::fetchAll("SELECT social_id, social_name, social_client_id, social_client_secret, social_redirect_uri FROM @social");
        foreach ($data as $key => $social) {
            $data[$key]['social_redirect_uri'] = $this->redirect_uri_create("/{$social['social_name']}");
        }
        return $data;
    }

    public function newSocial()
    {
        return DB::insert('social', $this->filter());
    }

    public function getSocial()
    {
        $data = DB::fetch("SELECT social_id, social_name, social_client_id, social_client_secret, social_redirect_uri FROM @social WHERE social_id = :social", [
            'social' => $this->getID()
        ]);
        return $data;
    }

    public function saveSocial()
    {
        return DB::update('social', $this->filter(), ['social' => $this->getID()]);
    }

    public function eliminarRed()
    {
        $id = $this->getID();
        if ($id === 0) {
            return false;
        }
        return (DB::execute("DELETE FROM @social WHERE social_id = :social", [
            'social' => $id
        ]));
    }
}
