<form method="POST" class="px-4" id="smtp-form">

   <h2 class="mb-3">Configuración de PHPMailer</h2>

   <p class="text-secondary mb-4">Este paso es opcional. Si deseas que el sistema pueda enviar correos (notificaciones, recuperación de contraseña, etc.), completá la configuración SMTP.</p>

   <div class="mb-3">
        <label for="smtp_host" class="form-label">SMTP Host</label>
        <input type="text" class="form-control" id="smtp_host" name="smtphost" placeholder="smtp.gmail.com" value="<?= $default['smtphost'] ?>">
   </div>

   <div class="mb-3">
        <label for="smtp_user" class="form-label">SMTP Usuario</label>
        <input type="email" class="form-control" id="smtp_user" name="smtpuser" placeholder="tu-correo@gmail.com" value="<?= $default['smtpuser'] ?>">
   </div>

   <div class="mb-3">
        <label for="smtp_pass" class="form-label">SMTP Contraseña</label>
        <input type="password" class="form-control" id="smtp_pass" name="smtppass" placeholder="••••••••" value="<?= $default['smtppass'] ?>">
   </div>

   <div class="mb-3">
        <label for="smtp_name" class="form-label">Nombre remitente</label>
        <input type="text" class="form-control" id="smtp_name" name="smtpname" placeholder="ZCode Notificaciones" value="<?= $default['smtpname'] ?>">
   </div>

   <div class="mb-3">
        <label for="smtp_port" class="form-label">Puerto SMTP</label>
        <input type="number" class="form-control" id="smtp_port" name="smtpport" placeholder="587" value="<?= $default['smtpport'] ?>">
        <small class="form-hint">Uso recomendado: 587 (TLS) o 465 (SSL)</small>
   </div>

   <h3 class="mt-4 mb-2 fs-5">Ayuda para Gmail</h3>
   <p class="text-secondary mb-3">Si usás Gmail, necesitás crear una contraseña de aplicación:<br><a href="https://myaccount.google.com/apppasswords" target="_blank">myaccount.google.com/apppasswords</a><br>Seleccioná "Mail" como aplicación y "Otro" como dispositivo.</p>

   <div class="d-flex gap-2 my-4">
        <button type="submit" name="next_smtp" value="1" class="btn btn-dark w-50">Continuar</button>
        <button type="submit" name="skip_smtp" value="1" class="btn btn-outline-secondary w-50">Omitir paso</button>
   </div>

</form>
