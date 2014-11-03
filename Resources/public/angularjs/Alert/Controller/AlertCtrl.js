/**
 * Alert Controller
 */
(function () {
    'use strict';

    angular.module('AlertModule').controller('AlertCtrl', [
        '$scope',
        'AlertFactory',
        function ($scope, AlertFactory) {
            $scope.alerts = AlertFactory.getAlerts();

            $scope.closeAlert  = function() {
                AlertFactory.closeAlert();
            };
        }
    ]);
})();