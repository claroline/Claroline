'use strict';

/**
 * Alert Controller
 */
function AlertCtrl($scope, AlertFactory) {
    $scope.alerts = AlertFactory.getAlerts();

    $scope.closeAlert  = function() {
        AlertFactory.closeAlert();
    };
}