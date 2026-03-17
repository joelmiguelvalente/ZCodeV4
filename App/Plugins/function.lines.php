<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
 */

declare(strict_types=1);

use App\Plugins\Services\FrontendPayloadBuilder;

function smarty_function_lines($params, Smarty\Template $template)
{
    $scope = $template->getTemplateVars() ?? [];
    $builder = new FrontendPayloadBuilder($scope);
    $params['vars'] = $params['vars'] ?? [];

    if ($params['vars'] === 'global') {
        $data = $builder->addUser()->addPost()->addFoto()->addConfig()->addPage()->addRoutes()->get();
    }

    // $params['data'] equivale a lo que antes cargabas desde datos.php
    $dataParam = (string) ($params['data'] ?? '');

    // Genera el <script>...</script> con ZCodeApp y posible avatar script
    $script = ($params['vars'] !== 'aditional') ? $builder->toJavascript($dataParam) : $builder->setScriptInLine();
    return $script;
}
