<?php 
use Install\src\utils\Helpers;
?>

<form method="POST" class="px-4">
	<h2 class="mb-3">Licencia de uso</h2>
	<p class="text-secondary mb-4">Para utilizar <strong><?= Helpers::version('full') ?></strong> debes aceptar los términos y condiciones de nuestra licencia.</p>

   <div class="border rounded" style="max-height: 350px; overflow-y: auto;">
      <pre class="m-0" style="white-space: pre-wrap; word-wrap: break-word;"><?= $license ?></pre>
   </div>

   <label class="form-check form-switch my-3">
      <input class="form-check-input" type="checkbox" name="accept_license" value="1" />
      <span class="form-check-label">Acepto los términos de la licencia</span>
   </label>

   <div class="text-center my-4">
      <button class="btn btn-dark" type="submit" disabled>Aceptar y Continuar</button>
   </div>
</form>

<script>
    const agree = document.querySelector('input[name="accept_license"]');
    const submit = document.querySelector('button[type="submit"]');
    agree.addEventListener('change', () => {
        submit.disabled = !agree.checked;
    });
</script>
