<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

use App\Callback\Callback;

$callback = new Callback();

$callback->social = 'github';

$data = $callback->cURLToken();

$userData = $callback->cURLUser($data);

$user = $callback->getDataInfoUser($userData);

$callback->OAuthComplete($user);
