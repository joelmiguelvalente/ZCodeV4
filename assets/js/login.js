import { basePath, baseTitle, $ } from './app/zcode.app.js';
import { UPModal } from './ui/modal.js';
import { loading } from './ui/loading.js';

const login = (() => {
	'use strict';

	const API = ZCodeApp.url;

	const TYPE = {
		password: 'Recuperar Contraseña',
		validation: 'Reenviar validación'
	};

	// Helpers
	const val = selector => $(selector).val();
	const csrf = () => val('input[name=csrf_token]');

	const setButtonLoading = state => {
		$('.upform-buttons input[type="submit"]').val(state ? 'Iniciando sesión...' : 'Iniciar sesión');
	};

	const showError = (field, msg) => {
		const group = $(`#${field}`).closest('.upform-group');
		group.find('small.help').addClass('error').html(msg);
		$(`#${field}`).addClass('input-error').focus();
	};

	const cleanError = field => {
		const group = $(`#${field}`).closest('.upform-group');
		group.find('small.help').removeClass('error').html('');
		$(`#${field}`).removeClass('input-error');
	};

	// -------- MODALES MULTIPLES
	function multiOptions(type, confirmed = false) {

		if (!confirmed) {
			return UPModal.setModal({
				title: TYPE[type],
				input: {
					label: 'Correo electrónico',
					type: 'email',
					name: 'r_email',
					maxlength: 35,
					required: true
				},
				buttons: {
					confirmAction: `login.multiOptions('${type}', true)`,
					cancelShow: true
				}
			});
		}

		const email = val('#r_email');
		if (!email) return;

		const page = type === 'password' ? 'pass' : 'validation';

		UPModal.proccess_start();

		$.post(`${API}/recover-${page}.php`, { r_email: email }, r => {
			const { status, message } = $.parseResponse(r);

			UPModal.proccess_end(2);
			UPModal.setModal({
				title: status ? 'Hecho' : 'Oops!',
				body: message,
				buttons: { confirmAction: 'close' }
			});
		});
	}

	// -------- VALIDAR OTP
	function comprobarOPT() {
		const code = val('input[name="one_password_time"]');
		if (!code) {
			return UPModal.alert({
				title: 'Oops', 
				body:'No ingresaste el código'
			});
		}
		const params = $.param({
			nick: val('#nick'),
			pass: val('#password'),
			rem: $('#remember').is(':checked'),
			csrf_token: csrf(),
			code, 
		});
		
		$.post(`/login-validar.php`, params, request => {
			const { status, message } = $.parseResponse(request);
			(status === 1) ? location.reload() : UPModal.alert('Oops', message, false);
		});
	}

	// -------- LOGIN PRINCIPAL
	function iniciarSesion() {

		const nick = val('#nick');
		const pass = val('#password');

		if (!nick || !pass) return;

		const params = $.param({
			nick,
			pass,
			rem: $('#remember').is(':checked'),
			csrf_token: csrf()
		});

		setButtonLoading(true);
		loading.start();
		
		$.post("/login-user.php", params, r => {
			const { status, message } = $.parseResponse(r); 
			
			if ([0, 2].includes(status)) {
				showError((status === 0 ? 'nick' : 'password'), message); 
				return; 
			} 
			if(status === 3) { 
				UPModal.alert({ title: 'Ups!', body: message }); 
				return; 
			} 
			if(status === 4) { 
				UPModal.setModal({ 
					input: { 
						label: 'Código 2FA', 
						type: 'numeric', 
						name: 'one_password_time', 
						placeholder: '000000', 
						maxlength: 6, 
						inputmode: 'numeric' 
					}, 
					buttons: { 
						confirmAction: login.comprobarOPT() 
					} 
				}); 
				return; 
			} 
			if(status === 1) { 
				location.reload(); 
			}
		})
		.fail(() => UPModal.alert({
			title: 'Error', 
			body: 'Fallo en la conexión'
		}))
		.always(() => {
			setButtonLoading(false);
			loading.end();
		});
	}

	// -------- VISIBILIDAD PASSWORD
	function togglePassword() {
		const btn = $('#IWantSeePassword');
		const input = $('#password');

		btn.on('click', () => {
			const visible = input.attr('type') === 'text';
			input.attr('type', visible ? 'password' : 'text');

			btn.toggleClass('unlock lock')
				.attr('data-title', visible ? 'Ver contraseña' : 'Ocultar contraseña');
		});
	}

	// Público
	return {
		multiOptions,
		comprobarOPT,
		iniciarSesion,
		constrasena: togglePassword
	};

})();


// Eventos
$(function () {

	$('form').on('submit', e => {
		e.preventDefault();
   	e.stopPropagation();
		login.iniciarSesion();
	});

	$(document).on('keydown', e => {
		if (e.key === 'Enter' && TYPE_LOAD === 'modal') {
			login.iniciarSesion();
		}
	});

	$('[data-toggle="forget_password"]').on('click', () => {
		login.multiOptions('password');
	});

	window.login = {
    	comprobarOPT: login.comprobarOPT 
	};

	login.constrasena();
});