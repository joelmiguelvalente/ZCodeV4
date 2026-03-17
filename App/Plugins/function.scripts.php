<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
 */

declare(strict_types=1);

use App\Plugins\Ui\Meta;
use App\Plugins\Services\UserPermission;

function smarty_function_scripts($params, Smarty\Template $template)
{
    $scope = $template->getTemplateVars();
    $Permission = new UserPermission($scope);

    $page_wysibb = ['agregar', 'posts', 'fotos', 'mensajes'];
    $tpl = '';

    // Asegura existencia del array
    if ($scope['tsPage'] === 'access') {
        $params['files'] = [];
    } else {
        $params['files'] = $params['files'] ?? [];
        if (!is_array($params['files'])) {
            $params['files'] = [];
        }
    }
    if ($scope['tsPage'] === 'admin' && $scope['tsAction'] === '') {
        $params['files'][] = 'joypixels.js';
        $params['files'][] = 'versiones.js';
    }
    if ($scope['tsPage'] === 'admin' && !empty($scope['tsAction'])) {
        $params['files'][] = $scope['tsAction'] . '.js';
    }

    // Archivos base
    /*$params['files'] = array_merge(
        ['jQuery.min.js', 'plugins.js'],
        $params['files']
    );*/

    // Función para validar archivos
    $fileExists = function (string $file) use ($Permission): array {
        $files = [];
        $path = TS_ASSETS . '/js/' . $file;

        if (is_file($path)) {
            $files[] = $Permission->getRoute('assets:js') . '/' . $file;
        }

        return $files;
    };

    if ($scope['tsAction'] === 'registro' && $scope['tsPage'] !== 'admin') {
        $params['files'][] = 'reCaptcha.js';
    }

    // Archivo según la página
    $jsPageType = ($scope['tsPage'] === 'access') ? 'tsAction' : 'tsPage';
    if (!empty($scope[$jsPageType])) {
        $params['files'][] = $scope[$jsPageType] . '.js';
    }

    // Render final
    foreach ($params['files'] as $file) {
        foreach ($fileExists($file) as $href) {
            $tpl .= Meta::generateHtmlTag($href, ($file !== 'joypixels.js'));
        }
    }

    return trim($tpl);
}
