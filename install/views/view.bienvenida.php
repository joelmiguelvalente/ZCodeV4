<?php 
use Install\src\utils\Helpers;
?>
<form method="POST" class="px-4 pt-3">
	<div class="text-center mb-4">
   	<img src="./../assets/images/favicon/logo-128.webp" alt="ZCode" height="80" class="mb-3 rounded shadow">
   	<h1 class="h2">Centro de instalación de: <?= Helpers::version('full') ?>!</h1>
   </div>
	<p>Este proceso te permitirá dejar tu entorno completamente configurado antes del primer uso. La instalación es guiada y toma solo unos minutos.</p>
	<p>Durante el recorrido podrás:</p>
	<ol class="list-unstyled space-y-3">
		<li class="d-flex align-items-center">
         <span class="me-3 text-primary">
           	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-database fs-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6m-8 0a8 3 0 1 0 16 0a8 3 0 1 0 -16 0" /><path d="M4 6v6a8 3 0 0 0 16 0v-6" /><path d="M4 12v6a8 3 0 0 0 16 0v-6" /></svg>
         </span>
         <span>Configurar base de datos</span>
      </li>
      <li class="d-flex align-items-center">
         <span class="me-3 text-primary">
           	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-shield-check fs-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.46 20.846a12 12 0 0 1 -7.96 -14.846a12 12 0 0 0 8.5 -3a12 12 0 0 0 8.5 3a12 12 0 0 1 -.09 7.06" /><path d="M15 19l2 2l4 -4" /></svg>
         </span>
         <span>Verificar permisos de escritura</span>
      </li>
      <li class="d-flex align-items-center">
        	<span class="me-3 text-primary">
          	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-world fs-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M3.6 9h16.8" /><path d="M3.6 15h16.8" /><path d="M11.5 3a17 17 0 0 0 0 18" /><path d="M12.5 3a17 17 0 0 1 0 18" /></svg>
        	</span>
        	<span>Datos principales del sitio</span>
      </li>
      <li class="d-flex align-items-center">
        	<span class="me-3 text-primary">
          	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-mail-cog fs-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 19h-7a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v5" /><path d="M3 7l9 6l9 -6" /><path d="M19.001 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M19.001 15.5v1.5" /><path d="M19.001 21v1.5" /><path d="M22.032 17.25l-1.299 .75" /><path d="M17.27 20l-1.3 .75" /><path d="M15.97 17.25l1.3 .75" /><path d="M20.733 20l1.3 .75" /></svg>
        	</span>
        	<span>Configurar PHPMailer (opcional)</span>
      </li>
      <li class="d-flex align-items-center">
        	<span class="me-3 text-primary">
          	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user-shield fs-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 21v-2a4 4 0 0 1 4 -4h2" /><path d="M22 16c0 4 -2.5 6 -3.5 6s-3.5 -2 -3.5 -6c1 0 2.5 -.5 3.5 -1.5c1 1 2.5 1.5 3.5 1.5z" /><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /></svg>
        	</span>
        	<span>Crear cuenta del administrador</span>
      </li>
      <li class="d-flex align-items-center">
         <span class="me-3 text-primary">
           	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-check fs-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
         </span>
         <span>Finalizar instalación</span>
      </li>
	</ol>
	<p>Nuestro objetivo es que, al concluir este asistente, tu sistema quede listo para funcionar sin pasos adicionales.</p>
	<p class="fst-italic text-center d-block py-3">Cuando estés preparado, continúa con la instalación.</p>

	<div class="text-center my-4">
		<input type="submit" class="btn btn-primary" value="Iniciar instalación">
   </div>
</form>