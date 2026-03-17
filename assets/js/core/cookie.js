/* =======================
   COOKIE UTILITY (Standalone)
   ======================= */

export const Cookie = {
	get(name) {
		if (!document.cookie) return null;

		const cookies = document.cookie.split(';');

		for (let i = 0; i < cookies.length; i++) {
			const [key, ...rest] = cookies[i].trim().split('=');
			if (decodeURIComponent(key) === name) {
				return decodeURIComponent(rest.join('='));
			}
		}
		return null;
	},

	set(name, value, options = {}) {
		const opts = {
			path: '/',
			sameSite: 'Lax',
			...options
		};

		// Expiración
		if (typeof opts.expires === 'number') {
			const d = new Date();
			d.setTime(d.getTime() + opts.expires * 864e5);
			opts.expires = d;
		}

		// SameSite=None requiere Secure
		if (opts.sameSite?.toLowerCase() === 'none') {
			opts.secure = true;
		}

		let cookie = `${encodeURIComponent(name)}=${encodeURIComponent(value)}`;

		if (opts.expires) cookie += `; Expires=${opts.expires.toUTCString()}`;
		if (opts.path) cookie += `; Path=${opts.path}`;
		if (opts.domain) cookie += `; Domain=${opts.domain}`;
		if (opts.sameSite) cookie += `; SameSite=${opts.sameSite}`;
		if (opts.secure) cookie += `; Secure`;

		document.cookie = cookie;
		return true;
	},

	remove(name, options = {}) {
		return this.set(name, '', {
			...options,
			expires: new Date(0)
		});
	}
};
