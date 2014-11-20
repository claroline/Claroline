/**
 * Alert box directive
 */
(function () {
    'use strict';

    angular.module('AlertModule').directive('alertBox', [
        'AlertFactory',
        function (AlertFactory) {
            return {
                restrict: 'E',
                replace: true,
                templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Alert/Partial/alert-box.html',
                scope: {},
                link: function (scope) {
                    scope.alerts = AlertFactory.getAlerts();

                    scope.closeAlert  = function (alert) {
                        AlertFactory.closeAlert(alert);
                    };
                }
            }
        }
    ]);
})();