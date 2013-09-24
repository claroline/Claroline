/**
 * Alert Controller
 */
var AlertCtrlProto = [
    '$scope',
    'alertFactory',
    function($scope, alertFactory) {
        $scope.alerts = alertFactory.getAlerts();

        $scope.closeAlert  = function() {
            alertFactory.closeAlert();
        };
    }
];