<header>
	<nav class="up-navbar d-flex justify-content-between align-items-center body-bg z-99 w-100 px-3">

		<div class="up-navbar-menu d-flex justify-content-start align-items-center" data-menu="main">

			<div class="up-branding me-2 d-flex justify-content-start align-items-center">
				<div class="up-menu-toggle d-flex justify-content-center align-items-center" aria-labelledby="toggle-menu" role="button" onclick="$('.up-sidebar').toggleClass('show');">
					{uicon name="menu-hamburger" class="pe-none" size="1.75rem"}
				</div>
				<div class="up-brand-logo">
					<a href="{$tsConfig.url}/" rel="interal" title="{$tsConfig.titulo}" class="text-uppercase text-decoration-none fw-bolder py-1 px-2 d-block fs-4">{$tsConfig.titulo}</a>
				</div>
			</div>

			<div class="up-menu--item mb-lg-0">
				<a title="Ir a Moderacion" class="up-menu--link text-decoration-none rounded position-relative py-2 py-lg-0 px-3 px-lg-2 d-flex justify-content-start justify-content-lg-center align-items-center column-gap-2{if $tsPage == 'moderacion'} active{/if}" rel="internal" href="{$tsConfig.url}/{if $tsPage == 'moderacion'}admin{else}moderacion{/if}/">
					{uicon name="hierarchy" size="1.5rem"}
					<span class="item--text">{if $tsPage == 'moderacion'}Administ{else}Mode{/if}ración</span>
				</a>
			</div>
			
		</div>
		
		<div class="up-navbar-menu d-flex justify-content-end align-items-center" data-menu="secondary">
			{if $tsUser->is_member}
				{include "head_menu_user.tpl"}
			{else}
				<div class="up-menu--item">
					<a title="Identificarme!" class="up-menu--link text-decoration-none rounded position-relative py-2 py-lg-0 px-3 px-lg-2 up-menu--login d-flex justify-content-center align-items-center" rel="internal" href="javascript:login_modal()">
						{uicon name="door-alt" size="1.5rem"}
						<span class="item--text">Iniciar sesión</span>
					</a>
				</div>
			{/if}
		</div>

	</nav>
</header>
