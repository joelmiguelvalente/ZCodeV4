import { basePath, baseTitle, $ } from './app/zcode.app.js';
import { initApp } from './app/init.js';
import { number_format } from './core/utils.js';
import { loading } from './ui/loading.js';
import { importModule } from './core/loader.js';
import { notifica } from './core/notifica.js';
import { mensaje } from './core/mensaje.js';
import { UPModal } from './ui/modal.js';

/**
 * Solo cargará la función completa al ejecutarlo
*/
function bloquear(user, bloqueado, lugar, aceptar) {
	importModule('Bloquear.js', 'bloquear', { user, bloqueado, lugar, aceptar });
}

async function login_modal() {
	const req = await post('/login-form.php');
	UPModal.setModal({
		title: 'Bienvenidos a ' + baseTitle,
		body: req,
		buttons: {
			confirmTxt: 'Iniciar sesión',
			confirmAction: `login.iniciarSesion()`,
			cancelShow: true
		}
	});
}

/**
 * Detecta si el navegador soporta Nesting CSS según su versión
 * y muestra una alerta si no lo hace.
 */
function checkNestingCompatibility() {
	const ua = navigator.userAgent;
	let browser = 'Unknown';
	let version = 0;

	if (ua.includes('Chrome') && !ua.includes('Edg')) {
		browser = 'Chrome';
		version = parseInt(ua.match(/Chrome\/(\d+)/)?.[1] || 0);
	} else if (ua.includes('Firefox')) {
		browser = 'Firefox';
		version = parseInt(ua.match(/Firefox\/(\d+)/)?.[1] || 0);
	} else if (ua.includes('Safari') && !ua.includes('Chrome')) {
		browser = 'Safari';
		version = parseInt(ua.match(/Version\/(\d+)/)?.[1] || 0);
	} else if (ua.includes('Edg')) {
		browser = 'Edge';
		version = parseInt(ua.match(/Edg\/(\d+)/)?.[1] || 0);
	}

	let requiredVersion = 0;

	switch (browser) {
		case 'Chrome':
		case 'Edge': // Edge sigue el mismo motor que Chrome (Chromium)
			requiredVersion = 120;
			break;
		case 'Firefox':
			requiredVersion = 117;
			break;
		case 'Safari':
			requiredVersion = 17;
			break;
		default:
			requiredVersion = 999;
			break;
	}
	if (version > 0 && version < requiredVersion) {
		const message = `🚨 ¡Atención! Su navegador (${browser} ${version}) podría no soportar completamente el nuevo diseño.<br>Para una experiencia óptima y la correcta visualización del sitio, le recomendamos actualizar a:<br>- Chrome / Edge ${requiredVersion} o superior<br>- Firefox ${requiredVersion} o superior<br>- Safari ${requiredVersion} o superior<br>Su versión actual puede causar fallos visuales.`;
		// Usamos su UPModal existente para dar un mensaje formal y no intrusivo
		if (typeof UPModal !== 'undefined' && UPModal.alert) {
			UPModal.alert({ title: 'Navegador Desactualizado', body: message });
		} else {
			console.warn(message);
		}
	}
}

// READY
$(() => {
	checkNestingCompatibility();

	$('#btnNotifica')?.on('click', (e) => {
		e.preventDefault()
		notifica.last()
	})

	$('#btnMensaje')?.on('click', (e) => {
		e.preventDefault()
		mensaje.last()
	})

	$('#up-collapse')?.on('click', (e) => {
		$('.up-collapse').toggleClass('show');
	})

	const $BrandDay = $('#brandday');
	const $StickyMSG = $('#stickymsg');

	if ($StickyMSG.length && $BrandDay.length) {
		$StickyMSG.addEventListener('mouseover', () => $BrandDay.style.opacity = .5);
		$StickyMSG.addEventListener('mouseout', () => $BrandDay.style.opacity = 1);
		$StickyMSG.addEventListener('click', () => location.href = `${basePath}/moderacion/`);
	}

	$('.drop-select--toggle').each(toggle => {
		toggle.addEventListener('click', e => {
			// Encontramos el menú nativo
			const menu = toggle.parentNode.querySelector('.drop-select--menu');
			// Iteramos sobre todos los menús. Usamos el $() para seleccionar,
			// y luego .each() para iterar sobre la colección.
			$('.drop-select--menu').each(m => {
				// 'm' es un elemento DOM nativo
				if (m !== menu) hide(m);
			});
			// Toggle del menú actual
			if (menu.style.display === 'block') hide(menu);
			else show(menu);
		});
	});

	// Select dropdown item
	$('.drop-select').each(drop => {
		drop.addEventListener('click', e => {
			const item = e.target.closest('.drop-select--item');
			if (!item) return;

			const text = item.querySelector('span').textContent;
			const value = item.dataset.value;

			const toggle = drop.querySelector('.drop-select--toggle');
			const input = drop.querySelector('input[type="hidden"]');
			const menu = drop.querySelector('.drop-select--menu');

			toggle.textContent = text;
			input.value = value;
			hide(el => el.style.display = 'none');
		});
	});

	// Close dropdown if clicked outside
	document.addEventListener('click', e => {
		if (!e.target.closest('.drop-select')) {
			$('.drop-select--menu').each(el => el.style.display = 'none');
		}
	});
	//
	const displayDropdown = [
		{ id: '.up-droplist[data-list="nots"]', attrName: 'Monitor', callFunction: notifica.last },
		{ id: '.up-droplist[data-list="mps"]', attrName: 'Mensajes', callFunction: mensaje.last }
	];

	document.body.addEventListener('click', e => {
		displayDropdown.forEach(({ id, attrName, callFunction }) => {
			const el = $(id);
			if (el.isVisible() && !e.target.closest(id) && !e.target.closest(`a[name="${attrName}"]`)) {
				callFunction();
			}
		});
	});

	initApp()
});
