<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace Admin\services;

use App\Database\DB;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class AdminService
{
    # Extensiones para imagenes
    public array $extension = ["jpeg", "jpg", "png", "gif", "bmp", "svg", "webp", "avif"];

    /**
     * Función para obtenener a los administradores
    */
    public function getAdministrators()
    {
        $admins = DB::fetchAll("SELECT u.user_id, u.user_name, p.p_nombre FROM @miembros AS u LEFT JOIN @perfil AS p ON u.user_id = p.user_id WHERE u.user_rango = :rango ORDER BY u.user_id", ['rango' => 1]);
        return $admins;
    }

    /**
     * Función para obtener la fecha de instalación/actualización
    */
    public function getFoundation()
    {
        return DB::fetch("SELECT stats_time_foundation, stats_time_upgrade FROM @stats WHERE stats_no = :id", ['id' => 1]);
    }

    # Las opciones para los rangos (saveRango() y newRango())
    private function optionsRange($post): string
    {
        return serialize([
            'suad'      => $post['superadmin'],
            'sumo'      => $post['supermod'],
            'moacp'     => $post['mod-accesopanel'],
            'mocdu'     => $post['mod-cancelardenunciasusuarios'],
            'moadf'     => $post['mod-aceptardenunciasfotos'],
            'mocdf'     => $post['mod-cancelardenunciasfotos'],
            'mocdp'     => $post['mod-cancelardenunciasposts'],
            'moadm'     => $post['mod-aceptardenunciasmensajes'],
            'mocdm'     => $post['mod-cancelardenunciasmensajes'],
            'movub'     => $post['mod-verusuariosbaneados'],
            'moub'      => $post['mod-usarbuscador'],
            'morp'      => $post['mod-reciclajeposts'],
            'morf'      => $post['mod-reficlajefotos'],
            'mocp'      => $post['mod-contenidoposts'],
            'mocc'      => $post['mod-contenidocomentarios'],
            'most'      => $post['mod-sticky'],
            'moayca'    => $post['mod-abrirycerrarajax'],
            'movcud'    => $post['mod-vercuentasdesactivadas'],
            'movcus'    => $post['mod-vercuentassuspendidas'],
            'mosu'      => $post['mod-suspenderusuarios'],
            'modu'      => $post['mod-desbanearusuarios'],
            'moep'      => $post['mod-eliminarposts'],
            'moedpo'    => $post['mod-editarposts'],
            'moop'      => $post['mod-ocultarposts'],
            'mocepc'    => $post['mod-comentarpostcerrado'],
            'moedcopo'  => $post['mod-editarcomposts'],
            'moaydcp'   => $post['mod-desyaprobarcomposts'],
            'moecp'     => $post['mod-eliminarcomposts'],
            'moef'      => $post['mod-eliminarfotos'],
            'moedfo'    => $post['mod-editarfotos'],
            'moecf'     => $post['mod-eliminarcomfotos'],
            'moepm'     => $post['mod-eliminarpubmuro'],
            'moecm'     => $post['mod-eliminarcommuro'],
            'moat'      => $post['mod-administrartickets'],
            'moet'      => $post['mod-eliminartickets'],
            'godp'      => $post['global-darpuntos'],
            'gopp'      => $post['global-publicarposts'],
            'gopcp'     => $post['global-publicarcomposts'],
            'govpp'     => $post['global-votarposipost'],
            'govpn'     => $post['global-votarnegapost'],
            'goepc'     => $post['global-editarpropioscomentarios'],
            'godpc'     => $post['global-eliminarpropioscomentarios'],
            'gopf'      => $post['global-publicarfotos'],
            'gopcf'     => $post['global-publicarcomfotos'],
            'gorpap'    => $post['global-revisarposts'],
            'govwm'     => $post['global-vermantenimiento'],
            'goaf'      => $post['global-antiflood'],
            'gopfp'     => $post['global-pointsforposts'],
            'gopfd'     => $post['global-pointsforday'],
            'goda'      => $post['global-avatargif']
        ]);
    }

    /**
     * Obtenemos todos los temas que existan en themes
    */
    public function getAllThemes(): array
    {
        $themes = scandir(TS_THEMES);
        $exists = [];
        foreach ($themes as $tid => $theme) {
            if (in_array($theme, ['.', '..'])) {
                continue;
            }
            $exists[$tid] = $theme;
        }
        return $exists;
    }

    /**
     * Agregamos esta función ya que se repite 2 veces,
     * extraemos las imagenes
    */
    public function getExtraIcons(string $folder = 'categorias')
    {
        $ruta = TS_ASSETS . "/images/$folder";
        # Accedemos a la carpeta de icons
        $carpeta = scandir($ruta);
        # Recorremos la carpeta
        foreach ($carpeta as $pid => $image) {
            if (in_array($image, ['.', '..'])) {
                continue;
            }
            # Comprobamos extension
            $ext = pathinfo($image, PATHINFO_EXTENSION);
            if (in_array($ext, $this->extension)) {
                $icons[] = $image;
            }
        }
        # Retornamos las imagenes
        return $icons;
    }

    /**
     * Función para obtener versiones del sistema
    */
    public function getVersions()
    {
        $temp = @gd_info();
        return [
            'php' => PHP_VERSION,
            'mysql' => db_exec('fetch_row', db_exec([__FILE__, __LINE__], 'query', 'SELECT VERSION()')),
            'server' => $_SERVER['SERVER_SOFTWARE'],
            'gd' => $temp['GD Version'] ?? 'La biblioteca GD no está instalada'
        ];
    }
}
