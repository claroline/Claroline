'use strict';

/**
 * Alert Factory
 */
function AlertFactory() {
    var alerts = [];

    return {
        /**
         * 
         * @returns Array
         */
        getAlerts: function() {
            return alerts;
        },
        
        /**
         * 
         * @param msg
         * @param type
         * @returns AlertFactory
         */
        addAlert: function(type, msg) {
            alerts.push({ type: type, msg: msg });
            return this;
        },
        
        /**
         * 
         * @param index
         * @returns AlertFactory
         */
        closeAlert: function(index) {
            alerts.splice(index, 1);
            return this;
        }
    };
}