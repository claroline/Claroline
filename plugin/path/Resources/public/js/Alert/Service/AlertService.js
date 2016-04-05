/**
 * Alert Factory
 * Manage stack of Alerts
 */
(function () {
    'use strict';

    angular.module('AlertModule').factory('AlertService', [
        '$timeout',
        function AlertService($timeout) {
            var duration = 7000;

            var alerts = [];

            var current = {};
            var timeoutflag = true;
            return {
                getCurrent: function getCurrent() {
                    if (alerts.length > 0) {
                        this.displayAlert(alerts.shift());
                    }

                    return current;
                },

                /**
                 * Get all alerts
                 * @returns {Array}
                 */
                getAlerts: function getAlerts() {
                    return alerts;
                },

                /**
                 * Add a new alert
                 * @param msg
                 * @param type
                 * @returns AlertService
                 */
                addAlert: function addAlert(type, msg, timeoutflag) {
                    var display = false;
                    if (alerts.length == 0) {
                        display = true;
                    }

                    // Store alert
                    alerts.push({ type: type, msg: msg });

                    if (display) {
                        if (timeoutflag) {
                            this.displayAlert(alerts.shift());
                        } else {
                            this.displayAlertWithoutTimeout(alerts.shift());
                        }
                    }

                    return this;
                },
                displayAlertWithoutTimeout: function displayAlertWithoutTimeout(alert) {
                    current.type = alert.type;
                    current.msg = alert.msg;
                },
                displayAlert: function displayAlert(alert) {
                    current.type = alert.type;
                    current.msg = alert.msg;

                    // Auto close alert
                    $timeout(function () {
                        this.closeCurrent();
                    }.bind(this), duration);
                },

                /**
                 * Close current displayed alert
                 * @returns AlertService
                 */
                closeCurrent: function closeCurrent() {
                    if (current) {
                        // Empty the current alert object
                        delete current.type;
                        delete current.msg;
                    }

                    if (alerts.length > 0) {
                        // Display next alert in the stack
                        this.displayAlert(alerts.shift());
                    }

                    return this;
                }
            };
        }
    ]);
})();