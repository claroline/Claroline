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
                    // Get symfony Alerts
                    if (EditorApp.alerts) {
                        // Get info messages
                        if (EditorApp.alerts.info) {
                            for (var i = 0; i < EditorApp.alerts.info.length; i++) {
                                AlertFactory.addAlert('info', EditorApp.alerts.info[i]);
                            }
                        }

                        // Get success messages
                        if (EditorApp.alerts.success) {
                            for (var i = 0; i < EditorApp.alerts.success.length; i++) {
                                AlertFactory.addAlert('success', EditorApp.alerts.success[i]);
                            }
                        }

                        // Get warning messages
                        if (EditorApp.alerts.warning) {
                            for (var i = 0; i < EditorApp.alerts.warning.length; i++) {
                                AlertFactory.addAlert('warning', EditorApp.alerts.warning[i]);
                            }
                        }

                        // Get danger messages
                        if (EditorApp.alerts.danger) {
                            for (var i = 0; i < EditorApp.alerts.danger.length; i++) {
                                AlertFactory.addAlert('danger', EditorApp.alerts.danger[i]);
                            }
                        }

                        // Get alert messages
                        if (EditorApp.alerts.alert) {
                            for (var i = 0; i < EditorApp.alerts.alert.length; i++) {
                                AlertFactory.addAlert('alert', EditorApp.alerts.alert[i]);
                            }
                        }
                    }

                    scope.alerts = AlertFactory.getAlerts();

                    console.log(scope.alerts);

                    scope.closeAlert  = function (alert) {
                        AlertFactory.closeAlert(alert);
                    };
                }
            }
        }
    ]);
})();