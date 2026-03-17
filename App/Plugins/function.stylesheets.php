<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
 */

declare(strict_types=1);

use App\Plugins\Ui\Meta;
use App\Plugins\Services\{SystemPlugin,UserPermission,Customizer};

function smarty_function_stylesheets($params, Smarty\Template $template)
{
    $scope = $template->getTemplateVars();
    $Permission = new UserPermission($scope);
    $System = new SystemPlugin($scope);
    $Customizer = new Customizer($scope);

    $page_wysibb = ['agregar', 'posts', 'fotos', 'mensajes'];
    $tpl = '';

   // Aseguramos que exista la clave
    if (!isset($params['files']) || !is_array($params['files'])) {
        $params['files'] = [];
    }

   // Función local para validar archivos
    $fileExists = function (string $file) use ($System): array {
        $files = [];

        $assetBase = TS_ASSETS . '/css/' . $file;
        $themeCss  = TS_THEMES . '/' . TS_TEMA . '/css/' . $file;
        $themeRoot = TS_THEMES . '/' . TS_TEMA . '/' . $file;

        if (is_file($assetBase)) {
            $files[] = $System->getRoute('assets:css', $file);
        } elseif (is_file($themeCss)) {
            $files[] = $System->getRoute('tema:css', $file);
        } elseif (is_file($themeRoot)) {
            $files[] = $System->getRoute('tema:base', $file);
        }

        return $files;
    };

   // Regla general: cargar archivo con el mismo nombre que la página
    if (!$System->inArray(['admin', 'moderacion'])) {
        $params['files'][] = "{$scope['tsPage']}.css";
    }

   // WysiBB
    if ($System->inArray($page_wysibb)) {
        $params['files'][] = 'wysibb.css';
    }

   // Admin > rangos
    if ($Permission->verify('admin', 'rangos')) {
        $params['files'][] = 'colorpicker.css';
    }

   // páginas de cuenta
    if ($System->inArray(['cuenta', 'login', 'registro'])) {
        $params['files'][] = 'buttons-social.css';
    }

   // Portal
    if ($scope['tsPage'] === 'portal') {
        $params['files'][] = 'perfil.css';
    }

   // Render final
    foreach ($params['files'] as $file) {
        foreach ($fileExists($file) as $href) {
            $tpl .= Meta::generateHtmlTag($href);
        }
    }

    $tpl .= $Customizer->customizer();

    return trim($tpl);
}
