/**
 * Confirm Modal Controller
 */
(function () {
    'use strict';

    angular.module('ConfirmModule').controller('ConfirmModalCtrl', [
        '$scope',
        '$uibModalInstance',
        'title',
        'message',
        'confirmButton',
        function ConfirmModalCtrl($scope, $uibModalInstance, title, message, confirmButton) {
            $scope.title         = title;
            $scope.message       = message;
            $scope.confirmButton = confirmButton;

            /**
             * Confirm delete step
             * @returns void
             */
            $scope.confirm = function() {
                $uibModalInstance.close();
            };

            /**
             * Abort delete step
             * @returns void
             */
            $scope.cancel = function() {
                $uibModalInstance.dismiss('cancel');
            };
        }
    ]);
})();