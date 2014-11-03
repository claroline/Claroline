/**
 * Confirm Modal Controller
 */
(function () {
    'use strict';

    angular.module('ConfirmModule').controller('ConfirmModalCtrl', [
        '$scope',
        '$modalInstance',
        'StepFactory',
        'title',
        'message',
        'confirmButton',
        function ($scope, $modalInstance, StepFactory, title, message, confirmButton) {
            $scope.step          = StepFactory.getStep();
            $scope.title         = title;
            $scope.message       = message;
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
    ]);
})();