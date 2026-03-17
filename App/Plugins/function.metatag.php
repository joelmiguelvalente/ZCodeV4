<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
 */

declare(strict_types=1);

use App\Plugins\Ui\Meta;

function smarty_function_metatag($params, Smarty\Template $template)
{
    $scope = $template->getTemplateVars();
    // Para mostrar
    $tpl = "";

    // Metas por defecto
    $default = [
      'type'         => isset($scope['tsPost']['post_id']) ? 'article' : 'website',
      'title'        => $scope['tsPost']['post_title']        ?? $scope['tsSeo']->seo['seo_titulo'],
      'description'  => $scope['tsPost']['post_descripcion']  ?? $scope['tsSeo']->seo['seo_descripcion'],
      'keywords'     => isset($scope['tsPost']['post_tags']) ? implode(',', $scope['tsPost']['post_tags']) : strtolower($scope['tsSeo']->seo['seo_keywords']),
      'image'        => $scope['tsPost']['post_portada']['lg'] ?? "{$scope['tsConfig']['url']}/assets/images{$scope['tsSeo']->seo['seo_portada']}",
      'card'         => 'summary_large_image'
    ];

    // Metas estándar (sin card)
    foreach ($default as $name => $value) {
        if ($name === 'card') {
            continue;
        }
        $tpl .= Meta::name($name, $value);
    }

    // Robots
    if (isset($params['robots']) && is_array($params['robots'])) {
        $content = $params['robots']['content'] ?? 'index,follow';
        foreach (['robots','googlebot','bingbot'] as $type) {
            $robotValue = $content;
            if ($type !== 'robots') {
                $robotValue .= ", max-snippet:-1, max-image-preview:large, max-video-preview:-1";
            }
            $tpl .= Meta::name($type, $robotValue);
        }
    }

    // Qué redes sociales activar
    $seo = [
      'facebook' => isset($params['seo']) && in_array('facebook', $params['seo'], true),
      'twitter'  => isset($params['seo']) && in_array('twitter', $params['seo'], true)
    ];
    $analytics = isset($params['seo']['analytics']) ?? false;

    # Facebook / Twitter
    $networks = [
        'twitter' => [
            'attr' => 'name',
            'prefix' => 'twitter',
            'fields' => ['card', 'title', 'description', 'image']
        ],
        'facebook' => [
            'attr' => 'property',
            'prefix' => 'og',
            'fields' => ['type', 'title', 'description', 'image']
        ]
    ];

    foreach ($networks as $nombre => $valor) {
        if ($seo[$nombre]) {
            foreach ($valor['fields'] as $field) {
                if ($nombre === 'twitter') {
                    $tpl .= Meta::twitter($field, $default[$field]);
                    continue;
                }
                if ($nombre === 'facebook') {
                    $tpl .= Meta::og($field, $default[$field]);
                }
            }
        }
    }

    // Favicon y Apple Touch Icons
    $asset = "{$scope['tsConfig']['url']}/assets/images/favicon";
    $tpl .= "<link rel=\"icon\" href=\"$asset/logo-32.webp\" sizes=\"32x32\" />\n";
    $tpl .= "<link rel=\"icon\" href=\"$asset/logo-128.webp\" sizes=\"128x128\" />\n";
    $tpl .= "<link rel=\"apple-touch-icon-precomposed\" href=\"$asset/logo-128.webp\" />\n";
    $tpl .= "<meta name=\"msapplication-TileImage\" content=\"$asset/logo-256.webp\" />";

    return trim($tpl);
}
