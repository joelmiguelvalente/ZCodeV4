import { $ } from '../js/app/zcode.app.js';
import { UPModal } from '../js/ui/modal.js';
import { loading } from '../js/ui/loading.js';

export function handleDeleteComment({ comid, autor, postid, status }) {
	postid = postid || appParam('postid');
	if( !status ) {
		UPModal.setModal({
			title: 'Borrar Comentario',
			body: '&#191;Quiere eliminar este comentario?',
			buttons: {
				confirmTxt: 'Borrar comentario',
				confirmAction: `comentario.borrar(${comid}, ${autor}, ${postid}, true)`,
				cancelShow: true
			}
		});
	} else {
		loading.start();
		$.post(`comentario-borrar.php`, { comid, autor, postid }, response => {
			if(parseInt(response.charAt(0)) === 0) UPModal.alert('Error', response.substring(3));
			else {
				UPModal.close();
				UPModal.alert('Listo', response.substring(3), false);
				// RESTAMOS
				$('#ncomments').text(totalComments() - 1);
				$('#comment' + comid).remove();
				loading.end();
			}
		}).fail(() => {
			UPModal.error_500("comentario.borrar('"+comid+"')");
			loading.end();
		});
	}
}