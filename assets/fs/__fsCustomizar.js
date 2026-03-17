import { $ } from '../js/app/zcode.app.js';

export function handleChangeColor() {
   const $inputs = $('input[type="color"]');

   const palette = {
      light: $('input[name="light"]').val(),
      dark: $('input[name="dark"]').val()
   };

   const updatePreview = (type, value) => {
      palette[type] = value;
      const css = buildThemeCSS('customizer', palette.light, palette.dark);
      $('#customizer_style').html(css);
      updateColorBoxes(type, palette);
   };

   $inputs.on('input', function (event) {
      const { name, value } = event.target;
      updatePreview(name, value);
   });

   $inputs.on('change', () => $.post(`cuenta-customizer.php`, palette));
}


/* helpers */
function updateColorBoxes(type, { light, dark }) {
   const current = type === 'light' ? light : dark;

   $('.box-' + type + '.normal').css({ background: current });
   $('.box-' + type + '.hover').css({ background: shiftColor(current, 20) });
   $('.box-' + type + '.active').css({ background: shiftColor(current, -25) });
   $('.box-' + type + '.transparent').css({
      background: `rgba(${hexToRgb(current)}, .5)`
   });
}

function buildThemeCSS(name, lightColor, darkColor) {
   const lightHover = shiftColor(lightColor, 20);
   const lightActive = shiftColor(lightColor, -25);

   const darkHover = shiftColor(darkColor, 20);
   const darkActive = shiftColor(darkColor, -25);

   return `
[data-theme-color="${name}"] {
   color-scheme: light;
   --main-bg: ${lightColor};
   --main-bg-hover: ${lightHover};
   --main-bg-active: ${lightActive};
   --main-bg-rgb: rgba(${hexToRgb(lightColor)}, var(--opacity));
}
[data-theme="dark"][data-theme-color="${name}"] {
   color-scheme: dark;
   --main-bg: ${darkColor};
   --main-bg-hover: ${darkHover};
   --main-bg-active: ${darkActive};
   --main-bg-rgb: rgba(${hexToRgb(darkColor)}, var(--opacity));
}
`;
}

function shiftColor(hex, amount) {
   let color = hex.replace('#', '');
   const num = parseInt(color, 16);

   const r = Math.max(0, Math.min(255, (num >> 16) + amount));
   const g = Math.max(0, Math.min(255, (num >> 8 & 0x00FF) + amount));
   const b = Math.max(0, Math.min(255, (num & 0x0000FF) + amount));

   return '#' + [r, g, b].map(c => c.toString(16).padStart(2, '0')).join('');
}

function hexToRgb(hex) {
   const bigint = parseInt(hex.slice(1), 16);

   return [
      (bigint >> 16) & 255,
      (bigint >> 8) & 255,
      bigint & 255
   ].join(', ');
}

/**
 * Permite uso automático si se importa sin función
 */
export default function () {
   handleChangeColor();
}