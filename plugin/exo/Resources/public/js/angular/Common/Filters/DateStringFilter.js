/**
 * Format a date string into the given format
 * @returns {function}
 * @constructor
 */
var DateStringFilter = function DateStringFilter($filter) {
    /**
     * Format the date in the given format
     * @param   {String} dateString
     * @param   {String} format
     * @param   {String} timezone
     * @returns {String}
     */
    return function path(dateString, format, timezone) {
        var date = new Date(Date.parse(dateString));

        return $filter('date')(date, format, timezone);
    };
};

// Set up dependency injection
DateStringFilter.$inject = [ '$filter' ];

// Register filter into Angular JS
angular
    .module('Common')
    .filter('date_string', DateStringFilter);

