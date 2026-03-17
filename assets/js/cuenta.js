import { basePath, $ } from './app/zcode.app.js';
import { importModule } from './core/loader.js';
import { loading } from './ui/loading.js';
import { UPModal } from './ui/modal.js';
import { notify } from './ui/notify.js';

function desactivate(start = 0) {
	importModule('DesactivarCuenta.js', 'desactivate', { step: start });
}

function generarContrasena() {
	importModule('Contrasena.js');
}

const cuenta = {
	/**
	 * Carga las provincias/estados según el país seleccionado
	 */
	paisProvincias() {
		const paisCode = $('select[name="pais"]').val();
		const $estado  = $('select[name="estado"]');
	 	if (!paisCode) {
			$estado.addClass('disabled').prop('disabled', true).val('');
			return;
	 	}
	 	// Limpio resultados previos
		$estado.empty();
		loading.start();
		$.get(`/registro-geo.php`, { pais_code: paisCode }, response => {
			const status  = parseInt(response.charAt(0), 10);
			const content = response.substring(3);
			if (status === 1) {
				$estado.append(content).prop('disabled', false).removeClass('disabled').val('').focus();
			}
			loading.end();
		});
	},
	/**
	 * POST reutilizable con toast automático
	 * @param {string} page - archivo sin el prefijo "cuenta-" ni extensión
	 * @param {object|string} params - datos para enviar
	 * @param {function|null} callback - función a ejecutar si es exitoso
	 */
	notifyRequest(page, params, callback = null) {
		$.post(`/cuenta-${page}.php`, params, response => {
			const type    = parseInt(response.charAt(0), 10);
			const message = response.substring(3);
			notify.start({
				content: message,
				type: type === 0 ? 'warning' : 'success'
			});
			loading.end();
			if (type === 1 && typeof callback === 'function') {
				callback();
			}
		});
	},

	/**
	 * Cambia avatar (web o social)
	 */
	avatar(name) {
		loading.start();
		const active = (name === 'web') ? 0 : 1;
		this.notifyRequest('avatar-social', { name, active }, () => setTimeout(() => location.reload(), 1000));
	},

	/**
	 * Guarda los datos del perfil
	 */
	guardarDatos() {
		loading.start();
		const formData = $('form[name="editarcuenta"]').serialize();
		this.notifyRequest('guardar', formData);
	},

	/**
	 * Define el tiempo para eliminación de cuenta
	 */
	eliminarCuenta(element) {
		const outtimeType = parseInt($(element).val(), 10);
		this.notifyRequest('eliminar-tiempo', { outtime_type: outtimeType }, response => {
			const type    = parseInt(response.charAt(0), 10);
			const message = response.substring(3);
			notify.start({
				content: message,
				type: type === 0 ? 'warning' : 'success'
			});
		});
	}
};

// desvincular(social)
function unlinkSocialAccount(social) {
	if (!social) return;
	$.post(`/cuenta-desvincular.php`, { social }, response => {
		if (!response) return;
		UPModal.setModal({
			title: 'Bien',
			body: 'Ha sido desvinculado correctamente.',
			buttons: {
				confirmTxt: 'Listo',
				confirmAction: 'location.reload()'
			}
		});
	});
}


function loadModulesByPage(page) {
	if (page === 'avatar') {
		importModule('Avatar.js', ['updateAvatarGif', 'changeAvatar', 'deleteAvatar']);
	}

	if (page === 'apariencia') {
		importModule('Apariencia.js', [
			'bindSystemThemeToggle',
			'bindPageBoxToggle',
			'bindAccentColorChange',
			'bindFontSettingsChange'
		]);
		if (!$('.customizar_tema').hasClass('d-none')) {
			importModule('Customizar.js');
		}
	}

	if (page === 'seguridad') {
		importModule('Seguridad.js');
		if ($('.remove_2fa').length > 0) {
			importModule('Authenticator.js', 'removeTwoFactorAuth');
		}
		if ($('.regenerate_token').length > 0) {
			$('.regenerate_token').on('click', () => 
				importModule('Authenticator.js', 'regenerateRecoveryTokens')
			);
		}
	}
}

function bindAvatarEvents() {
	$('.avatar-big-cont').on('click', () => $('input.browse[name="desktop"]').click());
	$('input.browse[name="desktop"]').on('change', function () {
		if (!this.files || !this.files.length) return;
		const { name } = this.files[0];
		$(this).next('.upform-file-text').html(name);
		$('#message_image').html('');
		avatar.subir(this.name);
		$('.avatar-loading').show();
	});
	if ($('.verify').length > 0) {
		$('.verify').on('click', () => {
			$('#message_image').html('');
			avatar.subir('url');
			$('.avatar-loading').show();
		});
	}
}


$(() => {
	const page = $('input[name="pagina"]').val();

	loadModulesByPage(page);
	bindAvatarEvents();
});