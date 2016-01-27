/**
 * Confirm Modal Controller
 */
(function () {
    'use strict';

    angular.module('ConfirmModule').controller('ConfirmModalCtrl', [
        '$scope',
        '$modalInstance',
        'title',
        'message',
        'confirmButton',
        function ConfirmModalCtrl($scope, $modalInstance, title, message, confirmButton) {
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