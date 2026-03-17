import { $ } from '../js/app/zcode.app.js';
import { UPModal } from '../js/ui/modal.js';
import { loading } from '../js/ui/loading.js';

let cache = {};
function loadTab(type) {
	let nameTab = `perfil_${type}`
	let status = $(`#${nameTab}`).attr('status');
	if(status == 'activo') {
		// LOADER/ STATUS
		$('#perfil_load').hide();
		$(`#${nameTab}`).fadeIn();
		return true;   
	}
	$.post(`/${nameTab.replace('_','-')}.php`, $.param({ pid: $('#info').attr('pid') }), response => {
		const { status, message } = $.parseResponse(response);
		if(status === 1) {
			if(typeof cache[type] === 'undefined') {
				$('#perfil_content').append(message);
				$(`#${nameTab}`).fadeIn();
				cache[type] = true;
			}
		} else UPModal.alert({ title: 'Error', body: message });
		// LOADER/ STATUS
		$('#perfil_load').hide();
		loading.end(); 
	});
	
}

export function loadTabs({ obj, classObj }) {
	const tab = obj.attr('tab');
	$(classObj).removeClass('selected');
	obj.addClass('selected');
	// Aplicamos algunos efectos
	$('#perfil_content > div').fadeOut();
	$('#perfil_load').fadeIn();
	// Cargamos contenido de dicho tab!
	loading.start();
	loadTab(tab);
}

export function handleLoadFilter(type) {
	const params = $.param({
		pid: $('#info').attr('pid'),
		type: type
	});
	$('.filter-item').removeClass('active');
	$(`.filter-item:nth-child(${type})`).addClass('active');
	$.post(`/muro-filtro.php`, params, req => {
		$('#wall-content').html(req.substring(3));
	})
}