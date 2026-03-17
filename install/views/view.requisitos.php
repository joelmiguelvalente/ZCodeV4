<form method="POST" class="px-4">
    <div class="row">
        <div class="col">
            <h3 class="h4">Requisitos del sistema</h3>
            <div class="list-group">
                <?php foreach ($systemStatus as $name => $info) : ?>
                    <div class="list-group-item border-0 border-start border-4 border-<?= $info['class'] ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= $info['icon'] ?> <?= $info['text'] ?></strong>
                                <?php if ($info['current']) : ?>
                                    <div class="text-secondary small">Actual: <?= $info['current'] ?></div>
                                <?php endif; ?>
                            </div>
                            <span class="badge bg-<?= $info['class'] ?> text-white"><?= $info['subtext'] ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col">
            <h3 class="h4">Permisos de carpetas</h3>
            <div class="list-group">
                <?php foreach ($pathCheck as $folder => $info) : ?>
                    <div class="list-group-item border-0 border-start border-4 border-<?= $info['class'] ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= $info['icon'] ?> <?= ucfirst($folder) ?></strong>
                                <div class="small text-secondary"><?= $info['route'] ?></div>
                                <div class="small">Permisos actuales: <?= $info['chmod'] ?></div>

                                <?php if ($info['created']) : ?>
                                    <div class="small text-warning">Carpeta creada automáticamente</div>
                                <?php endif; ?>
                            </div>

                            <span class="badge bg-<?= $info['class'] ?> text-white"><?= $info['text'] ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="text-center py-3">
        <?php if ($allSystemOk && $allPathsOk) : ?>
            <input type="hidden" name="verificar" value="no">
            <button class="btn btn-dark" type="submit">Continuar la instalación</button>
        <?php else : ?>
            <input type="hidden" name="verificar" value="si">
            <button class="btn btn-dark" type="submit">Volver a verificar</button>
        <?php endif; ?>
    </div>
</form>
