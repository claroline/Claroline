/**
 * Filter to generate Symfony routes
 * Based on the FriendsOfSymfony/JSRoutingBundle
 * @returns {function}
 * @constructor
 */
function RouterFilter() {
    /**
     * Generate route URL
     * @param   {String} routeName
     * @param   {Object} [parameters]
     * @returns {String}
     */
    return function path(routeName, parameters) {
        return Routing.generate(routeName, parameters);
    };
}

export default RouterFilter
