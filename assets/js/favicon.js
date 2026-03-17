import { $ } from './app/zcode.app.js';
import { UPModal } from './ui/modal.js';

$('#uploadForm').on('submit', function (e) {
   e.preventDefault();

   const formData = new FormData(this);
   const button = $('#uploadForm button');

   button.html('Generando...');

   $.request(`${ZCodeApp.ajax}/admin-upload-favicon.php`, {
      method: 'POST',
      data: formData,
      responseType: 'text'
   })
   .then(response => {
      const { status, message } = $.parseResponse(response);
      UPModal.alert({
         title: status === 1 ? 'Bien' : 'Error',
         body: message,
         redirect: true
      });
   })
   .catch((e) => {
      UPModal.alert({
         title: 'Error',
         body: 'Error al subir la imagen.',
      });
   })
   .finally(() => {
      button.html('Subir Imagen');
   });
});
