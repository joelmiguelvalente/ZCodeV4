/**
 * ZCode importModule
 * Carga módulos JS bajo demanda sin necesidad de script tags en el HTML.
 *
 * Soporta:
 * - Cache interno
 * - Versionado opcional
 * - Ejecución de una o varias funciones
 * - Validación de rutas
 *
 * @author Miguel92
 * @version 2.0.0
 */

const importedFiles = new Set();

export async function importModule(filePath = '', fn = null, params = {}, options = {}) {
	const {
		forceReload = false,
		version = ZCodeApp.version || null
	} = options;
	// Validación básica de ruta por seguridad
	if (!/^[a-zA-Z0-9/_-]+\.js$/.test(filePath) || filePath.includes('..')) {
		throw new Error(`Ruta inválida: ${filePath}`);
	}
	const fullPath = `${ZCodeApp.assets}/fs/__fs${filePath.replace(/^\/+/, '')}`;
	const url = version ? `${fullPath}?v=${version}` : fullPath;
	// Evitar múltiples importaciones innecesarias
	if (importedFiles.has(url) && !forceReload) return;
	try {
		const module = await import(url);
		importedFiles.add(url);
		// Si no se especifica función, intenta usar default
		if (!fn && typeof module.default === 'function') {
			return module.default(params);
		}
		// Ejecuta función solicitada
		const call = (name) => {
			if (typeof module[name] === 'function') {
				module[name](params);
			} else {
				console.warn(`⚠️ La función '${name}' no existe en ${filePath}`);
			}
		};
		Array.isArray(fn) ? fn.forEach(call) : call(fn);
	} catch (err) {
		console.error(`❌ Error al importar módulo: ${filePath}`, err);
	}
}