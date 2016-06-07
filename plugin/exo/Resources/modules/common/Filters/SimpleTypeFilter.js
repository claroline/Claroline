/**
 * Filter to get a simple representation of the MIME type of an object
 * @returns {function}
 * @constructor
 */
function SimpleTypeFilter(CommonService) {
    /**
     * Get simple type for Object
     * @param  {Object} object
     * @return {String}
     */
    return function simpleType(object) {
        return CommonService.getObjectSimpleType(object);
    };
}

export default SimpleTypeFilter
