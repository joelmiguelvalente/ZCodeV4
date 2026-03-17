<?php 
use Install\src\utils\Helpers;
?>

<form method="POST" id="db-form" class="px-4">
   <h2 class="mb-3">Configuración de la Base de Datos</h2>

   <p class="text-secondary mb-4">Ingresá los datos de conexión a tu servidor MySQL. Estos valores serán utilizados para generar la configuración del sistema.</p>

   <div class="mb-3">
      <label for="dbhost" class="form-label">Servidor</label>
      <input type="text" class="form-control" name="hostname" id="dbhost" value="<?= $default['hostname'] ?>" placeholder="localhost" required>
   </div>

   <div class="mb-3">
      <label for="dbuser" class="form-label">Usuario</label>
      <input type="text" class="form-control" name="username" id="dbuser" value="<?= $default['username'] ?>" placeholder="root" required>
   </div>

   <div class="mb-3">
      <label for="dbpass" class="form-label">Contraseña</label>
      <input type="password" class="form-control" name="password" id="dbpass" value="<?= $default['password'] ?>" placeholder="••••••••"<?= Helpers::isLocalhost() ? '' : ' required'?>>
   </div>

   <div class="mb-3">
      <label for="dbname" class="form-label">Nombre de la base</label>
      <input type="text" class="form-control" name="database" id="dbname" value="<?= $default['database'] ?>" placeholder="zcode" required>
   </div>

   <div class="mb-3">
      <label for="dbprefix" class="form-label">Prefijo de tablas <span class="text-secondary">(opcional)</span></label>
      <input type="text" class="form-control" name="prefix" id="dbprefix" value="<?= $default['prefix'] ?>" placeholder="zc_">
   </div>

   <div class="text-center my-4">
      <button type="submit" class="btn btn-dark">Continuar</button>
   </div>

</form>