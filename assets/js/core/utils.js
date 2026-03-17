/**
 * ZCode.js - Módulo de Utilidades Puras (Pure Utility Functions).
 * * * Este módulo centraliza las funciones auxiliares de uso general, 
 * * enfocándose en la simplicidad, el alto rendimiento y la compatibilidad 
 * * con estándares modernos de JavaScript (ESNext). Contiene utilidades para 
 * * manejo de cadenas, formatos numéricos, verificación de tipos y URLs.
 * * @fileoverview Colección de utilidades desacopladas y de propósito único.
 * @version 2.0.0 (Optimización y Transición a ESNext)
 * @author Miguel92
 * @contributors Asistencia experta de Gemini (Refactorización y Documentación JSDoc)
 */

/**
 * Crea un parámetro de URL a partir de ZCodeApp.
 * @param {string} key - La clave a buscar.
 * @param {boolean} [withoutAmp=false] - Si es true, omite el '&' inicial.
 * @returns {string} El parámetro URL (ej. "&clave=valor") o cadena vacía.
 */
export const appParam = (key, withoutAmp = false) => {
   // Determina la clave real en ZCodeApp
   const realKey = (key === 'key') ? 'user_key' : key;
   const value = ZCodeApp?.[realKey]; // Uso de Optional Chaining (seguridad)

   // Verifica si la clave existe y el valor no es falsy (asumiendo que '0' es un valor válido)
   if (value !== undefined && value !== null && value !== '') {
      const prefix = withoutAmp ? '' : '&';
      // URLSearchParams es crucial para el encoding correcto
      const encodedValue = encodeURIComponent(value); 
      return `${prefix}${key}=${encodedValue}`;
   }
   return '';
};

/**
 * Genera una cadena aleatoria criptográficamente segura (CSPRNG) si está disponible.
 * @param {number} [length=10] - Longitud deseada de la cadena.
 * @returns {string} La cadena aleatoria.
 */
export function generateRandomString(length = 10) {
   const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
   const charsetLength = charset.length;
   
   // 🔒 Prioridad: Criptográficamente seguro (usando la API Web Crypto)
   if (typeof window !== 'undefined' && window.crypto?.getRandomValues) {
      // Usamos Uint8Array que es más eficiente para esto que Uint32Array
      const array = new Uint8Array(length);
      window.crypto.getRandomValues(array);
      // Mapeamos los valores aleatorios al charset
      return Array.from(array, byte => charset[byte % charsetLength]).join('');
   }
   
   // ⚠️ Fallback: Uso del menos seguro Math.random() (solo si el entorno lo requiere)
   console.warn("Usando Math.random() no criptográfico. ¡Inseguro para tokens sensibles!");
   let output = '';
   for (let i = 0; i < length; i++) {
      // La operación es la misma, solo que Math.random es menos seguro
      output += charset.charAt(Math.floor(Math.random() * charsetLength));
   }
   return output;
}

/**
 * Verifica si un valor está "vacío" al estilo PHP/Legacy.
 * (undefined, null, false, 0, "", "0", o un objeto/array sin propiedades/elementos).
 * @param {*} value - El valor a comprobar.
 * @returns {boolean} True si está vacío.
 */
export const empty = (value) => {
   // 1. Chequeo rápido de valores falsy comunes
   if (value === undefined || value === null || value === false || value === 0 || value === "") {
      return true;
   }
   // 2. Chequeo del string "0" (lo incluías en tu original)
   if (value === "0") {
      return true;
   }
   // 3. Chequeo de objetos y arrays
   if (typeof value === 'object') {
      // Object.keys() es más moderno y directo que for...in con hasOwnProperty
      // También funciona para Arrays (retorna las llaves numéricas)
      return Object.keys(value).length === 0;
   }
   // Por defecto, si tiene algún valor, no está vacío
   return false;
};

/**
 * Codifica una cadena en Base64, manejando caracteres UTF-8 (requerido por btoa).
 * Compatible con Node.js y navegadores.
 * @param {string} str - La cadena a codificar.
 * @returns {string} La cadena codificada en Base64.
 */
export const base64_encode = (str) => {
   if (typeof Buffer !== 'undefined') {
      // Entorno Node.js
      return Buffer.from(str, 'utf8').toString('base64');
   }
   if (typeof window !== "undefined" && window.btoa) {
      // Entorno de Navegador. Se requiere la codificación para UTF-8.
      const utf8 = new TextEncoder().encode(str);
      // Map es ligeramente más rápido que un loop de strings
      const charCode = Array.from(utf8, byte => String.fromCharCode(byte)).join('');
      return window.btoa(charCode);
   }
   // Fallback o error si no hay btoa ni Buffer
   throw new Error('Base64 encoding no soportado en este entorno.');
};

/**
 * Verifica si un elemento existe en un array.
 * SUSTITUCIÓN: Usa el método nativo Array.prototype.includes().
 * @param {*} needle - El valor a buscar.
 * @param {Array} haystack - El array donde buscar.
 * @param {boolean} [strict=false] - Si es true, usa comparación estricta (===).
 * @returns {boolean} True si el elemento se encuentra.
 */
export const in_array = (needle, haystack, strict = false) => {
   // Si necesitas la comparación estricta (que es el default en JS), usa includes
   if (!strict) {
      // Si no es estricto, debemos hacer el loop manualmente o forzar la comparación ==
      // Pero en JS moderno, la comparación estricta es la buena práctica.
      // Para mantener tu comportamiento original (comparación no estricta, n[t] == e):
      for (const item of haystack) {
         if (item == needle) return true; // Uso de == para no estricto
      }
      return false;
   }
   // Uso de .includes() para la comparación estricta (Recomendado)
   return haystack.includes(needle);
};

/**
 * Formatea un número con separadores de miles y decimales.
 * @param {number|string} number - El número a formatear.
 * @param {number} [decimals=0] - Número de decimales.
 * @param {string} [decPoint='.'] - Separador de decimales.
 * @param {string} [thousandsSep=','] - Separador de miles.
 * @returns {string} El número formateado.
 */
export const number_format = (number, decimals = 0, decPoint = '.', thousandsSep = ',') => {
   // Usamos parseFloat para ser más permisivos con el input
   const num = Number.parseFloat(number); 
   // Si no es un número finito (NaN, Infinity), devolvemos '0'
   if (!Number.isFinite(num)) {
      return '0';
   }
   // 1. Aplicar decimales y obtener la parte entera/fraccionaria
   const fixed = num.toFixed(Math.max(0, decimals));
   let [integer, fraction] = fixed.split('.');
   // 2. Aplicar separador de miles usando la RegExp
   integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);
   // 3. Recomponer el resultado
   if (decimals > 0 && fraction !== undefined) {
      // Usamos el separador de decimales custom
      return `${integer}${decPoint}${fraction}`; 
   }
   return integer;
};

// 💎 Tip Moderno: Para la mayoría de los casos de uso, considera:
// const formatter = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' });
// formatter.format(123456.78); // "$ 123.456,78"

/**
 * Extrae el ID de un video de YouTube desde diversas URL/Formatos.
 * @param {string} linkVideo - La URL del video de YouTube.
 * @returns {string|false} El ID del video (11 caracteres) o false si no es válido.
 */
export function getYoutubeId(linkVideo) {
   // Regex simplificada y anclada (mejor rendimiento y seguridad)
   const regExp = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e|embed|watch)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
   // Usamos el match en la URL
   const match = linkVideo.match(regExp);
   // match[1] es el grupo de captura que contiene el ID de 11 caracteres.
   return (match && match[1]) ? match[1] : false;
}