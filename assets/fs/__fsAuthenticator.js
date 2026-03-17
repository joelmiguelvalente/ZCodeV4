import { $ } from '../js/app/zcode.app.js';
import { UPModal } from '../js/ui/modal.js';

/**
 * Desactiva el 2FA del usuario
 */
export function removeTwoFactorAuth() {
   const btn = $('.remove_2fa');
   btn.on('click', () => {
      $.post(`/cuenta-delete-2fa.php`, response => {
         const status = parseInt(response.charAt(0), 10);
         const message = response.substring(3);
         UPModal.alert(status ? 'Éxito' : 'Error', message, !!status);
      })
      .fail(() => {
         UPModal.alert('Error', 'No se pudo procesar la solicitud.', false);
      });
   });
}

/**
 * Regenera tokens de recuperación para 2FA
 */
export function regenerateRecoveryTokens() {
   $.post(`/cuenta-token-regenerate.php`, response => {
      const { status, message } = response;
      if (!status) {
         UPModal.alert('Lo siento', message, false);
         return;
      }
      const tokens = message.split(',');
      const tokenList = `
         <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;text-align:center">
            ${tokens.map(token => `<span>${token.trim()}</span>`).join('')}
         </div>
      `;

      UPModal.setModal({
         title: '2FA activado',
         body: `
            Guarda estos códigos en un lugar seguro. 
            Si pierdes el acceso al autenticador, podrás usar uno de estos códigos de emergencia.
            <br><br>
            <strong>Nuevos tokens:</strong><br>
            ${tokenList}
         `,
         buttons: {
            confirmTxt: 'Listo',
            confirmAction: 'close'
         }
      });

   }, 'json')
   .fail(() => {
      UPModal.alert({
         title: 'Error', 
         body: 'No se pudo regenerar los tokens.'
      });
   });
}