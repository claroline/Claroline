'use strict';

/**
 * Confirm Modal Controller
 */
function ConfirmModalCtrl($scope, $modalInstance, StepFactory, title, message, confirmButton) {
    $scope.step = StepFactory.getStep();
    $scope.title = title;
    $scope.message = message;
    $scope.confirmButton = confirmButton;

    /**
     * Confirm delete step
     * @returns void
     */
    $scope.confirm = function() {
        $modalInstance.close();
    };
    
    /**
     * Abort delete step
     * @returns void
     */
    $scope.cancel = function() {
        $modalInstance.dismiss('cancel');
    };
}