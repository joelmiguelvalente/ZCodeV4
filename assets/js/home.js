import { $, basePath } from './app/zcode.app.js';
import { initApp } from './app/init.js';
import { loading } from './ui/loading.js';

const checkCache = new Map();

/*function actualizar_comentarios() {
	loading.start()
	
	$.get('/posts-last-comentarios.php', h => {
		console.log(h)
	});
	loading.end()
}*/

function loadTabFilter({ category, period = 'historico', box }) {
   const $filterShow = $(`.up-card[category="${category}"] .filterShow`);
   $filterShow.html(`<div class="empty">Cargando ${period}...</div>`);
   // 🔍 Verificar cache
   if (checkCache.has(box) && checkCache.get(box).has(period)) {
      $filterShow.html(checkCache.get(box).get(period));
      return;
   }
   try {
   	$.post(`/tops-${box}.php`, $.param({ period: period }), function(responseData) {
	      // 💾 Guardar en cache
	      if (!checkCache.has(box)) {
	         checkCache.set(box, new Map());
	      }
	      checkCache.get(box).set(period, responseData);
	     	$filterShow.html(responseData);
   	});
   } catch (error) {
      console.error('Error al cargar los datos:', error);
      $filterShow.html('<div class="empty">Error al cargar los datos</div>');
   }
}


$(() => {
	$('.filter span').on('click', function(action) {
  		const $this = $(action.currentTarget);
  		const category = $this.data('category');
  		const period = $this.data('period');
  		const box = $this.data('box');
  		
		// Desactivamos el active
		$(`[data-category="${category}"]`).attr('data-active', false);
    	$(`[data-category="${category}"][data-period="${period}"]`).attr('data-active', true);
    	// Cargamos...
    	loadTabFilter({ category, period, box });
	});

	// Este funciona
	$('.lastPosts.up-card > .up-card--header .up-header--icon').on('click', () => actualizar_comentarios());
	initApp();
});