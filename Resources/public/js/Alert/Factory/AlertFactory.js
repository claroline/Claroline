/**
 * Alert Factory
 */
var AlertFactoryProto = function() {
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
        addAlert: function(msg, type) {
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
};