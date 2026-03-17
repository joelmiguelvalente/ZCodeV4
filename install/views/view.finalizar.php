<?php 
use Install\src\utils\Helpers;
?>

<form method="POST" class="px-4 text-center">

   <div class="py-5">
      <p class="fade-in mb-4">Gracias por instalar <strong><?= Helpers::version('name') ?></strong>. Tu nueva comunidad <strong>Link Sharing System</strong> está completamente configurada y lista para comenzar a funcionar. <br><br>Inicia sesión con tus datos para acceder, explorar las herramientas disponibles y personalizar la plataforma según tus necesidades. A partir de aquí ya podés gestionar usuarios, posts y todas las funciones incluidas para potenciar tu proyecto.</p>

  		<div class="text-center my-4">
  		   <button type="submit" class="btn btn-dark">Ir al Sitio</button>
  		</div>

      <div class="hr-text">
			<span>En discord</span>
		</div>
		<p class="text-muted mb-3">Te invito a unirte al servidor de <a href="https://discord.gg/StWZtrt2DE" rel="external" target="_blank">Discord</a>, donde comparto novedades y comunicados sobre futuras actualizaciones del proyecto.</p>
		<div class="hr-text">
			<span>En Github</span>
		</div>
		<p class="text-muted mb-3">Te invito a visitar el repositorio en <a href="https://github.com/joelmiguelvalente/ZCodeV4" rel="external" target="_blank">GitHub</a>. Allí podrás revisar el estado del desarrollo, reportar errores mediante <a href="https://github.com/joelmiguelvalente/ZCodeV4/issues" rel="external" target="_blank">Issues</a> y mantenerte informado sobre nuevas actualizaciones.</p>

      <hr class="my-4">

      <div class="alert alert-warning text-start mx-auto d-block" role="alert" style="max-width: 600px;">
         <strong class="d-block">Recomendación de seguridad:</strong>
         <span>Elimina o renombra la carpeta <code>./<?= basename(dirname(__DIR__, 1)) ?></code> para evitar que otra persona intente reinstalar el sistema.</span>
      </div>
   </div>

</form>