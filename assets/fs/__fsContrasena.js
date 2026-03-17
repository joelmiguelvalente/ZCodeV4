/**
 * Módulo de generación de contraseñas seguras
 * @author Miguel92
 * @version 2.0.0
 */

const DEFAULT_LENGTH = 16;
const CHARSET = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&_~|}{[]?-=";
const INPUT_IDS = [
   'new_passwd',
   'confirm_passwd'
];
/**
 * Genera una contraseña segura aleatoria
 */
const generatePassword = (length = DEFAULT_LENGTH) => {
   return Array.from({ length }, () =>
      CHARSET[Math.floor(Math.random() * CHARSET.length)]
   ).join('');
};

/**
 * Inserta una nueva contraseña generada
 * en los inputs definidos
 */
export function generatePasswordToInputs() {
   const password = generatePassword();
   INPUT_IDS.forEach(id => {
      const input = document.getElementById(id);
      if (input) {
         input.type = 'text';
         input.value = password;
      }
   });
   console.info('✅ Contraseña generada correctamente');
}

/**
 * Permite uso automático si se importa sin función
 */
export default function () {
   generatePasswordToInputs();
}
