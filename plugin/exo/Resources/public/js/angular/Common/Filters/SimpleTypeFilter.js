/**
 * Filter to get a simple representation of the MIME type of an object
 * @returns {function}
 * @constructor
 */
var SimpleTypeFilter = function SimpleTypeFilter(CommonService) {
    /**
     * Get simple type for Object
     * @param  {Object} object
     * @return {String}
     */
    return function simpleType(object) {
        return CommonService.getObjectSimpleType(object);
    };
};

// Set up dependency injection
SimpleTypeFilter.$inject = [ 'CommonService' ];

// Register filter into Angular JS
angular
    .module('Common')
    .filter('simple_type', SimpleTypeFilter);

