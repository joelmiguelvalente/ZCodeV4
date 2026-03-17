import { $ } from '../js/app/zcode.app.js';
import { UPModal } from '../js/ui/modal.js';

let countdownTimer = null;

/**
 * Inicializa la regeneración automática del QR para 2FA
 */
export function initTwoFactorQRRefresh() {
   console.log('Soy initTwoFactorQRRefresh')
   const counter = $('#countdown');
   if (!counter.length) return;
   loadQrCode();
   startQrCountdown();
}

/**
 * Inicia el contador regresivo
 */
function startQrCountdown() {
   let seconds = 30;
   // Prevención de múltiples intervalos activos
   if (countdownTimer) clearInterval(countdownTimer);
   $('#countdown').text(`${seconds}s`);
   countdownTimer = setInterval(() => {
      seconds--;
      $('#countdown').text(`${seconds}s`);
      if (seconds <= 0) {
         clearInterval(countdownTimer);
         countdownTimer = null;
         refreshQrCode();
      }
   }, 1000);
}

/**
 * Regenera el QR y reinicia el contador
 */
function refreshQrCode() {
   console.log('Soy refreshQrCode')
   $('#regenerate').empty();
   loadQrCode();
   startQrCountdown();
}

/**
 * Solicita un nuevo QR al servidor
 */
function loadQrCode() {
   $.post(`/cuenta-qr-regenerate.php`, response => {
      console.log(response)
      $('#regenerate').html(response);
   }).fail(() => {
      UPModal.alert({
         title: 'Error', 
         body: 'No se pudo regenerar el código QR.'
      });
   });
}

/**
 * Permite uso automático si se importa sin función
 */
export default function () {
   initTwoFactorQRRefresh();
}