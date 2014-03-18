'use strict';

/**
 * Confirm delete Modal Controller
 */
function ConfirmDeleteModalCtrl($scope, $modalInstance, StepFactory) {
    $scope.step = StepFactory.getStep();

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