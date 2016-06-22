/**
 * Format a date string into the given format
 * @returns {function}
 * @constructor
 */
function DateStringFilter($filter) {
    /**
     * Format the date in the given format
     * @param   {String} dateString
     * @param   {String} format
     * @param   {String} timezone
     * @returns {String}
     */
    return function formatDateString(dateString, format, timezone) {
        var date = new Date(Date.parse(dateString));

        return $filter('date')(date, format, timezone);
    };
}

export default DateStringFilter
