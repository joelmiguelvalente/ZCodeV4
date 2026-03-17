import { $ } from './app/zcode.app.js';
import { empty } from './core/utils.js';

const { url } = ZCodeApp;
let redirectURI = $('#redirect_uri');
if(redirectURI.length > 0) {

	if(empty(redirectURI.val())) redirectURI.val(`${url}/discord.php`)

	$('#social_name').on('change', (e) => {
   	const replace = $(e.target).val();
   	redirectURI.val(`${url}/${replace}.php`);
	});

	$("#botonCopiar").on("click", () => {
	   redirectURI.select();
	   document.execCommand("copy");
	   window.getSelection().removeAllRanges();
	   redirectURI.parent().find('small').html("Redirect URL ha sido copiado correctamente!");
	   setTimeout(() => redirectURI.parent().find('small').html(''), 5000);
	});
}