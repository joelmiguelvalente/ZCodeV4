import { $ } from '../app/zcode.app.js';
// NEWS
export const news = {
	total: 0,
	count: 1,
	time: 7,
	countdownTime: 0,
	slider() {
		if (this.total > 1) {
			this.count = this.count < this.total ? this.count + 1 : 1;
			$('#top_news .news--item').hide();
			$(`#new_${this.count}`).fadeIn();
			this.startCountdown();
			setTimeout(() => this.slider(), this.time * 1000);
		}
	},
	startCountdown() {
		this.countdownTime = this.time - 1;
		const countdown = $(`#new_${this.count} .countdown`);
		const interval = setInterval(() => {
			if(this.countdownTime > 0) {
				if(countdown) countdown.textContent = `${this.countdownTime}s`;
				this.countdownTime--;
			} else {
				clearInterval(interval);
			}
		}, 1000);
	}
};