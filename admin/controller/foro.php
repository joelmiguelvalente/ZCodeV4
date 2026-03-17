<?php

use Admin\models\Foro;

$Container->set(Foro::class, Foro::class);
$tsForo = $Container->get(Foro::class);

if (empty($act)) {
    $tsTitle = 'Gestionar Foro';
    $smarty->assign('tsForos', $tsForo->getForos());

# Editar | crear nueva categoría
} elseif (in_array($act, ['editar', 'nueva'])) {
    // SOLO LAS CATEGORIAS TIENEN ICONOS
    $smarty->assign("tsIcons", $AdminService->getExtraIcons());
    $tsTitle = ucfirst($act) . ' categoría';
    if (isset($_POST['super_nombre'])) {
        $status = ($act === 'nueva') ? $tsForo->newCategoria() : $tsForo->saveCategoria();
        if ($status == 1) {
            $tsCore->redireccionar('admin', $action, 'save=true');
        }
    } else {
        $smarty->assign('tsForo', $tsForo->getForo());
    }
}
