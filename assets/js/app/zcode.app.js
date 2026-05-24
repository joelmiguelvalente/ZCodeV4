/**
 * ZCode.js - Módulo Base para Manipulación DOM & Servicios de Red.
 * * Este micro-framework, inspirado en la ligereza de Zepto.js,
 * proporciona una capa de abstracción simple, moderna y performante
 * sobre las APIs nativas del navegador (Fetch, Web Animations API).
 * * @version 2.0.0 (Refactorización y Modernización ESNext)
 * @author Miguel92
 * @contributors Asistencia experta de Gemini (Refactorización y Documentación JSDoc)
 */

// Dependencia global (idealmente debería pasarse en el constructor o config, pero respetamos tu estructura)
export const {
	url: basePath,
	titulo: baseTitle,
	images: { assets: pathImages } = {}
} = ZCodeApp || { url: '', titulo: '', images: {} }; // Fallback para evitar crash si ZCodeApp no existe

class ZQuery {
	/**
	 * @param {NodeList|Array|Element|string} elements
	 */
	constructor(elements) {
		if (!elements) {
			this.elements = [];
		} else if (elements instanceof ZQuery) {
			this.elements = elements.elements;
		} else if (typeof elements === 'string') {
			this.elements = Array.from(document.querySelectorAll(elements));
		} else if (elements instanceof Node || elements === window) {
			this.elements = [elements];
		} else {
			this.elements = Array.from(elements);
		}
	}

	// --- Core ---

	get length() { return this.elements.length; }
	first() { return new ZQuery(this.elements[0] ?? []); }
	last() { return new ZQuery(this.elements.at(-1) ?? []); } // .at(-1) es más moderno

	// Permite usar: for (const el of $('div')) { ... }
	*[Symbol.iterator]() {
		for (const el of this.elements) yield el;
	}

	each(callback) {
		this.elements.forEach((el, i) => callback(el, i));
		return this;
	}

	eq(index) {
		return new ZQuery(this.elements.at(index));
	}

	map(callback) {
		const results = [];

		this.each((el, i) => {
			const res = callback.call(el, el, i);
			if (res == null) return;

			// Si el callback devuelve un array, lo expandimos
			if (Array.isArray(res)) {
				results.push(...res);
			} else {
				results.push(res);
			}
		});

		// Si el resultado son nodos, devolvemos ZQuery
		if (results.length && results[0] instanceof Node) {
			return new ZQuery(results);
		}

		return results;
	}

	// --- Classes & Styles ---

	addClass(classNames) {
		return this.each(el => el.classList.add(...classNames.split(' ')));
	}

	removeClass(classNames) {
		return this.each(el => el.classList.remove(...classNames.split(' ')));
	}

	toggleClass(classNames) {
		// Divide la cadena de clases por espacios.
		const classes = classNames.split(' ');
		
		return this.each(el => {
			classes.forEach(className => {
				// Aplica toggle a cada clase individualmente.
				if (className) { // Asegura que no sea una cadena vacía
					el.classList.toggle(className);
				}
			});
		});
	}

	hasClass(className) {
		return this.elements[0]?.classList.contains(className) ?? false;
	}

	css(prop, value = null) {
		if (typeof prop === 'object') {
			return this.each(el => Object.assign(el.style, prop));
		}
		if (value === null) {
			// Computed style es costoso, úsalo con sabiduría
			return this.elements[0] ? getComputedStyle(this.elements[0])[prop] : undefined;
		}
		return this.each(el => el.style[prop] = value);
	}

	// --- DOM Manipulation ---

	show(display = '') {
		return this.each(el => el.style.display = display);
	}

	hide() {
		return this.each(el => el.style.display = 'none');
	}

	remove() {
		return this.each(el => el.remove());
	}

	empty() {
		return this.each(el => el.innerHTML = '');
	}

	/**
	 * Alterna la visibilidad de los elementos (show/hide).
	 * Usa el método interno `isVisibled` para determinar el estado actual.
	 * @returns {ZQuery} Instancia actual para encadenamiento.
	 */
	toggle() {
	   return this.each(el => {
	      if (this.isVisibled(el)) {
	         $(el).hide();
	      } else {
	         $(el).show();
	      }
	   });
	}

	/**
	 * ⚠️ SECURITY WARNING: El uso de html() con input de usuario puede causar XSS.
	 * Asegúrate de sanitizar el contenido antes de pasarlo aquí.
	 */
	html(content) {
		if (content === undefined) return this.elements[0]?.innerHTML;
		return this.each(el => el.innerHTML = content);
	}

	text(content) {
		if (content === undefined) return this.elements[0]?.textContent;
		return this.each(el => el.textContent = content);
	}

	append(content) {
		return this.each((el) => {
			if (typeof content === 'string') {
				el.insertAdjacentHTML('beforeend', content);
			} else if (content instanceof Node) {
				el.append(content.cloneNode(true)); // Clone para evitar mover el nodo si son múltiples targets
			} else if (content instanceof ZQuery) {
				content.each(child => el.append(child.cloneNode(true)));
			}
		});
	}

	clone(deep = true) {
		const clones = this.elements.map(el => el.cloneNode(deep));
		return new ZQuery(clones);
	}

	// --- Attributes & Values ---

	attr(name, value = null) {
		if (typeof name === 'object') {
			// si recibimos un objeto, seteamos cada propiedad
			for (const key in name) {
				this.each(el => el.setAttribute(key, name[key]));
			}
			return this;
		}
		if (value === null) return this.elements[0]?.getAttribute(name);
		if (value === false) return this.each(el => el.removeAttribute(name)); // Feature extra: remover con false
		return this.each(el => el.setAttribute(name, value));
	}

	removeAttr(name) {
		return this.each(el => el.removeAttribute(name));
	}

	prop(name, value = null) {
		const el = this.elements[0];
		if (value === null) {
			// Getter: Devuelve el valor de la propiedad del primer elemento
			return el ? el[name] : undefined;
		}
		// Setter: Establece la propiedad en todos los elementos
		return this.each(element => {
			element[name] = value;
		});
	}

	data(key, value = undefined) {
		// Convierte kebab-case a camelCase automáticamente para dataset
		const camelKey = key.replace(/-./g, x => x[1].toUpperCase());
		if (value === undefined) return this.elements[0]?.dataset[camelKey];
		return this.each(el => el.dataset[camelKey] = value);
	}

	val(value = null) {
		if (value === null) return this.elements[0]?.value;
		return this.each(el => el.value = value);
	}

	focus() {
		// Llama al método focus() nativo en el primer elemento de la colección.
		this.elements[0]?.focus(); 
		// ¡Importante! Devuelve la instancia 'this' para que el chaining continúe.
		return this; 
	}

	// --- Traversal ---

	parent() {
		const parents = new Set(this.elements.map(el => el.parentElement).filter(Boolean));
		return new ZQuery([...parents]);
	}

	children(selector = null) {
		const kids = [];
		this.each(el => {
			// :scope es vital para querySelector inmediato
			const nodes = selector ? el.querySelectorAll(`:scope > ${selector}`) : el.children;
			kids.push(...nodes);
		});
		return new ZQuery(kids);
	}

	closest(selector) {
		return new ZQuery(this.elements[0]?.closest(selector));
	}

	find(selector) {
		const found = new Set(); // Set para evitar duplicados
		this.each(el => el.querySelectorAll(selector).forEach(n => found.add(n)));
		return new ZQuery([...found]);
	}
	
	/**
	 * Encuentra todos los elementos hermanos del primer elemento de la colección,
	 * opcionalmente filtrados por un selector.
	 * @param {string} [selector=null] - Selector CSS para filtrar los hermanos.
	 * @returns {ZQuery} Nueva instancia con los elementos hermanos.
	 */
	siblings(selector = null) {
		const el = this.elements[0];
		if (!el || !el.parentElement) return new ZQuery([]);

		const allSiblings = Array.from(el.parentElement.children).filter(child => child !== el);
		
		if (selector) {
			return new ZQuery(allSiblings.filter(sibling => sibling.matches(selector)));
		}
		
		return new ZQuery(allSiblings);
	}

	/**
	 * Encuentra el siguiente elemento hermano inmediato, opcionalmente filtrado por un selector.
	 * @param {string} [selector=null] - Selector CSS para filtrar el siguiente hermano.
	 * @returns {ZQuery} Nueva instancia con el hermano siguiente.
	 */
	next(selector = null) {
		let el = this.elements[0]?.nextElementSibling;
		
		while (el && selector && !el.matches(selector)) {
			el = el.nextElementSibling;
		}
		
		return new ZQuery(el);
	}

	/**
	 * Encuentra el elemento hermano inmediato anterior, opcionalmente filtrado por un selector.
	 * @param {string} [selector=null] - Selector CSS para filtrar el hermano anterior.
	 * @returns {ZQuery} Nueva instancia con el hermano anterior.
	 */
	prev(selector = null) {
		let el = this.elements[0]?.previousElementSibling;
		
		while (el && selector && !el.matches(selector)) {
			el = el.previousElementSibling;
		}
		
		return new ZQuery(el);
	}

	/**
	 * Reduce el conjunto de elementos a aquellos que coinciden con el selector o función.
	 * @param {string|function} selector - Selector CSS o función de filtro.
	 * @returns {ZQuery} Nueva instancia con los elementos filtrados.
	 */
	filter(selector) {
		if (typeof selector === 'function') {
			// Si es una función, la ejecutamos para cada elemento.
			const filtered = this.elements.filter((el, i) => selector.call(el, i, el));
			return new ZQuery(filtered);
		}
		
		// Si es un selector de string, usamos .matches().
		const filtered = this.elements.filter(el => el.matches(selector));
		return new ZQuery(filtered);
	}

	/**
	 * Elimina elementos de la colección actual que coincidan con el selector.
	 * @param {string} selector - Selector CSS para los elementos a excluir.
	 * @returns {ZQuery} Nueva instancia con los elementos restantes.
	 */
	not(selector) {
		const filtered = this.elements.filter(el => !el.matches(selector));
		return new ZQuery(filtered);
	}

	is(selector) {
		return this.elements[0]?.matches(selector) ?? false;
	}

	// --- Events ---


   on(events, selector, callback, options = false) {
      if (typeof selector === 'function') {
         options = callback ?? options;
         callback = selector;
         selector = null;
      }
      const eventList = events.split(/\s+/);
      return this.each(el => {
         eventList.forEach(event => {
            el.addEventListener(event, e => {
               const target = selector ? e.target.closest(selector) : el;
               if (!selector || target) {
                  callback.call(target, e, target);
               }
            }, options);
         });
      });
   }

	off(event, callback, options = false) {
		return this.each(el => el.removeEventListener(event, callback, options));
	}

	// --- Animations (Modern WAAPI) ---

	/**
	 * Anima propiedades CSS en los elementos utilizando la Web Animations API.
	 * @param {object} properties - Objeto con las propiedades CSS a animar y sus valores finales.
	 * @param {object} [options={duration: 400, easing: 'ease-out', fill: 'forwards'}] - Opciones de la animación.
	 * @returns {ZQuery} Instancia de ZQuery para chaining.
	 */
	animate(properties, options = {}) {
		const defaultOptions = {
			duration: 400,
			easing: 'ease-out',
			fill: 'forwards'
		};
		const animOptions = { ...defaultOptions, ...options };

		// Convertir las propiedades de destino en un array de Keyframes
		// WAAPI requiere al menos dos keyframes (inicio y fin)
		const finalKeyframe = properties;
		
		// El Keyframe inicial (cero) debe ser el estado actual (o un estado conocido)
		const initialKeyframe = {};
		
		// Crear el Keyframe inicial basándose en el estado actual de las propiedades
		this.each(el => {
			const style = getComputedStyle(el);
			for (const prop in properties) {
				// Solo inicializamos si la propiedad final no está en la inicial
				if (!initialKeyframe[prop]) {
					initialKeyframe[prop] = style[prop];
				}
			}
		});

		// Ejecutar la animación en cada elemento
		return this.each(el => {
			let easingValue = animOptions.easing || 'ease-out';
			if (typeof easingValue === 'string' && $.easing && $.easing[easingValue]) {
			   switch(easingValue) {
			      case 'swing':
			         easingValue = 'cubic-bezier(0.5, 0.05, 0.2, 0.95)';
			         break;
			      default:
			         console.warn(`[ZQuery] Easing '${easingValue}' no mapeado a cubic-bezier. Usando 'ease-out'.`);
			         easingValue = 'ease-out';
			   }
			   animOptions.easing = easingValue;
			}
			el.animate([initialKeyframe, finalKeyframe], animOptions);
		});
	}

	fadeIn(duration = 400) {
		return this.each(el => {
			el.style.display = ''; // Restaurar display original o block implícito
			if (getComputedStyle(el).display === 'none') el.style.display = 'block';
			
			// Web Animations API: Mucho más performante que manipular estilos en un loop JS
			el.animate([
				{ opacity: 0 },
				{ opacity: 1 }
			], {
				duration: duration,
				easing: 'ease-in-out',
				fill: 'forwards'
			});
		});
	}

	fadeOut(duration = 400) {
		// ¡FALTA fadeOut! Se asume que existe o se añade:
		return this.each(el => {
			el.animate([
				{ opacity: 1 },
				{ opacity: 0 }
			], {
				duration: duration,
				easing: 'ease-in-out',
				fill: 'forwards'
			}).onfinish = () => el.style.display = 'none'; // Esconder al terminar
		});
	}
	
	/**
	 * Muestra elementos con una animación de sliding hacia abajo.
	 *
	 * @param {number|object} durationOrOptions - Duración en ms o un objeto de opciones.
	 * @param {string} [easing='swing'] - Función de easing (de $.easing) si se usa solo la duración.
	 * @param {function} [callback] - Función a ejecutar al finalizar.
	 * @returns {ZQuery}
	 */
	slideDown(durationOrOptions = 400, easing = 'ease-in-out', callback) {
		const options = typeof durationOrOptions === 'object' ? durationOrOptions : { duration: durationOrOptions, easing, complete: callback };

		return this.each(el => {
			if (!this.isVisibled(el)) return;

			el.style.display = options.display || 'block';
			const originalHeight = el.scrollHeight;

			el.style.height = '0';
			el.style.overflow = 'hidden';

			el.animate([
					{ height: '0' },
					{ height: `${originalHeight}px` }
				], {
					duration: options.duration || 400,
					easing: options.easing || 'ease-in-out',
					fill: 'forwards'
				}
			).onfinish = () => {
				el.style.height = '';
				el.style.overflow = '';
				if (options.complete) options.complete.call(el);
			};
		});
	}

	/**
	 * Oculta elementos con una animación de sliding hacia arriba.
	 *
	 * @param {number|object} durationOrOptions - Duración en ms o un objeto de opciones.
	 * @param {string} [easing='swing'] - Función de easing (de $.easing) si se usa solo la duración.
	 * @param {function} [callback] - Función a ejecutar al finalizar.
	 * @returns {ZQuery}
	 */
	slideUp(durationOrOptions = 400, easing = 'swing', callback) {
		const options = typeof durationOrOptions === 'object' ? durationOrOptions : { duration: durationOrOptions, easing, complete: callback };

		return this.each(el => {
			if (this.isVisibled(el)) return;

			const startHeight = el.scrollHeight; // Altura inicial REAL
			
			el.style.height = `${startHeight}px`;
			el.style.overflow = 'hidden';

			// 1. Ejecutar la animación de altura
			$(el).animate({
				height: '0'
			}, {
				duration: options.duration || 400,
				easing: options.easing || 'swing', // Usamos nuestra función de easing
				fill: 'forwards'
			}).onfinish = () => {
				// 2. Limpieza: Esconder y devolver al estado natural
				el.style.display = 'none';
				el.style.height = '';
				el.style.overflow = '';
				if (options.complete) options.complete.call(el);
			};
		});
	}

	isVisibled(el = this.elements?.[0]) {
	   if (!el) return false;
	   return (el.offsetWidth > 0 || el.offsetHeight > 0 || el.getClientRects().length > 0);
	}

	isVisible(el = this.elements[0]) {
	   return el && (el.offsetWidth > 0 || el.offsetHeight > 0);
	}

	// --- Forms ---

	serialize() {
		const form = this.elements[0];
		if (!form || form.tagName !== 'FORM') return {};
		// Object.fromEntries es más limpio que el forEach manual
		return Object.fromEntries(new FormData(form).entries());
	}

	validate(requiredFields = []) {
		const data = this.serialize();
		let isValid = true;
		
		// Limpiar errores previos
		this.find('.is-invalid').removeClass('is-invalid');

		requiredFields.forEach(field => {
			if (!data[field]?.trim()) {
				isValid = false;
				const input = this.find(`[name="${field}"]`);
				input.addClass('is-invalid');
			}
		});
		return isValid;
	}

	submit(callback) {
		if (callback) {
			return this.on('submit', callback);
		} else {
			this.each(form => form.submit());
			return this;
		}
	}

	// --- Dimensiones y Posición ---
	
	/**
	 * Obtiene las dimensiones y posición del elemento respecto al viewport.
	 * @returns {DOMRect|undefined} Un objeto DOMRect (left, top, width, height, etc.).
	 */
	rect() {
		return this.elements[0]?.getBoundingClientRect();
	}

	/**
	 * Obtiene la posición del elemento respecto al documento (Document).
	 * @returns {{top: number, left: number}|undefined} Coordenadas del elemento.
	 */
	offset() {
		const rect = this.rect();
		if (!rect) return undefined;
		return {
			top: rect.top + window.scrollY,
			left: rect.left + window.scrollX
		};
	}

	/**
	 * Obtiene la posición del elemento respecto a su padre posicionado (offset parent).
	 * Se utiliza el offset del elemento y se resta el offset de su padre para obtener la posición relativa.
	 * @returns {{top: number, left: number}|undefined} Coordenadas relativas.
	 */
	position() {
		const el = this.elements[0];
		if (!el) return undefined;
		
		const offset = this.offset();
		const parentOffset = $(el.offsetParent).offset();

		// Si el padre no tiene offset (ej: document), asumimos 0,0
		const top = offset.top - (parentOffset?.top || 0);
		const left = offset.left - (parentOffset?.left || 0);

		return { top, left };
	}

	/**
	 * Obtiene el ancho (width) del elemento, excluyendo padding, border y margin.
	 * @returns {number|undefined} Ancho en píxeles.
	 */
	width() {
		const el = this.elements[0];
		if (!el) return undefined;
		// clientWidth incluye padding, por eso restamos
		return el.clientWidth - parseFloat(this.css('padding-left')) - parseFloat(this.css('padding-right'));
	}

	/**
	 * Obtiene el alto (height) del elemento, excluyendo padding, border y margin.
	 * @returns {number|undefined} Alto en píxeles.
	 */
	height() {
		const el = this.elements[0];
		if (!el) return undefined;
		return el.clientHeight - parseFloat(this.css('padding-top')) - parseFloat(this.css('padding-bottom'));
	}

	/**
	 * Obtiene el ancho interno (innerWidth): width + padding.
	 * @returns {number|undefined} Ancho en píxeles.
	 */
	innerWidth() {
		return this.elements[0]?.clientWidth;
	}

	/**
	 * Obtiene el alto interno (innerHeight): height + padding.
	 * @returns {number|undefined} Alto en píxeles.
	 */
	innerHeight() {
		return this.elements[0]?.clientHeight;
	}

	/**
	 * Obtiene el ancho externo (outerWidth): width + padding + border (por defecto).
	 * @param {boolean} [includeMargin=false] - Incluye también el margin si es true.
	 * @returns {number|undefined} Ancho en píxeles.
	 */
	outerWidth(includeMargin = false) {
		const el = this.elements[0];
		if (!el) return undefined;
		
		let width = el.offsetWidth; // width + padding + border
		
		if (includeMargin) {
			const style = getComputedStyle(el);
			width += parseFloat(style.marginLeft) + parseFloat(style.marginRight);
		}
		return width;
	}

	/**
	 * Obtiene el alto externo (outerHeight): height + padding + border (por defecto).
	 * @param {boolean} [includeMargin=false] - Incluye también el margin si es true.
	 * @returns {number|undefined} Alto en píxeles.
	 */
	outerHeight(includeMargin = false) {
		const el = this.elements[0];
		if (!el) return undefined;
		
		let height = el.offsetHeight; // height + padding + border
		
		if (includeMargin) {
			const style = getComputedStyle(el);
			height += parseFloat(style.marginTop) + parseFloat(style.marginBottom);
		}
		return height;
	}
}

// --- Factory ---
export const $ = (selector, parent) => {
	if (typeof selector === 'function') {
		document.addEventListener('DOMContentLoaded', selector);
		return;
	}
	return new ZQuery(selector, parent);
};

$.parseResponse = (request) => {
	const sepIndex = request.indexOf(':');
	if (sepIndex === -1) return { status: 0, message: request }; // fallback
	return { 
		status: parseInt(request.substring(0, sepIndex), 10), 
		message: request.substring(sepIndex + 1).trim()
	};
};

/* =======================
	NETWORKING (FETCH WRAPPER)
	======================= */
// Wrapper estilo jQuery (done, fail, always)
function jqWrapper(promise) {
	return {
		done(fn) {
			promise.then(fn);
			return this;
		},
		fail(fn) {
			promise.catch(fn);
			return this;
		},
		always(fn) {
			promise.finally(fn);
			return this;
		},
		finally(fn) { // alias moderno
			promise.finally(fn);
			return this;
		},
		then(fn) {
			promise.then(fn);
			return this;
		},
		catch(fn) {
			promise.catch(fn);
			return this;
		}
	};
}

$.request = async function(url, { 
	method = 'GET', 
	data = null, 
	headers = {}, 
	responseType = 'text', 
	contentType = null, 
	timeout = 15000 
} = {}) {
	
	// Validar URL base. Si la url ya empieza con http, ignorar basePath
	const finalUrl = url.startsWith('http') ? url : `${ZCodeApp.url}${url}`;
	
	const controller = new AbortController();
	const timer = setTimeout(() => controller.abort(), timeout);

	const config = {
		method,
		headers: {
			'X-Requested-With': 'XMLHttpRequest', // Legacy support para backends viejos
			...headers
		},
		signal: controller.signal
	};

	if (data) {
		if (data instanceof FormData) {
			config.body = data;
			// No setear Content-Type
		} else if (typeof data === 'object') {
			// Si es un objeto, mantenemos el JSON (ej. si no se usa $.post)
			config.body = JSON.stringify(data);
			config.headers['Content-Type'] = contentType || 'application/json';
		} else {
			// Si es una cadena (ej. el output de URLSearchParams), la enviamos tal cual
			config.body = String(data);
			config.headers['Content-Type'] = contentType || 'text/plain';
		}
	}

	try {
		const response = await fetch(finalUrl, config);
		clearTimeout(timer);

		if (!response.ok) {
			// Mejora: lanzar un error con el status para mejor debug
			throw new Error(`[ZQuery] HTTP ${response.status}: ${response.statusText}`);
		}

		switch (responseType) {
			case 'json': return await response.json();
			case 'blob': return await response.blob();
			case 'formData': return await response.formData();
			default: return await response.text();
		}
	} catch (err) {
		if (err.name === 'AbortError') {
			console.error('[ZQuery] Request timeout');
		} else {
			console.error('[ZQuery] Network Error:', err);
		}
		throw err;
	}
};

// Helpers más limpios
$.post = (url, data = {}, successCallback, options = {}) => {
	if (typeof data === 'function') {
		options = successCallback || {};
		successCallback = data;
		data = null;
	}
	if (typeof successCallback === 'object') {
		options = successCallback;
		successCallback = undefined;
	}
	if (typeof successCallback !== 'function') {
		options = successCallback || {};
		successCallback = undefined;
	}
	// Lógica de serialización (se mantiene)
	let body = data;
	let isForm = data instanceof FormData;
	if (typeof data === 'object' && !isForm) {
		body = new URLSearchParams(data).toString();
	}
	// Ejecutar la solicitud
	let request = $.request(`${ZCodeApp.url}/${url}`, {
		method: 'POST',
		data: body, 
		contentType: isForm ? false : 'application/x-www-form-urlencoded',
		...options
	});
	// 🔑 CLAVE: Si se proporcionó un callback, lo encadenamos al éxito (then) de la promesa
	if (successCallback) {
		// request ahora es una promesa que llama al callback de éxito si no hay error HTTP
		request = request.then(successCallback); 
	}
	// Devolvemos el jqWrapper para que el usuario pueda seguir encadenando .fail() y .always()
	return jqWrapper(request);
};

$.get = (url, params = {}, callback = null) => {
	if (typeof params === 'function') {
		callback = params;
		params = {};
	}
	const queryString = new URLSearchParams(params).toString();
	const finalUrl = queryString ? (url.includes('?') ? `${url}&${queryString}` : `${url}?${queryString}`) : url;

	const request = $.request(`${ZCodeApp.url}/${finalUrl}`, { method: 'GET' });

	if (typeof callback === 'function') {
		request.then(callback);
	}

	return jqWrapper(request);
};

$.getJSON = (url, params = {}, callback = null) => {
	if (typeof params === 'function') {
		callback = params;
		params = {};
	}
	const queryString = new URLSearchParams(params).toString();
	const finalUrl = queryString ? (url.includes('?') ? `${url}&${queryString}` : `${url}?${queryString}`) : url;

	const request = $.request(`${ZCodeApp.url}/${finalUrl}`, {
		method: 'GET',
		responseType: 'json'
	});
	if (typeof callback === 'function') {
		request.then(callback);
	}
	return jqWrapper(request);
};

$.param = function(obj) {
	const params = [];
	for (const key in obj) {
		if (obj.hasOwnProperty(key)) {
			const value = obj[key];
			if (Array.isArray(value)) {
				value.forEach(v => {
					params.push(`${encodeURIComponent(key)}[]=${encodeURIComponent(v)}`);
				});
			} else {
				params.push(`${encodeURIComponent(key)}=${encodeURIComponent(value)}`);
			}
		}
	}
	return params.join('&');
};

/* =======================
	EASING UTILITIES
	======================= */

// Funciones de easing clásicas (requeridas por slideUp/Down)
// Aquí solo se incluyen las dos mencionadas, pero se podrían añadir más.
$.easing = {
	// La función 'swing' es el default de jQuery
	swing: (t) => 0.5 - Math.cos(t * Math.PI) / 2,
	// Simulación de easeOutBounce (basada en fórmulas comunes)
	easeOutBounce: (t) => {
		const c1 = 1.70158;
		const c3 = c1 + 1;
		if (t < 1/2.75) {
			return 7.5625 * t * t;
		} else if (t < 2/2.75) {
			return 7.5625 * (t -= 1.5/2.75) * t + 0.75;
		} else if (t < 2.5/2.75) {
			return 7.5625 * (t -= 2.25/2.75) * t + 0.9375;
		} else {
			return 7.5625 * (t -= 2.625/2.75) * t + 0.984375;
		}
	},
	// Simulación de easeInOutElastic (basada en fórmulas comunes)
	easeInOutElastic: (t) => {
		const c5 = (2 * Math.PI) / 4.5;
		if (t === 0) return 0;
		if (t === 1) return 1;
		if ((t /= 0.5) < 1) return -0.5 * (Math.pow(2, 10 * (t -= 1)) * Math.sin((t - 0.1) * c5));
		return Math.pow(2, -10 * (t -= 1)) * Math.sin((t - 0.1) * c5) * 0.5 + 1;
	}
};

ZQuery.prototype.dropdown = function (options = {}) {
   const settings = {
      boxSelector: '.drop-box',
      activeClass: 'is-open',
      ...options
   };
   return this.each(trigger => {
      const targetSelector = trigger.dataset.dropdown;
      if (!targetSelector) return;
      //
      const panel = document.querySelector(targetSelector);
      if (!panel) return;
     	// Estado inicial
     	panel.style.display = 'none';

     	trigger.addEventListener('click', e => {
     	   e.preventDefault();
     	   const isOpen = panel.classList.contains(settings.activeClass);
     	   // Cerrar todos
         document.querySelectorAll(settings.boxSelector).forEach(box => {
            box.classList.remove(settings.activeClass);
            box.style.display = 'none';
         });

         if (!isOpen) {
            panel.classList.add(settings.activeClass);
            panel.style.display = 'block';
         }
      });
   });
};
