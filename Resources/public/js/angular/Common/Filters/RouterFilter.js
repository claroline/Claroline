/**
 * Filter to generate Symfony routes
 * Based on the FriendsOfSymfony/JSRoutingBundle
 * @returns {path}
 * @constructor
 */
var RouterFilter = function RouterFilter() {
    return function path(routeName, parameters) {
        return Routing.generate(routeName, parameters);
    };
};

// Set up dependency injection
RouterFilter.$inject = [ ];

// Register filter into Angular JS
angular
    .module('Common')
    .filter('path', RouterFilter);

