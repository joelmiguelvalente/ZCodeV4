import { $ } from './app/zcode.app.js';
import { loading } from './ui/loading.js';
import { timeAgo } from './timeago.min.js';
const joypixels = window.joypixels;

// FEED SUPPORT
$(() => {
	$.getJSON("/feed-support.php", response => {
		$('#ulitmas_noticias').html('<div class="empty">Obteniendo información...</div>');
		if(Array.isArray(response)) {
			$('#ulitmas_noticias').html('');
			response.map( data => {
				const { link, title, info, version } = data;
				let html = `<a href="${link}" target="_blank" class="feed:item">
				   <div class="feed:header">
				   	<h5>${title}</h5>
				   	<small class="feed:version">${version}</small>
				   </div>
				   <span class="feed:info">${info}</span>
				</a>`;
				$('#ulitmas_noticias').append(html);
			})
		} else $('#ulitmas_noticias').html(`<div class="empty">${response}</div>`)
	});

	//
	$.getJSON("/feed-version.php", response => {
		const { version, status, color } = response;
		// Clonamos
	  	let clonar = $('.list-clone').first().clone();
	  	// Añadimos color
	  	clonar.addClass(color)
	  	// Modificar los datos dentro del clon
	  	clonar.find('.fw-bold').text(version);
	  	clonar.find('.text-body-secondary').text(status);
	  	// Agregar el clon a la lista
		if(typeof version === 'undefined') {
			clonar.addClass('list-clone-danger')
			clonar.find('.fw-bold').text('No version');
	  		clonar.find('.text-body-secondary').text(response);
		}
	  	$('#ultima_version').append(clonar);
	});

	function changeBranch(branch = 'v3-dev') {
		$.getJSON('/github-api.php', { branch }, response => {
			if(response === null || response.state === 0) {
				$('#lastCommit').html('<div class="empty">No se puede cargar el último commit...</div>');
				return;
			}
			const { sha,  html_url, message, author, date, verified, reason } = response.data;
			//
			$('#lastCommit').html('');
			// Creamos la plantilla para mostrar la infomación del mismo
			// Reemplazamos \n por saltos de línea con <br>
			let messageAlter = message.replace(/\n/g, '-');
			let contentNew = '';
			messageAlter.split('--').map( (msg, position) => {
				let bold = (position === 0) ? ' fw-semibold fs-5' : '';
				let verifiedCommit = (verified) ? 'verified' : reason;
				let AddVerified = (position === 0) ? `<small class="badge main-bg position-absolute small" style="top:1.125rem;right:.5rem;">${verifiedCommit}</small>` : '';
				contentNew += `<span class="d-block mb-2${bold}">${msg}${AddVerified}</span>`;
			});
		
			let toImageTemplate = `<div class="data-github py-3 position-relative">${contentNew}</div>
			<div class="d-flex justify-content-between align-items-center px-2 py-1 border-top translucent-bg">
				<span>Sha: <a href="${html_url}" class="text-decoration-none text-primary" rel="noreferrer" target="_blank">${sha.substring(0, 8)}...</a></span>
				<time class="fst-italic small">${timeAgo(date)}</time>
			</div>`;

			// La añadimos al HTML
			$('#lastCommit').append(joypixels.toImage(toImageTemplate));
		})
	}
	// Autoejecutamos
	changeBranch();

});