<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
*/

declare(strict_types=1);

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use Install\src\utils\Helpers;

$show = Helpers::getCurrentStep() !== 'bienvenida';

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="./../assets/images/favicon/logo-32.webp" rel="shortcut icon" type="image/webp" sizes="32x32" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core/dist/css/tabler.min.css" />
<script src="https://cdn.jsdelivr.net/npm/@tabler/core" defer></script>
<title><?= Helpers::setTitle() ?></title>
</head>
<body>
    <main class="page page-center">
        <div class="container container-narrow<?= $show ? ' py-3' : '' ?>">
            <section class="shadow bg-white rounded border">
                <?php if ($show) : ?>
                    <div class="p-3 border-bottom mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="display-6"><?= ucfirst(Helpers::getCurrentStep()) ?></strong>
                            <em class="badge badge-dark"><?= Helpers::version('full') ?></em>
                        </div>
                    </div>
                    <?php
                endif;
                if (!empty($message)) : ?>
                    <div class="mx-5 alert alert-important alert-danger alert-dismissible" role="alert">
                        <div><?= $message ?></div>
                    </div>
                <?php endif;
                    require_once Helpers::view();
                ?>
            </section>
            <?php if ($show) : ?>
                <footer class="text-center py-3">
                    <p class="p-0 m-0">Copyright &copy; 2024 - <?= date('Y') ?></p>
                    <p class="p-0 m-0 fw-bold"><?= Helpers::version('full') ?></p>
                </footer>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
