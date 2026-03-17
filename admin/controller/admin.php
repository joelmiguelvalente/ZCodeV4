<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

use Admin\models\{
    Admin,
    Afiliado,
    Database,
    Favicon,
    Foro,
    Iconos,
    Medal,
    Mensajes,
    Noticias,
    Seo,
    Socials
};
use Admin\services\AdminService;

$tsPage = "admin";  // tsPage.tpl -> PLANTILLA PARA MOSTRAR CON ESTE ARCHIVO.

$tsLevel = 4;       // NIVEL DE ACCESO A ESTA PAGINA. => VER FAQs

$tsAjax = empty($_GET['ajax']) ? 0 : 1; // LA RESPUESTA SERA AJAX?

$tsContinue = true; // CONTINUAR EL SCRIPT

require_once dirname(__DIR__, 1) . "/header.php";  // INCLUIR EL HEADER

$tsTitle = $tsCore->settings['titulo'] . ' - ' . $tsCore->settings['slogan'];   // TITULO DE LA PAGINA ACTUAL

// VERIFICAMOS EL NIVEL DE ACCSESO ANTES CONFIGURADO
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if (!$tsLevelMsg) {
    $tsPage = 'aviso';
    $tsAjax = 0;
    $smarty->assign("tsAviso", $tsLevelMsg);
    //
    $tsContinue = false;
}

$Container->set(Admin::class, Admin::class);
$Container->set(AdminService::class, AdminService::class);

if ($tsContinue) {
    // ACTION
    $action = htmlspecialchars($_GET['action'] ?? '');
    // ACTION 2
    $act = htmlspecialchars($_GET['act'] ?? '');

    // CLASE POSTS
    $tsAdmin = $Container->get(Admin::class);
    $AdminService = $Container->get(AdminService::class);

    $CustomAdminMenu = require_once dirname(__DIR__, 1) . '/CustomAdminMenu.php';
    $smarty->assign('custom_menu', $CustomAdminMenu);

    // Bienvenida
    if ($action === '') {
        $tsTitle = 'Centro de Administración';
        $smarty->assign("tsAdmins", $AdminService->getAdministrators());
        $smarty->assign("Foundation", $AdminService->getFoundation());
        $smarty->assign("tsAllThemes", $AdminService->getAllThemes());

    // Creditos
    } elseif ($action === 'creditos') {
        $tsTitle = 'Soporte y Cr&eacute;ditos';
        $smarty->assign("tsVersion", $AdminService->getVersions());

    // Base de datos
    } elseif ($action === 'database') {
        $tsTitle = 'Base de datos';
        $Container->set(Database::class, Database::class);
        $tsDatabase = $Container->get(Database::class);
        $smarty->assign('tsTablesSQL', $tsDatabase->getAllTables());
        if ($act === 'lista') {
            $smarty->assign('tsBackupSQL', $tsDatabase->getBackups());
        }

   // Foro
    } elseif (in_array($action, ['foro', 'temas', 'users', 'sitemap'])) {
        include "$action.php";

    // Generador de favicon
    } elseif ($action === 'favicon') {
        $tsTitle = 'Generador de favicon';
        $Container->set(Favicon::class, Favicon::class);
        $Favicon = $Container->get(Favicon::class);

        $smarty->assign('tsAllFavicons', $Favicon->getAllFavicons());

    // Administrar imagenes categoras, medallas y rango
    } elseif ($action === 'packs') {
        $tsTitle = 'Packs de iconos';
        $Container->set(Iconos::class, Iconos::class);
        $Iconos = $Container->get(Iconos::class);
        $smarty->assign('tsDir', $_GET["path"] ?? '');
        if ($act === 'abrir') {
            $smarty->assign('tsPack', $Iconos->getPack());
        }

    // Configuraciones y Registro
    } elseif (in_array($action, ['configs', 'registro'])) {
        $tsTitle = ($action === 'configs') ? 'Configuraci&oacute;n' : 'Registro de ' . $tsTitle;
        // GUARDAR CONFIGURACION
        if (!empty($_POST['titulo']) or (!empty($_POST['pkey']) and !empty($_POST['skey']))) {
            if ($tsAdmin->saveConfig()) {
                $tsAdmin->redirect();
            }
        }

    // Noticias
    } elseif ($action === 'news') {
        $Container->set(Noticias::class, Noticias::class);
        $Noticias = $Container->get(Noticias::class);
        $tsTitle = 'Noticias';
        if (empty($act)) {
            $smarty->assign("tsNews", $Noticias->getNoticias());
        } elseif ($act === 'nuevo' && !empty($_POST['not_body'])) {
            if ($Noticias->newNoticia()) {
                $tsAdmin->redirect();
            }
        } elseif ($act === 'editar') {
            if (!empty($_POST['not_body'])) {
                if ($Noticias->editNoticia()) {
                    $tsAdmin->redirect();
                }
            } else {
                $smarty->assign("tsNew", $Noticias->getNoticia());
            }
        } elseif ($act === 'borrar') {
            if ($Noticias->delNoticia()) {
                $tsCore->redireccionar('admin', $action, 'borrar=true');
            }
        }

     // Redes sociales
    } elseif ($action === 'socials') {
        // CLASE MEDAL
        $Container->set(Socials::class, Socials::class);
        $Socials = $Container->get(Socials::class);
        $smarty->assign('tsNetsSocials', [
          'discord' => 'Discord',
          'facebook' => 'Facebook',
          'github' => 'Github',
          'google' => 'Google'
        ]);
        //
        $tsTitle = 'Configurar redes sociales';
        if (empty($act)) {
            $smarty->assign('tsSocials', $Socials->getSocials());
        }
        // Editar o Nuevo tema
        elseif (in_array($act, ['editar', 'nueva'])) {
            $tsTitle = ucfirst($act) . ' red social';
            if (!empty($_POST['save']) or !empty($_POST['edit'])) {
                $social = ($act === 'editar') ? $Socials->saveSocial() : $Socials->newSocial();
                if ($social) {
                    $tsAdmin->redirect();
                }
            } else {
                if ($act === 'editar') {
                    $smarty->assign("tsSocial", $Socials->getSocial());
                }
                if ($act === 'nuevo') {
                    $smarty->assign("tsError", $Socials->newSocial());
                }
            }
        }

    // Seo
    } elseif ($action === 'seo') {
        // CLASE MEDAL
        $tsTitle = 'Configurar SEO';
        $Container->set(Seo::class, Seo::class);
        $Seo = $Container->get(Seo::class);

        if (empty($act)) {
            $smarty->assign('tsSeo', $Seo->getSeo());
        }
        if (!empty($_POST['titulo'])) {
            if ($Seo->saveSEO()) {
                $tsAdmin->redirect();
            }
        }

    // Control de mensajes
    } elseif ($action == 'mensajes') {
        if (empty($act)) {
            $smarty->assign("tsControlMensajes", $tsMensajes->getMensajesControl());
        } elseif ($act == 'leer') {
            $smarty->assign("tsDatamp", $tsMensajes->getDataMensajePrivado());
            $smarty->assign("tsLeermp", $tsMensajes->getLeerMensajePrivado());
        }

    // Publicidades
    } elseif ($action === 'ads') {
        $tsTitle = 'Publicidades';
        if (!empty($_POST['save'])) {
            if ($tsAdmin->saveAds()) {
                $tsAdmin->redirect();
            }
        }

    // POSTS
    } elseif ($action === 'posts') {
        $tsTitle = 'Todos los posts';
        if (!$act) {
            $smarty->assign("tsAdminPosts", $tsAdmin->GetAdminPosts());
        }

    //FOTOS
    } elseif ($action === 'fotos') {
        $tsTitle = 'Todas las fotos';
        if (!$act) {
            $smarty->assign("tsAdminFotos", $tsAdmin->GetAdminFotos());
        }

    // ESTADÍSTICAS
    } elseif ($action === 'stats') {
        $tsTitle = 'Estad&iacute;sticas';
        $smarty->assign("tsAdminStats", $tsAdmin->GetAdminStats());

    // CAMBIOS DE NOMBRE DE USUARIO
    } elseif ($action === 'nicks') {
        $tsTitle = 'Nicks';
        $smarty->assign("tsAdminNicks", $tsAdmin->getChangeNicks($act));

   // LISTA NEGRA
    } elseif ($action === 'blacklist') {
        $tsTitle = 'Lista negra';
        if (!$act) {
            $smarty->assign("tsBlackList", $tsAdmin->getBlackList());
        } elseif (in_array($act, ['editar', 'nuevo'])) {
            $tsTitle = ucfirst($act) . ' lista negra';
            if ($_POST['edit'] or $_POST['new']) {
                $response = ($act === 'editar') ? $tsAdmin->saveBlock() : $tsAdmin->newBlock();
                if ($response) {
                    $tsAdmin->redirect();
                } else {
                    $smarty->assign("tsError", $response);
                }
                // Arreglo
                $block['value'] = $_POST['value'];
                $block['type'] = $_POST['type'];
                if ($act === 'nuevo') {
                    $block['reason'] = $_POST['reason'];
                }
                //
                $smarty->assign("tsBL", $block);
            } else {
                $smarty->assign("tsBL", $tsAdmin->getBlock());
            }
        }

    // CENSURAS
    } elseif ($action === 'badwords') {
        $tsTitle = 'Todas las censuras';
        if (!$act) {
            $smarty->assign("tsBadWords", $tsAdmin->getBadWords());
        } elseif (in_array($act, ['editar', 'nuevo'])) {
            $tsTitle = ucfirst($act) . ' censura';
            if ($_POST['edit'] or $_POST['new']) {
                $response = ($act === 'editar') ? $tsAdmin->saveBadWord() : $tsAdmin->newBadWord();
                if ($response == 1) {
                    $tsAdmin->redirect();
                } else {
                    $smarty->assign("tsError", $response);
                }
                $tsBWA = [
                    'word' => $_POST['before'],
                    'swop' => $_POST['after'],
                    'method' => $_POST['method'],
                    'type' => $_POST['type']
                ];
                if ($act === 'nuevo') {
                    $tsBWA['reason'] = $_POST['reason'];
                }
                $smarty->assign("tsBW", $tsBWA);
            } else {
                $smarty->assign("tsBW", $tsAdmin->getBadWord());
            }
        }

     // Sesiones
    } elseif ($action === 'sesiones') {
        $tsTitle = 'Todos las sesiones';
        if (!$act) {
            $smarty->assign("tsAdminSessions", $tsAdmin->GetSessions());
        }

   // Medallas
    } elseif ($action === 'medals') {
        $tsTitle = 'Todas las medallas';
        // CLASE MEDAL
        require_once TS_MODELS . "c.medals.php";
        //
        if (empty($act)) {
            $smarty->assign("tsMedals", $tsMedal->adGetMedals());
        } elseif (in_array($act, ['nueva', 'editar'])) {
            $tsTitle = ucfirst($act) . ' medalla';
            if (isset($_POST['save']) or isset($_POST['edit'])) {
                $status = ($act === 'nueva') ? $tsMedal->adNewMedal() : $tsMedal->editMedal();
                //$param = ($act === 'editar') ? "act=editar&mid={$_GET['mid']}&" : "";
                if ($status == 1) {
                    $tsAdmin->redirect();
                } else {
                    $smarty->assign("tsError", $status);
                }
                $smarty->assign("tsMed", [
                    'm_title' => $_POST['med_title'],
                    'm_description' => $_POST['med_desc'],
                    'm_image' => $_POST['med_img'],
                    'm_cant' => $_POST['med_cant'],
                    'm_type' => $_POST['med_type'],
                    'm_cond_user' => $_POST['med_cond_user'],
                    'm_cond_user_rango' => $_POST['med_cond_user_rango'],
                    'm_cond_post' => $_POST['med_cond_post'],
                    'm_cond_foto' => $_POST['med_cond_foto']
                ]);
            } else {
               //DATOS DE LA MEDALLA
                if ($act === 'editar') {
                    $smarty->assign("tsMed", $tsMedal->adGetMedal());
                }
            }
            //ICONOS PARA LAS MEDALLAS
            $smarty->assign("tsIcons", $AdminService->getExtraIcons('medallas'));
            //RANGOS DISPONIBLES
            $smarty->assign("tsRangos", $tsAdmin->getAllRangos());
        } elseif ($act === 'showassign') {
            $tsTitle = 'Mostrar las asignaciones';
            $smarty->assign("tsAsignaciones", $tsMedal->adGetAssign());
        }

     // Afiliados
    } elseif ($action === 'afs') {
      // CLASS
        require_once TS_MODELS . "c.afiliado.php";
      // QUE HACER
        if (empty($act)) {
            $smarty->assign("tsAfiliados", $tsAfiliado->getAfiliados('admin'));
        } elseif ($act === 'editar') {
            if ($_POST['edit']) {
                $aid = (int)$_GET['aid'];
                if ($tsAfiliado->editarAfiliado()) {
                    $tsCore->redireccionar('admin', $action, "act=editar&aid=$aid&save=true");
                }
            }
            $smarty->assign("tsAf", $tsAfiliado->getAfiliado('admin'));
        }

   // Categorías
    } elseif ($action === 'cats') {
        $tsTitle = 'Todas las categor&iacute;as';
        $smarty->assign('tsCats', $tsAdmin->getCats());
        if (!empty($_GET['ordenar'])) {
            $tsAdmin->saveOrden();
        } elseif (in_array($act, ['editar', 'nueva'])) {
            $tsTitle = ucfirst($act) . ' categor&iacute;a';
            if ($_POST['save']) {
                $both = ($act === 'editar') ? $tsAdmin->saveCat() : $tsAdmin->newCat();
                if ($both) {
                    $tsAdmin->redirect();
                }
            } else {
                $smarty->assign("tsType", $_GET['t']);
                if ($act === 'editar') {
                    $smarty->assign("tsCat", $tsAdmin->getCat());
                }
                if ($act === 'nueva') {
                    $smarty->assign("tsCID", $_GET['cid']);
                }
                // SOLO LAS CATEGORIAS TIENEN ICONOS
                $smarty->assign("tsIcons", $AdminService->getExtraIcons());
                require_once TS_MODELS . "c.foro.php";
                $smarty->assign('tsForos', $tsForo->getForos());
            }
        } elseif ($act === 'change') {
            $tsTitle = 'Cambiar categor&iacute;a';
            if ($_POST['save']) {
                if ($tsAdmin->MoveCat()) {
                    $tsAdmin->redirect();
                }
            }
        } elseif ($act === 'borrar') {
            $tsTitle = 'Borrar categor&iacute;a';
            if ($_POST['save']) {
                // BORRAR CATEGORIA
                if ($_GET['t'] === 'cat') {
                    $save = $tsAdmin->delCat();
                    if ($save == 1) {
                        $tsAdmin->redirect();
                    } else {
                        $smarty->assign("tsError", $save);
                    }
                }
            }
            //
            $smarty->assign("tsType", $_GET['t']);
            $smarty->assign("tsCID", $_GET['cid']);
            $smarty->assign("tsSID", $_GET['sid']);
        }

    // Rangos
    } elseif ($action === 'rangos') {
        $tsTitle = 'Todos los Rangos';
        // PORTADA
        if (empty($act)) {
            $smarty->assign("tsRangos", $tsAdmin->getRangos());
        }
        // LISTAR USUARIOS DEPENDIENDO EL RANGO
        elseif ($act === 'list') {
            $smarty->assign("tsMembers", $tsAdmin->getRangoUsers());
        // EDITAR RANGO
        } elseif (in_array($act, ['editar', 'nuevo'])) {
            $tsTitle = ucfirst($act) . " rango";
            if (!empty($_POST['save'])) {
                $execFunction = ($act === 'editar') ? $tsAdmin->saveRango() : $tsAdmin->newRango();
                if ($execFunction) {
                    $tsAdmin->redirect();
                }
            } else {
                if ($act === 'editar') {
                    $smarty->assign("tsRango", $tsAdmin->getRango());
                }
                if ($act === 'nuevo') {
                    $smarty->assign("tsError", $save);
                }
                $smarty->assign("tsType", $_GET['t']);
                $smarty->assign("tsIcons", $AdminService->getExtraIcons('rangos'));
                $smarty->assign('tsColor', $tsAdmin->rangoColor());
                require_once TS_ADMIN . '/admin-rangos-options.php';
                $smarty->assign('tsOptions', $options);
            }
        // NUEVO RANGO
        } elseif ($act === 'borrar') {
            $tsTitle = ucfirst($act) . " rango";
            if (empty($_POST['save'])) {
                $smarty->assign("tsRangos", $tsAdmin->getAllRangos());
            } else {
                if ($tsAdmin->delRango()) {
                    $tsAdmin->redirect();
                }
            }
        // CAMBIAR RANGO PREDETERMINADO DEL REGISTRO
        } elseif ($act === 'setdefault') {
            if ($tsAdmin->SetDefaultRango()) {
                $tsAdmin->redirect();
            }
        }
    }

/**********************************\

* (AGREGAR DATOS GENERADOS | SMARTY) *

\*********************************/
    // ACCION?
    $smarty->assign("tsAction", $action);
    //
    $smarty->assign("tsAct", $act);
    //
}



if (empty($tsAjax)) {    // SI LA PETICION SE HIZO POR AJAX DETENER EL SCRIPT Y NO MOSTRAR PLANTILLA, SI NO ENTONCES MOSTRARLA.
    $smarty->assign("tsTitle", $tsTitle);    // AGREGAR EL TITULO DE LA PAGINA ACTUAL

    $smarty->assign("tsSave", $_GET['save'] ?? false);  // AGREGAR EL TITULO DE LA PAGINA ACTUAL

    /*++++++++ = ++++++++*/
    include TS_ADMIN . '/footer.php';
    /*++++++++ = ++++++++*/
}
