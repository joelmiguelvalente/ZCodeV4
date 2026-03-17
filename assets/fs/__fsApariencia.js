import { $ } from '../js/app/zcode.app.js';
import { UPModal } from '../js/ui/modal.js';

function togglePageBoxLayout() {
   $('#pagebox-one').toggleClass('container no-container');
   $('#brandday').toggleClass('my-3 my-0');
   $('#pagebox-two').toggleClass('container container-fluid');
}

/**
 * Función central para sincronizar un setting con el servidor
 */
export function syncAccountSetting(page, selected, attributes, target = 'html') {
   $.post(`cuenta-${page}.php`, { selected })
   .done(response => {
      if (response) $(target).attr(attributes);
   })
   .fail(() => {
      UPModal.alert('Error', `No se pudo actualizar ${page}.`, false);
   });
}

/**
 * Tema del sistema
 */
export function bindSystemThemeToggle() {
   const { themes } = ZCodeApp;
   // Usamos la función flecha, y leemos el estado del elemento (e.target)
   $('#scheme').on('change', (e) => {
      // e.target es el elemento DOM #scheme
      const selected = e.target.checked ? 1 : 0; 
      syncAccountSetting('scheme', selected, {
         'data-theme': themes[selected]
      });
   });
}

/**
 * PageBox layout
 */
export function bindPageBoxToggle() {
   $('#pagebox').on('change', function () {
      const selected = this.checked ? 1 : 0;
      $.post(`cuenta-pagebox.php`, { selected })
      .done(response => {
         if (response) togglePageBoxLayout();
      })
      .fail(() => {
         UPModal.alert('Error', 'No se pudo actualizar el esquema de página.', false);
      });
   });
}

/**
* Color principal del tema
*/
export function bindAccentColorChange() {
   const { colores } = ZCodeApp;
   const $container = $('.syncThemeColor');
   $container.on('click', function(e, targetElement) {
      const $clickedItem = $(targetElement).closest('[data-color]');
      // Si el clic no fue en un swatches de color, ignorar
      if ($clickedItem.length === 0) return;
      const selected = $clickedItem.data('color');
      // Resto de la lógica
      $('.syncThemeColor > div').removeClass('border');
      $(`.syncThemeColor .tc${selected}`).addClass('border');
      syncAccountSetting('color', selected, {
         'data-theme-color': colores[selected]
      });
      toggleCustomTheme(selected);
   });
}

/**
 * Fuentes y tamaño
 */
export function bindFontSettingsChange() {
   $('#font_family').on('change', function () {
      const selected = this.value;
      syncAccountSetting('family', selected, {
         'data-font-family': selected
      }, 'body');
   });

   $('#font_size').on('change', function () {
      const selected = this.value;
      syncAccountSetting('size', selected, {
         'data-font-size': selected
      }, 'body');
   });
}

/**
 * Muestra u oculta el personalizador
 */
function toggleCustomTheme(selected) {
   const panel = $('.customizar_tema');
   if (selected === 0) {
      panel.removeClass('d-none');
      importModule('cuenta/customizar.js', 'handleChangeColor');
   } else {
      panel.addClass('d-none');
   }
}