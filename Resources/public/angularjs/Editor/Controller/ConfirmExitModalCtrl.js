'use strict';

/**
 * Confirm Exit editor
 */
function ConfirmExitModalCtrl($scope, $modalInstance) {
    /**
     * Exit without save modifications
     */
    $scope.exit = function () {
        $modalInstance.close('discard');
    };

    /**
     * Save modifications and exit editor
     */
    $scope.exitAndSave = function () {
        $modalInstance.close('save');
    };

    /**
     * Abort exit
     * @returns void
     */
    $scope.cancel = function() {
        $modalInstance.dismiss('cancel');
    };
}