<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace Admin\models;

use Admin\models\Core;
use App\Database\DB;

if (! defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class Foro
{
    protected Core $Core;

    public function __construct(Core $Core, User $User)
    {
        $this->Core = $Core;
    }

    private function filter(): array
    {
        $data = $_POST; // mejor filtrado antes, pero eso ya lo sabés
        unset($data['save'], $data['csrf_token']);
        return $data;
    }

    private function selectForos(?int $fid = 0): array
    {
        $data['sql'] = "SELECT fid, super_nombre, super_descripcion, super_color, super_img FROM @posts_supercategorias";
        if ($fid > 0) {
            $data['sql'] .= " WHERE fid = :fid";
            $data['param'] = ['fid' => $fid];
            return DB::fetch($data['sql'], $data['param']);
        }
        return DB::fetchAll($data['sql']);
    }

    public function getForos()
    {
        # Obtenemos todos los foros
        $data = $this->selectForos();
        # Mostraremos 3 categorías
        $max_display = 3;
        foreach ($data as $k => $super) {
            $data[$k]['super_img'] = $this->Core->imageCat($super['super_img'] ?? '1f30d.svg');
            $subcategorias = DB::fetchAll("SELECT c_nombre, c_seo FROM @posts_categorias WHERE c_foro = :fid", [
                'fid' => $super['fid']
            ]);

            # Solo mostraremos 3
            $total_tags = is_countable($subcategorias) ? count($subcategorias) : 0;
            $remaining_tags = $total_tags - $max_display;
            $data[$k]['super_subcategorias'] = array_slice($subcategorias, 0, $max_display);
            $data[$k]['remaining_tags'] = ($remaining_tags > 0) ? "+{$remaining_tags}" : "";
        }
        return $data;
    }

    public function getForo()
    {
        # Obtenemos todos los foros
        $data = $this->selectForos((int)$_GET['fid']);
        return $data;
    }

    public function saveCategoria()
    {
        return DB::update('posts_supercategorias', $this->filter(), ['fid' => (int)$_GET['fid']]);
    }

    public function newCategoria()
    {
        if (DB::insert('posts_supercategorias', $this->filter())) {
            return true;
        }
    }

    public function delCategoria()
    {
        return (DB::execute(
            "DELETE FROM @posts_supercategorias WHERE fid = :fid",
            ['fid' => (int)$_POST['fid']
            ]
        )) ? '1: Categoría eliminada' : '0: Problemas al eliminar.';
    }
}
