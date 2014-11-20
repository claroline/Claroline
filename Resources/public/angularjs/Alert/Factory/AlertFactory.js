/**
 * Alert Factory
 * Manage stack of Alerts
 */
(function () {
    'use strict';

    angular.module('AlertModule').factory('AlertFactory', [
        '$timeout',
        function ($timeout) {
            var alerts = {};
            var currentIndex = 0;

            return {
                /**
                 * Get all alerts
                 * @returns Array
                 */
                getAlerts: function () {
                    return alerts;
                },

                /**
                 * Add a new alert
                 * @param msg
                 * @param type
                 * @returns AlertFactory
                 */
                addAlert: function (type, msg) {
                    currentIndex++;

                    var alert = { id: currentIndex, type: type, msg: msg };
                    alerts[currentIndex] = alert;

                    // Auto close alert
                    $timeout(function () {
                        this.closeAlert(alert);
                    }.bind(this), 3000);

                    return this;
                },

                /**
                 * Close an alert
                 * @param index
                 * @returns AlertFactory
                 */
                closeAlert: function (alert) {
                    if (alerts[alert.id]) {
                        delete alerts[alert.id];
                    }

                    return this;
                }
            };
        }
    ]);
})();