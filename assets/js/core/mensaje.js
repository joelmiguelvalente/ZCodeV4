import { $ } from '../app/zcode.app.js';
import { UPModal } from '../ui/modal.js';
import { notifica } from './notifica.js';
import { loading } from '../ui/loading.js';
import { appParam } from '../core/utils.js';
import { basePath, baseTitle } from '../app/zcode.app.js';

/* Mensajes */
export const mensaje = {
	cache: {},
	vars: [],
	handleFormInput: [
		{ label: 'Para:', name: 'msg_to', value: 'to', placeholder: 'Ingrese el nombre de usuario' }, 
		{ label: 'Asunto:', name: 'msg_subject', value: 'sub', placeholder: 'Asunto del mensaje' }, 
		{ label: 'Mensaje:', name: 'msg_body', value: 'msg', placeholder: 'Mensaje' }
	],
	splitString(object, numb) {
		let getNumber = object.split(':');
		return getNumber[parseInt(numb)];
	},
	// CREAR HTML
	form() {
		let html = (this.vars['error']) ? `<div class="empty">${this.vars['error']}</div>` : '';
		html += this.handleFormInput.reduce((inputsAppend, { label, icono, name, value, placeholder }) => {
         const isInput = value === 'msg' ? 
            `<textarea name="${name}" id="${name}" class="upform-textarea" rows="2">${this.vars[value] || ''}</textarea>` :
            `<input class="upform-input" type="text" name="${name}" id="${name}" placeholder="${placeholder}" value="${this.vars[value] || ''}">`;
         return inputsAppend + `
            <div class="upform-group">
               <label class="upform-label" for="${name}">${label}</label>
               <div class="upform-group-input">${isInput}</div>
            </div>`;
      }, '');
		return html;                          
	},
	// FUNCIONES AUX
	checkform(req){
		if(parseInt(req) == 0) mensaje.enviar(1);
		else if(parseInt(req) == 1) {
			mensaje.nuevo(mensaje.vars['to'], mensaje.vars['sub'], mensaje.vars['msg'], 'No es posible enviarse mensajes a s&iacute; mismo.');
		} else if(parseInt(req) == 2) {
			mensaje.nuevo(mensaje.vars['to'], mensaje.vars['sub'], mensaje.vars['msg'], 'Este usuario no existe. Por favor, verif&iacute;calo.');
		}    
	},
	alert(req) {
		UPModal.proccess_end();
		UPModal.alert('Aviso', `<div class="empty">${req}</div>`);  
	},
	eliminar: function(id,type){
		mensaje.ajax('editar', `ids=${id}&act=delete`, () => {
			if(type == 1){
				$('#mp_' + mensaje.splitString(id, 0)).remove();
			} else if(type == 2) {
				location.href = basePath + '/mensajes/';
			}
		});
	},
	marcar: function(id, a, type, obj){
		let actRead = (a == 0) ? 'read' : 'unread';
		let showRead = (actRead == 'read') ? 'unread' : 'read';
		mensaje.ajax('editar', `ids=${id}&act=${actRead}`, function(r){
			// CAMBIAR ENTRE LEIDO Y NO LEIDO
			if(type == 1) {
				$('#mp_' + mensaje.splitString(id, 0))[(actRead == 'read' ? 'removeClass' : 'addClass')]('unread');
				$(obj).hide();
				$(obj).parent().find(`.${showRead}`).show();
			} else {
				location.href = basePath + '/mensajes/';
			}
		});
	},
	// POST
	ajax: function(action, params, fn){
		UPModal.proccess_end();
		loading.start();
		$.post(`/mensajes-${action}.php`, params, req => {
			fn(req);
			loading.end();
		});
	},
	// PREPARAR EL ENVIO
	nuevo: function (para, asunto = '', body = '', error = '') {
		if(empty(ZCodeApp.user_key)) location.href = basePath + '/registro/';
		// GUARDAR
		this.vars['to'] = para;
		this.vars['sub'] = asunto;
		this.vars['msg'] = body;
		this.vars['error'] = error;
		//
		UPModal.proccess_end();
		UPModal.setModal({
			title: 'Nuevo mensaje',
			body: this.form(),
			buttons: {
				confirmTxt: 'Enviar mensaje',
				confirmAction: `mensaje.enviar(0)`,
				cancelShow: true
			}
		});
	},
	// ENVIAR...
	enviar: function (enviar){
		// DATOS
		this.vars['to'] = $('#msg_to').val();
		this.vars['sub'] = encodeURIComponent($('#msg_subject').val());
		this.vars['msg'] = encodeURIComponent($('#msg_body').val());
		// COMPROBAR
		if(enviar == 0){ // VERIFICAR...
			if(this.vars['to'] == '')
				mensaje.nuevo(mensaje.vars['to'], mensaje.vars['sub'], mensaje.vars['msg'], 'Por favor, especific&aacute; el destinatario.');
			if(this.vars['msg'] == '')
				mensaje.nuevo(mensaje.vars['to'], mensaje.vars['sub'], mensaje.vars['msg'], 'El mensaje esta vac&iacute;o.');
			//
			UPModal.proccess_start('Verificando...', 'Nuevo Mensaje');
			this.ajax('validar', 'para=' + this.vars['to'], mensaje.checkform);
		} else if(enviar == 1) {
			UPModal.proccess_start('Enviando...', 'Nuevo Mensaje');
			// ENVIAR
			const paramsSend = `para=${mensaje.vars['to']}&asunto=${mensaje.vars['sub']}&mensaje=${mensaje.vars['msg']}`;
			this.ajax('enviar', paramsSend, mensaje.alert);
		}
	},
	// RESPONDER
	responder(mp_id) {
	  	this.vars['mp_id'] = $('#mp_id').val();
	  	this.vars['mp_body'] = encodeURIComponent($('#respuesta').bbcode());
	  	if(this.vars['mp_body'] == '') {
			$('#respuesta').focus();
			return;
	  	}
	  //
	  this.ajax('respuesta','id=' + this.vars['mp_id'] + '&body=' + this.vars['mp_body'], req => {
			$('#respuesta').val(''); // LIMPIAMOS
			$('.wysibb-body').html('');
			switch(req.charAt(0)){
				case '0':
					UPModal.alert("Error", req.substring(3));
				break;
				case '1':
					$('#historial').append($(req.substring(3)).fadeIn('slow'));
				break;
			}
			$('#respuesta').focus();
	  	});
	},
	last() {
		let total = parseInt($('a[name="Mensajes"]').data('popup'));
		notifica.close();
		  //
		if ($('#mp_list').css('display') != 'none') $('#mp_list').hide();
		else {
			if (($('#mp_list').css('display') == 'none' && total > 0) || typeof mensaje.cache.last == 'undefined') {
				$('a[name=Mensajes]').addClass('spinner iconify');
				$('#mp_list').show();
				mensaje.ajax('lista', '', function (r) {
					mensaje.cache['last'] = r;
					mensaje.show();
				});
			} else mensaje.show();
		}
	},
	popup(response) {
		let total = parseInt($('a[name="Mensajes"]').data('popup'));
		let withTitle = (response != total && response > 0);
		let title = withTitle ? total + ' mensaje' + (response != 1 ? 's' : '') : '';
		$('.menu-list-user .mensajes').attr({
			'data-badge': (response == 0 ? false : true),
			'data-title': title
		});
	},
	show() {
		if (typeof mensaje.cache.last != 'undefined') {
			$('a[name=Mensajes]').removeClass('spinner iconify');
			$('#mp_list').show().children('ul').html(mensaje.cache.last);
		}
	},
	close() {
		$('#mp_list').slideUp();
	}
}