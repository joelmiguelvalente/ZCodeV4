<?php 
use Install\src\utils\Helpers;
?>

<form method="POST" class="px-4">

   <h2 class="mb-3">Datos del Sitio</h2>

   <p class="text-secondary mb-4">Ingresá la información principal del sitio. Estos datos se usarán para generar la configuración inicial.</p>

   <div class="mb-3">
		<label for="site_title" class="form-label">Título del sitio</label>
		<input type="text" class="form-control" id="site_title" name="titulo" value="<?= $default['titulo'] ?>" placeholder="Mi Comunidad ZCode" required>
   </div>

   <div class="mb-3">
		<label for="site_slogan" class="form-label">Slogan</label>
		<input type="text" class="form-control" id="site_slogan" name="slogan" value="<?= $default['slogan'] ?>" placeholder="Un lugar para todos" required>
   </div>

   <div class="mb-3">
		<label for="site_url" class="form-label">URL del sitio</label>
		<div class="input-group">
		  	<span class="input-group-text"><?= Helpers::getScheme(true) ?></span>
		  	<input type="text" class="form-control" id="site_url" name="url" value="<?= $default['url'] ?? Helpers::getBaseUrl(false) ?>" placeholder="<?= Helpers::getBaseUrl(false) ?>" required>
		</div>
		<small class="form-hint">Incluye http:// o https://</small>
   </div>

   <div class="mb-3">
		<label for="site_email" class="form-label">Email de contacto</label>
		<input type="email" class="form-control" id="site_email" name="email" value="<?= $default['email'] ?>" placeholder="admin@misitio.com" required>
   </div>

   <h3 class="mt-4 mb-2 fs-5">Google reCAPTCHA (opcional)</h3>
   <p class="text-secondary mb-3">
		Completalo solo si querés habilitar protección antispam. Obten la clave desde <a href="https://www.google.com/recaptcha/admin" target="_blank"><strong>google.com/recaptcha/admin</strong></a>
   </p>

   <div class="mb-3">
		<label for="public" class="form-label">Public Key</label>
		<input type="text" class="form-control" id="public" name="pkey" value="<?= $default['pkey'] ?>" placeholder="6LfFFiMdAAAAAAQjDafWXZ0FeyesKYjVm4DSUoao">
   </div>

   <div class="mb-3">
		<label for="secret" class="form-label">Secret Key</label>
		<input type="text" class="form-control" id="secret" name="skey" value="<?= $default['skey'] ?>" placeholder="6LfFFiMdAAAAAFIP4oNFLQx5Fo1FyorTzNps8ChE">
   </div>

   <div class="text-center my-4">
      <button type="submit" class="btn btn-dark">Continuar</button>
   </div>

</form>