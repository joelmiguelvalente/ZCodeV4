import { $ } from '../js/app/zcode.app.js';
import { UPModal } from '../js/ui/modal.js';

/**
 * Avatar Module
 * @description Gestión de avatares, gif, efectos hover y cambio de imagen
 */

const AvatarDOM = {
   main: '#avatar-img',
   loaders: '.avatar_loader',
   loading: '.avatar-loading',
   avatarItems: '[data-avatar] img, [data-myavatar] img',
   gifInput: 'input[name="avatar_gif"]',
   gifCheck: 'input[name="avatar_active"]',
   containerPublic: '#more_avatar, #mis_avatares',
   myAvatars: '[data-myavatar]'
};

function updateAvatarSrc(src) {
   const cacheBuster = `?v=${generateRandomString(8)}`;
   $(AvatarDOM.main).add(AvatarDOM.loaders).attr('src', `${src}${cacheBuster}`);
}

/**
 * Activar / desactivar avatar GIF
 */
export function updateAvatarGif() {
   $(AvatarDOM.gifCheck).on('change', () => {
      const gif    = $(AvatarDOM.gifInput).val();
      const active = $(AvatarDOM.gifCheck).prop('checked');
      if (!gif) {
         UPModal.alert('Error', 'No hay una URL de GIF válida', false);
         return;
      }
      $.post(`cuenta-avatar-gif.php`, { gif, active })
      .done(() => {
         const newSrc = active ? gif : avatar.current;
         updateAvatarSrc(newSrc);
      })
      .fail(() => UPModal.alert('Error', 'No se pudo actualizar el avatar.', false));
   });
}


/**
 * Mouse over - vista previa del avatar
 */
function handleMouseOver(event) {
   const image = $(event.currentTarget).attr('src');
   const avatarImg = $(AvatarDOM.main);
   avatarImg.hide().before(`<img id="avatar-preview" class="avatar-big" width="120" height="120" src="${image}" alt="avatar preview">`);
   $(AvatarDOM.loaders).attr('src', image);
}


/**
 * Mouse out - restaurar avatar
 */
function handleMouseOut() {
   const avatarImg = $(AvatarDOM.main);

   avatarImg.show();
   $('#avatar-preview').remove();
   $(AvatarDOM.loaders).attr('src', avatarImg.attr('src'));
}


/**
 * Click - cambiar avatar
 */
function handleAvatarClick(event) {
   const avatarLoading = $(AvatarDOM.loading);
   const avatarItem = $(event.currentTarget).parent();
   const source = avatarItem.data('avatar') || avatarItem.data('myavatar');

   if (!source) return;

   avatarLoading.show();
   $.post(`cuenta-avatar-change.php`, { image: source })
   .done( src => updateAvatarSrc(src) )
   .fail(() => UPModal.alert('Error', 'No se pudo cambiar el avatar.', false))
   .always(() => avatarLoading.hide());
}

/**
 * Cargar eventos sobre avatares
 */
export function changeAvatar() {
   $(AvatarDOM.containerPublic).on({
      mouseover: handleMouseOver,
      mouseout:  handleMouseOut,
      click:     handleAvatarClick
   }, AvatarDOM.avatarItems);

}

/**
 * Eliminar avatar (mis avatares)
 */
export function deleteAvatar() {
   $(document).on('click', AvatarDOM.myAvatars, function () {
      const image = $(this).data('myavatar');
      if (!image) return;
      UPModal.confirm('Eliminar avatar', '¿Seguro deseas eliminar este avatar?', () => {
         $.post(`cuenta-avatar-delete.php`, { image })
         .done(() => location.reload())
         .fail(() => UPModal.alert('Error', 'No se pudo eliminar el avatar', false));
      });
   });
}