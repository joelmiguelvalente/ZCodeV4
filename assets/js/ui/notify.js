import { generateRandomString } from '../core/utils.js';

export const notify = {
	createContainer() {
		if (document.querySelectorAll('.notify').length === 0) {
			document.body.insertAdjacentHTML('beforeend', '<div class="notify"></div>');
		}
	},
	start({ title = '', content = '', type = 'default', autoClose = true, duration = 5 }) {
		this.createContainer();
		const gid = generateRandomString(8);

		const html = `
		<div class="notify-box notify-box--${type}" gid="${gid}">
			${title ? `<div class="notify--title">${title}</div>` : ''}
			${content ? `<div class="notify--body">${content}</div>` : ''}
			<div class="notify--close" ${autoClose ? 'style="display:none;"' : ''}>
				<span role="button" data-close="${gid}">&times;</span>
			</div>
		</div>`;
		document.querySelector('.notify').insertAdjacentHTML('beforeend', html);

		if (!autoClose) {
			document.querySelector(`[data-close="${gid}"]`).onclick = () => this.remove(gid);
		} else {
			setTimeout(() => this.remove(gid), duration * 1000);
		}
	},
	remove(gid){
		const el = document.querySelector(`[gid="${gid}"]`);
		if(el) el.remove();
		if (!document.querySelector('.notify')?.children.length) {
			document.querySelector('.notify')?.remove();
		}
	}
};
