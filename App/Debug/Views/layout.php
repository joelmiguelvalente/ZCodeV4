<?php

/**
 * Prepara un mapa de viewers sin duplicar divs
 */

$viewers = [];
foreach ($errors as $k => $err) {
    // ID único real, imposible que repita
    $id = 'err_' . md5($err['file'] . $err['line'] . $k);
    $errors[$k]['id'] = $id;

    // Pre-render del highlight
    $viewers[$id] = highlight_file_segment($err['file'], $err['line']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>ZCode Debugger</title>
<style>
*, *::after, *::before {
    padding: 0;
    margin: 0;
    box-sizing: border-box;
}
body {
    font: normal normal 400 1rem/1.5rem Arial, sans-serif;
    background: #f4f4f4;
}
header {
    background: #20232a;
    color: #fff;
    padding: 20px;
}
header h1 {
    margin: 0;
    font-size: 22px;
}
main {
    display: grid;
    grid-template-columns: 420px 1fr;
    gap: .5rem;
    padding: 20px;
}
#errors {
    padding-right: 15px;
    overflow-y: auto;
    max-height: calc(100vh - 80px);
}
.error-box {
    border-radius: 8px;
    padding: 18px;
    margin-bottom: 18px;
    color: #000;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.error-title {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: bold;
}
.error-meta {
    margin-top: 10px;
    font-size: 13px;
    color: #333;
}
#editor {
    padding-left: 15px;
}
#viewer {
    margin-top: 15px;
}
.code-block {
    background: #1e1e1e;
    color: #fff;
    padding: 15px;
    overflow: auto;
    border-radius: 6px;
    font-family: Consolas, monospace;
    font-size: 14px;
}
.code-block .ln {
    color: #999;
    width: 40px;
    display: inline-block;
    user-select: none;
}
.error-line {
    background: #ff5722;
    color: #fff;
}
</style>
</head>

<body>
<header>
    <h1>ZCode Debugger</h1>
</header>

<main>
    <!-- Lista de errores -->
    <div id="errors">
        <?php foreach ($errors as $err) : ?>
            <div class="error-box" data-id="<?= $err['id'] ?>" style="background: <?= htmlspecialchars($err['color']) ?>">
                <p class="error-title"><?= htmlspecialchars($err['title']) ?></p>
                <p><?= htmlspecialchars($err['message']) ?></p>
                <p class="error-meta">
                    Archivo: <?= htmlspecialchars($err['file']) ?><br>
                    Línea: <?= $err['line'] ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Viewer único -->
    <div id="editor">
        <h2>Visualizador de código</h2>
        <div id="viewer" class="code-block">
            Seleccione un error para ver el código...
        </div>
    </div>
</main>

<script>
// Los viewers generados por PHP
const viewers = <?= json_encode($viewers) ?>;

document.addEventListener("DOMContentLoaded", () => {
    const errorsList = document.getElementById("errors");
    const viewer = document.getElementById("viewer");

    errorsList.addEventListener("click", (e) => {
        const box = e.target.closest(".error-box");
        if (!box) return;

        const id = box.dataset.id;
        viewer.innerHTML = viewers[id] || "No se pudo cargar el código.";
    });
});
</script>

</body>
</html>
<?php die; ?>
