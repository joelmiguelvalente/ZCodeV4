<?php 
use Install\src\utils\Helpers;
?>

<form method="POST" class="px-4">
   <p class="text-muted mb-4">Crea la cuenta del usuario administrador que tendrá acceso total al panel de control.</p>

   <div class="mb-3">
      <label for="nick" class="form-label">Nickname</label>
      <input type="text" id="nick" name="user_name" value="<?= $default['user_name'] ?>" class="form-control" required autocomplete="off">
      <div class="form-text">Nombre de usuario para iniciar sesión.</div>
   </div>

   <div class="mb-3">
      <label for="email" class="form-label">Email del Administrador</label>
      <input type="email" id="email" name="user_email" value="<?= $default['user_email'] ?>" class="form-control" required autocomplete="off">
      <div class="form-text">Se usará para recuperación de contraseña y alertas internas.</div>
   </div>

   <div class="mb-3">
      <label for="password" class="form-label">Contraseña</label>
      <input type="password" id="password" name="user_password" value="<?= $default['user_password'] ?>" class="form-control" required>
      <div class="form-text">Mínimo 8 caracteres. Usa mayúsculas, minúsculas y números.</div>
   </div>

   <div class="mb-3">
      <label for="password2" class="form-label">Confirmar Contraseña</label>
      <input type="password" id="password2" name="confirmar" value="<?= $default['confirmar'] ?>" class="form-control" required>
      <div class="form-text">Repite la contraseña para evitar errores.</div>
   </div>

   <div class="text-center py-3">
      <button class="btn btn-dark" type="submit">Continuar</button>
   </div>

</form>

<script>
    const p1 = document.querySelector('#password');
    const p2 = document.querySelector('#password2');

    function validate() {
        p2.classList.toggle('is-invalid', p1.value !== p2.value);
    }

    p1.addEventListener('input', validate);
    p2.addEventListener('input', validate);
</script>
