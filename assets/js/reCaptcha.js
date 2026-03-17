/**
 * Módulo de Inicialización: Carga Asíncrona de reCAPTCHA v3.
 * Este script maneja la inyección dinámica del script de Google reCAPTCHA
 * y la ejecución inmediata para obtener el token antes de que el usuario
 * interactúe con el formulario, mejorando la UX y la seguridad.
 *
 * @fileoverview Inicialización de la seguridad del formulario de registro.
 */
import { $ } from './app/zcode.app.js';
import { notify } from './ui/notify.js';

// Usamos el callback de ZQuery ($) para esperar a que el DOM esté listo (DOMContentLoaded).
$(() => {
	'use strict';
	
	// --- CONSTANTES DE CONFIGURACIÓN ---
	
	// Aseguramos la existencia de ZCodeApp global
	if (typeof ZCodeApp === 'undefined' || !ZCodeApp.pkey) {
		console.error("ZCodeApp o pkey de reCAPTCHA no están definidos globalmente.");
		return;
	}
	
	/** Clave pública del sitio para reCAPTCHA v3. */
	const API_RECAPTCHA_KEY = ZCodeApp.pkey;
	
	/** URL completa del script de Google reCAPTCHA. */
	const RECAPTCHA_SCRIPT_URL = `https://www.google.com/recaptcha/api.js?render=${API_RECAPTCHA_KEY}`;

	/** Opciones de la solicitud reCAPTCHA (acción de la página). */
	const RECAPTCHA_ACTION_OPTIONS = { action: 'registro' }; // Usar una acción descriptiva

	/** Selector del campo oculto donde se guardará el token de reCAPTCHA. */
	const TOKEN_INPUT_SELECTOR = 'input[name="g-recaptcha-response"]'; // Nombre estándar de Google

	// --- FUNCIÓN DE CARGA ASÍNCRONA ---

	/**
	 * Carga un script externo de forma asíncrona usando $.request/fetch.
	 * Reemplaza el antiguo $.getScript (que no existe en ZQuery).
	 * @param {string} url - URL del script.
	 * @returns {Promise<void>}
	 */
	function loadScript(url) {
		return new Promise((resolve, reject) => {
			const script = document.createElement('script');
			script.src = url;
			script.async = true;
			
			script.onload = () => resolve();
			script.onerror = () => reject(new Error(`Fallo al cargar script: ${url}`));

			document.head.appendChild(script);
		});
	}

	// --- FUNCIONES DE RECAPTCHA ---

	/**
	 * Espera a que la librería de reCAPTCHA esté lista y ejecuta el token.
	 * @returns {Promise<string>} Promesa que resuelve con el token de reCAPTCHA.
	 */
	async function executeReCaptcha() {
		// grecaptcha.ready es el método de Google que espera que la API esté completamente cargada.
		return new Promise((resolve, reject) => {
			grecaptcha.ready(async () => {
				try {
					const token = await grecaptcha.execute(API_RECAPTCHA_KEY, RECAPTCHA_ACTION_OPTIONS);
					resolve(token);
				} catch (error) {
					reject(error);
				}
			});
		});
	}

	/**
	 * Inicializa el proceso: carga el script, obtiene el token y desbloquea el formulario.
	 */
	async function initializeSecurity() {
		// Bloqueamos el formulario por defecto para evitar envíos sin token.
		// Usamos attr('disabled', true) para bloquear.
		const $form = $('form');
		$form.attr('disabled', 'true');
		
		try {
			// 1. Cargar el script de Google
			notify.start({
				title: 'Por favor espere',
				content: 'Cargando reCAPTCHA'
			});
			await loadScript(RECAPTCHA_SCRIPT_URL);
			
			// 2. Ejecutar reCAPTCHA y obtener el token
			const token = await executeReCaptcha();

			// 3. Inyectar el token en el campo oculto
			$(TOKEN_INPUT_SELECTOR).val(token);
			notify.start({
				title: 'ZCode',
				content: 'Token reCAPTCHA obtenido y listo'
			});
			
			// 4. Desbloquear el formulario (removeAttr no existe, usamos attr(key, false) o removeAttr nativo)
			// Si no implementaste .removeAttr() en ZQuery, lo hacemos nativo o usamos la clase:
			$form.elements[0]?.removeAttribute('disabled');
			// O mejor aún, si tienes una clase: $form.removeClass('is-disabled');

		} catch (error) {
			console.error('⚠️ [FATAL] No se pudo inicializar reCAPTCHA. El formulario permanecerá bloqueado.', error);
			// Mostrar un error de seguridad al usuario si falla la carga.
			UPModal.alert('Error de Seguridad', 'No se pudo cargar la verificación. Intente recargar la página.', false);
		}
	}

	// --- EJECUCIÓN ---
	initializeSecurity();

});