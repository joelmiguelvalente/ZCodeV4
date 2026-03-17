/**
 * Módulo de Registro (Autoejecutable IIFE)
 * Gestiona la lógica del formulario de registro, validación de campos
 * en tiempo real (regex y servidor) y envío final de la cuenta.
 *
 * @version 2.0.0
 * @author Miguel92
 * @contributors Asistencia experta de Gemini (Refactorización y Documentación)
 */
import { $ } from './app/zcode.app.js';
import { notify } from './ui/notify.js';
import { UPModal } from './ui/modal.js';
import { loading } from './ui/loading.js';
import { generateRandomString } from './core/utils.js';

const registro = (() => {
	'use strict';

	// --- CONSTANTES Y CONFIGURACIÓN ---
	
	/** Códigos de estado del Backend (el primer carácter de la respuesta) */
	const STATUS = {
		ERROR: 0,
		SUCCESS: 1,
		WARNING: 2,
		INFO: 3,
		CRITICAL: 4
	};
	
	// Clases CSS utilizadas para el manejo de mensajes de validación
	const VALIDATION_CLASSES = 'error ok loading info';

	// Estado de aprobación de cada campo
	const approved = {
		nick: false,
		password: false,
		email: false,
		terminos: false
	};

	// Patrones de expresiones regulares para validar campos
	const REGEX = {
		nick: /^[a-zA-Z0-9\_\-]{4,20}$/,
		password: /^.{4,32}$/,
		email: /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/
	};
	
	// Niveles de seguridad de la contraseña para UX
	const PASSWORD_LEVEL = {
		colors: { 0: 'gray', 1: 'green', 2: '#E5B91E', 3: '#E5521E', 4: '#CD1B1B' },
		texts: { 0: 'Muy fácil', 1: 'Fácil', 2: 'Medio', 3: 'Difícil', 4: 'Extremadamente difícil' }
	};
	
	// Referencias a elementos del DOM (ZQuery instances)
	const $iWantPassword = $("#IWantSeePassword");
	const $inputPassword = $('input[type="password"]');

	// --- FUNCIONES DE UTILIDAD (HTTP & VALIDACIÓN) ---

	/**
	 * Constructor de peticiones POST simplificado para el módulo de registro.
	 * @param {string} page - El endpoint PHP (ej: 'check-nick').
	 * @param {object|string} data - Los datos a enviar (objeto o string serializado).
	 * @returns {Promise<string>} La promesa de la respuesta del servidor (texto).
	 */
	const up = {
		post: async function(page, data) {
			return await $.post(`/registro-${page}.php?ajax=true`, data);
		}
	};

	/**
	 * Muestra mensajes de validación al usuario.
	 * @param {string} selector - Selector del campo de entrada.
	 * @param {string} msg - El mensaje de texto a mostrar.
	 * @param {number} type - Código de estado para determinar la clase CSS (0: error, 1: ok, etc.).
	 * @returns {boolean} True si la validación fue exitosa (type=1), false en caso contrario.
	 */
	function displayMessage(selector, msg, type) {
		const statusClasses = VALIDATION_CLASSES.split(' ');
		const appendClass = statusClasses[type];
		
		// Selecciona el elemento '.help' dentro del contenedor principal
		const $fieldContainer = $(selector).closest('.upform-group, .upform-check');

		// Buscamos solo el help dentro de ese contenedor
		const $help = $fieldContainer.find('.help');
		$help.removeClass(VALIDATION_CLASSES).addClass(appendClass).html(msg);
		
		return (type === STATUS.SUCCESS);
	}

	/**
	 * Valida la respuesta del servidor (código y mensaje) y la regex local.
	 * @param {string} nameEl - Nombre del campo ('nick', 'email', 'password').
	 * @param {string} response - Respuesta del servidor (ej: '1: Nick disponible').
	 * @returns {boolean} Resultado final de la validación.
	 */
	function validateField(nameEl, response) {
		let valueOfText = $('#' + nameEl).val();
		const type = parseInt(response.charAt(0));
		const message = response.substring(3);

		// Si el nombre es 'password2', usamos la regex de 'password'
		const fieldRegex = REGEX[nameEl === 'password2' ? 'password' : nameEl];
		const verifyRegex = fieldRegex ? fieldRegex.test(valueOfText) : true;
	 
		// La validación es exitosa solo si pasa la regex y el servidor responde SUCCESS
		if (verifyRegex) {
			return displayMessage('#' + nameEl, message, type);
		}
		
		// Si falla la regex local, se asume error
		return displayMessage('#' + nameEl, "Formato incorrecto o fuera de rango", STATUS.ERROR);
	}

	/**
	 * Evalúa la fortaleza de la contraseña y actualiza el feedback visual.
	 * @param {string} password - La contraseña a evaluar.
	 * @param {string} nameEl - Nombre del campo (para compatibilidad).
	 */
	function checkStrength(password) {
		let strength = 0; // 0 (Muy fácil) a 4 (Extremadamente difícil)
	 
		if (password.length >= 8) strength += 1;
		if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;
		if (password.match(/\d/)) strength += 1;
		if (password.match(/[^a-zA-Z\d]/)) strength += 1;

		// Actualización visual usando ZQuery
		const $strengthEl = $('#password-strength span');
		const $textEl = $('#password-strength em');
		
		$strengthEl.css({ 
			'background-color': PASSWORD_LEVEL.colors[strength],
			// Quitamos .removeAttr('style') y ponemos el width aquí para asegurar la animación
			'width': `${(strength / 4) * 100}%` 
		});
		$textEl.html(PASSWORD_LEVEL.texts[strength]);
	}


	// --- FUNCIONES DE VALIDACIÓN DE CAMPOS ---

	/**
	 * Valida el campo Nick contra el servidor.
	 * @param {string} inputNameElement - 'nick'.
	 * @param {string} inputValue - Valor del campo.
	 * @param {string} inputIDElement - Selector de ID (ej: '#nick').
	 */
	function validateNick(inputNameElement, inputValue, inputIDElement) {
		// Validaciones de longitud mínima/máxima antes de contactar al servidor
	  if (inputValue.length < 4) {
			approved[inputNameElement] = displayMessage(inputIDElement, `Debe ser mayor a 4 caracteres`, STATUS.INFO);
			return;
		} else if (inputValue.length > 20) {
			approved[inputNameElement] = displayMessage(inputIDElement, `Debe ser menor a 20 caracteres`, STATUS.INFO);
			return;
		}

		// Feedback visual de "Comprobando..."
		displayMessage(inputIDElement, `Comprobando ${inputNameElement}...`, STATUS.WARNING);
		
		// Petición al servidor
		up.post('check-nick', { nick: inputValue }).then(response => {
			approved[inputNameElement] = validateField(inputNameElement, response);
		});
	}

	/**
	 * Valida el campo Email contra el servidor.
	 * @param {string} inputNameElement - 'email'.
	 * @param {string} inputValue - Valor del campo.
	 * @param {string} inputIDElement - Selector de ID (ej: '#email').
	 */
	function validateEmail(inputNameElement, inputValue, inputIDElement) {
		displayMessage(inputIDElement, `Comprobando ${inputNameElement}...`, STATUS.WARNING);
		up.post('check-email', { email: inputValue }).then(response => {
			approved[inputNameElement] = validateField(inputNameElement, response);
		});
	}

	/**
	 * Valida la contraseña (fortaleza local y contra el nick).
	 * @param {string} inputNameElement - 'password'.
	 * @param {string} inputIDElement - Selector de ID (ej: '#password').
	 */
	function validatePassword(inputNameElement, inputIDElement) {
		const valueOfPassword = $("#password").val();
		const valueOfNick = $("#nick").val();
		
		checkStrength(valueOfPassword); // Actualiza UX de fortaleza

		let message = '';
		let type = STATUS.SUCCESS; // Asumimos éxito a menos que haya reglas locales
		
		if (valueOfPassword === valueOfNick) {
			message = 'No puede ser igual al Nick';
			type = STATUS.ERROR;
		} else if (valueOfPassword.length < 4) {
			message = 'Debe tener al menos 4 caracteres';
			type = STATUS.ERROR;
		}

		// Si hay un error local, lo mostramos y terminamos la validación
		if (type === STATUS.ERROR) {
			approved[inputNameElement] = displayMessage(inputIDElement, message, type);
		} else {
			// Si no hay error local, mostramos el mensaje de éxito o info (como 'Comprobando...')
			// Tu código original usaba validateField para esto, lo cual es incorrecto
			// porque validateField espera la respuesta del servidor.
			approved[inputNameElement] = displayMessage(inputIDElement, 'Contraseña OK. ', STATUS.SUCCESS);
		}
	}

	/**
	 * Función principal que dirige la validación del campo.
	 * @param {HTMLElement} element - El elemento DOM que disparó el evento.
	 */
	function checkField(element) {
		const $element = $(element);
		const inputNameElement = $element.attr('name');
		const inputIDElement = `#${$element.attr('id')}`;
		let inputValue = $element.val();

		switch (inputNameElement) {
			case 'nick':
				validateNick(inputNameElement, inputValue, inputIDElement);
			break;
			case 'email':
				validateEmail(inputNameElement, inputValue, inputIDElement);
			break;
			case 'password':
			case 'password2': // Validar ambas como password
				validatePassword(inputNameElement, inputIDElement);
			break;
			case 'terminos':
				let isChecked = $element.prop('checked');
				// Si no está marcado, tipo 0 (error), si sí, tipo 1 (success)
				const type = isChecked ? STATUS.SUCCESS : STATUS.ERROR;
				const msg = isChecked ? 'Términos aceptados' : 'Debes aceptar los términos';
				
				approved[inputNameElement] = displayMessage(inputIDElement, msg, type);
			break;
		}
	}

	/**
	 * Verifica si todos los campos requeridos están aprobados.
	 * @param {object} obj - El objeto 'approved'.
	 * @returns {boolean} True si todos los valores son true.
	 */
	function areAllApproved(obj) {
		for (const prop in obj) {
		if (Object.prototype.hasOwnProperty.call(obj, prop) && !obj[prop]) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Muestra/Oculta el estado de carga en el botón de submit.
	 * @param {boolean} [action=false] - True para cargar, false para estado normal.
	 */
	function btnLoad(action = false) {
		const TXT_ACTION = action ? 'Creando nueva cuenta...' : 'Crear cuenta';
		$('.upform-buttons input[type="submit"]').attr({ value: TXT_ACTION, disabled: action });
	}

	/**
	 * Procesa el envío del formulario y la creación de la cuenta.
	 */
	function createAccount() {
		// Solo continuar si todos los campos requeridos han pasado la validación
		if (areAllApproved(approved)) {
			btnLoad(true);
			const formData = $('form').serialize();
			notify.start({
				title: 'ZCode',
				content: 'Estamos procesando...'
			});
			
			// Petición de creación de cuenta
			up.post('nuevo', formData).then(response => {
				const { status, message } = $.parseResponse(response);
			
				if (status === STATUS.ERROR || status === STATUS.WARNING) {
					UPModal.alert({ title: 'Error', body: message });
					btnLoad();
					return;
				}
				
				// Éxito o Acción Especial (e.g., 2FA)
				if (status === STATUS.SUCCESS || status === STATUS.WARNING) { // Warning 2 puede ser redirigir
					UPModal.setModal({
						status: 'success',
						title: 'Registro completado',
						body: message,
						buttons: {
							confirmShow: true,
							confirmAction: `registro.redirect(${status})`, // Usamos la función global expuesta
							cancelShow: false
						}
					});
				}
			}).catch(error => {
				UPModal.proccess_end();
				UPModal.alert({
					title: 'Error de Conexión', 
					body: 'Fallo al enviar la solicitud al servidor.'
				});
				btnLoad();
			});
		} else {
			UPModal.alert({
				title: 'Validación Pendiente', 
				body: 'Por favor, complete correctamente todos los campos requeridos.'
			});
		}
	}

	/**
	 * Redirige al usuario después de un registro exitoso.
	 * @param {number} [type=0] - Tipo de redirección (0: home, 2: cuenta).
	 */
	function redirect(type = 0) {
		location.href = ZCodeApp.url + '/' + (type === 2 ? 'cuenta/' : '');
	}

	/**
	 * Alterna la visibilidad del campo de contraseña (Mostrar/Ocultar).
	 */
	function togglePasswordVisibility() {
		$iWantPassword.on('click', () => {
			const set = $iWantPassword.attr('class'); // e.g., 'iconify unlock'
			const isVisible = $inputPassword.attr('type') === 'text';

			// Lógica: Si está visible, volvemos a 'password' y ponemos el icono 'lock'
			const newClass = isVisible ? 'iconify lock' : 'iconify unlock';
			const newType = isVisible ? 'password' : 'text';

			$iWantPassword.removeClass(set).addClass(newClass);
			$inputPassword.attr({ type: newType });
		});
	}

	/**
	 * Genera una contraseña fuerte y la inyecta en el campo.
	 */
	function generatePassword() {
		if (!$inputPassword || !$iWantPassword) return;
		// Aseguramos el estado de 'vista'
		$iWantPassword.removeClass('iconify unlock').addClass('iconify lock');
		// Generar, establecer tipo 'text' para que se vea, y enfocar
		$inputPassword.val(generateRandomString(12)).attr({ type: 'text' }).focus();
		
		// Verificar la fuerza de la nueva contraseña
		checkStrength($inputPassword.val());
	}


	// --- EXPOSICIÓN GLOBAL Y RETORNO ---
	
	// Retornamos y exponemos las funciones públicas
	return {
		createAccount: createAccount,
		check: checkField,
		togglePassword: togglePasswordVisibility,
		redirect: redirect,
		generate: generatePassword
	}

})();

// 🔑 CLAVE: EXPOSICIÓN GLOBAL para uso en 'onclick' y enlaces (ver Solución 1 del chat anterior)
// Esto debe hacerse porque las acciones del modal usan onclick, que no respeta el scope del módulo.
window.registro = {
	redirect: registro.redirect // Solo exponemos la función necesaria para el modal
}

// --- ASIGNACIÓN DE EVENTOS ---

// Asignar evento blur y keyup a inputs (para la validación en tiempo real)
$('form').on('keyup', 'input', function() {
	registro.check(this)
});

// Asignar evento change para inputs tipo radio y checkbox (ej: 'terminos')
$('form').on('change', 'input[type="checkbox"]', function() {
	registro.check(this)
});

// Asignar evento submit al formulario de registro
$('form').submit(function(e) {
	e.preventDefault();
	registro.createAccount();
});

// Para generar contraseña aleatoria
$('#generar').on('click', () => registro.generate());

// Auto-ejecutar el listener de visibilidad de contraseña
registro.togglePassword();