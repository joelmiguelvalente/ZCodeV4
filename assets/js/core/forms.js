export function resetFormStatusOnInput(scope = document) {
   const $scope = $(scope);

   $scope.on('input', 'input, textarea, select', function () {
      const $formGroup = $(this).closest('.upform-group');

      $formGroup.find('.upform-status').removeClass('error ok loading info').empty();
   });
}
