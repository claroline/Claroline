/**
 * Alert Factory
 */
(function () {
    'use strict';

    angular.module('AlertModule').factory('AlertFactory', [
        function () {
            var alerts = [];

            return {
                /**
                 *
                 * @returns Array
                 */
                getAlerts: function () {
                    return alerts;
                },

                /**
                 *
                 * @param msg
                 * @param type
                 * @returns AlertFactory
                 */
                addAlert: function (type, msg) {
                    alerts.push({ type: type, msg: msg });

                    var index = alerts.length;

                    // Auto close alert
                    setTimeout(function () {
                        console.log(index);
                        this.closeAlert(index);
                    }.bind(this), 3000);

                    return this;
                },

                /**
                 *
                 * @param index
                 * @returns AlertFactory
                 */
                closeAlert: function (index) {
                    alerts.splice(index, 1);
                    return this;
                }
            };

        }
    ]);
})();