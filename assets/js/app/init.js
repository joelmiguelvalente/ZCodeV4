import { importModule } from '../core/loader.js';
//import { resetFormStatusOnInput } from './core/forms.js';

function initLazyLoading() {
	const observer = new IntersectionObserver((entries, self) => {
		entries.forEach((entry) => {
			if (!entry.isIntersecting) return;

			const target = entry.target;
			const attr = target.localName === 'source' ? 'srcset' : 'src';
			const value = target.getAttribute(`data-${attr}`);

			if (value) {
				target[attr] = value;
				target.removeAttribute(`data-${attr}`);
			}

			self.unobserve(target);
		});
	}, { rootMargin: '50px' });

	document.querySelectorAll('[data-src], [data-srcset]').forEach(el => observer.observe(el));
}

/*
export function initUPModal() {
   resetFormStatusOnInput();
}
*/

export function initApp() {

	if(document.querySelectorAll('lite-youtube').length > 0){
		importModule('LiteYt.js', 'liteYt', {});
	}

   // Una nueva forma de guardar... CTRL + S
	document.addEventListener('keydown', e => {
		if(e.ctrlKey && e.key === 's'){
			e.preventDefault();
			const btn = document.querySelector('input[type="submit"]');
			if(btn) btn.click();
		}
	});

	document.querySelectorAll('a[data-encode="true"]').forEach(a => {
		const url = a.getAttribute('href');
		a.setAttribute('href', `${ZCodeApp.url}/saliendo/?p=` + base64_encode(url));
	});

	initLazyLoading();
}