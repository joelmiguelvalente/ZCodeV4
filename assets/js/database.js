import { $ } from './app/zcode.app.js';
import { loading } from './ui/loading.js';
import { importModule } from './core/loader.js';
import { UPModal } from './ui/modal.js';

const database = {
	table_action(action, table, id = 0) {
		loading.start();
		$.post(`/database-${action}.php`, { table }, req => {
			const { status, message } = $.parseResponse(req);
			let type = (status === 0) ? 'Error' : 'Bien';
			if(action === 'optimize') {
				$(`td[data-cache="${id}"]`).html('Vacio');
				$(`span[data-remove="${id}"]`).remove();
			}
			if(id != 0) $(`td[data-update="${id}"]`).html('Hace instantes');
			UPModal.alert({ title: type, body: message });
			loading.end();
		});
	},
	tablas() {
		let tablas = {};
		$('input[type="checkbox"]').each((i, inpt) => {
			const ic = $(inpt).val();
			if($(inpt).prop('checked') && $(inpt).val() != 'all') tablas[i] = ic;
		});
		return tablas;
	},
	table_all(action) {
		var tablas = this.tablas();
		if($.isEmptyObject(tablas)) {
			UPModal.alert('Espera', 'Debes seleccionar por lo menos una tabla.', false);
		} else {
			$.post(`/database-all.php`, { action, tablas }, req => {
				const { status, message } = $.parseResponse(req);
				let type = (status === 1) ? 'Bien' : 'Error';
				if(status) {
					Object.values(tablas).forEach((id, n) => {
						let number_id = Object.keys(tablas)[n];
						if(action === 'optimize') {
							$(`td[data-cache="${number_id}"]`).html('Vacio');
							$(`span[data-remove="${number_id}"]`).remove();
						}
						if(number_id != 0) $(`td[data-update="${number_id}"]`).html('Hace instantes');
					});
				}
				UPModal.alert({ title: type, body: message });
			});
		}
	},
	create_backup() {
		loading.start();
		let tablas = $('input#todos').prop('checked') ? '*' : this.tablas();
		$.post(`/database-backup.php`, { tablas }, req => {
			const { status, message } = $.parseResponse(req);
			let type = (status === 0) ? 'Error' : 'Bien';
			UPModal.alert({
				title: type, 
				body: message
			});
			if(status) {
				setTimeout(() => document.location.href = `${ZCodeApp.url}/admin/database/lista`, 1000);
			}
			loading.end();
		});
	},
	delete_backup(file) {
		loading.start();
		$.post(`/database-backup-del.php`, { file }, req => {
			const { status, message } = $.parseResponse(req);
			notify.start({ 
				title: (status ? 'Error' : 'Bien'), 
				content: message, 
				type: (status ? 'danger' : 'success') 
			});
			if(!status) setTimeout(() => location.reload(), 1000);
			loading.end();
		});
	}
}

$(() => {
	$('.db-action').on('click', function () {
      const action = this.dataset.action;
      const table  = this.dataset.table;
      const table_id  = this.dataset.tableId ?? 0;
      database.table_action(action, table, table_id);
   });
	$('.db-all').on('click', function () {
      const action = this.dataset.action;
      database.table_all(action);
   });

	$('.drop-options .actions').dropdown();
})