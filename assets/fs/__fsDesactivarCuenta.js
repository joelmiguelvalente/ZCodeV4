import { $ } from '../js/app/zcode.app.js';
import { UPModal } from '../js/ui/modal.js';
import { loading } from '../js/ui/loading.js';

/**
 * Módulo: Desactivar Cuenta
 * Maneja el flujo de confirmación y solicitud AJAX
 *
 * @author Miguel92
 * @version 1.0.0
 */
const ENDPOINT = `${ZCodeApp.url}/cuenta.php?action=desactivate`;

/**
 * Función principal exportada
 * Controla el flujo según el paso actual
 */
export function desactivate({ step = 0 } = {}) {
	// Paso 0 y 1: confirmaciones
	if (step === 0 || step === 1) {
		return showConfirmModal(step);
	}
	// Paso 2: ejecución final
	processDesactivation();
}

/**
 * Muestra modal de confirmación por pasos
 */
function showConfirmModal(step) {
	const messages = [
		'Si desactiva su cuenta, todo el contenido relacionado a usted dejará de ser visible temporalmente. Pasado ese tiempo, la administración eliminará toda su información y no podrá recuperarla.',
		'¿Está completamente seguro que desea desactivar su cuenta?'
	];
	const nextStep = step + 1;
	UPModal.setModal({
		title: 'Desactivar cuenta',
		body: messages[step] || messages[1],
		buttons: {
			confirmTxt: 'Lo sé',
			confirmAction: `desactivate(${nextStep})`,
			cancelShow: true,
			cancelTxt: 'No desactivar',
			cancelAction: 'close'
		}
	});
}

/**
 * Ejecuta la solicitud final al servidor
 */
function processDesactivation() {
	UPModal.proccess_start('Estamos procesando...');
	$.post(ENDPOINT, { 
		validar: 'ajaxcontinue', 
		csrf_token: $('input[name=csrf_token]').val() 
	}, response => {
		const isError = parseInt(response.charAt(0)) === 0;
		notify.start({
			title: isError ? 'Oops!' : 'Hecho',
			content: isError ? 'No se pudo desactivar la cuenta' : 'Tu cuenta fue desactivada correctamente',
			type: isError ? 'danger' : 'success'
		});
		UPModal.proccess_end();
	});
}

/**
 * Permite llamada automática sin nombre de función
 */
export default function (params) {
	desactivate(params);
}