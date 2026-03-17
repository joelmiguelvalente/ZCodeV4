import { empty } from '../core/utils.js';

/**
 * UltraModal v4.1.0
 * @author Miguel92 - 2024
 * @status => success | danger | warning | default
 * @optional => icons
 * @link https://icon-sets.iconify.design/system-uicons/
*/
export const UPModal = {
	show: false,

	default: {
		status: {
			empty: '',
			success: 'check-circle',
			danger: 'cross-circle',
			warning: 'warning-triangle',
			info: 'info-circle',
			question: 'question-circle',
			password: 'lock',
			email: 'mail',
			opt: 'fingerprint'
		},
		size: 'md',
		mask: false,
		close: true,
		scrolleable: false,
		folder: 'system-uicons'
	},

	buttons: {
		confirmShow: true,
		confirmTxt: 'Continuar',
		confirmClass: 'UPModal-button UPModal-button-success',
		confirmAction: 'close',
		cancelShow: false,
		cancelTxt: 'Cancelar',
		cancelClass: 'UPModal-button UPModal-button-danger',
		cancelAction: 'close'
	},

	template: `
	<div class="UPModal">
		<div class="UPModal-mask"></div>
		<div class="UPModal-dialog" data-modal-icon="false">
			<div class="UPModal-header">
				<div class="UPModal-icon"></div>
				<div class="UPModal-title"></div>
			</div>
			<div class="UPModal-content">
				<div class="UPModal-message"></div>
				<div class="UPModal-body"></div>
			</div>
			<div class="UPModal-buttons"></div>
		</div>
	</div>`,

	/* ====================
	   CORE
	==================== */

	setModal({ icon = false, status, close, mask, size, title, body, buttons, input }) {

		if (typeof input === 'object') {
			body = this.setInput(input);
		}

		this.init();
		this.setStatus(icon, status || this.default.status.empty);
		this.setMask(mask || this.default.mask);
		this.setCloseButton(close ?? this.default.close);
		this.setTitle(title);
		this.setBody(body);
		this.setButtons(buttons);
		this.center();

		window.addEventListener('resize', () => this.center());

		const dialog = document.querySelector('.UPModal-dialog');
		dialog.style.width = `var(--modal-size-${empty(size) ? this.default.size : size})`;
	},

	close() {
		this.show = false;
		document.querySelector('.loader_modal')?.remove();
		document.body.style.overflow = 'auto';
	},

	init() {
		if (this.show) return;

		this.show = true;

		const wrapper = document.createElement('div');
		wrapper.className = 'loader_modal';
		wrapper.innerHTML = this.template;

		document.body.prepend(wrapper);
		document.body.style.overflow = this.default.scrolleable ? 'auto' : 'hidden';
	},

	/* ====================
	   STATUS & ICON
	==================== */

	setStatus(icon, status) {

		const dialog = document.querySelector('.UPModal-dialog');
		const typeStatus = (!empty(status) && !['info','question'].includes(status));

		dialog.style.background = `var(--modal-background-${typeStatus ? status : 'default'})`;
		dialog.style.setProperty('--color-icon', `var(--modal-icon-${typeStatus ? status : 'default'})`);

		if (!icon) dialog.style.removeProperty('--color-icon');

		if (status) {
			icon ? this.createIcon(status) : this.removeIcon();
		}
	},

	removeIcon() {
		document.querySelector('.UPModal-icon')?.remove();
	},

	async createIcon(status) {
		const { assets } = ZCodeApp;

		const icon = this.default.status[status];
		const folder = this.default.folder;
		const image = (folder === 'spinner') ? icon : (icon ?? '').replace(/-/g, '_');

		if (!image) return;

		const res = await fetch(`${assets}/icons/${folder}.json`);
		const data = await res.json();

		const dialog = document.querySelector('.UPModal-dialog');
		const iconContainer = document.querySelector('.UPModal-icon');

		dialog.setAttribute('data-modal-icon', true);
		iconContainer.innerHTML = data[image] || '';
	},

	/* ====================
	   UI CONTROL
	==================== */

	setMask(active) {
		if (!active) return;

		document.querySelector('.UPModal-mask')
			.addEventListener('click', () => this.close());
	},

	setCloseButton(enable) {
		if (!enable) return;

		const button = document.createElement('div');
		button.className = 'UPModal-close';
		button.dataset.modalClose = true;
		button.innerHTML = '&times;';
		button.addEventListener('click', () => this.close());

		document.querySelector('.UPModal-title').before(button);
	},

	center() {
		const dialog = document.querySelector('.UPModal-dialog');
		if (!dialog) return;

		dialog.style.position = 'fixed';
		dialog.style.top = '50%';
		dialog.style.left = '50%';
		dialog.style.transform = 'translate(-50%, -50%)';
	},

	setTitle(text) {
		document.querySelector('.UPModal-title').innerHTML = text || '';
	},

	setBody(text) {
		document.querySelector('.UPModal-body').innerHTML = text || '';
	},

	/* ====================
	   BUTTONS
	==================== */

	setButtons(buttons) {
		if (buttons) {
			const { 
				confirmShow, confirmTxt, confirmClass, confirmAction, 
				cancelShow, cancelTxt, cancelClass, cancelAction
			} = this.buttons = { ...this.buttons, ...buttons };
			// Añadiendo botones
			let buttonsHTML = '';
			if(confirmShow) {
				let myActionOK = (confirmAction === 'close' || empty(confirmAction)) ? 'UPModal.close()' : confirmAction;
				buttonsHTML += `<input type="button" role="button" onclick="${myActionOK}" value="${confirmTxt}" class="${confirmClass}">`;
			}
			if(cancelShow) {
				let myActionDeny = (cancelAction === 'close' || empty(cancelAction)) ? 'UPModal.close()' : cancelAction;
				buttonsHTML += `<input type="button" role="button" onclick="${myActionDeny}" value="${cancelTxt}" class="${cancelClass}">`;
			}
			document.querySelector('.UPModal-buttons').innerHTML = buttonsHTML || '';
		} else {
			const buttons = document.querySelectorAll('.UPModal-buttons');
			buttons.forEach(element => element.remove());
		}
	},

	/* ====================
	   INPUT TEMPLATE
	==================== */

	setInput({ label, type, name, placeholder, maxlength, required, inputmode }) {
		let iconType = !empty(inputmode) ? 'opt' : type;

		return `
		<div class="upform-group">
			<label class="upform-label" for="${name}">${label}</label>
			<div class="upform-group-input">
				<input class="upform-input"
					type="${type}"
					name="${name}"
					id="${name}"
					placeholder="${placeholder}"
					${maxlength ? `maxlength="${maxlength}"` : ''}
					${inputmode ? `inputmode="${inputmode}"` : ''}
					${required ? 'required' : ''}
				>
			</div>
		</div>`;
	},

	/* ====================
	   ALERTAS
	==================== */

	alert({ title, body, icon = false, status = '', redirect = false, button = true }) {

		this.setModal({
			icon,
			status,
			title,
			body,
			buttons: {
				confirmShow: button,
				confirmTxt: 'Aceptar',
				confirmClass: `UPModal-button UPModal-button-${empty(status) ? 'success' : status}`,
				confirmAction: `UPModal.close();${redirect ? 'location.reload();' : ''}`
			}
		});

		document.querySelector('.UPModal-dialog').classList.add('UPModal-alert');
	},

	/* ====================
	   PROCESS
	==================== */

	async proccess_start(content = 'Espere, por favor', title = '') {

		if (!empty(title)) this.setTitle(title);

		const { assets } = ZCodeApp;

		const res = await fetch(`${assets}/icons/spinner.json`);
		const data = await res.json();

		const container = document.querySelector('.UPModal-message');
		console.log(container) // me trae null
		container.innerHTML = `<div class="UPModal-proccess">${data['3-dots-scale-middle']} ${content}</div>`;
		container.style.display = 'block';

		document.querySelector('.UPModal-body').style.display = 'none';
		document.querySelector('.UPModal-buttons').style.display = 'none';
	},

	proccess_end(timeout = 1) {
		setTimeout(() => {
			document.querySelector('.UPModal-body').style.display = 'block';
			document.querySelector('.UPModal-buttons').style.display = 'block';
			document.querySelector('.UPModal-message').style.display = 'none';
		}, timeout * 1000);
	}
};

/* ====================
   GLOBAL ESC KEY
==================== */
window.UPModal = UPModal;

document.addEventListener('keydown', e => {
	if (e.key === 'Escape') {
		UPModal.close();
	}
});