<?php

use Admin\models\Sitemap;

$Container->set(Sitemap::class, Sitemap::class);
$Sitemap = $Container->get(Sitemap::class);

$tsTitle = 'Administrar sitemap';
if (empty($act)) {
    $smarty->assign('tsURLs', $Sitemap->getSitemap());
} elseif ($act === 'sync') {
    if ($_GET['type'] === 'robots') {
        $Container->set(Seo::class, Seo::class);
        $Seo = $Container->get(Seo::class);
        if ($Seo->syncRobots()) {
            $tsAdmin->redirect();
        }
    }
    if ($_GET['type'] === 'sitemap') {
        if ($Sitemap->syncSitemap()) {
            $tsAdmin->redirect();
        }
    }
} elseif ($act === 'nueva') {
    if ($Sitemap->newUrlSitemap()) {
        $tsAdmin->redirect();
    }
} elseif ($act === 'editar') {
    $smarty->assign('tsURL', $Sitemap->SitemapEditID());
    if ($Sitemap->SitemapSaveID()) {
        $tsAdmin->redirect();
    }
} elseif ($act === 'config') {
    $smarty->assign('tsSitemap', $Sitemap->setSettings());
    if ($Sitemap->saveSettings()) {
        $tsAdmin->redirect();
    }
}
