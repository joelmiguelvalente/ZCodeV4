import $ from '../jQueryEsm.js';
import { number_format } from '../core/utils.js';
import { UPModal } from '../ui/modal.js';
import { loading } from '../ui/loading.js';

/* DENUNCIAS */
export const denuncia = {
	nueva(type, obj_id, obj_title, obj_user) {
		// PLANTILLA
		loading.start();
		$.post(`/denuncia-${type}.php`, { obj_id, obj_title, obj_user }, req => {
			denuncia.set_dialog({ req, obj_id, type});
			loading.end();
		});
	},
	set_dialog({ req, obj_id, type}) {
		UPModal.setModal({
			title: `Denunciar ${type}`,
			body: req,
			buttons: {
				confirmTxt: `Enviar denuncia`,
				confirmAction: `denuncia.enviar(${obj_id}, '${type}')`,
				cancelShow: true
			}
		});
	},
	enviar(obj_id, type) {
		let razon = $('select[name=razon]').val();
		let extras = $('textarea[name=extras]').val();
		  //
		loading.start();
		$.post(`/denuncia-${type}.php`, { obj_id, razon, extras }, req => {
			let action = parseInt(req.charAt(0));
			let message = req.substring(3);
			UPModal.alert((action === 0 ? 'Error' : 'Bien'), `<div class="empty">${message}</div>`, false);
			loading.end();
		});
	}
}