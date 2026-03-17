/**
 * ZCode.js - Módulo de Feedback Visual (Loading/Spinner).
 * * * Este módulo gestiona la superposición de carga global, mejorando la UX 
 * * durante procesos asíncronos. Utiliza APIs nativas para la manipulación 
 * * del DOM y desacopla la inyección del spinner (SVG) para mayor seguridad.
 * * @fileoverview Gestiona el estado 'loading' con un spinner animado.
 * @version 2.0.0 (Optimización de UX, Seguridad e Inyección)
 * @author Miguel92
 * @contributors Asistencia experta de Gemini (Refactorización y Documentación JSDoc)
 */

// Constante para el ID del contenedor
const LOADING_ID = 'loading_start';
// Recomendación: Mover estos estilos a un archivo CSS dedicado.
const CSS_CLASSES = {
	CONTAINER_STYLE: {
		position: 'fixed',
		top: '1rem',
		left: '1rem',
		padding: '.325rem 1rem .325rem .5rem',
		zIndex: 9999,
		background: 'rgba(7, 40, 83, 0.95)', // Usar rgba para mejor contraste
		borderRadius: '.325rem',
		boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)'
	},
	CONTENT_STYLE: {
		display: 'flex',
		justifyContent: 'center',
		alignItems: 'center',
		flexDirection: 'row-reverse',
		gap: '.5rem',
		fontWeight: '700',
		color: '#FFF'
	}
};

/**
 * @typedef {object} LoadingModule
 * @property {number} timeout - Milisegundos de retraso antes de remover el loading (UX).
 * @property {function(string): void} start - Inicia la superposición de carga.
 * @property {function(): void} end - Finaliza y remueve la superposición de carga.
 * @property {function(HTMLElement): Promise<void>} loadSpinner - Inyecta el SVG del spinner.
 */

export const loading = {
	
	timeout: 350,

	/**
	 * Inicia la superposición de carga. Si ya existe, retorna inmediatamente.
	 * @param {string} [text='Procesando...'] - Mensaje a mostrar junto al spinner.
	 * @param {string} [spinnerPath] - (Opcional) Ruta completa del spinner.json.
	 * @returns {void}
	 */
	start(text = 'Procesando...') {
		if (document.getElementById(LOADING_ID)) return;

		const container = document.createElement('div');
		container.id = LOADING_ID;

		const contentText = document.createElement('span');
		contentText.textContent = text;
		
		const contentWrapper = document.createElement('div');
		
		// 🚨 Recomendación: Usar ClassList.add() en lugar de Object.assign(style)
		Object.assign(container.style, CSS_CLASSES.CONTAINER_STYLE);
		Object.assign(contentWrapper.style, CSS_CLASSES.CONTENT_STYLE);

		// Estructura: container -> contentWrapper (spinner + text)
		contentWrapper.appendChild(contentText);
		container.appendChild(contentWrapper);
		document.body.appendChild(container);

		// Llamamos al spinner de forma asíncrona, sin bloquear la función principal
		this.loadSpinner(contentWrapper);
	},

	/**
	 * Carga y añade el SVG del spinner al elemento contenedor.
	 * @param {HTMLElement} target - El elemento donde inyectar el spinner (e.g., el wrapper del texto).
	 * @param {string} assetsPath - La ruta base de los assets para el spinner.json.
	 * @returns {Promise<void>}
	 */
	async loadSpinner(target) {
		try {
			const res = await fetch(`${ZCodeApp.assets}/icons/spinner.json`);
			if (!res.ok) throw new Error(`HTTP ${res.status}`);
			
			const data = await res.json();
			// Suponiendo que el JSON contiene el SVG en '90-ring-with-bg'
			let svg = data['90-ring-with-bg'];
			
			// 🎨 Ajustes específicos de SVG
			svg = svg
				.replace(/viewBox/g, 'fill="#FFF" viewBox') // Inyectar color de relleno directamente
				.replace(/width="1\.5rem"/g, 'width="1rem"')
				.replace(/height="1\.5rem"/g, 'height="1rem"');
			
			// ⚠️ ADVERTENCIA: Se usa insertAdjacentHTML por necesidad de SVG. 
			// Esto es un punto de riesgo XSS. Asegúrate que spinner.json es de confianza.
			target.insertAdjacentHTML('afterbegin', svg); 

		} catch (error) {
			console.error('[Loading spinner error] Fallo al cargar o inyectar SVG:', error);
			// Podríamos inyectar un texto simple si el spinner falla
			target.insertAdjacentText('afterbegin', '⏱️');
		}
	},

	/**
	 * Finaliza la superposición de carga con un pequeño retraso.
	 * @returns {void}
	 */
	end() {
		const loadingElement = document.getElementById(LOADING_ID);
		if (!loadingElement) return;

		// El timeout mejora la UX. Si la carga es muy rápida (< 300ms), 
		// mostrar y quitar el spinner inmediatamente puede ser confuso.
		setTimeout(() => {
			loadingElement.remove();
		}, this.timeout);
	}
};