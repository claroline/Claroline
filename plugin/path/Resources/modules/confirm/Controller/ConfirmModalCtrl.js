/**
 * Confirm Modal Controller
 */
export default class ConfirmModalCtrl {
  constructor($scope, $uibModalInstance, title, message, confirmButton) {
    $scope.title         = title
    $scope.message       = message
    $scope.confirmButton = confirmButton

    /**
     * Confirm delete step
     * @returns void
     */
    $scope.confirm = function () {
      $uibModalInstance.close()
    }

    /**
     * Abort delete step
     * @returns void
     */
    $scope.cancel = function () {
      $uibModalInstance.dismiss('cancel')
    }
  }
}
