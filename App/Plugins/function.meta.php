<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @version     4.1.0
 */

use App\Models\Seo;

function smarty_function_meta($params, Smarty\Template $template)
{

    # Opciones del usuario + defaults
    $opts = [
        'facebook'  => $params['facebook']  ?? false,
        'twitter'   => $params['twitter']   ?? false,
        'analytics' => $params['analytics'] ?? false,
        'robots'    => [
            'active' => $params['robots']   ?? false,
            'name'   => $params['name']     ?? 'robots',
            'content' => $params['content']  ?? 'index, follow'
        ]
    ];

    # Variables Smarty
    $tsCore  = $template->getTemplateVars("tsConfig") ?? [];
    $tsPost  = $template->getTemplateVars("tsPost")   ?? [];
    $tsFoto  = $template->getTemplateVars("tsFoto")   ?? [];
    $tsRoutes = $template->getTemplateVars("tsRoutes") ?? [];

    # Datos SEO base
    $seo = (new Seo())->getSeo();
    if (empty($seo)) {
        return '';
    }

    $url = (($_SERVER['HTTPS'] ?? '') === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/');

    $keywords = !empty($tsPost['post_id']) ? implode(', ', $tsPost['post_tags'] ?? []) : ($seo['seo_keywords'] ?? '');

    $image = $tsPost['post_portada']['lg'] ?? ($tsFoto['foto_url'] ?? "{$tsRoutes['assets']['base']}/images/sin_portada.png");

    # Construcción de metadatos
    $metaData = [
        'type' => isset($tsPost['post_id']) ? 'article' : 'website',
        'title' => $tsPost['post_title'] ?? ($tsFoto['f_title'] ?? ($seo['seo_titulo'] ?? $tsCore['titulo'])),
        'description' => $tsPost['post_body_descripcion'] ?? ($tsFoto['foto_descripcion'] ?? ($seo['seo_descripcion'] ?? "{$tsCore['titulo']} - {$tsCore['slogan']}")),
        'keywords' => strtolower($keywords),
        'image' => $image,
        'card' => 'summary_large_image'
    ];

    $html = "";

    # 5) Meta tags genéricos
    foreach ($metaData as $name => $value) {
        if ($name === "card") {
            continue;
        }
        $html .= tagMeta($name, htmlspecialchars($value, ENT_QUOTES));
    }

    # Facebook / Twitter
    $networks = [
        'facebook' => [
            'attr' => 'property',
            'prefix' => 'og',
            'fields' => ['type', 'title', 'description', 'image']
        ],
        'twitter' => [
            'attr' => 'name',
            'prefix' => 'twitter',
            'fields' => ['card', 'title', 'description', 'image']
        ]
    ];

    foreach ($networks as $key => $cfg) {
        if (!$opts[$key]) {
            continue;
        }

        $html .= tagMeta("{$cfg['prefix']}:url", $url, $cfg['attr']);

        foreach ($cfg['fields'] as $field) {
            $html .= tagMeta("{$cfg['prefix']}:$field", $metaData[$field], $cfg['attr']);
        }
    }

    # Robots
    if ($opts['robots']['active']) {
        $html .= tagMeta($opts['robots']['name'], $opts['robots']['content']);
    }

    # Sitemap
    if (($seo['seo_sitemap'] ?? 0) == 1 || file_exists(BASEPATH . 'sitemap.xml')) {
        $html .= "<link rel=\"sitemap\" type=\"application/xml\" href=\"{$tsCore['url']}/sitemap.xml\" />\n";
    }

    # Favicon
    foreach ([16,32,64,128,256] as $size) {
        $html .= "<link rel=\"icon\" type=\"image/webp\" sizes=\"{$size}x{$size}\" href=\"{$tsRoutes['assets']['favicon']}/logo-$size.webp\" />\n";
    }

    # Validación Google Analytics
    $gaID = trim($seo['seo_google_analytics'] ?? '');

    if ($opts['analytics'] && validateGA($gaID)) {
        $gaID = htmlspecialchars($gaID, ENT_QUOTES);
        $html .= <<<HTML
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$gaID}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{$gaID}');
</script>
HTML;
    }

    return $html;
}

/* ---------------------------------------------------------
 * Función aislada para crear etiqueta meta
 * --------------------------------------------------------- */
function tagMeta(string $name, string $content, string $attrName = 'name'): string
{
    return "<meta {$attrName}=\"{$name}\" content=\"{$content}\" />\n";
}


/* ---------------------------------------------------------
 * Función aislada para validar Google Analytics
 * --------------------------------------------------------- */
function validateGA(string $id): bool
{
    return preg_match('/^UA-\d{7,9}-\d{1,2}$/', $id) || preg_match('/^G-[A-Za-z0-9]{10}$/', $id);
}
