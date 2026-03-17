<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

use App\Models\{Afiliado,Actividad,Comentarios,Foro,Home,Posts,Tops};
use App\Utils\Paginator;

$tsPage = "posts";

$tsLevel = 0;

$tsAjax = empty($_GET['ajax']) ? 0 : 1;

$tsContinue = true;

$tsTitle = $tsCore->settings['titulo'];

// Nivel y permisos de acceso
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if (!$tsLevelMsg) {
    $tsPage = 'aviso';
    $tsAjax = 0;
    $smarty->assign("tsAviso", $tsLevelMsg);
    //
    $tsContinue = false;
}

$Container->set(Paginator::class, Paginator::class);

$Container->set(Afiliado::class, Afiliado::class);
$Container->set(Posts::class, Posts::class);
$Container->set(Home::class, Home::class);
$Container->set(Tops::class, Tops::class);
$Container->set(Foro::class, Foro::class);
$Container->set(Comentarios::class, Comentarios::class);

if ($tsContinue) {
    // Afiliados
    $tsAfiliado = $Container->get(Afiliado::class);

    // Referido?
    if (!empty($_GET['ref'])) {
        $tsAfiliado->urlIn();
    }

    // Posts Class
    $tsPosts = $Container->get(Posts::class);

    // Comentarios Class
    $tsComentarios = $Container->get(Comentarios::class);

    // Category
    $category = htmlentities($_GET['cat'] ?? ($_GET["category"] ?? ''));

    // Post anterior/siguiente
    if (isset($_GET['action']) && in_array($_GET['action'], ['next', 'prev', 'fortuitae'])) {
        $tsPosts->setNP();
    }


    if (!empty($_GET['post_id'])) {
        // DATOS DEL POST
        $tsPost = $tsPosts->getPost();
        //
        if (isset($tsPost['post_id']) && $tsPost['post_id'] > 0) {
            // TITULO NUEVO
            $tsTitle = $tsPost['post_title'] . ' - ' . $tsTitle;
            $tsComments = $tsComentarios->getComentarios((int)$tsPost['post_id']);
            $assigns = [
                "tsPost" => $tsPost,
                "tsAutor" => $tsPosts->getAutor((int)$tsPost['post_user']),
                "tsPunteador" => $tsPosts->getPunteador(),
                "tsRelated" => $tsPosts->getRelated($tsPost['post_tags']),
                "tsPostAutor" => $tsPosts->getPostAutor((int)$tsPost['post_user']),
                "tsComments" => [
                    'num' => $tsComments['num'],
                    'data' => $tsComments['data']
                ]
            ];
            // ASIGNAMOS A LA PLANTILLA
            $smarty->assign($assigns);
            // PAGINAS
            $Paginator = $Container->get(Paginator::class);
            $tsPages = $Paginator->getPages((int)$tsPost['post_comments'], (int)$tsCore->settings['c_max_com']);
            $tsPages['post_id'] = $tsPost['post_id'];
            $tsPages['autor'] = $tsPost['post_user'];
            $smarty->assign("tsAnterior", $tsPosts->getTitles('prev'));
            $smarty->assign("tsSiguente", $tsPosts->getTitles('next'));
            //
            $smarty->assign("tsPages", $tsPages);
            require UTILITIES . '/extras/datos.php';
            $smarty->assign("tsReactions", $reacciones);
        } else {
            //
            $tsAjax = 0;
            $smarty->assign("tsAviso", $tsPost);
            // RELACIONADOS
            $tsRelated = $tsPosts->getRelated();
            $smarty->assign("tsRelated", $tsRelated);
            $tsTitle = $tsPost[1] . ' - ' . $tsTitle;
            $tsPage = "post." . ($tsPost[0] === 'privado' ? 'privado' : 'aviso');

            $smarty->assign("tsType", 'post');
        }
    } else {
        // PAGINA
        $tsPage = "home";
        $tsTitle = $tsTitle . ' - ' . $tsCore->settings['slogan'];  // TITULO DE LA PAGINA ACTUAL

        // CLASE TOPS
        $tsHome = $Container->get(Home::class);
        $tsTops = $Container->get(Tops::class);

        // CAT
        $smarty->assign("tsCat", $category);
        // TITULO
        if (!empty($category)) {
            $catData = $tsHome->getCategory($category);
            $tsTitle = $tsCore->settings['titulo'] . ' - ' . $catData['c_nombre'];
            $smarty->assign("tsCatData", $catData);
        }
        if ((int)$tsCore->settings['c_allow_foro'] === 0 || !empty($category)) {
            // ULTIMOS POSTS
            $tsLastPosts = $tsHome->getLastPosts($category);
            $smarty->assign("tsPosts", $tsLastPosts['data']);
            $smarty->assign("tsPages", $tsLastPosts['pages']);
            // ULTIMOS POSTS FIJOS
            $smarty->assign("tsPostsStickys", $tsHome->getLastPostsStickys());
            // AFILIADOS
            $smarty->assign("tsAfiliados", $tsAfiliado->getAfiliados());
        }

        if ((int)$tsCore->settings['c_allow_foro'] === 1) {
            $tsForo = $Container->get(Foro::class);
            $smarty->assign("tsForos", $tsForo->getForoPosts());
            #var_dump($tsForo->getForoPosts());
        }
        $smarty->assign("tsStats", $tsTops->getStats());
        // ULTIMOS COMENTARIOS
        $smarty->assign("tsComments", $tsComentarios->getLastComentarios());
        // TOP POSTS
        $smarty->assign("tsTopPosts", $tsTops->getHomeTopPosts()['historico']);
        // TOP USERS
        $smarty->assign("tsTopUsers", $tsTops->getHomeTopUsers()['historico']);
    }
}

if (empty($tsAjax)) {
    $smarty->assign("tsTitle", $tsTitle);

    include BASEPATH . "footer.php";
}
