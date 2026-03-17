import { $ } from '../app/zcode.app.js';
import { number_format, appParam } from '../core/utils.js';
import { UPModal } from '../ui/modal.js';
import { loading } from '../ui/loading.js';
import { mensaje } from './mensaje.js';
import { basePath, baseTitle } from '../app/zcode.app.js';

/* Notificaciones */
export const notifica = {
   cache: {},
   retry: [],
   handleNumber(parse, format = false) {
   	let parsear = parseInt(parse);
   	return format ? parsear : number_format(parsear);
   },
   handleResponse(response, successCallback) {
      let handleRes = response.split('-');
      if (handleRes.length == 3 && handleRes[0] == 0) {
         successCallback(handleRes);
      } else if (handleRes.length == 4) {
         UPModal.alert('Notificaciones', handleRes[3]);
      }
   },
	userMenuHandle(response) {
		notifica.handleResponse(response, req => {
			let cache_id = 'following_' + req[1];
			notifica.cache[cache_id] = notifica.handleNumber(req[0]);
			$('div.avatar-box').children('ul').hide();
		});
	},
	userInPostHandle(response) {
		notifica.handleResponse(response, req => {
			let text = (parseInt(req[2]) === 0) ? 'Seguir usuario' : 'Dejar de seguir';
			$('[user-follow]').html(text);
			$('.user_follow_count').html(notifica.handleNumber(req[2], true));
			notifica.userMenuHandle(response);
		});
	},
	inPostHandle(response) {
		notifica.handleResponse(response, req => {
			$('.btn.follow_post, .btn.unfollow_post').parent().toggle();
			$('#seguidores_post').html(notifica.handleNumber(req[2], true));
		});
	},
	inComunidadHandle(response) {
		notifica.handleResponse(response, req => {
			$('.follow_comunidad, .unfollow_comunidad').toggle();
			$('.comunidad_seguidores').html(notifica.handleNumber(req[2], true) + ' Seguidores');
		});
	},
	temaInComunidadHandle(response) {
		notifica.handleResponse(response, req => {
			$('.follow_tema, .unfollow_tema').toggle();
			$('.tema_notifica_count').html(notifica.handleNumber(req[2], true) + ' Seguidores');
		});
	},
	ruserInAdminHandle(response) {
		notifica.handleResponse(response, req => $('.ruser' + notifica.handleNumber(req[1])).toggle());
	},
	listInAdminHandle(response) {
      notifica.handleResponse(response, req => {
      	let lNumb = notifica.handleNumber(req[1]);
      	let firstElement = $(`.list${lNumb}:first`);
         $(`.list${lNumb}`).toggle();
         firstElement.parent('div').parent('li').children('div:first').fadeTo(0, firstElement.css('display') == 'none' ? 0.5 : 1);
      });
	},
	spamHandle(response) {
		var req = response.split('-');
		if (req.length == 2) UPModal.alert('Notificaciones', req[1]);
		else UPModal.close();
	},
	ajax(param, func, obj) {
		if ($(obj).hasClass('spinner iconify')) return;
		notifica.retry.push(param);
		notifica.retry.push(func);
		var error = param[0] != 'action=count';
		$(obj).addClass('spinner iconify');
		loading.start();
		$.post(`/notificaciones-ajax.php`, [...param, appParam('key')].join('&'), response => {
			$(obj).removeClass('spinner iconify');
			func(response, obj);
			loading.end()
		}).fail(error => {
			if (error) UPModal.error_500('notifica.ajax(notifica.retry[0], notifica.retry[1])');
			loading.end()   
		});
	},
	// action = follow | action = unfollow
	followed(action, type, id, func, obj, where = '') {
		this.ajax(['action=' + action, 'type='+type, 'obj='+id], func, obj);
		if(where === 'perfil') {
			$.post(`/perfil-seguidores-sidebar.php`, { pid: id }, req => $('.reload_followed').html(req));
		}		
	},
	share(type, id) {
		let actionNot = (type === 'post') ? 'spam' : 'c_spam';
		UPModal.setModal({
			title: 'Recomendar',
			body: `¿Quieres recomendar este ${type} a tus seguidores?`,
			buttons: {
				confirmTxt: 'Recomendar',
				confirmAction: `notifica.${actionNot}('${id}', notifica.spamHandle)`,
				cancelShow: true
			}
		});
	},
	spam(id, func) {
		this.ajax(['action=spam', 'postid='+id], func);
	},
	c_spam(id, func) {
		this.ajax(['action=c_spam', 'temaid='+id], func);
	},
	last() {
		let total = notifica.handleNumber($('a[name="Monitor"]').data('popup'));
		mensaje.close();
		if ($('#mon_list').css('display') != 'none') $('#mon_list').fadeOut();
		else {
			if (($('#mon_list').css('display') == 'none' && total > 0) || typeof notifica.cache.last == 'undefined') {
				$('a[name=Monitor]').addClass('spinner iconify');
				$('#mon_list').slideDown();
				notifica.ajax(['action=last'], function (req) {
					notifica.cache['last'] = req;
					notifica.show();
				});
			}
			else notifica.show();
		}
	},
	check: () => notifica.ajax(['action=count'], notifica.popup),
	popup(response) {
		let total = notifica.handleNumber($('a[name="Monitor"]').data('popup'));
		let withTitle = (response != total && response > 0);
		let title = withTitle ? total + ' notificaci' + (response != 1 ? 'ones' : '&oacute;n') : '';
		$('.monitor').attr({
			'data-badge': (response == 0 ? false : true)
		});
	},
	show() {
		if (typeof notifica.cache.last != 'undefined') {
			$('a[name=Monitor]').removeClass('spinner iconify');
			$('#mon_list').attr('data-dropdown', 'true');
			$('#mon_list').children('ul').html(notifica.cache.last);
		}
	},
	filter() {
		let fid = [];
		let inputs = $('.check-filter input');
		inputs.map( (pos, input) => {
			if($(input).prop('checked')) fid.push(input.id)
		})
		$.post(basePath + '/notificaciones-filtro.php', { fid });
	},
	close: () => {
		$('#mon_list').attr('data-dropdown', 'false');
		$('a[name=Monitor]').parent('li').removeClass('monitor-notificaciones');   
	}
}
