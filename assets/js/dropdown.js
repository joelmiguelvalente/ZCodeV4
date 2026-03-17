document.addEventListener('DOMContentLoaded', () => {

   document.querySelectorAll('a[data-dropopen]').forEach(trigger => {
      trigger.addEventListener('click', function(event) {
         event.preventDefault();
         const dropopenValue = this.getAttribute('data-dropopen');
         if (!dropopenValue) return; 

         const targetDropdown = document.querySelector(`.up-dropdown[data-dropname="${dropopenValue}"]`);
         if (!targetDropdown) return;

         document.querySelectorAll('.up-dropdown:not(.up-dropdown--secondary)').forEach(dropdown => {
            dropdown.setAttribute('data-dropdown', 'false');
         });

         const isTrue = targetDropdown.getAttribute('data-dropdown') === 'true';

         if (!isTrue) {
            targetDropdown.setAttribute('data-dropdown', 'true');
         } else {
            targetDropdown.setAttribute('data-dropdown', 'false');
         }
      });
   });

   document.querySelectorAll('[data-dropaction]').forEach(trigger => {
      trigger.addEventListener('click', function(event) {
         let dropAction = this.getAttribute('data-dropaction') === 'true';
         const subDropdownElement = document.querySelector('.up-subdropdown');

         if (subDropdownElement) {
            if (dropAction) {
               subDropdownElement.classList.add('show');
            } else {
               subDropdownElement.classList.remove('show');
            }

            /**
             * Añadir height automático
             */
            const totalItems = subDropdownElement.querySelectorAll('.subitem-drop').length;
            const firstItemElement = subDropdownElement.querySelector('.subitem-drop');
               
            if (firstItemElement && totalItems > 0) {
               const firstHeight = firstItemElement.offsetHeight;
               const itemSpacing = 16; // 0.875 * 16px = 14px (ajusta si es distinto)
               let calculatedHeight = (Math.ceil(firstHeight) * totalItems) + (itemSpacing * totalItems) + 'px';

               const secondaryDropdownElement = document.querySelector('.up-dropdown[data-dropname="userpanel"]');
               if (secondaryDropdownElement) {
                  secondaryDropdownElement.style.height = dropAction ? calculatedHeight : 'auto';
                  secondaryDropdownElement.style.transition = 'height .4s ease-in-out';
               }
            }
         }
      });
   });

   document.addEventListener('click', (e) => {
      const isClickInsideTrigger = e.target.closest('a[data-dropopen]');
      const isClickInsideMainDropdown = e.target.closest('.-dropdown:not(.up-dropdown--secondary)');

      if (!isClickInsideTrigger && !isClickInsideMainDropdown) {
         document.querySelectorAll('.up-dropdown:not(.up-dropdown--secondary)').forEach(dropdown => {
            dropdown.setAttribute('data-dropdown', 'false');
         });
      }
   });
});
